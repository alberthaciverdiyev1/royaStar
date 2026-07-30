@extends('layouts.app')
@section('title', 'Exam Zone')

@push('styles')
<style>
/* Compact exam wrapper */
.exam-listing-wrapper {
    max-width: 72rem;
    margin: 0 auto;
    padding: 0.75rem 1rem 2rem 1rem;
}
@media (min-width: 640px) {
    .exam-listing-wrapper {
        padding: 1rem 1.5rem 3rem 1.5rem;
    }
}

/* Compact Banner — matches quiz hero style */
.exam-hero-banner {
    position: relative;
    border-radius: 1.5rem;
    padding: 1rem 1.25rem;
    background: linear-gradient(135deg, rgb(var(--primary)) 0%, rgb(var(--secondary)) 100%);
    color: #ffffff;
    box-shadow: 0 10px 25px -10px rgba(var(--primary), 0.3);
    overflow: hidden;
    margin-bottom: 1.5rem;
}
@media (min-width: 640px) {
    .exam-hero-banner {
        padding: 1.25rem 1.75rem;
        border-radius: 1.75rem;
        margin-bottom: 2rem;
    }
}

/* Exam Cards — polished like quiz question cards */
.exam-card {
    background-color: rgba(var(--surface-container-lowest), 1);
    border: 2px solid rgba(var(--surface-container-high), 1);
    border-radius: 1.5rem;
    padding: 1.25rem;
    transition: all 0.25s cubic-bezier(0.22, 1, 0.36, 1);
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}
@media (min-width: 640px) {
    .exam-card {
        padding: 1.5rem;
        border-radius: 1.75rem;
    }
}
.exam-card:hover {
    border-color: rgba(var(--primary), 0.3);
    transform: translateY(-2px);
    box-shadow: 0 8px 24px -8px rgba(var(--primary), 0.12);
}

/* Featured exam (first item) — gradient variant */
.exam-card.is-featured {
    background: linear-gradient(135deg, rgb(var(--primary)), rgb(var(--secondary)));
    border-color: transparent;
    color: #ffffff;
}
.exam-card.is-featured:hover {
    box-shadow: 0 8px 28px -8px rgba(var(--secondary), 0.35);
    border-color: rgba(255,255,255,0.15);
}

.exam-card .icon-decoration {
    position: absolute;
    top: -0.5rem;
    right: -0.5rem;
    opacity: 0.08;
    pointer-events: none;
    user-select: none;
    transition: all 0.5s ease;
}
.exam-card:hover .icon-decoration {
    opacity: 0.15;
    transform: scale(1.1) rotate(8deg);
}
.exam-card.is-featured .icon-decoration {
    color: #ffffff;
    opacity: 0.12;
}
.exam-card.is-featured:hover .icon-decoration {
    opacity: 0.2;
}

/* Badge chip */
.exam-card-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.35rem 0.85rem;
    border-radius: 9999px;
    font-size: 10px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    border: 1.5px solid;
    width: fit-content;
    background: rgba(var(--surface), 1);
    border-color: rgba(var(--surface-container-high), 1);
    color: rgba(var(--on-surface-variant), 1);
}
.exam-card.is-featured .exam-card-badge {
    background: rgba(255,255,255,0.18);
    border-color: rgba(255,255,255,0.15);
    color: #ffffff;
}

/* Card title */
.exam-card-title {
    font-size: 1.25rem;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: -0.025em;
    line-height: 1.15;
    color: rgba(var(--on-surface), 1);
}
.exam-card.is-featured .exam-card-title {
    color: #ffffff;
}
@media (min-width: 640px) {
    .exam-card-title {
        font-size: 1.375rem;
    }
}

/* Card description */
.exam-card-desc {
    font-size: 0.8125rem;
    font-weight: 600;
    line-height: 1.5;
    color: rgba(var(--on-surface-variant), 1);
    max-width: 28rem;
}
.exam-card.is-featured .exam-card-desc {
    color: rgba(255,255,255,0.8);
}

/* Progress bar area */
.exam-card-footer {
    margin-top: auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding-top: 0.5rem;
}
.exam-card-progress {
    flex: 1;
    max-width: 12rem;
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}
.exam-card-progress-label {
    font-size: 9px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: rgba(var(--on-surface), 0.35);
}
.exam-card.is-featured .exam-card-progress-label {
    color: rgba(255,255,255,0.6);
}

/* Action button */
.exam-card-action {
    width: 2.75rem;
    height: 2.75rem;
    border-radius: 9999px;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: all 0.25s ease;
    flex-shrink: 0;
    background: rgba(var(--surface), 1);
    color: rgba(var(--on-surface), 0.4);
}
.exam-card-action:hover {
    background: rgba(var(--primary), 0.1);
    color: rgb(var(--primary));
    transform: scale(1.05);
}
.exam-card-action:active {
    transform: scale(0.92);
}
.exam-card.is-featured .exam-card-action {
    background: rgba(255,255,255,0.2);
    color: #ffffff;
}
.exam-card.is-featured .exam-card-action:hover {
    background: rgba(255,255,255,0.3);
}

/* Empty state */
.empty-state {
    text-align: center;
    padding: 4rem 1rem;
}
.empty-state-icon {
    font-size: 4rem;
    color: rgba(var(--on-surface), 0.06);
    margin-bottom: 1.5rem;
}
.empty-state-text {
    color: rgba(var(--on-surface), 0.25);
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-size: 0.875rem;
}

/* Reward Card — compact, matches design language */
.reward-card-compact {
    margin-top: 2rem;
    background: rgba(var(--surface-container-lowest), 1);
    border: 2px solid rgba(var(--surface-container-high), 1);
    border-radius: 1.5rem;
    padding: 1.25rem 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: all 0.25s ease;
}
@media (min-width: 640px) {
    .reward-card-compact {
        border-radius: 1.75rem;
        padding: 1.5rem 2rem;
    }
}
.reward-card-compact:hover {
    border-color: rgba(var(--tertiary), 0.25);
    box-shadow: 0 4px 16px rgba(var(--tertiary), 0.06);
}
.reward-icon-circle {
    width: 3.5rem;
    height: 3.5rem;
    border-radius: 9999px;
    background: rgba(var(--tertiary), 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    color: rgb(var(--tertiary));
}
@media (min-width: 640px) {
    .reward-icon-circle {
        width: 4rem;
        height: 4rem;
    }
}
.reward-text-content {
    flex: 1;
}
.reward-title {
    font-size: 0.875rem;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: -0.02em;
    color: rgba(var(--on-surface), 1);
}
@media (min-width: 640px) {
    .reward-title {
        font-size: 1rem;
    }
}
.reward-subtitle {
    font-size: 0.75rem;
    font-weight: 600;
    color: rgba(var(--on-surface), 0.5);
    margin-top: 0.125rem;
}
</style>
@endpush

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
