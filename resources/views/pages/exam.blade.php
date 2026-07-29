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

@if($exams->isEmpty())
<div class="text-center py-20">
    <span class="material-symbols-outlined !text-7xl text-[rgb(var(--on-surface))/0.06] mb-6">quiz</span>
    <p class="text-[rgb(var(--on-surface))/0.25] font-black uppercase tracking-widest text-sm">No exams available yet</p>
</div>
@else
<div class="exam-grid">
    @foreach($exams as $index => $exam)
    @php
        $score = $examScores[$exam->id] ?? null;
        $gradeName = $exam->grade ? ($exam->grade->name[app()->getLocale()] ?? $exam->grade->name['az'] ?? 'General') : 'General';
        $isFirst = $index === 0;
    @endphp
    <div class="{{ $isFirst ? 'exam-card-colored' : 'exam-card-white' }}">
        <div class="exam-card-badge {{ $isFirst ? 'exam-badge-grade9' : 'exam-badge-general' }}">{{ $gradeName }}</div>
        <h3 class="exam-card-title">{{ $exam->name }}</h3>
        @if($exam->description)
        <p class="exam-card-desc">{{ $exam->description }}</p>
        @else
        <p class="exam-card-desc">{{ $exam->questions_count }} questions · {{ $exam->duration_minutes ?? 30 }} min</p>
        @endif
        <div class="exam-card-progress">
            <div class="w-full h-2 rounded-full {{ $isFirst ? 'bg-white/20' : 'bg-[rgb(var(--surface-container-high))]' }} overflow-hidden p-0.5">
                <div class="h-full rounded-full {{ $isFirst ? 'bg-white' : 'bg-[rgb(var(--primary))]' }}" style="width: {{ $score ?? 0 }}%"></div>
            </div>
            @if($score !== null)
            <span class="text-3xs font-black uppercase tracking-widest mt-1 {{ $isFirst ? 'text-white/60' : 'text-[rgb(var(--on-surface-variant))]' }}">Score: {{ $score }}%</span>
            @endif
        </div>
        <a href="{{ route('exam.detail', $exam) }}" class="{{ $isFirst ? 'exam-card-btn' : 'exam-card-btn-ghost' }}">
            <span class="material-symbols-outlined !text-lg">arrow_forward</span>
        </a>
    </div>
    @endforeach
</div>
@endif

<div class="reward-card">
    <div class="reward-gradient"></div>
    <div class="reward-icon-circle">
        <span class="material-symbols-outlined !text-5xl text-[rgb(var(--tertiary))]">workspace_premium</span>
    </div>
    <h4 class="reward-title">Star Collector</h4>
    <p class="reward-subtitle">Pass exams to earn stars and unlock new achievements!</p>
</div>
@endsection
