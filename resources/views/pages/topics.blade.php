@extends('layouts.app')
@section('title', 'Topics')

@section('content')
<section class="universe-banner group hover:shadow-[rgb(var(--primary))/0.4]">
    <div class="bg-decor-star">
        <span class="material-symbols-outlined !text-4xl text-[rgb(var(--tertiary))]">star</span>
    </div>
    <div class="bg-decor-rocket">
        <span class="material-symbols-outlined !text-6xl text-[rgb(var(--primary))]">rocket_launch</span>
    </div>

    <div class="relative z-10">
        <h2 class="universe-title">Your Learning<br/>Universe</h2>
        <p class="universe-text">
            Embark on your journey through the grammar galaxies. Every star earned brings you closer to mastery!
        </p>
    </div>

    <div class="banner-icon-magic group-hover:rotate-45 group-hover:scale-110">
        <span class="material-symbols-outlined !text-[140px] md:!text-[180px]">auto_awesome</span>
    </div>
    <div class="banner-icon-rocket group-hover:translate-x-4 group-hover:-translate-y-4 transition-transform duration-1000">
        <span class="material-symbols-outlined !text-[180px] md:!text-[220px] text-white">rocket_launch</span>
    </div>
</section>

<div class="max-w-6xl mx-auto px-4">
    <form method="GET" action="{{ route('topics') }}" class="search-bar">
        <span class="material-symbols-outlined !text-lg">search</span>
        <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search topics..." class="search-bar__input" data-auto-search />
        @if($search)
        <a href="{{ route('topics') }}" class="search-bar__clear material-symbols-outlined !text-lg">close</a>
        @endif
    </form>

    <div class="modules-grid">
        @forelse($topics as $i => $topic)
        <x-card
            href="{{ route('topics.detail', $topic) }}"
            badgeText="Topic {{ str_pad($topics->firstItem() + $i, 2, '0', STR_PAD_LEFT) }}"
            title="{{ $topic->name }}"
            description="{{ $topic->subject?->name ?? '' }}"
            progress="0"
            iconName="{{ ['star', 'bolt', 'rocket_launch', 'auto_awesome', 'psychology', 'menu_book'][$i % 6] }}"
        />
        @empty
        <div class="col-span-full text-center py-16">
            <span class="material-symbols-outlined !text-6xl text-[rgb(var(--on-surface))/0.1] mb-4">rocket_launch</span>
            <p class="text-[rgb(var(--on-surface))/0.3] font-black uppercase tracking-widest text-xs">
                {{ $search ? 'No topics match your search' : 'No topics available yet' }}
            </p>
        </div>
        @endforelse
    </div>
</div>

{{ $topics->appends(['search' => $search])->links('vendor.pagination.custom') }}
@endsection

@push('scripts')
<script>
document.querySelectorAll('[data-auto-search]').forEach(function(input) {
    var timer;
    input.addEventListener('input', function() {
        clearTimeout(timer);
        timer = setTimeout(function() { input.closest('form').submit(); }, 350);
    });
});
</script>
@endpush
