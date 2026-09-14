<x-app-layout>
    <x-slot name="title">{{ __('Edit Subject') }}: {{ $subject->name }}</x-slot>

    <div class="w-full space-y-6">
        <!-- Top Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200">
            <div>
                <p class="text-sm text-slate-500">Update subject name, maximum & pass marks, and class mapping.</p>
            </div>
            <a href="{{ route('admin.subjects.index') }}" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-300 rounded-lg font-semibold text-xs text-slate-700 uppercase tracking-wider shadow-sm hover:bg-slate-50 transition w-fit">
                &larr; Back to Subjects
            </a>
        </div>

        <x-alert />

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden w-full">
            <form method="POST" action="{{ route('admin.subjects.update', $subject) }}" class="p-4 sm:p-6 lg:p-8 space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <x-input-label for="name" :value="__('Subject Name')" class="font-semibold text-slate-700" />
                    <x-text-input id="name" class="block mt-1.5 w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" type="text" name="name" :value="old('name', $subject->name)" required autofocus />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="maximum_marks" :value="__('Maximum Marks')" class="font-semibold text-slate-700" />
                        <x-text-input id="maximum_marks" class="block mt-1.5 w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" type="number" step="0.5" name="maximum_marks" :value="old('maximum_marks', $subject->maximum_marks)" required />
                        <x-input-error :messages="$errors->get('maximum_marks')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="pass_marks" :value="__('Pass Marks')" class="font-semibold text-slate-700" />
                        <x-text-input id="pass_marks" class="block mt-1.5 w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" type="number" step="0.5" name="pass_marks" :value="old('pass_marks', $subject->pass_marks)" required />
                        <x-input-error :messages="$errors->get('pass_marks')" class="mt-2" />
                    </div>
                </div>

                <div>
                    <x-input-label :value="__('Map to Classes')" class="font-semibold text-slate-700 mb-2" />
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 bg-slate-50 p-4 rounded-xl border border-slate-200">
                        @foreach($classes as $class)
                            <label class="inline-flex items-center text-sm text-slate-700 cursor-pointer">
                                <input type="checkbox" name="class_ids[]" value="{{ $class->id }}" {{ in_array($class->id, old('class_ids', $selectedClassIds)) ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="ms-2 font-medium">{{ $class->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <a href="{{ route('admin.subjects.index') }}" class="w-full sm:w-auto text-center px-4 py-2.5 text-sm font-medium text-slate-600 hover:text-slate-900">Cancel</a>
                    <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                        {{ __('Save Changes') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
