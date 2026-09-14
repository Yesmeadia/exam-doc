<?php

namespace App\Services;

use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class StudentImportService
{
    /**
     * Generate sample Excel template for download.
     * Columns: Roll No | Student ID | Student Name
     */
    public function generateTemplate(): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Students');

        // Headers
        $headers = ['Roll No', 'Student ID', 'Student Name'];
        $sheet->fromArray([$headers], null, 'A1');

        // Sample data rows
        $sampleData = [
            [1, 'ST001', 'Abdul Rahman'],
            [2, 'ST002', 'Ameen Khan'],
            [3, 'ST003', 'Anas Ali'],
        ];
        $sheet->fromArray($sampleData, null, 'A2');

        // Styling: bold header, auto-width
        foreach (range('A', 'C') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->getStyle('A1:C1')->getFont()->setBold(true);
        $sheet->getStyle('A1:C1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE2E8F0');
        $sheet->getStyle('A1:C1')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $tempPath = tempnam(sys_get_temp_dir(), 'std_template_') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return $tempPath;
    }

    /**
     * Parse and validate uploaded Excel / CSV file without persisting.
     *
     * @param  UploadedFile  $file
     * @param  int           $academicYearId
     * @param  int           $classId         Selected class ID from the form
     * @param  int           $sectionId       Selected section ID from the form
     */
    public function validateAndPreview(
        UploadedFile $file,
        int $academicYearId,
        int $classId,
        int $sectionId
    ): array {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        if (empty($rows) || count($rows) < 2) {
            throw new Exception("The uploaded spreadsheet is empty or has no data rows.");
        }

        // Header row – just consume it (we rely on column position, not header names)
        array_shift($rows);

        // Load the selected class and section
        $class   = SchoolClass::find($classId);
        $section = Section::find($sectionId);

        if (!$class || !$section) {
            throw new Exception("Selected class or section was not found in the system.");
        }

        // Preload existing student IDs globally
        $existingStudentIds = Student::pluck('student_id')
            ->map(fn ($id) => strtolower(trim((string) $id)))
            ->flip()
            ->toArray();

        // Preload existing roll numbers for this academic year / class / section
        $existingRollNos = [];
        $existingStudents = Student::where('academic_year_id', $academicYearId)
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->pluck('roll_no')
            ->toArray();
        foreach ($existingStudents as $rn) {
            $existingRollNos[$rn] = true;
        }

        $validRows   = [];
        $invalidRows = [];
        $seenFileStudentIds = [];
        $seenFileRollNos    = [];
        $rowNumber = 2; // Data rows start at row 2

        foreach ($rows as $row) {
            // Column layout: A = Roll No, B = Student ID, C = Student Name
            $rollNoRaw   = trim((string) ($row['A'] ?? ''));
            $studentId   = strtoupper(trim((string) ($row['B'] ?? '')));
            $studentName = trim((string) ($row['C'] ?? ''));

            // Skip entirely empty rows
            if ($rollNoRaw === '' && $studentId === '' && $studentName === '') {
                $rowNumber++;
                continue;
            }

            $errors = [];

            // 1. Roll Number
            $rollNo = null;
            if ($rollNoRaw === '' || !is_numeric($rollNoRaw) || (int) $rollNoRaw <= 0) {
                $errors[] = "Roll Number must be a positive integer.";
            } else {
                $rollNo = (int) $rollNoRaw;

                if (isset($seenFileRollNos[$rollNo])) {
                    $errors[] = "Duplicate Roll Number {$rollNo} in file (previously on row {$seenFileRollNos[$rollNo]}).";
                } else {
                    $seenFileRollNos[$rollNo] = $rowNumber;
                }

                if (isset($existingRollNos[$rollNo])) {
                    $errors[] = "Roll Number {$rollNo} is already assigned in {$class->name} / {$section->name} for this academic year.";
                }
            }

            // 2. Student ID
            if ($studentId === '') {
                $errors[] = "Student ID is required.";
            } elseif (in_array(substr($studentId, 0, 1), ['=', '+', '-', '@'])) {
                $errors[] = "Student ID cannot start with special formula characters (=, +, -, @).";
            } else {
                $lowerStudentId = strtolower($studentId);
                if (isset($seenFileStudentIds[$lowerStudentId])) {
                    $errors[] = "Duplicate Student ID '{$studentId}' in file (previously on row {$seenFileStudentIds[$lowerStudentId]}).";
                } else {
                    $seenFileStudentIds[$lowerStudentId] = $rowNumber;
                }

                if (isset($existingStudentIds[$lowerStudentId])) {
                    $errors[] = "Student ID '{$studentId}' already exists in the database.";
                }
            }

            // 3. Student Name
            if ($studentName === '') {
                $errors[] = "Student Name is required.";
            } elseif (in_array(substr($studentName, 0, 1), ['=', '+', '-', '@'])) {
                $errors[] = "Student Name cannot start with special formula characters (=, +, -, @).";
            }

            $rowData = [
                'row_number'      => $rowNumber,
                'roll_no'         => $rollNo,
                'student_id'      => $studentId,
                'name'            => $studentName,
                'class_name'      => $class->name,
                'class_id'        => $classId,
                'section_name'    => $section->name,
                'section_id'      => $sectionId,
                'academic_year_id'=> $academicYearId,
            ];

            if (!empty($errors)) {
                $invalidRows[] = ['row' => $rowData, 'errors' => $errors];
            } else {
                $validRows[] = $rowData;
            }

            $rowNumber++;
        }

        return [
            'total_rows'    => count($validRows) + count($invalidRows),
            'valid_count'   => count($validRows),
            'invalid_count' => count($invalidRows),
            'valid_rows'    => $validRows,
            'invalid_rows'  => $invalidRows,
        ];
    }

    /**
     * Commit valid rows into the database within a transaction.
     */
    public function importValidRows(array $validRows, int $userId): int
    {
        return DB::transaction(function () use ($validRows, $userId) {
            $importedCount = 0;
            foreach ($validRows as $row) {
                Student::create([
                    'student_id'      => $row['student_id'],
                    'name'            => $row['name'],
                    'academic_year_id'=> $row['academic_year_id'],
                    'class_id'        => $row['class_id'],
                    'section_id'      => $row['section_id'],
                    'roll_no'         => $row['roll_no'],
                    'status'          => 'active',
                ]);
                $importedCount++;
            }

            AuditLogService::log('bulk_students_imported', null, [
                'imported_count' => $importedCount,
            ], $userId);

            return $importedCount;
        });
    }
}
