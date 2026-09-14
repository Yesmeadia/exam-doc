<?php

namespace App\Services;

use App\Models\AwardRoll;
use App\Models\Exam;
use App\Models\Mark;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class AwardRollService
{
    public function __construct(
        protected StudentSubjectService $studentSubjectService
    ) {}

    /**
     * Gather structured data for Award Roll.
     * STRICT REQUIREMENT: Order students by ROLL NUMBER ASCENDING.
     * STRICT REQUIREMENT: Only students allocated to the subject appear (Class 11/12).
     */
    public function getAwardRollData(
        Exam $exam,
        SchoolClass $class,
        Section $section,
        Subject $subject
    ): array {
        // 1. Fetch teacher assignment to get assigned teacher name
        $assignment = TeacherAssignment::with('teacher')
            ->where('academic_year_id', $exam->academic_year_id)
            ->where('class_id', $class->id)
            ->where('section_id', $section->id)
            ->where('subject_id', $subject->id)
            ->first();

        $teacherName = $assignment?->teacher?->name ?? 'Not Assigned';

        // 2. Fetch eligible students strictly ordered by ROLL NUMBER ASCENDING
        $students = $this->studentSubjectService->getEligibleStudentsForSubject(
            $exam->academic_year_id,
            $class->id,
            $section->id,
            $subject->id
        )->sortBy('roll_no', SORT_NUMERIC)->values();

        // 3. Fetch marks for this exam and subject
        $marks = Mark::where('exam_id', $exam->id)
            ->where('subject_id', $subject->id)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->keyBy('student_id');

        $maxMarks = (float) $subject->maximum_marks;
        $studentRows = [];

        foreach ($students as $student) {
            $markRecord = $marks->get($student->id);
            $markDisplay = '—';
            $percentageDisplay = '—';
            $remarks = '';

            if ($markRecord) {
                if ($markRecord->is_absent) {
                    $markDisplay = 'AB';
                    $percentageDisplay = 'AB';
                } elseif ($markRecord->marks !== null) {
                    $numericMarks = (float) $markRecord->marks;
                    $markDisplay = (string) $numericMarks;
                    if ($maxMarks > 0) {
                        $pct = ($numericMarks / $maxMarks) * 100;
                        $percentageDisplay = (round($pct, 1) == round($pct, 0))
                            ? number_format($pct, 0) . '%'
                            : number_format($pct, 1) . '%';
                    }
                }
                $remarks = $markRecord->remarks ?? '';
            }

            $studentRows[] = [
                'roll_no' => $student->roll_no,
                'student_id' => $student->student_id,
                'student_name' => $student->name,
                'marks' => $markDisplay,
                'percentage' => $percentageDisplay,
                'remarks' => $remarks,
            ];
        }

        // Resolve logo safely — public_path() may throw on shared hosting when the
        // public directory is separated from the Laravel root (e.g. Hostinger).
        // We try three locations and fall back gracefully to no logo.
        $logoDataUri = null;
        $logoCandidates = [];
        try { $logoCandidates[] = public_path('logo.svg'); } catch (\Throwable) {}
        $logoCandidates[] = base_path('public/logo.svg');
        $logoCandidates[] = dirname(base_path()) . '/public_html/logo.svg';

        foreach ($logoCandidates as $logoPath) {
            if ($logoPath && file_exists($logoPath)) {
                $logoDataUri = 'data:image/svg+xml;base64,' . base64_encode(file_get_contents($logoPath));
                break;
            }
        }

        return [
            'organization_name' => setting('school_name', config('app.name', 'School Results')),
            'logo_data_uri' => $logoDataUri,
            'exam_name' => $exam->exam_name,
            'academic_year' => $exam->academicYear?->name ?? '',
            'class_name' => $class->name,
            'section_name' => $section->name,
            'subject_name' => $subject->name,
            'maximum_marks' => $maxMarks,
            'teacher_name' => $teacherName,
            'students' => $studentRows,
            'total_students' => count($studentRows),
            'generated_at' => now()->timezone('Asia/Kolkata')->format('d M Y, h:i A'),
            'assignment' => $assignment,
        ];
    }

    /**
     * Render PDF bytes on-the-fly from the latest database data.
     * Does NOT write to disk — used for live digital preview.
     */
    public function renderPdfContent(
        Exam $exam,
        SchoolClass $class,
        Section $section,
        Subject $subject
    ): string {
        $data = $this->getAwardRollData($exam, $class, $section, $subject);

        $pdf = Pdf::loadView('results.pdf.award-roll', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => false,
                'defaultFont'          => 'sans-serif',
            ], true);

        return $pdf->output();
    }

    /**
     * Build standardized filename for Award Roll reports.
     */
    public function makeFileName(
        Exam $exam,
        SchoolClass $class,
        Section $section,
        Subject $subject,
        string $extension = 'pdf'
    ): string {
        $safeExam = Str::slug($exam->exam_name, '_');
        $safeClass = Str::slug($class->name, '_');
        $safeSection = Str::slug($section->name, '_');
        $safeSubject = Str::slug($subject->name, '_');

        return "{$safeExam}_{$safeClass}_{$safeSection}_{$safeSubject}_Award_Roll.{$extension}";
    }

    /**
     * Render Excel spreadsheet in memory from the latest database data.
     * Does NOT write to disk — streamed directly to download response.
     */
    public function renderExcelSpreadsheet(
        Exam $exam,
        SchoolClass $class,
        Section $section,
        Subject $subject
    ): Spreadsheet {
        $data = $this->getAwardRollData($exam, $class, $section, $subject);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Award Roll');

        // Institutional Title
        $schoolName = $data['organization_name'];
        $sheet->setCellValue('A1', strtoupper($schoolName));
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Subtitle
        $sheet->setCellValue('A2', 'OFFICIAL TABULATION & AWARD ROLL');
        $sheet->mergeCells('A2:F2');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Metadata block
        $meta = [
            ['Examination:', $data['exam_name'], 'Academic Year:', $data['academic_year']],
            ['Class & Section:', $data['class_name'] . ' (' . $data['section_name'] . ')', 'Subject:', $data['subject_name']],
            ['Maximum Marks:', $data['maximum_marks'], 'Teacher:', $data['teacher_name']],
            ['Total Students:', $data['total_students'], 'Generated On:', $data['generated_at']],
        ];

        $startRow = 4;
        foreach ($meta as $rIdx => $row) {
            $currRow = $startRow + $rIdx;
            $sheet->setCellValue('A' . $currRow, $row[0]);
            $sheet->setCellValue('B' . $currRow, $row[1]);
            $sheet->setCellValue('D' . $currRow, $row[2]);
            $sheet->setCellValue('E' . $currRow, $row[3]);
            $sheet->getStyle('A' . $currRow)->getFont()->setBold(true);
            $sheet->getStyle('D' . $currRow)->getFont()->setBold(true);
        }

        // Table Header
        $tableHeaderRow = 9;
        $headers = ['Roll No', 'Student ID', 'Student Name', 'Marks Obtained', 'Percentage', 'Remarks'];
        $cols = ['A', 'B', 'C', 'D', 'E', 'F'];
        foreach ($headers as $idx => $header) {
            $sheet->setCellValue($cols[$idx] . $tableHeaderRow, $header);
        }

        $sheet->getStyle("A{$tableHeaderRow}:F{$tableHeaderRow}")->getFont()->setBold(true);
        $sheet->getStyle("A{$tableHeaderRow}:F{$tableHeaderRow}")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE2E8F0');
        $sheet->getStyle("A{$tableHeaderRow}:B{$tableHeaderRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("D{$tableHeaderRow}:E{$tableHeaderRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Data Rows
        $dataRow = 10;
        foreach ($data['students'] as $st) {
            $sheet->setCellValueExplicit('A' . $dataRow, $st['roll_no'], DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('B' . $dataRow, (string) $st['student_id'], DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('C' . $dataRow, (string) $st['student_name'], DataType::TYPE_STRING);
            $sheet->setCellValue('D' . $dataRow, $st['marks']);
            $sheet->setCellValue('E' . $dataRow, $st['percentage']);
            $sheet->setCellValueExplicit('F' . $dataRow, (string) ($st['remarks'] ?? ''), DataType::TYPE_STRING);

            $sheet->getStyle('A' . $dataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B' . $dataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D' . $dataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E' . $dataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $dataRow++;
        }

        // Auto-fit columns
        foreach ($cols as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return $spreadsheet;
    }

    /**
     * Generate Award Roll record in memory (does NOT persist file to server disk).
     * The server keeps NO physical report files — files are generated on the fly.
     */
    public function generatePdf(
        Exam $exam,
        SchoolClass $class,
        Section $section,
        Subject $subject,
        ?int $userId = null
    ): AwardRoll {
        $data = $this->getAwardRollData($exam, $class, $section, $subject);
        $fileName = $this->makeFileName($exam, $class, $section, $subject, 'pdf');
        $filePath = "memory://award_rolls/{$fileName}";

        // Save or update AwardRoll record for audit / quick archive tracking without server disk writes
        $awardRoll = AwardRoll::updateOrCreate(
            [
                'exam_id' => $exam->id,
                'class_id' => $class->id,
                'section_id' => $section->id,
                'subject_id' => $subject->id,
            ],
            [
                'teacher_assignment_id' => $data['assignment']?->id,
                'generated_by' => $userId,
                'file_path' => $filePath,
                'file_name' => $fileName,
                'student_count' => $data['total_students'],
                'max_marks' => $data['maximum_marks'],
            ]
        );

        $awardRoll->touch();

        AuditLogService::log('award_roll_generated', $awardRoll, [
            'exam_id' => $exam->id,
            'class_id' => $class->id,
            'section_id' => $section->id,
            'subject_id' => $subject->id,
            'file_name' => $fileName,
            'total_students' => $data['total_students'],
            'storage' => 'in_memory_only',
        ], $userId);

        return $awardRoll;
    }

    /**
     * Generate bulk award rolls for all assignments in an exam.
     */
    public function generateBulkForExam(Exam $exam, ?int $userId = null): array
    {
        $assignments = TeacherAssignment::with(['schoolClass', 'section', 'subject'])
            ->where('academic_year_id', $exam->academic_year_id)
            ->where('status', 'active')
            ->get();

        $generated = [];
        if ($assignments->isNotEmpty()) {
            foreach ($assignments as $assignment) {
                if (!$assignment->schoolClass || !$assignment->section || !$assignment->subject) {
                    continue;
                }
                $generated[] = $this->generatePdf(
                    $exam,
                    $assignment->schoolClass,
                    $assignment->section,
                    $assignment->subject,
                    $userId
                );
            }
        } else {
            // Fallback: Generate for all active classes, sections, and subjects in this exam's academic year
            $classes = SchoolClass::with('sections')->where('status', 'active')->get();
            $subjects = Subject::where('academic_year_id', $exam->academic_year_id)->where('status', 'active')->get();
            if ($subjects->isEmpty()) {
                $subjects = Subject::where('status', 'active')->get();
            }

            foreach ($classes as $class) {
                foreach ($class->sections as $section) {
                    foreach ($subjects as $subject) {
                        $generated[] = $this->generatePdf(
                            $exam,
                            $class,
                            $section,
                            $subject,
                            $userId
                        );
                    }
                }
            }
        }

        return $generated;
    }
}
