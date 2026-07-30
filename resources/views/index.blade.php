@extends('layouts.app', ['isIndex' => true, 'noMainPadding' => true])
@section('title', 'Teacher Roya\'s Stars — Learn English the Fun Way')

@push('styles')
<link href="{{ asset('css/home.css') }}?v={{ filemtime(public_path('css/home.css')) }}" rel="stylesheet">
@endpush

@section('content')
<div class="fb">
    <div class="fb-orb fb-orb--1"></div>
    <div class="fb-orb fb-orb--2"></div>
    <div class="fb-orb fb-orb--3"></div>
</div>

<!-- ════════════ HERO ════════════ -->
<section class="hero">
    <div class="hero__grid"></div>
    <div class="hero__bg-shape hero__bg-shape--1"></div>
    <div class="hero__bg-shape hero__bg-shape--2"></div>

    <div class="hero__particles">
        <div class="hero__particle hero__particle--1"></div>
        <div class="hero__particle hero__particle--2"></div>
        <div class="hero__particle hero__particle--3"></div>
        <div class="hero__particle hero__particle--4"></div>
        <div class="hero__particle hero__particle--5"></div>
        <div class="hero__particle hero__particle--6"></div>
    </div>

    <div class="hero__fs hero__fs--1"><span class="material-symbols-outlined">star</span></div>
    <div class="hero__fs hero__fs--2"><span class="material-symbols-outlined">star</span></div>
    <div class="hero__fs hero__fs--3"><span class="material-symbols-outlined">star</span></div>

    <div class="relative w-full max-w-6xl mx-auto z-10 px-4 lg:px-6">
        <div class="hero__inner flex flex-col lg:flex-row items-center gap-12 lg:gap-24">

            <!-- Left -->
            <div class="hero__text flex-1 max-w-xl">
                <div class="hero__tag flex items-center gap-2 mb-5">
                    <span class="section-tag section-tag--mb0">
                        <span class="material-symbols-outlined icon--sm">auto_awesome</span>
                        Online Learning Platform
                    </span>
                </div>

                <h1 class="hero__title">
                    Master English<br />
                    <span class="hl"><span class="hl-under">One Star</span></span>
                    at a Time
                </h1>

                <p class="hero__desc">
                    Interactive lessons, engaging quizzes, and a gamified star system — making English learning fun and effective for every student in Azerbaijan.
                </p>

                <div class="hero__stats">
                    <div>
                        <div class="hero__stat-value hero__stat-value--tertiary">
                            <span class="counter" data-target="500">0</span>+
                        </div>
                        <div class="hero__stat-label">Stars Earned</div>
                    </div>
                    <div>
                        <div class="hero__stat-value hero__stat-value--secondary">
                            <span class="counter" data-target="200">0</span>+
                        </div>
                        <div class="hero__stat-label">Active Learners</div>
                    </div>
                    <div>
                        <div class="hero__stat-value hero__stat-value--primary">
                            <span class="counter" data-target="100">0</span>+
                        </div>
                        <div class="hero__stat-label">Quizzes</div>
                    </div>
                </div>
            </div>

            <!-- Right -->
            <div class="hero__visual">
                <div class="hero__visual-ring"></div>
                <div class="hero__visual-ring hero__visual-ring--2"></div>

                <div class="hero__visual-star">
                    <span class="material-symbols-outlined">star</span>
                </div>

                <div class="hero__visual-inner">
                    <div class="hero__visual-bg">
                        <span class="hero__visual-text">RS</span>
                    </div>
                </div>

                <div class="hero__visual-rocket">
                    <span class="material-symbols-outlined">rocket_launch</span>
                </div>
            </div>
        </div>
    </div>

    <div class="hero__scroll">
        <span>Scroll</span>
        <span class="material-symbols-outlined">keyboard_arrow_down</span>
    </div>
</section>

<!-- ════════════ HOW IT WORKS ════════════ -->
<section class="landing-section fade-up">
    <div class="max-w-6xl mx-auto px-4 text-center">
        <span class="section-tag">Getting Started</span>
        <h2 class="section-title">Three Simple <span class="hl">Steps</span></h2>
        <p class="section-desc mx-auto">Begin your English journey in minutes. No complicated setup — just pure learning and fun.</p>
    </div>

    <div class="s-grid px-4 stagger">
        <div class="s-card">
            <div class="s-card__num">1</div>
            <div class="s-card__arrow"><span class="material-symbols-outlined">chevron_right</span></div>
            <div class="f-card__icon"><span class="material-symbols-outlined">person_add</span></div>
            <h3>Create Account</h3>
            <p>Sign up with your email or phone. It's completely free and only takes a moment.</p>
        </div>
        <div class="s-card">
            <div class="s-card__num">2</div>
            <div class="s-card__arrow"><span class="material-symbols-outlined">chevron_right</span></div>
            <div class="f-card__icon"><span class="material-symbols-outlined">rocket_launch</span></div>
            <h3>Pick a Topic</h3>
            <p>Choose from grammar, vocabulary, reading, and exam prep. Learn entirely at your own pace.</p>
        </div>
        <div class="s-card">
            <div class="s-card__num">3</div>
            <div class="f-card__icon"><span class="material-symbols-outlined">auto_awesome</span></div>
            <h3>Earn Stars</h3>
            <p>Complete lessons and quizzes to collect stars, track progress, and stay motivated.</p>
        </div>
    </div>
</section>

<!-- ════════════ FEATURES ════════════ -->
<section class="landing-section fade-up" id="features">
    <div class="max-w-6xl mx-auto px-4 text-center">
        <span class="section-tag">Features</span>
        <h2 class="section-title">Everything You Need to <span class="hl">Excel</span></h2>
        <p class="section-desc mx-auto">A comprehensive platform designed for Azerbaijani students mastering English.</p>
    </div>

    <div class="f-grid mt-12 md:mt-16 px-4 stagger">
        <div class="f-card">
            <div class="f-card__icon"><span class="material-symbols-outlined">auto_stories</span></div>
            <h3>Interactive Lessons</h3>
            <p>Video-based lessons with real-world examples and visual aids that make complex grammar simple and intuitive.</p>
        </div>
        <div class="f-card">
            <div class="f-card__icon"><span class="material-symbols-outlined">quiz</span></div>
            <h3>Smart Quizzes</h3>
            <p>Topic-based quizzes with instant feedback. Every wrong answer is a learning opportunity.</p>
        </div>
        <div class="f-card">
            <div class="f-card__icon"><span class="material-symbols-outlined">star</span></div>
            <h3>Gamified Rewards</h3>
            <p>Earn stars for lessons, perfect scores, and daily streaks. Achievements turn studying into a challenge.</p>
        </div>
        <div class="f-card">
            <div class="f-card__icon"><span class="material-symbols-outlined">flag</span></div>
            <h3>Exam Preparation</h3>
            <p>Grade 9 and Final exam practice with realistic mock tests. Build confidence before exam day.</p>
        </div>
        <div class="f-card">
            <div class="f-card__icon"><span class="material-symbols-outlined">translate</span></div>
            <h3>Bilingual Support</h3>
            <p>Content available in Azerbaijani, English, and Russian. Learn in the language you're most comfortable with.</p>
        </div>
        <div class="f-card">
            <div class="f-card__icon"><span class="material-symbols-outlined">trending_up</span></div>
            <h3>Progress Tracking</h3>
            <p>Dashboard with completed lessons, quiz scores, star collection — all your stats in one place.</p>
        </div>
    </div>
</section>

<!-- ════════════ TEACHER ════════════ -->
<section class="landing-section fade-up pt0" id="teacher">
    <div class="max-w-6xl mx-auto px-4 text-center">
        <span class="section-tag">Your Teacher</span>
        <h2 class="section-title">Meet <span class="hl">Teacher Roya</span></h2>
        <p class="section-desc mx-auto">Passionate educator dedicated to making English accessible for every Azerbaijani student.</p>
    </div>

    <div class="t-card mt-10 md:mt-14">
        <div class="t-card__avatar">R</div>
        <div>
            <blockquote>
                <span class="mat">"</span> I believe every student has the potential to shine. My goal is to create a learning environment where English feels less like a subject and more like an adventure — complete with stars to reward every step forward.
            </blockquote>
            <cite>
                Teacher Roya
                <span>— Founder &amp; Lead Instructor</span>
            </cite>
        </div>
    </div>
</section>

<!-- ════════════ CTA ════════════ -->
<section class="fade-up pb-section">
    <div class="cta">
        <div class="cta__decor cta__decor--1"><span class="material-symbols-outlined">star</span></div>
        <div class="cta__decor cta__decor--2"><span class="material-symbols-outlined">rocket_launch</span></div>

        <span class="section-tag section-tag--cta">Start Today</span>
        <h2 class="cta__title">Ready to Become a Star?</h2>
        <p class="cta__desc">Join hundreds of students already learning with Teacher Roya. Your English adventure starts here — for free.</p>
        <div class="cta__btns">
            <a href="{{ route('signup') }}" class="cta__btn cta__btn--solid">
                <span class="material-symbols-outlined icon--md">person_add</span>
                Create Free Account
            </a>
            <a href="{{ route('login') }}" class="cta__btn cta__btn--ghost">
                <span class="material-symbols-outlined icon--md">login</span>
                I Already Have an Account
            </a>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('js/home.js') }}?v={{ filemtime(public_path('js/home.js')) }}"></script>
@endpush
