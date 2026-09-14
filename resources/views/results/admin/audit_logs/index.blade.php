<x-app-layout>
    <x-slot name="title">{{ __('System Audit Trail Logs') }}</x-slot>

    <div class="w-full space-y-6">
        <!-- Top Action & Search Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200">
            <div>
                <p class="text-sm text-slate-500">Immutable record of security events, mark revisions, password resets, and publishing actions.</p>
            </div>

            <!-- Search Form -->
            <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="flex items-center gap-2">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search action, IP..." class="text-xs rounded-xl border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 py-2">
                <button type="submit" class="px-4 py-2 bg-slate-800 text-white text-xs font-bold rounded-xl hover:bg-slate-900 transition shadow-xs">Search</button>
            </form>
        </div>

        <x-alert />

        <!-- Full-Width Responsive Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden w-full">
            <div class="overflow-x-auto w-full">
                <table class="w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50/80">
                        <tr>
                            <th class="px-5 py-3.5 text-left font-bold text-slate-700 text-xs uppercase tracking-wider">Timestamp</th>
                            <th class="px-5 py-3.5 text-left font-bold text-slate-700 text-xs uppercase tracking-wider">User</th>
                            <th class="px-5 py-3.5 text-left font-bold text-slate-700 text-xs uppercase tracking-wider">Action</th>
                            <th class="px-5 py-3.5 text-left font-bold text-slate-700 text-xs uppercase tracking-wider">IP Address</th>
                            <th class="px-5 py-3.5 text-left font-bold text-slate-700 text-xs uppercase tracking-wider">Details</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white font-mono text-xs">
                        @forelse($logs as $log)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="px-5 py-4 whitespace-nowrap text-slate-500 font-sans text-xs">
                                    {{ $log->created_at->timezone('Asia/Kolkata')->format('d M Y, h:i:s A') }}
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap font-sans font-bold text-slate-900">
                                    {{ $log->user?->name ?? 'System' }}
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap font-sans">
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-800 border border-slate-200">
                                        {{ $log->action }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-slate-500">
                                    {{ $log->ip_address ?? 'CLI / Local' }}
                                </td>
                                <td class="px-5 py-4 text-slate-600 max-w-md truncate" title="{{ json_encode($log->details) }}">
                                    {{ json_encode($log->details) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-slate-400 font-sans">No audit records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
