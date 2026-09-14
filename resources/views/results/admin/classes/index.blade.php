<x-app-layout>
    <x-slot name="title">{{ __('Classes & Sections') }}</x-slot>

    <div class="w-full space-y-6" x-data="{ addClassModal: false, addSectionModal: false, selectedClassId: null, selectedClassName: '' }">
        <!-- Top Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200">
            <div>
                <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Classes Management</h2>
                <p class="text-sm text-slate-500">Configure academic school grades and classes.</p>
            </div>
            <div class="flex items-center gap-2.5">
                <a href="{{ route('admin.sections.index') }}"
                    class="inline-flex items-center justify-center px-3.5 py-2 bg-blue-50 border border-blue-200 rounded-xl font-bold text-xs text-blue-800 uppercase tracking-wider hover:bg-blue-100 shadow-xs transition">
                    Manage Sections & Subjects &rarr;
                </a>
                <button @click="addClassModal = true" class="inline-flex items-center justify-center px-3 py-1.5 sm:px-4 sm:py-2.5 bg-indigo-600 rounded-lg sm:rounded-xl font-semibold text-[11px] sm:text-xs text-white uppercase tracking-wider hover:bg-indigo-700 shadow-sm transition w-fit">
                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add New Class
                </button>
            </div>
        </div>

        <x-alert />

        <!-- Classes Grid / Cards (Full Width) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-6 w-full">
            @forelse($classes as $class)
                <div class="bg-white rounded-xl sm:rounded-2xl shadow-sm border border-slate-200/80 p-3 sm:p-5 flex flex-col justify-between hover:border-indigo-200 transition">
                    <div>
                        <div class="flex items-center justify-between border-b border-slate-100 pb-2.5 sm:pb-3">
                            <h3 class="font-black text-base sm:text-lg text-slate-900">{{ $class->name }}</h3>
                            <span class="px-2 py-0.5 sm:px-2.5 sm:py-0.5 rounded-full text-[11px] sm:text-xs font-semibold {{ $class->status === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-slate-100 text-slate-600' }}">
                                {{ ucfirst($class->status) }}
                            </span>
                        </div>

                        <!-- Sections List -->
                        <div class="mt-3 sm:mt-4">
                            <p class="text-[11px] sm:text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Sections ({{ $class->sections->count() }})</p>
                            <div class="flex flex-wrap gap-1.5 sm:gap-2">
                                @forelse($class->sections as $section)
                                    <div class="inline-flex items-center gap-1.5 sm:gap-2 px-2.5 py-1 rounded-lg bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-800">
                                        <a href="{{ route('admin.sections.index', ['class_id' => $class->id, 'search' => $section->name]) }}"
                                            class="hover:text-indigo-600 transition" title="View & Manage this section">
                                            {{ $section->name }}
                                        </a>
                                        <form method="POST" action="{{ route('admin.sections.destroy', $section) }}" class="inline"
                                            data-confirm="Delete section '{{ $section->name }}'? Students in this section will lose their assignment."
                                            data-confirm-title="Delete Section"
                                            data-confirm-label="Yes, Delete"
                                            data-confirm-color="rose">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-slate-400 hover:text-rose-600 transition font-bold text-sm leading-none">&times;</button>
                                        </form>
                                    </div>
                                @empty
                                    <p class="text-xs text-slate-400 italic">No sections created yet.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Card Footer -->
                    <div class="mt-4 sm:mt-6 pt-2.5 sm:pt-3 border-t border-slate-100 flex items-center justify-between">
                        <button @click="addSectionModal = true; selectedClassId = {{ $class->id }}; selectedClassName = '{{ $class->name }}'" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 inline-flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add Section
                        </button>
                        <form method="POST" action="{{ route('admin.classes.destroy', $class) }}" class="inline"
                            data-confirm="Delete class '{{ $class->name }}' and all its sections? This is irreversible."
                            data-confirm-title="Delete Class"
                            data-confirm-label="Yes, Delete"
                            data-confirm-color="rose">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs font-medium text-rose-500 hover:text-rose-700 transition">Delete Class</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white rounded-2xl p-12 text-center text-slate-400 border border-slate-200">
                    No classes configured yet. Click "+ Add New Class" above.
                </div>
            @endforelse
        </div>

        <!-- Modal: Add Class -->
        <div x-show="addClassModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs transition-opacity" @click="addClassModal = false"></div>
                <div class="bg-white rounded-2xl overflow-hidden shadow-xl transform transition-all sm:max-w-md w-full z-10 p-6 border border-slate-100">
                    <h3 class="text-base font-bold text-slate-900 mb-4">Add School Class</h3>
                    <form method="POST" action="{{ route('admin.classes.store') }}" class="space-y-4">
                        @csrf
                        <div>
                            <x-input-label for="class_name" :value="__('Class Name (e.g. Class 10)')" class="font-semibold text-slate-700" />
                            <x-text-input id="class_name" class="block mt-1.5 w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" type="text" name="name" required placeholder="Class 10" />
                        </div>
                        <div>
                            <x-input-label for="class_order" :value="__('Display Order')" class="font-semibold text-slate-700" />
                            <x-text-input id="class_order" class="block mt-1.5 w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" type="number" name="display_order" value="0" />
                        </div>
                        <div class="flex justify-end gap-2 pt-4 border-t border-slate-100">
                            <button type="button" @click="addClassModal = false" class="px-4 py-2 text-sm text-slate-600 hover:text-slate-900">Cancel</button>
                            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs rounded-lg uppercase tracking-wider shadow-sm transition">Create Class</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal: Add Section -->
        <div x-show="addSectionModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs transition-opacity" @click="addSectionModal = false"></div>
                <div class="bg-white rounded-2xl overflow-hidden shadow-xl transform transition-all sm:max-w-md w-full z-10 p-6 border border-slate-100">
                    <h3 class="text-base font-bold text-slate-900 mb-1">Add Section</h3>
                    <p class="text-xs text-slate-500 mb-4">Assigning section to class: <span class="font-bold text-indigo-600" x-text="selectedClassName"></span></p>
                    <form method="POST" action="{{ route('admin.sections.store') }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="class_id" :value="selectedClassId">
                        <div>
                            <x-input-label for="section_name" :value="__('Section Name (e.g. A, B, C)')" class="font-semibold text-slate-700" />
                            <x-text-input id="section_name" class="block mt-1.5 w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" type="text" name="name" required placeholder="e.g. A, B" />
                        </div>
                        <div class="flex justify-end gap-2 pt-4 border-t border-slate-100">
                            <button type="button" @click="addSectionModal = false" class="px-4 py-2 text-sm text-slate-600 hover:text-slate-900">Cancel</button>
                            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs rounded-lg uppercase tracking-wider shadow-sm transition">Add Section</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
