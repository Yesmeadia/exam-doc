<x-app-layout>
    <x-slot name="title">{{ __('Students Directory') }}</x-slot>
    @php /** @var \Illuminate\Support\Collection<int, \App\Models\SchoolClass> $classes */ @endphp

    <div class="w-full space-y-6">
        <!-- Top Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200">
            <div>
                <p class="text-sm text-slate-500">Enrolled students, roll numbers, and individual subject allocations.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.students.import.form') }}" class="inline-flex items-center px-3.5 py-2.5 bg-emerald-600 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-wider hover:bg-emerald-700 shadow-sm transition">
                    <svg class="w-4 h-4 me-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    Bulk Import Excel
                </a>
                <a href="{{ route('admin.students.create') }}" class="inline-flex items-center px-3.5 py-2.5 bg-indigo-600 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-wider hover:bg-indigo-700 shadow-sm transition">
                    <svg class="w-4 h-4 me-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Student
                </a>
            </div>
        </div>

        <x-alert />

        <!-- Filter Bar -->
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200/80 w-full">
            <form method="GET" action="{{ route('admin.students.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                <div>
                    <select name="academic_year_id" onchange="this.form.submit()" class="text-sm rounded-xl border-slate-300 w-full focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">-- All Academic Years --</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ $academicYearId == $year->id ? 'selected' : '' }}>
                                {{ $year->name }} {{ $year->is_active ? '(Active)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <select name="class_id" onchange="if(this.form.section_id) this.form.section_id.value=''; this.form.submit();" class="text-sm rounded-xl border-slate-300 w-full focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">-- All Classes --</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ $classId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <select name="section_id" onchange="this.form.submit()" class="text-sm rounded-xl border-slate-300 w-full focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">-- All Sections --</option>
                        @foreach($sections as $s)
                            <option value="{{ $s->id }}" {{ $sectionId == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex gap-2">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search ID, Name..." class="text-sm rounded-xl border-slate-300 w-full focus:ring-indigo-500 focus:border-indigo-500">
                    <button type="submit" class="px-4 py-2 bg-slate-800 text-white text-xs font-semibold rounded-xl hover:bg-slate-900 transition">Filter</button>
                    @if($academicYearId || $classId || $sectionId || $search)
                        <a href="{{ route('admin.students.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition flex items-center justify-center" title="Reset all filters">Reset</a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Student Table (Full Width & Responsive) -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden w-full">
            <div class="overflow-x-auto w-full">
                <table class="w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50/80">
                        <tr>
                            <th class="px-5 py-3.5 text-center font-bold text-slate-700 text-xs uppercase tracking-wider">Roll No</th>
                            <th class="px-5 py-3.5 text-left font-bold text-slate-700 text-xs uppercase tracking-wider">Student ID</th>
                            <th class="px-5 py-3.5 text-left font-bold text-slate-700 text-xs uppercase tracking-wider">Name</th>
                            <th class="px-5 py-3.5 text-left font-bold text-slate-700 text-xs uppercase tracking-wider">Class & Section</th>
                            <th class="px-5 py-3.5 text-center font-bold text-slate-700 text-xs uppercase tracking-wider">Curriculum / Subjects</th>
                            <th class="px-5 py-3.5 text-right font-bold text-slate-700 text-xs uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($students as $student)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="px-5 py-4 whitespace-nowrap text-center font-black text-slate-900">
                                    {{ $student->roll_no }}
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap font-mono text-xs font-bold text-indigo-700">
                                    {{ $student->student_id }}
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap font-bold text-slate-900">
                                    {{ $student->name }}
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-slate-600">
                                    {{ $student->schoolClass?->name }} ({{ $student->section?->name }})
                                </td>
                                <td class="px-5 py-4 text-center whitespace-nowrap">
                                    @if($student->allowsIndividualSubjectAllocation())
                                        @php $subCount = $student->subjects->count(); @endphp
                                        <a href="{{ route('admin.students.subjects', array_merge(['student' => $student], request()->query())) }}" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $subCount > 0 ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'bg-slate-100 text-slate-600' }} hover:border-purple-300">
                                            {{ $subCount > 0 ? $subCount . ' Elective Subjects' : 'Set Electives' }}
                                        </a>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                            Full Subjects
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-right text-xs space-x-1.5">
                                    @if($student->allowsIndividualSubjectAllocation())
                                        <a href="{{ route('admin.students.subjects', array_merge(['student' => $student], request()->query())) }}" class="inline-flex items-center px-2.5 py-1.5 rounded-lg border border-purple-200 text-purple-700 bg-purple-50/50 hover:bg-purple-100/60 font-semibold transition">Subjects</a>
                                    @endif
                                    <a href="{{ route('admin.students.marks-sheet', array_merge(['student' => $student], request()->query())) }}" class="inline-flex items-center px-2.5 py-1.5 rounded-lg border border-indigo-200 text-indigo-700 bg-indigo-50/60 hover:bg-indigo-100 font-semibold transition" title="View Timetable Marks Sheet">Marks Sheet</a>
                                    <a href="{{ route('admin.students.edit', array_merge(['student' => $student], request()->query())) }}" class="inline-flex items-center px-2.5 py-1.5 rounded-lg border border-slate-200 text-indigo-600 hover:bg-indigo-50 font-semibold transition">Edit</a>
                                    <form method="POST" action="{{ route('admin.students.destroy', array_merge(['student' => $student], request()->query())) }}" class="inline"
                                        data-confirm="Delete student '{{ $student->name }}'? This action is permanent."
                                        data-confirm-title="Delete Student"
                                        data-confirm-label="Yes, Delete"
                                        data-confirm-color="rose">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center px-2.5 py-1.5 rounded-lg border border-slate-200 text-rose-600 hover:bg-rose-50 font-semibold transition">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                    No students found matching your criteria. Click "Bulk Import Excel" or "Add Student".
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
