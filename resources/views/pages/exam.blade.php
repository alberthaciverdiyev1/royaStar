@extends('layouts.app')
@section('title', 'Exam')

@section('content')
<section class="exam-banner group">
    <div class="bg-decor-star">
        <span class="material-symbols-outlined !text-4xl text-[rgb(var(--tertiary))]">star</span>
    </div>
    <div class="bg-decor-rocket">
        <span class="material-symbols-outlined !text-6xl text-[rgb(var(--primary))]">rocket_launch</span>
    </div>
    <div class="relative z-10">
        <h2 class="exam-banner-title">Exam Zone</h2>
        <p class="exam-banner-text">Test your knowledge and prove your mastery! Each exam brings you closer to becoming a grammar galaxy legend.</p>
    </div>
    <div class="banner-icon-magic group-hover:rotate-45 group-hover:scale-110">
        <span class="material-symbols-outlined !text-[140px] md:!text-[180px]">auto_awesome</span>
    </div>
    <div class="banner-icon-rocket group-hover:translate-x-4 group-hover:-translate-y-4 transition-transform duration-1000">
        <span class="material-symbols-outlined !text-[180px] md:!text-[220px] text-white">rocket_launch</span>
    </div>
</section>

<div class="exam-grid">
    <div class="exam-card-colored">
        <div class="exam-card-badge exam-badge-grade9">Grade 9</div>
        <h3 class="exam-card-title">Mock Exam 01</h3>
        <p class="exam-card-desc">Full grammar and vocabulary assessment for Grade 9 students.</p>
        <div class="exam-card-progress">
            <div class="w-full h-2 rounded-full bg-white/20 overflow-hidden p-0.5">
                <div class="h-full rounded-full bg-white" style="width: 0%"></div>
            </div>
        </div>
        <a href="{{ route('grade9') }}" class="exam-card-btn">
            <span class="material-symbols-outlined !text-lg">arrow_forward</span>
        </a>
    </div>

    <div class="exam-card-white">
        <div class="exam-card-badge exam-badge-grade11">Grade 11</div>
        <h3 class="exam-card-title">Mock Exam 01</h3>
        <p class="exam-card-desc">Comprehensive exam covering advanced grammar and writing skills.</p>
        <div class="exam-card-progress">
            <div class="w-full h-2 rounded-full bg-[rgb(var(--surface-container-high))] overflow-hidden p-0.5">
                <div class="h-full rounded-full bg-[rgb(var(--primary))]" style="width: 0%"></div>
            </div>
        </div>
        <a href="#" class="exam-card-btn-ghost">
            <span class="material-symbols-outlined !text-lg">arrow_forward</span>
        </a>
    </div>

    <div class="exam-card-white">
        <div class="exam-card-badge exam-badge-general">General Grammar</div>
        <h3 class="exam-card-title">Grammar Challenge</h3>
        <p class="exam-card-desc">Mixed-level grammar questions to sharpen your skills.</p>
        <div class="exam-card-progress">
            <div class="w-full h-2 rounded-full bg-[rgb(var(--surface-container-high))] overflow-hidden p-0.5">
                <div class="h-full rounded-full bg-[rgb(var(--primary))]" style="width: 0%"></div>
            </div>
        </div>
        <a href="{{ route('final-exam') }}" class="exam-card-btn-ghost">
            <span class="material-symbols-outlined !text-lg">arrow_forward</span>
        </a>
    </div>
</div>

<div class="reward-card">
    <div class="reward-gradient"></div>
    <div class="reward-icon-circle">
        <span class="material-symbols-outlined !text-5xl text-[rgb(var(--tertiary))]">workspace_premium</span>
    </div>
    <h4 class="reward-title">Star Collector</h4>
    <p class="reward-subtitle">Pass exams to earn stars and unlock new achievements!</p>
</div>
@endsection
