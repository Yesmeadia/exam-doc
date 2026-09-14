<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\Mark;
use App\Models\Student;

class ResultCalculationService
{
    public function __construct(
        protected StudentSubjectService $studentSubjectService
    ) {}

    /**
     * Calculate comprehensive result details for a student in an exam.
     */
    public function calculateStudentResult(Student $student, Exam $exam): array
    {
        // 1. Get allocated subjects for this student (handles Class 11/12 electives properly)
        $subjects = $this->studentSubjectService->getSubjectsForStudent($student);

        // 2. Fetch marks for this student and exam
        $marks = Mark::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->whereIn('subject_id', $subjects->pluck('id'))
            ->get()
            ->keyBy('subject_id');

        $subjectResults = [];
        $totalMaxMarks = 0;
        $totalObtainedMarks = 0;
        $allPassed = true;
        $hasMissingMarks = false;

        foreach ($subjects as $subject) {
            $markRecord = $marks->get($subject->id);
            $maxMarks = (float) $subject->maximum_marks;
            $passMarks = (float) $subject->pass_marks;
            $totalMaxMarks += $maxMarks;

            if (!$markRecord) {
                $hasMissingMarks = true;
                $subjectResults[] = [
                    'subject_id' => $subject->id,
                    'subject_name' => $subject->name,
                    'subject_code' => $subject->code,
                    'maximum_marks' => $maxMarks,
                    'pass_marks' => $passMarks,
                    'marks_obtained' => null,
                    'is_absent' => false,
                    'remarks' => 'Not Entered',
                    'is_passed' => false,
                    'grade' => 'N/A',
                ];
                $allPassed = false;
                continue;
            }

            $isAbsent = (bool) $markRecord->is_absent;
            $obtained = $isAbsent ? null : ($markRecord->marks !== null ? (float) $markRecord->marks : null);

            if ($obtained !== null) {
                $totalObtainedMarks += $obtained;
            }

            $isPassed = !$isAbsent && ($obtained !== null) && ($obtained >= $passMarks);
            if (!$isPassed) {
                $allPassed = false;
            }

            $subjectPercentage = ($maxMarks > 0 && $obtained !== null) ? ($obtained / $maxMarks) * 100 : 0;
            $subjectGrade = $isAbsent ? 'AB' : $this->getGradeFromPercentage($subjectPercentage);

            $subjectResults[] = [
                'subject_id' => $subject->id,
                'subject_name' => $subject->name,
                'subject_code' => $subject->code,
                'maximum_marks' => $maxMarks,
                'pass_marks' => $passMarks,
                'marks_obtained' => $obtained,
                'is_absent' => $isAbsent,
                'remarks' => $markRecord->remarks,
                'is_passed' => $isPassed,
                'grade' => $subjectGrade,
            ];
        }

        $overallPercentage = $totalMaxMarks > 0 ? round(($totalObtainedMarks / $totalMaxMarks) * 100, 2) : 0;
        $overallGrade = $this->getGradeFromPercentage($overallPercentage);
        $overallResult = ($allPassed && !$hasMissingMarks) ? 'PASS' : 'FAIL';

        return [
            'student' => $student,
            'exam' => $exam,
            'subjects' => $subjectResults,
            'total_max_marks' => $totalMaxMarks,
            'total_obtained_marks' => $totalObtainedMarks,
            'percentage' => $overallPercentage,
            'overall_result' => $overallResult,
            'overall_grade' => $overallGrade,
            'has_missing_marks' => $hasMissingMarks,
        ];
    }

    /**
     * Configurable grading scale.
     */
    public function getGradeFromPercentage(float $percentage): string
    {
        return match (true) {
            $percentage >= 90 => 'A+',
            $percentage >= 80 => 'A',
            $percentage >= 70 => 'B+',
            $percentage >= 60 => 'B',
            $percentage >= 50 => 'C',
            $percentage >= 35 => 'D',
            default => 'F',
        };
    }
}
