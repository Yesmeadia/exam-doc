<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Privacy Policy - {{ setting('school_name', config('app.name', 'School Result Portal')) }}</title>

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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h1 class="font-extrabold text-sm sm:text-base text-slate-900 truncate">
                        {{ setting('school_name', config('app.name', 'School Result Portal')) }}
                    </h1>
                    <p class="text-[11px] text-slate-500 font-medium">Privacy Policy & Academic Data Protection</p>
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
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Institutional Privacy Policy</h2>
                <p class="text-xs sm:text-sm text-slate-500 mt-2">
                    Last updated: {{ date('F Y') }} • Applies to faculty, administrators, and educational stakeholders.
                </p>
            </div>

            <!-- Section 1 -->
            <section class="space-y-3">
                <h3 class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-indigo-100 text-indigo-700 inline-flex items-center justify-center text-xs font-bold">1</span>
                    Scope & Commitment
                </h3>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                    This Privacy Policy governs the processing, management, and protection of academic records, teacher allocations, student evaluations, and institutional assessment data handled by the <strong>{{ setting('app_name', 'Examination Result Management System') }}</strong> on behalf of <strong>{{ setting('school_name', 'our institution') }}</strong>. We are committed to maintaining stringent confidentiality, data integrity, and strict adherence to institutional data governance standards.
                </p>
            </section>

            <!-- Section 2 -->
            <section class="space-y-3">
                <h3 class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-indigo-100 text-indigo-700 inline-flex items-center justify-center text-xs font-bold">2</span>
                    Information Collected
                </h3>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                    The platform collects only essential academic and administrative data necessary to fulfill its educational mission:
                </p>
                <ul class="list-disc list-inside text-xs sm:text-sm text-slate-600 space-y-1.5 pl-2">
                    <li><strong>Student Academic Information:</strong> Admission numbers, roll numbers, registered classes, sections, assigned elective subjects, and term marks.</li>
                    <li><strong>Faculty & Staff Identity:</strong> Institutional email addresses, names, assigned course subjects, and class sections.</li>
                    <li><strong>Authentication & Security Metrics:</strong> Encrypted password hashes, authenticated session tokens, timestamps, IP addresses, and Cloudflare Turnstile security verifications.</li>
                </ul>
            </section>

            <!-- Section 3 -->
            <section class="space-y-3">
                <h3 class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-indigo-100 text-indigo-700 inline-flex items-center justify-center text-xs font-bold">3</span>
                    Purposes of Processing
                </h3>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                    Information gathered within this system is utilized exclusively for:
                </p>
                <ul class="list-disc list-inside text-xs sm:text-sm text-slate-600 space-y-1.5 pl-2">
                    <li>Facilitating secure, role-restricted faculty mark entry and draft verification.</li>
                    <li>Generating official A4 Tabulation Award Rolls, report cards, and institutional analytics.</li>
                    <li>Maintaining verifiable audit trails of all grading updates, mark lock/unlock events, and administrative actions.</li>
                    <li>Preventing unauthorized access, grade tampering, and academic discrepancies.</li>
                </ul>
            </section>

            <!-- Section 4 -->
            <section class="space-y-3">
                <h3 class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-indigo-100 text-indigo-700 inline-flex items-center justify-center text-xs font-bold">4</span>
                    Access Control & Data Security
                </h3>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                    Data isolation is enforced through granular role-based permissions:
                </p>
                <ul class="list-disc list-inside text-xs sm:text-sm text-slate-600 space-y-1.5 pl-2">
                    <li><strong>Faculty Isolation:</strong> Teaching staff may only view and enter marks for classes, sections, and subjects specifically allocated to them.</li>
                    <li><strong>Cryptographic Protection:</strong> User passwords are encrypted with one-way Bcrypt hashing. Sensitive recovery tokens expire automatically.</li>
                    <li><strong>Audit Trail Logging:</strong> Every critical operation (grade entry, status modifications, publish events) is indelibly logged with operator identity and exact timestamps.</li>
                </ul>
            </section>

            <!-- Section 5 -->
            <section class="space-y-3">
                <h3 class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-indigo-100 text-indigo-700 inline-flex items-center justify-center text-xs font-bold">5</span>
                    Third-Party Disclosure & Sharing
                </h3>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                    Student marks and faculty records are confidential institutional assets. They are <strong>never sold, shared with commercial advertisers, or disclosed to unauthorized third parties</strong>. Information is disclosed solely to authorized education authorities, statutory oversight boards, or as formally mandated by governing academic regulations.
                </p>
            </section>

            <!-- Section 6 -->
            <section class="space-y-3">
                <h3 class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-indigo-100 text-indigo-700 inline-flex items-center justify-center text-xs font-bold">6</span>
                    Inquiries & Contact
                </h3>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                    For inquiries concerning academic data privacy or evaluation records, contact the institutional examination committee or office of the principal at <strong>{{ setting('school_name', config('app.name', 'School Administration')) }}</strong>.
                </p>
            </section>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200/80 py-6 text-xs text-slate-500">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-3 text-center sm:text-left">
            <p>&copy; {{ date('Y') }} {{ setting('school_name', config('app.name', 'School Result Portal')) }}. All rights reserved.</p>
            <div class="flex items-center gap-4">
                <a href="{{ route('privacy.policy') }}" class="text-indigo-600 font-semibold hover:underline">Privacy Policy</a>
                <span class="text-slate-300">•</span>
                <a href="{{ route('terms.conditions') }}" class="hover:text-slate-700 transition">Terms & Conditions</a>
                <span class="text-slate-300">•</span>
                <a href="{{ route('login') }}" class="hover:text-slate-700 transition">Portal Sign In</a>
            </div>
        </div>
    </footer>
</body>

</html>
