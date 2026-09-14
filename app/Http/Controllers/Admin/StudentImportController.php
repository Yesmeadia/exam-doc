<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkStudentImportRequest;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Services\StudentImportService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StudentImportController extends Controller
{
    public function __construct(
        protected StudentImportService $importService
    ) {}

    /**
     * Display student bulk import form.
     */
    public function showImportForm(): View
    {
        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        $classes       = SchoolClass::with('sections')->where('status', 'active')->orderBy('display_order')->get();

        return view('results.admin.students.import', compact('academicYears', 'classes'));
    }

    /**
     * Download Excel template (simplified: Roll No | Student ID | Student Name).
     */
    public function downloadTemplate(): BinaryFileResponse
    {
        $tempPath = $this->importService->generateTemplate();

        return response()->download(
            $tempPath,
            'student_bulk_import_template.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        )->deleteFileAfterSend(true);
    }

    /**
     * Preview and validate spreadsheet.
     */
    public function preview(BulkStudentImportRequest $request): View|RedirectResponse
    {
        try {
            $previewData = $this->importService->validateAndPreview(
                $request->file('file'),
                (int) $request->academic_year_id,
                (int) $request->class_id,
                (int) $request->section_id
            );

            // Store valid rows in session for confirmation
            $importToken = uniqid('import_', true);
            session(["import_preview_{$importToken}" => [
                'valid_rows'       => $previewData['valid_rows'],
                'academic_year_id' => $request->academic_year_id,
                'class_id'         => $request->class_id,
                'section_id'       => $request->section_id,
            ]]);

            $academicYear = AcademicYear::find($request->academic_year_id);
            $class        = SchoolClass::find($request->class_id);
            $section      = Section::find($request->section_id);

            return view('results.admin.students.preview', [
                'preview'      => $previewData,
                'importToken'  => $importToken,
                'academicYear' => $academicYear,
                'class'        => $class,
                'section'      => $section,
            ]);
        } catch (Exception $e) {
            return back()->withErrors(['file' => $e->getMessage()]);
        }
    }

    /**
     * Commit valid previewed rows into the database.
     */
    public function commit(Request $request): RedirectResponse
    {
        $request->validate([
            'import_token' => 'required|string',
        ]);

        $importToken = $request->import_token;
        $sessionData = session("import_preview_{$importToken}");

        if (!$sessionData || empty($sessionData['valid_rows'])) {
            return redirect()->route('admin.students.import.form')
                ->withErrors(['error' => 'Import session expired or no valid rows found. Please upload again.']);
        }

        $validRows     = $sessionData['valid_rows'];
        $importedCount = $this->importService->importValidRows($validRows, Auth::id());

        // Clear session data
        session()->forget("import_preview_{$importToken}");

        return redirect()->route('admin.students.index')->with(
            'success',
            "Bulk import completed: {$importedCount} student(s) imported successfully."
        );
    }
}
