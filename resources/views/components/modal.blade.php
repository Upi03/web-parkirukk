@props(['name', 'title' => '', 'maxWidth' => '2xl'])

@php
$maxWidthClass = match ($maxWidth) {
    'sm' => 'sm:max-w-sm',
    'md' => 'sm:max-w-md',
    'lg' => 'sm:max-w-lg',
    'xl' => 'sm:max-w-xl',
    '2xl' => 'sm:max-w-2xl',
    default => 'sm:max-w-2xl',
};
@endphp

<div
    x-data="{ show: false, name: '{{ $name }}' }"
    x-show="show"
    x-init="$watch('show', val => { 
        if (val) { 
            setTimeout(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); }, 50); 
        } 
    })"
    @open-modal.window="if ($event.detail === name) { show = true }"
    @close-modal.window="if ($event.detail === name) { show = false }"
    @keydown.escape.window="show = false"
    style="display: none;"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 overflow-y-auto"
    x-cloak
>
    <!-- Background Backdrop (Hanya di belakang dialog modal) -->
    <div
        x-show="show"
        class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs transition-opacity"
        @click="show = false"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        aria-hidden="true"
    ></div>

    <!-- Modal Dialog Panel (Tampil tajam di atas backdrop dengan z-10) -->
    <div
        x-show="show"
        @click.stop
        class="relative z-10 bg-white dark:bg-slate-900 rounded-xl overflow-hidden shadow-2xl w-full {{ $maxWidthClass }} border border-slate-200 dark:border-slate-800 my-8 max-h-[90vh] flex flex-col"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
    >
        @if($title)
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center shrink-0 bg-slate-50/50 dark:bg-slate-800/40">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ $title }}</h3>
            <button type="button" @click="show = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        @endif
        
        <div class="p-6 overflow-y-auto">
            {{ $slot }}
        </div>
    </div>
</div>
