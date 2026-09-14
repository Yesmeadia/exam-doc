<x-app-layout>
    <x-slot name="title">{{ __('Edit Faculty') }}: {{ $teacher->name }}</x-slot>

    <div class="w-full space-y-6">
        <!-- Top Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200">
            <div>
                <p class="text-sm text-slate-500">Update teacher profile, manage login credentials, or resend password reset links.</p>
            </div>
            <a href="{{ route('admin.teachers.index') }}" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-300 rounded-lg font-semibold text-xs text-slate-700 uppercase tracking-wider shadow-sm hover:bg-slate-50 transition w-fit">
                &larr; Back to Teachers
            </a>
        </div>

        <x-alert />

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Edit Form (2 Columns) -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
                    <form method="POST" action="{{ route('admin.teachers.update', $teacher) }}" class="p-4 sm:p-6 lg:p-8 space-y-6">
                        @csrf
                        @method('PUT')

                        <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
                            <span class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-700 flex items-center justify-center font-bold text-xs">1</span>
                            <h3 class="text-base font-bold text-slate-900">Personal Information</h3>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="name" :value="__('Teacher Full Name')" class="font-semibold text-slate-700" />
                                <x-text-input id="name" class="block mt-1.5 w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" type="text" name="name" :value="old('name', $teacher->name)" required autofocus />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="phone" :value="__('Phone Number (Optional)')" class="font-semibold text-slate-700" />
                                <x-text-input id="phone" class="block mt-1.5 w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" type="text" name="phone" :value="old('phone', $teacher->phone)" />
                                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center gap-2 pb-3 border-b border-slate-100 pt-3">
                            <span class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-700 flex items-center justify-center font-bold text-xs">2</span>
                            <h3 class="text-base font-bold text-slate-900">Portal Account & Password</h3>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="email" :value="__('Email Address (Login Username)')" class="font-semibold text-slate-700" />
                                <x-text-input id="email" class="block mt-1.5 w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" type="email" name="email" :value="old('email', $teacher->email)" required />
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="status" :value="__('Account Status')" class="font-semibold text-slate-700" />
                                <select id="status" name="status" class="block mt-1.5 w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    <option value="active" {{ old('status', $teacher->status) === 'active' ? 'selected' : '' }}>Active (Login Enabled)</option>
                                    <option value="banned" {{ old('status', $teacher->status) === 'banned' ? 'selected' : '' }}>Banned (Portal Access Suspended)</option>
                                    <option value="inactive" {{ old('status', $teacher->status) === 'inactive' ? 'selected' : '' }}>Inactive (Deactivated)</option>
                                </select>
                                <x-input-error :messages="$errors->get('status')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Manual Password Override -->
                        <div class="p-4 bg-slate-50 rounded-xl border border-slate-200">
                            <x-input-label for="password" :value="__('Set New Password Manually (Optional)')" class="font-semibold text-slate-800" />
                            <div class="relative mt-1.5">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                                <x-text-input id="password" class="block w-full pl-9 rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" type="password" name="password" placeholder="Leave empty to keep current password" />
                            </div>
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                            <p class="text-[11px] text-slate-500 mt-1">Leave empty to preserve existing password. Minimum 8 characters if provided.</p>
                        </div>

                        <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 pt-4 border-t border-slate-100">
                            <a href="{{ route('admin.teachers.index') }}" class="w-full sm:w-auto text-center text-sm font-medium text-slate-600 hover:text-slate-900 px-4 py-2.5">
                                Cancel
                            </a>
                            <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                {{ __('Save Changes') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Side Card: Fast Password Reset Link Dispatcher -->
            <div class="space-y-6">
                <div class="bg-gradient-to-br from-indigo-50/70 to-blue-50/50 rounded-2xl p-6 border border-indigo-100/90 shadow-sm space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center shadow-sm shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-900 text-sm">Resend Password Reset Link</h4>
                            <p class="text-xs text-slate-500">Self-service password setup</p>
                        </div>
                    </div>

                    <p class="text-xs text-slate-600 leading-relaxed">
                        Send a secure, single-use password reset link directly to <strong class="text-slate-900 font-semibold">{{ $teacher->email }}</strong>. The teacher can click the link in their email to configure their own password.
                    </p>

                    <form method="POST" action="{{ route('admin.teachers.resend-reset-link', $teacher) }}">
                        @csrf
                        <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-white hover:bg-slate-50 text-indigo-700 font-semibold text-xs rounded-xl border border-indigo-200 shadow-sm transition">
                            <svg class="w-4 h-4 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Resend Reset Link Now
                        </button>
                    </form>
                </div>

                <!-- Teacher Stats / Assignments Summary -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-3">
                    <h4 class="font-bold text-slate-900 text-xs uppercase tracking-wider text-slate-400">Assignment Overview</h4>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-600">Active Subject Assignments:</span>
                        <span class="font-bold text-indigo-700 bg-indigo-50 px-2.5 py-0.5 rounded-full text-xs">
                            {{ $teacher->teacher_assignments_count ?? $teacher->teacherAssignments()->count() }}
                        </span>
                    </div>
                    <div class="pt-2">
                        <a href="{{ route('admin.assignments.index', ['teacher_id' => $teacher->id]) }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 inline-flex items-center gap-1">
                            Manage Subject Assignments &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
