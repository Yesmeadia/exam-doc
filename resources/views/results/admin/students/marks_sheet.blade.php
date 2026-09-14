<x-app-layout>
    <x-slot name="title">{{ __('Marks Sheet - ') . $student->name }}</x-slot>

    <div class="w-full space-y-5">
        <!-- Top Screen Action Bar (Hidden on Print) -->
        <div
            class="no-print flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-2 border-b border-slate-200">
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.students.index', request()->query()) }}"
                    class="inline-flex items-center px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shadow-2xs">
                    &larr; Students Directory
                </a>
                <a href="{{ route('admin.marks-sheets.index', request()->query()) }}"
                    class="inline-flex items-center px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shadow-2xs">
                    Marks Sheets Index
                </a>
            </div>

            <div class="flex items-center flex-wrap gap-2">
                <!-- Sibling Navigation -->
                @if($prevStudent)
                    <a href="{{ route('admin.students.marks-sheet', array_merge(['student' => $prevStudent], request()->query())) }}"
                        class="inline-flex items-center px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shadow-2xs">
                        &larr; Prev
                    </a>
                @endif

                @if($siblings->count() > 1)
                    <select onchange="if (this.value) window.location.href = this.value"
                        class="text-xs font-semibold rounded-lg border-slate-300 py-1.5 pl-2.5 pr-7 focus:ring-indigo-500 focus:border-indigo-500 shadow-2xs bg-white text-slate-800">
                        @foreach($siblings as $sib)
                            <option value="{{ route('admin.students.marks-sheet', array_merge(['student' => $sib], request()->query())) }}" {{ $sib->id === $student->id ? 'selected' : '' }}>
                                Roll {{ $sib->roll_no }} - {{ $sib->name }}
                            </option>
                        @endforeach
                    </select>
                @endif

                @if($nextStudent)
                    <a href="{{ route('admin.students.marks-sheet', array_merge(['student' => $nextStudent], request()->query())) }}"
                        class="inline-flex items-center px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shadow-2xs">
                        Next &rarr;
                    </a>
                @endif

                <!-- Download PDF Button -->
                <a href="{{ route('admin.students.marks-sheet.pdf', $student) }}" target="_blank"
                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-bold uppercase tracking-wider shadow-xs transition">
                    <svg class="w-4 h-4 text-rose-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Download PDF
                </a>

                <!-- Browser Print Button -->
                <button onclick="window.print()"
                    class="inline-flex items-center gap-1.5 px-4 py-1.5 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-xs font-bold uppercase tracking-wider shadow-xs transition">
                    <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Print
                </button>
            </div>
        </div>

        <x-alert />

        <!-- Printable Document Area (Structured Identical to Statement & Award Roll) -->
        <div class="print-container bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8 space-y-5 w-full">
            <!-- 1. Institutional Header -->
            <div class="border-b-2 border-blue-900 pb-3 text-center">
                <div class="flex items-center justify-center gap-4">
                    <img src="{{ asset('logo.svg') }}" alt="School Logo" class="w-12 h-12 object-contain shrink-0"
                        onerror="this.onerror=null; this.src='{{ asset('icons/logo.svg') }}';">
                    <div class="text-center">
                        <h1 class="text-xl sm:text-2xl font-black text-blue-900 tracking-tight uppercase leading-tight">
                            {{ $schoolName }}
                        </h1>
                        <div class="mt-1">
                            <span class="inline-block bg-blue-900 text-white text-[10px] font-bold px-3.5 py-0.5 rounded tracking-wider uppercase">
                                Examination Marks Sheet
                            </span>
                        </div>
                        <p class="text-xs font-bold text-slate-800 mt-1 uppercase">
                            Academic Session: {{ $student->academicYear?->name ?? 'Current' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- 2. Student Metadata Box -->
            <div class="border border-slate-300 rounded-lg p-3 sm:p-4 bg-slate-50/70">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-x-6 gap-y-2 text-xs">
                    <div>
                        <span class="text-slate-500 font-bold uppercase text-[10px] tracking-wider block">Student Name:</span>
                        <p class="font-black text-slate-900 text-sm">{{ $student->name }}</p>
                    </div>
                    <div>
                        <span class="text-slate-500 font-bold uppercase text-[10px] tracking-wider block">Roll No:</span>
                        <p class="font-black text-slate-900 text-sm">{{ $student->roll_no }}</p>
                    </div>
                    <div>
                        <span class="text-slate-500 font-bold uppercase text-[10px] tracking-wider block">Student ID:</span>
                        <p class="font-black text-slate-900 text-sm font-mono">{{ $student->student_id }}</p>
                    </div>
                    <div>
                        <span class="text-slate-500 font-bold uppercase text-[10px] tracking-wider block">Class & Section:</span>
                        <p class="font-black text-slate-900 text-sm">{{ $student->schoolClass?->name }} - {{ $student->section?->name }}</p>
                    </div>
                    <div>
                        <span class="text-slate-500 font-bold uppercase text-[10px] tracking-wider block">Academic Year:</span>
                        <p class="font-bold text-slate-800">{{ $student->academicYear?->name }}</p>
                    </div>
                    <div>
                        <span class="text-slate-500 font-bold uppercase text-[10px] tracking-wider block">Gender:</span>
                        <p class="font-bold text-slate-800">{{ ucfirst($student->gender) }}</p>
                    </div>
                    <div>
                        <span class="text-slate-500 font-bold uppercase text-[10px] tracking-wider block">Issue Date:</span>
                        <p class="font-bold text-slate-800">{{ now()->format('d/m/Y') }}</p>
                    </div>
                </div>
            </div>

            <!-- 3. Marks Tabulation Table -->
            <div class="overflow-x-auto w-full">
                <table class="w-full border-collapse border border-slate-300 text-center text-xs">
                    <!-- Table Header: Institutional Navy #1e3a8a -->
                    <thead>
                        <tr class="bg-blue-900 text-white font-bold" style="background-color: #1e3a8a;">
                            <th class="p-2.5 text-left border border-blue-900 font-bold text-white min-w-[150px] uppercase">
                                Examination
                            </th>
                            @foreach($subjects as $subject)
                                <th class="p-2.5 border border-blue-900 font-bold text-white uppercase min-w-[100px]">
                                    {{ $subject->name }}
                                    <span class="block text-[10px] text-blue-200 font-normal">Max: {{ (int) $subject->maximum_marks }}</span>
                                </th>
                            @endforeach
                            <th class="p-2.5 border border-slate-900 font-bold text-white min-w-[90px] bg-slate-900" style="background-color: #0f172a;">
                                Total / Max
                            </th>
                            <th class="p-2.5 border border-slate-900 font-bold text-white min-w-[70px] bg-slate-900" style="background-color: #0f172a;">
                                Pct %
                            </th>
                            <th class="p-2.5 border border-slate-900 font-bold text-white min-w-[60px] bg-slate-900" style="background-color: #0f172a;">
                                Grade
                            </th>
                            <th class="p-2.5 border border-slate-900 font-bold text-white min-w-[80px] bg-slate-900" style="background-color: #0f172a;">
                                Result
                            </th>
                        </tr>
                    </thead>

                    <!-- Table Body -->
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @forelse($exams as $exam)
                            @php
                                $summary = $examSummaries[$exam->id] ?? null;
                            @endphp
                            <tr class="border-b border-slate-200 hover:bg-slate-50/70">
                                <td class="p-2.5 text-left border border-slate-300 font-bold text-slate-900">
                                    {{ $exam->exam_name }}
                                </td>

                                @foreach($subjects as $subject)
                                    @php
                                        $cell = $matrix[$exam->id][$subject->id] ?? null;
                                    @endphp
                                    <td class="p-2.5 border border-slate-300">
                                        @if(!$cell || $cell['status'] === 'pending')
                                            <span class="text-slate-400 font-mono">—</span>
                                        @elseif($cell['is_absent'])
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-700">AB</span>
                                        @else
                                            <span class="font-bold {{ $cell['is_passed'] ? 'text-emerald-700' : 'text-rose-700 font-black' }}">
                                                {{ (float) $cell['obtained'] == (int) $cell['obtained'] ? (int) $cell['obtained'] : number_format($cell['obtained'], 1) }}
                                            </span>
                                        @endif
                                    </td>
                                @endforeach

                                <!-- Exam Aggregates -->
                                <td class="p-2.5 border border-slate-300 font-bold text-slate-900 bg-slate-50">
                                    @if($summary && !$summary['has_missing'])
                                        {{ (float) $summary['total_obtained'] == (int) $summary['total_obtained'] ? (int) $summary['total_obtained'] : number_format($summary['total_obtained'], 1) }}
                                        <span class="text-slate-400 font-normal">/ {{ (int) $summary['total_max'] }}</span>
                                    @else
                                        <span class="text-slate-400 font-mono">—</span>
                                    @endif
                                </td>

                                <td class="p-2.5 border border-slate-300 font-bold text-slate-900 bg-slate-50">
                                    {{ $summary && !$summary['has_missing'] ? $summary['percentage'] . '%' : '—' }}
                                </td>

                                <td class="p-2.5 border border-slate-300 font-bold text-slate-900 bg-slate-50">
                                    {{ $summary && !$summary['has_missing'] ? $summary['grade'] : '—' }}
                                </td>

                                <td class="p-2.5 border border-slate-300">
                                    @if(!$summary || $summary['has_missing'])
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200/60">PENDING</span>
                                    @elseif($summary['result'] === 'PASS')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60">PASS</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200/60">FAIL</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($subjects) + 5 }}" class="p-8 text-center text-slate-400">
                                    No examination records available for this student.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    <!-- Table Footer: Subject Cumulative Performance -->
                    @if(count($exams) > 1)
                        <tfoot>
                            <tr class="bg-slate-100/80 border-t-2 border-slate-300 font-bold text-slate-800">
                                <td class="p-2.5 text-left border border-slate-300 font-bold uppercase text-[11px]">
                                    Subject Avg
                                </td>
                                @foreach($subjects as $subject)
                                    @php
                                        $subSummary = $subjectSummaries[$subject->id] ?? null;
                                    @endphp
                                    <td class="p-2.5 border border-slate-300">
                                        @if($subSummary && $subSummary['count_entered'] > 0)
                                            <span class="font-bold text-slate-900">{{ $subSummary['percentage'] }}%</span>
                                            <span class="block text-[10px] text-slate-500 font-normal">({{ $subSummary['grade'] }})</span>
                                        @else
                                            <span class="text-slate-400 font-mono">—</span>
                                        @endif
                                    </td>
                                @endforeach

                                <td class="p-2.5 border border-slate-300 font-black text-slate-900 bg-slate-200/80">
                                    {{ $grandTotalObtained }} / {{ $grandTotalMax }}
                                </td>
                                <td class="p-2.5 border border-slate-300 font-black text-slate-900 bg-slate-200/80">
                                    {{ $grandPercentage }}%
                                </td>
                                <td class="p-2.5 border border-slate-300 font-black text-slate-900 bg-slate-200/80">
                                    {{ $overallGrade }}
                                </td>
                                <td class="p-2.5 border border-slate-300">
                                    @if($overallResult === 'PASS')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60">PASS</span>
                                    @elseif($overallResult === 'FAIL')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200/60">FAIL</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200/60">PENDING</span>
                                    @endif
                                </td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>

            <!-- 4. Grand Cumulative Summary Box -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-4 rounded-xl border border-slate-200 bg-slate-50 text-center">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 block">Total Marks</span>
                    <p class="text-lg font-black text-slate-900 mt-0.5">{{ $grandTotalObtained }} <span class="text-xs font-normal text-slate-500">/ {{ $grandTotalMax }}</span></p>
                </div>
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 block">Aggregate Percentage</span>
                    <p class="text-lg font-black text-blue-900 mt-0.5">{{ $grandPercentage }}%</p>
                </div>
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 block">Cumulative Grade</span>
                    <p class="text-lg font-black text-slate-900 mt-0.5">{{ $overallGrade }}</p>
                </div>
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 block">Overall Result</span>
                    <div class="mt-1">
                        @if($overallResult === 'PASS')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">PASS</span>
                        @elseif($overallResult === 'FAIL')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800">FAIL</span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">PENDING</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- 5. Standardized Institutional Signatures Block -->
            <div class="grid grid-cols-3 gap-8 pt-8 mt-6 border-t border-slate-200 text-center">
                <div>
                    <div class="w-3/4 mx-auto border-t border-slate-900 mb-2"></div>
                    <span class="text-xs font-bold text-slate-900 uppercase">Class Teacher / In-Charge</span>
                </div>
                <div>
                    <div class="w-3/4 mx-auto border-t border-slate-900 mb-2"></div>
                    <span class="text-xs font-bold text-slate-900 uppercase">Examination Coordinator</span>
                </div>
                <div>
                    <div class="w-3/4 mx-auto border-t border-slate-900 mb-2"></div>
                    <span class="text-xs font-bold text-slate-900 uppercase">Principal / Headmaster</span>
                </div>
            </div>

            <!-- 6. Footer Note -->
            <p class="text-[11px] text-slate-400 text-center pt-2">
                Official computer-generated marks sheet issued by {{ $schoolName }}. Generated on {{ now()->format('d/m/Y, h:i A') }}.
            </p>
        </div>
    </div>

    <!-- Print Stylesheet -->
    <style>
        @media print {
            body {
                background: white !important;
                color: #0f172a !important;
                font-size: 10pt !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .no-print,
            header,
            nav,
            aside,
            #sidebar,
            .sidebar {
                display: none !important;
            }

            .print-container {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
            }

            table {
                width: 100% !important;
                border-collapse: collapse !important;
                page-break-inside: auto;
            }

            thead tr {
                background-color: #1e3a8a !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            thead th {
                color: #ffffff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            tr {
                page-break-inside: avoid;
            }

            @page {
                size: A4 portrait;
                margin: 10mm 12mm 10mm 12mm;
            }
        }
    </style>
</x-app-layout>