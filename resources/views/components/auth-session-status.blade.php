@props(['status'])

@if ($status)
    @php
        $isInactivity = str_contains(strtolower($status), 'inactivity');
    @endphp
    <div {{ $attributes->merge(['class' => 'p-3.5 rounded-xl text-xs sm:text-sm font-semibold flex items-center gap-3 ' . ($isInactivity ? 'bg-amber-50 text-amber-900 border border-amber-200/80 shadow-xs' : 'bg-emerald-50 text-emerald-800 border border-emerald-200/80 shadow-xs')]) }}>
        @if ($isInactivity)
            <div class="w-8 h-8 rounded-lg bg-amber-100 border border-amber-300/60 flex items-center justify-center text-amber-700 shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        @else
            <div class="w-8 h-8 rounded-lg bg-emerald-100 border border-emerald-300/60 flex items-center justify-center text-emerald-700 shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        @endif
        <div class="flex-1 leading-snug">
            {{ $status }}
        </div>
    </div>
@endif
