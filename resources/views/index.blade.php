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

<!-- ════════════ ACCEPTED STUDENTS (TOP) ════════════ -->
@if(isset($topAccepted) && $topAccepted->isNotEmpty())
<section class="landing-section fade-up pt0" id="accepted">
    <style>
        .accepted-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
            gap: 1.1rem;
            margin-top: 2.5rem;
            text-align: left;
        }
        .accepted-card {
            position: relative;
            border-radius: 22px;
            padding: 1.4rem 1.1rem 1.1rem;
            background: linear-gradient(160deg, rgb(var(--surface-container-high))/0.65, rgb(var(--surface-container-lowest)));
            border: 1px solid rgb(var(--surface-container-high));
            box-shadow: 0 14px 40px -22px rgb(0 0 0 / 0.45);
            transition: transform .2s ease, box-shadow .2s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        .accepted-card:hover { transform: translateY(-5px); box-shadow: 0 24px 44px -22px rgb(0 0 0 / 0.5); }
        .accepted-card__img {
            width: 84px; height: 84px; border-radius: 50%;
            overflow: hidden; display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, rgb(var(--primary)), rgb(var(--secondary)));
            color: #fff; font-weight: 800; font-size: 1.6rem;
            border: 3px solid rgb(var(--surface-container-lowest));
            box-shadow: 0 8px 20px -10px rgb(var(--secondary) / 0.7);
        }
        .accepted-card__img img { width: 100%; height: 100%; object-fit: cover; }
        .accepted-card h3 { margin: 0.9rem 0 0.2rem; font-weight: 800; font-size: 1.02rem; color: rgb(var(--on-surface)); line-height: 1.25; }
        .accepted-card__points {
            margin-top: 0.85rem; display: inline-flex; align-items: center; gap: 0.35rem;
            padding: 0.4rem 0.85rem; border-radius: 999px;
            background: rgb(var(--secondary)); color: #fff; font-weight: 800; font-size: 1.05rem;
            box-shadow: 0 10px 24px -10px rgb(var(--secondary) / 0.7);
        }
        .accepted-card__points .material-symbols-outlined { font-size: 18px; }
        .accepted-card__points small { font-weight: 600; opacity: .85; font-size: .62rem; text-transform: uppercase; letter-spacing: .06em; }
        .accepted-more-btn {
            display: inline-flex; align-items: center; gap: .55rem;
            margin-top: 2.4rem; padding: .85rem 1.7rem; border-radius: 999px;
            color: #fff; font-weight: 800; font-size: .8rem; text-transform: uppercase; letter-spacing: .08em;
            background: rgb(var(--primary)); box-shadow: 0 16px 34px -14px rgb(var(--primary) / 0.75);
            transition: transform .2s ease; text-decoration: none;
        }
        .accepted-more-btn:hover { transform: translateY(-2px); }
        .accepted-card__points small { margin-left: 2px; }
    </style>

    <div class="max-w-6xl mx-auto px-4 text-center">
        <span class="section-tag">
            <span class="material-symbols-outlined icon--sm">school</span>
            {{ text('home.accepted.tag') }}
        </span>
        <h2 class="section-title">{{ text('home.accepted.title_1') }} <span class="hl">{{ text('home.accepted.title_hl') }}</span></h2>
        <p class="section-desc mx-auto">{{ text('home.accepted.desc') }}</p>

        <div class="accepted-grid">
            @foreach($topAccepted as $student)
            <div class="accepted-card">
                <div class="accepted-card__img">
                    @if($student->image && (str_contains($student->image, '/') || str_contains($student->image, 'http')))
                        <img src="{{ $student->image }}" alt="{{ e(trim($student->name . ' ' . ($student->surname ?? ''))) }}" loading="lazy">
                    @else
                        <span>{{ mb_strtoupper(mb_substr($student->name, 0, 1)) }}</span>
                    @endif
                </div>
                <h3>{{ e(trim($student->name . ' ' . ($student->surname ?? ''))) }}</h3>
                <div class="accepted-card__points">
                    <span class="material-symbols-outlined">star</span>
                    <strong>{{ $student->exam_points }}</strong>
                    <small>{{ text('home.accepted.points_label') }}</small>
                </div>
            </div>
            @endforeach
        </div>

        <a href="{{ route('accepted-students') }}" class="accepted-more-btn">
            <span class="material-symbols-outlined">school</span>
            {{ text('home.accepted.btn') }}
        </a>
    </div>
</section>
@endif

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
