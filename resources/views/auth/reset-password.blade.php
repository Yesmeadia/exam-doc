<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-white">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Reset Password - {{ config('app.name', 'School Result Portal') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-full font-sans antialiased text-slate-800 bg-white" x-data="{
    showPassword: false,
    showConfirmPassword: false
}">
    <div class="min-h-screen flex flex-col lg:flex-row">
        <!-- LEFT SIDE: Executive Brand Showcase (Wider Hero 60%) -->
        <div
            class="relative hidden lg:flex lg:w-7/12 xl:w-3/5 bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950 text-white flex-col justify-between p-12 xl:p-20 overflow-hidden border-r border-slate-800/80">
            <!-- Ambient Decorative Glows -->
            <div class="absolute -top-24 -left-24 w-96 h-96 bg-indigo-500/15 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-violet-500/15 rounded-full blur-3xl pointer-events-none"></div>

            <!-- Top Header: Logo & Name -->
            <div class="relative z-10">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-white/10 p-2.5 backdrop-blur-md border border-white/15 flex items-center justify-center shadow-xl shadow-black/40">
                        <img src="{{ asset('logo.png') }}" alt="{{ config('app.name') }}" class="w-full h-full object-contain drop-shadow-sm">
                    </div>
                    <div>
                        <h2 class="font-extrabold text-xl tracking-tight text-white leading-tight">
                            {{ config('app.name', 'School Result Portal') }}
                        </h2>
                        <p class="text-xs text-indigo-300 font-medium uppercase tracking-wider mt-0.5">
                            Security Credentials Reset
                        </p>
                    </div>
                </div>
            </div>

            <!-- Middle Value Propositions -->
            <div class="relative z-10 my-auto py-10 space-y-7 max-w-xl">
                <div>
                    <h3 class="text-3xl font-extrabold tracking-tight text-white leading-tight">
                        Create Your New Password
                    </h3>
                    <p class="text-sm text-slate-300 mt-3 leading-relaxed">
                        Please establish a strong password adhering to institutional security policy to protect student grade and examination integrity.
                    </p>
                </div>

                <div class="space-y-4 pt-1">
                    <div class="flex items-start gap-3.5">
                        <div class="w-8 h-8 rounded-lg bg-indigo-500/20 border border-indigo-400/30 flex items-center justify-center text-indigo-300 shrink-0 mt-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-white">Strong Cipher Hashing</h4>
                            <p class="text-xs text-slate-400 mt-0.5">Passwords are secured using Bcrypt hash algorithms with dynamic work factor salting.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3.5">
                        <div class="w-8 h-8 rounded-lg bg-indigo-500/20 border border-indigo-400/30 flex items-center justify-center text-indigo-300 shrink-0 mt-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-white">Minimum Complexity</h4>
                            <p class="text-xs text-slate-400 mt-0.5">Use at least 8 characters including mixed uppercase, numbers, and special symbols.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Left Footer -->
            <div class="relative z-10 pt-6 border-t border-slate-800/80 text-xs text-slate-400 flex items-center justify-between">
                <div class="flex items-center gap-3 font-medium">
                    <a href="{{ route('privacy.policy') }}" class="hover:text-white transition underline underline-offset-2">Privacy Policy</a>
                    <span class="text-slate-600">•</span>
                    <a href="{{ route('terms.conditions') }}" class="hover:text-white transition underline underline-offset-2">Terms & Conditions</a>
                </div>
                <span>&copy; {{ date('Y') }}</span>
            </div>
        </div>

        <!-- RIGHT SIDE: Seamless Unboxed Reset Form (Reduced Width 40%) -->
        <div class="flex-1 lg:w-5/12 xl:w-2/5 flex flex-col justify-center items-center p-6 sm:p-10 lg:p-10 xl:p-16 bg-white min-h-screen">
            <div class="w-full max-w-sm sm:max-w-[420px] mx-auto">
                <!-- Mobile Brand Header -->
                <div class="lg:hidden text-center mb-8">
                    <div class="inline-flex w-14 h-14 rounded-2xl bg-slate-50 p-2.5 border border-slate-200 items-center justify-center shadow-md mb-3">
                        <img src="{{ asset('logo.png') }}" alt="{{ config('app.name') }}" class="w-full h-full object-contain">
                    </div>
                    <h2 class="text-xl font-bold text-slate-900">{{ config('app.name', 'School Result Portal') }}</h2>
                    <p class="text-xs text-slate-500 font-medium mt-0.5">Examination & Result Management System</p>
                </div>

                <!-- Unboxed Form Header -->
                <div class="mb-8">
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Set new password</h1>
                    <p class="text-sm text-slate-500 mt-2">Enter your email and confirm your new account credentials.</p>
                </div>

                <!-- Unboxed Reset Form -->
                <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
                    @csrf

                    <!-- Password Reset Token -->
                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                    <!-- Email Address -->
                    <div>
                        <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Email Address
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                </svg>
                            </div>
                            <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus
                                autocomplete="username" readonly
                                class="block w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-700 text-sm focus:outline-none" />
                        </div>
                        <x-input-error :messages="$errors->get('email')" class="mt-1.5 text-xs" />
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            New Password
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input id="password" :type="showPassword ? 'text' : 'password'" name="password" required
                                autocomplete="new-password" placeholder="••••••••"
                                class="block w-full pl-10 pr-10 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm placeholder-slate-400 focus:border-indigo-600 focus:ring-4 focus:ring-indigo-500/10 transition shadow-2xs" />
                            <button type="button" @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none">
                                <svg x-show="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg x-show="showPassword" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                </svg>
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-1.5 text-xs" />
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="password_confirmation" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Confirm Password
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <input id="password_confirmation" :type="showConfirmPassword ? 'text' : 'password'"
                                name="password_confirmation" required autocomplete="new-password" placeholder="••••••••"
                                class="block w-full pl-10 pr-10 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm placeholder-slate-400 focus:border-indigo-600 focus:ring-4 focus:ring-indigo-500/10 transition shadow-2xs" />
                            <button type="button" @click="showConfirmPassword = !showConfirmPassword"
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none">
                                <svg x-show="!showConfirmPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg x-show="showConfirmPassword" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                </svg>
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5 text-xs" />
                    </div>

                    <!-- Action Buttons -->
                    <div class="space-y-3 pt-2">
                        <button type="submit"
                            class="w-full flex items-center justify-center gap-2 py-3 px-4 rounded-xl text-white font-semibold text-sm bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-500 hover:to-indigo-600 focus:outline-none focus:ring-4 focus:ring-indigo-500/20 shadow-md shadow-indigo-600/30 active:scale-[0.99] transition duration-150">
                            <span>Reset Password</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </button>

                        <a href="{{ route('login') }}"
                            class="w-full flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl text-slate-700 hover:text-slate-900 font-semibold text-xs border border-slate-200 hover:bg-slate-50 transition">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            <span>Back to Login</span>
                        </a>
                    </div>
                </form>

                <!-- Footer Disclaimer & Legal Links -->
                <div class="mt-8 pt-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-slate-400">
                    <p>Internal school access only.</p>
                    <div class="flex items-center gap-3 font-medium">
                        <a href="{{ route('privacy.policy') }}" class="text-slate-500 hover:text-indigo-600 transition">Privacy Policy</a>
                        <span class="text-slate-300">•</span>
                        <a href="{{ route('terms.conditions') }}" class="text-slate-500 hover:text-indigo-600 transition">Terms & Conditions</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
