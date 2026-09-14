<x-app-layout>
    <x-slot name="title">{{ __('Profile Settings') }}</x-slot>

    <div class="w-full space-y-6">
        <div class="p-6 sm:p-8 bg-white shadow-sm border border-slate-200/80 rounded-2xl w-full">
            <div class="w-full max-w-2xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="p-6 sm:p-8 bg-white shadow-sm border border-slate-200/80 rounded-2xl w-full">
            <div class="w-full max-w-2xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="p-6 sm:p-8 bg-white shadow-sm border border-slate-200/80 rounded-2xl w-full">
            <div class="w-full max-w-2xl">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-app-layout>
