<x-app-layout>
    <x-slot name="title">{{ __('Examinations') }}</x-slot>

    <div class="w-full space-y-6">
        <!-- Top Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200">
            <div>
                <p class="text-sm text-slate-500">Manage examination sessions, lifecycle workflows, and mark verification states.</p>
            </div>
            <a href="{{ route('admin.exams.create') }}" class="inline-flex items-center justify-center px-3 py-1.5 sm:px-4 sm:py-2.5 bg-indigo-600 border border-transparent rounded-lg sm:rounded-xl font-semibold text-[11px] sm:text-xs text-white uppercase tracking-wider hover:bg-indigo-700 shadow-sm transition w-fit">
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Exam
            </a>
        </div>

        <x-alert />

        <!-- Full-Width Responsive Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden w-full">
            <div class="overflow-x-auto w-full">
                <table class="w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50/80">
                        <tr>
                            <th class="px-5 py-3.5 text-left font-bold text-slate-700 text-xs uppercase tracking-wider">Exam Name</th>
                            <th class="px-5 py-3.5 text-left font-bold text-slate-700 text-xs uppercase tracking-wider">Academic Year</th>
                            <th class="px-5 py-3.5 text-left font-bold text-slate-700 text-xs uppercase tracking-wider">Dates</th>
                            <th class="px-5 py-3.5 text-center font-bold text-slate-700 text-xs uppercase tracking-wider">Lifecycle Status</th>
                            <th class="px-5 py-3.5 text-right font-bold text-slate-700 text-xs uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($exams as $exam)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="px-5 py-4 whitespace-nowrap font-bold text-slate-900">
                                    {{ $exam->exam_name }}
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-slate-600">
                                    {{ $exam->academicYear?->name }}
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-xs text-slate-500">
                                    {{ $exam->start_date ? $exam->start_date->format('d M Y') : '—' }} &rarr; {{ $exam->end_date ? $exam->end_date->format('d M Y') : '—' }}
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-center">
                                    @php
                                        $badgeColor = match($exam->status) {
                                            'Published' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                            'Mark Entry Open' => 'bg-blue-50 text-blue-700 border-blue-200',
                                            'Locked' => 'bg-purple-50 text-purple-700 border-purple-200',
                                            'Verification' => 'bg-amber-50 text-amber-700 border-amber-200',
                                            'Active' => 'bg-teal-50 text-teal-700 border-teal-200',
                                            default => 'bg-slate-100 text-slate-700 border-slate-200',
                                        };
                                    @endphp
                                    <form method="POST" action="{{ route('admin.exams.status', $exam) }}" class="inline-flex items-center">
                                        @csrf
                                        @method('PATCH')
                                        <select name="status" onchange="this.form.submit()"
                                            class="text-xs font-bold rounded-lg border py-1 pl-2.5 pr-7 shadow-xs cursor-pointer focus:ring-2 focus:ring-indigo-500/20 {{ $badgeColor }}"
                                            title="Change exam lifecycle status">
                                            @foreach($statuses as $st)
                                                <option value="{{ $st }}" {{ $exam->status === $st ? 'selected' : '' }}>
                                                    {{ $st }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </form>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-right text-xs space-x-1.5">
                                    <a href="{{ route('admin.exams.edit', $exam) }}" class="inline-flex items-center px-2.5 py-1.5 rounded-lg border border-slate-200 text-indigo-600 hover:bg-indigo-50 font-semibold transition">
                                        Edit
                                    </a>
                                    <a href="{{ route('admin.marks.index', ['exam_id' => $exam->id]) }}" class="inline-flex items-center px-2.5 py-1.5 rounded-lg border border-slate-200 text-blue-600 hover:bg-blue-50 font-semibold transition">
                                        Marks
                                    </a>
                                    <a href="{{ route('admin.award-rolls.index', ['exam_id' => $exam->id]) }}" class="inline-flex items-center px-2.5 py-1.5 rounded-lg border border-slate-200 text-purple-600 hover:bg-purple-50 font-semibold transition">
                                        Award Roll
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                    No exams registered yet. Click "Add Exam" to create one.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $exams->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
