<x-app-layout>
    <x-slot name="title">{{ __('Marks Detail') }}: {{ $assignment->schoolClass?->name }} ({{ $assignment->section?->name }}) - {{ $assignment->subject?->name }}</x-slot>

    <div class="w-full space-y-6">
        <!-- Top Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200">
            <div>
                <p class="text-sm text-slate-500">
                    Exam: <span class="font-bold text-slate-900">{{ $exam->exam_name }}</span> |
                    Teacher: <span class="font-semibold text-slate-700">{{ $assignment->teacher?->name }}</span> |
                    Max Marks: <span class="font-bold text-indigo-600">{{ number_format($assignment->subject?->maximum_marks, 0) }}</span>
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('teacher.marks.entry', ['assignment' => $assignment->id, 'exam_id' => $exam->id]) }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    <span>Enter / Edit Marks</span>
                </a>
                <a href="{{ route('admin.marks.index', ['exam_id' => $exam->id]) }}" class="inline-flex items-center px-3.5 py-2 rounded-xl bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 text-xs font-semibold uppercase tracking-wider transition shadow-sm">
                    &larr; Back to Marks Matrix
                </a>
            </div>
        </div>

        <x-alert />

        <!-- Student Marks Detail Table (Full Width & Responsive) -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden w-full">
            <div class="overflow-x-auto w-full">
                <table class="w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50/80">
                        <tr>
                            <th class="px-5 py-3.5 text-center font-bold text-slate-700 text-xs uppercase tracking-wider">Roll No</th>
                            <th class="px-5 py-3.5 text-left font-bold text-slate-700 text-xs uppercase tracking-wider">Student ID</th>
                            <th class="px-5 py-3.5 text-left font-bold text-slate-700 text-xs uppercase tracking-wider">Student Name</th>
                            <th class="px-5 py-3.5 text-center font-bold text-slate-700 text-xs uppercase tracking-wider">Marks</th>
                            <th class="px-5 py-3.5 text-center font-bold text-slate-700 text-xs uppercase tracking-wider">Attendance</th>
                            <th class="px-5 py-3.5 text-left font-bold text-slate-700 text-xs uppercase tracking-wider">Remarks</th>
                            <th class="px-5 py-3.5 text-center font-bold text-slate-700 text-xs uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($students as $student)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="px-5 py-3.5 text-center font-bold text-slate-900">{{ $student->roll_no }}</td>
                                <td class="px-5 py-3.5 font-mono text-xs text-indigo-700 font-bold">{{ $student->student_id }}</td>
                                <td class="px-5 py-3.5 font-bold text-slate-900">{{ $student->name }}</td>
                                <td class="px-5 py-3.5 text-center font-bold text-base text-slate-900">
                                    @if($student->current_mark?->is_absent)
                                        <span class="text-rose-600 font-bold">AB</span>
                                    @else
                                        {{ $student->current_mark?->marks !== null ? number_format($student->current_mark->marks, 0) : '—' }}
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    @if($student->current_mark?->is_absent)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">Absent</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Present</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-xs text-slate-500">
                                    {{ $student->current_mark?->remarks ?? '—' }}
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    @php
                                        $mStatus = $student->current_mark?->status ?? 'pending';
                                        $bColor = match($mStatus) {
                                            'locked' => 'bg-purple-50 text-purple-700 border border-purple-200',
                                            'verified' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
                                            'submitted' => 'bg-blue-50 text-blue-700 border border-blue-200',
                                            'draft' => 'bg-amber-50 text-amber-700 border border-amber-200',
                                            default => 'bg-slate-100 text-slate-600',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold {{ $bColor }}">
                                        {{ ucfirst($mStatus) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-slate-400">No students enrolled in this class section.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
