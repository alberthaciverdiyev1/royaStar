@extends('layouts.app')
@section('title', $lesson->name . ' - Lesson')

@section('content')
<section class="lesson-cover">
    <div class="lesson-cover__deco">
        <span class="material-symbols-outlined !text-8xl md:!text-[200px] opacity-[0.05]">auto_awesome</span>
    </div>
    <div class="lesson-cover__deco lesson-cover__deco--right">
        <span class="material-symbols-outlined !text-[120px] md:!text-[240px] opacity-[0.03]">rocket_launch</span>
    </div>

    <a href="{{ route('topics.detail', $lesson->topic) }}" class="lesson-cover__back">
        <span class="material-symbols-outlined !text-lg">arrow_back</span>
        <span>Back</span>
    </a>

    <div class="lesson-cover__content">
        <div class="lesson-cover__breadcrumb">
            <span>{{ $lesson->topic?->name ?? 'Lesson' }}</span>
            <span class="material-symbols-outlined !text-sm">chevron_right</span>
            <span class="text-white/40">{{ $lesson->name }}</span>
        </div>
        <h1 class="lesson-cover__title">{{ $lesson->name }}</h1>
        @if($lesson->description)
        <p class="lesson-cover__desc">{{ $lesson->description }}</p>
        @endif
        <div class="lesson-cover__meta">
            <span class="lesson-cover__meta-item">
                <span class="material-symbols-outlined !text-base">play_circle</span>
                {{ $lesson->videos->count() }} {{ Str::plural('Video', $lesson->videos->count()) }}
            </span>
            @if($lesson->quiz)
            <span class="lesson-cover__meta-item">
                <span class="material-symbols-outlined !text-base">quiz</span>
                {{ $lesson->quiz->questions->count() }} Questions
            </span>
            @endif
        </div>
    </div>
</section>

<div class="lesson-body">
    {{-- Videos --}}
    @if($lesson->videos->isNotEmpty())
    <section class="lesson-section">
        <div class="lesson-section__header">
            <span class="material-symbols-outlined !text-2xl text-[rgb(var(--primary))]">video_library</span>
            <div>
                <h2 class="lesson-section__title">Lesson Videos</h2>
                <p class="lesson-section__desc">Watch and learn at your own pace</p>
            </div>
        </div>
        <div class="lesson-video-grid">
            @foreach($lesson->videos as $video)
            <div class="lesson-video-card">
                <div class="lesson-video-card__player">
                    @if($video->youtube_url)
                    <iframe src="{{ $video->embed_url }}" class="lesson-video-card__iframe" allowfullscreen loading="lazy"></iframe>
                    @endif
                </div>
                @if($video->name)
                <div class="lesson-video-card__footer">
                    <span class="material-symbols-outlined !text-base text-[rgb(var(--primary))]">play_circle</span>
                    <span class="lesson-video-card__title">{{ $video->name }}</span>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </section>
    @endif

    {{-- Quiz --}}
    @if($lesson->quiz)
    <section class="lesson-section">
        <div class="lesson-section__header">
            <span class="material-symbols-outlined !text-2xl text-[rgb(var(--secondary))]">quiz</span>
            <div>
                <h2 class="lesson-section__title">Knowledge Check</h2>
                <p class="lesson-section__desc">Test what you've learned in this lesson</p>
            </div>
        </div>
        <a href="{{ route('quiz', $lesson->quiz) }}" class="lesson-quiz-card">
            <div class="lesson-quiz-card__icon">
                <span class="material-symbols-outlined !text-3xl">quiz</span>
            </div>
            <div class="lesson-quiz-card__body">
                <h3 class="lesson-quiz-card__title">{{ $lesson->quiz->name }}</h3>
                <div class="lesson-quiz-card__stats">
                    <span class="lesson-quiz-card__stat">
                        <span class="material-symbols-outlined !text-sm">help</span>
                        {{ $lesson->quiz->questions->count() }} questions
                    </span>
                    <span class="lesson-quiz-card__stat">
                        <span class="material-symbols-outlined !text-sm" style="font-variation-settings:'FILL' 1">star</span>
                        Earn stars
                    </span>
                </div>
            </div>
            <div class="lesson-quiz-card__action">
                <span class="material-symbols-outlined !text-2xl">arrow_forward</span>
            </div>
        </a>
    </section>
    @endif

    {{-- Empty state --}}
    @if($lesson->videos->isEmpty() && !$lesson->quiz)
    <section class="lesson-section">
        <div class="text-center py-20">
            <span class="material-symbols-outlined !text-7xl text-[rgb(var(--on-surface))/0.06] mb-6">menu_book</span>
            <p class="text-[rgb(var(--on-surface))/0.25] font-black uppercase tracking-widest text-sm">No content available yet</p>
        </div>
    </section>
    @endif

    {{-- Rating --}}
    <section class="lesson-section">
        <div class="lesson-section__header">
            <span class="material-symbols-outlined !text-2xl text-[rgb(var(--tertiary))]">feedback</span>
            <div>
                <h2 class="lesson-section__title">Rate This Lesson</h2>
                <p class="lesson-section__desc">How was your learning experience today?</p>
            </div>
        </div>

        <form method="POST" action="{{ route('lesson.rate', $lesson) }}" class="lesson-rate-card">
            @csrf
            <input name="rating" type="hidden" id="ratingInput" />

            <div class="lesson-rate-card__stars">
                @for($i = 1; $i <= 5; $i++)
                <button type="button" class="lesson-star" data-index="{{ $i }}">
                    <span class="material-symbols-outlined !text-5xl md:!text-6xl">star</span>
                </button>
                @endfor
            </div>

            <div class="lesson-rate-card__field">
                <label class="lesson-rate-card__label">Share your thoughts</label>
                <textarea name="review" rows="4" class="lesson-rate-card__textarea" placeholder="What did you think of this lesson? Your feedback helps Teacher Roya improve..."></textarea>
            </div>

            <button type="submit" class="lesson-rate-card__submit">
                <span>Submit Feedback</span>
                <span class="material-symbols-outlined !text-xl">rocket_launch</span>
            </button>
        </form>
    </section>
</div>
@endsection

@push('scripts')
<script>
(function() {
    var stars = document.querySelectorAll('.lesson-star');
    var input = document.getElementById('ratingInput');

    function resetStars(selected) {
        stars.forEach(function(s, i) {
            var icon = s.querySelector('.material-symbols-outlined');
            if (i < selected) {
                icon.style.fontVariationSettings = "'FILL' 1";
                s.classList.add('lesson-star--active');
                s.classList.remove('lesson-star--inactive');
            } else {
                icon.style.fontVariationSettings = "'FILL' 0";
                s.classList.add('lesson-star--inactive');
                s.classList.remove('lesson-star--active');
            }
        });
    }

    stars.forEach(function(star, idx) {
        star.addEventListener('click', function() {
            var val = idx + 1;
            if (input) input.value = val;
            resetStars(val);
        });
        star.addEventListener('mouseenter', function() {
            stars.forEach(function(s, i) {
                var icon = s.querySelector('.material-symbols-outlined');
                icon.style.fontVariationSettings = i <= idx ? "'FILL' 1" : "'FILL' 0";
            });
        });
        star.addEventListener('mouseleave', function() {
            var selected = parseInt(input?.value || 0);
            stars.forEach(function(s, i) {
                var icon = s.querySelector('.material-symbols-outlined');
                if (i >= selected) icon.style.fontVariationSettings = "'FILL' 0";
            });
        });
    });
})();
</script>
@endpush
