@extends('layouts.app')
@section('title', $lesson->name . ' - Lesson')

@push('styles')
<link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css" />
<style>
/* Celestial Hero Cover */
.lesson-hero-cover {
    position: relative;
    border-radius: 2.5rem;
    padding: 3rem 2rem;
    background: radial-gradient(circle at 80% 20%, rgba(255, 255, 255, 0.15) 0%, transparent 60%),
                linear-gradient(135deg, rgb(var(--primary)) 0%, rgb(var(--secondary)) 100%);
    color: #ffffff;
    box-shadow: 0 25px 50px -12px rgba(var(--primary), 0.35);
    overflow: hidden;
    margin-bottom: 2rem;
}
@media (min-width: 768px) {
    .lesson-hero-cover {
        border-radius: 3.5rem;
        padding: 4rem 3.5rem;
    }
}

/* Custom Plyr Video Player Styling */
.custom-player-container {
    border-radius: 1.75rem;
    overflow: hidden;
    border: 2px solid rgba(var(--surface-container-high), 1);
    background: rgba(var(--surface-container-lowest), 1);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}
.custom-player-container:hover {
    border-color: rgba(var(--primary), 0.35);
    box-shadow: 0 15px 40px rgba(var(--primary), 0.12);
}

.plyr {
    --plyr-color-main: rgb(var(--primary));
    --plyr-video-control-color: #ffffff;
    --plyr-video-control-color-hover: #ffffff;
    --plyr-video-control-background-hover: rgb(var(--primary));
    --plyr-badge-border-radius: 9999px;
    border-radius: 1.5rem;
    overflow: hidden;
}

.sidebar-widget-card {
    background-color: rgba(var(--surface-container-lowest), 1);
    border: 2px solid rgba(var(--surface-container-high), 1);
    border-radius: 1.75rem;
    padding: 1.5rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
}

/* Quiz Card Banner */
.lesson-quiz-banner {
    background: linear-gradient(135deg, rgba(var(--secondary), 0.08) 0%, rgba(var(--surface-container-lowest), 1) 100%);
    border: 2px solid rgba(var(--secondary), 0.3);
    border-radius: 1.75rem;
    padding: 1.5rem;
    transition: all 0.3s cubic-bezier(0.22, 1, 0.36, 1);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.03);
}
.lesson-quiz-banner:hover {
    transform: translateY(-4px);
    border-color: rgb(var(--secondary));
    box-shadow: 0 16px 40px rgba(var(--secondary), 0.15);
}

/* Rating Stars */
.lesson-star-btn {
    color: rgba(var(--on-surface), 0.2);
    transition: transform 0.2s cubic-bezier(0.22, 1, 0.36, 1), color 0.2s ease;
    cursor: pointer;
    background: transparent;
    border: none;
    padding: 0.25rem;
}
.lesson-star-btn:hover {
    transform: scale(1.25) rotate(6deg);
    color: rgb(var(--tertiary));
}
.lesson-star-btn.active {
    color: rgb(var(--tertiary));
}
</style>
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
                <form id="rateForm" class="sidebar-widget-card space-y-6">
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
<script>
document.addEventListener('DOMContentLoaded', function() {
    Plyr.setup('.js-plyr-player', {
        controls: ['play-large', 'play', 'progress', 'current-time', 'mute', 'volume', 'fullscreen'],
        youtube: {
            noCookie: true,
            rel: 0,
            showinfo: 0,
            iv_load_policy: 3,
            modestbranding: 1
        }
    });

    // ── Rating star handler ──
    var stars = document.querySelectorAll('.lesson-star-btn');
    var input = document.getElementById('ratingInput');
    if (stars.length && input) {
        function resetStars(selected) {
            stars.forEach(function(s, i) {
                var icon = s.querySelector('.material-symbols-outlined');
                if (i < selected) {
                    icon.style.fontVariationSettings = "'FILL' 1";
                    s.classList.add('active');
                } else {
                    icon.style.fontVariationSettings = "'FILL' 0";
                    s.classList.remove('active');
                }
            });
        }

        stars.forEach(function(star, idx) {
            star.addEventListener('click', function() {
                var val = idx + 1;
                input.value = val;
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
                    if (i >= selected) {
                        icon.style.fontVariationSettings = "'FILL' 0";
                    } else {
                        icon.style.fontVariationSettings = "'FILL' 1";
                    }
                });
            });
        });
    }

    // ── JS form submit + rocket animation ──
    var form = document.getElementById('rateForm');
    if (form) {
        var submitBtn = document.getElementById('rateSubmitBtn');
        var reviewInput = document.getElementById('reviewInput');

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            var rating = input.value;
            if (!rating) {
                var p = form.querySelector('.lesson-star-btn');
                if (p) {
                    p.style.animation = 'shake 0.4s ease';
                    setTimeout(function() { p.style.animation = ''; }, 500);
                }
                return;
            }

            // Disable button
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="material-symbols-outlined !text-lg animate-spin">progress_activity</span>';

            // Get CSRF token
            var csrfToken = form.querySelector('input[name="_token"]').value;

            fetch('{{ route('lesson.rate', $lesson->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    rating: parseInt(rating),
                    review: reviewInput ? reviewInput.value : '',
                }),
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    launchRocket();
                    // Replace form with success message
                    var card = form;
                    card.innerHTML =
                        '<div class="text-center space-y-3 py-4">' +
                            '<span class="material-symbols-outlined !text-5xl text-emerald-500" style="font-variation-settings:\'FILL\' 1">check_circle</span>' +
                            '<p class="text-lg font-black text-[rgb(var(--on-surface))]">Thank You!</p>' +
                            '<p class="text-xs font-semibold text-[rgb(var(--on-surface))/0.6]">Your feedback has been saved successfully.</p>' +
                            '<div class="flex justify-center items-center gap-1 pt-2">' +
                                Array.from({length: parseInt(rating)}, function() {
                                    return '<span class="material-symbols-outlined !text-3xl text-[rgb(var(--tertiary))]" style="font-variation-settings:\'FILL\' 1">star</span>';
                                }).join('') +
                            '</div>' +
                        '</div>';
                } else {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<span>Submit Feedback</span><span class="material-symbols-outlined !text-lg">rocket_launch</span>';
                    alert(data.message || 'Something went wrong.');
                }
            })
            .catch(function() {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<span>Submit Feedback</span><span class="material-symbols-outlined !text-lg">rocket_launch</span>';
                alert('Something went wrong. Please try again.');
            });
        });
    }

    // ── Rocket animation: huge red emoji from button, slow fly upward ──
    function launchRocket() {
        var container = document.getElementById('rocketContainer');
        var rocket = document.getElementById('rocket');
        var sparks = [
            document.getElementById('spark1'),
            document.getElementById('spark2'),
            document.getElementById('spark3'),
            document.getElementById('spark4'),
            document.getElementById('spark5'),
        ];

        var btnRect = submitBtn.getBoundingClientRect();
        var startX = btnRect.left + btnRect.width / 2;
        var startY = btnRect.top;

        var ROCKET_SIZE = 120;
        var DURATION = 4000;

        container.style.display = 'block';

        // Place rocket at button position
        rocket.style.transition = 'none';
        rocket.style.left = (startX - ROCKET_SIZE / 2) + 'px';
        rocket.style.top = startY + 'px';
        rocket.style.fontSize = ROCKET_SIZE + 'px';
        rocket.style.lineHeight = '1';
        rocket.style.opacity = '1';
        rocket.style.transform = 'scale(1)';

        // Place sparks at button position
        sparks.forEach(function(s, i) {
            s.style.transition = 'none';
            s.style.position = 'absolute';
            var spread = (i - 2) * 20;
            s.style.left = (startX + spread) + 'px';
            s.style.top = (startY + ROCKET_SIZE / 2 + 10) + 'px';
            s.style.opacity = '1';
        });

        // Slow rocket fly upward
        requestAnimationFrame(function() {
            rocket.style.transition = 'all ' + DURATION + 'ms cubic-bezier(0.15, 0.85, 0.35, 1)';
            rocket.style.top = '-200px';
            rocket.style.opacity = '0';
            rocket.style.transform = 'scale(1.2)';

            sparks.forEach(function(s, i) {
                s.style.transition = 'all ' + DURATION + 'ms cubic-bezier(0.15, 0.85, 0.35, 1)';
                s.style.top = '-250px';
                s.style.opacity = '0';
                s.style.left = (startX + (i - 2) * 20 + (Math.random() - 0.5) * 80) + 'px';
            });
        });

        // Slow trail particles — bigger, slower, more of them
        var trailInterval = setInterval(function() {
            for (var t = 0; t < 2; t++) {
                (function() {
                    var trail = document.createElement('div');
                    trail.className = 'absolute rounded-full';
                    var size = 6 + Math.random() * 10;
                    trail.style.width = size + 'px';
                    trail.style.height = size + 'px';
                    trail.style.background = ['#f59e0b', '#ef4444', '#f97316', '#dc2626'][Math.floor(Math.random() * 4)];
                    trail.style.left = (startX + (Math.random() - 0.5) * 40) + 'px';
                    trail.style.top = (startY + ROCKET_SIZE / 2 + Math.random() * 20) + 'px';
                    trail.style.opacity = '0.9';
                    trail.style.transition = 'all 1.8s ease-out';
                    trail.style.position = 'absolute';
                    trail.style.borderRadius = '50%';
                    container.appendChild(trail);
                    requestAnimationFrame(function() {
                        trail.style.top = (parseInt(trail.style.top) - 60 - Math.random() * 80) + 'px';
                        trail.style.opacity = '0';
                        trail.style.transform = 'translateX(' + (Math.random() - 0.5) * 50 + 'px)';
                    });
                    setTimeout(function() { trail.remove(); }, 2000);
                })();
            }
        }, 200);

        setTimeout(function() {
            clearInterval(trailInterval);
            container.style.display = 'none';
        }, DURATION + 500);
    }
});
</script>

<style>
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    20% { transform: translateX(-6px); }
    40% { transform: translateX(6px); }
    60% { transform: translateX(-4px); }
    80% { transform: translateX(4px); }
}
</style>
@endpush
