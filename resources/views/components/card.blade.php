@props([
    'variant' => 'white',
    'href' => '',
    'badgeText' => '',
    'title' => '',
    'description' => '',
    'progress' => '',
    'iconName' => 'star',
    'compact' => false,
])

@php
    $tag = $href ? 'a' : 'div';
    $variantClasses = match($variant) {
        'red' => 'bg-[rgb(var(--surface-container-lowest))] text-[rgb(var(--on-surface))] border-[rgb(var(--surface-container-high))] shadow-sm hover:bg-[rgb(var(--secondary))] hover:text-white hover:border-white/10 hover:shadow-xl hover:shadow-[rgb(var(--secondary))/0.15] hover:brightness-105',
        'gray' => 'bg-[rgb(var(--surface-container-high))] border-[rgb(var(--surface-container-high))] grayscale opacity-60 pointer-events-none',
        default => 'bg-[rgb(var(--surface-container-lowest))] text-[rgb(var(--on-surface))] border-[rgb(var(--surface-container-high))] shadow-sm hover:shadow-lg hover:border-[rgb(var(--primary))/0.2]',
    };
    $badgeClasses = $variant === 'red'
        ? 'bg-[rgb(var(--surface))] border-[rgb(var(--surface-container-high))] text-[rgb(var(--on-surface-variant))] group-hover:bg-white/20 group-hover:border-white/10 group-hover:text-white'
        : 'bg-[rgb(var(--surface))] border-[rgb(var(--surface-container-high))] text-[rgb(var(--on-surface-variant))]';
    $descClasses = $variant === 'red'
        ? 'text-[rgb(var(--on-surface))/0.5] group-hover:text-white/80'
        : 'text-[rgb(var(--on-surface))/0.5]';
    $actionBtnClasses = $variant === 'red'
        ? 'bg-white text-[rgb(var(--secondary))]'
        : 'bg-[rgb(var(--secondary))] text-white';
    $actionText = text('card.details');
    // Compact sizing classes
    $cardSizing = $compact
        ? 'p-5 md:p-6 rounded-2xl md:rounded-3xl min-h-[180px] md:min-h-[200px]'
        : 'p-6 md:p-10 rounded-3xl md:rounded-4xl min-h-[240px] md:min-h-[260px]';
    $sectionSpacing = $compact ? 'space-y-2 md:space-y-2.5' : 'space-y-3 md:space-y-4';
    $badgeSize = $compact ? 'px-3 py-1 text-4xs' : 'px-4 py-1.5 text-3xs';
    $titleSize = $compact ? 'text-sm md:text-base' : 'text-lg md:text-2xl';
    $descSize = $compact ? 'text-3xs md:text-2xs' : 'text-2xs md:text-xs';
    $footerSpacing = $compact ? 'pt-3 md:pt-4 space-y-2 md:space-y-2.5' : 'pt-4 md:pt-6 space-y-3 md:space-y-4';
    $progressSpacing = $compact ? 'space-y-1' : 'space-y-1.5 md:space-y-2';
    $progressLabelSize = $compact ? 'text-4xs' : 'text-3xs';
    $progressBarSize = $compact ? 'h-2' : 'h-3';
    $btnSize = $compact ? 'py-2 md:py-2.5 text-3xs' : 'py-3 md:py-3.5 text-2xs';
@endphp

<{{ $tag }} @if($href) href="{{ $href }}" @endif {{ $attributes->merge(['class' => "relative group {$cardSizing} flex flex-col justify-between transition-all duration-300 active:scale-[0.98] border-2 block overflow-hidden no-underline {$variantClasses}"]) }}>
    <div class="absolute -top-3 -right-3 opacity-10 group-hover:scale-110 group-hover:rotate-12 transition-transform duration-700 pointer-events-none select-none">
        <span class="material-symbols-outlined !text-[70px] md:!text-[100px]">{{ $iconName }}</span>
    </div>

    <div class="relative z-10 {{ $sectionSpacing }}">
        @if($badgeText)
        <span class="inline-block {{ $badgeSize }} font-black uppercase tracking-widest border italic shadow-sm rounded-full {{ $badgeClasses }}">
            {{ $badgeText }}
        </span>
        @endif
        <div>
            <h3 class="{{ $titleSize }} font-black italic tracking-tight leading-tight uppercase mb-1">{{ $title }}</h3>
            <p class="{{ $descSize }} font-medium leading-relaxed italic pr-8 {{ $descClasses }}">{{ $description }}</p>
        </div>
    </div>

    <div class="mt-auto {{ $footerSpacing }} relative z-10">
        @if($progress)
        <div class="{{ $progressSpacing }}">
            <div class="flex justify-between items-end {{ $progressLabelSize }} font-black uppercase tracking-widest opacity-80 italic">
                <span>{{ $variant === 'red' ? text('card.mission_progress') : text('card.ready_status') }}</span>
                <span>{{ $progress }}%</span>
            </div>
            <div class="{{ $progressBarSize }} w-full rounded-full overflow-hidden shadow-inner p-0.5 border {{ $variant === 'red' ? 'bg-white/20 border-white/5' : 'bg-[rgb(var(--surface))] border-[rgb(var(--surface-container-high))]' }}">
                <div class="h-full rounded-full bg-[rgb(var(--tertiary))] shadow-lg shadow-[rgb(var(--tertiary))/0.3]" style="width: {{ $progress }}%"></div>
            </div>
        </div>
        @endif

        <div class="w-full {{ $btnSize }} rounded-full font-black text-center uppercase tracking-widest shadow-lg italic {{ $actionBtnClasses }}">
            {{ $actionText }}
        </div>
    </div>
</{{ $tag }}>
