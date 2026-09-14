<x-app-layout>
    <x-slot name="title">{{ __('Academic Years') }}</x-slot>

    <div class="w-full space-y-6">
        <!-- Top Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200">
            <div>
                <p class="text-sm text-slate-500">Configure school academic calendar sessions and manage active operational years.</p>
            </div>
            <a href="{{ route('admin.academic-years.create') }}"
                class="inline-flex items-center justify-center px-4 py-2.5 bg-indigo-600 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-wider hover:bg-indigo-700 shadow-sm transition w-fit">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Academic Year
            </a>
        </div>

        <x-alert />

        <!-- Full-Width Responsive Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden w-full">
            <div class="overflow-x-auto w-full">
                <table class="w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50/80">
                        <tr>
                            <th class="px-6 py-3.5 text-left font-bold text-slate-700 text-xs uppercase tracking-wider">Year Name</th>
                            <th class="px-6 py-3.5 text-left font-bold text-slate-700 text-xs uppercase tracking-wider">Start Date</th>
                            <th class="px-6 py-3.5 text-left font-bold text-slate-700 text-xs uppercase tracking-wider">End Date</th>
                            <th class="px-6 py-3.5 text-center font-bold text-slate-700 text-xs uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3.5 text-right font-bold text-slate-700 text-xs uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($years as $year)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="px-6 py-4 whitespace-nowrap font-bold text-slate-900">{{ $year->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-slate-600">
                                    {{ $year->start_date ? $year->start_date->format('d M Y') : '—' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-slate-600">
                                    {{ $year->end_date ? $year->end_date->format('d M Y') : '—' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($year->is_active)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                            Active
                                        </span>
                                    @else
                                        <form method="POST" action="{{ route('admin.academic-years.toggle-active', $year) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="text-xs text-slate-500 hover:text-indigo-600 font-medium underline">
                                                Set as Active
                                            </button>
                                        </form>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-xs space-x-2">
                                    <a href="{{ route('admin.academic-years.edit', $year) }}"
                                        class="inline-flex items-center px-2.5 py-1.5 rounded-lg border border-slate-200 text-indigo-600 hover:bg-indigo-50 hover:border-indigo-200 font-semibold transition">
                                        Edit
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                    No academic years found. Click "Add Academic Year" to create one.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $years->links() }}
            </div>
        </div>
    </div>
</x-app-layout>