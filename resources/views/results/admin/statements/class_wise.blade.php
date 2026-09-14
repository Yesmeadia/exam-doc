<x-app-layout>
    <x-slot name="title">{{ __('Class Wise Statement of Examination Marks') }}</x-slot>

    <div class="w-full space-y-6" x-data="{
        classes: {{ Js::from($classes) }},
        selectedClassId: '{{ $selectedClassId }}',
        selectedSectionId: '{{ $selectedSectionId ?? 'all' }}',
        sections: [],
        searchQuery: '',
        statusFilter: 'all',
        updateSections() {
            const found = this.classes.find(c => String(c.id) === String(this.selectedClassId));
            this.sections = (found && found.sections) ? found.sections : [];
        },
        matchesFilter(student) {
            const matchesSearch = !this.searchQuery ||
                String(student.roll_no).toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                String(student.student_id).toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                String(student.student_name).toLowerCase().includes(this.searchQuery.toLowerCase());

            if (!matchesSearch) return false;

            if (this.statusFilter === 'all') return true;
            if (this.statusFilter === 'pass') return student.result === 'PASS';
            if (this.statusFilter === 'fail') return student.result === 'FAIL';
            if (this.statusFilter === 'pending') return student.result === 'PENDING';

            return true;
        }
    }" x-init="updateSections()">

        <!-- Top Action Bar (Hidden in Print) -->
        <div class="no-print flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200">
            <div>
                <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Class Wise Statement</h2>
            </div>

            @if($statementData && count($statementData['students']) > 0)
                <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                    <!-- Browser Landscape Print Button -->
                    <button type="button" onclick="window.print()"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold uppercase tracking-wider shadow-sm transition">
                        <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Print Statement
                    </button>

                    <!-- PDF Download -->
                    <form method="GET" action="{{ route('admin.class-wise-statement.pdf') }}" target="_blank">
                        <input type="hidden" name="exam_id" value="{{ $selectedExamId }}">
                        <input type="hidden" name="class_id" value="{{ $selectedClassId }}">
                        <input type="hidden" name="section_id" value="{{ $selectedSectionId ?? 'all' }}">
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold uppercase tracking-wider shadow-sm transition">
                            <svg class="w-4 h-4 text-rose-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Download PDF
                        </button>
                    </form>

                    <!-- Excel Download -->
                    <form method="GET" action="{{ route('admin.class-wise-statement.excel') }}">
                        <input type="hidden" name="exam_id" value="{{ $selectedExamId }}">
                        <input type="hidden" name="class_id" value="{{ $selectedClassId }}">
                        <input type="hidden" name="section_id" value="{{ $selectedSectionId ?? 'all' }}">
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold uppercase tracking-wider shadow-sm transition">
                            <svg class="w-4 h-4 text-emerald-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Export Excel (.xlsx)
                        </button>
                    </form>
                </div>
            @endif
        </div>

        <x-alert />

        <!-- Filter Card (Hidden in Print) -->
        <div class="no-print bg-white rounded-2xl shadow-sm border border-slate-200/80 p-5 sm:p-6 w-full">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-700">Filter Examination & Class Criteria</span>
                </div>
                @if($statementData)
                    <span class="text-xs text-slate-500 font-medium">
                        Showing: <strong class="text-slate-800">{{ $statementData['class']->name }}</strong> ({{ $statementData['section_title'] }}) • <strong class="text-slate-800">{{ $statementData['exam']->exam_name }}</strong>
                    </span>
                @endif
            </div>

            <form method="GET" action="{{ route('admin.class-wise-statement.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                <!-- Exam Selector -->
                <div>
                    <label for="exam_id" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Examination
                    </label>
                    <div class="relative">
                        <select id="exam_id" name="exam_id"
                            class="block w-full rounded-xl border-slate-300 shadow-xs focus:border-indigo-500 focus:ring-indigo-500 text-sm font-semibold text-slate-800 py-2.5 pl-3 pr-8"
                            required>
                            @foreach($exams as $exam)
                                <option value="{{ $exam->id }}" {{ $selectedExamId == $exam->id ? 'selected' : '' }}>
                                    {{ $exam->exam_name }} @if($exam->academicYear) ({{ $exam->academicYear->name }}) @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Class Selector -->
                <div>
                    <label for="class_id" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        School Class
                    </label>
                    <div class="relative">
                        <select id="class_id" name="class_id"
                            x-model="selectedClassId" @change="updateSections()"
                            class="block w-full rounded-xl border-slate-300 shadow-xs focus:border-indigo-500 focus:ring-indigo-500 text-sm font-semibold text-slate-800 py-2.5 pl-3 pr-8"
                            required>
                            @foreach($classes as $c)
                                <option value="{{ $c->id }}" {{ $selectedClassId == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Section Selector -->
                <div>
                    <label for="section_id" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Section / Stream
                    </label>
                    <div class="relative">
                        <select id="section_id" name="section_id"
                            x-model="selectedSectionId"
                            class="block w-full rounded-xl border-slate-300 shadow-xs focus:border-indigo-500 focus:ring-indigo-500 text-sm font-semibold text-slate-800 py-2.5 pl-3 pr-8">
                            <option value="all">-- All Sections / Streams --</option>
                            <template x-for="s in sections" :key="s.id">
                                <option :value="s.id" x-text="s.name" :selected="String(s.id) === String(selectedSectionId)"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white rounded-xl text-sm font-bold shadow-sm transition-all hover:shadow hover:-translate-y-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Generate Statement
                    </button>
                </div>
            </form>
        </div>

        @if($statementData)
            <!-- Printable Statement Area -->
            <div class="print-container space-y-5">
                <div class="print-only hidden text-center mb-3">
                    <h1 class="text-base font-bold uppercase">{{ $statementData['school_name'] }}</h1>
                    <p class="text-xs">{{ $statementData['exam']->exam_name }} • Class: {{ $statementData['class']->name }} ({{ $statementData['section_title'] }})</p>
                </div>

                <!-- KPI Stat Cards Container (Hidden in Print) -->
                <div class="no-print bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80">
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3.5">
                        <!-- Enrolled -->
                        <div class="group relative overflow-hidden bg-gradient-to-br from-slate-50 to-slate-100/70 border border-slate-200/80 rounded-2xl p-4 transition hover:shadow-md">
                            <div class="flex items-center justify-between text-slate-400 mb-1">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Enrolled</span>
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                            <p class="text-2xl font-black text-slate-900 tracking-tight">{{ $statementData['analytics']['total_enrolled'] }}</p>
                            <span class="text-[10px] text-slate-500 font-medium">Candidates</span>
                        </div>

                        <!-- Appeared -->
                        <div class="group relative overflow-hidden bg-gradient-to-br from-sky-50 to-blue-50/60 border border-sky-100 rounded-2xl p-4 transition hover:shadow-md">
                            <div class="flex items-center justify-between text-sky-500 mb-1">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-sky-700">Appeared</span>
                                <svg class="w-4 h-4 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <p class="text-2xl font-black text-sky-950 tracking-tight">{{ $statementData['analytics']['appeared'] }}</p>
                            <span class="text-[10px] text-sky-600 font-medium">With recorded marks</span>
                        </div>

                        <!-- Passed -->
                        <div class="group relative overflow-hidden bg-gradient-to-br from-emerald-50 to-teal-50/60 border border-emerald-100 rounded-2xl p-4 transition hover:shadow-md">
                            <div class="flex items-center justify-between text-emerald-500 mb-1">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700">Passed</span>
                                <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <p class="text-2xl font-black text-emerald-900 tracking-tight">{{ $statementData['analytics']['passed'] }}</p>
                            <span class="text-[10px] text-emerald-600 font-medium">All subjects cleared</span>
                        </div>

                        <!-- Failed -->
                        <div class="group relative overflow-hidden bg-gradient-to-br from-rose-50 to-red-50/60 border border-rose-100 rounded-2xl p-4 transition hover:shadow-md">
                            <div class="flex items-center justify-between text-rose-500 mb-1">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-rose-700">Failed</span>
                                <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </div>
                            <p class="text-2xl font-black text-rose-900 tracking-tight">{{ $statementData['analytics']['failed'] }}</p>
                            <span class="text-[10px] text-rose-600 font-medium">Below pass criteria</span>
                        </div>

                        <!-- Pass Rate -->
                        <div class="group relative overflow-hidden bg-gradient-to-br from-indigo-50 to-purple-50/60 border border-indigo-100 rounded-2xl p-4 transition hover:shadow-md">
                            <div class="flex items-center justify-between text-indigo-500 mb-1">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-700">Pass Rate</span>
                                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                            </div>
                            <p class="text-2xl font-black text-indigo-950 tracking-tight">{{ $statementData['analytics']['pass_rate'] }}%</p>
                            <span class="text-[10px] text-indigo-600 font-medium">Success index</span>
                        </div>

                        <!-- Class Average -->
                        <div class="group relative overflow-hidden bg-gradient-to-br from-amber-50 to-yellow-50/60 border border-amber-100 rounded-2xl p-4 transition hover:shadow-md">
                            <div class="flex items-center justify-between text-amber-500 mb-1">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-amber-700">Class Avg</span>
                                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                </svg>
                            </div>
                            <p class="text-2xl font-black text-amber-950 tracking-tight">{{ $statementData['analytics']['average_percentage'] }}%</p>
                            <span class="text-[10px] text-amber-700 font-medium">Average score</span>
                        </div>
                    </div>

                    <!-- Topper Highlight (If exists) -->
                    @if($statementData['analytics']['top_scorer'])
                        @php $topper = $statementData['analytics']['top_scorer']; @endphp
                        <div class="no-print mt-4 p-3.5 rounded-xl bg-gradient-to-r from-amber-500/10 via-amber-50 to-yellow-50/50 border border-amber-200/80 flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-amber-500 text-white flex items-center justify-center font-black text-sm shadow-sm">
                                    ★
                                </div>
                                <div>
                                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-amber-800">Class Top Scorer (Rank #1)</span>
                                    <p class="text-sm font-black text-slate-900 leading-tight">
                                        {{ $topper['student_name'] }}
                                        <span class="text-xs font-mono font-bold text-indigo-700 ms-1.5">({{ $topper['student_id'] }})</span>
                                        <span class="text-xs font-normal text-slate-500 ms-1">• Roll {{ $topper['roll_no'] }}</span>
                                    </p>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="text-lg font-black text-amber-700">{{ $topper['percentage'] }}%</span>
                                <p class="text-[10px] font-semibold text-slate-500">{{ $topper['total_obtained'] }} / {{ $topper['total_max'] }} Marks</p>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Table Controls & Live Search Strip (Hidden in Print) -->
                <div class="no-print bg-white rounded-2xl shadow-sm border border-slate-200/80 p-4 flex flex-col sm:flex-row items-center justify-between gap-3.5">
                    <!-- Status Filter Tabs -->
                    <div class="flex items-center gap-1.5 overflow-x-auto w-full sm:w-auto pb-1 sm:pb-0">
                        <button type="button" @click="statusFilter = 'all'"
                            :class="statusFilter === 'all' ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700'"
                            class="px-3 py-1.5 rounded-lg text-xs font-bold transition whitespace-nowrap">
                            All Students ({{ $statementData['analytics']['total_enrolled'] }})
                        </button>
                        <button type="button" @click="statusFilter = 'pass'"
                            :class="statusFilter === 'pass' ? 'bg-emerald-700 text-white shadow-xs' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200/60'"
                            class="px-3 py-1.5 rounded-lg text-xs font-bold transition whitespace-nowrap">
                            Passed ({{ $statementData['analytics']['passed'] }})
                        </button>
                        <button type="button" @click="statusFilter = 'fail'"
                            :class="statusFilter === 'fail' ? 'bg-rose-700 text-white shadow-xs' : 'bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200/60'"
                            class="px-3 py-1.5 rounded-lg text-xs font-bold transition whitespace-nowrap">
                            Failed ({{ $statementData['analytics']['failed'] }})
                        </button>
                        <button type="button" @click="statusFilter = 'pending'"
                            :class="statusFilter === 'pending' ? 'bg-amber-600 text-white shadow-xs' : 'bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200/60'"
                            class="px-3 py-1.5 rounded-lg text-xs font-bold transition whitespace-nowrap">
                            Pending ({{ $statementData['analytics']['pending'] }})
                        </button>
                    </div>

                    <!-- Live Search Box -->
                    <div class="relative w-full sm:w-72">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" x-model="searchQuery" placeholder="Filter by Name, Roll, or ID..."
                            class="block w-full pl-9 pr-3 py-1.5 text-xs rounded-xl border border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 shadow-2xs font-medium text-slate-800 placeholder-slate-400">
                    </div>
                </div>

                <!-- Broadsheet Statement Matrix Table (Elevated Design) -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden w-full">
                    <div class="overflow-x-auto w-full max-h-[750px] relative">
                        <table class="w-full text-xs border-collapse divide-y divide-slate-200">
                            <!-- Sticky Table Header (Institutional Navy #1e3a8a) -->
                            <thead class="sticky top-0 z-20 shadow-xs">
                                <tr class="text-white font-bold text-center" style="background-color: #1e3a8a;">
                                    <th rowspan="2" class="px-3.5 py-3 text-center font-bold uppercase tracking-wider border-r border-blue-950 sticky left-0 z-30 shadow-r" style="background-color: #1e3a8a;">
                                        Roll
                                    </th>
                                    <th rowspan="2" class="px-4 py-3 text-left font-bold uppercase tracking-wider border-r border-blue-950 min-w-[160px]" style="background-color: #1e3a8a;">
                                        Candidate Name
                                    </th>
                                    @if(!$selectedSectionId)
                                        <th rowspan="2" class="px-3 py-3 text-center font-bold uppercase tracking-wider border-r border-blue-950" style="background-color: #1e3a8a;">
                                            Stream / Sec
                                        </th>
                                    @endif

                                    <!-- Subject / Optional Group Columns -->
                                    @foreach($statementData['columns'] as $col)
                                        <th class="px-3 py-2.5 text-center font-bold uppercase tracking-wider border-r border-blue-950 whitespace-nowrap {{ $col['is_optional_group'] ? 'bg-indigo-950 text-indigo-100' : '' }}" style="{{ !$col['is_optional_group'] ? 'background-color: #1e3a8a;' : '' }}">
                                            <div class="flex items-center justify-center gap-1.5">
                                                <span>{{ $col['title'] }}</span>
                                            </div>
                                        </th>
                                    @endforeach

                                    <!-- Result & Rank Headers -->
                                    <th rowspan="2" class="px-3.5 py-3 text-center font-bold uppercase tracking-wider border-r border-slate-900 bg-slate-900" style="background-color: #0f172a;">
                                        Obtained
                                    </th>
                                    <th rowspan="2" class="px-2.5 py-3 text-center font-bold uppercase tracking-wider border-r border-slate-900 bg-slate-900 text-slate-300" style="background-color: #0f172a;">
                                        Max
                                    </th>
                                    <th rowspan="2" class="px-3.5 py-3 text-center font-bold uppercase tracking-wider border-r border-slate-900 bg-slate-900 text-indigo-200" style="background-color: #0f172a;">
                                        Percentage
                                    </th>
                                    <th rowspan="2" class="px-3 py-3 text-center font-bold uppercase tracking-wider border-r border-slate-900 bg-slate-900" style="background-color: #0f172a;">
                                        Grade
                                    </th>
                                    <th rowspan="2" class="px-3.5 py-3 text-center font-bold uppercase tracking-wider border-r border-slate-900 bg-slate-900" style="background-color: #0f172a;">
                                        Result
                                    </th>
                                    <th rowspan="2" class="px-3 py-3 text-center font-bold uppercase tracking-wider bg-slate-900" style="background-color: #0f172a;">
                                        Rank
                                    </th>
                                </tr>
                                <tr class="text-blue-100 text-[10px] text-center border-b border-blue-950" style="background-color: #172554;">
                                    @foreach($statementData['columns'] as $col)
                                        <th class="px-2.5 py-1.5 border-r border-blue-950/60 font-semibold {{ $col['is_optional_group'] ? 'bg-indigo-900/60 text-indigo-200' : '' }}">
                                            Max: {{ (int) $col['maximum_marks'] }} | Pass: {{ (int) $col['pass_marks'] }}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>

                            <!-- Table Body -->
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @forelse($statementData['students'] as $st)
                                    <tr x-show="matchesFilter({{ Js::from($st) }})"
                                        class="hover:bg-indigo-50/50 transition-colors duration-100">
                                        <!-- Roll No (Sticky) -->
                                        <td class="px-3.5 py-2.5 whitespace-nowrap text-center font-black text-slate-900 border-r border-slate-100 bg-white sticky left-0 z-10">
                                            {{ $st['roll_no'] }}
                                        </td>

                                        <!-- Candidate Name -->
                                        <td class="px-4 py-2.5 whitespace-nowrap text-left font-bold text-slate-900 border-r border-slate-100">
                                            {{ $st['student_name'] }}
                                        </td>

                                        <!-- Section (if all sections viewed) -->
                                        @if(!$selectedSectionId)
                                            <td class="px-3 py-2.5 whitespace-nowrap text-center text-slate-600 font-medium border-r border-slate-100">
                                                <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold {{ str_contains(strtolower($st['section_name']), 'sci') ? 'bg-sky-50 text-sky-700' : 'bg-purple-50 text-purple-700' }}">
                                                    {{ $st['section_name'] }}
                                                </span>
                                            </td>
                                        @endif

                                        <!-- Evaluated Columns (Single or Merged Optional Group) -->
                                        @foreach($statementData['columns'] as $colKey => $col)
                                            @php
                                                $colData = $st['columns'][$colKey] ?? null;
                                                $display = $colData ? $colData['display'] : '—';
                                                $status = $colData ? $colData['status'] : 'pending';
                                                $isPassed = $colData ? $colData['is_passed'] : false;
                                                $tag = $colData ? $colData['tag'] : null;

                                                // Color-code tag badge
                                                $tagClass = match(strtolower($tag ?? '')) {
                                                    'edu' => 'bg-purple-100 text-purple-800 border-purple-200',
                                                    'math' => 'bg-blue-100 text-blue-800 border-blue-200',
                                                    'bio' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                                    'evs' => 'bg-teal-100 text-teal-800 border-teal-200',
                                                    'isl' => 'bg-amber-100 text-amber-800 border-amber-200',
                                                    default => 'bg-slate-100 text-slate-700 border-slate-200',
                                                };
                                            @endphp
                                            <td class="px-3 py-2.5 whitespace-nowrap text-center font-semibold border-r border-slate-100 {{ $col['is_optional_group'] ? 'bg-indigo-50/20' : '' }}">
                                                @if($status === 'absent')
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[11px] font-black bg-rose-100 text-rose-700">
                                                        AB
                                                    </span>
                                                    @if($tag)
                                                        <span class="inline-block ms-1 px-1 py-0.2 rounded text-[9px] font-bold border {{ $tagClass }}">
                                                            {{ $tag }}
                                                        </span>
                                                    @endif
                                                @elseif($status === 'entered')
                                                    <span class="text-sm font-black {{ $isPassed ? 'text-slate-900' : 'text-rose-600' }}">
                                                        {{ $display }}
                                                    </span>
                                                    @if($tag)
                                                        <span class="inline-block ms-1 px-1 py-0.2 rounded text-[9px] font-bold border {{ $tagClass }}" title="Selected: {{ $colData['subject_name'] ?? $tag }}">
                                                            {{ $tag }}
                                                        </span>
                                                    @endif
                                                @elseif($status === 'not_applicable')
                                                    <span class="text-slate-300 font-normal select-none" title="Not applicable to student's stream">—</span>
                                                @else
                                                    <span class="text-slate-300 font-bold">—</span>
                                                    @if($tag)
                                                        <span class="inline-block ms-1 px-1 py-0.2 rounded text-[9px] font-bold border {{ $tagClass }}">
                                                            {{ $tag }}
                                                        </span>
                                                    @endif
                                                @endif
                                            </td>
                                        @endforeach

                                        <!-- Total Obtained -->
                                        <td class="px-3.5 py-2.5 whitespace-nowrap text-center font-black text-slate-900 border-r border-slate-100 bg-slate-50/60">
                                            {{ $st['has_appeared'] ? $st['total_obtained'] : '—' }}
                                        </td>

                                        <!-- Max Marks -->
                                        <td class="px-2.5 py-2.5 whitespace-nowrap text-center font-medium text-slate-500 border-r border-slate-100 bg-slate-50/60">
                                            {{ $st['total_max'] }}
                                        </td>

                                        <!-- Percentage (with color threshold) -->
                                        @php
                                            $pct = (float) $st['percentage'];
                                            $pctColor = match(true) {
                                                $pct >= 75 => 'text-emerald-700 bg-emerald-50/70',
                                                $pct >= 50 => 'text-indigo-700 bg-indigo-50/70',
                                                $pct >= 33 => 'text-amber-700 bg-amber-50/70',
                                                default => 'text-rose-700 bg-rose-50/70',
                                            };
                                        @endphp
                                        <td class="px-3.5 py-2.5 whitespace-nowrap text-center font-black border-r border-slate-100 {{ $st['has_appeared'] ? $pctColor : 'bg-slate-50/40 text-slate-400' }}">
                                            @if($st['has_appeared'])
                                                {{ $st['percentage'] }}%
                                            @else
                                                —
                                            @endif
                                        </td>

                                        <!-- Grade -->
                                        <td class="px-3 py-2.5 whitespace-nowrap text-center font-bold border-r border-slate-100">
                                            @if($st['has_appeared'])
                                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-md text-xs font-black {{ in_array($st['grade'], ['A+', 'A']) ? 'bg-emerald-100 text-emerald-800' : (in_array($st['grade'], ['B+', 'B']) ? 'bg-blue-100 text-blue-800' : ($st['grade'] === 'F' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800')) }}">
                                                    {{ $st['grade'] }}
                                                </span>
                                            @else
                                                <span class="text-slate-400">—</span>
                                            @endif
                                        </td>

                                        <!-- Result Badge -->
                                        <td class="px-3.5 py-2.5 whitespace-nowrap text-center border-r border-slate-100">
                                            @if($st['result'] === 'PASS')
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-black bg-emerald-100 text-emerald-800 shadow-2xs">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                                    PASS
                                                </span>
                                            @elseif($st['result'] === 'FAIL')
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-black bg-rose-100 text-rose-800 shadow-2xs">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                                                    FAIL
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 shadow-2xs">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                    PENDING
                                                </span>
                                            @endif
                                        </td>

                                        <!-- Class Rank -->
                                        <td class="px-3 py-2.5 whitespace-nowrap text-center font-black">
                                            @if($st['rank'])
                                                @if($st['rank'] === 1)
                                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-gradient-to-tr from-amber-500 to-yellow-400 text-amber-950 font-black text-xs shadow-sm ring-2 ring-amber-300" title="1st Position in Class">
                                                        🥇 1
                                                    </span>
                                                @elseif($st['rank'] === 2)
                                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-gradient-to-tr from-slate-300 to-slate-200 text-slate-900 font-black text-xs shadow-sm ring-2 ring-slate-300" title="2nd Position">
                                                        🥈 2
                                                    </span>
                                                @elseif($st['rank'] === 3)
                                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-gradient-to-tr from-amber-700 to-amber-600 text-white font-black text-xs shadow-sm ring-2 ring-amber-500" title="3rd Position">
                                                        🥉 3
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 font-bold text-xs">
                                                        #{{ $st['rank'] }}
                                                    </span>
                                                @endif
                                            @else
                                                <span class="text-slate-300">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ 4 + count($statementData['columns']) + 6 + (!$selectedSectionId ? 1 : 0) }}" class="px-6 py-16 text-center text-slate-400">
                                            <svg class="w-10 h-10 mx-auto mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <p class="text-sm font-semibold text-slate-600">No enrolled students found</p>
                                            <p class="text-xs text-slate-400 mt-0.5">There are no candidates matching the selected examination and class/section.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>

                            <!-- Column Statistics Footer -->
                            @if(count($statementData['students']) > 0)
                                <tfoot class="border-t-2 border-slate-300 font-bold divide-y divide-slate-200">
                                    <!-- Subject Average Row -->
                                    <tr class="bg-slate-100 text-slate-800">
                                        <td colspan="{{ 3 + (!$selectedSectionId ? 1 : 0) }}" class="px-4 py-3 text-left uppercase text-[11px] tracking-wider font-black text-slate-700 bg-slate-100 sticky left-0 z-10">
                                            Subject Average
                                        </td>
                                        @foreach($statementData['columns'] as $colKey => $col)
                                            @php $stat = $statementData['column_stats'][$colKey] ?? null; @endphp
                                            <td class="px-3 py-3 text-center font-black text-slate-900 {{ $col['is_optional_group'] ? 'bg-indigo-50/50' : '' }}">
                                                {{ $stat['average'] ?? '—' }}
                                            </td>
                                        @endforeach
                                        <td colspan="6" class="px-4 py-3 text-center text-xs font-bold text-slate-700 bg-slate-100">
                                            Class Overall Average: <span class="font-black text-indigo-700 text-sm ms-1">{{ $statementData['analytics']['average_percentage'] }}%</span>
                                        </td>
                                    </tr>

                                    <!-- Pass Rate Row -->
                                    <tr class="bg-slate-50 text-slate-700">
                                        <td colspan="{{ 3 + (!$selectedSectionId ? 1 : 0) }}" class="px-4 py-2.5 text-left uppercase text-[10px] tracking-wider font-extrabold text-slate-500 bg-slate-50 sticky left-0 z-10">
                                            Pass Rate (%)
                                        </td>
                                        @foreach($statementData['columns'] as $colKey => $col)
                                            @php $stat = $statementData['column_stats'][$colKey] ?? null; @endphp
                                            <td class="px-3 py-2.5 text-center font-black text-emerald-700 {{ $col['is_optional_group'] ? 'bg-indigo-50/30' : '' }}">
                                                {{ $stat['pass_rate'] ?? 0 }}%
                                            </td>
                                        @endforeach
                                        <td colspan="6" class="px-4 py-2.5 text-center text-xs font-bold text-emerald-700 bg-slate-50">
                                            Class Pass Rate: <span class="font-black text-sm ms-1">{{ $statementData['analytics']['pass_rate'] }}%</span>
                                        </td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Print Specific CSS (Landscape A4) -->
    <style>
        @media print {
            body {
                background: white !important;
                color: black !important;
                font-size: 8pt !important;
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

            .print-only {
                display: block !important;
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
                font-size: 7pt !important;
            }

            th,
            td {
                border: 0.5pt solid #333 !important;
                padding: 3px 4px !important;
                color: #000 !important;
            }

            thead tr {
                background-color: #e2e8f0 !important;
                color: #000 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            tfoot tr {
                background-color: #f1f5f9 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            tr {
                page-break-inside: avoid;
            }

            @page {
                size: A4 landscape;
                margin: 8mm;
            }
        }
    </style>
</x-app-layout>
