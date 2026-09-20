<x-app-layout>
    <x-slot name="title">{{ __('Curriculum Subjects') }}</x-slot>

    <div class="w-full space-y-6" x-data="{
        quickEditModal: false,
        activeSubject: { id: null, name: '', maximum_marks: 100, pass_marks: 33 },
        openQuickEdit(subject) {
            this.activeSubject = {
                id: subject.id,
                name: subject.name,
                maximum_marks: subject.maximum_marks,
                pass_marks: subject.pass_marks
            };
            this.quickEditModal = true;
        }
    }">
        <!-- Top Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200">
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Curriculum Subjects</h1>
                <p class="text-sm text-slate-500 mt-0.5">Manage subjects, configure Maximum & Pass Marks (including Higher Secondary 11th & 12th), and class mappings.</p>
            </div>
            <a href="{{ route('admin.subjects.create') }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-indigo-600 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-wider hover:bg-indigo-700 shadow-sm transition w-fit">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Subject
            </a>
        </div>

        <x-alert />

        <!-- Filters Bar -->
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200/80 w-full">
            <form method="GET" action="{{ route('admin.subjects.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                <!-- Search -->
                <div class="sm:col-span-5">
                    <input type="text" name="search" value="{{ $search ?? '' }}"
                        placeholder="Search subjects by name or code..."
                        class="text-sm rounded-xl border-slate-300 w-full focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- Class Filter -->
                <div class="sm:col-span-4">
                    <select name="class_id" onchange="this.form.submit()"
                        class="text-sm rounded-xl border-slate-300 w-full focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">-- All Classes --</option>
                        <option value="higher_secondary" {{ $classId === 'higher_secondary' ? 'selected' : '' }}>
                            🎓 Higher Secondary (11th & 12th Only)
                        </option>
                        <optgroup label="Specific Classes">
                            @foreach($classes as $c)
                                <option value="{{ $c->id }}" {{ (string)$classId === (string)$c->id ? 'selected' : '' }}>
                                    {{ $c->name }}
                                </option>
                            @endforeach
                        </optgroup>
                    </select>
                </div>

                <!-- Academic Year Filter -->
                <div class="sm:col-span-3">
                    <select name="academic_year_id" onchange="this.form.submit()"
                        class="text-sm rounded-xl border-slate-300 w-full focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">-- All Academic Years --</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ $academicYearId == $year->id ? 'selected' : '' }}>
                                {{ $year->name }} {{ $year->is_active ? '(Active)' : '' }}
                            </option>
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
                            <th class="px-5 py-3.5 text-left font-bold text-slate-700 text-xs uppercase tracking-wider">Subject</th>
                            <th class="px-5 py-3.5 text-left font-bold text-slate-700 text-xs uppercase tracking-wider">Classes Mapped</th>
                            <th class="px-5 py-3.5 text-center font-bold text-slate-700 text-xs uppercase tracking-wider">Max Marks</th>
                            <th class="px-5 py-3.5 text-center font-bold text-slate-700 text-xs uppercase tracking-wider">Pass Marks</th>
                            <th class="px-5 py-3.5 text-right font-bold text-slate-700 text-xs uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($subjects as $subject)
                            @php
                                $isHS = $subject->classes->contains(function ($c) {
                                    $cName = strtolower($c->name);
                                    return str_contains($cName, '11') || str_contains($cName, '12') || str_contains($cName, 'xi');
                                });
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <div class="font-bold text-slate-900">{{ $subject->name }}</div>
                                        @if($subject->code)
                                            <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 font-semibold">{{ $subject->code }}</span>
                                        @endif
                                        @if($isHS)
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-purple-50 text-purple-700 border border-purple-200/60" title="Assigned to Higher Secondary (11th/12th)">
                                                Higher Secondary
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap gap-1">
                                        @forelse($subject->classes as $c)
                                            <span class="px-2 py-0.5 rounded-md text-xs bg-slate-100 text-slate-700 font-semibold border border-slate-200/80">{{ $c->name }}</span>
                                        @empty
                                            <span class="text-xs text-slate-400 italic">All / Not specified</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-center font-black text-slate-900">
                                    <span class="inline-block px-2.5 py-1 rounded-lg {{ $subject->maximum_marks < 100 ? 'bg-indigo-50 text-indigo-700 border border-indigo-200/80 font-bold' : 'bg-slate-50 text-slate-800' }}">
                                        {{ number_format($subject->maximum_marks, 0) }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-center font-semibold text-emerald-700">
                                    {{ number_format($subject->pass_marks, 0) }}
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-right text-xs space-x-1.5">
                                    <!-- Quick Edit Marks Button -->
                                    <button type="button"
                                        @click="openQuickEdit({{ Js::from([
                                            'id' => $subject->id,
                                            'name' => $subject->name,
                                            'maximum_marks' => (float) $subject->maximum_marks,
                                            'pass_marks' => (float) $subject->pass_marks,
                                        ]) }})"
                                        class="inline-flex items-center px-2.5 py-1.5 rounded-lg border border-indigo-200 text-indigo-600 bg-indigo-50/50 hover:bg-indigo-100 font-semibold transition"
                                        title="Quickly change Maximum & Pass Marks">
                                        Change Marks
                                    </button>

                                    <!-- Full Edit -->
                                    <a href="{{ route('admin.subjects.edit', $subject) }}"
                                        class="inline-flex items-center px-2.5 py-1.5 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-50 font-semibold transition">
                                        Edit
                                    </a>

                                    <!-- Delete -->
                                    <form method="POST" action="{{ route('admin.subjects.destroy', $subject) }}" class="inline"
                                        data-confirm="Delete subject '{{ $subject->name }}'? This cannot be undone."
                                        data-confirm-title="Delete Subject"
                                        data-confirm-label="Yes, Delete"
                                        data-confirm-color="rose">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center px-2.5 py-1.5 rounded-lg border border-slate-200 text-rose-600 hover:bg-rose-50 font-semibold transition">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                    No subjects found matching your filter. Click "+ Add Subject" to create one.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $subjects->links() }}
            </div>
        </div>

        <!-- QUICK EDIT MARKS MODAL -->
        <div x-show="quickEditModal" style="display: none;"
            class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto p-4"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">

            <!-- Backdrop -->
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs" @click="quickEditModal = false"></div>

            <!-- Modal Content -->
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6 border border-slate-200/80 z-10 space-y-5">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Change Subject Marks</h2>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Editing marks for: <strong class="text-indigo-600 font-bold" x-text="activeSubject.name"></strong>
                        </p>
                    </div>
                    <button type="button" @click="quickEditModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
                </div>

                <form :action="'{{ url('admin/subjects') }}/' + activeSubject.id + '/marks'" method="POST" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Maximum Marks *</label>
                            <input type="number" step="0.5" name="maximum_marks" x-model="activeSubject.maximum_marks" required
                                class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-2xs font-bold">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Pass Marks *</label>
                            <input type="number" step="0.5" name="pass_marks" x-model="activeSubject.pass_marks" required
                                class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-2xs font-bold text-emerald-700">
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="quickEditModal = false"
                            class="px-4 py-2 text-xs font-semibold rounded-xl text-slate-600 hover:bg-slate-100 transition">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-5 py-2 text-xs font-semibold rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs transition">
                            Save Marks
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
