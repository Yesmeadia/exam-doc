<x-app-layout>
    <x-slot name="title">{{ __('Bulk Student Import') }}</x-slot>

    <div class="w-full space-y-6" x-data="{
        classes: {{ Js::from($classes) }},
        selectedClassId: '',
        selectedSectionId: '',
        sections: [],
        updateSections() {
            const found = this.classes.find(c => String(c.id) === String(this.selectedClassId));
            this.sections = (found && found.sections) ? found.sections : [];
            this.selectedSectionId = this.sections.length > 0 ? this.sections[0].id : '';
        }
    }">
        {{-- Top Action Bar --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200">
            <div>
                <p class="text-sm text-slate-500">Upload a spreadsheet with student records for a specific class &amp; section.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.students.import.template') }}" class="inline-flex items-center px-4 py-2 bg-white border border-slate-300 rounded-lg font-semibold text-xs text-slate-700 uppercase tracking-wider hover:bg-slate-50 shadow-sm transition">
                    <svg class="w-4 h-4 me-1.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Download Excel Template
                </a>
                <a href="{{ route('admin.students.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-slate-300 rounded-lg font-semibold text-xs text-slate-700 uppercase tracking-wider hover:bg-slate-50 shadow-sm transition">
                    &larr; Back to Students
                </a>
            </div>
        </div>

        <x-alert />

        {{-- Upload Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-6 sm:p-8 w-full">
            <h3 class="text-base font-bold text-slate-900 mb-1">Upload Student List</h3>
            <p class="text-xs text-slate-500 mb-6">Select the target Academic Year, Class, and Section, then upload your Excel file.</p>

            <form method="POST" action="{{ route('admin.students.import.preview') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf

                {{-- Row 1: Academic Year | Class | Section --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                    <div>
                        <x-input-label for="academic_year_id" :value="__('Academic Year')" class="font-semibold text-slate-700" />
                        <select id="academic_year_id" name="academic_year_id"
                            class="block mt-1.5 w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" required>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ $year->is_active ? 'selected' : '' }}>
                                    {{ $year->name }} {{ $year->is_active ? '★' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('academic_year_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="class_id" :value="__('Class')" class="font-semibold text-slate-700" />
                        <select id="class_id" name="class_id"
                            x-model="selectedClassId"
                            @change="updateSections()"
                            class="block mt-1.5 w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" required>
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
                            x-model="selectedSectionId"
                            class="block mt-1.5 w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" required>
                            <option value="">-- Select Section --</option>
                            <template x-for="s in sections" :key="s.id">
                                <option :value="s.id" x-text="s.name"></option>
                            </template>
                        </select>
                        <x-input-error :messages="$errors->get('section_id')" class="mt-2" />
                    </div>
                </div>

                {{-- File Upload --}}
                <div>
                    <x-input-label for="file" :value="__('Select Excel or CSV File (.xlsx, .xls, .csv)')" class="font-semibold text-slate-700" />
                    <div class="mt-2 flex justify-center px-6 pt-6 pb-6 border-2 border-slate-300 border-dashed rounded-2xl hover:border-indigo-500 transition bg-slate-50/60">
                        <div class="space-y-2 text-center">
                            <svg class="mx-auto h-12 w-12 text-slate-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-slate-600 justify-center">
                                <label for="file" class="relative cursor-pointer bg-white rounded-lg font-bold text-indigo-600 hover:text-indigo-500 px-3 py-1 border border-slate-300 shadow-xs">
                                    <span>Browse File</span>
                                    <input id="file" name="file" type="file" accept=".xlsx,.xls,.csv" class="sr-only" required>
                                </label>
                                <p class="ps-2 py-1">or drag and drop</p>
                            </div>
                            <p class="text-xs text-slate-400">XLSX, XLS, or CSV up to 5MB</p>
                        </div>
                    </div>
                    <x-input-error :messages="$errors->get('file')" class="mt-2" />
                </div>

                <div class="flex flex-col-reverse sm:flex-row items-center justify-between gap-3 pt-4 border-t border-slate-100">
                    <a href="{{ route('admin.students.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">
                        &larr; Back to Students
                    </a>
                    <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                        {{ __('Upload & Preview Validation') }} &rarr;
                    </button>
                </div>
            </form>
        </div>

        {{-- Expected File Layout --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-6 sm:p-8 w-full">
            <h3 class="text-base font-bold text-slate-900 mb-2">Expected Column Format</h3>
            <p class="text-xs text-slate-500 mb-4">
                Your file must have exactly <strong>3 columns</strong> in this order — Class and Section are set above, not in the file.
            </p>

            <div class="overflow-x-auto border border-slate-200 rounded-xl w-full">
                <table class="w-full divide-y divide-slate-200 text-xs">
                    <thead class="bg-slate-100 font-bold text-slate-700">
                        <tr>
                            <th class="px-3.5 py-2.5 text-left">Roll No</th>
                            <th class="px-3.5 py-2.5 text-left">Student ID</th>
                            <th class="px-3.5 py-2.5 text-left">Student Name</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        <tr>
                            <td class="px-3.5 py-2.5 font-black text-slate-900">1</td>
                            <td class="px-3.5 py-2.5 font-mono font-bold text-indigo-700">ST001</td>
                            <td class="px-3.5 py-2.5 font-bold text-slate-900">Abdul Rahman</td>
                        </tr>
                        <tr>
                            <td class="px-3.5 py-2.5 font-black text-slate-900">2</td>
                            <td class="px-3.5 py-2.5 font-mono font-bold text-indigo-700">ST002</td>
                            <td class="px-3.5 py-2.5 font-bold text-slate-900">Ameen Khan</td>
                        </tr>
                        <tr>
                            <td class="px-3.5 py-2.5 font-black text-slate-900">3</td>
                            <td class="px-3.5 py-2.5 font-mono font-bold text-indigo-700">ST003</td>
                            <td class="px-3.5 py-2.5 font-bold text-slate-900">Anas Ali</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-4 text-xs text-slate-500 space-y-1">
                <p>&bull; <strong>Roll No</strong> must be a positive number, unique within the selected class &amp; section.</p>
                <p>&bull; <strong>Student ID</strong> must be unique across all students in the system.</p>
                <p>&bull; <strong>Class &amp; Section</strong> are selected above — do not include them in the file.</p>
            </div>
        </div>
    </div>
</x-app-layout>
