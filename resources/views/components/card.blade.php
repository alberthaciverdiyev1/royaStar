@props([
    'variant' => 'white',
    'href' => '',
    'badgeText' => '',
    'title' => '',
    'description' => '',
    'progress' => '',
    'iconName' => 'star',
])

@php
    $tag = $href ? 'a' : 'div';
    $hrefAttr = $href ? "href=\"{$href}\"" : '';
    $variantClasses = match($variant) {
        'red' => 'bg-[rgb(var(--secondary))] text-white shadow-xl shadow-[rgb(var(--secondary))/0.15] border-white/10 hover:brightness-105',
        'gray' => 'bg-[rgb(var(--surface-container-high))] border-[rgb(var(--surface-container-high))] grayscale opacity-60 pointer-events-none',
        default => 'bg-[rgb(var(--surface-container-lowest))] text-[rgb(var(--on-surface))] border-[rgb(var(--surface-container-high))] shadow-sm hover:shadow-lg hover:border-[rgb(var(--primary))/0.2]',
    };
    $badgeClasses = $variant === 'red'
        ? 'bg-white/20 border-white/10 text-white'
        : 'bg-[rgb(var(--surface))] border-[rgb(var(--surface-container-high))] text-[rgb(var(--on-surface-variant))]';
    $descClasses = $variant === 'red' ? 'text-white/80' : 'text-[rgb(var(--on-surface))/0.5]';
    $actionBtnClasses = $variant === 'red'
        ? 'bg-white text-[rgb(var(--secondary))]'
        : 'bg-[rgb(var(--secondary))] text-white';
    $actionText = match($variant) {
        'red' => 'Review Mission',
        'gray' => 'Locked',
        default => 'Continue Learning',
    };
@endphp

<{{ $tag }} {{ $hrefAttr }} {{ $attributes->merge(['class' => "relative group p-6 md:p-10 rounded-3xl md:rounded-4xl flex flex-col justify-between min-h-[240px] md:min-h-[260px] transition-all duration-300 active:scale-[0.98] border-2 block overflow-hidden no-underline {$variantClasses}"]) }}>
    <div class="absolute -top-3 -right-3 opacity-10 group-hover:scale-110 group-hover:rotate-12 transition-transform duration-700 pointer-events-none select-none">
        <span class="material-symbols-outlined !text-[70px] md:!text-[100px]">{{ $iconName }}</span>
    </div>

    <div class="relative z-10 space-y-3 md:space-y-4">
        @if($badgeText)
        <span class="inline-block px-4 py-1.5 rounded-full text-3xs font-black uppercase tracking-widest border italic shadow-sm {{ $badgeClasses }}">
            {{ $badgeText }}
        </span>
        @endif
        <div>
            <h3 class="text-lg md:text-2xl font-black italic tracking-tight leading-tight uppercase mb-1">{{ $title }}</h3>
            <p class="text-2xs md:text-xs font-medium leading-relaxed italic pr-8 {{ $descClasses }}">{{ $description }}</p>
        </div>
    </div>

    <div class="mt-auto pt-4 md:pt-6 space-y-3 md:space-y-4 relative z-10">
        @if($progress)
        <div class="space-y-1.5 md:space-y-2">
            <div class="flex justify-between items-end text-3xs font-black uppercase tracking-widest opacity-80 italic">
                <span>{{ $variant === 'red' ? 'Mission Progress' : 'Ready Status' }}</span>
                <span>{{ $progress }}%</span>
            </div>
            <div class="h-3 w-full rounded-full overflow-hidden shadow-inner p-0.5 border {{ $variant === 'red' ? 'bg-white/20 border-white/5' : 'bg-[rgb(var(--surface))] border-[rgb(var(--surface-container-high))]' }}">
                <div class="h-full rounded-full bg-[rgb(var(--tertiary))] shadow-lg shadow-[rgb(var(--tertiary))/0.3]" style="width: {{ $progress }}%"></div>
            </div>
        </div>
        @endif

        <div class="w-full py-3 md:py-3.5 rounded-full font-black text-center text-2xs uppercase tracking-widest shadow-lg italic {{ $actionBtnClasses }}">
            {{ $actionText }}
        </div>
    </div>
</{{ $tag }}>
