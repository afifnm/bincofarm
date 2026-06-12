@props(['id', 'title' => '', 'maxWidth' => 'max-w-lg'])

<div id="{{ $id }}"
     class="hidden fixed inset-0 z-[9999] flex items-center justify-center p-4"
     style="background:rgba(0,0,0,0.5);">

    {{-- Backdrop --}}
    <div class="absolute inset-0" onclick="closeModal('{{ $id }}')"></div>

    {{-- Panel --}}
    <div class="relative w-full {{ $maxWidth }} rounded-2xl shadow-2xl max-h-[90vh] overflow-y-auto"
         style="background:var(--color-surface);border:1px solid var(--color-border);"
         onclick="event.stopPropagation()">

        @if($title)
        <div class="flex items-center justify-between px-5 py-4 border-b" style="border-color:var(--color-border);">
            <h3 class="text-sm font-semibold" style="color:var(--color-text);">{{ $title }}</h3>
            <button type="button" onclick="closeModal('{{ $id }}')"
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

        <div class="px-5 py-5">
            {{ $slot }}
        </div>
    </div>
</div>
