@extends('layouts.app')
@section('title', 'Exam Zone')

<link href="{{ asset('css/exam.css') }}?v={{ filemtime(public_path('css/exam.css')) }}" rel="stylesheet">

@section('content')
<div class="exam-listing-wrapper space-y-4 sm:space-y-6">

    <!-- Compact Header Banner — matches quiz style -->
    <section class="exam-hero-banner group">
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-white/20 text-[10px] font-black uppercase tracking-wider text-white">
                        <span class="material-symbols-outlined !text-xs">assignment</span>
                        Exam Zone
                    </span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-white/15 text-[10px] font-black uppercase tracking-wider text-white/90">
                        <span class="material-symbols-outlined !text-xs">workspace_premium</span>
                        {{ $exams->count() }} Exams
                    </span>
                </div>
                <h2 class="text-lg sm:text-xl md:text-2xl font-black italic uppercase tracking-tight text-white leading-tight">
                    Test Your Knowledge
                </h2>
                <p class="text-[11px] sm:text-xs font-semibold text-white/80 max-w-lg">
                    Prove your mastery! Each exam brings you closer to becoming a grammar galaxy legend.
                </p>
            </div>

            <!-- Stats summary -->
            <div class="hidden sm:flex items-center gap-4 text-center">
                <div>
                    <div class="text-2xl font-black text-white">{{ $exams->count() }}</div>
                    <div class="text-[9px] font-black uppercase tracking-widest text-white/70">Exams</div>
                </div>
                <div class="w-px h-10 bg-white/20"></div>
                <div>
                    <div class="text-2xl font-black text-white">{{ $exams->sum('questions_count') }}</div>
                    <div class="text-[9px] font-black uppercase tracking-widest text-white/70">Questions</div>
                </div>
            </div>
        </div>
    </section>

    @if($exams->isEmpty())
    <div class="empty-state">
        <span class="material-symbols-outlined empty-state-icon">quiz</span>
        <p class="empty-state-text">No exams available yet</p>
    </div>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
        @foreach($exams as $index => $exam)
        @php
            $score = $examScores[$exam->id] ?? null;
            $gradeName = $exam->grade ? ($exam->grade->name[app()->getLocale()] ?? $exam->grade->name['az'] ?? 'General') : 'General';
            $isFeatured = $index === 0;
            $icons = ['stars', 'rocket_launch', 'auto_awesome', 'military_tech', 'psychology', 'bolt'];
            $decorIcon = $icons[$index % count($icons)];
        @endphp
        <a href="{{ route('exam.detail', $exam) }}" class="exam-card {{ $isFeatured ? 'is-featured' : '' }} no-underline">
            <div class="icon-decoration">
                <span class="material-symbols-outlined !text-[80px] md:!text-[100px]">{{ $decorIcon }}</span>
            </div>

            <div class="exam-card-badge">
                <span class="material-symbols-outlined !text-[12px]">school</span>
                {{ $gradeName }}
            </div>

            <h3 class="exam-card-title">{{ $exam->name }}</h3>

            @if($exam->description)
            <p class="exam-card-desc">{{ $exam->description }}</p>
            @else
            <div class="flex items-center gap-3 text-3xs font-bold {{ $isFeatured ? 'text-white/70' : 'text-[rgb(var(--on-surface-variant))]' }}">
                <span class="inline-flex items-center gap-1">
                    <span class="material-symbols-outlined !text-[14px]">checklist</span>
                    {{ $exam->questions_count }} questions
                </span>
                @if($exam->duration_minutes)
                <span class="inline-flex items-center gap-1">
                    <span class="material-symbols-outlined !text-[14px]">schedule</span>
                    {{ $exam->duration_minutes }} min
                </span>
                @endif
            </div>
            @endif

            <div class="exam-card-footer">
                <div class="exam-card-progress">
                    <div class="exam-card-progress-label">
                        @if($score !== null)
                        Best Score: {{ $score }}%
                        @else
                        Not attempted
                        @endif
                    </div>
                    <div class="h-2 w-full rounded-full overflow-hidden p-0.5 border {{ $isFeatured ? 'bg-white/20 border-white/10' : 'bg-[rgb(var(--surface))] border-[rgb(var(--surface-container-high))]' }}">
                        <div class="h-full rounded-full {{ $isFeatured ? 'bg-white' : 'bg-[rgb(var(--primary))]' }} transition-all duration-500" style="width: {{ $score ?? 0 }}%"></div>
                    </div>
                </div>
                <div class="exam-card-action">
                    <span class="material-symbols-outlined !text-xl">arrow_forward</span>
                </div>
            </div>
        </a>
        @endforeach
    </div>
    @endif

    <!-- Compact Reward Card -->
    <div class="reward-card-compact">
        <div class="reward-icon-circle">
            <span class="material-symbols-outlined !text-2xl md:!text-3xl" style="font-variation-settings:'FILL' 1">workspace_premium</span>
        </div>
        <div class="reward-text-content">
            <h4 class="reward-title">Star Collector</h4>
            <p class="reward-subtitle">Pass exams to earn stars and unlock new achievements!</p>
        </div>
        <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-[rgb(var(--primary))] text-white font-black text-3xs uppercase tracking-widest no-underline hover:opacity-90 active:scale-95 transition-all shadow-md shadow-[rgb(var(--primary))/0.15] flex-shrink-0">
            <span>Learn More</span>
            <span class="material-symbols-outlined !text-sm">arrow_forward</span>
        </a>
    </div>

</div>
@endsection
