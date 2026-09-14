<x-app-layout>
    <x-slot name="title">{{ __('Add New Teacher') }}</x-slot>

    <div class="w-full space-y-6">
        <!-- Top Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200">
            <div>
                <p class="text-sm text-slate-500">Register a faculty member. A secure password setup link will be automatically emailed to them.</p>
            </div>
            <a href="{{ route('admin.teachers.index') }}" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-300 rounded-lg font-semibold text-xs text-slate-700 uppercase tracking-wider shadow-sm hover:bg-slate-50 transition w-fit">
                &larr; Back to Teachers
            </a>
        </div>

        <x-alert />

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
            <form method="POST" action="{{ route('admin.teachers.store') }}" class="p-4 sm:p-6 lg:p-8 space-y-8">
                @csrf

                <!-- Section 1: Teacher Profile Details -->
                <div>
                    <div class="flex items-center gap-2 pb-3 border-b border-slate-100 mb-5">
                        <span class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-700 flex items-center justify-center font-bold text-xs">1</span>
                        <h3 class="text-base font-bold text-slate-900">Personal & Faculty Details</h3>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <x-input-label for="name" :value="__('Teacher Full Name')" class="font-semibold text-slate-700" />
                            <x-text-input id="name" class="block mt-1.5 w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" type="text" name="name" :value="old('name')" required autofocus placeholder="e.g. Mr. Abdul Rahman" />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="phone" :value="__('Phone Number (Optional)')" class="font-semibold text-slate-700" />
                            <x-text-input id="phone" class="block mt-1.5 w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" type="text" name="phone" :value="old('phone')" placeholder="e.g. +91 9876543210" />
                            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                        </div>
                    </div>
                </div>

                <!-- Section 2: Portal Login Account & Automated Password Setup -->
                <div class="bg-gradient-to-br from-indigo-50/60 to-blue-50/40 rounded-xl p-5 sm:p-6 border border-indigo-100/80">
                    <div class="flex items-center gap-2 pb-3 border-b border-indigo-100 mb-4">
                        <span class="w-7 h-7 rounded-lg bg-indigo-600 text-white flex items-center justify-center font-bold text-xs shadow-sm">2</span>
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Portal Login & Security</h3>
                            <p class="text-xs text-indigo-700 font-medium mt-0.5">Faculty login account configuration</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <x-input-label for="email" :value="__('Teacher Email Address (Login Username)')" class="font-bold text-slate-800" />
                            <div class="relative mt-1.5">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206" />
                                    </svg>
                                </div>
                                <x-text-input id="email" class="block w-full pl-9 rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm font-medium" type="email" name="email" :value="old('email')" required placeholder="teacher.login@school.edu" />
                            </div>
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            <p class="text-[11px] text-slate-500 mt-1">This email acts as the unique login username for faculty mark entry access.</p>
                        </div>

                        <!-- Automated Password Delivery Notice -->
                        <div class="flex items-start gap-3 p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-900">
                            <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 mt-0.5">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-emerald-900">Automated Password Setup by Email</h4>
                                <p class="text-xs text-emerald-800 mt-0.5 leading-relaxed">
                                    As Super Admin, you do <strong>not</strong> need to set a password. A secure password setup link will be automatically emailed to this address upon submission. The teacher will be prompted to set their own confidential password.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <a href="{{ route('admin.teachers.index') }}" class="w-full sm:w-auto text-center text-sm font-medium text-slate-600 hover:text-slate-900 px-4 py-2.5">
                        Cancel
                    </a>
                    <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                        {{ __('Create Teacher & Email Setup Link') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
