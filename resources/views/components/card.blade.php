<div {{ $attributes->merge(['class' => 'rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm text-slate-950 dark:text-slate-50']) }}>
    @if(isset($header))
        <div class="flex flex-col space-y-1.5 p-6 pb-3">
            {{ $header }}
        </div>
    @endif
    <div class="p-6 pt-0">
        {{ $slot }}
    </div>
</div>
