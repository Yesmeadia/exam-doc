<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Class Statement - {{ $exam->exam_name }} - {{ $class->name }} ({{ $section_title }})</title>
    <style>
        @page {
            margin: 8mm 10mm 8mm 10mm;
            size: A4 landscape;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 8.5px;
            color: #0f172a;
            line-height: 1.25;
            margin: 0;
            padding: 0;
        }

        /* Header Layout */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }
        .header-logo {
            width: 50px;
            vertical-align: middle;
            text-align: left;
        }
        .logo-img {
            width: 44px;
            height: 44px;
        }
        .header-content {
            vertical-align: middle;
            text-align: center;
            padding-right: 50px;
        }
        .org-name {
            font-size: 15px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #1e3a8a;
            margin: 0 0 2px 0;
            line-height: 1.2;
        }
        .badge-title {
            display: inline-block;
            background-color: #1e3a8a;
            color: #ffffff;
            font-size: 9px;
            font-weight: bold;
            padding: 2px 14px;
            border-radius: 3px;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin: 2px 0;
        }
        .exam-title {
            font-size: 10.5px;
            font-weight: bold;
            color: #1e293b;
            margin: 0;
        }

        /* Meta Information Strip */
        .meta-strip {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #cbd5e1;
            background-color: #f8fafc;
            border-radius: 3px;
            margin-bottom: 8px;
            font-size: 9px;
        }
        .meta-strip td {
            padding: 3.5px 8px;
            vertical-align: middle;
            border-right: 1px solid #e2e8f0;
        }
        .meta-strip td:last-child {
            border-right: none;
        }
        .meta-label {
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            font-size: 8px;
            letter-spacing: 0.3px;
        }
        .meta-val {
            font-weight: bold;
            color: #0f172a;
        }

        /* Statement Tabulation Table */
        .statement-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            font-size: 8px;
        }
        .statement-table thead {
            display: table-header-group;
        }
        .statement-table tr {
            page-break-inside: avoid;
        }
        .statement-table th {
            background-color: #1e3a8a;
            color: #ffffff;
            border: 0.5px solid #1e3a8a;
            padding: 4px 3px;
            font-weight: bold;
            font-size: 7.5px;
            text-transform: uppercase;
            text-align: center;
        }
        .statement-table th.sub-th {
            background-color: #172554;
            color: #dbeafe;
            font-size: 6.5px;
            font-weight: normal;
            border: 0.5px solid #172554;
        }
        .statement-table th.th-result {
            background-color: #0f172a;
            color: #ffffff;
            border: 0.5px solid #0f172a;
        }
        .statement-table td {
            border: 0.5px solid #cbd5e1;
            padding: 3px 3px;
            color: #1e293b;
            vertical-align: middle;
        }
        .statement-table tbody tr:nth-child(even) td {
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
        .font-bold {
            font-weight: bold;
        }
        .font-mono {
            font-family: DejaVu Sans Mono, monospace;
        }

        /* Status Colors */
        .mark-pass {
            color: #047857;
            font-weight: bold;
        }
        .mark-fail {
            color: #b91c1c;
            font-weight: bold;
        }
        .mark-ab {
            color: #dc2626;
            font-weight: bold;
        }
        .badge-pass {
            background-color: #d1fae5;
            color: #065f46;
            font-weight: bold;
            padding: 1px 4px;
            border-radius: 2px;
            font-size: 7px;
        }
        .badge-fail {
            background-color: #fee2e2;
            color: #991b1b;
            font-weight: bold;
            padding: 1px 4px;
            border-radius: 2px;
            font-size: 7px;
        }
        .badge-pending {
            background-color: #fef3c7;
            color: #92400e;
            font-weight: bold;
            padding: 1px 4px;
            border-radius: 2px;
            font-size: 7px;
        }

        /* Summary Analytics Strip */
        .analytics-table {
            width: 100%;
            border-collapse: collapse;
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-radius: 3px;
            margin-bottom: 10px;
            font-size: 8px;
        }
        .analytics-table td {
            padding: 4px 6px;
            font-weight: bold;
            color: #334155;
            text-align: center;
            border-right: 1px solid #cbd5e1;
        }
        .analytics-table td:last-child {
            border-right: none;
        }
        .num-highlight {
            color: #1e3a8a;
            font-size: 9px;
        }

        /* Signatures Area */
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            page-break-inside: avoid;
            margin-top: 14px;
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
            margin: 0 auto 3px auto;
        }
        .sig-title {
            font-weight: bold;
            font-size: 8.5px;
            color: #0f172a;
        }

        /* Confidential stamp */
        .footer-note {
            font-size: 7px;
            color: #94a3b8;
            text-align: center;
            border-top: 0.5px solid #e2e8f0;
            padding-top: 4px;
            margin-top: 10px;
        }
    </style>
</head>
<body>

    <!-- Institutional Header -->
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
                <h1 class="org-name">{{ $school_name }}</h1>
                <div class="badge-title">CLASS WISE STATEMENT OF MARKS & EVALUATION</div>
                <div class="exam-title">{{ $exam->exam_name }} @if(!empty($academic_year))• Academic Session: {{ $academic_year }}@endif</div>
            </td>
        </tr>
    </table>

    <!-- Metadata Strip -->
    <table class="meta-strip">
        <tr>
            <td><span class="meta-label">Class:</span> <span class="meta-val">{{ $class->name }}</span></td>
            <td><span class="meta-label">Section:</span> <span class="meta-val">{{ $section_title }}</span></td>
            <td><span class="meta-label">Examination:</span> <span class="meta-val">{{ $exam->exam_name }}</span></td>
            <td><span class="meta-label">Total Enrolled:</span> <span class="meta-val">{{ $analytics['total_enrolled'] }}</span></td>
            <td><span class="meta-label">Appeared:</span> <span class="meta-val">{{ $analytics['appeared'] }}</span></td>
            <td><span class="meta-label">Pass Rate:</span> <span class="meta-val">{{ $analytics['pass_rate'] }}%</span></td>
            <td><span class="meta-label">Generated:</span> <span class="meta-val">{{ $generated_at }}</span></td>
        </tr>
    </table>

    <!-- Tabulation Table -->
    <table class="statement-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 32px;">Roll</th>
                <th rowspan="2" style="width: 55px;" class="text-left">ID</th>
                <th rowspan="2" style="width: 110px;" class="text-left">Student Name</th>
                @if(!$section)
                    <th rowspan="2" style="width: 30px;">Sec</th>
                @endif
                @foreach($columns as $col)
                    <th colspan="3" style="min-width: 54px;">{{ $col['short_title'] ?? $col['title'] }}</th>
                @endforeach
                <th rowspan="2" style="width: 40px;" class="th-result">Total</th>
                <th rowspan="2" style="width: 35px;" class="th-result">Max</th>
                <th rowspan="2" style="width: 42px;" class="th-result">Pct %</th>
                <th rowspan="2" style="width: 30px;" class="th-result">Grd</th>
                <th rowspan="2" style="width: 30px;" class="th-result">Rank</th>
            </tr>
            <tr>
                @foreach($columns as $col)
                    <th class="sub-th">Marks</th>
                    <th class="sub-th">%</th>
                    <th class="sub-th">Gr.</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($students as $st)
                <tr>
                    <td class="text-center font-bold">{{ $st['roll_no'] }}</td>
                    <td class="text-left font-mono" style="font-size: 7.5px;">{{ $st['student_id'] }}</td>
                    <td class="text-left font-bold" style="white-space: nowrap; overflow: hidden;">{{ $st['student_name'] }}</td>
                    @if(!$section)
                        <td class="text-center">{{ $st['section_name'] }}</td>
                    @endif

                    @foreach($columns as $colKey => $col)
                        @php
                            $colData = $st['columns'][$colKey] ?? null;
                            $display = $colData ? $colData['display'] : '—';
                            $status = $colData ? $colData['status'] : 'pending';
                            $isPassed = $colData ? $colData['is_passed'] : false;
                            $tag = $colData ? ($colData['tag'] ?? null) : null;
                            $pct = ($colData && isset($colData['percentage'])) ? $colData['percentage'] : null;
                            $gr  = ($colData && !empty($colData['grade']) && $colData['grade'] !== '—') ? $colData['grade'] : null;
                        @endphp
                        {{-- MARKS --}}
                        <td class="text-center">
                            @if($status === 'absent')
                                <span class="mark-ab">AB</span>
                            @elseif($status === 'entered')
                                <span class="{{ $isPassed ? 'mark-pass' : 'mark-fail' }}">{{ $display }}</span>
                            @elseif($status === 'not_applicable')
                                <span style="color: #94a3b8; font-size: 7px;">—</span>
                            @else
                                <span style="color: #94a3b8;">—</span>
                            @endif
                        </td>
                        {{-- % --}}
                        <td class="text-center" style="font-size: 7px;">
                            @if($status === 'entered' && $pct !== null)
                                <span style="color: {{ $isPassed ? '#047857' : '#b91c1c' }}; font-weight: bold;">{{ $pct }}</span>
                            @else
                                <span style="color: #94a3b8;">—</span>
                            @endif
                        </td>
                        {{-- GR. --}}
                        <td class="text-center" style="font-size: 7px;">
                            @if($status === 'entered' && $gr)
                                <span style="font-weight: bold; color: {{ in_array($gr, ['A1','A+','A']) ? '#047857' : (in_array($gr, ['A2','B1','B+','B']) ? '#1d4ed8' : ($gr === 'F' ? '#b91c1c' : '#92400e')) }};">{{ $gr }}</span>
                            @elseif($status === 'absent')
                                <span class="mark-ab" style="font-size: 6.5px;">AB</span>
                            @else
                                <span style="color: #94a3b8;">—</span>
                            @endif
                            @if($tag)
                                <span style="font-size: 5.5px; color: #64748b; display:block;">({{ $tag }})</span>
                            @endif
                        </td>
                    @endforeach

                    <td class="text-center font-bold">{{ $st['has_appeared'] ? $st['total_obtained'] : '—' }}</td>
                    <td class="text-center" style="color: #64748b;">{{ $st['total_max'] }}</td>
                    <td class="text-center font-bold" style="font-size: 8.5px;">
                        {{ $st['has_appeared'] ? $st['percentage'] . '%' : '—' }}
                    </td>
                    <td class="text-center font-bold">{{ $st['has_appeared'] ? $st['grade'] : '—' }}</td>
                    <td class="text-center font-bold">
                        {{ $st['rank'] ? '#' . $st['rank'] : '—' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ 4 + count($columns) + 5 + (!$section ? 1 : 0) }}" class="text-center" style="padding: 15px; color: #94a3b8;">
                        No enrolled students found for the selected examination and class/section.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if(count($students) > 0)
            <tfoot>
                <tr style="background-color: #f1f5f9; font-weight: bold;">
                    <td colspan="{{ 3 + (!$section ? 1 : 0) }}" class="text-left" style="padding-left: 6px;">
                        Subject Averages / Pass Rate
                    </td>
                    @foreach($columns as $colKey => $col)
                        @php $stat = $column_stats[$colKey] ?? null; @endphp
                        <td colspan="3" class="text-center" style="font-size: 7px; line-height: 1.1;">
                            Avg: {{ $stat['average'] ?? '—' }}<br>
                            <span style="color: #065f46;">{{ $stat['pass_rate'] ?? 0 }}%</span>
                        </td>
                    @endforeach
                    <td colspan="5" class="text-center" style="font-size: 7.5px;">
                        Class Pass Rate: {{ $analytics['pass_rate'] }}% | Class Avg: {{ $analytics['average_percentage'] }}%
                    </td>
                </tr>
            </tfoot>
        @endif
    </table>

    <!-- Analytics Summary Strip -->
    <table class="analytics-table">
        <tr>
            <td>Total Enrolled: <span class="num-highlight">{{ $analytics['total_enrolled'] }}</span></td>
            <td>Appeared: <span class="num-highlight">{{ $analytics['appeared'] }}</span></td>
            <td>Passed: <span class="num-highlight" style="color: #047857;">{{ $analytics['passed'] }}</span></td>
            <td>Failed: <span class="num-highlight" style="{{ $analytics['failed'] > 0 ? 'color: #b91c1c;' : '' }}">{{ $analytics['failed'] }}</span></td>
            <td>Pass Percentage: <span class="num-highlight">{{ $analytics['pass_rate'] }}%</span></td>
            <td>Class Average %: <span class="num-highlight">{{ $analytics['average_percentage'] }}%</span></td>
            @if($analytics['top_scorer'])
                <td>Highest Scorer: <span class="num-highlight">{{ $analytics['top_scorer']['student_name'] }} ({{ $analytics['top_scorer']['percentage'] }}%)</span></td>
            @endif
        </tr>
    </table>

    <!-- Institutional Signatures -->
    <table class="signature-table">
        <tr>
            <td>
                <div class="sig-line"></div>
                <div class="sig-title">Class Teacher Signature</div>
            </td>
            <td>
                <div class="sig-line"></div>
                <div class="sig-title">Examination In-Charge</div>
            </td>
            <td>
                <div class="sig-line"></div>
                <div class="sig-title">Principal / Head of Institution</div>
            </td>
        </tr>
    </table>

    <div class="footer-note">
        Official Broadsheet Statement of Marks • {{ $school_name }} • Printed directly on-demand
    </div>

</body>
</html>
