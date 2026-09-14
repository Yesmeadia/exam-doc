<x-app-layout>
    <x-slot name="title">{{ __('Add Student') }}</x-slot>

    <div class="w-full space-y-6" x-data="{
        classes: {{ Js::from($classes) }},
        selectedClassId: '{{ old('class_id') }}',
        sections: [],
        updateSections() {
            const found = this.classes.find(c => c.id == this.selectedClassId);
            this.sections = found ? found.sections : [];
        }
    }" x-init="updateSections()">
        <!-- Top Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200">
            <div>
                <p class="text-sm text-slate-500">Register a new student and enroll them into an academic year and class.</p>
            </div>
            <a href="{{ route('admin.students.index') }}" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-300 rounded-lg font-semibold text-xs text-slate-700 uppercase tracking-wider shadow-sm hover:bg-slate-50 transition w-fit">
                &larr; Back to Students
            </a>
        </div>

        <x-alert />

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden w-full">
            <form method="POST" action="{{ route('admin.students.store') }}" class="p-4 sm:p-6 lg:p-8 space-y-6">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="student_id" :value="__('Student ID (Unique)')" class="font-semibold text-slate-700" />
                        <x-text-input id="student_id" class="block mt-1.5 w-full font-mono uppercase rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" type="text" name="student_id" :value="old('student_id')" required autofocus placeholder="ST001" oninput="this.value = this.value.toUpperCase()" />
                        <x-input-error :messages="$errors->get('student_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="name" :value="__('Full Student Name')" class="font-semibold text-slate-700" />
                        <x-text-input id="name" class="block mt-1.5 w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" type="text" name="name" :value="old('name')" required placeholder="Abdul Rahman" />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                    <div>
                        <x-input-label for="gender" :value="__('Gender (Optional)')" class="font-semibold text-slate-700" />
                        <select id="gender" name="gender" class="block mt-1.5 w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">-- Select Gender --</option>
                            <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                            <option value="other" {{ old('gender') === 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>

                    <div>
                        <x-input-label for="academic_year_id" :value="__('Academic Year')" class="font-semibold text-slate-700" />
                        <select id="academic_year_id" name="academic_year_id" class="block mt-1.5 w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" required>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ (old('academic_year_id') == $year->id || $year->is_active) ? 'selected' : '' }}>
                                    {{ $year->name }} {{ $year->is_active ? '(Active)' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('academic_year_id')" class="mt-2" />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <x-input-label for="class_id" :value="__('Class')" class="font-semibold text-slate-700" />
                        <select id="class_id" name="class_id" x-model="selectedClassId" @change="updateSections()" class="block mt-1.5 w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" required>
                            <option value="">-- Select Class --</option>
                            <template x-for="c in classes" :key="c.id">
                                <option :value="c.id" x-text="c.name"></option>
                            </template>
                        </select>
                        <x-input-error :messages="$errors->get('class_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="section_id" :value="__('Section')" class="font-semibold text-slate-700" />
                        <select id="section_id" name="section_id" class="block mt-1.5 w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" required>
                            <option value="">-- Select Section --</option>
                            <template x-for="s in sections" :key="s.id">
                                <option :value="s.id" x-text="s.name"></option>
                            </template>
                        </select>
                        <x-input-error :messages="$errors->get('section_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="roll_no" :value="__('Roll Number')" class="font-semibold text-slate-700" />
                        <x-text-input id="roll_no" class="block mt-1.5 w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" type="number" name="roll_no" :value="old('roll_no')" required placeholder="1" />
                        <x-input-error :messages="$errors->get('roll_no')" class="mt-2" />
                        <p class="text-[11px] text-slate-400 mt-1">Unique within Class + Section.</p>
                    </div>
                </div>

                <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <a href="{{ route('admin.students.index') }}" class="w-full sm:w-auto text-center px-4 py-2.5 text-sm font-medium text-slate-600 hover:text-slate-900">Cancel</a>
                    <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                        {{ __('Save Student') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
