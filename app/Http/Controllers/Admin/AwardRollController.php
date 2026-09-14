<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AwardRoll;
use App\Models\Exam;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Subject;
use App\Services\AwardRollService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class AwardRollController extends Controller
{
    public function __construct(
        protected AwardRollService $awardRollService
    ) {}

    public function index(Request $request): View
    {
        $exams = Exam::orderBy('id', 'desc')->get();
        $selectedExamId = $request->filled('exam_id') ? $request->query('exam_id') : $exams->first()?->id;
        $selectedExam = $exams->firstWhere('id', $selectedExamId) ?? $exams->first();

        // Eager-load sections for dynamic Alpine dropdown
        $classes = SchoolClass::with('sections')->where('status', 'active')->orderBy('display_order')->get();
        $selectedClassId = $request->query('class_id');
        $sections = $selectedClassId ? Section::where('class_id', $selectedClassId)->get() : collect();
        $selectedSectionId = $request->query('section_id');

        $subjects = Subject::with('classes')->where('status', 'active')
            ->when($selectedExam?->academic_year_id, fn ($q, $yrId) => $q->where('academic_year_id', $yrId))
            ->orderBy('display_order')
            ->orderBy('name')
            ->get();

        if ($subjects->isEmpty()) {
            $subjects = Subject::with('classes')->where('status', 'active')->orderBy('display_order')->orderBy('name')->get();
        }

        $selectedSubjectId = $request->query('subject_id');

        // Archive examination filter (optional)
        $archiveExamId = $request->query('archive_exam_id');

        // Existing generated award rolls list - ALWAYS latest first
        $awardRolls = AwardRoll::with(['exam', 'schoolClass', 'section', 'subject', 'generator'])
            ->when($archiveExamId, fn ($q) => $q->where('exam_id', $archiveExamId))
            ->orderByDesc('updated_at')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('results.admin.award_rolls.index', compact(
            'exams',
            'selectedExam',
            'selectedExamId',
            'classes',
            'selectedClassId',
            'sections',
            'selectedSectionId',
            'subjects',
            'selectedSubjectId',
            'awardRolls',
            'archiveExamId'
        ));
    }

    /**
     * Download individual Award Roll report (PDF or Excel) generated directly in memory.
     * Flow: Admin clicks "Download Report" -> Authenticated + Authorized -> Query DB -> Generate in memory -> HTTP download response.
     * Server keeps NO report files on disk.
     */
    public function generate(Request $request): Response|StreamedResponse|RedirectResponse
    {
        $request->validate([
            'exam_id'    => 'required|exists:exams,id',
            'class_id'   => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'format'     => 'nullable|in:pdf,excel',
        ]);

        try {
            $exam    = Exam::findOrFail($request->exam_id);
            $class   = SchoolClass::findOrFail($request->class_id);
            $section = Section::findOrFail($request->section_id);
            $subject = Subject::findOrFail($request->subject_id);
            $format  = $request->input('format', 'pdf');

            // Maintain audit record for archive view without storing physical files on server
            $this->awardRollService->generatePdf(
                $exam,
                $class,
                $section,
                $subject,
                Auth::id()
            );

            // Format 1: In-memory Excel spreadsheet download
            if ($format === 'excel') {
                $spreadsheet = $this->awardRollService->renderExcelSpreadsheet($exam, $class, $section, $subject);
                $fileName    = $this->awardRollService->makeFileName($exam, $class, $section, $subject, 'xlsx');
                $writer      = new Xlsx($spreadsheet);

                return response()->streamDownload(function () use ($writer) {
                    $writer->save('php://output');
                }, $fileName, [
                    'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'Cache-Control'       => 'no-store, no-cache, must-revalidate',
                    'Pragma'              => 'no-cache',
                ]);
            }

            // Format 2 (Default): In-memory PDF download
            $pdfContent = $this->awardRollService->renderPdfContent($exam, $class, $section, $subject);
            $fileName   = $this->awardRollService->makeFileName($exam, $class, $section, $subject, 'pdf');

            return response($pdfContent, 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
                'Cache-Control'       => 'no-store, no-cache, must-revalidate',
                'Pragma'              => 'no-cache',
            ]);
        } catch (Throwable $e) {
            Log::error('Failed to generate Award Roll: ' . $e->getMessage(), [
                'trace'   => $e->getTraceAsString(),
                'request' => $request->only(['exam_id', 'class_id', 'section_id', 'subject_id', 'format']),
            ]);

            return back()->withErrors(['error' => 'Failed to generate report: ' . $e->getMessage()]);
        }
    }

    /**
     * Bulk generate Award Roll metadata for all active assignments in an exam.
     */
    public function generateBulk(Request $request): RedirectResponse
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
        ]);

        try {
            $exam = Exam::findOrFail($request->exam_id);
            $generated = $this->awardRollService->generateBulkForExam($exam, Auth::id());

            return redirect()->route('admin.award-rolls.index', ['exam_id' => $exam->id])
                ->with('success', "Bulk Award Roll archive refreshed: " . count($generated) . " Award Rolls ready for instant download.");
        } catch (Throwable $e) {
            Log::error('Bulk Award Roll generation failed: ' . $e->getMessage(), [
                'trace'   => $e->getTraceAsString(),
                'exam_id' => $request->exam_id,
            ]);

            return back()->withErrors(['error' => 'Bulk generation failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Live digital preview: generates PDF on-the-fly from latest database data in memory.
     * Always reflects the most up-to-date marks without server disk storage.
     */
    public function preview(AwardRoll $awardRoll): Response|RedirectResponse
    {
        $this->authorize('download', $awardRoll);

        if (!$awardRoll->exam || !$awardRoll->schoolClass || !$awardRoll->section || !$awardRoll->subject) {
            return back()->withErrors(['error' => 'Associated exam, class, section, or subject not found for this Award Roll.']);
        }

        try {
            $pdfContent = $this->awardRollService->renderPdfContent(
                $awardRoll->exam,
                $awardRoll->schoolClass,
                $awardRoll->section,
                $awardRoll->subject
            );

            $fileName = $this->awardRollService->makeFileName(
                $awardRoll->exam,
                $awardRoll->schoolClass,
                $awardRoll->section,
                $awardRoll->subject,
                'pdf'
            );

            return response($pdfContent, 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $fileName . '"',
                'Cache-Control'       => 'no-store, no-cache, must-revalidate',
                'Pragma'              => 'no-cache',
            ]);
        } catch (Throwable $e) {
            Log::error('Award Roll live preview failed: ' . $e->getMessage(), [
                'award_roll_id' => $awardRoll->id,
                'trace'         => $e->getTraceAsString(),
            ]);
            return back()->withErrors(['error' => 'Failed to generate live preview: ' . $e->getMessage()]);
        }
    }

    /**
     * Secure authorized on-demand download of Award Roll PDF generated in memory.
     * The server keeps NO physical file — always queries the live database.
     */
    public function download(AwardRoll $awardRoll): Response|RedirectResponse
    {
        $this->authorize('download', $awardRoll);

        if (!$awardRoll->exam || !$awardRoll->schoolClass || !$awardRoll->section || !$awardRoll->subject) {
            return back()->withErrors(['error' => 'Associated exam, class, section, or subject not found for this Award Roll.']);
        }

        try {
            $pdfContent = $this->awardRollService->renderPdfContent(
                $awardRoll->exam,
                $awardRoll->schoolClass,
                $awardRoll->section,
                $awardRoll->subject
            );

            $fileName = $this->awardRollService->makeFileName(
                $awardRoll->exam,
                $awardRoll->schoolClass,
                $awardRoll->section,
                $awardRoll->subject,
                'pdf'
            );

            return response($pdfContent, 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
                'Cache-Control'       => 'no-store, no-cache, must-revalidate',
                'Pragma'              => 'no-cache',
            ]);
        } catch (Throwable $e) {
            Log::error('Award Roll in-memory download failed: ' . $e->getMessage(), [
                'award_roll_id' => $awardRoll->id,
                'trace'         => $e->getTraceAsString(),
            ]);

            return back()->withErrors(['error' => 'Failed to download report: ' . $e->getMessage()]);
        }
    }

    /**
     * Secure authorized on-demand download of Award Roll Excel generated in memory.
     * The server keeps NO physical file — always queries the live database.
     */
    public function downloadExcel(AwardRoll $awardRoll): StreamedResponse|RedirectResponse
    {
        $this->authorize('download', $awardRoll);

        if (!$awardRoll->exam || !$awardRoll->schoolClass || !$awardRoll->section || !$awardRoll->subject) {
            return back()->withErrors(['error' => 'Associated exam, class, section, or subject not found for this Award Roll.']);
        }

        try {
            $spreadsheet = $this->awardRollService->renderExcelSpreadsheet(
                $awardRoll->exam,
                $awardRoll->schoolClass,
                $awardRoll->section,
                $awardRoll->subject
            );

            $fileName = $this->awardRollService->makeFileName(
                $awardRoll->exam,
                $awardRoll->schoolClass,
                $awardRoll->section,
                $awardRoll->subject,
                'xlsx'
            );

            $writer = new Xlsx($spreadsheet);

            return response()->streamDownload(function () use ($writer) {
                $writer->save('php://output');
            }, $fileName, [
                'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Cache-Control'       => 'no-store, no-cache, must-revalidate',
                'Pragma'              => 'no-cache',
            ]);
        } catch (Throwable $e) {
            Log::error('Award Roll Excel download failed: ' . $e->getMessage(), [
                'award_roll_id' => $awardRoll->id,
                'trace'         => $e->getTraceAsString(),
            ]);

            return back()->withErrors(['error' => 'Failed to download Excel report: ' . $e->getMessage()]);
        }
    }
}
