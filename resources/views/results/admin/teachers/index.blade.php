<x-app-layout>
    <x-slot name="title">{{ __('Teachers & Faculty') }}</x-slot>

    <div class="w-full space-y-6">
        <!-- Top Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200">
            <div>
                <p class="text-sm text-slate-500">Manage faculty members, portal login access, and subject allocations.</p>
            </div>
            <a href="{{ route('admin.teachers.create') }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-indigo-600 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-wider hover:bg-indigo-700 shadow-sm transition w-fit">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Teacher
            </a>
        </div>

        <x-alert />

        <!-- Full-Width Responsive Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden w-full">
            <div class="overflow-x-auto w-full">
                <table class="w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50/80">
                        <tr>
                            <th class="px-5 py-3.5 text-left font-bold text-slate-700 text-xs uppercase tracking-wider">Faculty Member</th>
                            <th class="px-5 py-3.5 text-left font-bold text-slate-700 text-xs uppercase tracking-wider">Login Email</th>
                            <th class="px-5 py-3.5 text-left font-bold text-slate-700 text-xs uppercase tracking-wider">Phone</th>
                            <th class="px-5 py-3.5 text-center font-bold text-slate-700 text-xs uppercase tracking-wider">Assigned Classes</th>
                            <th class="px-5 py-3.5 text-center font-bold text-slate-700 text-xs uppercase tracking-wider">Status</th>
                            <th class="px-5 py-3.5 text-right font-bold text-slate-700 text-xs uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($teachers as $teacher)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-indigo-600 to-violet-500 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">
                                            {{ strtoupper(substr($teacher->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <span class="font-bold text-slate-900 block">{{ $teacher->name }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-slate-600 text-xs sm:text-sm">
                                    {{ $teacher->email }}
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-xs text-slate-500">
                                    {{ $teacher->phone ?? '—' }}
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                        {{ $teacher->teacher_assignments_count }} Subjects
                                    </span>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-center">
                                    @if($teacher->status === 'banned')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 shadow-2xs">
                                            Banned
                                        </span>
                                    @elseif($teacher->status === 'active')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100 shadow-2xs">
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                            {{ ucfirst($teacher->status) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-right text-xs space-x-1.5">
                                    <a href="{{ route('admin.teachers.edit', $teacher) }}" class="inline-flex items-center px-2.5 py-1.5 rounded-lg border border-slate-200 text-indigo-600 hover:bg-indigo-50 hover:border-indigo-200 font-semibold transition">
                                        Edit
                                    </a>
                                    <a href="{{ route('admin.assignments.index', ['teacher_id' => $teacher->id]) }}" class="inline-flex items-center px-2.5 py-1.5 rounded-lg border border-slate-200 text-purple-600 hover:bg-purple-50 hover:border-purple-200 font-semibold transition">
                                        Assignments
                                    </a>
                                    <form method="POST" action="{{ route('admin.teachers.toggle-ban', $teacher) }}" class="inline-block">
                                        @csrf
                                        @if($teacher->status === 'banned')
                                            <button type="submit"
                                                data-confirm="Unban {{ $teacher->name }} and restore their portal login access?"
                                                data-confirm-title="Restore Access"
                                                data-confirm-label="Yes, Unban"
                                                data-confirm-color="emerald"
                                                class="inline-flex items-center px-2.5 py-1.5 rounded-lg border border-emerald-200 text-emerald-700 bg-emerald-50 hover:bg-emerald-100 font-semibold transition">
                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                Unban
                                            </button>
                                        @else
                                            <button type="submit"
                                                data-confirm="Are you sure you want to ban {{ $teacher->name }}? They will be immediately blocked from portal login."
                                                data-confirm-title="Ban Teacher"
                                                data-confirm-label="Yes, Ban"
                                                data-confirm-color="rose"
                                                class="inline-flex items-center px-2.5 py-1.5 rounded-lg border border-rose-200 text-rose-700 bg-rose-50 hover:bg-rose-100 font-semibold transition">
                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                </svg>
                                                Ban
                                            </button>
                                        @endif
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                    <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    No teachers found. Click "+ Add Teacher" to register a faculty member.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $teachers->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
