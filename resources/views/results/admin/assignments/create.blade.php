<x-app-layout>
    <x-slot name="title">{{ __('Assign Teacher to Subject & Class') }}</x-slot>

    @php
        $activeYear = $academicYears->firstWhere('is_active', true) ?? $academicYears->first();
        $defaultYearId = old('academic_year_id', $activeYear?->id ?? '');
        $oldSectionIds = old('section_ids', old('section_id') ? [(int) old('section_id')] : []);
    @endphp

    <div class="w-full space-y-6" x-data="{
        classesPayload: {{ Js::from($classesPayload) }},
        selectedTeacherId: '{{ old('teacher_id', '') }}',
        selectedAcademicYearId: '{{ $defaultYearId }}',
        selectedClassId: '{{ old('class_id', '') }}',
        selectedSectionIds: {{ Js::from($oldSectionIds) }},
        selectedSubjectId: '{{ old('subject_id', '') }}',
        subjectSearch: '',

        // Helper getters
        get currentClass() {
            return this.classesPayload.find(c => String(c.id) === String(this.selectedClassId)) || null;
        },

        get currentSections() {
            return this.currentClass ? this.currentClass.sections : [];
        },

        // When class changes, reset or reconcile selected sections
        onClassChange() {
            this.selectedSectionIds = [];
            this.selectedSubjectId = '';
            // Auto-select all sections if class has sections
            if (this.currentSections.length > 0) {
                this.selectedSectionIds = this.currentSections.map(s => s.id);
            }
        },

        toggleAllSections() {
            if (this.selectedSectionIds.length === this.currentSections.length) {
                this.selectedSectionIds = [];
            } else {
                this.selectedSectionIds = this.currentSections.map(s => s.id);
            }
            this.reconcileSelectedSubject();
        },

        isSectionSelected(secId) {
            return this.selectedSectionIds.includes(Number(secId)) || this.selectedSectionIds.includes(String(secId));
        },

        toggleSection(secId) {
            const numId = Number(secId);
            const strId = String(secId);
            const idx = this.selectedSectionIds.findIndex(id => id === numId || id === strId);
            if (idx > -1) {
                this.selectedSectionIds.splice(idx, 1);
            } else {
                this.selectedSectionIds.push(numId);
            }
            this.reconcileSelectedSubject();
        },

        // Compute ONLY subjects that are added to the selected section(s)
        get availableSubjects() {
            if (!this.currentClass || this.selectedSectionIds.length === 0) {
                return [];
            }

            const chosenSections = this.currentSections.filter(s =>
                this.selectedSectionIds.includes(s.id) || this.selectedSectionIds.includes(String(s.id))
            );

            if (chosenSections.length === 0) return [];

            // Map each subject with section coverage
            const subjectMap = new Map();

            chosenSections.forEach(sec => {
                (sec.subjects || []).forEach(sub => {
                    if (!subjectMap.has(sub.id)) {
                        subjectMap.set(sub.id, {
                            id: sub.id,
                            name: sub.name,
                            code: sub.code || '',
                            maximum_marks: sub.maximum_marks,
                            is_optional: sub.is_optional,
                            sectionIds: [sec.id],
                            sectionNames: [sec.name],
                        });
                    } else {
                        const existing = subjectMap.get(sub.id);
                        if (!existing.sectionIds.includes(sec.id)) {
                            existing.sectionIds.push(sec.id);
                            existing.sectionNames.push(sec.name);
                        }
                    }
                });
            });

            return Array.from(subjectMap.values()).map(sub => ({
                ...sub,
                isAllSections: sub.sectionIds.length === chosenSections.length,
                totalSectionsCount: chosenSections.length
            }));
        },

        get filteredSubjects() {
            const query = this.subjectSearch.trim().toLowerCase();
            if (!query) return this.availableSubjects;
            return this.availableSubjects.filter(sub =>
                (sub.name || '').toLowerCase().includes(query) ||
                (sub.code || '').toLowerCase().includes(query)
            );
        },

        reconcileSelectedSubject() {
            if (!this.selectedSubjectId) return;
            const exists = this.availableSubjects.some(s => String(s.id) === String(this.selectedSubjectId));
            if (!exists) {
                this.selectedSubjectId = '';
            }
        },

        get teacherName() {
            const el = document.getElementById('teacher_id');
            return (el && el.selectedIndex > 0) ? el.options[el.selectedIndex].text : '';
        },

        get academicYearName() {
            const el = document.getElementById('academic_year_id');
            return (el && el.selectedIndex >= 0) ? el.options[el.selectedIndex].text : '';
        },

        get selectedSubjectName() {
            const found = this.availableSubjects.find(s => String(s.id) === String(this.selectedSubjectId));
            return found ? found.name : '';
        },

        get selectedSectionNames() {
            return this.currentSections
                .filter(s => this.isSectionSelected(s.id))
                .map(s => s.name);
        },

        get isReadyToSubmit() {
            return this.selectedTeacherId &&
                this.selectedAcademicYearId &&
                this.selectedClassId &&
                this.selectedSectionIds.length > 0 &&
                this.selectedSubjectId;
        }
    }">
        <!-- Top Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200">
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Assign Teacher to Subject & Class</h1>
                <p class="text-sm text-slate-500 mt-0.5">Allocate faculty members to classes, multiple sections, and
                    section-specific subjects.</p>
            </div>
            <a href="{{ route('admin.assignments.index') }}"
                class="inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-300 rounded-xl font-semibold text-xs text-slate-700 uppercase tracking-wider shadow-sm hover:bg-slate-50 hover:border-slate-400 transition w-fit">
                <svg class="w-4 h-4 mr-1.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Assignments
            </a>
        </div>

        <x-alert />

        <!-- Form Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/90 overflow-hidden w-full">
            <form method="POST" action="{{ route('admin.assignments.store') }}" class="p-5 sm:p-7 lg:p-8 space-y-8">
                @csrf

                <!-- STEP 1: Select Faculty Member -->
                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        <span
                            class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold flex items-center justify-center">1</span>
                        <x-input-label for="teacher_id" :value="__('Select Faculty Member / Teacher')"
                            class="text-base font-bold text-slate-800" />
                        <span class="text-rose-500 text-sm font-semibold">*</span>
                    </div>
                    <p class="text-xs text-slate-500 ml-8">Choose the teacher who will be authorized to enter and
                        evaluate student marks.</p>

                    <div class="ml-8 mt-2">
                        <select id="teacher_id" name="teacher_id" x-model="selectedTeacherId"
                            class="block w-full max-w-2xl rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2.5 px-3"
                            required>
                            <option value="">-- Choose a Teacher --</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}" {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>
                                    {{ $teacher->name }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('teacher_id')" class="mt-2" />
                    </div>
                </div>

                <!-- STEP 2: Academic Year -->
                <div class="space-y-2 pt-4 border-t border-slate-100">
                    <div class="flex items-center gap-2">
                        <span
                            class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold flex items-center justify-center">2</span>
                        <x-input-label for="academic_year_id" :value="__('Academic Year')"
                            class="text-base font-bold text-slate-800" />
                        <span class="text-rose-500 text-sm font-semibold">*</span>
                    </div>
                    <p class="text-xs text-slate-500 ml-8">Select the academic session during which this assignment is
                        effective.</p>

                    <div class="ml-8 mt-2">
                        <select id="academic_year_id" name="academic_year_id" x-model="selectedAcademicYearId"
                            class="block w-full max-w-2xl rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2.5 px-3"
                            required>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ (old('academic_year_id', $defaultYearId) == $year->id) ? 'selected' : '' }}>
                                    {{ $year->name }} {{ $year->is_active ? '(Active Session)' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('academic_year_id')" class="mt-2" />
                    </div>
                </div>

                <!-- STEP 3: Class & Multiple Select Sections Listed Under Class -->
                <div class="space-y-4 pt-4 border-t border-slate-100">
                    <div class="flex items-center gap-2">
                        <span
                            class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold flex items-center justify-center">3</span>
                        <h2 class="text-base font-bold text-slate-800">Class & Sections Allocation</h2>
                        <span class="text-rose-500 text-sm font-semibold">*</span>
                    </div>
                    <p class="text-xs text-slate-500 ml-8">Select a class, then select one or multiple sections listed
                        under that class to assign simultaneously.</p>

                    <div class="ml-8 space-y-4">
                        <!-- Class Dropdown -->
                        <div>
                            <x-input-label for="class_id" :value="__('Select Class')"
                                class="text-xs uppercase tracking-wider font-bold text-slate-600 mb-1.5" />
                            <select id="class_id" name="class_id" x-model="selectedClassId" @change="onClassChange()"
                                class="block w-full max-w-2xl rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2.5 px-3"
                                required>
                                <option value="">-- Choose Class --</option>
                                <template x-for="c in classesPayload" :key="c.id">
                                    <option :value="c.id" x-text="c.name"></option>
                                </template>
                            </select>
                            <x-input-error :messages="$errors->get('class_id')" class="mt-2" />
                        </div>

                        <!-- Sections Listed Under Selected Class (Multi-Select) -->
                        <div class="p-4 bg-slate-50/80 rounded-xl border border-slate-200/90 max-w-3xl space-y-3">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <div>
                                    <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                                        <span>Sections Under Class</span>
                                        <template x-if="currentClass">
                                            <span
                                                class="px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800"
                                                x-text="currentClass.name"></span>
                                        </template>
                                    </h3>
                                    <p class="text-xs text-slate-500 mt-0.5">Check all the sections the teacher
                                        instructs for this subject.</p>
                                </div>

                                <template x-if="currentSections.length > 0">
                                    <div class="flex items-center gap-2">
                                        <button type="button" @click="toggleAllSections()"
                                            class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-lg bg-white border border-slate-300 text-slate-700 hover:bg-slate-100 transition shadow-2xs">
                                            <span
                                                x-text="selectedSectionIds.length === currentSections.length ? 'Deselect All' : 'Select All (' + currentSections.length + ')'"></span>
                                        </button>
                                    </div>
                                </template>
                            </div>

                            <!-- If No Class Selected -->
                            <template x-if="!selectedClassId">
                                <div
                                    class="py-6 px-4 text-center rounded-lg border-2 border-dashed border-slate-200 bg-white">
                                    <svg class="w-8 h-8 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                    <p class="text-xs font-medium text-slate-500">Please choose a class above to view
                                        and multi-select its sections.</p>
                                </div>
                            </template>

                            <!-- If Class Has No Sections -->
                            <template x-if="selectedClassId && currentSections.length === 0">
                                <div
                                    class="py-6 px-4 text-center rounded-lg border-2 border-dashed border-amber-200 bg-amber-50/50">
                                    <p class="text-xs font-medium text-amber-800">No active sections found for this
                                        class. Please configure sections in Academic Setup first.</p>
                                </div>
                            </template>

                            <!-- Sections Multi-Select Grid -->
                            <template x-if="selectedClassId && currentSections.length > 0">
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2.5 pt-1">
                                    <template x-for="sec in currentSections" :key="sec.id">
                                        <label
                                            class="flex items-start gap-3 p-3 rounded-xl border cursor-pointer select-none transition-all duration-150"
                                            :class="isSectionSelected(sec.id)
                                                ? 'bg-indigo-50/70 border-indigo-300 ring-2 ring-indigo-500/20 shadow-2xs'
                                                : 'bg-white border-slate-200 hover:border-slate-300 hover:bg-slate-50/60'">
                                            <input type="checkbox" name="section_ids[]" :value="sec.id"
                                                :checked="isSectionSelected(sec.id)" @change="toggleSection(sec.id)"
                                                class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 mt-0.5">
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center justify-between">
                                                    <span class="text-sm font-bold text-slate-900"
                                                        x-text="sec.name"></span>
                                                    <template x-if="isSectionSelected(sec.id)">
                                                        <span
                                                            class="inline-flex items-center text-[10px] font-bold text-indigo-600 uppercase">Selected</span>
                                                    </template>
                                                </div>
                                                <p class="text-[11px] text-slate-500 mt-0.5"
                                                    x-text="sec.subjects.length + ' subjects added'"></p>
                                            </div>
                                        </label>
                                    </template>
                                </div>
                            </template>

                            <!-- Section Selection Summary -->
                            <template x-if="selectedSectionIds.length > 0">
                                <div class="flex items-center gap-1.5 text-xs text-indigo-700 font-semibold pt-1">
                                    <svg class="w-4 h-4 text-indigo-600 shrink-0" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>Selected <strong x-text="selectedSectionIds.length"></strong> section(s):
                                        <span x-text="selectedSectionNames.join(', ')"></span></span>
                                </div>
                            </template>

                            <x-input-error :messages="$errors->get('section_ids')" class="mt-2" />
                            <x-input-error :messages="$errors->get('section_id')" class="mt-2" />
                        </div>
                    </div>
                </div>

                <!-- STEP 4: Curriculum Subject (Only Added for the Selected Section(s)) -->
                <div class="space-y-4 pt-4 border-t border-slate-100">
                    <div class="flex items-center gap-2">
                        <span
                            class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold flex items-center justify-center">4</span>
                        <x-input-label for="subject_id" :value="__('Select Subject (Only Added for Selected Section)')"
                            class="text-base font-bold text-slate-800" />
                        <span class="text-rose-500 text-sm font-semibold">*</span>
                    </div>
                    <p class="text-xs text-slate-500 ml-8">Only subjects that are configured in the curriculum for the
                        selected section(s) appear below.</p>

                    <div class="ml-8 space-y-3">
                        <!-- If No Sections Selected Yet -->
                        <template x-if="selectedSectionIds.length === 0">
                            <div
                                class="p-6 text-center rounded-xl border-2 border-dashed border-slate-200 bg-slate-50/50 max-w-3xl">
                                <svg class="w-8 h-8 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                                <p class="text-xs font-medium text-slate-500">Please select at least one section in Step
                                    3 above to load the subjects added for it.</p>
                            </div>
                        </template>

                        <!-- If Sections Selected But No Subjects Added -->
                        <template x-if="selectedSectionIds.length > 0 && availableSubjects.length === 0">
                            <div
                                class="p-6 text-center rounded-xl border-2 border-dashed border-amber-200 bg-amber-50/50 max-w-3xl">
                                <p class="text-xs font-semibold text-amber-800">No subjects are added for the selected
                                    section(s) yet.</p>
                                <p class="text-[11px] text-amber-600 mt-1">Please assign subjects to these sections
                                    under <em>Academic Setup &rarr; Sections</em> first.</p>
                            </div>
                        </template>

                        <!-- Subject Cards / Radio Selector Grid -->
                        <template x-if="selectedSectionIds.length > 0 && availableSubjects.length > 0">
                            <div class="max-w-3xl space-y-3">
                                <!-- Search filter if > 4 subjects -->
                                <template x-if="availableSubjects.length > 4">
                                    <div class="relative max-w-md">
                                        <input type="text" x-model="subjectSearch"
                                            placeholder="Search subjects added for this section..."
                                            class="block w-full text-xs rounded-xl border-slate-300 pl-8 pr-3 py-1.5 focus:border-indigo-500 focus:ring-indigo-500 shadow-2xs">
                                        <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5 pointer-events-none"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </div>
                                </template>

                                <!-- Subject Option Cards -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                    <template x-for="sub in filteredSubjects" :key="sub.id">
                                        <label
                                            class="flex items-start gap-3 p-3.5 rounded-xl border cursor-pointer select-none transition-all duration-150"
                                            :class="String(selectedSubjectId) === String(sub.id)
                                                ? 'bg-indigo-50/80 border-indigo-400 ring-2 ring-indigo-500/20 shadow-sm'
                                                : 'bg-white border-slate-200 hover:border-slate-300 hover:bg-slate-50/70'">
                                            <input type="radio" id="subject_id" name="subject_id" :value="sub.id"
                                                x-model="selectedSubjectId" required
                                                class="text-indigo-600 border-slate-300 focus:ring-indigo-500 mt-1">
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center justify-between gap-1">
                                                    <span class="text-sm font-bold text-slate-900"
                                                        x-text="sub.name"></span>
                                                    <template x-if="sub.code">
                                                        <span
                                                            class="text-[10px] font-mono font-semibold px-1.5 py-0.5 rounded bg-slate-100 text-slate-600"
                                                            x-text="sub.code"></span>
                                                    </template>
                                                </div>

                                                <div class="flex flex-wrap items-center gap-1.5 mt-1.5 text-[11px]">
                                                    <span class="text-slate-500">Max Marks: <strong
                                                            class="text-slate-700"
                                                            x-text="sub.maximum_marks"></strong></span>
                                                    <span class="text-slate-300">&bull;</span>
                                                    <span class="px-1.5 py-0.2 rounded font-medium text-[10px]"
                                                        :class="sub.is_optional ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700'"
                                                        x-text="sub.is_optional ? 'Elective' : 'Core/Compulsory'"></span>
                                                </div>

                                                <!-- Section Coverage Note if Multiple Sections Selected -->
                                                <template x-if="selectedSectionIds.length > 1">
                                                    <div class="mt-1.5 text-[10px]">
                                                        <template x-if="sub.isAllSections">
                                                            <span
                                                                class="text-emerald-600 font-semibold inline-flex items-center gap-1">
                                                                <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                                    viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2" d="M5 13l4 4L19 7" />
                                                                </svg>
                                                                Added in all selected sections
                                                            </span>
                                                        </template>
                                                        <template x-if="!sub.isAllSections">
                                                            <span class="text-amber-700 font-medium">
                                                                Added in: <span
                                                                    x-text="sub.sectionNames.join(', ')"></span>
                                                            </span>
                                                        </template>
                                                    </div>
                                                </template>
                                            </div>
                                        </label>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <x-input-error :messages="$errors->get('subject_id')" class="mt-2" />
                    </div>
                </div>

                <!-- LIVE ASSIGNMENT PREVIEW / CONFIRMATION SUMMARY -->
                <div
                    class="p-5 rounded-2xl bg-gradient-to-br from-indigo-50/60 via-slate-50 to-purple-50/30 border border-indigo-100/80 max-w-3xl ml-8">
                    <div class="flex items-center gap-2 mb-3">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-indigo-900">Assignment Summary</h3>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
                        <div class="bg-white p-2.5 rounded-xl border border-slate-200/70 shadow-2xs">
                            <span
                                class="text-slate-400 block text-[10px] font-bold uppercase tracking-wider">Teacher</span>
                            <span class="font-bold text-slate-800 truncate block mt-0.5"
                                x-text="teacherName || 'Not Selected'"></span>
                        </div>

                        <div class="bg-white p-2.5 rounded-xl border border-slate-200/70 shadow-2xs">
                            <span class="text-slate-400 block text-[10px] font-bold uppercase tracking-wider">Academic
                                Year</span>
                            <span class="font-bold text-slate-800 truncate block mt-0.5"
                                x-text="academicYearName || 'Not Selected'"></span>
                        </div>

                        <div class="bg-white p-2.5 rounded-xl border border-slate-200/70 shadow-2xs">
                            <span class="text-slate-400 block text-[10px] font-bold uppercase tracking-wider">Class &
                                Sections</span>
                            <span class="font-bold text-slate-800 truncate block mt-0.5"
                                x-text="currentClass ? currentClass.name + ' (' + selectedSectionIds.length + ' secs)' : 'Not Selected'"></span>
                        </div>

                        <div class="bg-white p-2.5 rounded-xl border border-slate-200/70 shadow-2xs">
                            <span
                                class="text-slate-400 block text-[10px] font-bold uppercase tracking-wider">Subject</span>
                            <span class="font-bold text-indigo-700 truncate block mt-0.5"
                                x-text="selectedSubjectName || 'Not Selected'"></span>
                        </div>
                    </div>

                    <div
                        class="mt-3 flex items-center justify-between text-xs text-slate-500 pt-2 border-t border-slate-200/60">
                        <span>Total assignments to be generated:</span>
                        <span class="font-bold text-indigo-700"
                            x-text="selectedSectionIds.length + ' record(s)'"></span>
                    </div>
                </div>

                <!-- Form Action Buttons -->
                <div
                    class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 pt-6 border-t border-slate-100">
                    <a href="{{ route('admin.assignments.index') }}"
                        class="w-full sm:w-auto text-center px-5 py-2.5 text-sm font-semibold text-slate-600 hover:text-slate-900 transition">
                        Cancel
                    </a>
                    <button type="submit" :disabled="!isReadyToSubmit"
                        :class="isReadyToSubmit ? 'bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm' : 'bg-slate-200 text-slate-400 cursor-not-allowed'"
                        class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-2.5 text-sm font-semibold rounded-xl transition">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span
                            x-text="selectedSectionIds.length > 1 ? 'Assign to ' + selectedSectionIds.length + ' Sections' : 'Assign Teacher'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>