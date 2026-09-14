<x-app-layout>
    <x-slot name="title">{{ __('Student Marks Sheets Explorer') }}</x-slot>
    @php /** @var \Illuminate\Support\Collection<int, \App\Models\SchoolClass> $classes */ @endphp

    <div class="w-full space-y-6">
        <!-- Top Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200">
            <div>
                <p class="text-sm text-slate-500">Select any student to generate and view their complete timetable-style cumulative examination marks sheet.</p>
            </div>
            <a href="{{ route('admin.students.index') }}"
                class="inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-300 rounded-lg font-semibold text-xs text-slate-700 uppercase tracking-wider shadow-sm hover:bg-slate-50 transition w-fit">
                &larr; Back to Students Directory
            </a>
        </div>

        <x-alert />

        <!-- Filters Form -->
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200/80 w-full">
            <form method="GET" action="{{ route('admin.marks-sheets.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Academic Year</label>
                    <select name="academic_year_id" onchange="this.form.submit()" class="text-sm rounded-xl border-slate-300 w-full focus:ring-indigo-500 focus:border-indigo-500">
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ $academicYearId == $year->id ? 'selected' : '' }}>
                                {{ $year->name }} {{ $year->is_active ? '(Active)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Class</label>
                    <select name="class_id" onchange="this.form.submit()" class="text-sm rounded-xl border-slate-300 w-full focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">-- All Classes --</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ $classId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Section</label>
                    <select name="section_id" onchange="this.form.submit()" class="text-sm rounded-xl border-slate-300 w-full focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">-- All Sections --</option>
                        @foreach($sections as $s)
                            <option value="{{ $s->id }}" {{ $sectionId == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Search Student</label>
                    <div class="flex gap-2">
                        <input type="text" name="search" value="{{ $search }}" placeholder="Roll, ID, Name..." class="text-sm rounded-xl border-slate-300 w-full focus:ring-indigo-500 focus:border-indigo-500">
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-xs font-bold rounded-xl hover:bg-indigo-700 transition">Filter</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Students Roster Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden w-full">
            <div class="overflow-x-auto w-full">
                <table class="w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50/80">
                        <tr>
                            <th class="px-5 py-3.5 text-center font-bold text-slate-700 text-xs uppercase tracking-wider">Roll No</th>
                            <th class="px-5 py-3.5 text-left font-bold text-slate-700 text-xs uppercase tracking-wider">Student ID</th>
                            <th class="px-5 py-3.5 text-left font-bold text-slate-700 text-xs uppercase tracking-wider">Student Name</th>
                            <th class="px-5 py-3.5 text-left font-bold text-slate-700 text-xs uppercase tracking-wider">Class & Section</th>
                            <th class="px-5 py-3.5 text-left font-bold text-slate-700 text-xs uppercase tracking-wider">Academic Year</th>
                            <th class="px-5 py-3.5 text-right font-bold text-slate-700 text-xs uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($students as $student)
                            <tr class="hover:bg-indigo-50/30 transition">
                                <td class="px-5 py-4 whitespace-nowrap text-center font-mono font-bold text-slate-700">
                                    {{ $student->roll_no }}
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap font-mono text-xs font-semibold text-slate-600">
                                    {{ $student->student_id }}
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap font-bold text-slate-900">
                                    {{ $student->name }}
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-slate-600">
                                    {{ $student->schoolClass?->name }} ({{ $student->section?->name }})
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-xs text-slate-500">
                                    {{ $student->academicYear?->name }}
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-right">
                                    <a href="{{ route('admin.students.marks-sheet', $student) }}"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-xs transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        View Marks Sheet &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                    No students found matching your criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $students->withQueryString()->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
