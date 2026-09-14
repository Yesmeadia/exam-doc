<x-app-layout>
    <x-slot name="title">{{ __('School & System Settings') }}</x-slot>

    <div x-data="{
        school_name: @js(old('school_name', $settings['school_name'] ?? '')),
        school_short_name: @js(old('school_short_name', $settings['school_short_name'] ?? '')),
        portal_title: @js(old('portal_title', $settings['portal_title'] ?? '')),
        app_name: @js(old('app_name', $settings['app_name'] ?? '')),
        footer_text: @js(old('footer_text', $settings['footer_text'] ?? ''))
    }" class="w-full space-y-6">
        <!-- Top Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200">
            <div>
                <p class="text-sm text-slate-500">Configure institutional identity, portal branding, and system-wide
                    application settings.</p>
            </div>
            <a href="{{ route('admin.dashboard') }}"
                class="inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-300 rounded-lg font-semibold text-xs text-slate-700 uppercase tracking-wider shadow-sm hover:bg-slate-50 transition w-fit">
                &larr; Back to Dashboard
            </a>
        </div>

        <x-alert />

        <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Settings Form Columns -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Institution Identity Card -->
                    <div class="bg-white rounded-2xl p-6 sm:p-7 border border-slate-200/80 shadow-xs">
                        <div class="flex items-center gap-3 pb-4 mb-5 border-b border-slate-100">
                            <div
                                class="w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-700 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-extrabold text-slate-900 tracking-tight">Institution & School
                                    Identity</h3>
                                <p class="text-xs text-slate-500">The primary school name displayed on official report
                                    cards, award rolls, and system headers.</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <x-input-label for="school_name" :value="__('Full School / Institution Name')"
                                    class="font-bold text-xs uppercase tracking-wider text-slate-700" />
                                <x-text-input id="school_name"
                                    class="block mt-1.5 w-full text-sm rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 font-semibold text-slate-900 shadow-sm"
                                    type="text" name="school_name" x-model="school_name" required
                                    placeholder="e.g. RUHSS POONCH" />
                                <x-input-error :messages="$errors->get('school_name')" class="mt-1.5" />
                                <p class="text-[11px] text-slate-400 mt-1">Appears on official PDF Award Rolls, report
                                    headers, and login screen.</p>
                            </div>

                            <div>
                                <x-input-label for="school_short_name" :value="__('School Short Name / Brand Acronym')"
                                    class="font-bold text-xs uppercase tracking-wider text-slate-700" />
                                <x-text-input id="school_short_name"
                                    class="block mt-1.5 w-full text-sm rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 font-semibold text-slate-900 shadow-sm"
                                    type="text" name="school_short_name" x-model="school_short_name" required
                                    placeholder="e.g. RUIHSS ERMS" />
                                <x-input-error :messages="$errors->get('school_short_name')" class="mt-1.5" />
                                <p class="text-[11px] text-slate-400 mt-1">Displayed in the sidebar navigation header
                                    brand banner and logo badge.</p>
                            </div>
                        </div>
                    </div>

                    <!-- System & Portal Branding Card -->
                    <div class="bg-white rounded-2xl p-6 sm:p-7 border border-slate-200/80 shadow-xs">
                        <div class="flex items-center gap-3 pb-4 mb-5 border-b border-slate-100">
                            <div
                                class="w-10 h-10 rounded-xl bg-violet-50 border border-violet-100 text-violet-700 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-extrabold text-slate-900 tracking-tight">Portal & System
                                    Titles</h3>
                                <p class="text-xs text-slate-500">Customize browser tab titles, header brand chip, and
                                    footer notice.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="portal_title" :value="__('Header Portal Title')"
                                    class="font-bold text-xs uppercase tracking-wider text-slate-700" />
                                <x-text-input id="portal_title"
                                    class="block mt-1.5 w-full text-sm rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 font-semibold text-slate-900 shadow-sm"
                                    type="text" name="portal_title" x-model="portal_title" required
                                    placeholder="e.g. Examination & Result Portal" />
                                <x-input-error :messages="$errors->get('portal_title')" class="mt-1.5" />
                                <p class="text-[11px] text-slate-400 mt-1">Shown in the top dashboard navbar badge chip.
                                </p>
                            </div>

                            <div>
                                <x-input-label for="app_name" :value="__('Application Name')"
                                    class="font-bold text-xs uppercase tracking-wider text-slate-700" />
                                <x-text-input id="app_name"
                                    class="block mt-1.5 w-full text-sm rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 font-semibold text-slate-900 shadow-sm"
                                    type="text" name="app_name" x-model="app_name" required
                                    placeholder="e.g. ERMS Results" />
                                <x-input-error :messages="$errors->get('app_name')" class="mt-1.5" />
                                <p class="text-[11px] text-slate-400 mt-1">Directly updates APP_NAME in .env and browser
                                    titles.</p>
                            </div>
                        </div>

                        <div class="mt-4">
                            <x-input-label for="footer_text" :value="__('Footer Copyright / Subtitle')"
                                class="font-bold text-xs uppercase tracking-wider text-slate-700" />
                            <x-text-input id="footer_text"
                                class="block mt-1.5 w-full text-sm rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 text-slate-900 shadow-sm"
                                type="text" name="footer_text" x-model="footer_text"
                                placeholder="e.g. Examination Result Management System (ERMS)" />
                            <x-input-error :messages="$errors->get('footer_text')" class="mt-1.5" />
                            <p class="text-[11px] text-slate-400 mt-1">Displayed at the bottom of every page.</p>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <x-primary-button
                            class="px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs uppercase tracking-wider shadow-md shadow-indigo-600/20">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            {{ __('Save Settings') }}
                        </x-primary-button>
                    </div>
                </div>

                <!-- Live Preview Sidebar Card -->
                <div class="space-y-6">
                    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs">
                        <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500">Live Preview &
                                Branding</h4>
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                Realtime Sync
                            </span>
                        </div>

                        <div class="space-y-4 text-xs">
                            <!-- Sidebar Brand Preview -->
                            <div>
                                <span class="text-[11px] font-semibold text-slate-400 block mb-1.5">Sidebar Brand
                                    Header:</span>
                                <div
                                    class="bg-slate-900 text-white p-3 rounded-xl flex items-center gap-3 border border-slate-800 shadow-xs">
                                    <div
                                        class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-500 flex items-center justify-center font-black text-sm text-white shadow-md shadow-indigo-900/50 shrink-0">
                                        <span
                                            x-text="(school_short_name ? school_short_name.trim().charAt(0) : 'R').toUpperCase()"></span>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-extrabold text-sm text-white leading-none truncate"
                                            x-text="school_short_name || 'RUIHSS ERMS'"></p>
                                        <p class="text-[10px] font-medium text-slate-400 mt-1 uppercase tracking-wider">
                                            Result Management</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Header Chip Preview -->
                            <div>
                                <span class="text-[11px] font-semibold text-slate-400 block mb-1.5">Top Navbar Header
                                    Chip:</span>
                                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80 flex items-center">
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold uppercase tracking-wider bg-indigo-50 text-indigo-700 border border-indigo-100/80 truncate"
                                        x-text="portal_title || 'Examination & Result Portal'">
                                    </span>
                                </div>
                            </div>

                            <!-- Document / Report Header Preview -->
                            <div>
                                <span class="text-[11px] font-semibold text-slate-400 block mb-1.5">Official Document /
                                    Award Roll Header:</span>
                                <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200/80 text-center">
                                    <p class="font-bold text-slate-900 text-sm uppercase break-words leading-tight"
                                        x-text="school_name || 'RAZA UL ULOOM ISLAMIA HIGHER SECONDARY SCHOOL'"></p>
                                    <p class="text-[10px] text-indigo-600 font-semibold uppercase mt-1 tracking-wider">
                                        Official Tabulation & Award Roll</p>
                                </div>
                            </div>

                            <!-- Browser Tab Preview -->
                            <div>
                                <span class="text-[11px] font-semibold text-slate-400 block mb-1.5">Browser Tab
                                    Title:</span>
                                <div
                                    class="p-2.5 bg-slate-100 rounded-xl border border-slate-200/80 flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-600 shrink-0"></span>
                                    <span class="text-xs font-semibold text-slate-700 truncate"
                                        x-text="(app_name || 'ERMS Results') + ' - ' + (portal_title || 'Examination & Result Portal')"></span>
                                </div>
                            </div>

                            <!-- Footer Preview -->
                            <div>
                                <span class="text-[11px] font-semibold text-slate-400 block mb-1.5">Footer Copyright
                                    Notice:</span>
                                <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200/80 text-center">
                                    <p class="text-[11px] text-slate-500 truncate"
                                        x-text="'© {{ date('Y') }} ' + (footer_text || 'Examination Result Management System (ERMS)') + '. All Rights Reserved'">
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dynamic Synchronization Info Card -->
                    <div
                        class="bg-indigo-50/60 rounded-2xl p-6 border border-indigo-100 text-xs text-indigo-900 space-y-3">
                        <h4 class="font-bold text-xs uppercase tracking-wider text-indigo-800">Dynamic Synchronization
                        </h4>
                        <p class="text-slate-600 leading-relaxed">
                            Updating institutional and portal settings immediately synchronizes across:
                        </p>
                        <ul class="list-disc list-inside space-y-1.5 text-slate-600 font-medium">
                            <li>A4 Tabulation PDF Award Rolls</li>
                            <li>Portal Login Screen Identity</li>
                            <li>Sidebar & Top Navigation Bar Header Chip</li>
                            <li>Application Name in <code
                                    class="bg-white px-1 py-0.5 rounded border border-indigo-200 text-indigo-700 font-mono text-[11px]">.env</code>
                                & Browser Window Titles</li>
                            <li>System Footer Copyright Notice</li>
                        </ul>
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>