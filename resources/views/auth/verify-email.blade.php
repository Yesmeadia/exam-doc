<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-white">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Verify Email - {{ config('app.name', 'School Result Portal') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-full font-sans antialiased text-slate-800 bg-white">
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
                            Account Verification
                        </p>
                    </div>
                </div>
            </div>

            <!-- Middle Value Propositions -->
            <div class="relative z-10 my-auto py-10 space-y-7 max-w-xl">
                <div>
                    <h3 class="text-3xl font-extrabold tracking-tight text-white leading-tight">
                        Verify Your Institutional Address
                    </h3>
                    <p class="text-sm text-slate-300 mt-3 leading-relaxed">
                        Email verification confirms authorized ownership of faculty and administrative accounts prior to granting access to grading, auditing, and student records.
                    </p>
                </div>

                <div class="space-y-4 pt-1">
                    <div class="flex items-start gap-3.5">
                        <div class="w-8 h-8 rounded-lg bg-indigo-500/20 border border-indigo-400/30 flex items-center justify-center text-indigo-300 shrink-0 mt-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-white">Cryptographic Signature</h4>
                            <p class="text-xs text-slate-400 mt-0.5">Activation links are bound to temporary cryptographic hash digests to prevent replay abuse.</p>
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

        <!-- RIGHT SIDE: Seamless Unboxed Verification Notice (Reduced Width 40%) -->
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
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Verify your email</h1>
                    <p class="text-sm text-slate-500 mt-2">
                        Thanks for signing up! Before getting started, please check your inbox and click the verification link we just emailed to you.
                    </p>
                </div>

                @if (session('status') == 'verification-link-sent')
                    <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200/80 text-emerald-800 text-xs font-semibold flex items-center gap-2.5">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>A new verification link has been sent to your registered email address.</span>
                    </div>
                @endif

                <div class="space-y-4">
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit"
                            class="w-full flex items-center justify-center gap-2 py-3 px-4 rounded-xl text-white font-semibold text-sm bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-500 hover:to-indigo-600 focus:outline-none focus:ring-4 focus:ring-indigo-500/20 shadow-md shadow-indigo-600/30 active:scale-[0.99] transition duration-150">
                            <span>Resend Verification Email</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </button>
                    </form>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="w-full flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl text-slate-700 hover:text-slate-900 font-semibold text-xs border border-slate-200 hover:bg-slate-50 transition">
                            <span>Sign Out & Return to Login</span>
                        </button>
                    </form>
                </div>

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
