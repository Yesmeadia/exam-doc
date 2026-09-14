<x-app-layout>
    <div class="w-full space-y-7">
        <!-- Top Action Bar -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-2 border-b border-slate-200">
            <div>
                <h2 class="font-extrabold text-xl sm:text-2xl text-slate-900 tracking-tight">
                    Welcome back, {{ Auth::user()->name }}
                </h2>
                <div class="flex items-center gap-2 text-xs text-slate-500 mt-1 font-medium">
                    <span>Academic Session:</span>
                    <span class="inline-flex items-center gap-1 font-semibold text-indigo-700 bg-indigo-50/70 px-2 py-0.5 rounded-md border border-indigo-100/60">
                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-600"></span>
                        {{ $activeYear?->name ?? 'No Active Session' }}
                    </span>
                </div>
            </div>

            <!-- Exam Selector Filter -->
            <div class="flex items-center gap-3">
                <form method="GET" action="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
                    @if($selectedYearId)
                        <input type="hidden" name="academic_year_id" value="{{ $selectedYearId }}">
                    @endif
                    <div class="relative flex items-center">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <select name="exam_id" onchange="this.form.submit()"
                            class="pl-9 pr-9 py-2 text-xs font-semibold rounded-xl border border-slate-200 bg-white hover:border-slate-300 focus:bg-white text-slate-700 shadow-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition cursor-pointer">
                            <option value="">-- Select Active Exam --</option>
                            @foreach($exams as $exam)
                                <option value="{{ $exam->id }}" {{ $selectedExam?->id === $exam->id ? 'selected' : '' }}>
                                    {{ $exam->exam_name }} ({{ ucfirst($exam->status) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <x-alert />

        <!-- KPI Metric Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <!-- Total Students -->
            <a href="{{ route('admin.students.index') }}" class="stat-card group">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 group-hover:text-blue-600 transition">
                            Total Students
                        </p>
                        <p class="text-2xl font-extrabold text-slate-900 mt-1.5 tracking-tight">
                            {{ number_format($totalStudents) }}
                        </p>
                    </div>
                    <div class="stat-icon stat-icon-blue">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                    <span class="inline-flex items-center gap-1 font-medium">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Active Enrollments
                    </span>
                    <span class="text-blue-600 group-hover:translate-x-0.5 transition-transform font-bold">&rarr;</span>
                </div>
            </a>

            <!-- Teachers -->
            <a href="{{ route('admin.teachers.index') }}" class="stat-card group">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 group-hover:text-emerald-600 transition">
                            Faculty Staff
                        </p>
                        <p class="text-2xl font-extrabold text-slate-900 mt-1.5 tracking-tight">
                            {{ number_format($totalTeachers) }}
                        </p>
                    </div>
                    <div class="stat-icon stat-icon-emerald">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                    <span class="inline-flex items-center gap-1 font-medium">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active Teachers
                    </span>
                    <span class="text-emerald-600 group-hover:translate-x-0.5 transition-transform font-bold">&rarr;</span>
                </div>
            </a>

            <!-- Classes -->
            <a href="{{ route('admin.classes.index') }}" class="stat-card group">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 group-hover:text-violet-600 transition">
                            Classes & Sections
                        </p>
                        <p class="text-2xl font-extrabold text-slate-900 mt-1.5 tracking-tight">
                            {{ number_format($totalClasses) }}
                        </p>
                    </div>
                    <div class="stat-icon stat-icon-violet">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                    <span class="inline-flex items-center gap-1 font-medium">
                        <span class="w-1.5 h-1.5 rounded-full bg-violet-500"></span> Academic Levels
                    </span>
                    <span class="text-violet-600 group-hover:translate-x-0.5 transition-transform font-bold">&rarr;</span>
                </div>
            </a>

            <!-- Subjects -->
            <a href="{{ route('admin.subjects.index') }}" class="stat-card group">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 group-hover:text-amber-600 transition">
                            Curriculum Subjects
                        </p>
                        <p class="text-2xl font-extrabold text-slate-900 mt-1.5 tracking-tight">
                            {{ number_format($totalSubjects) }}
                        </p>
                    </div>
                    <div class="stat-icon stat-icon-amber">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                    <span class="inline-flex items-center gap-1 font-medium">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Active Courses
                    </span>
                    <span class="text-amber-600 group-hover:translate-x-0.5 transition-transform font-bold">&rarr;</span>
                </div>
            </a>

            <!-- Assignments -->
            <a href="{{ route('admin.assignments.index') }}" class="stat-card group">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 group-hover:text-pink-600 transition">
                            Evaluator Mappings
                        </p>
                        <p class="text-2xl font-extrabold text-slate-900 mt-1.5 tracking-tight">
                            {{ number_format($totalAssignments) }}
                        </p>
                    </div>
                    <div class="stat-icon stat-icon-rose">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                    <span class="inline-flex items-center gap-1 font-medium">
                        <span class="w-1.5 h-1.5 rounded-full bg-pink-500"></span> Assigned Pairs
                    </span>
                    <span class="text-pink-600 group-hover:translate-x-0.5 transition-transform font-bold">&rarr;</span>
                </div>
            </a>
        </div>

        <!-- Mark Entry Progress Widget -->
        @if($selectedExam)
            <div class="bg-white rounded-xl p-6 sm:p-7 border border-slate-200/80 shadow-sm">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 pb-5 border-b border-slate-100">
                    <div>
                        <div class="flex items-center gap-2.5">
                            <div class="w-2.5 h-2.5 rounded-full bg-indigo-600"></div>
                            <h3 class="text-base font-extrabold text-slate-900 tracking-tight">
                                Mark Evaluation Status: <span class="text-indigo-600">{{ $selectedExam->exam_name }}</span>
                            </h3>
                        </div>
                        <p class="text-xs text-slate-500 mt-1 pl-5">
                            Live evaluation tracking across all {{ $totalAssignments }} assigned class-subject examination rolls.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-emerald-50 border border-emerald-100 text-xs font-semibold text-emerald-800">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span>Submitted: <strong>{{ $submittedPercent }}%</strong> ({{ $submittedCount }}/{{ $totalAssignments }})</span>
                        </div>

                        <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-amber-50 border border-amber-100 text-xs font-semibold text-amber-800">
                            <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                            <span>Pending: <strong>{{ $pendingPercent }}%</strong> ({{ count($pendingAssignments) }}/{{ $totalAssignments }})</span>
                        </div>

                        <a href="{{ route('admin.marks.index') }}"
                           class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold text-indigo-700 bg-indigo-50 hover:bg-indigo-600 hover:text-white border border-indigo-200/60 transition shadow-sm">
                            <span>Open Mark Matrix</span>
                            <span>&rarr;</span>
                        </a>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="mt-5 space-y-2">
                    <div class="w-full bg-slate-100 rounded-full h-3.5 overflow-hidden flex shadow-inner p-0.5">
                        <div class="bg-emerald-500 h-full rounded-full transition-all duration-700 shadow-sm"
                             style="width: {{ $submittedPercent }}%"
                             title="Submitted: {{ $submittedPercent }}%"></div>
                        <div class="bg-amber-400 h-full rounded-full transition-all duration-700 shadow-sm"
                             style="width: {{ $pendingPercent }}%"
                             title="Pending: {{ $pendingPercent }}%"></div>
                    </div>
                    <div class="flex justify-between items-center text-[11px] text-slate-400 font-medium px-1">
                        <span>0% Initiated</span>
                        <span>Overall Evaluation Readiness: <strong class="text-slate-700">{{ $submittedPercent }}%</strong></span>
                        <span>100% Finalized</span>
                    </div>
                </div>
            </div>
        @endif

        <!-- Quick Action Command Center -->
        <div class="command-center p-6 sm:p-7">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-indigo-500/20 text-indigo-300 border border-indigo-400/30">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </span>
                        <h3 class="text-base font-extrabold text-white tracking-tight">Examination Command Center</h3>
                    </div>
                    <p class="text-xs text-slate-300 mt-1 pl-9">Fast shortcuts to configure academic sessions, import student rosters, and generate official tabulation sheets.</p>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
                <!-- 1. New Exam -->
                <a href="{{ route('admin.exams.create') }}" class="action-card group">
                    <div class="action-icon bg-indigo-500/20 text-indigo-300 border border-indigo-400/30">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                    <span class="text-xs font-bold text-white tracking-tight">New Exam</span>
                    <span class="text-[10px] text-slate-300 mt-0.5">Create Session</span>
                </a>

                <!-- 2. Bulk Import Excel -->
                <a href="{{ route('admin.students.import.form') }}" class="action-card group">
                    <div class="action-icon bg-blue-500/20 text-blue-300 border border-blue-400/30">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                    </div>
                    <span class="text-xs font-bold text-white tracking-tight">Import Students</span>
                    <span class="text-[10px] text-slate-300 mt-0.5">Bulk Excel Upload</span>
                </a>

                <!-- 3. Assign Faculty -->
                <a href="{{ route('admin.assignments.create') }}" class="action-card group">
                    <div class="action-icon bg-purple-500/20 text-purple-300 border border-purple-400/30">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                    </div>
                    <span class="text-xs font-bold text-white tracking-tight">Assign Faculty</span>
                    <span class="text-[10px] text-slate-300 mt-0.5">Map Classes</span>
                </a>

                <!-- 4. Mark Status & Verify -->
                <a href="{{ route('admin.marks.index') }}" class="action-card group">
                    <div class="action-icon bg-amber-500/20 text-amber-300 border border-amber-400/30">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <span class="text-xs font-bold text-white tracking-tight">Mark Matrix</span>
                    <span class="text-[10px] text-slate-300 mt-0.5">Verify & Lock</span>
                </a>

                <!-- 5. Award Rolls PDF -->
                <a href="{{ route('admin.award-rolls.index') }}" class="action-card group">
                    <div class="action-icon bg-rose-500/20 text-rose-300 border border-rose-400/30">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <span class="text-xs font-bold text-white tracking-tight">Award Rolls</span>
                    <span class="text-[10px] text-slate-300 mt-0.5">Official PDF Sheets</span>
                </a>
            </div>
        </div>

        <!-- Pending Mark Entries Section -->
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-5 sm:p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-amber-50 border border-amber-200/80 text-amber-700 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-extrabold text-slate-900 tracking-tight">Pending Mark Entries</h3>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200/60">
                                {{ count($pendingAssignments) }} Pending
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">Faculty assignments where marks have not been finalized or locked yet.</p>
                    </div>
                </div>

                <a href="{{ route('admin.marks.index') }}"
                   class="inline-flex items-center gap-1.5 text-xs font-bold text-indigo-600 hover:text-indigo-800 bg-indigo-50/70 hover:bg-indigo-100/80 px-3.5 py-2 rounded-xl border border-indigo-100 transition">
                    <span>View Complete Mark Matrix</span>
                    <span>&rarr;</span>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50/75 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3.5 text-left">Class & Section</th>
                            <th class="px-6 py-3.5 text-left">Subject</th>
                            <th class="px-6 py-3.5 text-left">Faculty Teacher</th>
                            <th class="px-6 py-3.5 text-left">Evaluation Status</th>
                            <th class="px-6 py-3.5 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($pendingAssignments as $assignment)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <!-- Class & Section -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 text-slate-800 border border-slate-200/80">
                                            {{ $assignment->schoolClass?->name }}
                                        </span>
                                        <span class="text-xs font-semibold text-slate-500">
                                            Sec {{ $assignment->section?->name }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Subject -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <p class="text-sm font-bold text-slate-900">{{ $assignment->subject?->name }}</p>
                                    </div>
                                </td>

                                <!-- Teacher -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full bg-indigo-50 text-indigo-700 font-bold text-xs flex items-center justify-center border border-indigo-100">
                                            {{ strtoupper(substr($assignment->teacher?->name ?? 'NA', 0, 2)) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-slate-800">{{ $assignment->teacher?->name ?? 'Unassigned' }}</p>
                                            @if($assignment->teacher?->email)
                                                <p class="text-[11px] text-slate-400">{{ $assignment->teacher?->email }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <!-- Status -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($assignment->mark_status === 'draft')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            Draft In-Progress
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            Not Started
                                        </span>
                                    @endif
                                </td>

                                <!-- Action -->
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    @if($selectedExam)
                                        <a href="{{ route('admin.marks.show', ['exam' => $selectedExam->id, 'assignment' => $assignment->id]) }}"
                                           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold text-indigo-700 bg-indigo-50 hover:bg-indigo-600 hover:text-white transition">
                                            <span>Review Marks</span>
                                            <span>&rarr;</span>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-3">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </div>
                                        <h4 class="text-sm font-extrabold text-slate-800">All Marks Submitted!</h4>
                                        <p class="text-xs text-slate-500 mt-1">
                                            All {{ $totalAssignments }} assigned subjects for this exam have submitted evaluations. You can now verify or publish results.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
