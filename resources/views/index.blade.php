@extends('layouts.app', ['isIndex' => true, 'noMainPadding' => true])
@section('title', text('home.page_title'))

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
                        {{ text('home.hero.tag') }}
                    </span>
                </div>

                <h1 class="hero__title">
                    {{ text('home.hero.title_1') }}<br />
                    <span class="hl"><span class="hl-under">{{ text('home.hero.title_hl') }}</span></span>
                    {{ text('home.hero.title_2') }}
                </h1>

                <p class="hero__desc">
                    {{ text('home.hero.desc') }}
                </p>

                <div class="hero__stats">
                    <div>
                        <div class="hero__stat-value hero__stat-value--tertiary">
                            <span class="counter" data-target="500">0</span>+
                        </div>
                        <div class="hero__stat-label">{{ text('home.hero.stat_1') }}</div>
                    </div>
                    <div>
                        <div class="hero__stat-value hero__stat-value--secondary">
                            <span class="counter" data-target="200">0</span>+
                        </div>
                        <div class="hero__stat-label">{{ text('home.hero.stat_2') }}</div>
                    </div>
                    <div>
                        <div class="hero__stat-value hero__stat-value--primary">
                            <span class="counter" data-target="100">0</span>+
                        </div>
                        <div class="hero__stat-label">{{ text('home.hero.stat_3') }}</div>
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
                        <img src="{{ asset('images/logo.jpeg') }}" alt="Roya's Stars Logo" class="hero__visual-logo">
                    </div>
                </div>

                <div class="hero__visual-rocket">
                    <span class="material-symbols-outlined">rocket_launch</span>
                </div>
            </div>
        </div>
    </div>

    <div class="hero__scroll">
        <span>{{ text('home.hero.scroll') }}</span>
        <span class="material-symbols-outlined">keyboard_arrow_down</span>
    </div>
</section>

<!-- ════════════ HOW IT WORKS ════════════ -->
<section class="landing-section fade-up">
    <div class="max-w-6xl mx-auto px-4 text-center">
        <span class="section-tag">{{ text('home.start.tag') }}</span>
        <h2 class="section-title">{{ text('home.start.title_1') }} <span class="hl">{{ text('home.start.title_hl') }}</span></h2>
        <p class="section-desc mx-auto">{{ text('home.start.desc') }}</p>
    </div>

    <div class="s-grid px-4 stagger">
        <div class="s-card">
            <div class="s-card__num">1</div>
            <div class="s-card__arrow"><span class="material-symbols-outlined">chevron_right</span></div>
            <div class="f-card__icon"><span class="material-symbols-outlined">person_add</span></div>
            <h3>{{ text('home.step_1_title') }}</h3>
            <p>{{ text('home.step_1_desc') }}</p>
        </div>
        <div class="s-card">
            <div class="s-card__num">2</div>
            <div class="s-card__arrow"><span class="material-symbols-outlined">chevron_right</span></div>
            <div class="f-card__icon"><span class="material-symbols-outlined">rocket_launch</span></div>
            <h3>{{ text('home.step_2_title') }}</h3>
            <p>{{ text('home.step_2_desc') }}</p>
        </div>
        <div class="s-card">
            <div class="s-card__num">3</div>
            <div class="f-card__icon"><span class="material-symbols-outlined">auto_awesome</span></div>
            <h3>{{ text('home.step_3_title') }}</h3>
            <p>{{ text('home.step_3_desc') }}</p>
        </div>
    </div>
</section>

<!-- ════════════ FEATURES ════════════ -->
<section class="landing-section fade-up" id="features">
    <div class="max-w-6xl mx-auto px-4 text-center">
        <span class="section-tag">{{ text('home.features.tag') }}</span>
        <h2 class="section-title">{{ text('home.features.title_1') }} <span class="hl">{{ text('home.features.title_hl') }}</span></h2>
        <p class="section-desc mx-auto">{{ text('home.features.desc') }}</p>
    </div>

    <div class="f-grid mt-12 md:mt-16 px-4 stagger">
        <div class="f-card">
            <div class="f-card__icon"><span class="material-symbols-outlined">auto_stories</span></div>
            <h3>{{ text('home.feature_1_title') }}</h3>
            <p>{{ text('home.feature_1_desc') }}</p>
        </div>
        <div class="f-card">
            <div class="f-card__icon"><span class="material-symbols-outlined">quiz</span></div>
            <h3>{{ text('home.feature_2_title') }}</h3>
            <p>{{ text('home.feature_2_desc') }}</p>
        </div>
        <div class="f-card">
            <div class="f-card__icon"><span class="material-symbols-outlined">star</span></div>
            <h3>{{ text('home.feature_3_title') }}</h3>
            <p>{{ text('home.feature_3_desc') }}</p>
        </div>
        <div class="f-card">
            <div class="f-card__icon"><span class="material-symbols-outlined">flag</span></div>
            <h3>{{ text('home.feature_4_title') }}</h3>
            <p>{{ text('home.feature_4_desc') }}</p>
        </div>
        <div class="f-card">
            <div class="f-card__icon"><span class="material-symbols-outlined">translate</span></div>
            <h3>{{ text('home.feature_5_title') }}</h3>
            <p>{{ text('home.feature_5_desc') }}</p>
        </div>
        <div class="f-card">
            <div class="f-card__icon"><span class="material-symbols-outlined">trending_up</span></div>
            <h3>{{ text('home.feature_6_title') }}</h3>
            <p>{{ text('home.feature_6_desc') }}</p>
        </div>
    </div>
</section>

<!-- ════════════ TEACHER ════════════ -->
<section class="landing-section fade-up pt0" id="teacher">
    <div class="max-w-6xl mx-auto px-4 text-center">
        <span class="section-tag">{{ text('home.teacher.tag') }}</span>
        <h2 class="section-title">{{ text('home.teacher.title_1') }} <span class="hl">{{ text('home.teacher.title_hl') }}</span></h2>
        <p class="section-desc mx-auto">{{ text('home.teacher.desc') }}</p>
    </div>

    <div class="t-card mt-10 md:mt-14">
        <div class="t-card__avatar">{{ mb_substr(text('home.teacher.name'), 0, 1) }}</div>
        <div>
            <blockquote>
                <span class="mat">"</span> {{ text('home.teacher.quote') }}
            </blockquote>
            <cite>
                {{ text('home.teacher.name') }}
                <span>— {{ text('home.teacher.role') }}</span>
            </cite>
        </div>
    </div>
</section>

<!-- ════════════ CTA ════════════ -->
<section class="fade-up pb-section">
    <div class="cta">
        <div class="cta__decor cta__decor--1"><span class="material-symbols-outlined">star</span></div>
        <div class="cta__decor cta__decor--2"><span class="material-symbols-outlined">rocket_launch</span></div>

        <span class="section-tag section-tag--cta">{{ text('home.cta.tag') }}</span>
        <h2 class="cta__title">{{ text('home.cta.title') }}</h2>
        <p class="cta__desc">{{ text('home.cta.desc') }}</p>
        <div class="cta__btns">
            <a href="{{ route('signup') }}" class="cta__btn cta__btn--solid">
                <span class="material-symbols-outlined icon--md">person_add</span>
                {{ text('home.cta.btn_1') }}
            </a>
            <a href="{{ route('login') }}" class="cta__btn cta__btn--ghost">
                <span class="material-symbols-outlined icon--md">login</span>
                {{ text('home.cta.btn_2') }}
            </a>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('js/home.js') }}?v={{ filemtime(public_path('js/home.js')) }}"></script>
@endpush
