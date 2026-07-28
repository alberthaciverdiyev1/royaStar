@extends('layouts.app')
@section('title', $topic->name . ' - Lessons')

@section('content')
<section class="path-hero">
    <div class="path-hero__deco">
        <span class="material-symbols-outlined !text-7xl md:!text-9xl opacity-[0.06]">auto_awesome</span>
    </div>
    <div class="path-hero__deco path-hero__deco--right">
        <span class="material-symbols-outlined !text-8xl md:!text-[200px] opacity-[0.04]">rocket_launch</span>
    </div>

    <div class="path-hero__content">
        <div class="path-hero__subject">{{ $topic->subject?->name ?? 'Learning Path' }}</div>
        <h1 class="path-hero__title">{{ $topic->name }}</h1>
        <p class="path-hero__desc">{{ $lessons->total() }} lessons — Start your journey below</p>
    </div>
</section>

<section class="path-container">
    <form method="GET" class="path-search">
        <span class="material-symbols-outlined !text-lg">search</span>
        <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search lessons..." class="path-search__input" data-auto-search />
        @if($search)
        <a href="{{ route('topics.detail', $topic) }}" class="path-search__clear material-symbols-outlined !text-lg">close</a>
        @endif
    </form>

    <div class="path-timeline">
        @forelse($lessons as $i => $lesson)
        <a href="{{ route('lesson', $lesson) }}" class="path-step {{ $i === 0 && !$search ? 'path-step--active' : '' }}">
            <div class="path-step__marker">
                <div class="path-step__dot">{{ ($lessons->firstItem() + $i) }}</div>
                @if(!$loop->last)
                <div class="path-step__line"></div>
                @endif
            </div>

            <div class="path-step__card">
                <div class="path-step__meta">
                    <span class="path-step__badge">Lesson {{ str_pad($lessons->firstItem() + $i, 2, '0', STR_PAD_LEFT) }}</span>
                    <span class="path-step__icon material-symbols-outlined !text-lg">arrow_forward</span>
                </div>
                <h3 class="path-step__title">{{ $lesson->name }}</h3>
                @if($lesson->description)
                <p class="path-step__desc">{{ $lesson->description }}</p>
                @endif
                <div class="path-step__action">
                    <span>{{ $i === 0 && !$search ? 'Start Learning' : 'Begin Lesson' }}</span>
                    <span class="material-symbols-outlined !text-base">play_circle</span>
                </div>
            </div>
        </a>
        @empty
        <div class="col-span-full text-center py-24">
            <span class="material-symbols-outlined !text-7xl text-[rgb(var(--on-surface))/0.06] mb-6">menu_book</span>
            <p class="text-[rgb(var(--on-surface))/0.25] font-black uppercase tracking-widest text-sm">
                {{ $search ? 'No lessons match your search' : 'No lessons available yet' }}
            </p>
        </div>
        @endforelse
    </div>

    {{ $lessons->appends(['search' => $search])->links('vendor.pagination.custom') }}
</section>
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
