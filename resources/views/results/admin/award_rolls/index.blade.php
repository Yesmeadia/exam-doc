<x-app-layout>
    <x-slot name="title">{{ __('Award Rolls') }}</x-slot>

    <div class="w-full space-y-6" x-data="{
        classes: {{ Js::from($classes) }},
        selectedClassId: '{{ $selectedClassId }}',
        selectedSectionId: '{{ $selectedSectionId }}',
        allSubjects: {{ Js::from($subjects->map(fn($s) => [
            'id' => $s->id,
            'name' => $s->name,
            'class_ids' => $s->classes->pluck('id')->toArray(),
        ])) }},
        selectedSubjectId: '{{ $selectedSubjectId }}',
        sections: [],
        filteredSubjects: [],
        updateSections() {
            const found = this.classes.find(c => String(c.id) === String(this.selectedClassId));
            this.sections = (found && found.sections) ? found.sections : [];
            if (!this.sections.find(s => String(s.id) === String(this.selectedSectionId))) {
                this.selectedSectionId = this.sections.length > 0 ? this.sections[0].id : '';
            }
            this.updateSubjects();
        },
        updateSubjects() {
            if (!this.selectedClassId) {
                this.filteredSubjects = this.allSubjects;
                return;
            }
            const foundClass = this.classes.find(c => String(c.id) === String(this.selectedClassId));
            const className = foundClass ? (foundClass.name || '').toLowerCase() : '';
            const isHigherSecondary = /11|12|xi|xii/i.test(className);

            const foundSection = this.sections.find(s => String(s.id) === String(this.selectedSectionId));
            const sectionName = foundSection ? (foundSection.name || '').toLowerCase() : '';
            const isScience = sectionName.includes('science');
            const isHumanities = sectionName.includes('humanities') || sectionName.includes('arts');

            this.filteredSubjects = this.allSubjects.filter(sub => {
                // Must be attached to this class (if class associations exist)
                if (sub.class_ids.length > 0 && !sub.class_ids.includes(Number(this.selectedClassId))) {
                    return false;
                }
                if (isHigherSecondary) {
                    const subName = (sub.name || '').toLowerCase();
                    if (isScience) {
                        if (/(history|political science|civics|education)/i.test(subName)) {
                            return false;
                        }
                    } else if (isHumanities) {
                        if (/(biology|bio|physics|chemistry)/i.test(subName)) {
                            return false;
                        }
                    }
                }
                return true;
            });

            if (!this.filteredSubjects.find(s => String(s.id) === String(this.selectedSubjectId))) {
                this.selectedSubjectId = this.filteredSubjects.length > 0 ? this.filteredSubjects[0].id : '';
            }
        }
    }" x-init="updateSections()">

        {{-- Top Action Bar --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200">
            <div>
                <p class="text-sm text-slate-500">Generate print-ready A4 Award Roll reports sorted by roll number.</p>
            </div>
            @if($selectedExam)
                <form method="POST" action="{{ route('admin.award-rolls.generate-bulk') }}"
                    data-confirm="Register Award Roll records for all active assignments in {{ $selectedExam->exam_name }}?"
                    data-confirm-title="Bulk Register Award Rolls"
                    data-confirm-label="Yes, Register All"
                    data-confirm-color="indigo">
                    @csrf
                    <input type="hidden" name="exam_id" value="{{ $selectedExam->id }}">
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-bold uppercase tracking-wider shadow-sm transition">
                        <svg class="w-4 h-4 me-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        Register All Award Rolls
                    </button>
                </form>
            @endif
        </div>

        <x-alert />

        {{-- Generator Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-6 sm:p-8 w-full">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-1">
                <h3 class="text-base font-bold text-slate-900">Download Report</h3>
                <div id="download-status" style="display:none;" class="flex items-center gap-2 text-xs font-semibold text-indigo-600">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                    </svg>
                    <span id="download-status-text">Generating report...</span>
                </div>
            </div>
            <p class="text-xs text-slate-500 mb-5">
                Select an examination, class, section, and subject. The file downloads directly to your computer and
                the entry appears automatically in the <strong>Generated Award Rolls Directory</strong> below.
                No files are stored on the server.
            </p>

            {{-- Hidden iframe: receives the binary stream so the page never navigates away --}}
            <iframe id="download-frame" name="download-frame"
                    style="display:none;width:0;height:0;border:0;" aria-hidden="true"></iframe>

            <form id="award-roll-form"
                  method="POST"
                  action="{{ route('admin.award-rolls.generate') }}"
                  target="download-frame"
                  class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <x-input-label for="exam_id" :value="__('Examination')" class="font-semibold text-slate-700" />
                        <select id="exam_id" name="exam_id"
                            class="block mt-1.5 w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                            required>
                            @foreach($exams as $exam)
                                <option value="{{ $exam->id }}" {{ $selectedExamId == $exam->id ? 'selected' : '' }}>
                                    {{ $exam->exam_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="class_id" :value="__('Class')" class="font-semibold text-slate-700" />
                        <select id="class_id" name="class_id"
                            x-model="selectedClassId" @change="updateSections()"
                            class="block mt-1.5 w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                            required>
                            <option value="">-- Select Class --</option>
                            <template x-for="c in classes" :key="c.id">
                                <option :value="c.id" x-text="c.name" :selected="c.id == selectedClassId"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <x-input-label for="section_id" :value="__('Section')" class="font-semibold text-slate-700" />
                        <select id="section_id" name="section_id"
                            x-model="selectedSectionId" @change="updateSubjects()"
                            class="block mt-1.5 w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                            required>
                            <option value="">-- Select Section --</option>
                            <template x-for="s in sections" :key="s.id">
                                <option :value="s.id" x-text="s.name" :selected="s.id == selectedSectionId"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <x-input-label for="subject_id" :value="__('Subject')" class="font-semibold text-slate-700" />
                        <select id="subject_id" name="subject_id"
                            x-model="selectedSubjectId"
                            class="block mt-1.5 w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                            required>
                            <option value="">-- Select Subject --</option>
                            <template x-for="sub in filteredSubjects" :key="sub.id">
                                <option :value="sub.id" x-text="sub.name" :selected="String(sub.id) === String(selectedSubjectId)"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-slate-100">
                    <p class="text-[11px] text-slate-400">
                        Marks are compiled live from the database and streamed to your browser. No files are stored on the server.
                    </p>
                    <div class="flex items-center gap-2">
                        <button type="submit" id="btn-excel" name="format" value="excel"
                            class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs uppercase tracking-wider shadow-sm transition disabled:opacity-60 disabled:cursor-not-allowed">
                            <svg class="w-4 h-4 me-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Download Excel (.xlsx)
                        </button>
                        <button type="submit" id="btn-pdf" name="format" value="pdf"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-xs uppercase tracking-wider shadow-sm transition disabled:opacity-60 disabled:cursor-not-allowed">
                            <svg class="w-4 h-4 me-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Download Report (PDF)
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Generated Award Rolls Directory --}}
        <div id="award-rolls-directory"
             class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden w-full">
            <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Generated Award Rolls Directory</h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Every download is logged here. Reports are re-generated live on demand —
                        no physical files are stored on the server.
                    </p>
                </div>
                <form method="GET" action="{{ route('admin.award-rolls.index') }}" class="flex items-center gap-2">
                    <input type="hidden" name="exam_id" value="{{ $selectedExamId }}">
                    <select name="archive_exam_id" onchange="this.form.submit()"
                        class="text-xs font-semibold rounded-xl border-slate-300 py-1.5 pl-3 pr-8 focus:ring-indigo-500 focus:border-indigo-500 shadow-2xs">
                        <option value="">All Examinations</option>
                        @foreach($exams as $exam)
                            <option value="{{ $exam->id }}"
                                {{ ($archiveExamId ?? '') == $exam->id ? 'selected' : '' }}>
                                {{ $exam->exam_name }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>

            <div class="overflow-x-auto w-full">
                <table class="w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50/80">
                        <tr>
                            <th class="px-5 py-3.5 text-left font-bold text-slate-700 text-xs uppercase tracking-wider">Exam</th>
                            <th class="px-5 py-3.5 text-left font-bold text-slate-700 text-xs uppercase tracking-wider">Class &amp; Section</th>
                            <th class="px-5 py-3.5 text-left font-bold text-slate-700 text-xs uppercase tracking-wider">Subject</th>
                            <th class="px-5 py-3.5 text-center font-bold text-slate-700 text-xs uppercase tracking-wider">Students</th>
                            <th class="px-5 py-3.5 text-left font-bold text-slate-700 text-xs uppercase tracking-wider">Last Downloaded</th>
                            <th class="px-5 py-3.5 text-right font-bold text-slate-700 text-xs uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($awardRolls as $ar)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="px-5 py-4 whitespace-nowrap font-bold text-slate-900">
                                    {{ $ar->exam?->exam_name }}
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-slate-700 font-semibold">
                                    {{ $ar->schoolClass?->name }} ({{ $ar->section?->name }})
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap font-bold text-indigo-900">
                                    {{ $ar->subject?->name }}
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-center font-bold text-slate-700">
                                    {{ $ar->student_count }}
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-xs text-slate-500">
                                    {{ ($ar->updated_at ?? $ar->created_at)->timezone('Asia/Kolkata')->format('d M Y, h:i A') }}
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-right text-xs space-x-1.5">
                                    <a href="{{ route('admin.award-rolls.preview', $ar) }}" target="_blank"
                                        class="inline-flex items-center px-2.5 py-1.5 bg-slate-100 text-slate-700 hover:bg-slate-200 font-bold rounded-lg border border-slate-200 transition"
                                        title="View live PDF in browser">
                                        <svg class="w-3.5 h-3.5 me-1 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        View
                                    </a>
                                    <a href="{{ route('admin.award-rolls.download', $ar) }}"
                                        class="inline-flex items-center px-2.5 py-1.5 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 font-bold rounded-lg border border-indigo-100 transition"
                                        title="Download PDF to computer">
                                        <svg class="w-3.5 h-3.5 me-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                        PDF
                                    </a>
                                    <a href="{{ route('admin.award-rolls.download-excel', $ar) }}"
                                        class="inline-flex items-center px-2.5 py-1.5 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-bold rounded-lg border border-emerald-100 transition"
                                        title="Download Excel to computer">
                                        <svg class="w-3.5 h-3.5 me-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        Excel
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                    No Award Rolls generated yet. Use the form above to download your first report.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $awardRolls->links() }}
            </div>
        </div>
    </div>

    {{-- Download + Auto-refresh orchestration --}}
    <script>
    (function () {
        'use strict';

        var form        = document.getElementById('award-roll-form');
        var frame       = document.getElementById('download-frame');
        var statusEl    = document.getElementById('download-status');
        var statusTxt   = document.getElementById('download-status-text');
        var btnPdf      = document.getElementById('btn-pdf');
        var btnExcel    = document.getElementById('btn-excel');
        var directoryEl = document.getElementById('award-rolls-directory');

        if (!form || !frame || !statusEl || !directoryEl) { return; }

        function setStatus(visible, msg) {
            statusTxt.textContent   = msg || 'Please wait...';
            statusEl.style.display  = visible ? 'flex' : 'none';
        }

        function setButtons(disabled) {
            btnPdf.disabled   = disabled;
            btnExcel.disabled = disabled;
        }

        function refreshDirectory() {
            setStatus(true, 'Updating directory...');
            fetch(window.location.href, { credentials: 'same-origin' })
                .then(function (res) { return res.text(); })
                .then(function (html) {
                    var parser = new DOMParser();
                    var doc    = parser.parseFromString(html, 'text/html');
                    var fresh  = doc.getElementById('award-rolls-directory');
                    if (fresh && directoryEl) {
                        directoryEl.innerHTML = fresh.innerHTML;
                    }
                })
                .catch(function () { /* silently ignore */ })
                .finally(function () {
                    setStatus(false);
                    setButtons(false);
                });
        }

        // The iframe "load" fires after server finishes streaming the binary file.
        // Skip the very first blank load (iframe initialisation).
        frame.addEventListener('load', function () {
            if (!frame.dataset.hasLoaded) {
                frame.dataset.hasLoaded = '1';
                return;
            }
            setTimeout(refreshDirectory, 800);
        });

        form.addEventListener('submit', function () {
            var active = document.activeElement;
            var fmt    = (active && active.name === 'format') ? active.value : 'pdf';
            setStatus(true, fmt === 'excel' ? 'Generating Excel...' : 'Generating PDF...');
            setButtons(true);
            // Safety: re-enable after 30 s if iframe load never fires
            setTimeout(function () {
                setStatus(false);
                setButtons(false);
            }, 30000);
        });
    })();
    </script>
</x-app-layout>
