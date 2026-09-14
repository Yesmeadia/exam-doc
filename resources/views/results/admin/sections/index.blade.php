<x-app-layout>
    <x-slot name="title">{{ __('Sections Management') }}</x-slot>

    <div class="w-full space-y-6" x-data="{
        addSectionModal: false,
        editSectionModal: false,
        assignSubjectsModal: false,
        currentSection: null,
        editData: { id: null, class_id: '', name: '', status: 'active' },
        assignData: {
            section_id: null,
            section_name: '',
            class_name: '',
            compulsory_ids: [],
            optional_ids: []
        },
        openEdit(section) {
            this.editData = {
                id: section.id,
                class_id: section.class_id,
                name: section.name,
                status: section.status
            };
            this.editSectionModal = true;
        },
        openAssignSubjects(section) {
            this.assignData.section_id = section.id;
            this.assignData.section_name = section.name;
            this.assignData.class_name = section.school_class ? section.school_class.name : '';
            this.assignData.compulsory_ids = (section.compulsory_subjects || []).map(s => s.id);
            this.assignData.optional_ids = (section.optional_subjects || []).map(s => s.id);
            this.assignSubjectsModal = true;
        },
        toggleCompulsory(subjectId) {
            const numId = Number(subjectId);
            if (this.assignData.compulsory_ids.includes(numId)) {
                this.assignData.compulsory_ids = this.assignData.compulsory_ids.filter(id => id !== numId);
            } else {
                this.assignData.compulsory_ids.push(numId);
                // Remove from optional if it was there
                this.assignData.optional_ids = this.assignData.optional_ids.filter(id => id !== numId);
            }
        },
        toggleOptional(subjectId) {
            const numId = Number(subjectId);
            if (this.assignData.optional_ids.includes(numId)) {
                this.assignData.optional_ids = this.assignData.optional_ids.filter(id => id !== numId);
            } else {
                this.assignData.optional_ids.push(numId);
                // Remove from compulsory if it was there
                this.assignData.compulsory_ids = this.assignData.compulsory_ids.filter(id => id !== numId);
            }
        }
    }">
        <!-- Top Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200">
            <div>
                <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Sections Management</h2>
                <p class="text-sm text-slate-500 mt-0.5">Manage class sections and directly assign compulsory & optional curriculum subjects.</p>
            </div>
            <div class="flex items-center gap-2.5">
                <a href="{{ route('admin.classes.index') }}"
                    class="inline-flex items-center px-3.5 py-2 bg-white border border-slate-300 rounded-xl text-xs font-semibold text-slate-700 hover:bg-slate-50 shadow-xs transition">
                    &larr; View Classes
                </a>
                <button @click="addSectionModal = true"
                    class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 rounded-xl font-bold text-xs text-white uppercase tracking-wider hover:bg-indigo-700 shadow-sm transition">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add New Section
                </button>
            </div>
        </div>

        <x-alert />

        <!-- Filter Bar -->
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200/80 flex flex-col md:flex-row items-center justify-between gap-4">
            <form method="GET" action="{{ route('admin.sections.index') }}" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                <!-- Class Filter -->
                <div class="min-w-[180px]">
                    <select name="class_id" onchange="this.form.submit()"
                        class="block w-full rounded-xl border-slate-300 shadow-2xs text-xs font-semibold text-slate-800 py-2 pl-3 pr-8 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All School Classes</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ (string)$classId === (string)$c->id ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <select name="status" onchange="this.form.submit()"
                        class="block w-full rounded-xl border-slate-300 shadow-2xs text-xs font-semibold text-slate-800 py-2 pl-3 pr-8 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="all" {{ $status === 'all' || !$status ? 'selected' : '' }}>All Statuses</option>
                        <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <!-- Search Filter -->
                <div class="relative w-full sm:w-64">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search section name..."
                        class="block w-full rounded-xl border-slate-300 shadow-2xs text-xs font-medium text-slate-800 py-2 pl-3 pr-8 focus:ring-indigo-500 focus:border-indigo-500 placeholder-slate-400">
                    <button type="submit" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>
                </div>

                @if($classId || $search || ($status && $status !== 'all'))
                    <a href="{{ route('admin.sections.index') }}" class="text-xs font-bold text-rose-600 hover:text-rose-800 transition">
                        Reset Filters
                    </a>
                @endif
            </form>

            <span class="text-xs font-semibold text-slate-500 whitespace-nowrap">
                Total Sections: <strong class="text-slate-900">{{ $sections->total() }}</strong>
            </span>
        </div>

        <!-- Sections Tabulation Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden w-full">
            <div class="overflow-x-auto w-full">
                <table class="w-full text-left text-xs border-collapse divide-y divide-slate-200">
                    <thead class="bg-slate-50 text-slate-700 font-bold uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="py-3.5 px-4">Class</th>
                            <th class="py-3.5 px-4">Section Name</th>
                            <th class="py-3.5 px-4">Enrolled Students</th>
                            <th class="py-3.5 px-4">Compulsory Subjects</th>
                            <th class="py-3.5 px-4">Optional / Elective Subjects</th>
                            <th class="py-3.5 px-4 text-center">Status</th>
                            <th class="py-3.5 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($sections as $section)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <!-- Class -->
                                <td class="py-3.5 px-4 font-black text-slate-900 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-indigo-50 text-indigo-800 border border-indigo-200/60">
                                        {{ $section->schoolClass?->name ?? '—' }}
                                    </span>
                                </td>

                                <!-- Section Name -->
                                <td class="py-3.5 px-4 font-bold text-slate-900 text-sm whitespace-nowrap">
                                    {{ $section->name }}
                                </td>

                                <!-- Enrolled Students -->
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <a href="{{ route('admin.students.index', ['class_id' => $section->class_id, 'section_id' => $section->id]) }}"
                                        class="inline-flex items-center gap-1 font-bold text-slate-700 hover:text-indigo-600 transition" title="Filter students in this section">
                                        <span>{{ $section->students_count }} students</span>
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                </td>

                                <!-- Compulsory Subjects -->
                                <td class="py-3.5 px-4">
                                    @if($section->compulsorySubjects->isNotEmpty())
                                        <div class="flex flex-wrap gap-1 max-w-xs">
                                            @foreach($section->compulsorySubjects as $sub)
                                                <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60" title="{{ $sub->name }} (Max: {{ (int)$sub->maximum_marks }})">
                                                    {{ $sub->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-slate-400 text-[11px] italic">No compulsory subjects assigned</span>
                                    @endif
                                </td>

                                <!-- Optional Subjects -->
                                <td class="py-3.5 px-4">
                                    @if($section->optionalSubjects->isNotEmpty())
                                        <div class="flex flex-wrap gap-1 max-w-xs">
                                            @foreach($section->optionalSubjects as $sub)
                                                <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold bg-purple-50 text-purple-700 border border-purple-200/60" title="{{ $sub->name }} (Max: {{ (int)$sub->maximum_marks }})">
                                                    {{ $sub->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-slate-400 text-[11px] italic">No optional subjects assigned</span>
                                    @endif
                                </td>

                                <!-- Status -->
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold {{ $section->status === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/60' : 'bg-slate-100 text-slate-600' }}">
                                        {{ ucfirst($section->status) }}
                                    </span>
                                </td>

                                <!-- Actions -->
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- Assign Subjects Button -->
                                        <button type="button" @click="openAssignSubjects({{ Js::from($section) }})"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200/70 text-xs font-bold transition"
                                            title="Assign compulsory and optional curriculum subjects">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                            </svg>
                                            Assign Subjects
                                        </button>

                                        <!-- Edit Section -->
                                        <button type="button" @click="openEdit({{ Js::from($section) }})"
                                            class="inline-flex items-center px-2 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                                            Edit
                                        </button>

                                        <!-- Delete Section -->
                                        <form method="POST" action="{{ route('admin.sections.destroy', $section) }}" class="inline"
                                            data-confirm="Delete section '{{ $section->name }}'? Students in this section will become unassigned."
                                            data-confirm-title="Delete Section"
                                            data-confirm-label="Yes, Delete"
                                            data-confirm-color="rose">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center px-2 py-1.5 rounded-lg hover:bg-rose-50 text-rose-600 hover:text-rose-800 text-xs font-bold transition">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">
                                    No sections found matching your filter criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($sections->hasPages())
                <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                    {{ $sections->links() }}
                </div>
            @endif
        </div>

        <!-- Modal: Add Section -->
        <div x-show="addSectionModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs transition-opacity" @click="addSectionModal = false"></div>
                <div class="bg-white rounded-2xl overflow-hidden shadow-xl transform transition-all sm:max-w-lg w-full z-10 p-6 border border-slate-100">
                    <h3 class="text-base font-bold text-slate-900 mb-1">Create New Section</h3>
                    <p class="text-xs text-slate-500 mb-4">Create a section and select which class it belongs to.</p>

                    <form method="POST" action="{{ route('admin.sections.store') }}" class="space-y-4">
                        @csrf
                        <div>
                            <x-input-label for="add_class_id" :value="__('Select School Class')" class="font-bold text-slate-700" />
                            <select id="add_class_id" name="class_id" required
                                class="block mt-1.5 w-full rounded-xl border-slate-300 shadow-2xs focus:border-indigo-500 focus:ring-indigo-500 text-sm font-semibold text-slate-800">
                                <option value="">-- Choose Class --</option>
                                @foreach($classes as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <x-input-label for="add_section_name" :value="__('Section Name (e.g. A, B, C)')" class="font-bold text-slate-700" />
                            <x-text-input id="add_section_name" class="block mt-1.5 w-full rounded-xl border-slate-300 shadow-2xs focus:border-indigo-500 focus:ring-indigo-500"
                                type="text" name="name" required placeholder="e.g. A, B" />
                        </div>

                        <div>
                            <x-input-label for="add_status" :value="__('Status')" class="font-bold text-slate-700" />
                            <select id="add_status" name="status"
                                class="block mt-1.5 w-full rounded-xl border-slate-300 shadow-2xs focus:border-indigo-500 focus:ring-indigo-500 text-sm font-semibold text-slate-800">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                        <div class="flex justify-end gap-2.5 pt-4 border-t border-slate-100">
                            <button type="button" @click="addSectionModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-900">Cancel</button>
                            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl uppercase tracking-wider shadow-sm transition">Create Section</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal: Edit Section -->
        <div x-show="editSectionModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs transition-opacity" @click="editSectionModal = false"></div>
                <div class="bg-white rounded-2xl overflow-hidden shadow-xl transform transition-all sm:max-w-lg w-full z-10 p-6 border border-slate-100">
                    <h3 class="text-base font-bold text-slate-900 mb-1">Edit Section Details</h3>
                    <p class="text-xs text-slate-500 mb-4">Modify section attributes and parent class.</p>

                    <form :action="'{{ url('admin/results/sections') }}/' + editData.id" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')
                        <div>
                            <x-input-label for="edit_class_id" :value="__('Parent Class')" class="font-bold text-slate-700" />
                            <select id="edit_class_id" name="class_id" x-model="editData.class_id" required
                                class="block mt-1.5 w-full rounded-xl border-slate-300 shadow-2xs focus:border-indigo-500 focus:ring-indigo-500 text-sm font-semibold text-slate-800">
                                @foreach($classes as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <x-input-label for="edit_section_name" :value="__('Section Name')" class="font-bold text-slate-700" />
                            <x-text-input id="edit_section_name" class="block mt-1.5 w-full rounded-xl border-slate-300 shadow-2xs focus:border-indigo-500 focus:ring-indigo-500"
                                type="text" name="name" x-model="editData.name" required />
                        </div>

                        <div>
                            <x-input-label for="edit_status" :value="__('Status')" class="font-bold text-slate-700" />
                            <select id="edit_status" name="status" x-model="editData.status"
                                class="block mt-1.5 w-full rounded-xl border-slate-300 shadow-2xs focus:border-indigo-500 focus:ring-indigo-500 text-sm font-semibold text-slate-800">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                        <div class="flex justify-end gap-2.5 pt-4 border-t border-slate-100">
                            <button type="button" @click="editSectionModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-900">Cancel</button>
                            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl uppercase tracking-wider shadow-sm transition">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal: Assign Curriculum Subjects to Section -->
        <div x-show="assignSubjectsModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 py-6">
                <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs transition-opacity" @click="assignSubjectsModal = false"></div>
                <div class="bg-white rounded-2xl overflow-hidden shadow-2xl transform transition-all max-w-2xl w-full z-10 p-6 border border-slate-100 space-y-5">
                    <div class="border-b border-slate-200 pb-3 flex items-start justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg font-black text-slate-900">Assign Curriculum Subjects</h3>
                                <span class="px-2 py-0.5 rounded text-xs font-bold bg-blue-50 text-blue-800 border border-blue-200/60" x-text="assignData.class_name + ' - ' + assignData.section_name"></span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">
                                Check subjects below as <strong>Compulsory / Core</strong> or <strong>Optional / Elective</strong> for students enrolled in this section.
                            </p>
                        </div>
                        <button type="button" @click="assignSubjectsModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold leading-none">&times;</button>
                    </div>

                    <form :action="'{{ url('admin/results/sections') }}/' + assignData.section_id + '/assign-subjects'" method="POST" class="space-y-5">
                        @csrf

                        <!-- Hidden Inputs for Submission -->
                        <template x-for="id in assignData.compulsory_ids" :key="'comp_' + id">
                            <input type="hidden" name="compulsory_subject_ids[]" :value="id">
                        </template>
                        <template x-for="id in assignData.optional_ids" :key="'opt_' + id">
                            <input type="hidden" name="optional_subject_ids[]" :value="id">
                        </template>

                        <!-- Interactive Subject Selection List -->
                        <div class="max-h-96 overflow-y-auto pr-1 space-y-2.5">
                            @forelse($allSubjects as $sub)
                                <div class="p-3 rounded-xl border border-slate-200 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:border-indigo-300 transition">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-slate-900 text-sm">{{ $sub->name }}</span>
                                            <span class="text-[11px] font-mono text-slate-500 uppercase">({{ $sub->code }})</span>
                                        </div>
                                        <span class="text-[11px] text-slate-400">Max Marks: {{ (int)$sub->maximum_marks }} | Pass Marks: {{ (int)$sub->pass_marks }}</span>
                                    </div>

                                    <!-- Choice Buttons -->
                                    <div class="flex items-center gap-2 shrink-0">
                                        <!-- Compulsory Choice Button -->
                                        <button type="button" @click="toggleCompulsory({{ $sub->id }})"
                                            :class="assignData.compulsory_ids.includes({{ $sub->id }})
                                                ? 'bg-emerald-600 text-white font-bold shadow-xs'
                                                : 'bg-white text-slate-700 border border-slate-300 hover:bg-slate-50'"
                                            class="px-2.5 py-1.5 rounded-lg text-xs font-semibold transition inline-flex items-center gap-1">
                                            <svg x-show="assignData.compulsory_ids.includes({{ $sub->id }})" class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                            Compulsory
                                        </button>

                                        <!-- Optional Choice Button -->
                                        <button type="button" @click="toggleOptional({{ $sub->id }})"
                                            :class="assignData.optional_ids.includes({{ $sub->id }})
                                                ? 'bg-purple-600 text-white font-bold shadow-xs'
                                                : 'bg-white text-slate-700 border border-slate-300 hover:bg-slate-50'"
                                            class="px-2.5 py-1.5 rounded-lg text-xs font-semibold transition inline-flex items-center gap-1">
                                            <svg x-show="assignData.optional_ids.includes({{ $sub->id }})" class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                            Optional
                                        </button>
                                    </div>
                                </div>
                            @empty
                                <div class="p-6 text-center text-slate-400">
                                    No subjects created yet. Create subjects under Academic &rarr; Subjects first.
                                </div>
                            @endforelse
                        </div>

                        <!-- Summary selection counters -->
                        <div class="flex items-center justify-between text-xs text-slate-600 bg-slate-100/70 p-3 rounded-xl">
                            <div>
                                Selected:
                                <strong class="text-emerald-700" x-text="assignData.compulsory_ids.length"></strong> Compulsory,
                                <strong class="text-purple-700" x-text="assignData.optional_ids.length"></strong> Optional
                            </div>
                            <div class="flex gap-2">
                                <button type="button" @click="assignData.compulsory_ids = []; assignData.optional_ids = [];"
                                    class="text-rose-600 hover:text-rose-800 font-semibold transition">
                                    Clear All
                                </button>
                            </div>
                        </div>

                        <div class="flex justify-end gap-2.5 pt-4 border-t border-slate-100">
                            <button type="button" @click="assignSubjectsModal = false"
                                class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-900">Cancel</button>
                            <button type="submit"
                                class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl uppercase tracking-wider shadow-sm transition">
                                Save Subject Assignments
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
