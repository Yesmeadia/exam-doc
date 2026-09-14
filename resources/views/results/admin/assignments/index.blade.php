<x-app-layout>
    <x-slot name="title">{{ __('Teacher Assignments') }}</x-slot>

    <div class="w-full space-y-6">
        <!-- Top Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200">
            <div>
                <p class="text-sm text-slate-500">Map teachers to their assigned classes, sections, and subjects for mark entry access.</p>
            </div>
            <a href="{{ route('admin.assignments.create') }}" class="inline-flex items-center justify-center px-3 py-1.5 sm:px-4 sm:py-2.5 bg-indigo-600 border border-transparent rounded-lg sm:rounded-xl font-semibold text-[11px] sm:text-xs text-white uppercase tracking-wider hover:bg-indigo-700 shadow-sm transition w-fit">
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Assign Teacher
            </a>
        </div>

        <x-alert />

        <!-- Filters -->
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200/80 w-full">
            <form method="GET" action="{{ route('admin.assignments.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
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
                    <select name="teacher_id" onchange="this.form.submit()" class="text-sm rounded-xl border-slate-300 w-full focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">-- All Teachers --</option>
                        @foreach($teachers as $t)
                            <option value="{{ $t->id }}" {{ $teacherId == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <select name="class_id" onchange="this.form.submit()" class="text-sm rounded-xl border-slate-300 w-full focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">-- All Classes --</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ $classId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>

        <!-- Full-Width Responsive Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden w-full">
            <div class="overflow-x-auto w-full">
                <table class="w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50/80">
                        <tr>
                            <th class="px-5 py-3.5 text-left font-bold text-slate-700 text-xs uppercase tracking-wider">Teacher</th>
                            <th class="px-5 py-3.5 text-left font-bold text-slate-700 text-xs uppercase tracking-wider">Class</th>
                            <th class="px-5 py-3.5 text-left font-bold text-slate-700 text-xs uppercase tracking-wider">Section</th>
                            <th class="px-5 py-3.5 text-left font-bold text-slate-700 text-xs uppercase tracking-wider">Subject</th>
                            <th class="px-5 py-3.5 text-left font-bold text-slate-700 text-xs uppercase tracking-wider">Academic Year</th>
                            <th class="px-5 py-3.5 text-right font-bold text-slate-700 text-xs uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($assignments as $assignment)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full bg-indigo-50 text-indigo-700 flex items-center justify-center font-bold text-xs border border-indigo-100 shrink-0">
                                            {{ strtoupper(substr($assignment->teacher?->name ?? 'NA', 0, 2)) }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900">{{ $assignment->teacher?->name }}</p>
                                            <p class="text-xs text-slate-400">{{ $assignment->teacher?->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-slate-700 font-semibold">
                                    {{ $assignment->schoolClass?->name }}
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-slate-700 font-semibold">
                                    {{ $assignment->section?->name }}
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap font-bold text-indigo-700">
                                    {{ $assignment->subject?->name }}
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-xs text-slate-500">
                                    {{ $assignment->academicYear?->name }}
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-right text-xs">
                                    <form method="POST" action="{{ route('admin.assignments.destroy', $assignment) }}" class="inline"
                                        data-confirm="Remove this teacher assignment? The teacher will no longer be linked to this subject."
                                        data-confirm-title="Remove Assignment"
                                        data-confirm-label="Yes, Remove"
                                        data-confirm-color="rose">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center px-2.5 py-1.5 rounded-lg border border-slate-200 text-rose-600 hover:bg-rose-50 font-semibold transition">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                    No teacher assignments found. Click "+ Assign Teacher" to assign one.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $assignments->withQueryString()->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
