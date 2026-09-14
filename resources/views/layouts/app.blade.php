<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ setting('app_name', config('app.name', 'ERMS Results')) }} -
        {{ setting('portal_title', 'Examination & Result Portal') }}
    </title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- PWA & Mobile Web App Manifest -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#4f46e5">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="ERMS">
    <link rel="apple-touch-icon" href="/icons/logo.png">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Executive Design System & Enhancements -->
    <style>
        .dual-line-clamp {
            display: -webkit-box !important;
            -webkit-box-orient: vertical !important;
            -webkit-line-clamp: 2 !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            word-break: break-word !important;
            white-space: normal !important;
            line-height: 1.25 !important;
        }

        .stat-card {
            background: #ffffff;
            border-radius: 1rem;
            border: 1px solid rgba(226, 232, 240, 0.9);
            padding: 1.25rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04);
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            text-decoration: none;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(15, 23, 42, 0.08);
            border-color: rgba(99, 102, 241, 0.35);
        }

        .stat-icon {
            width: 2.75rem;
            height: 2.75rem;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.2s ease;
            flex-shrink: 0;
        }

        .stat-icon svg {
            width: 1.35rem;
            height: 1.35rem;
        }

        .stat-card:hover .stat-icon {
            transform: scale(1.08);
        }

        .stat-icon-blue {
            background: linear-gradient(135deg, #2563eb 0%, #4f46e5 100%);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
        }

        .stat-icon-emerald {
            background: linear-gradient(135deg, #10b981 0%, #0d9488 100%);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);
        }

        .stat-icon-violet {
            background: linear-gradient(135deg, #7c3aed 0%, #9333ea 100%);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(124, 58, 237, 0.25);
        }

        .stat-icon-amber {
            background: linear-gradient(135deg, #f59e0b 0%, #ea580c 100%);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.25);
        }

        .stat-icon-rose {
            background: linear-gradient(135deg, #ec4899 0%, #e11d48 100%);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(225, 29, 72, 0.25);
        }

        .command-center {
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%);
            border-radius: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.25);
            color: #ffffff;
        }

        .action-card {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 0.75rem;
            padding: 1rem 0.5rem;
            text-align: center;
            transition: all 0.2s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: #ffffff;
        }

        .action-card:hover {
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(129, 140, 248, 0.4);
            transform: translateY(-2px);
            color: #ffffff;
        }

        .action-card-highlight {
            background: linear-gradient(135deg, #059669 0%, #0d9488 100%);
            border-color: rgba(52, 211, 153, 0.4);
            box-shadow: 0 4px 14px rgba(5, 150, 105, 0.3);
        }

        .action-card-highlight:hover {
            background: linear-gradient(135deg, #10b981 0%, #14b8a6 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(5, 150, 105, 0.4);
        }

        .action-icon {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 0.65rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 0.5rem;
            transition: transform 0.2s ease;
        }

        .action-icon svg {
            width: 1.25rem;
            height: 1.25rem;
        }

        .action-card:hover .action-icon {
            transform: scale(1.1);
        }
    </style>
</head>

<body class="h-full font-sans antialiased text-slate-800 bg-slate-50">
    <div x-data="{ sidebarOpen: false }" class="flex h-screen overflow-hidden bg-slate-50">
        <!-- Mobile Sidebar Backdrop -->
        <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
            x-transition:enter="transition-opacity ease-linear duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-300"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-40 bg-slate-950/60 backdrop-blur-sm lg:hidden">
        </div>

        <!-- Sidebar Container -->
        <div :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            class="fixed inset-y-0 left-0 z-50 transition-transform duration-300 ease-in-out lg:static lg:translate-x-0 flex flex-col shrink-0">
            @include('layouts.sidebar')
        </div>

        <!-- Content Area -->
        <div class="flex-1 flex flex-col min-w-0 h-screen overflow-y-auto">
            <!-- Top Navbar -->
            <header
                class="sticky top-0 z-30 flex items-center justify-between min-h-16 py-1.5 sm:py-0 sm:h-16 px-3 sm:px-6 lg:px-8 bg-white/90 backdrop-blur-md border-b border-slate-200/80 shadow-xs shrink-0">
                <div class="flex items-center gap-2 sm:gap-3 min-w-0 flex-1">
                    <!-- Mobile toggle button -->
                    <button @click="sidebarOpen = !sidebarOpen" type="button"
                        class="p-1.5 -ml-1 rounded-xl text-slate-500 hover:bg-slate-100 hover:text-slate-800 transition lg:hidden focus:outline-none focus:ring-2 focus:ring-indigo-500/20 shrink-0">
                        <span class="sr-only">Open sidebar</span>
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <!-- Active Page Title in Header -->
                    <div class="flex items-center gap-2 min-w-0 flex-1">
                        @php
                            $routeName = request()->route()?->getName() ?? '';
                            $pageTitle = $title ?? match (true) {
                                request()->routeIs('admin.dashboard') => 'Super Admin Dashboard',
                                request()->routeIs('admin.academic-years.*') => 'Academic Years',
                                request()->routeIs('admin.exams.*') => 'Examinations',
                                request()->routeIs('admin.classes.*'), request()->routeIs('admin.sections.*') => 'Classes & Sections',
                                request()->routeIs('admin.subjects.*') => 'Curriculum Subjects',
                                request()->routeIs('admin.students.import.*') => 'Bulk Student Import',
                                request()->routeIs('admin.students.subjects*') => 'Elective Subject Allocation (Class 11/12)',
                                request()->routeIs('admin.students.marks-sheet') => 'Student Timetable Marks Sheet',
                                request()->routeIs('admin.marks-sheets.*') => 'Student Marks Sheets Explorer',
                                request()->routeIs('admin.students.*') => 'Students Directory',
                                request()->routeIs('admin.teachers.*') => 'Teachers & Faculty',
                                request()->routeIs('admin.assignments.*') => 'Teacher Assignments',
                                request()->routeIs('admin.marks.*') => 'Mark Entry & Verification Matrix',
                                request()->routeIs('admin.award-rolls.*') => 'Award Rolls (PDF)',
                                request()->routeIs('admin.audit-logs.*') => 'System Audit Trail Logs',
                                request()->routeIs('admin.settings.*') => 'School & System Settings',
                                request()->routeIs('teacher.dashboard') => 'Faculty Mark Entry Dashboard',
                                request()->routeIs('teacher.marks.*') => 'Student Mark Entry',
                                request()->routeIs('profile.*') => 'My Profile & Account',
                                default => 'Examination Result Management',
                            };
                        @endphp
                        <h1
                            class="text-xs sm:text-base lg:text-lg font-black text-slate-900 tracking-tight dual-line-clamp leading-tight">
                            {{ $pageTitle }}
                        </h1>
                    </div>
                </div>

                <!-- Right Top Actions -->
                <div class="flex items-center gap-2.5 sm:gap-3">

                    <!-- User Profile Dropdown -->
                    @auth
                        <x-dropdown align="right" width="56">
                            <x-slot name="trigger">
                                <button
                                    class="inline-flex items-center gap-2.5 p-1.5 pr-2.5 rounded-xl border border-slate-200/80 bg-white hover:bg-slate-50/80 hover:border-slate-300 text-slate-700 text-xs font-semibold focus:outline-none transition shadow-xs">
                                    <span
                                        class="w-7 h-7 rounded-lg bg-gradient-to-tr from-indigo-600 to-violet-500 text-white flex items-center justify-center font-bold text-xs shadow-xs">
                                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                    </span>
                                    <div class="text-left hidden sm:block">
                                        <p class="leading-tight font-semibold text-slate-800">{{ Auth::user()->name }}</p>
                                        <p class="text-[10px] text-slate-400 font-normal leading-tight">
                                            {{ Auth::user()->hasRole('super-admin') ? 'Super Admin' : 'Faculty' }}
                                        </p>
                                    </div>
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/60">
                                    <p class="text-xs font-semibold text-slate-900">{{ Auth::user()->name }}</p>
                                    <p class="text-[11px] text-slate-500 truncate mt-0.5">{{ Auth::user()->email }}</p>
                                    <div class="mt-2">
                                        @if(Auth::user()->hasRole('super-admin'))
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                                Super Administrator
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                                Faculty Teacher
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="py-1">
                                    <x-dropdown-link :href="route('profile.edit')" class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        <span>{{ __('Profile Settings') }}</span>
                                    </x-dropdown-link>
                                </div>
                                <div class="border-t border-slate-100 py-1">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <x-dropdown-link :href="route('logout')"
                                            onclick="event.preventDefault(); this.closest('form').submit();"
                                            class="flex items-center gap-2 text-rose-600 hover:text-rose-700 hover:bg-rose-50/50">
                                            <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                            </svg>
                                            <span>{{ __('Log Out') }}</span>
                                        </x-dropdown-link>
                                    </form>
                                </div>
                            </x-slot>
                        </x-dropdown>
                    @endauth
                </div>
            </header>

            <!-- Page Main Content Slot (Full Width & Responsive) -->
            <main class="flex-1 w-full max-w-full p-3 sm:p-5 lg:p-6 overflow-x-hidden">
                {{ $slot }}
            </main>

            <!-- Footer -->
            <footer class="py-4 px-6 text-center text-xs text-slate-400 border-t border-slate-200/80 bg-white">
                &copy; {{ date('Y') }} {{ setting('footer_text', 'Examination Result Management System (ERMS)') }}. All
                Rights Reserved
            </footer>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- GLOBAL CUSTOM CONFIRM MODAL                                  -->
    <!-- Usage: add data-confirm="Your message here" to any          -->
    <!-- <form> or <button type="submit">                            -->
    <!-- ============================================================ -->
    <div id="confirm-modal-root" x-data="{
            open: false,
            title: '',
            message: '',
            confirmLabel: 'Confirm',
            confirmColor: 'rose',
            _resolve: null,
            ask(message, title, confirmLabel, confirmColor) {
                this.message      = message      || 'Are you sure?';
                this.title        = title        || 'Confirm Action';
                this.confirmLabel = confirmLabel || 'Confirm';
                this.confirmColor = confirmColor || 'rose';
                this.open = true;
                return new Promise(resolve => { this._resolve = resolve; });
            },
            confirm() { this.open = false; this._resolve && this._resolve(true);  },
            cancel()  { this.open = false; this._resolve && this._resolve(false); }
        }" @open-confirm.window="
            const { message, title, confirmLabel, confirmColor, callback } = $event.detail;
            ask(message, title, confirmLabel, confirmColor).then(ok => { if (ok && callback) callback(true); });
        " x-show="open" x-cloak style="display:none">
        {{-- Backdrop --}}
        <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="cancel()"
            class="fixed inset-0 z-[90] bg-slate-900/60 backdrop-blur-sm"></div>

        {{-- Modal Panel --}}
        <div x-show="open" x-transition:enter="transition ease-out duration-250"
            x-transition:enter-start="opacity-0 scale-95 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-4"
            class="fixed inset-0 z-[100] flex items-center justify-center p-4" @keydown.escape.window="cancel()">
            <div
                class="relative w-full max-w-sm bg-white rounded-2xl shadow-2xl border border-slate-200/60 overflow-hidden">

                {{-- Top accent bar --}}
                <div class="h-1 w-full" :class="{
                        'bg-gradient-to-r from-rose-500 to-red-500':    confirmColor === 'rose',
                        'bg-gradient-to-r from-amber-400 to-orange-500': confirmColor === 'amber',
                        'bg-gradient-to-r from-indigo-500 to-violet-600': confirmColor === 'indigo',
                        'bg-gradient-to-r from-emerald-400 to-teal-500': confirmColor === 'emerald',
                    }"></div>

                <div class="p-6">
                    {{-- Icon --}}
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-11 h-11 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-sm"
                            :class="{
                                'bg-rose-100':    confirmColor === 'rose',
                                'bg-amber-100':   confirmColor === 'amber',
                                'bg-indigo-100':  confirmColor === 'indigo',
                                'bg-emerald-100': confirmColor === 'emerald',
                            }">
                            {{-- Rose/danger: warning triangle --}}
                            <template x-if="confirmColor === 'rose'">
                                <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </template>
                            {{-- Amber: question --}}
                            <template x-if="confirmColor === 'amber'">
                                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </template>
                            {{-- Indigo: info --}}
                            <template x-if="confirmColor === 'indigo'">
                                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </template>
                            {{-- Emerald: check --}}
                            <template x-if="confirmColor === 'emerald'">
                                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                            </template>
                        </div>

                        <div class="flex-1 min-w-0">
                            <h3 class="font-bold text-slate-900 text-base leading-snug" x-text="title"></h3>
                        </div>
                    </div>

                    <p class="text-sm text-slate-600 leading-relaxed mb-6" x-text="message"></p>

                    {{-- Actions --}}
                    <div class="flex items-center justify-end gap-3">
                        <button @click="cancel()" type="button"
                            class="px-4 py-2 text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition">Cancel</button>

                        <button @click="confirm()" type="button"
                            class="px-5 py-2 text-sm font-bold text-white rounded-xl shadow-sm transition" :class="{
                                'bg-rose-600 hover:bg-rose-700':      confirmColor === 'rose',
                                'bg-amber-500 hover:bg-amber-600':    confirmColor === 'amber',
                                'bg-indigo-600 hover:bg-indigo-700':  confirmColor === 'indigo',
                                'bg-emerald-600 hover:bg-emerald-700':confirmColor === 'emerald',
                            }" x-text="confirmLabel"></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- PWA Service Worker Registration --}}
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => console.log('ERMS PWA Registered successfully', reg))
                    .catch(err => console.log('ERMS PWA Registration error', err));
            });
        }

        // ─── Global custom confirm() replacement ───────────────────────────
        // Intercepts forms and buttons with data-confirm attribute.
        // Also exposes window.customConfirm(msg, title, label, color) -> Promise<bool>

        window.customConfirm = function (message, title, confirmLabel, confirmColor) {
            return new Promise(resolve => {
                window.dispatchEvent(new CustomEvent('open-confirm', {
                    detail: {
                        message,
                        title: title || 'Confirm Action',
                        confirmLabel: confirmLabel || 'Confirm',
                        confirmColor: confirmColor || 'rose',
                        callback: resolve
                    }
                }));
            });
        };

        document.addEventListener('DOMContentLoaded', function () {
            // Intercept form submits with data-confirm
            document.body.addEventListener('submit', function (e) {
                const form = e.target;
                const msg = form.dataset.confirm;
                if (!msg) return;
                e.preventDefault();

                const title = form.dataset.confirmTitle || 'Confirm Action';
                const label = form.dataset.confirmLabel || 'Yes, Proceed';
                const color = form.dataset.confirmColor || 'rose';

                window.customConfirm(msg, title, label, color).then(ok => {
                    if (ok) {
                        form.removeAttribute('data-confirm');
                        form.submit();
                    }
                });
            }, true);

            // Intercept button clicks with data-confirm (for non-form buttons)
            document.body.addEventListener('click', function (e) {
                const btn = e.target.closest('[data-confirm]');
                if (!btn || btn.tagName === 'FORM') return;
                // Only intercept buttons not inside a form (standalone buttons)
                const form = btn.closest('form');
                if (form && form.dataset.confirm) return; // form handler takes over
                e.preventDefault();

                const msg = btn.dataset.confirm;
                const title = btn.dataset.confirmTitle || 'Confirm Action';
                const label = btn.dataset.confirmLabel || 'Yes, Proceed';
                const color = btn.dataset.confirmColor || 'rose';

                window.customConfirm(msg, title, label, color).then(ok => {
                    if (ok) {
                        btn.removeAttribute('data-confirm');
                        btn.click();
                    }
                });
            }, true);
        });
    </script>

    @auth
        <!-- ============================================================ -->
        <!-- AUTO LOGOUT INACTIVITY WARNING MODAL (5-MINUTE IDLE TIMEOUT)  -->
        <!-- ============================================================ -->
        <div id="idle-timeout-modal-root" x-data="idleTimer()" x-init="initTimer()" @keydown.window="recordActivity()"
            @click.window="recordActivity()" @scroll.window="recordActivity()" @mousemove.window="throttledRecordActivity()"
            @touchstart.window="recordActivity()" x-show="showWarning" x-cloak style="display:none">
            {{-- Backdrop --}}
            <div x-show="showWarning" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0" class="fixed inset-0 z-[110] bg-slate-950/70 backdrop-blur-sm"></div>

            {{-- Modal Panel --}}
            <div x-show="showWarning" x-transition:enter="transition ease-out duration-250"
                x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                class="fixed inset-0 z-[120] flex items-center justify-center p-4">
                <div
                    class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl border border-amber-200/80 overflow-hidden">
                    {{-- Top Warning Accent Bar --}}
                    <div class="h-1.5 w-full bg-gradient-to-r from-amber-400 via-orange-500 to-rose-500"></div>

                    <div class="p-6">
                        <div class="flex items-start gap-4 mb-4">
                            <div
                                class="w-12 h-12 rounded-2xl bg-amber-100 border border-amber-200 flex items-center justify-center flex-shrink-0 text-amber-600 shadow-sm animate-pulse">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>

                            <div class="flex-1 min-w-0">
                                <h3 class="font-black text-slate-900 text-lg leading-tight">Session Inactivity Warning</h3>
                                <p class="text-xs text-slate-500 mt-1">You have been inactive for over 4 minutes.</p>
                            </div>
                        </div>

                        <div class="bg-amber-50/80 border border-amber-200/60 rounded-xl p-4 text-center my-4">
                            <p class="text-xs font-semibold text-amber-900 uppercase tracking-wider mb-1">Automatic Logout
                                In</p>
                            <div class="text-3xl sm:text-4xl font-black text-amber-600 font-mono tracking-tight"
                                x-text="secondsRemaining + 's'"></div>
                            <p class="text-[11px] text-amber-700/80 mt-1">To protect your exam and student records, your
                                session will terminate.</p>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-2">
                            <button type="button" @click="logoutNow()"
                                class="px-4 py-2.5 text-xs font-bold text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-xl transition">
                                Log Out Now
                            </button>

                            <button type="button" @click="stayLoggedIn()"
                                class="px-5 py-2.5 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 active:scale-95 rounded-xl shadow-md shadow-indigo-600/20 transition flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                Stay Logged In
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function idleTimer() {
                const TOTAL_TIMEOUT_MS = 5 * 60 * 1000;
                const WARNING_DURATION_MS = 30 * 1000;
                const WARNING_THRESHOLD_MS = TOTAL_TIMEOUT_MS - WARNING_DURATION_MS;
                const STORAGE_KEY = 'erms_last_user_activity';
                const KEEP_ALIVE_URL = "{{ route('session.keep-alive') }}";
                const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                return {
                    showWarning: false,
                    secondsRemaining: 30,
                    timerInterval: null,
                    lastThrottleTime: 0,

                    initTimer() {
                        if (!localStorage.getItem(STORAGE_KEY)) {
                            localStorage.setItem(STORAGE_KEY, Date.now().toString());
                        }

                        this.timerInterval = setInterval(() => {
                            this.checkIdleStatus();
                        }, 1000);

                        // Cross-tab sync: listen for activity in other tabs
                        window.addEventListener('storage', (e) => {
                            if (e.key === STORAGE_KEY) {
                                const elapsed = Date.now() - parseInt(e.newValue || Date.now(), 10);
                                if (elapsed < WARNING_THRESHOLD_MS && this.showWarning) {
                                    this.showWarning = false;
                                }
                            }
                        });

                        // Instant check on tab focus or wake-up from sleep
                        document.addEventListener('visibilitychange', () => {
                            if (!document.hidden) {
                                this.checkIdleStatus();
                            }
                        });
                        window.addEventListener('focus', () => {
                            this.checkIdleStatus();
                        });
                    },

                    checkIdleStatus() {
                        const lastActive = parseInt(localStorage.getItem(STORAGE_KEY) || Date.now(), 10);
                        const elapsed = Date.now() - lastActive;

                        if (elapsed >= TOTAL_TIMEOUT_MS) {
                            this.performLogout();
                        } else if (elapsed >= WARNING_THRESHOLD_MS) {
                            this.showWarning = true;
                            this.secondsRemaining = Math.max(1, Math.ceil((TOTAL_TIMEOUT_MS - elapsed) / 1000));
                        } else {
                            if (this.showWarning) {
                                this.showWarning = false;
                            }
                        }
                    },

                    recordActivity() {
                        localStorage.setItem(STORAGE_KEY, Date.now().toString());
                        if (this.showWarning) {
                            this.showWarning = false;
                        }
                    },

                    throttledRecordActivity() {
                        const now = Date.now();
                        if (now - this.lastThrottleTime > 2000) {
                            this.lastThrottleTime = now;
                            this.recordActivity();
                        }
                    },

                    stayLoggedIn() {
                        this.recordActivity();
                        this.showWarning = false;

                        if (CSRF_TOKEN) {
                            fetch(KEEP_ALIVE_URL, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': CSRF_TOKEN,
                                    'Accept': 'application/json'
                                }
                            }).catch(() => { });
                        }
                    },

                    logoutNow() {
                        this.performLogout();
                    },

                    performLogout() {
                        clearInterval(this.timerInterval);
                        localStorage.removeItem(STORAGE_KEY);
                        // Build a hidden form and POST to the logout route (GET no longer accepted)
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = "{{ route('logout') }}";
                        form.style.display = 'none';

                        const csrfInput = document.createElement('input');
                        csrfInput.type = 'hidden';
                        csrfInput.name = '_token';
                        csrfInput.value = CSRF_TOKEN || '';
                        form.appendChild(csrfInput);

                        const reasonInput = document.createElement('input');
                        reasonInput.type = 'hidden';
                        reasonInput.name = 'reason';
                        reasonInput.value = 'inactivity';
                        form.appendChild(reasonInput);

                        document.body.appendChild(form);
                        form.submit();
                    }
                };
            }
        </script>
    @endauth
</body>

</html>