<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Marks Sheet - {{ $student->name }} (Roll {{ $student->roll_no }})</title>
    <style>
        @page {
            margin: 12mm 14mm 12mm 14mm;
            size: A4 portrait;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #0f172a;
            line-height: 1.35;
            margin: 0;
            padding: 0;
        }

        /* Header Layout */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .header-logo {
            width: 55px;
            vertical-align: top;
            text-align: left;
            padding-top: 2px;
        }
        .logo-img {
            width: 48px;
            height: 48px;
        }
        .header-content {
            vertical-align: middle;
            text-align: center;
            padding-right: 55px;
        }
        .org-name {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #1e3a8a;
            margin: 0 0 3px 0;
            line-height: 1.2;
        }
        .badge-title {
            display: inline-block;
            background-color: #1e3a8a;
            color: #ffffff;
            font-size: 9.5px;
            font-weight: bold;
            padding: 2px 14px;
            border-radius: 3px;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            margin: 2px 0 4px 0;
        }
        .exam-title {
            font-size: 11px;
            font-weight: bold;
            color: #1e293b;
            margin: 0;
        }

        /* Meta Information Card */
        .meta-box {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #cbd5e1;
            background-color: #f8fafc;
            border-radius: 4px;
            margin-bottom: 14px;
            font-size: 9.5px;
        }
        .meta-box td {
            padding: 4px 8px;
            vertical-align: middle;
            border-bottom: 1px solid #e2e8f0;
        }
        .meta-box tr:last-child td {
            border-bottom: none;
        }
        .meta-label {
            font-weight: bold;
            color: #64748b;
            width: 18%;
            text-transform: uppercase;
            font-size: 8.5px;
            letter-spacing: 0.4px;
        }
        .meta-val {
            font-weight: bold;
            color: #0f172a;
            width: 32%;
        }

        /* Marks Tabulation Table */
        .marks-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            font-size: 9.5px;
        }
        .marks-table thead {
            display: table-header-group;
        }
        .marks-table tr {
            page-break-inside: avoid;
        }
        .marks-table th {
            background-color: #1e3a8a;
            color: #ffffff;
            border: 1px solid #1e3a8a;
            padding: 5px 6px;
            font-weight: bold;
            font-size: 8.5px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            text-align: center;
        }
        .marks-table th.th-exam {
            text-align: left;
            width: 22%;
        }
        .marks-table th.th-total {
            background-color: #0f172a;
            color: #ffffff;
            border: 1px solid #0f172a;
        }
        .marks-table td {
            border: 1px solid #cbd5e1;
            padding: 4px 6px;
            color: #1e293b;
            vertical-align: middle;
            text-align: center;
        }
        .marks-table tbody tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .marks-table tfoot td {
            background-color: #f1f5f9;
            font-weight: bold;
            border: 1px solid #cbd5e1;
        }

        .text-left { text-align: left !important; }
        .text-center { text-align: center !important; }
        .text-right { text-align: right !important; }
        .font-bold { font-weight: bold; }
        .font-mono { font-family: DejaVu Sans Mono, monospace; }

        /* Status Colors */
        .mark-pass { color: #047857; font-weight: bold; }
        .mark-fail { color: #b91c1c; font-weight: bold; }
        .mark-ab { color: #dc2626; font-weight: bold; }

        /* Summary Result Card */
        .summary-box {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #cbd5e1;
            background-color: #f8fafc;
            border-radius: 4px;
            margin-bottom: 16px;
            font-size: 9.5px;
        }
        .summary-box td {
            padding: 6px 10px;
            text-align: center;
            border-right: 1px solid #e2e8f0;
        }
        .summary-box td:last-child {
            border-right: none;
        }
        .summary-title {
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 2px;
        }
        .summary-val {
            font-size: 13px;
            font-weight: bold;
            color: #0f172a;
        }
        .badge-pass {
            display: inline-block;
            background-color: #d1fae5;
            color: #065f46;
            font-weight: bold;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 10px;
        }
        .badge-fail {
            display: inline-block;
            background-color: #fee2e2;
            color: #991b1b;
            font-weight: bold;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 10px;
        }
        .badge-pending {
            display: inline-block;
            background-color: #fef3c7;
            color: #92400e;
            font-weight: bold;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 10px;
        }

        /* Signatures Area */
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            page-break-inside: avoid;
            margin-top: 24px;
        }
        .signature-table td {
            width: 33.33%;
            text-align: center;
            vertical-align: bottom;
            padding: 0 15px;
        }
        .sig-line {
            border-top: 1px solid #0f172a;
            width: 80%;
            margin: 0 auto 4px auto;
        }
        .sig-title {
            font-weight: bold;
            font-size: 9px;
            color: #0f172a;
        }

        /* Footer Note */
        .footer-note {
            font-size: 7.5px;
            color: #94a3b8;
            text-align: center;
            border-top: 0.5px solid #e2e8f0;
            padding-top: 5px;
            margin-top: 14px;
        }
    </style>
</head>
<body>

    <!-- 1. Institutional Header -->
    <table class="header-table">
        <tr>
            <td class="header-logo">
                @if($logoDataUri)
                    <img src="{{ $logoDataUri }}" alt="Logo" class="logo-img">
                @endif
            </td>
            <td class="header-content">
                <div class="org-name">{{ $schoolName }}</div>
                <div><span class="badge-title">EXAMINATION MARKS SHEET</span></div>
                <div class="exam-title">ACADEMIC SESSION: {{ $student->academicYear?->name ?? 'CURRENT' }}</div>
            </td>
        </tr>
    </table>

    <!-- 2. Student Metadata Box -->
    <table class="meta-box">
        <tr>
            <td class="meta-label">Student Name:</td>
            <td class="meta-val">{{ $student->name }}</td>
            <td class="meta-label">Roll Number:</td>
            <td class="meta-val">{{ $student->roll_no }}</td>
        </tr>
        <tr>
            <td class="meta-label">Student ID:</td>
            <td class="meta-val font-mono">{{ $student->student_id }}</td>
            <td class="meta-label">Class & Section:</td>
            <td class="meta-val">{{ $student->schoolClass?->name }} - {{ $student->section?->name }}</td>
        </tr>
        <tr>
            <td class="meta-label">Academic Year:</td>
            <td class="meta-val">{{ $student->academicYear?->name }}</td>
            <td class="meta-label">Issue Date:</td>
            <td class="meta-val">{{ now()->format('d/m/Y') }}</td>
        </tr>
    </table>

    <!-- 3. Marks Tabulation Table -->
    <table class="marks-table">
        <thead>
            <tr>
                <th class="th-exam">Examination</th>
                @foreach($subjects as $subject)
                    <th>
                        {{ $subject->name }}
                        <div style="font-size: 7px; font-weight: normal; color: #dbeafe;">Max: {{ (int) $subject->maximum_marks }}</div>
                    </th>
                @endforeach
                <th class="th-total" style="width: 14%;">Total / Max</th>
                <th class="th-total" style="width: 10%;">Pct %</th>
                <th class="th-total" style="width: 8%;">Grd</th>
                <th class="th-total" style="width: 10%;">Result</th>
            </tr>
        </thead>
        <tbody>
            @forelse($exams as $exam)
                @php
                    $summary = $examSummaries[$exam->id] ?? null;
                @endphp
                <tr>
                    <td class="text-left font-bold" style="font-size: 9px;">{{ $exam->exam_name }}</td>
                    @foreach($subjects as $subject)
                        @php
                            $rowMark = $matrix[$exam->id][$subject->id] ?? null;
                        @endphp
                        <td>
                            @if(!$rowMark || $rowMark['status'] === 'pending')
                                <span style="color: #94a3b8;">—</span>
                            @elseif($rowMark['is_absent'])
                                <span class="mark-ab">AB</span>
                            @else
                                <span class="{{ $rowMark['is_passed'] ? 'mark-pass' : 'mark-fail' }}">
                                    {{ $rowMark['obtained'] }}
                                </span>
                            @endif
                        </td>
                    @endforeach

                    <!-- Row Aggregates -->
                    <td class="font-bold">
                        @if($summary && !$summary['has_missing'])
                            {{ $summary['total_obtained'] }} / {{ $summary['total_max'] }}
                        @else
                            <span style="color: #94a3b8;">—</span>
                        @endif
                    </td>
                    <td class="font-bold">
                        {{ $summary && !$summary['has_missing'] ? $summary['percentage'] . '%' : '—' }}
                    </td>
                    <td class="font-bold">
                        {{ $summary && !$summary['has_missing'] ? $summary['grade'] : '—' }}
                    </td>
                    <td>
                        @if(!$summary || $summary['has_missing'])
                            <span class="badge-pending">PENDING</span>
                        @elseif($summary['result'] === 'PASS')
                            <span class="badge-pass">PASS</span>
                        @else
                            <span class="badge-fail">FAIL</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($subjects) + 5 }}" class="text-center" style="padding: 15px; color: #64748b;">
                        No examination records found for this student.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if(count($exams) > 1)
            <tfoot>
                <tr>
                    <td class="text-left font-bold">Subject Cumulative Avg</td>
                    @foreach($subjects as $subject)
                        @php
                            $subSummary = $subjectSummaries[$subject->id] ?? null;
                        @endphp
                        <td>
                            @if($subSummary && $subSummary['count_entered'] > 0)
                                <span class="font-bold">{{ $subSummary['percentage'] }}%</span>
                                <div style="font-size: 7px; color: #64748b;">({{ $subSummary['grade'] }})</div>
                            @else
                                <span style="color: #94a3b8;">—</span>
                            @endif
                        </td>
                    @endforeach
                    <td class="font-bold">{{ $grandTotalObtained }} / {{ $grandTotalMax }}</td>
                    <td class="font-bold">{{ $grandPercentage }}%</td>
                    <td class="font-bold">{{ $overallGrade }}</td>
                    <td>
                        @if($overallResult === 'PASS')
                            <span class="badge-pass">PASS</span>
                        @elseif($overallResult === 'FAIL')
                            <span class="badge-fail">FAIL</span>
                        @else
                            <span class="badge-pending">PENDING</span>
                        @endif
                    </td>
                </tr>
            </tfoot>
        @endif
    </table>

    <!-- 4. Grand Cumulative Summary Box -->
    <table class="summary-box">
        <tr>
            <td>
                <div class="summary-title">Total Obtained</div>
                <div class="summary-val">{{ $grandTotalObtained }} <span style="font-size: 9px; color: #64748b; font-weight: normal;">/ {{ $grandTotalMax }}</span></div>
            </td>
            <td>
                <div class="summary-title">Aggregate Percentage</div>
                <div class="summary-val" style="color: #1e3a8a;">{{ $grandPercentage }}%</div>
            </td>
            <td>
                <div class="summary-title">Cumulative Grade</div>
                <div class="summary-val">{{ $overallGrade }}</div>
            </td>
            <td>
                <div class="summary-title">Final Result</div>
                <div>
                    @if($overallResult === 'PASS')
                        <span class="badge-pass" style="font-size: 12px; padding: 3px 12px;">PASS</span>
                    @elseif($overallResult === 'FAIL')
                        <span class="badge-fail" style="font-size: 12px; padding: 3px 12px;">FAIL</span>
                    @else
                        <span class="badge-pending" style="font-size: 12px; padding: 3px 12px;">PENDING</span>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <!-- 5. Standardized Institutional Signatures Area -->
    <table class="signature-table">
        <tr>
            <td>
                <div class="sig-line"></div>
                <div class="sig-title">Class Teacher / In-Charge</div>
            </td>
            <td>
                <div class="sig-line"></div>
                <div class="sig-title">Examination Coordinator</div>
            </td>
            <td>
                <div class="sig-line"></div>
                <div class="sig-title">Principal / Headmaster</div>
            </td>
        </tr>
    </table>

    <!-- 6. Institutional Security Stamp -->
    <div class="footer-note">
        This is an official computer-generated student marks sheet issued by {{ $schoolName }}. Generated on {{ now()->timezone('Asia/Kolkata')->format('d M Y, h:i A') }}. Any unauthorized alterations render this certificate void.
    </div>

</body>
</html>
