<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Terms & Conditions - {{ setting('school_name', config('app.name', 'School Result Portal')) }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-full font-sans antialiased text-slate-800 bg-slate-50 selection:bg-indigo-500 selection:text-white flex flex-col justify-between">
    <!-- Header -->
    <header class="sticky top-0 z-30 bg-white/95 backdrop-blur border-b border-slate-200/80 shadow-xs">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-500 flex items-center justify-center text-white shadow-md shadow-indigo-600/20 shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h1 class="font-extrabold text-sm sm:text-base text-slate-900 truncate">
                        {{ setting('school_name', config('app.name', 'School Result Portal')) }}
                    </h1>
                    <p class="text-[11px] text-slate-500 font-medium">Terms of Institutional Access & Service</p>
                </div>
            </div>
            <a href="{{ route('login') }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-100 hover:text-slate-900 transition">
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>Back to Sign In</span>
            </a>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14 flex-1">
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm p-6 sm:p-10 space-y-8">
            <!-- Title Section -->
            <div class="border-b border-slate-100 pb-6">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100/80 mb-3">
                    Institutional Governance
                </span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Terms and Conditions of Use</h2>
                <p class="text-xs sm:text-sm text-slate-500 mt-2">
                    Effective: {{ date('F Y') }} • Mandatory for all authenticated faculty members and administrative personnel.
                </p>
            </div>

            <!-- Section 1 -->
            <section class="space-y-3">
                <h3 class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-indigo-100 text-indigo-700 inline-flex items-center justify-center text-xs font-bold">1</span>
                    Authorization & Access Boundaries
                </h3>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                    This platform is an official, restricted internal management system belonging to <strong>{{ setting('school_name', 'our school') }}</strong>. Access is restricted solely to authorized teachers, examination controllers, and administrative officers. Any attempted or unauthorized access, automated scraping, or security evasion will result in immediate termination of privileges and potential disciplinary or legal action.
                </p>
            </section>

            <!-- Section 2 -->
            <section class="space-y-3">
                <h3 class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-indigo-100 text-indigo-700 inline-flex items-center justify-center text-xs font-bold">2</span>
                    User Credentials & Security Duties
                </h3>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                    Each account holder is individually responsible for safeguarding their login credentials:
                </p>
                <ul class="list-disc list-inside text-xs sm:text-sm text-slate-600 space-y-1.5 pl-2">
                    <li>Credential sharing with unauthorized colleagues, students, or external entities is strictly forbidden.</li>
                    <li>Users must sign out upon completing grading sessions on shared institutional or laboratory computers.</li>
                    <li>Suspicious authentication activity or lost credentials must be reported immediately to the super administrator.</li>
                </ul>
            </section>

            <!-- Section 3 -->
            <section class="space-y-3">
                <h3 class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-indigo-100 text-indigo-700 inline-flex items-center justify-center text-xs font-bold">3</span>
                    Evaluation Fidelity & Academic Integrity
                </h3>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                    Faculty members entering marks and assessments represent the institutional grading authority:
                </p>
                <ul class="list-disc list-inside text-xs sm:text-sm text-slate-600 space-y-1.5 pl-2">
                    <li>All submitted marks must accurately reflect the student's authentic examination performance.</li>
                    <li>Submitted marks are locked upon final submission and can only be modified with administrative review and documented audit justification.</li>
                    <li>Willful falsification or tampering with scores is considered severe professional misconduct.</li>
                </ul>
            </section>

            <!-- Section 4 -->
            <section class="space-y-3">
                <h3 class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-indigo-100 text-indigo-700 inline-flex items-center justify-center text-xs font-bold">4</span>
                    Intellectual Property & Official Award Rolls
                </h3>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                    All examination award rolls, PDF tabulation sheets, student statistics, and curriculum mappings generated through the <strong>{{ setting('app_name', 'Examination Result Management System') }}</strong> remain the sole intellectual property and official records of <strong>{{ setting('school_name', 'the institution') }}</strong>.
                </p>
            </section>

            <!-- Section 5 -->
            <section class="space-y-3">
                <h3 class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-indigo-100 text-indigo-700 inline-flex items-center justify-center text-xs font-bold">5</span>
                    Comprehensive Audit Trail Logging
                </h3>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                    By accessing this application, users acknowledge and consent to active session logging. The system records all administrative actions, mark drafts, submissions, verifications, unlocks, and password resets in an immutable audit ledger to ensure complete accountability.
                </p>
            </section>

            <!-- Section 6 -->
            <section class="space-y-3">
                <h3 class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-indigo-100 text-indigo-700 inline-flex items-center justify-center text-xs font-bold">6</span>
                    Modifications & Inquiries
                </h3>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                    The administration reserves the right to modify system policies in conformity with education board standards. Questions regarding these terms should be directed to the Institutional Examination Directorate.
                </p>
            </section>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200/80 py-6 text-xs text-slate-500">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-3 text-center sm:text-left">
            <p>&copy; {{ date('Y') }} {{ setting('school_name', config('app.name', 'School Result Portal')) }}. All rights reserved.</p>
            <div class="flex items-center gap-4">
                <a href="{{ route('privacy.policy') }}" class="hover:text-slate-700 transition">Privacy Policy</a>
                <span class="text-slate-300">•</span>
                <a href="{{ route('terms.conditions') }}" class="text-indigo-600 font-semibold hover:underline">Terms & Conditions</a>
                <span class="text-slate-300">•</span>
                <a href="{{ route('login') }}" class="hover:text-slate-700 transition">Portal Sign In</a>
            </div>
        </div>
    </footer>
</body>

</html>
