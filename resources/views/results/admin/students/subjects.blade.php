<x-app-layout>
    <x-slot name="title">{{ __('Elective Subject Allocation') }}: {{ $student->name }} ({{ $student->schoolClass?->name }})</x-slot>

    <div class="w-full space-y-6">
        <!-- Top Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200">
            <div>
                <p class="text-sm text-slate-500">
                    Assign Higher Secondary elective stream subjects for <span class="font-bold text-slate-900">{{ $student->name }}</span> ({{ $student->student_id }} - {{ $student->schoolClass?->name }})
                </p>
            </div>
            <a href="{{ route('admin.students.index', request()->query()) }}" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-300 rounded-lg font-semibold text-xs text-slate-700 uppercase tracking-wider shadow-sm hover:bg-slate-50 transition w-fit">
                &larr; Back to Students
            </a>
        </div>

        <x-alert />

        <!-- Student Profile Badge (Full Width) -->
        <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white rounded-2xl p-6 shadow-sm border border-slate-800 w-full">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                <div>
                    <p class="text-xs uppercase tracking-wider text-indigo-300 font-semibold">Student Name</p>
                    <p class="text-base font-bold mt-0.5">{{ $student->name }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wider text-indigo-300 font-semibold">Student ID</p>
                    <p class="text-base font-mono font-bold mt-0.5">{{ $student->student_id }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wider text-indigo-300 font-semibold">Class & Section</p>
                    <p class="text-base font-bold mt-0.5">{{ $student->schoolClass?->name }} ({{ $student->section?->name }})</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wider text-indigo-300 font-semibold">Roll Number</p>
                    <p class="text-base font-bold mt-0.5">{{ $student->roll_no }}</p>
                </div>
            </div>
        </div>

        <!-- Subject Selection Form (Full Width) -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-6 sm:p-8 w-full">
            <div class="mb-5 pb-4 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Select Allocated Stream Subjects</h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Individual subject allocation is designated for Class 11 and Class 12 students. Only selected subjects will appear for this student in Teacher Mark Entry, Award Rolls, and Results.
                </p>
            </div>

            <form method="POST" action="{{ route('admin.students.subjects.save', array_merge(['student' => $student], request()->query())) }}" class="space-y-6">
                @csrf
                @foreach(request()->only(['academic_year_id', 'class_id', 'section_id', 'search', 'page']) as $k => $v)
                    @if(!is_null($v) && $v !== '')
                        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                    @endif
                @endforeach

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 w-full">
                    @forelse($allSubjects as $subject)
                        @php
                            $isChecked = in_array($subject->id, old('subject_ids', $allocatedSubjectIds));
                        @endphp
                        <label class="relative flex items-start p-4 rounded-xl border {{ $isChecked ? 'border-indigo-500 bg-indigo-50/40' : 'border-slate-200 hover:border-slate-300 bg-white' }} cursor-pointer transition">
                            <div class="flex items-center h-5">
                                <input type="checkbox" name="subject_ids[]" value="{{ $subject->id }}" {{ $isChecked ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            </div>
                            <div class="ms-3 text-sm">
                                <span class="font-bold text-slate-900 block">{{ $subject->name }}</span>
                                <span class="text-xs text-slate-400 mt-1 block">Max Marks: {{ number_format($subject->maximum_marks, 0) }}</span>
                            </div>
                        </label>
                    @empty
                        <p class="text-sm text-slate-500 col-span-full">No active subjects registered in the system.</p>
                    @endforelse
                </div>

                <div class="flex flex-col-reverse sm:flex-row items-center justify-between gap-3 pt-6 border-t border-slate-100">
                    <a href="{{ route('admin.students.index', request()->query()) }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Cancel</a>
                    <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                        {{ __('Save Subject Allocation') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
