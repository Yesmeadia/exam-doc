<nav x-data="{ open: false }" class="bg-white border-b border-gray-200 sticky top-0 z-50 shadow-sm">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                        <span
                            class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-white p-1 border border-slate-200 shadow-sm overflow-hidden shrink-0">
                            <img src="{{ asset('icons/logo.svg') }}" alt="{{ setting('school_short_name', 'School Logo') }}" class="w-full h-full object-contain" onerror="this.onerror=null; this.src='{{ asset('icons/logo.png') }}';">
                        </span>
                        <span
                            class="font-bold text-gray-900 text-lg tracking-tight">{{ setting('school_short_name', setting('app_name', config('app.name', 'ERMS Results'))) }}</span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-4 sm:-my-px sm:ms-8 sm:flex items-center">
                    @auth
                        @if(Auth::user()->hasRole('super-admin'))
                            <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                                {{ __('Dashboard') }}
                            </x-nav-link>

                            <!-- Academic Structure Dropdown -->
                            <x-dropdown align="left" width="48">
                                <x-slot name="trigger">
                                    <button
                                        class="inline-flex items-center px-2 py-2 text-sm font-medium leading-5 text-gray-600 hover:text-indigo-600 focus:outline-none transition ease-in-out duration-150">
                                        <span>Academic</span>
                                        <svg class="ms-1 h-4 w-4 fill-current text-gray-400" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </x-slot>
                                <x-slot name="content">
                                    <x-dropdown-link
                                        :href="route('admin.academic-years.index')">{{ __('Academic Years') }}</x-dropdown-link>
                                    <x-dropdown-link :href="route('admin.exams.index')">{{ __('Exams') }}</x-dropdown-link>
                                    <x-dropdown-link
                                        :href="route('admin.classes.index')">{{ __('Classes') }}</x-dropdown-link>
                                    <x-dropdown-link
                                        :href="route('admin.sections.index')">{{ __('Sections') }}</x-dropdown-link>
                                    <x-dropdown-link
                                        :href="route('admin.subjects.index')">{{ __('Subjects') }}</x-dropdown-link>
                                </x-slot>
                            </x-dropdown>

                            <!-- People Dropdown -->
                            <x-dropdown align="left" width="48">
                                <x-slot name="trigger">
                                    <button
                                        class="inline-flex items-center px-2 py-2 text-sm font-medium leading-5 text-gray-600 hover:text-indigo-600 focus:outline-none transition ease-in-out duration-150">
                                        <span>Students & Staff</span>
                                        <svg class="ms-1 h-4 w-4 fill-current text-gray-400" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </x-slot>
                                <x-slot name="content">
                                    <x-dropdown-link
                                        :href="route('admin.students.index')">{{ __('All Students') }}</x-dropdown-link>
                                    <x-dropdown-link
                                        :href="route('admin.students.import.form')">{{ __('Bulk Import Excel') }}</x-dropdown-link>
                                    <x-dropdown-link
                                        :href="route('admin.teachers.index')">{{ __('Teachers') }}</x-dropdown-link>
                                    <x-dropdown-link
                                        :href="route('admin.assignments.index')">{{ __('Teacher Assignments') }}</x-dropdown-link>
                                </x-slot>
                            </x-dropdown>

                            <!-- Marks & Results Dropdown -->
                            <x-dropdown align="left" width="56">
                                <x-slot name="trigger">
                                    <button
                                        class="inline-flex items-center px-2 py-2 text-sm font-medium leading-5 text-gray-600 hover:text-indigo-600 focus:outline-none transition ease-in-out duration-150">
                                        <span>Marks & Results</span>
                                        <svg class="ms-1 h-4 w-4 fill-current text-gray-400" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </x-slot>
                                <x-slot name="content">
                                     <x-dropdown-link
                                         :href="route('admin.marks.index')">{{ __('Mark Entry Status & Verify') }}</x-dropdown-link>
                                     <x-dropdown-link
                                         :href="route('admin.marks-sheets.index')">{{ __('Student Marks Sheets') }}</x-dropdown-link>
                                     <x-dropdown-link
                                         :href="route('admin.class-wise-statement.index')">{{ __('Class Wise Statement') }}</x-dropdown-link>
                                     <x-dropdown-link
                                         :href="route('admin.award-rolls.index')">{{ __('Award Rolls (PDF)') }}</x-dropdown-link>
                                    <x-dropdown-link
                                        :href="route('admin.audit-logs.index')">{{ __('Audit Trail') }}</x-dropdown-link>
                                </x-slot>
                            </x-dropdown>
                        @elseif(Auth::user()->hasRole('teacher'))
                            <x-nav-link :href="route('teacher.dashboard')" :active="request()->routeIs('teacher.dashboard')">
                                {{ __('My Assignments & Marks') }}
                            </x-nav-link>
                        @endif
                    @endauth


                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                @auth
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:text-gray-900 focus:outline-none transition ease-in-out duration-150">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="w-7 h-7 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs">
                                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                    </span>
                                    <span>{{ Auth::user()->name }}</span>
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">
                                        {{ Auth::user()->getRoleNames()->first() ?? 'User' }}
                                    </span>
                                </div>

                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Profile') }}
                            </x-dropdown-link>

                            @if(Auth::user()->hasRole('super-admin'))
                                <x-dropdown-link :href="route('admin.settings.index')">
                                    {{ __('School & Settings') }}
                                </x-dropdown-link>
                            @endif

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf

                                <x-dropdown-link :href="route('logout')" onclick="event.preventDefault();
                                                                this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-medium text-gray-700 hover:text-indigo-600">Log
                        in</a>
                @endauth
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-white border-t border-gray-100">
        <div class="pt-2 pb-3 space-y-1">
            @auth
                @if(Auth::user()->hasRole('super-admin'))
                    <x-responsive-nav-link :href="route('admin.dashboard')">{{ __('Dashboard') }}</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.exams.index')">{{ __('Exams') }}</x-responsive-nav-link>
                    <x-responsive-nav-link
                        :href="route('admin.classes.index')">{{ __('Classes') }}</x-responsive-nav-link>
                    <x-responsive-nav-link
                        :href="route('admin.sections.index')">{{ __('Sections') }}</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.subjects.index')">{{ __('Subjects') }}</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.students.index')">{{ __('Students') }}</x-responsive-nav-link>
                    <x-responsive-nav-link
                        :href="route('admin.students.import.form')">{{ __('Bulk Import Students') }}</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.teachers.index')">{{ __('Teachers') }}</x-responsive-nav-link>
                    <x-responsive-nav-link
                        :href="route('admin.assignments.index')">{{ __('Teacher Assignments') }}</x-responsive-nav-link>
                    <x-responsive-nav-link
                        :href="route('admin.marks.index')">{{ __('Mark Entry Status') }}</x-responsive-nav-link>
                    <x-responsive-nav-link
                        :href="route('admin.marks-sheets.index')">{{ __('Student Marks Sheets') }}</x-responsive-nav-link>
                    <x-responsive-nav-link
                        :href="route('admin.class-wise-statement.index')">{{ __('Class Wise Statement') }}</x-responsive-nav-link>
                    <x-responsive-nav-link
                        :href="route('admin.award-rolls.index')">{{ __('Award Rolls') }}</x-responsive-nav-link>
                    <x-responsive-nav-link
                        :href="route('admin.audit-logs.index')">{{ __('Audit Trail') }}</x-responsive-nav-link>
                @elseif(Auth::user()->hasRole('teacher'))
                    <x-responsive-nav-link :href="route('teacher.dashboard')">{{ __('My Assignments') }}</x-responsive-nav-link>
                @endif
            @endauth

        </div>

        @auth
            <!-- Responsive Settings Options -->
            <div class="pt-4 pb-1 border-t border-gray-200">
                <div class="px-4">
                    <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                </div>

                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('profile.edit')">
                        {{ __('Profile') }}
                    </x-responsive-nav-link>

                    <!-- Authentication -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault();
                                                        this.closest('form').submit();">
                            {{ __('Log Out') }}
                        </x-responsive-nav-link>
                    </form>
                </div>
            </div>
        @endauth
    </div>
</nav>