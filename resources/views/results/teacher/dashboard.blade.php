<x-app-layout>
    <div class="w-full space-y-6">
        <!-- Top Action & Overview Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200">
            <div>
                <h2 class="font-extrabold text-xl sm:text-2xl text-slate-900 tracking-tight">
                    Welcome back, {{ $teacher->name }}
                </h2>
                <p class="text-xs text-slate-500 mt-1 font-medium">
                    Faculty Mark Entry & Student Evaluation Workspace
                </p>
            </div>

            <!-- Exam Selector -->
            @if($exams->isNotEmpty())
                <div class="flex items-center gap-3">
                    <form method="GET" action="{{ route('teacher.dashboard') }}" class="flex items-center gap-2">
                        <div class="relative flex items-center">
                            <div
                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <select name="exam_id" onchange="this.form.submit()"
                                class="pl-9 pr-9 py-2 text-xs font-semibold rounded-xl border border-slate-200 bg-white hover:border-slate-300 focus:bg-white text-slate-700 shadow-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition cursor-pointer">
                                @foreach($exams as $exam)
                                    <option value="{{ $exam->id }}" {{ $selectedExamId == $exam->id ? 'selected' : '' }}>
                                        {{ $exam->exam_name }} ({{ ucfirst($exam->status) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
            @endif
        </div>

        <x-alert />

        @if(!$selectedExam)
            <div
                class="rounded-2xl p-4 bg-amber-50 border border-amber-200/80 text-amber-900 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs">
                <div class="flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span><strong>No active examination cycle configured.</strong> Marks cannot be submitted until an active
                        examination is configured.</span>
                </div>
                @if(Auth::user()->hasRole('super-admin'))
                    <a href="{{ route('admin.exams.create') }}"
                        class="px-3 py-1.5 rounded-lg bg-amber-600 text-white font-bold hover:bg-amber-700 transition shrink-0 inline-block text-center">
                        + Create Exam
                    </a>
                @endif
            </div>
        @endif

        <!-- Section Title & Meta -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h3 class="text-base font-extrabold text-slate-900 tracking-tight">
                    {{ Auth::user()->hasRole('super-admin') ? 'School Teaching Assignments & Mark Entry' : 'My Teaching Assignments' }}
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    {{ Auth::user()->hasRole('super-admin') ? 'All classes, sections, and subjects assigned across the faculty.' : 'Classes, sections, and subjects assigned to your account for mark entry.' }}
                </p>
            </div>
            <span class="text-xs font-bold text-slate-500 bg-slate-100 px-3 py-1 rounded-full w-fit">
                {{ $assignments->count() }} {{ Str::plural('Allocation', $assignments->count()) }} Total
            </span>
        </div>

        <!-- Assignment Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-5">
            @forelse($assignments as $assignment)
                <div
                    class="bg-white rounded-xl sm:rounded-2xl p-3 sm:p-5 lg:p-6 border border-slate-200/80 shadow-xs hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between group">
                    <div>
                        <!-- Card Header -->
                        <div class="flex items-center justify-between gap-2">
                            <span
                                class="inline-flex items-center px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-md sm:rounded-lg text-[11px] sm:text-xs font-extrabold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                {{ $assignment->schoolClass?->name }} - {{ $assignment->section?->name }}
                            </span>

                            @php
                                $status = strtolower($assignment->current_status ?? 'pending');
                                $statusClasses = match ($status) {
                                    'locked' => 'bg-purple-50 text-purple-700 border-purple-200/80',
                                    'verified' => 'bg-emerald-50 text-emerald-700 border-emerald-200/80',
                                    'submitted' => 'bg-blue-50 text-blue-700 border-blue-200/80',
                                    'draft' => 'bg-amber-50 text-amber-700 border-amber-200/80',
                                    default => 'bg-slate-100 text-slate-600 border-slate-200',
                                };
                            @endphp
                            <span
                                class="inline-flex items-center gap-1 sm:gap-1.5 px-2 py-0.5 rounded-full text-[10px] sm:text-xs font-bold uppercase tracking-wider border {{ $statusClasses }}">
                                <span
                                    class="w-1.5 h-1.5 rounded-full {{ in_array($status, ['submitted', 'verified', 'locked']) ? 'bg-emerald-500' : ($status === 'draft' ? 'bg-amber-500' : 'bg-slate-400') }}"></span>
                                {{ $assignment->current_status }}
                            </span>
                        </div>

                        <!-- Subject Info -->
                        <div class="mt-2 sm:mt-3">
                            <h4 class="text-sm sm:text-base lg:text-lg font-extrabold text-slate-900 group-hover:text-indigo-600 transition tracking-tight leading-snug dual-line-clamp"
                                title="{{ $assignment->subject?->name }}">
                                {{ $assignment->subject?->name }}
                            </h4>
                            @if(Auth::user()->hasRole('super-admin') && $assignment->teacher)
                                <p class="text-[11px] sm:text-xs text-slate-500 mt-0.5 sm:mt-1">
                                    Teacher: <strong class="text-slate-700">{{ $assignment->teacher->name }}</strong>
                                </p>
                            @endif
                        </div>

                        <!-- Meta stats -->
                        <div
                            class="mt-3 sm:mt-4 pt-2.5 sm:pt-3 border-t border-slate-100 grid grid-cols-2 gap-2 sm:gap-3 text-xs">
                            <div
                                class="bg-slate-50/70 p-2 sm:p-2.5 lg:p-3 rounded-lg sm:rounded-xl border border-slate-100">
                                <p class="text-[10px] sm:text-[11px] font-medium text-slate-400">Eligible Students</p>
                                <p class="text-sm sm:text-base font-extrabold text-slate-900 mt-0.5">
                                    {{ $assignment->student_count }}</p>
                            </div>
                            <div
                                class="bg-slate-50/70 p-2 sm:p-2.5 lg:p-3 rounded-lg sm:rounded-xl border border-slate-100">
                                <p class="text-[10px] sm:text-[11px] font-medium text-slate-400">Maximum Marks</p>
                                <p class="text-sm sm:text-base font-extrabold text-slate-900 mt-0.5">
                                    {{ number_format($assignment->subject?->maximum_marks, 0) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Action Button -->
                    <div class="mt-3.5 sm:mt-5 pt-2.5 sm:pt-3 border-t border-slate-100">
                        @if($selectedExam)
                            @php
                                $isSubmitted = in_array(strtolower($assignment->current_status), ['submitted', 'verified', 'locked']);
                                $examStatus = strtolower($selectedExam->status);
                                $canEnterMarks = in_array($examStatus, ['active', 'mark entry open', 'verification', 'draft']) || Auth::user()->hasRole('super-admin');
                            @endphp
                            @if($canEnterMarks || $isSubmitted)
                                <a href="{{ route('teacher.marks.entry', ['assignment' => $assignment->id, 'exam_id' => $selectedExam->id]) }}"
                                    class="w-full inline-flex justify-center items-center gap-1.5 px-2.5 py-1.5 sm:px-4 sm:py-2 rounded-lg text-[11px] sm:text-xs font-bold transition shadow-xs {{ $isSubmitted ? 'bg-slate-100 text-slate-700 hover:bg-slate-200' : 'bg-indigo-600 text-white hover:bg-indigo-700 shadow-indigo-600/20' }}">
                                    @if($isSubmitted)
                                        <span>View Marks</span>
                                        <span class="hidden sm:inline">&nbsp;/ Verify</span>
                                    @else
                                        <span>Enter Marks</span>
                                    @endif
                                    <span>&rarr;</span>
                                </a>
                            @else
                                <button disabled
                                    class="w-full inline-flex justify-center items-center px-2.5 py-1.5 sm:px-4 sm:py-2 bg-slate-100 text-slate-400 rounded-lg text-[11px] sm:text-xs font-bold cursor-not-allowed"
                                    title="Exam is currently {{ $selectedExam->status }}">
                                    <span class="sm:hidden">Closed ({{ $selectedExam->status }})</span>
                                    <span class="hidden sm:inline">Mark Entry Closed ({{ $selectedExam->status }})</span>
                                </button>
                            @endif
                        @else
                            <button disabled
                                class="w-full inline-flex justify-center items-center px-2.5 py-1.5 sm:px-4 sm:py-2 bg-slate-100 text-slate-400 rounded-lg text-[11px] sm:text-xs font-bold cursor-not-allowed">
                                No Exam Configured
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white rounded-2xl p-12 text-center border border-slate-200/80">
                    <div
                        class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <h4 class="text-base font-extrabold text-slate-900">No Teaching Assignments Found</h4>
                    <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                        You have not been assigned to any classes or subjects yet. Please reach out to the school Super
                        Administrator.
                    </p>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>