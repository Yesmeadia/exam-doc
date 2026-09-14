<x-app-layout>
    <x-slot name="title">{{ __('Assign Teacher to Subject & Class') }}</x-slot>

    <div class="w-full space-y-6" x-data="{
        classes: {{ Js::from($classes) }},
        selectedClassId: '{{ old('class_id') }}',
        selectedSectionId: '{{ old('section_id') }}',
        allSubjects: {{ Js::from($subjects->map(fn($s) => [
            'id' => $s->id,
            'name' => $s->name,
            'class_ids' => $s->classes->pluck('id')->toArray(),
        ])) }},
        selectedSubjectId: '{{ old('subject_id') }}',
        sections: [],
        filteredSubjects: [],
        updateSections() {
            const found = this.classes.find(c => String(c.id) === String(this.selectedClassId));
            this.sections = (found && found.sections) ? found.sections : [];
            if (!this.sections.find(s => String(s.id) === String(this.selectedSectionId))) {
                this.selectedSectionId = this.sections.length > 0 ? this.sections[0].id : '';
            }
            this.updateSubjects();
        },
        updateSubjects() {
            if (!this.selectedClassId) {
                this.filteredSubjects = this.allSubjects;
                return;
            }
            const foundClass = this.classes.find(c => String(c.id) === String(this.selectedClassId));
            const className = foundClass ? (foundClass.name || '').toLowerCase() : '';
            const isHigherSecondary = /11|12|xi|xii/i.test(className);

            const foundSection = this.sections.find(s => String(s.id) === String(this.selectedSectionId));
            const sectionName = foundSection ? (foundSection.name || '').toLowerCase() : '';
            const isScience = sectionName.includes('science');
            const isHumanities = sectionName.includes('humanities') || sectionName.includes('arts');

            this.filteredSubjects = this.allSubjects.filter(sub => {
                if (sub.class_ids.length > 0 && !sub.class_ids.includes(Number(this.selectedClassId))) {
                    return false;
                }
                if (isHigherSecondary) {
                    const subName = (sub.name || '').toLowerCase();
                    if (isScience) {
                        if (/(history|political science|civics|education)/i.test(subName)) {
                            return false;
                        }
                    } else if (isHumanities) {
                        if (/(biology|bio|physics|chemistry)/i.test(subName)) {
                            return false;
                        }
                    }
                }
                return true;
            });

            if (!this.filteredSubjects.find(s => String(s.id) === String(this.selectedSubjectId))) {
                this.selectedSubjectId = this.filteredSubjects.length > 0 ? this.filteredSubjects[0].id : '';
            }
        }
    }" x-init="updateSections()">
        <!-- Top Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200">
            <div>
                <p class="text-sm text-slate-500">Allocate faculty members to classes, sections, and subjects.</p>
            </div>
            <a href="{{ route('admin.assignments.index') }}"
                class="inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-300 rounded-lg font-semibold text-xs text-slate-700 uppercase tracking-wider shadow-sm hover:bg-slate-50 transition w-fit">
                &larr; Back to Assignments
            </a>
        </div>

        <x-alert />

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden w-full">
            <form method="POST" action="{{ route('admin.assignments.store') }}" class="p-4 sm:p-6 lg:p-8 space-y-6">
                @csrf

                <div>
                    <x-input-label for="teacher_id" :value="__('Select Faculty Member')"
                        class="font-semibold text-slate-700" />
                    <select id="teacher_id" name="teacher_id"
                        class="block mt-1.5 w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                        required>
                        <option value="">-- Select Teacher --</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>
                                {{ $teacher->name }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('teacher_id')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="academic_year_id" :value="__('Academic Year')"
                        class="font-semibold text-slate-700" />
                    <select id="academic_year_id" name="academic_year_id"
                        class="block mt-1.5 w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                        required>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ (old('academic_year_id') == $year->id || $year->is_active) ? 'selected' : '' }}>
                                {{ $year->name }} {{ $year->is_active ? '(Active Session)' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('academic_year_id')" class="mt-2" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="class_id" :value="__('Class')" class="font-semibold text-slate-700" />
                        <select id="class_id" name="class_id" x-model="selectedClassId" @change="updateSections()"
                            class="block mt-1.5 w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                            required>
                            <option value="">-- Select Class --</option>
                            <template x-for="c in classes" :key="c.id">
                                <option :value="c.id" x-text="c.name"></option>
                            </template>
                        </select>
                        <x-input-error :messages="$errors->get('class_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="section_id" :value="__('Section')" class="font-semibold text-slate-700" />
                        <select id="section_id" name="section_id"
                            class="block mt-1.5 w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                            required>
                            <option value="">-- Select Section --</option>
                            <template x-for="s in sections" :key="s.id">
                                <option :value="s.id" x-text="s.name"></option>
                            </template>
                        </select>
                        <x-input-error :messages="$errors->get('section_id')" class="mt-2" />
                    </div>
                </div>

                <div>
                    <x-input-label for="subject_id" :value="__('Subject')" class="font-semibold text-slate-700" />
                    <select id="subject_id" name="subject_id"
                        class="block mt-1.5 w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                        required>
                        <option value="">-- Select Subject --</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                                {{ $subject->name }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('subject_id')" class="mt-2" />
                </div>

                <div
                    class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <a href="{{ route('admin.assignments.index') }}"
                        class="w-full sm:w-auto text-center px-4 py-2.5 text-sm font-medium text-slate-600 hover:text-slate-900">Cancel</a>
                    <button type="submit"
                        class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                        {{ __('Assign Teacher') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>