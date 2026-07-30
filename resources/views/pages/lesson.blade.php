@extends('layouts.app')
@section('title', $lesson->name . ' - Lesson')

@push('styles')
<link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css" />
<link href="{{ asset('css/lesson.css') }}?v={{ filemtime(public_path('css/lesson.css')) }}" rel="stylesheet">
@endpush

@section('content')
<div class="w-full max-w-[1400px] mx-auto px-4 md:px-8 py-6 space-y-8">

    <!-- Celestial Hero Cover -->
    <section class="lesson-hero-cover group">
        <div class="absolute -top-12 -right-12 text-white/10 pointer-events-none transition-transform duration-1000 group-hover:rotate-45">
            <span class="material-symbols-outlined !text-[280px]">play_circle</span>
        </div>
        <div class="absolute -bottom-10 -left-10 text-white/10 pointer-events-none">
            <span class="material-symbols-outlined !text-[240px]">auto_awesome</span>
        </div>

        <div class="relative z-10 space-y-4 max-w-2xl">
            <!-- Navigation Back Link -->
            @if($lesson->topic)
            <a href="{{ route('topics.detail', $lesson->topic) }}" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/20 backdrop-blur-md text-3xs font-black uppercase tracking-widest text-white hover:bg-white/30 transition-all no-underline">
                <span class="material-symbols-outlined !text-sm">arrow_back</span>
                <span>Back to {{ $lesson->topic->name }}</span>
            </a>
            @endif

            <!-- Breadcrumbs -->
            <div class="flex items-center gap-2 text-3xs font-black uppercase tracking-widest text-white/80">
                <span>{{ $lesson->topic?->name ?? 'Lesson' }}</span>
                <span class="material-symbols-outlined !text-xs">chevron_right</span>
                <span class="text-amber-300 font-extrabold">{{ $lesson->name }}</span>
            </div>

            <!-- Title & Description -->
            <div class="space-y-1.5">
                <h1 class="text-3xl sm:text-5xl font-black italic uppercase tracking-tight text-white leading-tight">
                    {{ $lesson->name }}
                </h1>
                @if($lesson->description)
                <p class="text-xs sm:text-sm font-semibold text-white/80">
                    {{ $lesson->description }}
                </p>
                @endif
            </div>

            <!-- Meta Pills -->
            <div class="flex items-center gap-3 pt-2 flex-wrap">
                @if($lesson->videos->isNotEmpty())
                <span class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full bg-white/20 backdrop-blur-md text-xs font-black text-white">
                    <span class="material-symbols-outlined !text-sm">video_library</span>
                    {{ $lesson->videos->count() }} {{ Str::plural('Video', $lesson->videos->count()) }}
                </span>
                @endif
                @if($lesson->quiz)
                <span class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full bg-amber-400/30 text-amber-200 text-xs font-black">
                    <span class="material-symbols-outlined !text-sm" style="font-variation-settings:'FILL' 1">stars</span>
                    {{ $lesson->quiz->questions->count() }} Quiz Questions
                </span>
                @endif
            </div>
        </div>
    </section>

    <!-- Spacious 2-Column Dashboard Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

        <!-- MAIN COLUMN (8 cols): Videos & Feedback -->
        <main class="lg:col-span-8 space-y-8">

            <!-- Custom Lesson Video Players Section -->
            @if($lesson->videos->isNotEmpty())
            <section class="space-y-4">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined !text-2xl text-[rgb(var(--primary))]">smart_display</span>
                    <h3 class="text-base sm:text-lg font-black uppercase tracking-tight text-[rgb(var(--on-surface))]">
                        Interactive Lesson Player
                    </h3>
                </div>

                <div class="grid grid-cols-1 gap-6">
                    @foreach($lesson->videos as $video)
                    <div class="custom-player-container p-4 space-y-3">
                        <!-- Custom Top Header Bar -->
                        <div class="flex items-center justify-between px-1.5 pt-1">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                <h4 class="font-black text-sm text-[rgb(var(--on-surface))] truncate">
                                    {{ $video->name ?: 'Lesson Video' }}
                                </h4>
                            </div>
                            <span class="inline-flex items-center gap-1 text-4xs font-black uppercase tracking-widest text-[rgb(var(--primary))] bg-[rgb(var(--primary))/0.1] px-2.5 py-0.5 rounded-full">
                                HD 1080p
                            </span>
                        </div>

                        <!-- Custom Plyr Wrapper -->
                        <div class="plyr__video-embed js-plyr-player">
                            <iframe
                                src="https://www.youtube-nocookie.com/embed/{{ $video->youtube_id }}?origin={{ urlencode(url('/')) }}&amp;iv_load_policy=3&amp;modestbranding=1&amp;playsinline=1&amp;showinfo=0&amp;rel=0&amp;enablejsapi=1"
                                allowfullscreen
                                allowtransparency
                                allow="autoplay"
                            ></iframe>
                        </div>
                    </div>
                    @endforeach
                </div>
            </section>
            @endif

            <!-- Empty State if no videos or quiz -->
            @if($lesson->videos->isEmpty() && !$lesson->quiz)
            <section class="bg-[rgb(var(--surface-container-lowest))] border-2 border-dashed border-[rgb(var(--surface-container-high))] rounded-3xl p-12 text-center space-y-3">
                <span class="material-symbols-outlined !text-6xl text-[rgb(var(--on-surface))/0.15]">menu_book</span>
                <h4 class="font-black text-base text-[rgb(var(--on-surface))] uppercase">No Media Content Available</h4>
                <p class="text-xs font-semibold text-[rgb(var(--on-surface))/0.5] max-w-sm mx-auto">
                    Content is being prepared for this lesson. Check back soon!
                </p>
            </section>
            @endif

            <!-- Feedback & Rating Section -->
            <section class="space-y-4">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined !text-2xl text-[rgb(var(--tertiary))]">feedback</span>
                    <h3 class="text-base sm:text-lg font-black uppercase tracking-tight text-[rgb(var(--on-surface))]">
                        Rate This Lesson
                    </h3>
                </div>

                @if($existingReview)
                <!-- Already rated — read-only display -->
                <div class="sidebar-widget-card space-y-4 text-center">
                    <span class="material-symbols-outlined !text-4xl text-emerald-500" style="font-variation-settings:'FILL' 1">check_circle</span>
                    <p class="text-sm font-bold text-[rgb(var(--on-surface))/0.7]">Siz artıq bu dərsə rəy vermisiniz</p>
                    <div class="flex justify-center items-center gap-2">
                        @for($i = 1; $i <= 5; $i++)
                        <span class="lesson-star-btn active" style="cursor: default; color: {{ ($existingReview->rating ?? 0) >= $i ? 'rgb(var(--tertiary))' : 'rgba(var(--on-surface), 0.15)' }}">
                            <span class="material-symbols-outlined !text-4xl sm:!text-5xl" style="{{ ($existingReview->rating ?? 0) >= $i ? 'font-variation-settings:\'FILL\' 1;' : '' }}">star</span>
                        </span>
                        @endfor
                    </div>
                    @if($existingReview->review)
                    <p class="text-sm italic text-[rgb(var(--on-surface))/0.6] max-w-md mx-auto">"{{ $existingReview->review }}"</p>
                    @endif
                </div>
                @else
                <!-- Rating form — JS submit -->
                <form id="rateForm" class="sidebar-widget-card space-y-6" data-rate-url="{{ route('lesson.rate', $lesson->id) }}">
                    @csrf
                    <input name="rating" type="hidden" id="ratingInput" value="" />

                    <div class="text-center space-y-2">
                        <p class="text-xs font-bold text-[rgb(var(--on-surface))/0.6] uppercase tracking-widest">
                            How was your learning experience?
                        </p>
                        <div class="flex justify-center items-center gap-2">
                            @for($i = 1; $i <= 5; $i++)
                            <button type="button" class="lesson-star-btn" data-index="{{ $i }}">
                                <span class="material-symbols-outlined !text-4xl sm:!text-5xl">star</span>
                            </button>
                            @endfor
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-3xs font-black uppercase tracking-widest text-[rgb(var(--on-surface))/0.6]">Your Feedback (Optional)</label>
                        <textarea name="review" id="reviewInput" rows="3" class="w-full bg-[rgb(var(--surface-container-high))] border-2 border-[rgb(var(--surface-container-high))] focus:border-[rgb(var(--primary))] text-[rgb(var(--on-surface))] text-xs sm:text-sm font-semibold rounded-2xl p-4 focus:outline-none transition-all placeholder-[rgb(var(--on-surface))/0.4]" placeholder="Share your thoughts about this lesson..."></textarea>
                    </div>

                    <button id="rateSubmitBtn" type="submit" class="w-full py-3.5 bg-[rgb(var(--primary))] text-white rounded-full font-black text-xs sm:text-sm uppercase tracking-widest flex items-center justify-center gap-2 active:scale-95 transition-all shadow-lg shadow-[rgb(var(--primary))/0.2]">
                        <span>Submit Feedback</span>
                        <span class="material-symbols-outlined !text-lg">rocket_launch</span>
                    </button>
                </form>
                @endif

                <!-- Rocket animation container -->
                <div id="rocketContainer" aria-hidden="true" class="fixed inset-0 pointer-events-none z-[999]" style="display: none;">
                    <div id="rocket" class="absolute" style="filter: drop-shadow(0 0 60px rgba(220,38,38,0.8));">🚀</div>
                    <div id="spark1" class="absolute w-3 h-3 rounded-full bg-amber-400"></div>
                    <div id="spark2" class="absolute w-2.5 h-2.5 rounded-full bg-amber-500"></div>
                    <div id="spark3" class="absolute w-3.5 h-3.5 rounded-full bg-amber-300"></div>
                    <div id="spark4" class="absolute w-2 h-2 rounded-full bg-red-400"></div>
                    <div id="spark5" class="absolute w-2.5 h-2.5 rounded-full bg-red-500"></div>
                </div>
            </section>

        </main>

        <!-- SIDEBAR COLUMN (4 cols): Quiz CTA & Navigation -->
        <aside class="lg:col-span-4 space-y-6 lg:sticky lg:top-24">

            <!-- Knowledge Check Quiz Card -->
            @if($lesson->quiz)
            <div class="sidebar-widget-card space-y-4">
                <div class="flex items-center gap-2 text-[rgb(var(--secondary))]">
                    <span class="material-symbols-outlined !text-2xl">quiz</span>
                    <h4 class="font-black text-sm uppercase tracking-wide">Knowledge Check</h4>
                </div>

                <div class="lesson-quiz-banner space-y-3">
                    <div class="w-12 h-12 rounded-2xl bg-[rgb(var(--secondary))] text-white flex items-center justify-center shadow-lg shadow-[rgb(var(--secondary))/0.3]">
                        <span class="material-symbols-outlined !text-2xl">psychology</span>
                    </div>
                    <div>
                        <h4 class="font-black text-base text-[rgb(var(--on-surface))] uppercase tracking-tight">
                            {{ $lesson->quiz->name }}
                        </h4>
                        <div class="flex items-center gap-3 mt-1 text-xs font-bold text-[rgb(var(--on-surface))/0.6]">
                            <span class="flex items-center gap-1">
                                <span class="material-symbols-outlined !text-sm text-[rgb(var(--secondary))]">help</span>
                                {{ $lesson->quiz->questions->count() }} Questions
                            </span>
                            <span class="flex items-center gap-1 text-amber-600 font-black">
                                <span class="material-symbols-outlined !text-sm" style="font-variation-settings:'FILL' 1">star</span>
                                Earn XP
                            </span>
                        </div>
                    </div>

                    <a href="{{ route('quiz', $lesson->quiz->id) }}" class="w-full py-3 bg-[rgb(var(--secondary))] text-white rounded-full font-black text-xs uppercase tracking-widest flex items-center justify-center gap-2 no-underline active:scale-95 transition-all shadow-md shadow-[rgb(var(--secondary))/0.2]">
                        <span>Start Quiz</span>
                        <span class="material-symbols-outlined !text-lg">arrow_forward</span>
                    </a>
                </div>
            </div>
            @endif

            <!-- Return to Parent Topic Card -->
            @if($lesson->topic)
            <a href="{{ route('topics.detail', $lesson->topic) }}" class="sidebar-widget-card flex items-center justify-between gap-3 text-[rgb(var(--on-surface))] hover:border-[rgb(var(--primary))/0.3] no-underline transition-all group">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined !text-2xl text-[rgb(var(--primary))] group-hover:-translate-x-1 transition-transform">arrow_back</span>
                    <div>
                        <h4 class="font-black text-xs uppercase tracking-wide">Back to {{ $lesson->topic->name }}</h4>
                        <p class="text-3xs font-bold text-[rgb(var(--on-surface))/0.5]">See all lessons in this topic</p>
                    </div>
                </div>
                <span class="material-symbols-outlined !text-lg text-[rgb(var(--on-surface))/0.4]">chevron_right</span>
            </a>
            @endif

        </aside>

    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.plyr.io/3.7.8/plyr.js"></script>
<script src="{{ asset('js/lesson.js') }}?v={{ filemtime(public_path('js/lesson.js')) }}"></script>
@endpush
