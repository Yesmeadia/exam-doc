<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\Mark;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Setting;
use App\Models\Student;
use App\Models\Subject;
use App\Services\ResultCalculationService;
use App\Services\StudentSubjectService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StudentMarksSheetController extends Controller
{
    public function __construct(
        protected StudentSubjectService $studentSubjectService,
        protected ResultCalculationService $resultCalculationService
    ) {}

    /**
     * Display a selector directory of students to view their marks sheets.
     */
    public function index(Request $request): View
    {
        $academicYearId = $request->query('academic_year_id');
        $classId = $request->query('class_id');
        $sectionId = $request->query('section_id');
        $search = $request->query('search');

        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        $activeYear = $academicYears->firstWhere('is_active', true) ?? $academicYears->first();
        if (!$academicYearId && $activeYear) {
            $academicYearId = $activeYear->id;
        }

        $classes = SchoolClass::where('status', 'active')->orderBy('display_order')->get();
        $sections = $classId ? Section::where('class_id', $classId)->get() : collect();

        $students = Student::with(['academicYear', 'schoolClass', 'section'])
            ->when($academicYearId, fn ($q) => $q->where('academic_year_id', $academicYearId))
            ->when($classId, fn ($q) => $q->where('class_id', $classId))
            ->when($sectionId, fn ($q) => $q->where('section_id', $sectionId))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('student_id', 'like', "%{$search}%")
                        ->orWhere('roll_no', 'like', "%{$search}%");
                });
            })
            ->orderBy('class_id')
            ->orderBy('section_id')
            ->orderBy('roll_no', 'asc')
            ->paginate(20);

        return view('results.admin.students.marks_sheets_index', compact(
            'students',
            'academicYears',
            'classes',
            'sections',
            'academicYearId',
            'classId',
            'sectionId',
            'search'
        ));
    }

    /**
     * Display the comprehensive timetable-style marks sheet for an individual student.
     */
    public function show(Student $student): View
    {
        $data = $this->buildMarksSheetData($student);

        return view('results.admin.students.marks_sheet', $data);
    }

    /**
     * Download the individual student marks sheet as an A4 portrait PDF.
     */
    public function downloadPdf(Student $student): Response
    {
        $data = $this->buildMarksSheetData($student);

        $pdf = Pdf::loadView('results.pdf.student-marks-sheet', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => false,
                'defaultFont'          => 'sans-serif',
            ], true);

        $safeName = Str::slug($student->name, '_');
        $safeRoll = $student->roll_no ?? $student->student_id;
        $fileName = "Marks_Sheet_{$safeRoll}_{$safeName}.pdf";

        return response($pdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Cache-Control'       => 'no-store, no-cache, must-revalidate',
            'Pragma'              => 'no-cache',
        ]);
    }

    /**
     * Build all data required for the marks sheet (screen and PDF).
     */
    public function buildMarksSheetData(Student $student): array
    {
        $student->load(['academicYear', 'schoolClass', 'section']);

        // 1. Get subjects for this student (checks section subjects, electives for 11/12, or full curriculum)
        $subjects = $this->studentSubjectService->getSubjectsForStudent($student);

        // Ensure any subjects where the student already has marks recorded are also included
        $markSubjectIds = Mark::where('student_id', $student->id)->distinct()->pluck('subject_id');
        if ($markSubjectIds->isNotEmpty()) {
            $missingSubjectIds = $markSubjectIds->diff($subjects->pluck('id'));
            if ($missingSubjectIds->isNotEmpty()) {
                $additionalSubjects = Subject::whereIn('id', $missingSubjectIds)->where('status', 'active')->get();
                $subjects = $subjects->merge($additionalSubjects)->sortBy('display_order')->values();
            }
        }

        // 2. Get exams for this student's academic year (ordered chronologically)
        $exams = Exam::where('academic_year_id', $student->academic_year_id)
            ->orderBy('start_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        if ($exams->isEmpty()) {
            $examIds = Mark::where('student_id', $student->id)->distinct()->pluck('exam_id');
            $exams = Exam::whereIn('id', $examIds)->orderBy('start_date', 'asc')->get();
        }

        // 3. Fetch all marks entered for this student across all these exams and subjects
        $marks = Mark::where('student_id', $student->id)
            ->whereIn('exam_id', $exams->pluck('id'))
            ->whereIn('subject_id', $subjects->pluck('id'))
            ->get()
            ->groupBy('exam_id');

        // 4. Build Timetable Matrix Data
        $matrix = [];
        $examSummaries = [];
        $subjectSummaries = [];

        foreach ($subjects as $subject) {
            $subjectSummaries[$subject->id] = [
                'total_obtained' => 0,
                'total_max' => 0,
                'count_entered' => 0,
            ];
        }

        $grandTotalObtained = 0;
        $grandTotalMax = 0;
        $allExamsPassed = true;

        foreach ($exams as $exam) {
            $examMarks = $marks->get($exam->id, collect())->keyBy('subject_id');
            $rowMarks = [];
            $examObtained = 0;
            $examMax = 0;
            $examPassed = true;
            $hasMissing = false;

            foreach ($subjects as $subject) {
                $markRecord = $examMarks->get($subject->id);
                $maxMarks = (float) $subject->maximum_marks;
                $passMarks = (float) $subject->pass_marks;
                $examMax += $maxMarks;

                if (!$markRecord) {
                    $hasMissing = true;
                    $examPassed = false;
                    $rowMarks[$subject->id] = [
                        'obtained' => null,
                        'max' => $maxMarks,
                        'pass_marks' => $passMarks,
                        'is_absent' => false,
                        'status' => 'pending',
                        'is_passed' => false,
                        'percentage' => null,
                        'grade' => '—',
                        'remarks' => null,
                    ];
                    continue;
                }

                $isAbsent = (bool) $markRecord->is_absent;
                $obtained = $isAbsent ? null : ($markRecord->marks !== null ? (float) $markRecord->marks : null);

                if ($obtained !== null) {
                    $examObtained += $obtained;
                    $subjectSummaries[$subject->id]['total_obtained'] += $obtained;
                    $subjectSummaries[$subject->id]['total_max'] += $maxMarks;
                    $subjectSummaries[$subject->id]['count_entered']++;
                }

                $isPassed = !$isAbsent && ($obtained !== null) && ($obtained >= $passMarks);
                if (!$isPassed) {
                    $examPassed = false;
                }

                $pct = ($maxMarks > 0 && $obtained !== null) ? round(($obtained / $maxMarks) * 100, 1) : 0;
                $grade = $isAbsent ? 'AB' : ($obtained !== null ? $this->resultCalculationService->getGradeFromPercentage($pct) : '—');

                $rowMarks[$subject->id] = [
                    'obtained' => $obtained,
                    'max' => $maxMarks,
                    'pass_marks' => $passMarks,
                    'is_absent' => $isAbsent,
                    'status' => $markRecord->status,
                    'is_passed' => $isPassed,
                    'percentage' => $pct,
                    'grade' => $grade,
                    'remarks' => $markRecord->remarks,
                ];
            }

            $examPct = $examMax > 0 ? round(($examObtained / $examMax) * 100, 1) : 0;
            $examGrade = $this->resultCalculationService->getGradeFromPercentage($examPct);
            $examResult = ($examPassed && !$hasMissing) ? 'PASS' : ($hasMissing ? 'PENDING' : 'FAIL');

            if ($examResult !== 'PASS') {
                $allExamsPassed = false;
            }

            $grandTotalObtained += $examObtained;
            $grandTotalMax += $examMax;

            $matrix[$exam->id] = $rowMarks;
            $examSummaries[$exam->id] = [
                'total_obtained' => $examObtained,
                'total_max' => $examMax,
                'percentage' => $examPct,
                'grade' => $examGrade,
                'result' => $examResult,
                'has_missing' => $hasMissing,
            ];
        }

        foreach ($subjects as $subject) {
            $subTot = $subjectSummaries[$subject->id];
            $subPct = $subTot['total_max'] > 0 ? round(($subTot['total_obtained'] / $subTot['total_max']) * 100, 1) : 0;
            $subGrade = $subTot['total_max'] > 0 ? $this->resultCalculationService->getGradeFromPercentage($subPct) : '—';
            $subjectSummaries[$subject->id]['percentage'] = $subPct;
            $subjectSummaries[$subject->id]['grade'] = $subGrade;
        }

        $grandPercentage = $grandTotalMax > 0 ? round(($grandTotalObtained / $grandTotalMax) * 100, 2) : 0;
        $overallGrade = $this->resultCalculationService->getGradeFromPercentage($grandPercentage);
        $overallResult = $allExamsPassed && $grandTotalMax > 0 ? 'PASS' : ($grandTotalMax === 0 ? 'PENDING' : 'FAIL');

        // Sibling Students in same class/section for switcher / prev / next navigation
        $siblings = Student::where('class_id', $student->class_id)
            ->where('section_id', $student->section_id)
            ->where('academic_year_id', $student->academic_year_id)
            ->orderBy('roll_no', 'asc')
            ->get();

        $currentIndex = $siblings->search(fn ($s) => $s->id === $student->id);
        $prevStudent = $currentIndex > 0 ? $siblings->get($currentIndex - 1) : null;
        $nextStudent = ($currentIndex !== false && $currentIndex < $siblings->count() - 1) ? $siblings->get($currentIndex + 1) : null;

        $schoolName = Setting::get('school_name', 'RUIHSS POONCH');
        $schoolShortName = Setting::get('school_short_name', 'RUIHSS');

        // Safe logo data URI
        $logoDataUri = null;
        $logoCandidates = [
            public_path('logo.svg'),
            base_path('public/logo.svg'),
            dirname(base_path()) . '/public_html/logo.svg',
        ];
        foreach ($logoCandidates as $logoPath) {
            if ($logoPath && file_exists($logoPath)) {
                $logoDataUri = 'data:image/svg+xml;base64,' . base64_encode(file_get_contents($logoPath));
                break;
            }
        }

        return compact(
            'student',
            'subjects',
            'exams',
            'matrix',
            'examSummaries',
            'subjectSummaries',
            'grandTotalObtained',
            'grandTotalMax',
            'grandPercentage',
            'overallGrade',
            'overallResult',
            'siblings',
            'prevStudent',
            'nextStudent',
            'schoolName',
            'schoolShortName',
            'logoDataUri'
        );
    }
}
