{{-- 
    Date shortcut buttons component.
    Usage: <x-date-shortcuts :on-this-month="'setThisMonth'" :on-last-month="'setLastMonth'" :on-reset="'resetDates'" />
    The parent Alpine component must implement the functions passed.
    Alternatively include inline JS calls.
--}}
<div class="flex flex-wrap gap-1.5 mt-2">
    <button type="button" @click="{{ $onThisMonth ?? 'setThisMonth()' }}"
            class="filter-pill text-xs px-3 py-1">
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
        </svg>
        Bulan Ini
    </button>
    <button type="button" @click="{{ $onLastMonth ?? 'setLastMonth()' }}"
            class="filter-pill text-xs px-3 py-1">
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 19.5L8.25 12l7.5-7.5"/>
        </svg>
        Bulan Lalu
    </button>
    <button type="button" @click="{{ $onReset ?? 'resetDates()' }}"
            class="filter-pill text-xs px-3 py-1">
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
        </svg>
        Reset
    </button>
</div>
