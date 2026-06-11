@props(['name' => 'modal', 'title' => '', 'maxWidth' => 'max-w-lg', 'closeExpr' => 'open = false'])

{{-- The outer x-show wrapper in each page controls visibility.
     This component provides only the backdrop + panel visual structure. --}}
<div class="fixed inset-0 z-50 flex items-center justify-center p-4"
     @keydown.escape.window="{{ $closeExpr }}">

    {{-- Backdrop --}}
    <div class="absolute inset-0"
         style="background:rgba(0,0,0,.45);backdrop-filter:blur(4px);-webkit-backdrop-filter:blur(4px);"
         @click="{{ $closeExpr }}"></div>

    {{-- Panel --}}
    <div class="relative w-full {{ $maxWidth }} rounded-2xl shadow-2xl"
         style="background:var(--color-surface); border:1px solid var(--color-border);"
         @click.stop>

        {{-- Header --}}
        @if($title || isset($header))
        <div class="flex items-center justify-between px-5 py-4 border-b" style="border-color:var(--color-border);">
            <h3 class="text-sm font-semibold" style="color:var(--color-text);">
                {{ $title }}{{ $header ?? '' }}
            </h3>
            <button @click="{{ $closeExpr }}"
                    class="w-7 h-7 flex items-center justify-center rounded-lg transition-colors"
                    style="color:var(--color-text-muted);"
                    onmouseover="this.style.background='var(--color-bg)'"
                    onmouseout="this.style.background=''">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        @endif

        {{-- Content --}}
        <div class="px-5 py-5 max-h-[80vh] overflow-y-auto">
            {{ $slot }}
        </div>
    </div>
</div>
