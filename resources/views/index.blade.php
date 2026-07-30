@extends('layouts.app', ['isIndex' => true, 'noMainPadding' => true])
@section('title', 'Teacher Roya\'s Stars — Learn English the Fun Way')

@push('styles')
<style>
/* ─── Reset / Base ─── */
.landing-section { padding: 4rem 1rem; }
@media (min-width: 768px) { .landing-section { padding: 5rem 1rem; } }

.section-tag {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    padding: .35rem 1.1rem;
    border-radius: 9999px;
    border: 1.5px solid rgba(var(--primary), .2);
    color: rgb(var(--primary));
    font-size: 10px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: .12em;
    margin-bottom: 1.25rem;
}
.section-title {
    font-size: 1.75rem;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: -.03em;
    line-height: 1.1;
    color: rgba(var(--on-surface), 1);
}
@media (min-width: 768px) { .section-title { font-size: 2.75rem; } }
.section-title .hl { color: rgb(var(--secondary)); }
.section-desc {
    margin-top: .75rem;
    font-size: .9rem;
    line-height: 1.65;
    color: rgba(var(--on-surface-variant), 1);
    max-width: 32rem;
}

/* ─── Floating background ─── */
.fb {
    position: fixed; inset: 0; pointer-events: none; overflow: hidden; z-index: -10;
}
.fb-orb {
    position: absolute;
    border-radius: 9999px;
    filter: blur(100px);
    opacity: .07;
}
.fb-orb--1 { top: 0; left: -5%; width: 500px; height: 500px; background: rgb(var(--secondary)); animation: fb-float 30s ease-in-out infinite; }
.fb-orb--2 { bottom: 0; right: -5%; width: 400px; height: 400px; background: rgb(var(--primary)); animation: fb-float 35s ease-in-out infinite reverse; }
.fb-orb--3 { top: 40%; left: 45%; width: 300px; height: 300px; background: rgb(var(--tertiary)); animation: fb-float 25s ease-in-out infinite 8s; }
@keyframes fb-float {
    0%, 100% { transform: translate(0, 0) scale(1); }
    33% { transform: translate(50px, -60px) scale(1.12); }
    66% { transform: translate(-40px, 40px) scale(.92); }
}

/* ─── HERO ─── */
.hero {
    position: relative;
    min-height: 100dvh;
    display: flex;
    align-items: center;
    overflow: hidden;
    background:
        radial-gradient(ellipse 90% 55% at 50% 0%, rgba(var(--primary),.1) 0%, transparent 65%),
        radial-gradient(ellipse 60% 45% at 80% 100%, rgba(var(--secondary),.07) 0%, transparent 55%),
        radial-gradient(ellipse 50% 40% at 20% 80%, rgba(var(--tertiary),.04) 0%, transparent 50%);
}
.hero__grid {
    position: absolute; inset: 0;
    background-image:
        linear-gradient(rgba(var(--primary),.04) 1px, transparent 1px),
        linear-gradient(90deg, rgba(var(--primary),.04) 1px, transparent 1px);
    background-size: 64px 64px;
    mask-image: radial-gradient(ellipse 65% 60% at 50% 45%, #000 25%, transparent 70%);
    -webkit-mask-image: radial-gradient(ellipse 65% 60% at 50% 45%, #000 25%, transparent 70%);
}
.hero__bg-shape {
    position: absolute;
    border-radius: 9999px;
    opacity: .04;
    background: rgb(var(--secondary));
    filter: blur(60px);
}
.hero__bg-shape--1 { width: 600px; height: 600px; top: -200px; right: -100px; }
.hero__bg-shape--2 { width: 400px; height: 400px; bottom: -100px; left: -100px; background: rgb(var(--primary)); }

/* Star particles */
.hero__particles { position: absolute; inset: 0; pointer-events: none; overflow: hidden; }
.hero__particle {
    position: absolute;
    width: 5px; height: 5px;
    border-radius: 9999px;
    opacity: 0;
}
.hero__particle--1 { top: 18%; left: 12%; background: rgb(var(--primary)); animation: hp 14s ease-in-out infinite; }
.hero__particle--2 { top: 28%; left: 78%; background: rgb(var(--tertiary)); animation: hp 18s ease-in-out infinite 3s; width: 7px; height: 7px; }
.hero__particle--3 { top: 65%; left: 85%; background: rgb(var(--secondary)); animation: hp 12s ease-in-out infinite 6s; width: 4px; height: 4px; }
.hero__particle--4 { top: 75%; left: 18%; background: rgb(var(--primary)); animation: hp 16s ease-in-out infinite 2s; }
.hero__particle--5 { top: 50%; left: 50%; background: rgb(var(--tertiary)); animation: hp 20s ease-in-out infinite 7s; width: 6px; height: 6px; }
.hero__particle--6 { top: 10%; left: 45%; background: rgb(var(--secondary)); animation: hp 15s ease-in-out infinite 5s; width: 3px; height: 3px; }
@keyframes hp {
    0%, 100% { opacity: 0; transform: translateY(20px) scale(.5); }
    25% { opacity: .3; }
    50% { opacity: .1; transform: translateY(-30px) scale(1.2); }
    75% { opacity: .2; }
}

/* Floating stars SVG */
.hero__fs {
    position: absolute; pointer-events: none; color: rgb(var(--tertiary)); opacity: .08;
}
.hero__fs--1 { top: 8%; right: 15%; animation: hp 6s ease-in-out infinite; }
.hero__fs--2 { bottom: 15%; left: 8%; animation: hp 8s ease-in-out infinite 2s; }
.hero__fs--3 { top: 40%; left: 5%; animation: hp 7s ease-in-out infinite 4s; }
.hero__fs .material-symbols-outlined { font-size: 32px !important; }
@media (min-width: 768px) { .hero__fs .material-symbols-outlined { font-size: 48px !important; } }

/* ─── LOGO VISUAL ─── */
.hero__visual {
    position: relative;
    flex-shrink: 0;
}
.hero__visual-ring {
    position: absolute;
    inset: -12px;
    border-radius: 9999px;
    border: 1.5px solid rgba(var(--surface-container-high),.4);
    animation: ring-pulse 4s ease-in-out infinite;
}
.hero__visual-ring--2 { inset: -24px; animation-delay: 1.5s; }
@keyframes ring-pulse {
    0%, 100% { transform: scale(1); opacity: .4; }
    50% { transform: scale(1.08); opacity: .15; }
}
.hero__visual-inner {
    position: relative;
    width: 11rem; height: 11rem;
    border-radius: 9999px;
    padding: .6rem;
    background: rgba(var(--surface-container-lowest),.6);
    border: 2px solid rgba(var(--surface-container-high),.5);
    box-shadow: 0 25px 80px -20px rgba(0,0,0,.25);
    backdrop-filter: blur(12px);
    transition: transform .5s cubic-bezier(.22,1,.36,1);
}
.hero__visual-inner:hover { transform: scale(1.03); }
@media (min-width: 768px) { .hero__visual-inner { width: 14rem; height: 14rem; } }
.hero__visual-bg {
    width: 100%; height: 100%;
    border-radius: 9999px;
    background: linear-gradient(135deg, rgb(var(--primary)), rgb(var(--secondary)));
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    position: relative;
}
.hero__visual-bg::before {
    content: '';
    position: absolute; inset: 0;
    background: radial-gradient(circle at 30% 25%, rgba(255,255,255,.2), transparent 60%);
}
.hero__visual-text {
    font-size: 4rem;
    font-weight: 900;
    color: #fff;
    letter-spacing: -.04em;
    text-shadow: 0 2px 20px rgba(0,0,0,.15);
    position: relative;
    z-index: 1;
}
@media (min-width: 768px) { .hero__visual-text { font-size: 5rem; } }
.hero__visual-star {
    position: absolute;
    top: -.75rem; right: -.25rem;
    z-index: 3;
    animation: pulse-star 2.5s cubic-bezier(.4,0,.6,1) infinite;
}
.hero__visual-star .material-symbols-outlined {
    font-size: 3rem !important;
    color: rgb(var(--tertiary));
    filter: drop-shadow(0 4px 16px rgba(var(--tertiary),.4));
}
@media (min-width: 768px) { .hero__visual-star .material-symbols-outlined { font-size: 4rem !important; } }
.hero__visual-rocket {
    position: absolute;
    bottom: -.25rem; left: -.75rem;
    opacity: .2;
    transform: rotate(-15deg);
    z-index: 1;
}
.hero__visual-rocket .material-symbols-outlined { font-size: 3rem !important; color: rgb(var(--primary)); }
@media (min-width: 768px) { .hero__visual-rocket .material-symbols-outlined { font-size: 3.5rem !important; } }

/* ─── HERO TEXT ─── */
.hero__title {
    font-size: 1.75rem;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: -.05em;
    line-height: 1.05;
    color: rgba(var(--on-surface), 1);
}
@media (min-width: 768px) { .hero__title { font-size: 3.25rem; } }
@media (min-width: 1024px) { .hero__title { font-size: 3.75rem; } }
.hero__title .hl { color: rgb(var(--secondary)); }
.hero__title .hl-under {
    display: inline-block;
    position: relative;
}
.hero__title .hl-under::after {
    content: '';
    position: absolute;
    bottom: -2px; left: 0; right: 0;
    height: 4px;
    border-radius: 2px;
    background: rgb(var(--secondary));
    opacity: .25;
}
.hero__desc {
    margin-top: 1.25rem;
    font-size: 1rem;
    line-height: 1.65;
    color: rgba(var(--on-surface-variant), 1);
    max-width: 520px;
}
@media (min-width: 768px) { .hero__desc { font-size: 1.125rem; margin-top: 1.5rem; } }

/* Stats row */
.hero__stats {
    display: flex;
    flex-wrap: wrap;
    gap: 1.5rem 2.5rem;
    margin-top: 2.5rem;
}
@media (min-width: 768px) { .hero__stats { margin-top: 3rem; gap: 2rem 3.5rem; } }
.hero__stat-value {
    font-size: 1.5rem;
    font-weight: 900;
    letter-spacing: -.03em;
}
@media (min-width: 768px) { .hero__stat-value { font-size: 1.75rem; } }
.hero__stat-label {
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .12em;
    color: rgba(var(--on-surface-variant), .6);
    margin-top: .15rem;
}

/* ─── SCROLL HINT ─── */
.hero__scroll {
    position: absolute;
    bottom: 2rem;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: .25rem;
    color: rgba(var(--on-surface),.2);
    animation: scroll-bounce 2.5s ease-in-out infinite;
}
.hero__scroll span:first-child {
    font-size: 7px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: .2em;
}
.hero__scroll .material-symbols-outlined { font-size: 20px !important; }
@keyframes scroll-bounce {
    0%, 100% { transform: translateX(-50%) translateY(0); }
    50% { transform: translateX(-50%) translateY(8px); }
}

/* ─── FEATURE CARDS ─── */
.f-grid {
    display: grid;
    gap: 1.25rem;
    max-width: 80rem;
    margin: 0 auto;
}
@media (min-width: 640px) { .f-grid { grid-template-columns: repeat(2, 1fr); } }
@media (min-width: 1024px) { .f-grid { grid-template-columns: repeat(3, 1fr); } }

.f-card {
    position: relative;
    border-radius: 28px;
    border: 1px solid rgba(var(--surface-container-high),.4);
    background: rgba(var(--surface-container-lowest),.35);
    padding: 2rem;
    backdrop-filter: blur(8px);
    transition: all .4s cubic-bezier(.22,1,.36,1);
    overflow: hidden;
}
.f-card::before {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: inherit;
    background: linear-gradient(135deg, rgba(var(--primary),.04), rgba(var(--secondary),.04));
    opacity: 0;
    transition: opacity .4s;
}
.f-card:hover {
    border-color: rgba(var(--primary),.25);
    transform: translateY(-8px);
    box-shadow: 0 30px 70px -24px rgba(var(--primary),.18);
}
.f-card:hover::before { opacity: 1; }
@media (min-width: 768px) { .f-card { border-radius: 36px; padding: 2.5rem; } }

.f-card__icon {
    width: 3.25rem; height: 3.25rem;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, rgba(var(--primary),.08), rgba(var(--secondary),.08));
    color: rgb(var(--secondary));
    margin-bottom: 1.25rem;
    transition: all .4s;
}
.f-card:hover .f-card__icon {
    background: linear-gradient(135deg, rgb(var(--secondary)), rgb(var(--primary-fixed)));
    color: #fff;
    transform: scale(1.1) rotate(-4deg);
    box-shadow: 0 8px 24px -8px rgba(var(--secondary),.25);
}
.f-card__icon .material-symbols-outlined { font-size: 24px !important; }

.f-card h3 {
    font-size: 1rem;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: -.02em;
    color: rgba(var(--on-surface), 1);
    margin-bottom: .4rem;
}
.f-card p {
    font-size: .8rem;
    line-height: 1.6;
    color: rgba(var(--on-surface-variant), 1);
}

/* ─── STEPS ─── */
.s-grid {
    display: grid;
    gap: 2rem;
    margin-top: 3rem;
    max-width: 60rem;
    margin-left: auto;
    margin-right: auto;
}
@media (min-width: 768px) { .s-grid { grid-template-columns: repeat(3, 1fr); gap: 1.5rem; margin-top: 4rem; } }

.s-card {
    text-align: center;
    position: relative;
    padding: 2rem 1.5rem 1.75rem;
    border-radius: 28px;
    border: 1px solid rgba(var(--surface-container-high),.3);
    background: rgba(var(--surface-container-lowest),.5);
    backdrop-filter: blur(12px);
    transition: all .4s cubic-bezier(.22,1,.36,1);
    overflow: hidden;
}
.s-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 4px;
    background: linear-gradient(90deg, rgb(var(--primary)), rgb(var(--secondary)), rgb(var(--tertiary)));
    opacity: 0;
    transition: opacity .4s;
}
.s-card:nth-child(2)::before { background: linear-gradient(90deg, rgb(var(--secondary)), rgb(var(--tertiary)), rgb(var(--primary))); }
.s-card:nth-child(3)::before { background: linear-gradient(90deg, rgb(var(--tertiary)), rgb(var(--primary)), rgb(var(--secondary))); }

.s-card::after {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: inherit;
    background: linear-gradient(135deg, rgba(var(--primary),.03), rgba(var(--secondary),.03));
    opacity: 0;
    transition: opacity .4s;
    pointer-events: none;
}

.s-card:hover {
    border-color: rgba(var(--primary),.2);
    transform: translateY(-8px);
    box-shadow: 0 24px 60px -20px rgba(var(--secondary),.15);
}
.s-card:hover::before { opacity: 1; }
.s-card:hover::after { opacity: 1; }

.s-card__num {
    position: relative;
    z-index: 1;
    width: 3.25rem; height: 3.25rem;
    border-radius: 9999px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, rgb(var(--primary)), rgb(var(--secondary)));
    color: #fff;
    font-size: 1rem;
    font-weight: 900;
    margin: 0 auto 1.25rem;
    box-shadow: 0 8px 24px -8px rgba(var(--secondary),.35);
    transition: all .35s cubic-bezier(.22,1,.36,1);
}
.s-card:hover .s-card__num {
    transform: scale(1.08);
    box-shadow: 0 12px 32px -8px rgba(var(--secondary),.45);
}

.s-card .f-card__icon {
    position: relative;
    z-index: 1;
    margin: 0 auto 1rem;
    width: 3rem; height: 3rem;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, rgba(var(--primary),.08), rgba(var(--secondary),.08));
    color: rgb(var(--secondary));
    transition: all .4s;
}
.s-card:hover .f-card__icon {
    background: linear-gradient(135deg, rgb(var(--secondary)), rgb(var(--primary-fixed)));
    color: #fff;
    transform: scale(1.1) rotate(-6deg);
    box-shadow: 0 8px 24px -8px rgba(var(--secondary),.25);
}
.s-card .f-card__icon .material-symbols-outlined { font-size: 24px !important; }

.s-card h3 {
    position: relative;
    z-index: 1;
    font-size: .95rem;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: -.02em;
    color: rgba(var(--on-surface), 1);
    margin-bottom: .4rem;
    transition: color .3s;
}
.s-card:hover h3 { color: rgb(var(--secondary)); }

.s-card p {
    position: relative;
    z-index: 1;
    font-size: .8rem;
    line-height: 1.55;
    color: rgba(var(--on-surface-variant), 1);
    max-width: 20rem;
    margin: 0 auto;
}

.s-card__arrow {
    display: none;
    position: absolute;
    top: 50%;
    right: -1.5rem;
    transform: translateY(-50%);
    color: rgba(var(--primary),.12);
    z-index: 2;
}
.s-card__arrow .material-symbols-outlined { font-size: 28px !important; }
.s-card:hover .s-card__arrow { color: rgba(var(--secondary),.2); }
@media (min-width: 768px) { .s-card:not(:last-child) .s-card__arrow { display: block; } }

/* ─── TEACHER ─── */
.t-card {
    border-radius: 32px;
    border: 1px solid rgba(var(--surface-container-high),.5);
    background: rgba(var(--surface-container-lowest),.45);
    padding: 2.5rem;
    max-width: 48rem;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2rem;
    text-align: center;
    backdrop-filter: blur(8px);
}
@media (min-width: 768px) { .t-card { flex-direction: row; text-align: left; padding: 3rem; } }
.t-card__avatar {
    width: 7rem; height: 7rem;
    border-radius: 9999px;
    background: linear-gradient(135deg, rgb(var(--primary)), rgb(var(--secondary)));
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    font-weight: 900;
    color: #fff;
    flex-shrink: 0;
    box-shadow: 0 12px 32px -12px rgba(var(--secondary),.25);
    border: 3px solid rgba(var(--surface-container-lowest), 1);
}
.t-card blockquote {
    font-size: 1rem;
    line-height: 1.7;
    color: rgba(var(--on-surface),.75);
    font-style: italic;
}
.t-card blockquote .mat {
    color: rgb(var(--secondary));
    font-size: 1.5rem;
    line-height: 0;
    vertical-align: middle;
}
.t-card cite {
    display: block;
    margin-top: .75rem;
    font-style: normal;
    font-size: .8rem;
    font-weight: 800;
    color: rgba(var(--on-surface), 1);
}
.t-card cite span {
    font-weight: 500;
    color: rgba(var(--on-surface-variant), .6);
}

/* ─── CTA ─── */
.cta {
    position: relative;
    overflow: hidden;
    border-radius: 40px;
    max-width: 64rem;
    margin: 0 auto;
    padding: 3.5rem 2rem;
    text-align: center;
    background: linear-gradient(135deg, rgb(var(--primary)), rgb(var(--secondary)));
    color: #fff;
}
@media (min-width: 768px) { .cta { border-radius: 56px; padding: 5rem 3rem; } }
.cta::before {
    content: '';
    position: absolute; inset: 0;
    background: radial-gradient(circle at 25% 0%, rgba(255,255,255,.15) 0%, transparent 50%);
    pointer-events: none;
}
.cta__title {
    font-size: 1.5rem;
    font-weight: 900;
    text-transform: uppercase;
    font-style: italic;
    letter-spacing: -.02em;
    position: relative; z-index: 1;
}
@media (min-width: 768px) { .cta__title { font-size: 2.75rem; } }
.cta__desc {
    margin: .75rem auto 2rem;
    max-width: 28rem;
    font-size: .9rem;
    opacity: .85;
    position: relative; z-index: 1;
    line-height: 1.6;
}
.cta__btns {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 1rem;
    position: relative; z-index: 1;
}
.cta__btn {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    border-radius: 9999px;
    padding: .9rem 2rem;
    font-size: .75rem;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: .1em;
    text-decoration: none;
    transition: all .15s;
}
.cta__btn:active { transform: scale(.95); }
.cta__btn--solid {
    background: #fff;
    color: rgb(var(--secondary));
    box-shadow: 0 8px 24px -6px rgba(0,0,0,.15);
}
.cta__btn--solid:hover { box-shadow: 0 12px 32px -8px rgba(0,0,0,.2); }
.cta__btn--ghost {
    border: 2px solid rgba(255,255,255,.3);
    color: #fff;
    backdrop-filter: blur(8px);
}
.cta__btn--ghost:hover { border-color: rgba(255,255,255,.6); background: rgba(255,255,255,.08); }
.cta__decor {
    position: absolute;
    opacity: .08;
    color: #fff;
    pointer-events: none;
}
.cta__decor--1 { top: 1rem; right: 1rem; animation: pulse-star 3s infinite; }
.cta__decor--2 { bottom: .5rem; left: 1rem; transform: rotate(-20deg); }
.cta__decor .material-symbols-outlined { font-size: 60px !important; }
@media (min-width: 768px) { .cta__decor .material-symbols-outlined { font-size: 90px !important; } }

/* ─── Animations ─── */
.fade-up {
    opacity: 0;
    transform: translateY(40px);
    transition: opacity .8s ease, transform .8s cubic-bezier(.22,1,.36,1);
}
.fade-up.visible { opacity: 1; transform: translateY(0); }

.stagger > * { opacity: 0; transform: translateY(20px); transition: opacity .6s ease, transform .6s cubic-bezier(.22,1,.36,1); }
.stagger.visible > * { opacity: 1; transform: translateY(0); }
.fade-up.visible .stagger > * { opacity: 1; transform: translateY(0); }

.stagger.visible > *:nth-child(1),
.fade-up.visible .stagger > *:nth-child(1) { transition-delay: .05s; }
.stagger.visible > *:nth-child(2),
.fade-up.visible .stagger > *:nth-child(2) { transition-delay: .15s; }
.stagger.visible > *:nth-child(3),
.fade-up.visible .stagger > *:nth-child(3) { transition-delay: .25s; }
.stagger.visible > *:nth-child(4),
.fade-up.visible .stagger > *:nth-child(4) { transition-delay: .35s; }
.stagger.visible > *:nth-child(5),
.fade-up.visible .stagger > *:nth-child(5) { transition-delay: .45s; }
.stagger.visible > *:nth-child(6),
.fade-up.visible .stagger > *:nth-child(6) { transition-delay: .55s; }

/* ─── Mobile ─── */
@media (max-width: 1023px) {
    .hero__inner { flex-direction: column-reverse !important; text-align: center !important; }
    .hero__text { text-align: center !important; }
    .hero__stats { justify-content: center !important; }
    .hero__desc { margin-left: auto; margin-right: auto; }
    .hero__tag { justify-content: center; }
}
</style>
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
                    <span class="section-tag" style="margin-bottom: 0;">
                        <span class="material-symbols-outlined" style="font-size:14px;">auto_awesome</span>
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
                        <div class="hero__stat-value" style="color: rgb(var(--tertiary));">
                            <span class="counter" data-target="500">0</span>+
                        </div>
                        <div class="hero__stat-label">Stars Earned</div>
                    </div>
                    <div>
                        <div class="hero__stat-value" style="color: rgb(var(--secondary));">
                            <span class="counter" data-target="200">0</span>+
                        </div>
                        <div class="hero__stat-label">Active Learners</div>
                    </div>
                    <div>
                        <div class="hero__stat-value" style="color: rgb(var(--primary));">
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
<section class="landing-section fade-up" id="teacher" style="padding-top:0;">
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
<section class="fade-up" style="padding:0 1rem 4rem;">
    <div class="cta">
        <div class="cta__decor cta__decor--1"><span class="material-symbols-outlined">star</span></div>
        <div class="cta__decor cta__decor--2"><span class="material-symbols-outlined">rocket_launch</span></div>

        <span class="section-tag" style="border-color:rgba(255,255,255,.2);color:#fff;position:relative;z-index:1;">Start Today</span>
        <h2 class="cta__title">Ready to Become a Star?</h2>
        <p class="cta__desc">Join hundreds of students already learning with Teacher Roya. Your English adventure starts here — for free.</p>
        <div class="cta__btns">
            <a href="{{ route('signup') }}" class="cta__btn cta__btn--solid">
                <span class="material-symbols-outlined" style="font-size:16px;">person_add</span>
                Create Free Account
            </a>
            <a href="{{ route('login') }}" class="cta__btn cta__btn--ghost">
                <span class="material-symbols-outlined" style="font-size:16px;">login</span>
                I Already Have an Account
            </a>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
(function() {
    // Fade-up sections
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.15 });
    document.querySelectorAll('.fade-up').forEach(el => observer.observe(el));

    // Counter animation
    const counterObs = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const el = entry.target;
                const target = parseInt(el.dataset.target);
                let cur = 0;
                const step = Math.ceil(target / 35);
                const t = setInterval(() => {
                    cur += step;
                    if (cur >= target) { el.textContent = target; clearInterval(t); }
                    else { el.textContent = cur; }
                }, 35);
                counterObs.unobserve(el);
            }
        });
    }, { threshold: 0.5 });
    document.querySelectorAll('.counter').forEach(el => counterObs.observe(el));
})();
</script>
@endpush
