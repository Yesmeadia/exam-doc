<x-app-layout>
    <x-slot name="title">{{ __('Import Preview & Validation Summary') }}</x-slot>

    <div class="w-full space-y-6">
        <!-- Top Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200">
            <div class="space-y-0.5">
                <p class="text-sm text-slate-500">
                    Academic Year: <span class="font-bold text-indigo-600">{{ $academicYear->name }}</span>
                </p>
                <p class="text-sm text-slate-500">
                    Importing into: <span class="font-bold text-slate-900">{{ $class->name }} &mdash; Section {{ $section->name }}</span>
                </p>
            </div>
            <a href="{{ route('admin.students.import.form') }}" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-300 rounded-lg font-semibold text-xs text-slate-700 uppercase tracking-wider shadow-sm hover:bg-slate-50 transition w-fit">
                &larr; Upload Another File
            </a>
        </div>

        <x-alert />

        <!-- Summary KPI Badges (Full Width) -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 w-full">
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Rows Evaluated</p>
                    <p class="text-2xl font-black text-slate-900 mt-1">{{ $preview['total_rows'] }}</p>
                </div>
                <span class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center font-bold text-sm">
                    {{ $preview['total_rows'] }}
                </span>
            </div>

            <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-emerald-600">Valid Rows (Ready to Import)</p>
                    <p class="text-2xl font-black text-emerald-700 mt-1">{{ $preview['valid_count'] }}</p>
                </div>
                <span class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-100 flex items-center justify-center font-bold text-sm">
                    ✓
                </span>
            </div>

            <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-rose-600">Invalid Rows (Errors Detected)</p>
                    <p class="text-2xl font-black text-rose-700 mt-1">{{ $preview['invalid_count'] }}</p>
                </div>
                <span class="w-10 h-10 rounded-xl bg-rose-50 text-rose-700 border border-rose-100 flex items-center justify-center font-bold text-sm">
                    !
                </span>
            </div>
        </div>

        <!-- Error Report if Invalid Rows Exist -->
        @if($preview['invalid_count'] > 0)
            <div class="bg-rose-50/80 border border-rose-200 rounded-2xl p-6 w-full">
                <h3 class="font-bold text-rose-900 text-base mb-1">Validation Errors Found in Spreadsheet ({{ $preview['invalid_count'] }} rows)</h3>
                <p class="text-xs text-rose-700 mb-4">The following rows contain errors and cannot be imported. Only valid rows will be imported if you proceed.</p>

                <div class="overflow-x-auto bg-white rounded-xl border border-rose-200 w-full">
                    <table class="w-full divide-y divide-rose-100 text-xs">
                        <thead class="bg-rose-100/50 font-bold text-rose-900">
                            <tr>
                                <th class="px-3.5 py-2.5 text-left">Row #</th>
                                <th class="px-3.5 py-2.5 text-left">Student ID</th>
                                <th class="px-3.5 py-2.5 text-left">Name</th>
                                <th class="px-3.5 py-2.5 text-left">Class & Section</th>
                                <th class="px-3.5 py-2.5 text-left">Roll No</th>
                                <th class="px-3.5 py-2.5 text-left text-rose-700">Specific Error Reason(s)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-rose-50">
                            @foreach($preview['invalid_rows'] as $invalid)
                                <tr>
                                    <td class="px-3.5 py-2 font-bold text-slate-700">Row {{ $invalid['row']['row_number'] }}</td>
                                    <td class="px-3.5 py-2 font-mono">{{ $invalid['row']['student_id'] ?: 'EMPTY' }}</td>
                                    <td class="px-3.5 py-2 font-semibold text-slate-900">{{ $invalid['row']['name'] ?: 'EMPTY' }}</td>
                                    <td class="px-3.5 py-2 text-slate-600">{{ $invalid['row']['class_name'] }} / {{ $invalid['row']['section_name'] }}</td>
                                    <td class="px-3.5 py-2 text-slate-900">{{ $invalid['row']['roll_no'] ?: 'EMPTY' }}</td>
                                    <td class="px-3.5 py-2">
                                        <ul class="list-disc list-inside text-rose-600 font-semibold space-y-0.5">
                                            @foreach($invalid['errors'] as $err)
                                                <li>{{ $err }}</li>
                                            @endforeach
                                        </ul>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Valid Rows Preview Table -->
        @if($preview['valid_count'] > 0)
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden w-full">
                <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h3 class="font-bold text-slate-900 text-base">Valid Rows Ready for Import ({{ $preview['valid_count'] }})</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Review the verified records below before committing them into the student roster.</p>
                    </div>

                    <form method="POST" action="{{ route('admin.students.import.commit') }}">
                        @csrf
                        <input type="hidden" name="import_token" value="{{ $importToken }}">
                        <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-sm transition">
                            <svg class="w-4 h-4 me-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Import {{ $preview['valid_count'] }} Valid Record(s)
                        </button>
                    </form>
                </div>

                <div class="overflow-x-auto max-h-96 w-full">
                    <table class="w-full divide-y divide-slate-200 text-xs">
                        <thead class="bg-slate-50 sticky top-0 font-bold text-slate-700">
                            <tr>
                                <th class="px-4 py-2.5 text-center">Row</th>
                                <th class="px-4 py-2.5 text-center">Roll No</th>
                                <th class="px-4 py-2.5 text-left">Student ID</th>
                                <th class="px-4 py-2.5 text-left">Student Name</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach($preview['valid_rows'] as $valid)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-4 py-2 text-center text-slate-400">{{ $valid['row_number'] }}</td>
                                    <td class="px-4 py-2 text-center font-black text-slate-900">{{ $valid['roll_no'] }}</td>
                                    <td class="px-4 py-2 font-mono font-bold text-indigo-700">{{ $valid['student_id'] }}</td>
                                    <td class="px-4 py-2 font-bold text-slate-900">{{ $valid['name'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 text-center w-full">
                <p class="text-sm font-semibold text-amber-800">No valid records were found in this spreadsheet. Please correct the errors and re-upload.</p>
                <a href="{{ route('admin.students.import.form') }}" class="mt-3 inline-block text-xs font-bold text-amber-900 underline">Upload Corrected File</a>
            </div>
        @endif
    </div>
</x-app-layout>
