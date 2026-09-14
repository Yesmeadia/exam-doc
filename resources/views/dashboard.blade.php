<x-app-layout>
    <x-slot name="title">{{ __('Dashboard') }}</x-slot>

    <div class="w-full space-y-6">
        <div class="bg-white overflow-hidden shadow-sm border border-slate-200/80 rounded-2xl p-6 text-slate-900">
            {{ __("You're logged in!") }}
        </div>
    </div>
</x-app-layout>
