<?php

namespace Tests\Unit;

use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\Mark;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentSubject;
use App\Models\Subject;
use App\Services\ResultCalculationService;
use App\Services\StudentSubjectService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResultCalculationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ResultCalculationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $studentSubjectService = new StudentSubjectService();
        $this->service = new ResultCalculationService($studentSubjectService);
    }

    public function test_it_calculates_student_result_percentage_pass_fail_and_grades_accurately(): void
    {
        $academicYear = AcademicYear::create([
            'name' => '2026-27',
            'is_active' => true,
        ]);

        $class = SchoolClass::create(['name' => 'Class 10']);
        $section = Section::create(['class_id' => $class->id, 'name' => 'A']);

        $english = Subject::create([
            'name' => 'English',
            'code' => 'ENG101',
            'maximum_marks' => 100,
            'pass_marks' => 33,
            'academic_year_id' => $academicYear->id,
        ]);

        $math = Subject::create([
            'name' => 'Mathematics',
            'code' => 'MTH101',
            'maximum_marks' => 100,
            'pass_marks' => 33,
            'academic_year_id' => $academicYear->id,
        ]);

        $student = Student::create([
            'student_id' => 'ST001',
            'name' => 'Abdul Rahman',
            'dob' => '2012-04-10',
            'academic_year_id' => $academicYear->id,
            'class_id' => $class->id,
            'section_id' => $section->id,
            'roll_no' => 1,
            'status' => 'active',
        ]);

        StudentSubject::create([
            'student_id' => $student->id,
            'subject_id' => $english->id,
            'academic_year_id' => $academicYear->id,
            'status' => 'active',
        ]);

        StudentSubject::create([
            'student_id' => $student->id,
            'subject_id' => $math->id,
            'academic_year_id' => $academicYear->id,
            'status' => 'active',
        ]);

        $exam = Exam::create([
            'academic_year_id' => $academicYear->id,
            'exam_name' => 'Annual Examination 2026',
            'exam_code' => 'ANNUAL-2026',
            'status' => Exam::STATUS_PUBLISHED,
        ]);

        // English: 80, Math: 90 -> Total: 170 / 200 = 85.00% -> Grade A -> Result PASS
        Mark::create([
            'exam_id' => $exam->id,
            'academic_year_id' => $academicYear->id,
            'student_id' => $student->id,
            'subject_id' => $english->id,
            'marks' => 80,
            'status' => Mark::STATUS_VERIFIED,
        ]);

        Mark::create([
            'exam_id' => $exam->id,
            'academic_year_id' => $academicYear->id,
            'student_id' => $student->id,
            'subject_id' => $math->id,
            'marks' => 90,
            'status' => Mark::STATUS_VERIFIED,
        ]);

        $result = $this->service->calculateStudentResult($student, $exam);

        $this->assertEquals(200.0, $result['total_max_marks']);
        $this->assertEquals(170.0, $result['total_obtained_marks']);
        $this->assertEquals(85.00, $result['percentage']);
        $this->assertEquals('PASS', $result['overall_result']);
        $this->assertEquals('A', $result['overall_grade']);
        $this->assertFalse($result['has_missing_marks']);
    }

    public function test_it_marks_result_as_fail_if_a_subject_is_below_pass_marks(): void
    {
        $academicYear = AcademicYear::create(['name' => '2026-27', 'is_active' => true]);
        $class = SchoolClass::create(['name' => 'Class 10']);
        $section = Section::create(['class_id' => $class->id, 'name' => 'A']);

        $english = Subject::create([
            'name' => 'English',
            'maximum_marks' => 100,
            'pass_marks' => 33,
            'academic_year_id' => $academicYear->id,
        ]);

        $student = Student::create([
            'student_id' => 'ST002',
            'name' => 'Ameen Khan',
            'dob' => '2012-07-11',
            'academic_year_id' => $academicYear->id,
            'class_id' => $class->id,
            'section_id' => $section->id,
            'roll_no' => 2,
            'status' => 'active',
        ]);

        StudentSubject::create([
            'student_id' => $student->id,
            'subject_id' => $english->id,
            'academic_year_id' => $academicYear->id,
            'status' => 'active',
        ]);

        $exam = Exam::create([
            'academic_year_id' => $academicYear->id,
            'exam_name' => 'Annual Examination 2026',
            'exam_code' => 'ANNUAL-2026',
            'status' => Exam::STATUS_PUBLISHED,
        ]);

        // English: 25 (Fail, pass mark is 33)
        Mark::create([
            'exam_id' => $exam->id,
            'academic_year_id' => $academicYear->id,
            'student_id' => $student->id,
            'subject_id' => $english->id,
            'marks' => 25,
            'status' => Mark::STATUS_VERIFIED,
        ]);

        $result = $this->service->calculateStudentResult($student, $exam);

        $this->assertEquals('FAIL', $result['overall_result']);
    }

    public function test_it_handles_absent_status_properly(): void
    {
        $academicYear = AcademicYear::create(['name' => '2026-27', 'is_active' => true]);
        $class = SchoolClass::create(['name' => 'Class 10']);
        $section = Section::create(['class_id' => $class->id, 'name' => 'A']);

        $math = Subject::create([
            'name' => 'Mathematics',
            'maximum_marks' => 100,
            'pass_marks' => 33,
            'academic_year_id' => $academicYear->id,
        ]);

        $student = Student::create([
            'student_id' => 'ST003',
            'name' => 'Anas Ali',
            'dob' => '2012-09-15',
            'academic_year_id' => $academicYear->id,
            'class_id' => $class->id,
            'section_id' => $section->id,
            'roll_no' => 3,
            'status' => 'active',
        ]);

        StudentSubject::create([
            'student_id' => $student->id,
            'subject_id' => $math->id,
            'academic_year_id' => $academicYear->id,
            'status' => 'active',
        ]);

        $exam = Exam::create([
            'academic_year_id' => $academicYear->id,
            'exam_name' => 'Annual Examination 2026',
            'exam_code' => 'ANNUAL-2026',
            'status' => Exam::STATUS_PUBLISHED,
        ]);

        Mark::create([
            'exam_id' => $exam->id,
            'academic_year_id' => $academicYear->id,
            'student_id' => $student->id,
            'subject_id' => $math->id,
            'marks' => null,
            'is_absent' => true,
            'status' => Mark::STATUS_VERIFIED,
        ]);

        $result = $this->service->calculateStudentResult($student, $exam);

        $this->assertEquals('FAIL', $result['overall_result']);
        $this->assertTrue($result['subjects'][0]['is_absent']);
        $this->assertEquals('AB', $result['subjects'][0]['grade']);
    }
}
