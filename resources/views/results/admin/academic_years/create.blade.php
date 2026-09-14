<x-app-layout>
    <x-slot name="title">{{ __('Add Academic Year') }}</x-slot>

    <div class="w-full space-y-6">
        <!-- Top Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200">
            <div>
                <p class="text-sm text-slate-500">Define a new academic session and set start/end operational dates.</p>
            </div>
            <a href="{{ route('admin.academic-years.index') }}" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-300 rounded-lg font-semibold text-xs text-slate-700 uppercase tracking-wider shadow-sm hover:bg-slate-50 transition w-fit">
                &larr; Back to Academic Years
            </a>
        </div>

        <x-alert />

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden w-full">
            <form method="POST" action="{{ route('admin.academic-years.store') }}" class="p-4 sm:p-6 lg:p-8 space-y-6">
                @csrf

                <div>
                    <x-input-label for="name" :value="__('Year Name (e.g. 2026-27)')" class="font-semibold text-slate-700" />
                    <x-text-input id="name" class="block mt-1.5 w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" type="text" name="name" :value="old('name')" required autofocus placeholder="2026-27" />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="start_date" :value="__('Start Date')" class="font-semibold text-slate-700" />
                        <x-text-input id="start_date" class="block mt-1.5 w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" type="date" name="start_date" :value="old('start_date')" />
                        <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="end_date" :value="__('End Date')" class="font-semibold text-slate-700" />
                        <x-text-input id="end_date" class="block mt-1.5 w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" type="date" name="end_date" :value="old('end_date')" />
                        <x-input-error :messages="$errors->get('end_date')" class="mt-2" />
                    </div>
                </div>

                <div class="flex items-center p-3 bg-slate-50 rounded-xl border border-slate-200">
                    <input id="is_active" type="checkbox" name="is_active" value="1" {{ old('is_active') ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                    <label for="is_active" class="ms-2 text-sm text-slate-700 font-semibold">Set as currently active academic year</label>
                </div>

                <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <a href="{{ route('admin.academic-years.index') }}" class="w-full sm:w-auto text-center text-sm font-medium text-slate-600 hover:text-slate-900 px-4 py-2.5">Cancel</a>
                    <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                        {{ __('Create Academic Year') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
