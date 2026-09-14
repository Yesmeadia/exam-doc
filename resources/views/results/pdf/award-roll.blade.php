<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Award Roll - {{ $exam_name }} - {{ $class_name }} {{ $section_name }} - {{ $subject_name }}</title>
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
            font-size: 10.5px;
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
            width: 60px;
            vertical-align: top;
            text-align: left;
            padding-top: 2px;
        }
        .logo-img {
            width: 52px;
            height: 52px;
        }
        .header-content {
            vertical-align: middle;
            text-align: center;
            padding-right: 60px; /* Balance logo width for optical centering */
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
            font-size: 10px;
            font-weight: bold;
            padding: 2px 14px;
            border-radius: 3px;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            margin: 2px 0 4px 0;
        }
        .exam-title {
            font-size: 12px;
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
            font-size: 10.5px;
        }
        .meta-box td {
            padding: 4.5px 8px;
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
            font-size: 9px;
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
            font-size: 10.5px;
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
            padding: 6px 8px;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .marks-table td {
            border: 1px solid #cbd5e1;
            padding: 5px 8px;
            color: #1e293b;
            vertical-align: middle;
        }
        .marks-table tbody tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .text-center {
            text-align: center;
        }
        .text-left {
            text-align: left;
        }
        .text-right {
            text-align: right;
        }
        .font-mono {
            font-family: DejaVu Sans Mono, monospace;
        }
        .marks-ab {
            color: #dc2626;
            font-weight: bold;
        }

        /* Summary Strip */
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            margin-bottom: 18px;
            font-size: 10px;
        }
        .summary-table td {
            padding: 5px 10px;
            font-weight: bold;
            color: #334155;
            text-align: center;
            border-right: 1px solid #cbd5e1;
        }
        .summary-table td:last-child {
            border-right: none;
        }
        .summary-num {
            color: #1e3a8a;
            font-size: 11px;
        }

        /* Footer & Teacher Signature Area */
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            page-break-inside: avoid;
            margin-top: 20px;
        }
        .remarks-col {
            width: 60%;
            vertical-align: bottom;
            padding-right: 25px;
        }
        .remarks-header {
            font-weight: bold;
            font-size: 9.5px;
            text-transform: uppercase;
            color: #475569;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }
        .remarks-line {
            border-bottom: 1px dotted #94a3b8;
            height: 18px;
            margin-bottom: 4px;
        }
        .signature-col {
            width: 40%;
            vertical-align: bottom;
            text-align: center;
            padding-left: 15px;
        }
        .sig-line {
            border-top: 1.5px solid #0f172a;
            width: 85%;
            margin: 0 auto 5px auto;
        }
        .sig-title {
            font-weight: bold;
            font-size: 11px;
            color: #0f172a;
        }
        .sig-name {
            font-size: 10px;
            color: #475569;
            margin-top: 2px;
        }

        /* Bottom Confidentiality Stamp */
        .confidential-note {
            font-size: 8.5px;
            color: #94a3b8;
            text-align: center;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
            margin-top: 24px;
        }
    </style>
</head>
<body>

    <!-- Header with School Logo SVG & Titles -->
    <table class="header-table">
        <tr>
            <td class="header-logo">
                @if(!empty($logo_data_uri))
                    <img src="{{ $logo_data_uri }}" class="logo-img" alt="Logo">
                @elseif(file_exists(public_path('logo.svg')))
                    <img src="{{ public_path('logo.svg') }}" class="logo-img" alt="Logo">
                @endif
            </td>
            <td class="header-content">
                <h1 class="org-name">{{ $organization_name }}</h1>
                <div class="badge-title">OFFICIAL TABULATION & AWARD ROLL</div>
                <div class="exam-title">{{ $exam_name }} @if(!empty($academic_year))• Academic Session: {{ $academic_year }}@endif</div>
            </td>
        </tr>
    </table>

    <!-- Meta Information Box -->
    <table class="meta-box">
        <tr>
            <td class="meta-label">Class & Section:</td>
            <td class="meta-val">{{ $class_name }} ({{ $section_name }})</td>
            <td class="meta-label">Subject:</td>
            <td class="meta-val">{{ $subject_name }}</td>
        </tr>
        <tr>
            <td class="meta-label">Maximum Marks:</td>
            <td class="meta-val">{{ number_format($maximum_marks, 0) }}</td>
            <td class="meta-label">Subject Teacher:</td>
            <td class="meta-val">{{ $teacher_name }}</td>
        </tr>
        <tr>
            <td class="meta-label">Total Candidates:</td>
            <td class="meta-val">{{ $total_students }}</td>
            <td class="meta-label">Generation Date:</td>
            <td class="meta-val">{{ $generated_at }}</td>
        </tr>
    </table>

    <!-- Student Marks Tabulation Table -->
    <table class="marks-table">
        <thead>
            <tr>
                <th style="width: 12%;" class="text-center">Roll No</th>
                <th style="width: 20%;" class="text-left">Student ID</th>
                <th style="width: 38%;" class="text-left">Student Name</th>
                <th style="width: 15%;" class="text-center">Marks Obtained</th>
                <th style="width: 15%;" class="text-center">Percentage</th>
            </tr>
        </thead>
        <tbody>
            @forelse($students as $row)
                @php
                    $isAbsent = ($row['marks'] === 'AB');
                    $pct = $row['percentage'] ?? '—';
                    if ($pct === '—' && is_numeric($row['marks']) && $maximum_marks > 0) {
                        $calc = ($row['marks'] / $maximum_marks) * 100;
                        $pct = (round($calc, 1) == round($calc, 0))
                            ? number_format($calc, 0) . '%'
                            : number_format($calc, 1) . '%';
                    }
                @endphp
                <tr>
                    <td class="text-center"><strong>{{ $row['roll_no'] }}</strong></td>
                    <td class="text-left font-mono"><strong>{{ $row['student_id'] }}</strong></td>
                    <td class="text-left"><strong>{{ $row['student_name'] }}</strong></td>
                    <td class="text-center">
                        @if($isAbsent)
                            <span class="marks-ab">AB</span>
                        @else
                            <strong>{{ $row['marks'] }}</strong>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($isAbsent)
                            <span class="marks-ab">AB</span>
                        @elseif($pct !== '—')
                            <strong>{{ $pct }}</strong>
                        @else
                            <span style="color: #94a3b8;">—</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center" style="padding: 20px; color: #94a3b8;">
                        No enrolled candidates found for this subject and class/section.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Summary Count Strip -->
    @php
        $presentCount = count(array_filter($students, fn($s) => is_numeric($s['marks'])));
        $absentCount = count(array_filter($students, fn($s) => $s['marks'] === 'AB'));
    @endphp
    <table class="summary-table">
        <tr>
            <td>Total Candidates: <span class="summary-num">{{ $total_students }}</span></td>
            <td>Present: <span class="summary-num">{{ $presentCount }}</span></td>
            <td>Absent: <span class="summary-num" style="{{ $absentCount > 0 ? 'color: #dc2626;' : '' }}">{{ $absentCount }}</span></td>
            <td>Max Marks: <span class="summary-num">{{ number_format($maximum_marks, 0) }}</span></td>
        </tr>
    </table>

    <!-- Teacher Remarks & Teacher Signature Only (Verified By and Principal Removed) -->
    <table class="footer-table">
        <tr>
            <td class="remarks-col">
                <div class="remarks-header">Teacher Remarks / Notes:</div>
                <div class="remarks-line"></div>
                <div class="remarks-line"></div>
            </td>
            <td class="signature-col">
                <div class="sig-line"></div>
                <div class="sig-title">Teacher Signature</div>
                <div class="sig-name">({{ $teacher_name }})</div>
            </td>
        </tr>
    </table>

    <!-- Confidential Footer -->
    <div class="confidential-note">
        Official Examination Tabulation Document • {{ $organization_name }} • Confidentially Archived
    </div>

</body>
</html>
