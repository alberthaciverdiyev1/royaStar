@extends('layouts.app')
@section('title', text('exam.page_title'))

<link href="{{ asset('css/exam.css') }}?v={{ filemtime(public_path('css/exam.css')) }}" rel="stylesheet">

@section('content')
@php
    $isGradeMode = isset($grades);
    if ($isGradeMode) {
        $heroTitle = text('exam.grade_title');
        $heroSubtitle = text('exam.grade_subtitle');
        $statPrimary = $grades->count();
        $statPrimaryLabel = text('exam.grades_label');
        $statSecondary = $grades->sum('exams_count');
        $statSecondaryLabel = text('exam.exams_label');
    } else {
        $heroTitle = text('exam.title');
        $heroSubtitle = text('exam.subtitle');
        $statPrimary = $exams->count();
        $statPrimaryLabel = text('exam.exams_label');
        $statSecondary = $exams->sum('questions_count');
        $statSecondaryLabel = text('exam.questions_label');
    }
@endphp
<div class="exam-listing-wrapper space-y-4 sm:space-y-6">

    <!-- Compact Header Banner — matches quiz style -->
    <section class="exam-hero-banner group">
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-white/20 text-[10px] font-black uppercase tracking-wider text-white">
                        <span class="material-symbols-outlined !text-xs">assignment</span>
                        {{ text('exam.badge') }}
                    </span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-white/15 text-[10px] font-black uppercase tracking-wider text-white/90">
                        <span class="material-symbols-outlined !text-xs">workspace_premium</span>
                        {{ $statSecondary }} {{ $statSecondaryLabel }}
                    </span>
                </div>
                <h2 class="text-lg sm:text-xl md:text-2xl font-black italic uppercase tracking-tight text-white leading-tight">
                    {{ $heroTitle }}
                </h2>
                <p class="text-[11px] sm:text-xs font-semibold text-white/80 max-w-lg">
                    {{ $heroSubtitle }}
                </p>
            </div>

            <!-- Stats summary -->
            <div class="hidden sm:flex items-center gap-4 text-center">
                <div>
                    <div class="text-2xl font-black text-white">{{ $statPrimary }}</div>
                    <div class="text-[9px] font-black uppercase tracking-widest text-white/70">{{ $statPrimaryLabel }}</div>
                </div>
                <div class="w-px h-10 bg-white/20"></div>
                <div>
                    <div class="text-2xl font-black text-white">{{ $statSecondary }}</div>
                    <div class="text-[9px] font-black uppercase tracking-widest text-white/70">{{ $statSecondaryLabel }}</div>
                </div>
            </div>
        </div>
    </section>

    @if($isGradeMode)
    <!-- GRADE DIRECTORY -->
    @if($grades->isEmpty())
    <div class="empty-state">
        <span class="material-symbols-outlined empty-state-icon">quiz</span>
        <p class="empty-state-text">{{ text('exam.empty') }}</p>
    </div>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
        @foreach($grades as $index => $grade)
        @php
            $gradeName = $grade->name;
            $isFeatured = $index === 0;
        @endphp
        <a href="{{ route('exam.grade', $grade) }}" class="grade-card {{ $isFeatured ? 'is-featured' : '' }} no-underline">
            <div class="icon-decoration">
                <span class="material-symbols-outlined !text-[80px] md:!text-[100px]">school</span>
            </div>

            <div class="exam-card-badge">
                <span class="material-symbols-outlined !text-[12px]">quiz</span>
                {{ text('exam.card_exams_count', ['count' => $grade->exams_count]) }}
            </div>

            <h3 class="exam-card-title">{{ $gradeName }}</h3>

            <p class="exam-card-desc">{{ text('exam.card_grade_desc') }}</p>

            <div class="exam-card-footer">
                <div class="exam-card-progress-label">{{ text('exam.card_available') }}</div>
                <div class="exam-card-action">
                    <span class="material-symbols-outlined !text-xl">arrow_forward</span>
                </div>
            </div>
        </a>
        @endforeach
    </div>
    @endif
    @else
    <!-- EXAMS FOR SELECTED GRADE -->
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('exam') }}" class="inline-flex items-center gap-1.5 text-3xs font-black uppercase tracking-widest text-[rgb(var(--on-surface-variant))] hover:text-[rgb(var(--primary))] transition-all no-underline">
            <span class="material-symbols-outlined !text-sm">arrow_back</span>
            {{ text('exam.back_all_grades') }}
        </a>
        @php
            $gradeName = $grade->name;
        @endphp
        <h3 class="text-base sm:text-lg font-black uppercase tracking-tight text-[rgb(var(--on-surface))]">
            {{ $gradeName }}
            <span class="text-xs font-bold text-[rgb(var(--on-surface))]/40">· {{ text('exam.card_exams_count', ['count' => $exams->count()]) }}</span>
        </h3>
    </div>

    @if($exams->isEmpty())
    <div class="empty-state">
        <span class="material-symbols-outlined empty-state-icon">quiz</span>
        <p class="empty-state-text">{{ text('exam.empty') }}</p>
    </div>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
        @foreach($exams as $index => $exam)
        @php
            $score = $examScores[$exam->id] ?? null;
            $gradeName = $exam->grade ? $exam->grade->name : 'General';
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
                    {{ $exam->questions_count }} {{ text('exam.card_questions') }}
                </span>
                @if($exam->duration_minutes)
                <span class="inline-flex items-center gap-1">
                    <span class="material-symbols-outlined !text-[14px]">schedule</span>
                    {{ $exam->duration_minutes }} {{ text('exam.card_min') }}
                </span>
                @endif
            </div>
            @endif

            <div class="exam-card-footer">
                <div class="exam-card-progress">
                    <div class="exam-card-progress-label">
                        @if($score !== null)
                        {{ text('exam.card_best', ['score' => $score]) }}
                        @else
                        {{ text('exam.card_not_attempted') }}
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
    @endif

    <!-- Compact Reward Card -->
    <div class="reward-card-compact">
        <div class="reward-icon-circle">
            <span class="material-symbols-outlined !text-2xl md:!text-3xl" style="font-variation-settings:'FILL' 1">workspace_premium</span>
        </div>
        <div class="reward-text-content">
            <h4 class="reward-title">{{ text('exam.reward_title') }}</h4>
            <p class="reward-subtitle">{{ text('exam.reward_desc') }}</p>
        </div>
        <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-[rgb(var(--primary))] text-white font-black text-3xs uppercase tracking-widest no-underline hover:opacity-90 active:scale-95 transition-all shadow-md shadow-[rgb(var(--primary))/0.15] flex-shrink-0">
            <span>{{ text('exam.learn_more') }}</span>
            <span class="material-symbols-outlined !text-sm">arrow_forward</span>
        </a>
    </div>

</div>
@endsection
