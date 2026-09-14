<x-app-layout>
    <x-slot name="title">{{ __('Edit Examination') }}: {{ $exam->exam_name }}</x-slot>

    <div class="w-full space-y-6">
        <!-- Top Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200">
            <div>
                <p class="text-sm text-slate-500">Update exam parameters, schedules, or change lifecycle status.</p>
            </div>
            <a href="{{ route('admin.exams.index') }}" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-300 rounded-lg font-semibold text-xs text-slate-700 uppercase tracking-wider shadow-sm hover:bg-slate-50 transition w-fit">
                &larr; Back to Examinations
            </a>
        </div>

        <x-alert />

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden w-full">
            <form method="POST" action="{{ route('admin.exams.update', $exam) }}" class="p-4 sm:p-6 lg:p-8 space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <x-input-label for="exam_name" :value="__('Exam Name')" class="font-semibold text-slate-700" />
                    <x-text-input id="exam_name" class="block mt-1.5 w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" type="text" name="exam_name" :value="old('exam_name', $exam->exam_name)" required autofocus />
                    <x-input-error :messages="$errors->get('exam_name')" class="mt-2" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="academic_year_id" :value="__('Academic Year')" class="font-semibold text-slate-700" />
                        <select id="academic_year_id" name="academic_year_id" class="block mt-1.5 w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" required>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ old('academic_year_id', $exam->academic_year_id) == $year->id ? 'selected' : '' }}>
                                    {{ $year->name }} {{ $year->is_active ? '(Active Session)' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('academic_year_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="status" :value="__('Lifecycle Status')" class="font-semibold text-slate-700" />
                        <select id="status" name="status" class="block mt-1.5 w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" required>
                            @foreach($statuses as $status)
                                <option value="{{ $status }}" {{ old('status', $exam->status) === $status ? 'selected' : '' }}>
                                    {{ $status }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('status')" class="mt-2" />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="start_date" :value="__('Start Date')" class="font-semibold text-slate-700" />
                        <x-text-input id="start_date" class="block mt-1.5 w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" type="date" name="start_date" :value="old('start_date', $exam->start_date?->format('Y-m-d'))" />
                        <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="end_date" :value="__('End Date')" class="font-semibold text-slate-700" />
                        <x-text-input id="end_date" class="block mt-1.5 w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" type="date" name="end_date" :value="old('end_date', $exam->end_date?->format('Y-m-d'))" />
                        <x-input-error :messages="$errors->get('end_date')" class="mt-2" />
                    </div>
                </div>

                <div>
                    <x-input-label for="description" :value="__('Description / Remarks (Optional)')" class="font-semibold text-slate-700" />
                    <textarea id="description" name="description" rows="3" class="block mt-1.5 w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">{{ old('description', $exam->description) }}</textarea>
                    <x-input-error :messages="$errors->get('description')" class="mt-2" />
                </div>

                <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <a href="{{ route('admin.exams.index') }}" class="w-full sm:w-auto text-center px-4 py-2.5 text-sm font-medium text-slate-600 hover:text-slate-900">Cancel</a>
                    <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                        {{ __('Save Changes') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
