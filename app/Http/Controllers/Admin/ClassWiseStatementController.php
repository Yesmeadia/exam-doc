<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Services\ClassWiseStatementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ClassWiseStatementController extends Controller
{
    public function __construct(
        protected ClassWiseStatementService $statementService
    ) {}

    /**
     * Display the interactive Class Wise Statement broadsheet.
     */
    public function index(Request $request): View
    {
        $exams = Exam::orderBy('id', 'desc')->get();
        $selectedExamId = $request->filled('exam_id') ? (int) $request->query('exam_id') : $exams->first()?->id;
        $selectedExam = $exams->firstWhere('id', $selectedExamId) ?? $exams->first();

        $classes = SchoolClass::with('sections')->where('status', 'active')->orderBy('display_order')->get();
        $selectedClassId = $request->filled('class_id') ? (int) $request->query('class_id') : $classes->first()?->id;
        $selectedClass = $classes->firstWhere('id', $selectedClassId) ?? $classes->first();

        $sections = $selectedClass ? Section::withTrashed()->where('class_id', $selectedClass->id)->get() : collect();
        $selectedSectionId = $request->filled('section_id') && $request->query('section_id') !== 'all'
            ? (int) $request->query('section_id')
            : null;

        $statementData = null;
        if ($selectedExam && $selectedClass) {
            try {
                $statementData = $this->statementService->getStatementData(
                    $selectedExam->id,
                    $selectedClass->id,
                    $selectedSectionId
                );
            } catch (Throwable $e) {
                Log::error('Class Wise Statement query error: ' . $e->getMessage(), [
                    'exam_id' => $selectedExam->id,
                    'class_id' => $selectedClass->id,
                    'section_id' => $selectedSectionId,
                ]);
            }
        }

        return view('results.admin.statements.class_wise', compact(
            'exams',
            'selectedExam',
            'selectedExamId',
            'classes',
            'selectedClass',
            'selectedClassId',
            'sections',
            'selectedSectionId',
            'statementData'
        ));
    }

    /**
     * Download Class Wise Statement as A4 Landscape PDF.
     */
    public function exportPdf(Request $request): Response|RedirectResponse
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'nullable',
        ]);

        $examId = (int) $request->input('exam_id');
        $classId = (int) $request->input('class_id');
        $sectionId = $request->filled('section_id') && $request->input('section_id') !== 'all'
            ? (int) $request->input('section_id')
            : null;

        try {
            $exam = Exam::findOrFail($examId);
            $class = SchoolClass::findOrFail($classId);
            $section = $sectionId ? Section::withTrashed()->find($sectionId) : null;

            $pdfContent = $this->statementService->renderPdfContent($examId, $classId, $sectionId);
            $fileName = $this->statementService->makeFileName($exam, $class, $section, 'pdf');

            return response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
                'Cache-Control' => 'no-store, no-cache, must-revalidate',
                'Pragma' => 'no-cache',
            ]);
        } catch (Throwable $e) {
            Log::error('Class Wise Statement PDF export failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withErrors(['error' => 'Failed to export PDF statement: ' . $e->getMessage()]);
        }
    }

    /**
     * Download Class Wise Statement as Excel spreadsheet (.xlsx).
     */
    public function exportExcel(Request $request): StreamedResponse|RedirectResponse
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'nullable',
        ]);

        $examId = (int) $request->input('exam_id');
        $classId = (int) $request->input('class_id');
        $sectionId = $request->filled('section_id') && $request->input('section_id') !== 'all'
            ? (int) $request->input('section_id')
            : null;

        try {
            $exam = Exam::findOrFail($examId);
            $class = SchoolClass::findOrFail($classId);
            $section = $sectionId ? Section::withTrashed()->find($sectionId) : null;

            $spreadsheet = $this->statementService->renderExcelSpreadsheet($examId, $classId, $sectionId);
            $fileName = $this->statementService->makeFileName($exam, $class, $section, 'xlsx');
            $writer = new Xlsx($spreadsheet);

            return response()->streamDownload(function () use ($writer) {
                $writer->save('php://output');
            }, $fileName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Cache-Control' => 'no-store, no-cache, must-revalidate',
                'Pragma' => 'no-cache',
            ]);
        } catch (Throwable $e) {
            Log::error('Class Wise Statement Excel export failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withErrors(['error' => 'Failed to export Excel statement: ' . $e->getMessage()]);
        }
    }
}
