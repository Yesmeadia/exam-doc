{{--
Custom Alert Component
- Success: auto-hides after 5 seconds with animated progress bar
- Error / Validation: stays until manually dismissed
--}}

@php
    $hasSuccess = session('success');
    $hasError = session('error');
    $hasErrors = $errors->any();
@endphp

@if($hasSuccess || $hasError || $hasErrors)
    <div id="alert-stack" class="space-y-3 mb-5">

        {{-- SUCCESS ALERT --}}
        @if($hasSuccess)
            <div id="alert-success" x-data="{
                                    show: true,
                                    progress: 100,
                                    interval: null,
                                    init() {
                                        // Start countdown timer
                                        this.interval = setInterval(() => {
                                            this.progress -= (100 / 50); // 5000ms / 100ms steps = 50 steps
                                            if (this.progress <= 0) {
                                                this.dismiss();
                                            }
                                        }, 100);
                                    },
                                    dismiss() {
                                        clearInterval(this.interval);
                                        this.show = false;
                                    }
                                }" x-show="show" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
                class="relative overflow-hidden rounded-2xl shadow-lg border border-emerald-200/60 bg-gradient-to-r from-emerald-50 to-teal-50"
                role="alert">
                {{-- Glow accent bar (left) --}}
                <div class="absolute left-0 top-0 bottom-0 w-1 bg-gradient-to-b from-emerald-400 to-teal-500 rounded-l-2xl">
                </div>

                <div class="flex items-start gap-4 px-5 py-4 pl-6">
                    {{-- Icon --}}
                    <div class="flex-shrink-0 w-9 h-9 rounded-xl bg-emerald-100 flex items-center justify-center shadow-sm">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold uppercase tracking-widest text-emerald-600 mb-0.5">Success</p>
                        <p class="text-sm font-medium text-emerald-900 leading-snug">{{ $hasSuccess }}</p>
                    </div>

                    {{-- Close Button --}}
                    <button @click="dismiss()"
                        class="flex-shrink-0 -mt-0.5 w-7 h-7 rounded-lg bg-emerald-100/80 hover:bg-emerald-200 text-emerald-500 hover:text-emerald-700 flex items-center justify-center transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Auto-dismiss progress bar --}}
                <div class="h-0.5 bg-emerald-100 mx-5 mb-3 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-emerald-400 to-teal-500 rounded-full transition-all duration-100 ease-linear"
                        :style="`width: ${progress}%`"></div>
                </div>
            </div>
        @endif

        {{-- ERROR SESSION ALERT --}}
        @if($hasError)
            <div id="alert-error" x-data="{ show: true }" x-show="show" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
                class="relative overflow-hidden rounded-2xl shadow-lg border border-rose-200/60 bg-gradient-to-r from-rose-50 to-red-50"
                role="alert">
                <div class="absolute left-0 top-0 bottom-0 w-1 bg-gradient-to-b from-rose-400 to-red-500 rounded-l-2xl"></div>

                <div class="flex items-start gap-4 px-5 py-4 pl-6">
                    <div class="flex-shrink-0 w-9 h-9 rounded-xl bg-rose-100 flex items-center justify-center shadow-sm">
                        <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>

                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold uppercase tracking-widest text-rose-600 mb-0.5">Error</p>
                        <p class="text-sm font-medium text-rose-900 leading-snug">{{ $hasError }}</p>
                    </div>

                    <button @click="show = false"
                        class="flex-shrink-0 -mt-0.5 w-7 h-7 rounded-lg bg-rose-100/80 hover:bg-rose-200 text-rose-500 hover:text-rose-700 flex items-center justify-center transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        @endif

        {{-- VALIDATION ERRORS ALERT --}}
        @if($hasErrors)
            <div id="alert-validation" x-data="{ show: true, expanded: true }" x-show="show"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
                class="relative overflow-hidden rounded-2xl shadow-lg border border-amber-200/60 bg-gradient-to-r from-amber-50 to-orange-50"
                role="alert">
                <div class="absolute left-0 top-0 bottom-0 w-1 bg-gradient-to-b from-amber-400 to-orange-500 rounded-l-2xl">
                </div>

                <div class="flex items-start gap-4 px-5 py-4 pl-6">
                    <div class="flex-shrink-0 w-9 h-9 rounded-xl bg-amber-100 flex items-center justify-center shadow-sm">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <p class="text-xs font-bold uppercase tracking-widest text-amber-600">Validation Errors</p>
                            <span
                                class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-amber-200 text-amber-800 text-xs font-black">
                                {{ $errors->count() }}
                            </span>
                        </div>

                        {{-- Collapsible error list --}}
                        <div x-show="expanded" x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 -translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 -translate-y-1">
                            <ul class="space-y-1 mt-1">
                                @foreach($errors->all() as $error)
                                    <li class="flex items-start gap-1.5 text-xs text-amber-900">
                                        <span class="mt-1 flex-shrink-0 w-1 h-1 rounded-full bg-amber-500"></span>
                                        {{ $error }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        {{-- Toggle button --}}
                        <button @click="expanded = !expanded"
                            class="mt-2 text-xs font-semibold text-amber-600 hover:text-amber-800 underline-offset-2 hover:underline transition">
                            <span x-text="expanded ? 'Hide details' : 'Show details'"></span>
                        </button>
                    </div>

                    <button @click="show = false"
                        class="flex-shrink-0 -mt-0.5 w-7 h-7 rounded-lg bg-amber-100/80 hover:bg-amber-200 text-amber-500 hover:text-amber-700 flex items-center justify-center transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        @endif

    </div>
@endif