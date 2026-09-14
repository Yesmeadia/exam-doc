<x-app-layout>
    <x-slot name="title">{{ __('Curriculum Subjects') }}</x-slot>

    <div class="w-full space-y-6">
        <!-- Top Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200">
            <div>
                <p class="text-sm text-slate-500">Manage academic subjects, class grade mappings, and passing criteria.</p>
            </div>
            <a href="{{ route('admin.subjects.create') }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-indigo-600 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-wider hover:bg-indigo-700 shadow-sm transition w-fit">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Subject
            </a>
        </div>

        <x-alert />

        <!-- Full-Width Responsive Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden w-full">
            <div class="overflow-x-auto w-full">
                <table class="w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50/80">
                        <tr>
                            <th class="px-5 py-3.5 text-left font-bold text-slate-700 text-xs uppercase tracking-wider">Subject Name</th>
                            <th class="px-5 py-3.5 text-left font-bold text-slate-700 text-xs uppercase tracking-wider">Classes Mapped</th>
                            <th class="px-5 py-3.5 text-center font-bold text-slate-700 text-xs uppercase tracking-wider">Max Marks</th>
                            <th class="px-5 py-3.5 text-center font-bold text-slate-700 text-xs uppercase tracking-wider">Pass Marks</th>
                            <th class="px-5 py-3.5 text-right font-bold text-slate-700 text-xs uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($subjects as $subject)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="px-5 py-4 whitespace-nowrap font-bold text-slate-900">
                                    {{ $subject->name }}
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap gap-1">
                                        @forelse($subject->classes as $c)
                                            <span class="px-2 py-0.5 rounded-md text-xs bg-slate-100 text-slate-700 font-semibold border border-slate-200/80">{{ $c->name }}</span>
                                        @empty
                                            <span class="text-xs text-slate-400">All / None</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-center font-bold text-slate-900">
                                    {{ number_format($subject->maximum_marks, 0) }}
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-center font-semibold text-emerald-700">
                                    {{ number_format($subject->pass_marks, 0) }}
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-right text-xs space-x-2">
                                    <a href="{{ route('admin.subjects.edit', $subject) }}" class="inline-flex items-center px-2.5 py-1.5 rounded-lg border border-slate-200 text-indigo-600 hover:bg-indigo-50 font-semibold transition">
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('admin.subjects.destroy', $subject) }}" class="inline"
                                        data-confirm="Delete subject '{{ $subject->name }}'? This cannot be undone."
                                        data-confirm-title="Delete Subject"
                                        data-confirm-label="Yes, Delete"
                                        data-confirm-color="rose">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center px-2.5 py-1.5 rounded-lg border border-slate-200 text-rose-600 hover:bg-rose-50 font-semibold transition">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                    No subjects added yet. Click "+ Add Subject" to create one.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $subjects->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
