@extends('layouts.app', ['hideHeader' => true, 'hideNavbar' => true])
@section('title', $lesson['name'] ?? 'Lesson')

@section('content')
<section class="max-w-content mx-auto px-4 py-8 md:py-12">
    <!-- Header -->
    <header class="mb-8">
        <a href="{{ route('topics') }}" class="inline-flex items-center gap-2 text-3xs font-black uppercase tracking-widest text-[rgb(var(--on-surface-variant))] hover:text-[rgb(var(--primary))] transition-all mb-6 no-underline">
            <span class="material-symbols-outlined !text-sm">arrow_back</span>
            Back to Topics
        </a>
        <div class="flex flex-wrap items-center gap-3 mb-4">
            <span class="text-3xs font-black uppercase tracking-widest inline-flex items-center gap-2 px-4 py-1.5 rounded-full border bg-[rgb(var(--primary))/0.05] text-[rgb(var(--primary))] border-[rgb(var(--primary))/0.1]">
                {{ $lesson['topic_name'] ?? 'Grammar' }}
            </span>
            <div class="flex items-center gap-0.5">
                @for($s = 0; $s < ($lesson['star'] ?? 3); $s++)
                <span class="material-symbols-outlined !text-lg text-[rgb(var(--tertiary))]" style="font-variation-settings:'FILL' 1">star</span>
                @endfor
            </div>
        </div>
        <h1 class="text-3xl md:text-4xl font-black text-[rgb(var(--on-surface))] uppercase tracking-tight">{{ $lesson['name'] }}</h1>
        @if(!empty($lesson['description']))
        <p class="text-sm font-bold text-[rgb(var(--on-surface-variant))] mt-3 max-w-2xl leading-relaxed">{{ $lesson['description'] }}</p>
        @endif
    </header>

    <!-- Videos -->
    @if(count($videos ?? []) > 0)
    <div class="mb-10">
        <h2 class="text-lg font-black uppercase tracking-tight text-[rgb(var(--on-surface))] mb-6 flex items-center gap-2">
            <span class="material-symbols-outlined !text-2xl text-[rgb(var(--primary))]">video_library</span>
            Videos
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($videos as $video)
            <div class="bg-[rgb(var(--surface-container-lowest))] rounded-3xl border border-[rgb(var(--surface-container-high))] overflow-hidden shadow-lg shadow-black/5">
                <div class="aspect-video">
                    @if(!empty($video['youtube_url']))
                    <iframe src="{{ str_replace('watch?v=', 'embed/', $video['youtube_url']) }}" class="w-full h-full" allowfullscreen loading="lazy"></iframe>
                    @elseif(!empty($video['video_url']))
                    <video controls class="w-full aspect-video bg-black">
                        <source src="{{ $video['video_url'] }}" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Quizzes -->
    @if(count($quizzes ?? []) > 0)
    <div class="mb-10">
        <h2 class="text-lg font-black uppercase tracking-tight text-[rgb(var(--on-surface))] mb-6 flex items-center gap-2">
            <span class="material-symbols-outlined !text-2xl text-[rgb(var(--secondary))]">quiz</span>
            Quizzes
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($quizzes as $quiz)
            <a href="{{ route('quiz', ['id' => $quiz['id']]) }}" class="block bg-[rgb(var(--surface-container-lowest))] rounded-3xl border border-[rgb(var(--surface-container-high))] p-6 hover:shadow-xl hover:border-[rgb(var(--primary))/0.3] transition-all no-underline group">
                <div class="flex items-start justify-between mb-4">
                    <div class="w-12 h-12 rounded-2xl bg-[rgb(var(--secondary))/0.1] flex items-center justify-center">
                        <span class="material-symbols-outlined !text-2xl text-[rgb(var(--secondary))]">quiz</span>
                    </div>
                    <div class="flex items-center gap-0.5">
                        @for($s = 0; $s < ($quiz['star'] ?? 0); $s++)
                        <span class="material-symbols-outlined !text-sm text-[rgb(var(--tertiary))]" style="font-variation-settings:'FILL' 1">star</span>
                        @endfor
                    </div>
                </div>
                <h3 class="font-black text-sm uppercase text-[rgb(var(--on-surface))] group-hover:text-[rgb(var(--primary))] transition-colors">{{ $quiz['name'] }}</h3>
                <p class="text-3xs font-bold text-[rgb(var(--on-surface-variant))] mt-2 flex items-center gap-1">
                    <span class="material-symbols-outlined !text-sm">help</span>
                    {{ $quiz['questions_count'] ?? 0 }} questions
                </p>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    @if(count($videos ?? []) == 0 && count($quizzes ?? []) == 0)
    <div class="text-center py-20">
        <span class="material-symbols-outlined !text-6xl text-[rgb(var(--on-surface))/0.1] mb-4">menu_book</span>
        <p class="text-sm font-bold text-[rgb(var(--on-surface))/0.3] uppercase tracking-widest">No content available yet</p>
    </div>
    @endif

    <!-- Rating -->
    <div class="rounded-3xl md:rounded-4xl p-6 md:p-10 bg-[rgb(var(--surface-container-lowest))] border border-[rgb(var(--surface-container-high))] shadow-xl shadow-[rgb(var(--primary))/0.05]">
        <div class="text-center space-y-2 mb-8">
            <h4 class="text-xl font-black uppercase tracking-tight text-[rgb(var(--on-surface))]">Rate this Lesson</h4>
            <p class="text-3xs font-black uppercase tracking-widest text-[rgb(var(--on-surface))/0.6]">How was your learning journey today?</p>
        </div>
        <form method="POST" action="{{ route('lesson.rate', ['id' => $lesson['id'] ?? 0]) }}" class="space-y-6">
            @csrf
            <input name="rating" type="hidden" id="ratingInput" />
            <div class="flex justify-center gap-2 md:gap-4">
                @for($i = 1; $i <= 5; $i++)
                <button type="button" class="rating-star transition-all hover:scale-125 active:scale-75 text-[rgb(var(--on-surface))/0.2] min-w-touch min-h-touch flex items-center justify-center" data-index="{{ $i }}">
                    <span class="material-symbols-outlined !text-[44px]">star</span>
                </button>
                @endfor
            </div>
            <div>
                <label class="text-xs font-black uppercase tracking-widest px-2 text-[rgb(var(--primary))] block mb-3">Any thoughts for Teacher Roya?</label>
                <textarea name="review" rows="4" class="w-full rounded-2xl p-5 bg-[rgb(var(--surface))] text-[rgb(var(--on-surface))] font-bold text-sm outline-none placeholder:text-[rgb(var(--on-surface))/0.3] transition-all border border-[rgb(var(--surface-container-high))] focus:border-[rgb(var(--primary))/0.5]" placeholder="Share your feedback..."></textarea>
            </div>
            <button type="submit" class="w-full py-4 bg-[rgb(var(--secondary))] text-white rounded-full font-black text-sm active:scale-95 transition-all flex items-center justify-center gap-3 uppercase tracking-widest shadow-lg shadow-[rgb(var(--secondary))/0.2] hover:bg-[rgb(var(--secondary))/0.9]">
                Submit Review
                <span class="material-symbols-outlined !text-xl">rocket_launch</span>
            </button>
        </form>
    </div>
</section>
@endsection

@push('scripts')
<script>
    (function() {
        var stars = document.querySelectorAll('.rating-star');
        var input = document.getElementById('ratingInput');

        stars.forEach(function(star, idx) {
            star.addEventListener('click', function() {
                stars.forEach(function(s, i) {
                    var icon = s.querySelector('.material-symbols-outlined');
                    if (i <= idx) {
                        icon.style.fontVariationSettings = "'FILL' 1";
                        s.classList.remove('text-\\[rgb\\(var\\(--on-surface\\)\\)\\]/20');
                        s.classList.add('text-\\[rgb\\(var\\(--tertiary\\)\\)\\]');
                    } else {
                        icon.style.fontVariationSettings = "'FILL' 0";
                        s.classList.remove('text-\\[rgb\\(var\\(--tertiary\\)\\)\\]');
                        s.classList.add('text-\\[rgb\\(var\\(--on-surface\\)\\)\\]/20');
                    }
                });
                if (input) input.value = idx + 1;
            });

            star.addEventListener('mouseenter', function() {
                stars.forEach(function(s, i) {
                    var icon = s.querySelector('.material-symbols-outlined');
                    if (i <= idx) icon.style.fontVariationSettings = "'FILL' 1";
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
