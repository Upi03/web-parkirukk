@props(['title', 'value', 'icon', 'trend' => null, 'percentage' => null])

<div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm flex flex-col justify-between hover:shadow-md transition-shadow">
    <div class="flex flex-row items-center justify-between space-y-0 pb-2">
        <h3 class="tracking-tight text-sm font-medium text-slate-500 dark:text-slate-400">
            {{ $title }}
        </h3>
        <i data-lucide="{{ $icon }}" class="h-4 w-4 text-slate-400"></i>
    </div>
    <div>
        <div class="text-2xl font-bold text-slate-900 dark:text-white">
            {{ $value }}
        </div>
        @if($percentage)
            <p class="text-xs mt-1 {{ $trend === 'naik' ? 'text-green-600 dark:text-green-400' : ($trend === 'turun' ? 'text-red-600 dark:text-red-400' : 'text-slate-500') }}">
                {{ $percentage }}
            </p>
        @endif
    </div>
</div>
