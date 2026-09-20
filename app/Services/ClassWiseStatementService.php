<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\Mark;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Setting;
use App\Models\Student;
use App\Models\StudentSubject;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ClassWiseStatementService
{
    public function __construct(
        protected StudentSubjectService $studentSubjectService,
        protected ResultCalculationService $resultCalculationService
    ) {}

    /**
     * Build the complete data structure for the Class Wise Statement.
     *
     * @return array<string, mixed>
     */
    public function getStatementData(int $examId, int $classId, ?int $sectionId = null): array
    {
        $exam = Exam::with('academicYear')->findOrFail($examId);
        $class = SchoolClass::with('sections')->findOrFail($classId);
        $section = $sectionId ? Section::withTrashed()->where('class_id', $class->id)->find($sectionId) : null;

        // 1. Fetch Students
        $students = $this->fetchStatementStudents($class, $section, $exam->academic_year_id);

        // 2. Fetch Subjects
        $subjects = $this->fetchStatementSubjects($class, $section, $exam, $students);

        // 3. Build Column Definitions
        $columns = $this->buildStatementColumns($class, $section, $subjects);

        // 4. Fetch Marks & Evaluate Student Rows
        $evaluation = $this->evaluateStudentsForStatement($students, $columns, $exam->id);
        $studentRows = $evaluation['student_rows'];
        $columnStats = $evaluation['column_stats'];
        $appearedCount = $evaluation['appeared_count'];
        $passedCount = $evaluation['passed_count'];
        $failedCount = $evaluation['failed_count'];
        $pendingCount = $evaluation['pending_count'];
        $allClassPercentages = $evaluation['all_class_percentages'];
        $allClassObtained = $evaluation['all_class_obtained'];

        // 5. Calculate Rankings
        $this->calculateStudentRankings($studentRows);

        // 6. Calculate Final Column Statistics
        $finalColumnStats = $this->computeFinalColumnStats($columns, $columnStats);

        // 7. Overall Analytics
        $analytics = $this->computeOverallAnalytics(
            $studentRows,
            $appearedCount,
            $passedCount,
            $failedCount,
            $pendingCount,
            $allClassPercentages,
            $allClassObtained
        );

        return [
            'school_name' => Setting::get('school_name', config('app.name', 'RUIHSS POONCH')),
            'school_short_name' => Setting::get('school_short_name', 'RUIHSS'),
            'logo_data_uri' => $this->resolveLogoDataUri(),
            'exam' => $exam,
            'class' => $class,
            'section' => $section,
            'section_title' => $section ? $section->name : 'All Sections',
            'academic_year' => $exam->academicYear?->name ?? '',
            'columns' => $columns,
            'students' => $studentRows,
            'column_stats' => $finalColumnStats,
            'analytics' => $analytics,
            'generated_at' => now()->timezone('Asia/Kolkata')->format('d M Y, h:i A'),
        ];
    }

    /**
     * Fetch active students for this statement broadsheet.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Student>
     */
    protected function fetchStatementStudents(SchoolClass $class, ?Section $section, ?int $academicYearId): \Illuminate\Database\Eloquent\Collection
    {
        $studentsQuery = Student::with(['section', 'studentSubjects.subject'])
            ->where('class_id', $class->id)
            ->where('status', 'active');

        if ($academicYearId) {
            $studentsQuery->where('academic_year_id', $academicYearId);
        }

        if ($section) {
            $studentsQuery->where('section_id', $section->id);
        }

        $students = $studentsQuery->orderBy('section_id', 'asc')
            ->orderBy('roll_no', 'asc')
            ->get();

        if ($students->isEmpty()) {
            $fallbackQuery = Student::with(['section', 'studentSubjects.subject'])
                ->where('class_id', $class->id)
                ->where('status', 'active');
            if ($section) {
                $fallbackQuery->where('section_id', $section->id);
            }
            $students = $fallbackQuery->orderBy('section_id', 'asc')
                ->orderBy('roll_no', 'asc')
                ->get();
        }

        return $students;
    }

    /**
     * Fetch relevant subjects for the class/section statement.
     *
     * @param \Illuminate\Database\Eloquent\Collection<int, Student> $students
     * @return Collection<int, Subject>
     */
    protected function fetchStatementSubjects(SchoolClass $class, ?Section $section, Exam $exam, \Illuminate\Database\Eloquent\Collection $students): Collection
    {
        /** @var Collection<int, Subject> $subjects */
        $subjects = $class->subjects()
            ->where('subjects.status', 'active')
            ->orderBy('subjects.display_order', 'asc')
            ->orderBy('subjects.name', 'asc')
            ->get();

        if ($subjects->isEmpty() && $exam->academic_year_id) {
            $subjects = Subject::where('academic_year_id', $exam->academic_year_id)
                ->where('status', 'active')
                ->orderBy('display_order', 'asc')
                ->orderBy('name', 'asc')
                ->get();
        }

        if ($subjects->isEmpty()) {
            $subjects = Subject::where('status', 'active')
                ->orderBy('display_order', 'asc')
                ->orderBy('name', 'asc')
                ->get();
        }

        // Check if students have marks in any additional subjects not yet in the list
        if ($students->isNotEmpty()) {
            $additionalSubjectIds = Mark::where('exam_id', $exam->id)
                ->whereIn('student_id', $students->pluck('id'))
                ->distinct()
                ->pluck('subject_id')
                ->diff($subjects->pluck('id'));

            if ($additionalSubjectIds->isNotEmpty()) {
                $extraSubjects = Subject::whereIn('id', $additionalSubjectIds)
                    ->orderBy('display_order', 'asc')
                    ->orderBy('name', 'asc')
                    ->get();
                $subjects = $subjects->concat($extraSubjects)->sortBy('display_order')->values();
            }
        }

        // When a specific section is set, filter and isolate section-specific subjects
        if ($section) {
            $subjects = $this->filterSubjectsForSection($class, $section, $exam, $students, $subjects);
        }

        return $subjects;
    }

    /**
     * Filter and supplement subjects for a specific section and stream.
     *
     * @param \Illuminate\Database\Eloquent\Collection<int, Student> $students
     * @param Collection<int, Subject> $subjects
     * @return Collection<int, Subject>
     */
    protected function filterSubjectsForSection(SchoolClass $class, Section $section, Exam $exam, \Illuminate\Database\Eloquent\Collection $students, Collection $subjects): Collection
    {
        $studentIds = $students->pluck('id')->toArray();

        // 0. Directly assigned subjects for this section
        $assignedSectionSubjectIds = $section->subjects()
            ->where('subjects.status', 'active')
            ->pluck('subjects.id')
            ->toArray();

        // 1. Subjects assigned in TeacherAssignment
        $teacherSubjectIds = TeacherAssignment::where('class_id', $class->id)
            ->where('section_id', $section->id)
            ->where('status', 'active')
            ->whereHas('subject', fn ($q) => $q->where('status', 'active'))
            ->pluck('subject_id')
            ->toArray();

        // 2. Individually allocated subjects
        $allocatedSubjectIds = !empty($studentIds)
            ? StudentSubject::whereIn('student_id', $studentIds)
                ->where('status', 'active')
                ->whereHas('subject', fn ($q) => $q->where('status', 'active'))
                ->pluck('subject_id')
                ->toArray()
            : [];

        // 3. Subjects where marks are recorded
        $markSubjectIds = !empty($studentIds)
            ? Mark::where('exam_id', $exam->id)
                ->whereIn('student_id', $studentIds)
                ->whereHas('subject', fn ($q) => $q->where('status', 'active'))
                ->pluck('subject_id')
                ->toArray()
            : [];

        $sectionSubjectIds = array_unique(array_filter(array_merge($assignedSectionSubjectIds, $teacherSubjectIds, $allocatedSubjectIds, $markSubjectIds)));

        if (!empty($sectionSubjectIds)) {
            $missingIds = array_diff($sectionSubjectIds, $subjects->pluck('id')->toArray());
            if (!empty($missingIds)) {
                $missingSubjects = Subject::whereIn('id', $missingIds)
                    ->where('status', 'active')
                    ->get();
                $subjects = $subjects->concat($missingSubjects);
            }
            $subjects = $subjects->whereIn('id', $sectionSubjectIds)->sortBy('display_order')->values();
        }

        // Stream-specific filtering for Higher Secondary (11th & 12th)
        if ($class->allowsIndividualSubjectAllocation()) {
            $sectionName = strtolower(trim((string) $section->name));
            $isHumanities = str_contains($sectionName, 'humanities') || str_contains($sectionName, 'arts');
            $isScience = str_contains($sectionName, 'science');

            if ($isHumanities) {
                $subjects = $subjects->filter(function ($sub) {
                    return !preg_match('/\b(biology|bio|physics|chemistry)\b/i', strtolower($sub->name));
                })->values();
            } elseif ($isScience) {
                $subjects = $subjects->filter(function ($sub) {
                    return !preg_match('/\b(history|political science|civics|education)\b/i', strtolower($sub->name));
                })->values();
            }
        }

        return $subjects;
    }

    /**
     * Evaluate students across statement columns and calculate marks and totals.
     *
     * @param \Illuminate\Database\Eloquent\Collection<int, Student> $students
     */
    protected function evaluateStudentsForStatement(\Illuminate\Database\Eloquent\Collection $students, array $columns, int $examId): array
    {
        $allReferencedSubjectIds = [];
        foreach ($columns as $col) {
            foreach ($col['subject_ids'] as $sid) {
                $allReferencedSubjectIds[] = $sid;
            }
        }
        $allReferencedSubjectIds = array_unique($allReferencedSubjectIds);

        $marks = $students->isNotEmpty() && !empty($allReferencedSubjectIds)
            ? Mark::where('exam_id', $examId)
                ->whereIn('student_id', $students->pluck('id'))
                ->whereIn('subject_id', $allReferencedSubjectIds)
                ->get()
                ->groupBy('student_id')
            : collect();

        $studentRows = [];
        $columnStats = [];

        foreach ($columns as $colKey => $col) {
            $columnStats[$colKey] = [
                'column' => $col,
                'total_appeared' => 0,
                'total_passed' => 0,
                'total_marks_sum' => 0,
                'highest_mark' => null,
            ];
        }

        $allClassPercentages = [];
        $allClassObtained = [];
        $passedCount = 0;
        $failedCount = 0;
        $pendingCount = 0;
        $appearedCount = 0;

        /** @var Student $student */
        foreach ($students as $student) {
            $studentMarks = $marks->get($student->id, collect())->keyBy('subject_id');
            $rowColumns = [];
            $totalObtained = 0;
            $totalMax = 0;
            $allPassed = true;
            $hasMissing = false;
            $hasAnyEnteredMark = false;

            $studentSectionName = strtolower(trim((string) ($student->section?->name ?? '')));
            $allocatedSubjectIds = $student->studentSubjects->where('status', 'active')->pluck('subject_id')->toArray();

            foreach ($columns as $colKey => $col) {
                $colResult = $this->evaluateStudentColumn(
                    $student,
                    $col,
                    $studentMarks,
                    $studentSectionName,
                    $allocatedSubjectIds
                );

                $rowColumns[$colKey] = $colResult;

                if (!$colResult['is_applicable']) {
                    continue;
                }

                $totalMax += $colResult['max_marks'];

                if ($colResult['status'] === 'absent') {
                    $hasAnyEnteredMark = true;
                    $allPassed = false;
                    $columnStats[$colKey]['total_appeared']++;
                } elseif ($colResult['status'] === 'entered') {
                    $hasAnyEnteredMark = true;
                    $numericMark = $colResult['marks'];
                    $isPassed = $colResult['is_passed'];

                    if (!$isPassed) {
                        $allPassed = false;
                    }

                    $totalObtained += $numericMark;
                    $columnStats[$colKey]['total_appeared']++;
                    if ($isPassed) {
                        $columnStats[$colKey]['total_passed']++;
                    }
                    $columnStats[$colKey]['total_marks_sum'] += $numericMark;

                    if ($columnStats[$colKey]['highest_mark'] === null || $numericMark > $columnStats[$colKey]['highest_mark']) {
                        $columnStats[$colKey]['highest_mark'] = $numericMark;
                    }
                } elseif ($colResult['status'] === 'pending') {
                    $hasMissing = true;
                    $allPassed = false;
                }
            }

            $percentage = $totalMax > 0 ? round(($totalObtained / $totalMax) * 100, 2) : 0;
            $grade = $hasAnyEnteredMark ? $this->resultCalculationService->getGradeFromPercentage($percentage) : '—';

            if (!$hasAnyEnteredMark) {
                $resultStatus = 'PENDING';
                $pendingCount++;
            } elseif ($allPassed && !$hasMissing) {
                $resultStatus = 'PASS';
                $passedCount++;
                $appearedCount++;
                $allClassPercentages[] = $percentage;
                $allClassObtained[] = $totalObtained;
            } elseif ($hasMissing) {
                $resultStatus = 'PENDING';
                $pendingCount++;
                $appearedCount++;
            } else {
                $resultStatus = 'FAIL';
                $failedCount++;
                $appearedCount++;
                $allClassPercentages[] = $percentage;
                $allClassObtained[] = $totalObtained;
            }

            $studentRows[] = [
                'student' => $student,
                'student_id' => $student->student_id,
                'student_name' => $student->name,
                'roll_no' => $student->roll_no,
                'section_name' => $student->section?->name ?? '—',
                'columns' => $rowColumns,
                'total_obtained' => $totalObtained,
                'total_max' => $totalMax,
                'percentage' => $percentage,
                'grade' => $grade,
                'result' => $resultStatus,
                'has_appeared' => $hasAnyEnteredMark,
                'rank' => null,
            ];
        }

        return [
            'student_rows' => $studentRows,
            'column_stats' => $columnStats,
            'appeared_count' => $appearedCount,
            'passed_count' => $passedCount,
            'failed_count' => $failedCount,
            'pending_count' => $pendingCount,
            'all_class_percentages' => $allClassPercentages,
            'all_class_obtained' => $allClassObtained,
        ];
    }

    /**
     * Calculate Rankings among appeared students.
     */
    protected function calculateStudentRankings(array &$studentRows): void
    {
        $rankedIndices = collect($studentRows)
            ->filter(fn ($row) => $row['has_appeared'])
            ->sortByDesc(fn ($row) => $row['percentage'] * 10000 + $row['total_obtained'])
            ->keys()
            ->values();

        $currentRank = 1;
        $prevPercentage = null;
        $prevObtained = null;

        foreach ($rankedIndices as $pos => $index) {
            $row = $studentRows[$index];
            if ($prevPercentage !== null && ($row['percentage'] != $prevPercentage || $row['total_obtained'] != $prevObtained)) {
                $currentRank = $pos + 1;
            }
            $studentRows[$index]['rank'] = $currentRank;
            $prevPercentage = $row['percentage'];
            $prevObtained = $row['total_obtained'];
        }
    }

    /**
     * Calculate Final Column Statistics.
     */
    protected function computeFinalColumnStats(array $columns, array $columnStats): array
    {
        $finalColumnStats = [];
        foreach ($columns as $colKey => $col) {
            $stat = $columnStats[$colKey];
            $app = $stat['total_appeared'];
            $pass = $stat['total_passed'];
            $passRate = $app > 0 ? round(($pass / $app) * 100, 1) : 0;
            $avgMark = $app > 0 ? round($stat['total_marks_sum'] / $app, 1) : 0;

            $finalColumnStats[$colKey] = [
                'column_key' => $colKey,
                'title' => $col['title'],
                'maximum_marks' => $col['maximum_marks'],
                'pass_marks' => $col['pass_marks'],
                'appeared' => $app,
                'passed' => $pass,
                'pass_rate' => $passRate,
                'average' => $avgMark,
                'highest' => $stat['highest_mark'] ?? 0,
            ];
        }

        return $finalColumnStats;
    }

    /**
     * Compute Overall Class Analytics.
     */
    protected function computeOverallAnalytics(
        array $studentRows,
        int $appearedCount,
        int $passedCount,
        int $failedCount,
        int $pendingCount,
        array $allClassPercentages,
        array $allClassObtained
    ): array {
        $totalEnrolled = count($studentRows);
        $classPassRate = $appearedCount > 0 ? round(($passedCount / $appearedCount) * 100, 1) : 0;
        $classAveragePercentage = count($allClassPercentages) > 0 ? round(array_sum($allClassPercentages) / count($allClassPercentages), 1) : 0;
        $classAverageObtained = count($allClassObtained) > 0 ? round(array_sum($allClassObtained) / count($allClassObtained), 1) : 0;

        $topScorer = collect($studentRows)
            ->filter(fn ($r) => $r['has_appeared'] && $r['result'] === 'PASS')
            ->sortByDesc('percentage')
            ->first();

        if (!$topScorer) {
            $topScorer = collect($studentRows)
                ->filter(fn ($r) => $r['has_appeared'])
                ->sortByDesc('percentage')
                ->first();
        }

        return [
            'total_enrolled' => $totalEnrolled,
            'appeared' => $appearedCount,
            'passed' => $passedCount,
            'failed' => $failedCount,
            'pending' => $pendingCount,
            'pass_rate' => $classPassRate,
            'average_percentage' => $classAveragePercentage,
            'average_obtained' => $classAverageObtained,
            'top_scorer' => $topScorer,
        ];
    }

    /**
     * Resolve School Logo as Data URI.
     */
    protected function resolveLogoDataUri(): ?string
    {
        $logoCandidates = [];
        try { $logoCandidates[] = public_path('logo.svg'); } catch (\Throwable) {}
        $logoCandidates[] = base_path('public/logo.svg');
        $logoCandidates[] = dirname(base_path()) . '/public_html/logo.svg';

        foreach ($logoCandidates as $logoPath) {
            if ($logoPath && file_exists($logoPath)) {
                return 'data:image/svg+xml;base64,' . base64_encode(file_get_contents($logoPath));
            }
        }

        return null;
    }

    /**
     * Build the columns for the statement broadsheet.
     * In 11th and 12th class, merges optional subject pairs into a single column:
     * - For Humanities: (Education / Mathematics) and (EVS / Islamic)
     * - For Science: (Biology / Mathematics)
     */
    protected function buildStatementColumns(SchoolClass $class, ?Section $section, Collection $subjects): array
    {
        $isHigherSecondary = $class->allowsIndividualSubjectAllocation();

        // For classes other than 11th & 12th: all subjects are standard single columns
        if (!$isHigherSecondary) {
            $cols = [];
            foreach ($subjects as $sub) {
                $cols['sub_' . $sub->id] = [
                    'key' => 'sub_' . $sub->id,
                    'title' => $sub->name,
                    'short_title' => $sub->name,
                    'sub_header' => ((int) $sub->maximum_marks) . '/' . ((int) $sub->pass_marks),
                    'is_optional_group' => false,
                    'stream' => null,
                    'maximum_marks' => (float) $sub->maximum_marks,
                    'pass_marks' => (float) $sub->pass_marks,
                    'subject_ids' => [$sub->id],
                    'subjects' => [$sub],
                ];
            }
            return $cols;
        }

        // Higher Secondary (11th & 12th) Optional Grouping
        $subEdu = $this->findSubjectByKeywords($subjects, ['education', 'edu']);
        $subMath = $this->findSubjectByKeywords($subjects, ['mathematics', 'math']);
        $subEvs = $this->findSubjectByKeywords($subjects, ['evs', 'environmental']);
        $subIslamic = $this->findSubjectByKeywords($subjects, ['islamic', 'islam', 'isl']);
        $subBio = $this->findSubjectByKeywords($subjects, ['biology', 'bio']);

        $groupedSubjectIds = array_filter([
            $subEdu?->id,
            $subMath?->id,
            $subEvs?->id,
            $subIslamic?->id,
            $subBio?->id,
        ]);

        $sectionName = $section ? strtolower(trim((string) $section->name)) : 'all';
        $isHumanitiesSection = str_contains($sectionName, 'humanities');
        $isScienceSection = str_contains($sectionName, 'science');
        $isAllSections = ($sectionName === 'all');

        $cols = [];

        // 1. Compulsory / Standard single subjects (e.g. English, Urdu, etc. that are not in the optional pairs)
        foreach ($subjects as $sub) {
            if (in_array($sub->id, $groupedSubjectIds)) {
                continue;
            }
            $cols['sub_' . $sub->id] = [
                'key' => 'sub_' . $sub->id,
                'title' => $sub->name,
                'short_title' => $sub->name,
                'sub_header' => ((int) $sub->maximum_marks) . '/' . ((int) $sub->pass_marks),
                'is_optional_group' => false,
                'stream' => null,
                'maximum_marks' => (float) $sub->maximum_marks,
                'pass_marks' => (float) $sub->pass_marks,
                'subject_ids' => [$sub->id],
                'subjects' => [$sub],
            ];
        }

        // 2. Science Optional: Biology / Mathematics
        if (($isScienceSection || $isAllSections) && ($subBio || $subMath)) {
            $pairSubs = array_filter([$subBio, $subMath]);
            $title = ($subBio ? $subBio->name : 'Biology') . ' / ' . ($subMath ? $subMath->name : 'Mathematics');
            $maxMarks = (float) ($subBio?->maximum_marks ?? $subMath?->maximum_marks ?? 100);
            $passMarks = (float) ($subBio?->pass_marks ?? $subMath?->pass_marks ?? 33);

            $subHeader = ($subBio && $subMath && (int) $subBio->maximum_marks !== (int) $subMath->maximum_marks)
                ? ((int) $subBio->maximum_marks . ' | ' . (int) $subMath->maximum_marks)
                : (((int) $maxMarks) . '/' . ((int) $passMarks));

            $cols['opt_science_bio_math'] = [
                'key' => 'opt_science_bio_math',
                'title' => $title,
                'short_title' => 'Biology / Math',
                'sub_header' => $subHeader,
                'is_optional_group' => true,
                'stream' => 'science',
                'maximum_marks' => $maxMarks,
                'pass_marks' => $passMarks,
                'subject_ids' => array_map(fn ($s) => $s->id, $pairSubs),
                'subjects' => $pairSubs,
            ];
        }

        // 3. Humanities Optional 1: Education / Mathematics
        if (($isHumanitiesSection || $isAllSections) && ($subEdu || $subMath)) {
            $pairSubs = array_filter([$subEdu, $subMath]);
            $title = ($subEdu ? $subEdu->name : 'Education') . ' / ' . ($subMath ? $subMath->name : 'Mathematics');
            $maxMarks = (float) ($subEdu?->maximum_marks ?? $subMath?->maximum_marks ?? 100);
            $passMarks = (float) ($subEdu?->pass_marks ?? $subMath?->pass_marks ?? 33);

            $cols['opt_humanities_edu_math'] = [
                'key' => 'opt_humanities_edu_math',
                'title' => $title,
                'short_title' => 'Edu / Math',
                'sub_header' => ((int) $maxMarks) . '/' . ((int) $passMarks),
                'is_optional_group' => true,
                'stream' => 'humanities',
                'maximum_marks' => $maxMarks,
                'pass_marks' => $passMarks,
                'subject_ids' => array_map(fn ($s) => $s->id, $pairSubs),
                'subjects' => $pairSubs,
            ];
        }

        // 4. Optional 2: EVS / Islamic (Applicable to Humanities, Science, or All Sections)
        if (($isHumanitiesSection || $isScienceSection || $isAllSections) && ($subEvs || $subIslamic)) {
            $pairSubs = array_filter([$subEvs, $subIslamic]);
            $title = ($subEvs ? $subEvs->name : 'EVS') . ' / ' . ($subIslamic ? $subIslamic->name : 'Islamic');
            $maxMarks = (float) ($subEvs?->maximum_marks ?? $subIslamic?->maximum_marks ?? 100);
            $passMarks = (float) ($subEvs?->pass_marks ?? $subIslamic?->pass_marks ?? 33);

            $subHeader = ($subEvs && $subIslamic && (int) $subEvs->maximum_marks !== (int) $subIslamic->maximum_marks)
                ? ((int) $subEvs->maximum_marks . ' | ' . (int) $subIslamic->maximum_marks)
                : (((int) $maxMarks) . '/' . ((int) $passMarks));

            $cols['opt_humanities_evs_islamic'] = [
                'key' => 'opt_humanities_evs_islamic',
                'title' => $title,
                'short_title' => 'EVS / Islamic',
                'sub_header' => $subHeader,
                'is_optional_group' => true,
                'stream' => null,
                'maximum_marks' => $maxMarks,
                'pass_marks' => $passMarks,
                'subject_ids' => array_map(fn ($s) => $s->id, $pairSubs),
                'subjects' => $pairSubs,
            ];
        }

        return $cols;
    }

    /**
     * Evaluate a student's marks and status for a single statement column.
     */
    protected function evaluateStudentColumn(
        Student $student,
        array $column,
        Collection $studentMarks,
        string $studentSectionName,
        array $allocatedSubjectIds
    ): array {
        $maxMarks = $column['maximum_marks'];
        $passMarks = $column['pass_marks'];

        // CASE 1: Standard Single Subject Column
        if (!$column['is_optional_group']) {
            $subject = $column['subjects'][0];
            $isElectiveClass = $student->allowsIndividualSubjectAllocation();

            $isApplicable = true;
            if ($isElectiveClass && !empty($allocatedSubjectIds)) {
                $isApplicable = in_array($subject->id, $allocatedSubjectIds);
            }

            if (!$isApplicable) {
                return [
                    'is_applicable' => false,
                    'display' => '— (N/A)',
                    'tag' => null,
                    'marks' => null,
                    'max_marks' => 0,
                    'is_absent' => false,
                    'is_passed' => null,
                    'status' => 'not_applicable',
                    'subject_name' => null,
                ];
            }

            $markRecord = $studentMarks->get($subject->id);

            if (!$markRecord) {
                return [
                    'is_applicable' => true,
                    'display' => '—',
                    'tag' => null,
                    'marks' => null,
                    'max_marks' => $maxMarks,
                    'is_absent' => false,
                    'is_passed' => false,
                    'status' => 'pending',
                    'subject_name' => $subject->name,
                ];
            }

            $isAbsent = (bool) $markRecord->is_absent;
            $numericMark = $isAbsent ? null : ($markRecord->marks !== null ? (float) $markRecord->marks : null);

            if ($isAbsent) {
                return [
                    'is_applicable' => true,
                    'display' => 'AB',
                    'tag' => null,
                    'marks' => null,
                    'max_marks' => $maxMarks,
                    'is_absent' => true,
                    'is_passed' => false,
                    'status' => 'absent',
                    'subject_name' => $subject->name,
                ];
            }

            if ($numericMark !== null) {
                $isPassed = ($numericMark >= $passMarks);
                $percentage = $maxMarks > 0 ? round(($numericMark / $maxMarks) * 100, 1) : null;
                $grade = $percentage !== null ? $this->resultCalculationService->getGradeFromPercentage($percentage) : '—';
                return [
                    'is_applicable' => true,
                    'display' => (string) $numericMark,
                    'tag' => null,
                    'marks' => $numericMark,
                    'max_marks' => $maxMarks,
                    'percentage' => $percentage,
                    'grade' => $grade,
                    'is_absent' => false,
                    'is_passed' => $isPassed,
                    'status' => 'entered',
                    'subject_name' => $subject->name,
                ];
            }

            return [
                'is_applicable' => true,
                'display' => '—',
                'tag' => null,
                'marks' => null,
                'max_marks' => $maxMarks,
                'is_absent' => false,
                'is_passed' => false,
                'status' => 'pending',
                'subject_name' => $subject->name,
            ];
        }

        // CASE 2: Merged Optional Subject Column (Higher Secondary)
        $stream = $column['stream']; // 'humanities' or 'science'

        // Check stream applicability
        $isStreamMatch = true;
        if ($stream === 'science') {
            $isStreamMatch = str_contains($studentSectionName, 'science');
        } elseif ($stream === 'humanities') {
            $isStreamMatch = str_contains($studentSectionName, 'humanities');
        }

        if (!$isStreamMatch) {
            return [
                'is_applicable' => false,
                'display' => '— (N/A)',
                'tag' => null,
                'marks' => null,
                'max_marks' => 0,
                'is_absent' => false,
                'is_passed' => null,
                'status' => 'not_applicable',
                'subject_name' => null,
            ];
        }

        // Determine which subject the student chose among the paired subjects
        $pairedSubjects = $column['subjects'];
        $chosenSubject = null;

        // 1. Check allocated subjects
        if (!empty($allocatedSubjectIds)) {
            foreach ($pairedSubjects as $psub) {
                if (in_array($psub->id, $allocatedSubjectIds)) {
                    $chosenSubject = $psub;
                    break;
                }
            }
        }

        // 2. If no explicit allocation found, check if mark is recorded in either subject
        if (!$chosenSubject) {
            foreach ($pairedSubjects as $psub) {
                if ($studentMarks->has($psub->id)) {
                    $chosenSubject = $psub;
                    break;
                }
            }
        }

        // If no subject chose yet, but the student belongs to this stream:
        if (!$chosenSubject) {
            return [
                'is_applicable' => true,
                'display' => '—',
                'tag' => null,
                'marks' => null,
                'max_marks' => $maxMarks,
                'is_absent' => false,
                'is_passed' => false,
                'status' => 'pending',
                'subject_name' => null,
            ];
        }

        $tag = $this->getShortSubjectTag($chosenSubject->name);
        $markRecord = $studentMarks->get($chosenSubject->id);

        if (!$markRecord) {
            return [
                'is_applicable' => true,
                'display' => '—',
                'tag' => $tag,
                'marks' => null,
                'max_marks' => (float) $chosenSubject->maximum_marks,
                'is_absent' => false,
                'is_passed' => false,
                'status' => 'pending',
                'subject_name' => $chosenSubject->name,
            ];
        }

        $isAbsent = (bool) $markRecord->is_absent;
        $numericMark = $isAbsent ? null : ($markRecord->marks !== null ? (float) $markRecord->marks : null);
        $subPassMarks = (float) $chosenSubject->pass_marks;
        $subMaxMarks = (float) $chosenSubject->maximum_marks;

        if ($isAbsent) {
            return [
                'is_applicable' => true,
                'display' => 'AB',
                'tag' => $tag,
                'marks' => null,
                'max_marks' => $subMaxMarks,
                'is_absent' => true,
                'is_passed' => false,
                'status' => 'absent',
                'subject_name' => $chosenSubject->name,
            ];
        }

        if ($numericMark !== null) {
            $isPassed = ($numericMark >= $subPassMarks);
            $percentage = $subMaxMarks > 0 ? round(($numericMark / $subMaxMarks) * 100, 1) : null;
            $grade = $percentage !== null ? $this->resultCalculationService->getGradeFromPercentage($percentage) : '—';
            return [
                'is_applicable' => true,
                'display' => (string) $numericMark,
                'tag' => $tag,
                'marks' => $numericMark,
                'max_marks' => $subMaxMarks,
                'percentage' => $percentage,
                'grade' => $grade,
                'is_absent' => false,
                'is_passed' => $isPassed,
                'status' => 'entered',
                'subject_name' => $chosenSubject->name,
            ];
        }

        return [
            'is_applicable' => true,
            'display' => '—',
            'tag' => $tag,
            'marks' => null,
            'max_marks' => $subMaxMarks,
            'is_absent' => false,
            'is_passed' => false,
            'status' => 'pending',
            'subject_name' => $chosenSubject->name,
        ];
    }

    /**
     * Get a compact 3-4 character tag for optional subjects to differentiate which one the student selected.
     */
    protected function getShortSubjectTag(string $name): string
    {
        $lower = strtolower($name);
        if (str_contains($lower, 'education')) return 'Edu';
        if (str_contains($lower, 'mathematics') || str_contains($lower, 'math')) return 'Math';
        if (str_contains($lower, 'biology') || str_contains($lower, 'bio')) return 'Bio';
        if (str_contains($lower, 'evs') || str_contains($lower, 'environmental')) return 'EVS';
        if (str_contains($lower, 'islamic') || str_contains($lower, 'islam')) return 'Isl';

        return Str::limit($name, 4, '');
    }

    /**
     * Find a subject from a collection by keyword matching on name or code.
     */
    protected function findSubjectByKeywords(Collection $subjects, array $keywords): ?Subject
    {
        foreach ($subjects as $subject) {
            $name = strtolower(trim((string) $subject->name));
            $code = strtolower(trim((string) $subject->code));
            foreach ($keywords as $kw) {
                $kw = strtolower($kw);
                if (str_contains($name, $kw) || str_contains($code, $kw)) {
                    return $subject;
                }
            }
        }

        return null;
    }

    /**
     * Render PDF content in landscape A4 mode.
     */
    public function renderPdfContent(int $examId, int $classId, ?int $sectionId = null): string
    {
        $data = $this->getStatementData($examId, $classId, $sectionId);

        $pdf = Pdf::loadView('results.pdf.class-wise-statement', $data)
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => false,
                'defaultFont'          => 'sans-serif',
            ], true);

        return $pdf->output();
    }

    /**
     * Build standardized filename for exports.
     */
    public function makeFileName(Exam $exam, SchoolClass $class, ?Section $section = null, string $extension = 'pdf'): string
    {
        $safeExam = Str::slug($exam->exam_name, '_');
        $safeClass = Str::slug($class->name, '_');
        $safeSection = $section ? Str::slug($section->name, '_') : 'All_Sections';

        return "Class_Statement_{$safeExam}_{$safeClass}_{$safeSection}.{$extension}";
    }

    /**
     * Generate an Excel spreadsheet representing the Class Wise Statement.
     */
    public function renderExcelSpreadsheet(int $examId, int $classId, ?int $sectionId = null): Spreadsheet
    {
        $data = $this->getStatementData($examId, $classId, $sectionId);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Class Statement');

        // Columns: Roll No (A), Student ID (B), Student Name (C), Section (D)
        // Then statement columns: Col 5, 6, 7...
        // End cols: Total Marks, Max Marks, Percentage, Grade, Rank
        $numCols = count($data['columns']);
        $totalColsCount = 4 + $numCols + 5;
        $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalColsCount);

        // 1. Institutional Title
        $sheet->setCellValue('A1', strtoupper($data['school_name']));
        $sheet->mergeCells("A1:{$lastColLetter}1");
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF1E3A8A'));
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // 2. Subtitle
        $sheet->setCellValue('A2', 'CLASS WISE STATEMENT OF MARKS & EVALUATION');
        $sheet->mergeCells("A2:{$lastColLetter}2");
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // 3. Metadata Bar
        $metaRow = 4;
        $sheet->setCellValue('A' . $metaRow, 'Examination: ' . $data['exam']->exam_name);
        $sheet->setCellValue('D' . $metaRow, 'Academic Year: ' . $data['academic_year']);
        $sheet->setCellValue('G' . $metaRow, 'Class: ' . $data['class']->name . ' (' . $data['section_title'] . ')');
        $sheet->setCellValue('K' . $metaRow, 'Generated: ' . $data['generated_at']);
        $sheet->getStyle("A{$metaRow}:{$lastColLetter}{$metaRow}")->getFont()->setBold(true);

        // 4. Analytics Bar
        $analyticsRow = 5;
        $analyticsText = sprintf(
            'Enrolled: %d | Appeared: %d | Passed: %d | Failed: %d | Pass Rate: %s%% | Class Avg: %s%%',
            $data['analytics']['total_enrolled'],
            $data['analytics']['appeared'],
            $data['analytics']['passed'],
            $data['analytics']['failed'],
            $data['analytics']['pass_rate'],
            $data['analytics']['average_percentage']
        );
        $sheet->setCellValue('A' . $analyticsRow, $analyticsText);
        $sheet->mergeCells("A{$analyticsRow}:{$lastColLetter}{$analyticsRow}");
        $sheet->getStyle("A{$analyticsRow}")->getFont()->setItalic(true)->setSize(10);

        // 5. Table Header Rows (Row 7 and 8)
        $headerRow1 = 7;
        $headerRow2 = 8;

        // Base Headers
        $sheet->setCellValue('A' . $headerRow1, 'Roll No');
        $sheet->mergeCells("A{$headerRow1}:A{$headerRow2}");

        $sheet->setCellValue('B' . $headerRow1, 'Student ID');
        $sheet->mergeCells("B{$headerRow1}:B{$headerRow2}");

        $sheet->setCellValue('C' . $headerRow1, 'Student Name');
        $sheet->mergeCells("C{$headerRow1}:C{$headerRow2}");

        $sheet->setCellValue('D' . $headerRow1, 'Section');
        $sheet->mergeCells("D{$headerRow1}:D{$headerRow2}");

        $currCol = 5;
        foreach ($data['columns'] as $col) {
            $col1 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currCol);
            $col2 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currCol + 1);
            $col3 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currCol + 2);
            // Subject title spanning 3 columns
            $sheet->setCellValue($col1 . $headerRow1, $col['title']);
            $sheet->mergeCells("{$col1}{$headerRow1}:{$col3}{$headerRow1}");
            // Sub-headers row 2
            $sheet->setCellValue($col1 . $headerRow2, 'Marks');
            $sheet->setCellValue($col2 . $headerRow2, '%');
            $sheet->setCellValue($col3 . $headerRow2, 'Gr.');
            $currCol += 3;
        }

        // Summary Headers
        $totObtCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currCol++);
        $sheet->setCellValue($totObtCol . $headerRow1, 'Total Obtained');
        $sheet->mergeCells("{$totObtCol}{$headerRow1}:{$totObtCol}{$headerRow2}");

        $maxCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currCol++);
        $sheet->setCellValue($maxCol . $headerRow1, 'Max Marks');
        $sheet->mergeCells("{$maxCol}{$headerRow1}:{$maxCol}{$headerRow2}");

        $pctCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currCol++);
        $sheet->setCellValue($pctCol . $headerRow1, 'Percentage (%)');
        $sheet->mergeCells("{$pctCol}{$headerRow1}:{$pctCol}{$headerRow2}");

        $gradeCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currCol++);
        $sheet->setCellValue($gradeCol . $headerRow1, 'Grade');
        $sheet->mergeCells("{$gradeCol}{$headerRow1}:{$gradeCol}{$headerRow2}");

        $rankCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currCol++);
        $sheet->setCellValue($rankCol . $headerRow1, 'Rank');
        $sheet->mergeCells("{$rankCol}{$headerRow1}:{$rankCol}{$headerRow2}");

        // Style Headers
        $sheet->getStyle("A{$headerRow1}:{$lastColLetter}{$headerRow2}")->getFont()->setBold(true)->setSize(10);
        $sheet->getStyle("A{$headerRow1}:{$lastColLetter}{$headerRow2}")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE2E8F0');
        $sheet->getStyle("A{$headerRow1}:{$lastColLetter}{$headerRow2}")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        // 6. Data Rows
        $dataStartRow = 9;
        $currentRow = $dataStartRow;

        foreach ($data['students'] as $st) {
            $sheet->setCellValueExplicit('A' . $currentRow, $st['roll_no'], DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('B' . $currentRow, (string) $st['student_id'], DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('C' . $currentRow, (string) $st['student_name'], DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('D' . $currentRow, (string) $st['section_name'], DataType::TYPE_STRING);

            $colIdx = 5;
            foreach ($data['columns'] as $colKey => $col) {
                $marksCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
                $pctCol2  = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 1);
                $grCol    = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 2);
                $colData = $st['columns'][$colKey] ?? null;

                // Marks
                $marksVal = '—';
                $pctVal   = '—';
                $grVal    = '—';
                if ($colData && $colData['status'] === 'entered') {
                    $marksVal = $colData['display'];
                    if (!empty($colData['percentage'])) {
                        $pctVal = $colData['percentage'];
                    }
                    if (!empty($colData['grade']) && $colData['grade'] !== '—') {
                        $grVal = $colData['grade'];
                    }
                    if (!empty($colData['tag'])) {
                        $grVal .= ($grVal !== '—' ? ' ' : '') . '[' . $colData['tag'] . ']';
                    }
                } elseif ($colData && $colData['status'] === 'absent') {
                    $marksVal = 'AB';
                    $pctVal   = 'AB';
                    $grVal    = 'AB';
                }

                $sheet->setCellValue($marksCol . $currentRow, $marksVal);
                $sheet->getStyle($marksCol . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->setCellValue($pctCol2 . $currentRow, $pctVal);
                $sheet->getStyle($pctCol2 . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->setCellValue($grCol . $currentRow, $grVal);
                $sheet->getStyle($grCol . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $colIdx += 3;
            }

            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx++) . $currentRow, $st['has_appeared'] ? $st['total_obtained'] : '—');
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx++) . $currentRow, $st['total_max']);
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx++) . $currentRow, $st['has_appeared'] ? $st['percentage'] . '%' : '—');
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx++) . $currentRow, $st['has_appeared'] ? $st['grade'] : '—');
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx++) . $currentRow, $st['rank'] ?? '—');

            // Alignments
            $sheet->getStyle("A{$currentRow}:B{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(5) . "{$currentRow}:{$lastColLetter}{$currentRow}")
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $currentRow++;
        }

        // 7. Subject Summary Rows
        $avgRow = $currentRow;
        $sheet->setCellValue('A' . $avgRow, 'Subject Average');
        $sheet->mergeCells("A{$avgRow}:D{$avgRow}");
        $colIdx = 5;
        foreach ($data['columns'] as $colKey => $col) {
            $subCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx++);
            $sheet->setCellValue($subCol . $avgRow, $data['column_stats'][$colKey]['average'] ?? '—');
        }
        $sheet->getStyle("A{$avgRow}:{$lastColLetter}{$avgRow}")->getFont()->setBold(true);
        $sheet->getStyle("A{$avgRow}:{$lastColLetter}{$avgRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $passRow = $currentRow + 1;
        $sheet->setCellValue('A' . $passRow, 'Pass Rate (%)');
        $sheet->mergeCells("A{$passRow}:D{$passRow}");
        $colIdx = 5;
        foreach ($data['columns'] as $colKey => $col) {
            $subCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx++);
            $sheet->setCellValue($subCol . $passRow, ($data['column_stats'][$colKey]['pass_rate'] ?? 0) . '%');
        }
        $sheet->getStyle("A{$passRow}:{$lastColLetter}{$passRow}")->getFont()->setBold(true);
        $sheet->getStyle("A{$passRow}:{$lastColLetter}{$passRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Borders
        $sheet->getStyle("A{$headerRow1}:{$lastColLetter}{$passRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Auto-fit Columns
        for ($i = 1; $i <= $totalColsCount; $i++) {
            $colString = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
            $sheet->getColumnDimension($colString)->setAutoSize(true);
        }

        return $spreadsheet;
    }
}
