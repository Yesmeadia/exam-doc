<x-app-layout>
    <x-slot name="title">{{ __('Mark Entry & Verification Matrix') }}</x-slot>

    <div class="w-full space-y-6">
        <!-- Top Action & Selector Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200">
            <div>
                <p class="text-sm text-slate-500">Review faculty submissions, audit student mark evaluations, and lock/unlock matrix cells.</p>
            </div>

            <!-- Exam Selector -->
            <form method="GET" action="{{ route('admin.marks.index') }}" class="flex items-center gap-2">
                <div class="relative flex items-center">
                    <select name="exam_id" onchange="this.form.submit()" class="text-xs font-bold rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-2 pl-3 pr-8">
                        @foreach($exams as $exam)
                            <option value="{{ $exam->id }}" {{ $selectedExamId == $exam->id ? 'selected' : '' }}>
                                {{ $exam->exam_name }} ({{ $exam->status }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>

        <x-alert />

        <!-- Filters (Full Width) -->
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200/80 flex flex-wrap items-center justify-between gap-3 w-full">
            <form method="GET" action="{{ route('admin.marks.index') }}" class="flex flex-wrap items-center gap-3">
                <input type="hidden" name="exam_id" value="{{ $selectedExamId }}">

                <select name="class_id" onchange="this.form.submit()" class="text-xs font-semibold rounded-xl border-slate-300 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">-- All Classes --</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ $classId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>

                <select name="status" onchange="this.form.submit()" class="text-xs font-semibold rounded-xl border-slate-300 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">-- All Statuses --</option>
                    <option value="pending" {{ $statusFilter === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="draft" {{ $statusFilter === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="submitted" {{ $statusFilter === 'submitted' ? 'selected' : '' }}>Submitted</option>
                    <option value="verified" {{ $statusFilter === 'verified' ? 'selected' : '' }}>Verified</option>
                    <option value="locked" {{ $statusFilter === 'locked' ? 'selected' : '' }}>Locked</option>
                </select>
            </form>

            <div class="text-xs text-slate-500 font-medium">
                Showing <span class="font-bold text-slate-900">{{ count($assignments) }}</span> assignment entries
            </div>
        </div>

        <!-- Assignments Mark Status Matrix Table (Full Width & Responsive) -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden w-full">
            <div class="overflow-x-auto w-full">
                <table class="w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50/80">
                        <tr>
                            <th class="px-5 py-3.5 text-left font-bold text-slate-700 text-xs uppercase tracking-wider">Class</th>
                            <th class="px-5 py-3.5 text-left font-bold text-slate-700 text-xs uppercase tracking-wider">Section</th>
                            <th class="px-5 py-3.5 text-left font-bold text-slate-700 text-xs uppercase tracking-wider">Subject</th>
                            <th class="px-5 py-3.5 text-left font-bold text-slate-700 text-xs uppercase tracking-wider">Teacher</th>
                            <th class="px-5 py-3.5 text-center font-bold text-slate-700 text-xs uppercase tracking-wider">Status</th>
                            <th class="px-5 py-3.5 text-left font-bold text-slate-700 text-xs uppercase tracking-wider">Submitted At</th>
                            <th class="px-5 py-3.5 text-right font-bold text-slate-700 text-xs uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($assignments as $row)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="px-5 py-4 whitespace-nowrap font-bold text-slate-900">{{ $row->schoolClass?->name }}</td>
                                <td class="px-5 py-4 whitespace-nowrap text-slate-700 font-semibold">{{ $row->section?->name }}</td>
                                <td class="px-5 py-4 whitespace-nowrap font-bold text-indigo-900">{{ $row->subject?->name }}</td>
                                <td class="px-5 py-4 whitespace-nowrap text-slate-600 font-medium">{{ $row->teacher?->name }}</td>
                                <td class="px-5 py-4 whitespace-nowrap text-center">
                                    @php
                                        $badgeColor = match($row->calculated_status) {
                                            'locked' => 'bg-purple-50 text-purple-700 border border-purple-200',
                                            'verified' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
                                            'submitted' => 'bg-blue-50 text-blue-700 border border-blue-200',
                                            'draft' => 'bg-amber-50 text-amber-700 border border-amber-200',
                                            default => 'bg-rose-50 text-rose-700 border border-rose-200',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $badgeColor }}">
                                        {{ ucfirst($row->calculated_status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-xs text-slate-500">
                                    {{ $row->submitted_at_date ? \Carbon\Carbon::parse($row->submitted_at_date)->timezone('Asia/Kolkata')->format('d M Y, h:i A') : '—' }}
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-right text-xs space-x-1.5">
                                    @if($selectedExam)
                                        <a href="{{ route('admin.marks.show', ['exam' => $selectedExam->id, 'assignment' => $row->id]) }}" class="inline-flex items-center px-2.5 py-1.5 rounded-lg border border-slate-200 text-indigo-600 hover:bg-indigo-50 font-semibold transition">
                                            View
                                        </a>
                                        <a href="{{ route('teacher.marks.entry', ['assignment' => $row->id, 'exam_id' => $selectedExam->id]) }}" class="inline-flex items-center px-2.5 py-1.5 rounded-lg border border-slate-200 text-emerald-600 hover:bg-emerald-50 font-semibold transition">
                                            Enter / Edit
                                        </a>

                                        @if(in_array($row->calculated_status, ['submitted', 'verified', 'locked']))
                                            <form method="POST" action="{{ route('admin.marks.unlock') }}" class="inline"
                                                data-confirm="Unlock these marks? The teacher will be able to edit them again."
                                                data-confirm-title="Unlock Marks"
                                                data-confirm-label="Yes, Unlock"
                                                data-confirm-color="amber">
                                                @csrf
                                                <input type="hidden" name="exam_id" value="{{ $selectedExam->id }}">
                                                <input type="hidden" name="class_id" value="{{ $row->class_id }}">
                                                <input type="hidden" name="section_id" value="{{ $row->section_id }}">
                                                <input type="hidden" name="subject_id" value="{{ $row->subject_id }}">
                                                <button type="submit" class="inline-flex items-center px-2.5 py-1.5 rounded-lg border border-slate-200 text-amber-600 hover:bg-amber-50 font-semibold transition">Unlock</button>
                                            </form>
                                        @endif

                                        @if($row->calculated_status === 'submitted')
                                            <form method="POST" action="{{ route('admin.marks.verify') }}" class="inline">
                                                @csrf
                                                <input type="hidden" name="exam_id" value="{{ $selectedExam->id }}">
                                                <input type="hidden" name="class_id" value="{{ $row->class_id }}">
                                                <input type="hidden" name="section_id" value="{{ $row->section_id }}">
                                                <input type="hidden" name="subject_id" value="{{ $row->subject_id }}">
                                                <button type="submit" class="inline-flex items-center px-2.5 py-1.5 rounded-lg border border-slate-200 text-emerald-600 hover:bg-emerald-50 font-semibold transition">Verify</button>
                                            </form>
                                        @endif

                                        @if(in_array($row->calculated_status, ['submitted', 'verified']))
                                            <form method="POST" action="{{ route('admin.marks.lock') }}" class="inline"
                                                data-confirm="Lock these marks officially? Teachers will no longer be able to edit them."
                                                data-confirm-title="Lock Marks"
                                                data-confirm-label="Yes, Lock"
                                                data-confirm-color="indigo">
                                                @csrf
                                                <input type="hidden" name="exam_id" value="{{ $selectedExam->id }}">
                                                <input type="hidden" name="class_id" value="{{ $row->class_id }}">
                                                <input type="hidden" name="section_id" value="{{ $row->section_id }}">
                                                <input type="hidden" name="subject_id" value="{{ $row->subject_id }}">
                                                <button type="submit" class="inline-flex items-center px-2.5 py-1.5 rounded-lg border border-slate-200 text-purple-600 hover:bg-purple-50 font-semibold transition">Lock</button>
                                            </form>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-slate-400">No mark assignments found matching your selection.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
