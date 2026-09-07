@extends('layouts.app')
@section('title', text('accepted.page_title'))

@section('content')
<style>
    .acc-hero {
        position: relative;
        overflow: hidden;
        border-radius: 26px;
        padding: 2.6rem 1.5rem;
        text-align: center;
        background: linear-gradient(135deg, rgb(var(--primary)), rgb(var(--secondary)));
        color: #fff;
        box-shadow: 0 24px 60px -24px rgb(0 0 0 / 0.5);
    }
    .acc-hero .mat-deco { position: absolute; font-size: 90px; opacity: .12; color:#fff; }
    .acc-badge {
        display: inline-flex; align-items: center; gap: .4rem;
        background: rgb(255 255 255 / .2); padding: .35rem .9rem; border-radius: 999px;
        font-size: .65rem; font-weight: 800; text-transform: uppercase; letter-spacing: .12em; color: #fff;
        border: 1px solid rgb(255 255 255 / .25);
    }
    .acc-hero h1 { font-size: 1.6rem; font-weight: 900; text-transform: uppercase; letter-spacing: -.01em; line-height: 1.2; margin: .9rem 0 .5rem; }
    .acc-hero h1 .hl-under { background: rgb(0 0 0 / .18); }
    .acc-hero p { font-size: .9rem; font-weight: 600; color: rgb(255 255 255 / .9); max-width: 620px; margin: 0 auto; }

    .accepted-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1.1rem;
        margin-top: 2rem;
    }
    .accepted-card {
        position: relative;
        border-radius: 22px;
        padding: 1.4rem 1.1rem 1.15rem;
        background: linear-gradient(160deg, rgb(var(--surface-container-high))/0.65, rgb(var(--surface-container-lowest)));
        border: 1px solid rgb(var(--surface-container-high));
        box-shadow: 0 14px 40px -22px rgb(0 0 0 / 0.45);
        transition: transform .2s ease, box-shadow .2s ease;
        display: flex; flex-direction: column; align-items: center; text-align: center;
    }
    .accepted-card:hover { transform: translateY(-5px); }
    .accepted-card__rank {
        position: absolute; top: -10px; left: 12px;
        width: 30px; height: 30px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-weight: 900; font-size: .8rem; color: #fff;
        background: rgb(var(--secondary)); box-shadow: 0 8px 18px -8px rgb(var(--secondary) / .8);
        border: 2px solid rgb(var(--surface-container-lowest));
    }
    .accepted-card__img {
        width: 88px; height: 88px; border-radius: 50%;
        overflow: hidden; display: flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg, rgb(var(--primary)), rgb(var(--secondary)));
        color: #fff; font-weight: 800; font-size: 1.7rem;
        border: 3px solid rgb(var(--surface-container-lowest));
        box-shadow: 0 8px 20px -10px rgb(var(--secondary) / 0.7);
    }
    .accepted-card__img img { width: 100%; height: 100%; object-fit: cover; }
    .accepted-card h3 { margin: .9rem 0 .2rem; font-weight: 800; font-size: 1.02rem; color: rgb(var(--on-surface)); line-height: 1.25; }
    .accepted-card__points {
        margin-top: .85rem; display: inline-flex; align-items: center; gap: .35rem;
        padding: .4rem .85rem; border-radius: 999px;
        background: rgb(var(--secondary)); color: #fff; font-weight: 800; font-size: 1.05rem;
        box-shadow: 0 10px 24px -10px rgb(var(--secondary) / 0.7);
    }
    .accepted-card__points .material-symbols-outlined { font-size: 18px; }
    .accepted-card__points small { font-weight: 600; opacity: .85; font-size: .62rem; text-transform: uppercase; letter-spacing: .06em; }
    .acc-empty {
        margin-top: 2rem; border-radius: 24px; padding: 4rem 2rem; text-align: center;
        background: rgb(var(--surface-container-lowest)); border: 1px dashed rgb(var(--surface-container-high));
    }
    .acc-empty .mat { font-size: 60px; opacity: .25; }
    .acc-empty h3 { font-weight: 800; color: rgb(var(--on-surface)); margin: .6rem 0 .2rem; }
    .acc-empty p { font-size: .9rem; color: rgb(var(--on-surface)); opacity: .6; }
    .acc-home-btn {
        display: inline-flex; align-items: center; gap: .5rem; margin-top: 1.8rem;
        padding: .7rem 1.4rem; border-radius: 999px; color: #fff; font-weight: 800;
        font-size: .78rem; text-transform: uppercase; letter-spacing: .08em;
        background: rgb(var(--primary)); box-shadow: 0 16px 34px -16px rgb(var(--primary) / .8);
        transition: transform .2s ease; text-decoration: none;
    }
    .acc-home-btn:hover { transform: translateY(-2px); }
</style>

<div class="max-w-6xl mx-auto px-4 sm:px-6">
    <!-- Hero -->
    <section class="acc-hero">
        <span class="mat-deco" style="top:-20px; left:-10px; transform: rotate(-20deg)">school</span>
        <span class="mat-deco" style="bottom:-30px; right:-10px; transform: rotate(15deg)">star</span>
        <span class="acc-badge">
            <span class="material-symbols-outlined !text-sm">workspace_premium</span>
            {{ text('accepted.badge') }}
        </span>
        <h1>
            {{ text('accepted.title_1') }}
            <span class="hl"><span class="hl-under">{{ text('accepted.title_hl') }}</span></span>
        </h1>
        <p>{{ text('accepted.desc') }}</p>
    </section>

    @if(isset($accepted) && $accepted->isNotEmpty())
    <div class="accepted-grid">
        @foreach($accepted as $index => $student)
        <div class="accepted-card">
            <div class="accepted-card__rank">{{ $index + 1 }}</div>
            <div class="accepted-card__img">
                @if($student->image && (str_contains($student->image, '/') || str_contains($student->image, 'http')))
                    <img src="{{ $student->image }}" alt="{{ e(trim($student->name . ' ' . ($student->surname ?? ''))) }}" loading="lazy">
                @else
                    <span>{{ mb_strtoupper(mb_substr($student->name, 0, 1)) }}</span>
                @endif
            </div>
            <h3>{{ e(trim($student->name . ' ' . ($student->surname ?? ''))) }}</h3>
            <div class="accepted-card__points">
                <strong>{{ $student->exam_points }}</strong>
                <small>{{ text('accepted.points_label') }}</small>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="acc-empty">
        <span class="mat material-symbols-outlined">school</span>
        <h3>{{ text('accepted.empty_title') }}</h3>
        <p>{{ text('accepted.empty_desc') }}</p>
    </div>
    @endif

    <div class="text-center">
        <a href="{{ route('home') }}" class="acc-home-btn">
            <span class="material-symbols-outlined">arrow_back</span>
            {{ text('accepted.home') }}
        </a>
    </div>
</div>
@endsection
