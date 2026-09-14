<x-app-layout>
    <x-slot name="title">{{ __('Mark Entry') }}: {{ $assignment->schoolClass?->name }} ({{ $assignment->section?->name }}) - {{ $assignment->subject?->name }}</x-slot>

    <div class="w-full space-y-6" x-data="{
        submitConfirmModal: false,
        maxMarks: {{ (float)$assignment->subject->maximum_marks }},
        validateMark(el) {
            const val = parseFloat(el.value);
            if (!isNaN(val)) {
                if (val < 0) el.value = 0;
                if (val > this.maxMarks) {
                    alert('Marks cannot exceed maximum marks (' + this.maxMarks + ')');
                    el.value = this.maxMarks;
                }
            }
        }
    }">
        <!-- Top Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 sm:gap-4 pb-2 border-b border-slate-200">
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-1.5 mb-1">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] sm:text-[11px] font-extrabold bg-indigo-50 text-indigo-700 border border-indigo-100 shrink-0">
                        {{ $assignment->schoolClass?->name }} ({{ $assignment->section?->name }})
                    </span>
                    <h2 class="text-xs sm:text-base font-extrabold text-slate-900 tracking-tight dual-line-clamp" title="{{ $assignment->subject?->name }}">
                        {{ $assignment->subject?->name }}
                    </h2>
                </div>
                <p class="text-[10px] sm:text-xs text-slate-500">
                    Exam: <span class="font-bold text-slate-800">{{ $exam->exam_name }}</span> |
                    Maximum Marks: <span class="font-bold text-indigo-600" id="max_marks_display">{{ number_format($assignment->subject?->maximum_marks, 0) }}</span>
                </p>
            </div>
            <a href="{{ route('teacher.dashboard', ['exam_id' => $exam->id]) }}" class="inline-flex items-center justify-center px-2.5 py-1 sm:px-4 sm:py-2 bg-white border border-slate-300 rounded-lg font-bold text-[11px] sm:text-xs text-slate-700 shadow-2xs hover:bg-slate-50 transition w-fit shrink-0">
                <span class="sm:hidden">&larr; Back</span>
                <span class="hidden sm:inline">&larr; Back to Dashboard</span>
            </a>
        </div>

        <div class="w-full space-y-4 sm:space-y-6">
            <x-alert />

            @if($isLocked)
                <div class="bg-blue-50 border-l-4 border-blue-500 p-3 sm:p-4 rounded-r-xl shadow-sm flex items-center justify-between gap-2 text-xs">
                    <div>
                        <h3 class="text-xs sm:text-sm font-bold text-blue-900">Submission Locked (Status: {{ ucfirst($submissionStatus) }})</h3>
                        <p class="text-[11px] sm:text-xs text-blue-700 mt-0.5">
                            Marks were officially submitted @if($submittedAt) on {{ $submittedAt->timezone('Asia/Kolkata')->format('d M Y, h:i A') }} @endif. Editing is locked unless unlocked by the Super Admin.
                        </p>
                    </div>
                    <span class="px-2.5 py-1 bg-blue-600 text-white rounded-lg text-[10px] sm:text-xs font-bold uppercase shrink-0">Locked</span>
                </div>
            @else
                <div class="bg-indigo-50 border-l-4 border-indigo-500 p-2.5 sm:p-3 rounded-r-xl text-[11px] sm:text-xs text-indigo-800 flex flex-col sm:flex-row sm:items-center justify-between gap-1.5">
                    <span>
                        <strong>Spreadsheet Mode:</strong> Enter marks (0 - {{ number_format($assignment->subject?->maximum_marks, 0) }}). Use <kbd class="px-1 py-0.5 bg-white border border-indigo-200 rounded font-mono text-[10px]">Tab</kbd> or <kbd class="px-1 py-0.5 bg-white border border-indigo-200 rounded font-mono text-[10px]">Enter</kbd> to jump down.
                    </span>
                    <span class="text-indigo-600 font-semibold shrink-0">Sorted by Roll No &uarr;</span>
                </div>
            @endif

            <form id="marksForm" method="POST" action="{{ route('teacher.marks.draft', $assignment) }}">
                @csrf
                <input type="hidden" name="exam_id" value="{{ $exam->id }}">

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-2.5 sm:px-4 py-2.5 sm:py-3.5 text-center font-bold text-gray-700 text-[11px] sm:text-xs uppercase tracking-wider w-20 sm:w-24">Roll No</th>
                                    <th class="px-2.5 sm:px-4 py-2.5 sm:py-3.5 text-left font-bold text-gray-700 text-[11px] sm:text-xs uppercase tracking-wider w-28 sm:w-36">Student ID</th>
                                    <th class="px-2.5 sm:px-4 py-2.5 sm:py-3.5 text-left font-bold text-gray-700 text-[11px] sm:text-xs uppercase tracking-wider">Student Name</th>
                                    <th class="px-2.5 sm:px-4 py-2.5 sm:py-3.5 text-center font-bold text-gray-700 text-[11px] sm:text-xs uppercase tracking-wider w-28 sm:w-36">
                                        Marks (Max {{ number_format($assignment->subject?->maximum_marks, 0) }})
                                    </th>
                                    <th class="px-2.5 sm:px-4 py-2.5 sm:py-3.5 text-center font-bold text-gray-700 text-[11px] sm:text-xs uppercase tracking-wider w-16 sm:w-24">Absent</th>
                                    <th class="px-2.5 sm:px-4 py-2.5 sm:py-3.5 text-left font-bold text-gray-700 text-[11px] sm:text-xs uppercase tracking-wider">Remarks</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse($students as $student)
                                    @php
                                        $currentMark = $student->current_mark;
                                        $isAbsent = $currentMark?->is_absent ?? false;
                                        $markVal = $currentMark?->marks;
                                    @endphp
                                    <tr class="hover:bg-gray-50/70 transition" x-data="{ absent: {{ $isAbsent ? 'true' : 'false' }} }">
                                        <td class="px-2.5 sm:px-4 py-2 sm:py-3 text-center font-bold text-gray-900 text-sm sm:text-base">
                                            {{ $student->roll_no }}
                                        </td>
                                        <td class="px-2.5 sm:px-4 py-2 sm:py-3 font-mono text-[11px] sm:text-xs font-semibold text-indigo-700">
                                            {{ $student->student_id }}
                                        </td>
                                        <td class="px-2.5 sm:px-4 py-2 sm:py-3 font-medium text-gray-900 text-xs sm:text-sm">
                                            {{ $student->name }}
                                        </td>
                                        <td class="px-2.5 sm:px-4 py-2 sm:py-3 text-center">
                                            <input
                                                type="number"
                                                step="0.5"
                                                min="0"
                                                max="{{ $assignment->subject->maximum_marks }}"
                                                name="marks[{{ $student->id }}][marks]"
                                                value="{{ $markVal }}"
                                                :disabled="absent || {{ $isLocked ? 'true' : 'false' }}"
                                                @input="validateMark($el)"
                                                @keydown.enter.prevent="$el.closest('tr').nextElementSibling?.querySelector('input[type=number]')?.focus()"
                                                class="w-20 sm:w-28 text-center font-bold text-sm sm:text-base rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100 disabled:text-gray-400 py-1 sm:py-2"
                                                placeholder="0.0"
                                            >
                                        </td>
                                        <td class="px-2.5 sm:px-4 py-2 sm:py-3 text-center">
                                            <label class="inline-flex items-center">
                                                <input
                                                    type="checkbox"
                                                    name="marks[{{ $student->id }}][is_absent]"
                                                    value="1"
                                                    x-model="absent"
                                                    {{ $isLocked ? 'disabled' : '' }}
                                                    @change="if (absent) { $el.closest('tr').querySelector('input[type=number]').value = ''; }"
                                                    class="rounded border-gray-300 text-rose-600 shadow-sm focus:border-rose-500 focus:ring-rose-500"
                                                >
                                                <span class="ms-1.5 text-xs font-semibold" :class="absent ? 'text-rose-600' : 'text-gray-400'">AB</span>
                                            </label>
                                        </td>
                                        <td class="px-2.5 sm:px-4 py-2 sm:py-3">
                                            <input
                                                type="text"
                                                name="marks[{{ $student->id }}][remarks]"
                                                value="{{ $currentMark?->remarks }}"
                                                {{ $isLocked ? 'disabled' : '' }}
                                                class="w-full text-xs rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100 disabled:text-gray-400"
                                                placeholder="Remarks (optional)"
                                            >
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                            No eligible students found for this subject and section.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if(!$isLocked && $students->isNotEmpty())
                        <div class="p-2 sm:p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between gap-2">
                            <button
                                type="submit"
                                formaction="{{ route('teacher.marks.draft', $assignment) }}"
                                class="inline-flex items-center justify-center px-2.5 py-1.5 sm:px-5 sm:py-2.5 bg-white border border-slate-300 rounded-lg text-[11px] sm:text-xs font-bold text-slate-700 uppercase tracking-wider hover:bg-slate-100 shadow-2xs transition shrink-0"
                            >
                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 me-1 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                                </svg>
                                <span>Save Draft</span>
                            </button>

                            <button
                                type="button"
                                @click="submitConfirmModal = true"
                                class="inline-flex items-center justify-center px-2.5 py-1.5 sm:px-6 sm:py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-[11px] sm:text-xs font-bold uppercase tracking-wider shadow-2xs transition shrink-0"
                            >
                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 me-1 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="hidden sm:inline">Submit Marks (Lock Submission)</span>
                                <span class="sm:hidden">Submit Marks</span>
                            </button>
                        </div>
                    @endif
                </div>

                <!-- Submit Confirmation Modal -->
                <div x-show="submitConfirmModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                    <div class="flex items-center justify-center min-h-screen px-4">
                        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="submitConfirmModal = false"></div>
                        <div class="bg-white rounded-xl overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full z-10 p-6">
                            <div class="flex items-start">
                                <div class="w-10 h-10 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center me-3 shrink-0">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900">Confirm Mark Submission</h3>
                                    <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                                        Once submitted, marks cannot be edited unless Super Admin unlocks this submission. Please ensure all student marks have been entered correctly.
                                    </p>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-gray-100">
                                <button type="button" @click="submitConfirmModal = false" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900">
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    formaction="{{ route('teacher.marks.submit', $assignment) }}"
                                    class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold uppercase tracking-wider transition"
                                >
                                    Confirm & Submit Marks
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
