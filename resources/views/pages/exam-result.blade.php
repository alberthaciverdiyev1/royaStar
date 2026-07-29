@extends('layouts.app')
@section('title', 'Exam Result - ' . $exam->name)

@push('styles')
<style>
/* Page Container & Layout */
.result-hero-wrapper {
    max-width: 52rem;
    margin: 0 auto;
    padding: 1.25rem 1rem 4rem 1rem;
}
@media (min-width: 640px) {
    .result-hero-wrapper {
        padding: 2rem 1.5rem 5rem 1.5rem;
    }
}

/* Hero Banner & Background Orbital Effects */
.magnificent-banner {
    position: relative;
    border-radius: 2.25rem;
    padding: 2.5rem 1.5rem;
    text-align: center;
    background: radial-gradient(circle at 50% 0%, rgba(255, 255, 255, 0.15) 0%, transparent 75%),
                linear-gradient(135deg, rgb(var(--secondary)) 0%, rgb(var(--primary)) 100%);
    color: #ffffff;
    box-shadow: 0 25px 50px -12px rgba(var(--secondary), 0.35);
    overflow: hidden;
}
@media (min-width: 768px) {
    .magnificent-banner {
        border-radius: 3rem;
        padding: 3.5rem 3rem;
    }
}

/* Score Circle Badge & Orbital Animations */
.hero-score-ring {
    position: relative;
    width: 8rem;
    height: 8rem;
    margin: 0 auto 1.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
}
@media (min-width: 768px) {
    .hero-score-ring {
        width: 9.5rem;
        height: 9.5rem;
    }
}

.score-inner-disc {
    position: absolute;
    inset: 6px;
    border-radius: 9999px;
    background: rgba(0, 0, 0, 0.2);
    backdrop-filter: blur(12px);
    border: 2px solid rgba(255, 255, 255, 0.25);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    box-shadow: inset 0 2px 10px rgba(255, 255, 255, 0.2);
}

.trophy-badge {
    position: absolute;
    top: -8px;
    right: -8px;
    width: 2.75rem;
    height: 2.75rem;
    border-radius: 9999px;
    background: rgb(var(--tertiary));
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 8px 20px rgba(var(--tertiary), 0.4);
    animation: bounceSlow 3s infinite ease-in-out;
}
@keyframes bounceSlow {
    0%, 100% { transform: translateY(0) scale(1); }
    50% { transform: translateY(-6px) scale(1.08); }
}

/* Star Rating Rating Glow */
.star-glow-fill {
    color: rgb(var(--tertiary));
    filter: drop-shadow(0 4px 12px rgba(var(--tertiary), 0.6));
    animation: starPulse 2.5s infinite ease-in-out;
}
@keyframes starPulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.12); }
}

/* Stat Box Grid */
.stat-card-item {
    background-color: rgba(var(--surface-container-lowest), 1);
    border: 2px solid rgba(var(--surface-container-high), 1);
    border-radius: 1.25rem;
    padding: 1rem 0.75rem;
    text-align: center;
    transition: all 0.25s cubic-bezier(0.22, 1, 0.36, 1);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
}
.stat-card-item:hover {
    transform: translateY(-2px);
    border-color: rgba(var(--primary), 0.3);
    box-shadow: 0 8px 25px rgba(var(--primary), 0.1);
}

/* Breakdown Cards */
.breakdown-card {
    background-color: rgba(var(--surface-container-lowest), 1);
    border: 2px solid rgba(var(--surface-container-high), 1);
    border-radius: 1.5rem;
    padding: 1.25rem 1.5rem;
    margin-bottom: 1rem;
    transition: all 0.25s ease;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
}
.breakdown-card:last-child {
    margin-bottom: 0;
}
.breakdown-card.pass {
    border-color: rgba(16, 185, 129, 0.35);
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.04) 0%, transparent 100%);
}
.breakdown-card.fail {
    border-color: rgba(239, 68, 68, 0.35);
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.04) 0%, transparent 100%);
}

/* Filter Tab Pills */
.filter-tab-btn {
    padding: 0.4rem 1rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    border: 2px solid rgba(var(--surface-container-high), 1);
    background: rgba(var(--surface-container-lowest), 1);
    color: rgba(var(--on-surface), 0.6);
    cursor: pointer;
    transition: all 0.2s ease;
}
.filter-tab-btn.active {
    background: rgb(var(--secondary));
    border-color: rgb(var(--secondary));
    color: #ffffff;
    box-shadow: 0 4px 14px rgba(var(--secondary), 0.25);
}
</style>
@endpush

@section('content')
<div class="result-hero-wrapper space-y-6 sm:space-y-8">

    <!-- Magnificent Hero Banner -->
    <section class="magnificent-banner group">
        <!-- Floating Celestial Orbs -->
        <div class="absolute -top-10 -left-10 text-white/10 pointer-events-none">
            <span class="material-symbols-outlined !text-[180px]">workspace_premium</span>
        </div>
        <div class="absolute -bottom-10 -right-10 text-white/10 pointer-events-none transform transition-transform duration-1000 group-hover:rotate-45">
            <span class="material-symbols-outlined !text-[200px]">military_tech</span>
        </div>

        <div class="relative z-10 space-y-4">

            <!-- Score Circle with Svg Ring & Trophy Badge -->
            <div class="hero-score-ring">
                <!-- SVG Ring Progress -->
                <svg class="w-full h-full transform -rotate-90" viewBox="0 0 100 100">
                    <circle cx="50" cy="50" r="42" stroke="rgba(255,255,255,0.15)" stroke-width="8" fill="transparent"/>
                    <circle cx="50" cy="50" r="42" stroke="rgb(var(--tertiary))" stroke-width="8" stroke-dasharray="263.8" stroke-dashoffset="{{ 263.8 - (263.8 * $result['score'] / 100) }}" stroke-linecap="round" fill="transparent" style="transition: stroke-dashoffset 1.5s cubic-bezier(0.22, 1, 0.36, 1);"/>
                </svg>

                <div class="score-inner-disc">
                    <span class="text-2xl sm:text-4xl font-black text-white leading-none tracking-tight">{{ $result['score'] }}%</span>
                    <span class="text-[10px] font-black uppercase tracking-widest text-white/70 mt-1">Exam Score</span>
                </div>

                <div class="trophy-badge">
                    @if($result['score'] >= 90)
                    <span class="material-symbols-outlined !text-xl">trophy</span>
                    @elseif($result['score'] >= 60)
                    <span class="material-symbols-outlined !text-xl">emoji_events</span>
                    @else
                    <span class="material-symbols-outlined !text-xl">psychology</span>
                    @endif
                </div>
            </div>

            <!-- Grade Title & Exam Name -->
            <div class="space-y-1.5">
                <div class="inline-flex items-center gap-2 px-4 py-1 rounded-full bg-white/20 backdrop-blur-md text-3xs font-black uppercase tracking-widest text-white">
                    <span class="material-symbols-outlined !text-xs">verified</span>
                    @if($result['score'] >= 90)
                    Grade A — Exceptional Exam Result! 🌟
                    @elseif($result['score'] >= 75)
                    Grade B — High Distinction! 🚀
                    @elseif($result['score'] >= 60)
                    Grade C — Exam Passed! 💪
                    @else
                    Grade D — Needs Improvement 🎯
                    @endif
                </div>
                <h2 class="text-2xl sm:text-4xl font-black italic uppercase tracking-tight text-white leading-tight">
                    @if($result['score'] >= 90) Magnificent Victory! 🏆 @elseif($result['score'] >= 75) Great Achievement! 🚀 @elseif($result['score'] >= 60) Exam Passed! 👍 @else Keep Training! 🎯 @endif
                </h2>
                <p class="text-xs sm:text-sm font-semibold text-white/80 max-w-md mx-auto">
                    {{ $exam->name }}
                </p>
            </div>

            <!-- Star Awards -->
            <div class="flex justify-center items-center gap-2 pt-2">
                @php $stars = $result['score'] >= 90 ? 3 : ($result['score'] >= 75 ? 2 : ($result['score'] >= 50 ? 1 : 0)); @endphp
                @for($i = 0; $i < $stars; $i++)
                <span class="material-symbols-outlined !text-3xl sm:!text-4xl star-glow-fill" style="font-variation-settings:'FILL' 1">star</span>
                @endfor
                @for($i = $stars; $i < 3; $i++)
                <span class="material-symbols-outlined !text-3xl sm:!text-4xl text-white/20">star</span>
                @endfor
            </div>

        </div>
    </section>

    <!-- Star XP Bonus Reward Banner -->
    <section class="bg-gradient-to-r from-amber-500/10 via-amber-400/20 to-amber-500/10 border-2 border-amber-400/30 rounded-2xl p-4 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-400/20 text-amber-600 flex items-center justify-center flex-shrink-0">
                <span class="material-symbols-outlined !text-2xl" style="font-variation-settings:'FILL' 1">stars</span>
            </div>
            <div>
                <h4 class="font-black text-xs sm:text-sm uppercase tracking-wide text-amber-900">Exam XP Awarded!</h4>
                <p class="text-3xs sm:text-2xs font-bold text-amber-700">Exam completion bonus added to your profile stats</p>
            </div>
        </div>
        <span class="font-black text-sm sm:text-base text-amber-600 bg-white/80 px-3 py-1 rounded-full shadow-sm">
            +{{ $stars * 15 + 20 }} Stars
        </span>
    </section>

    <!-- Quick Stats Grid -->
    <section class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="stat-card-item">
            <span class="material-symbols-outlined !text-xl text-[rgb(var(--primary))] mb-1">analytics</span>
            <div class="text-lg sm:text-2xl font-black text-[rgb(var(--on-surface))]">{{ $result['score'] }}%</div>
            <div class="text-3xs font-black uppercase tracking-widest text-[rgb(var(--on-surface))/0.5]">Final Grade</div>
        </div>
        <div class="stat-card-item">
            <span class="material-symbols-outlined !text-xl text-emerald-600 mb-1">check_circle</span>
            <div class="text-lg sm:text-2xl font-black text-emerald-600">{{ $result['correct'] }}</div>
            <div class="text-3xs font-black uppercase tracking-widest text-[rgb(var(--on-surface))/0.5]">Correct</div>
        </div>
        <div class="stat-card-item">
            <span class="material-symbols-outlined !text-xl text-rose-600 mb-1">cancel</span>
            <div class="text-lg sm:text-2xl font-black text-rose-600">{{ $result['wrong'] }}</div>
            <div class="text-3xs font-black uppercase tracking-widest text-[rgb(var(--on-surface))/0.5]">Wrong</div>
        </div>
        <div class="stat-card-item">
            <span class="material-symbols-outlined !text-xl text-slate-400 mb-1">remove_circle</span>
            <div class="text-lg sm:text-2xl font-black text-slate-400">{{ $result['skipped'] }}</div>
            <div class="text-3xs font-black uppercase tracking-widest text-[rgb(var(--on-surface))/0.5]">Skipped</div>
        </div>
    </section>

    <!-- Detailed Question Breakdown -->
    <section class="space-y-4">
        <!-- Header & Filter Tabs -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined !text-2xl text-[rgb(var(--secondary))]">fact_check</span>
                <h3 class="text-base sm:text-lg font-black uppercase tracking-tight text-[rgb(var(--on-surface))]">
                    Exam Questions Breakdown
                </h3>
            </div>
            <!-- Interactive Filters -->
            <div class="flex items-center gap-1.5 flex-wrap">
                <button type="button" class="filter-tab-btn active" onclick="filterResults('all', this)">All ({{ count($result['answers']) }})</button>
                <button type="button" class="filter-tab-btn" onclick="filterResults('correct', this)">Correct ({{ $result['correct'] }})</button>
                <button type="button" class="filter-tab-btn" onclick="filterResults('wrong', this)">Wrong ({{ $result['wrong'] }})</button>
                @if($result['skipped'] > 0)
                <button type="button" class="filter-tab-btn" onclick="filterResults('skipped', this)">Skipped ({{ $result['skipped'] }})</button>
                @endif
            </div>
        </div>

        <!-- Question Cards List -->
        <div class="space-y-3">
            @foreach($result['answers'] as $i => $answer)
            @php
                $statusType = $answer['is_correct'] === true ? 'correct' : ($answer['is_correct'] === false ? (empty($answer['answer']) ? 'skipped' : 'wrong') : 'skipped');
            @endphp
            <div class="breakdown-card {{ $answer['is_correct'] ? 'pass' : ($answer['is_correct'] === false ? 'fail' : '') }} review-card-item" data-status="{{ $statusType }}">
                <!-- Card Header -->
                <div class="flex items-center justify-between gap-3 mb-2.5">
                    <span class="text-3xs font-black uppercase tracking-widest text-[rgb(var(--secondary))] bg-[rgb(var(--secondary))/0.1] px-2.5 py-0.5 rounded-full">
                        Question {{ $i + 1 }}
                    </span>
                    <div>
                        @if($answer['is_correct'] === true)
                        <span class="inline-flex items-center gap-1 text-emerald-600 text-xs font-black uppercase tracking-wider">
                            <span class="material-symbols-outlined !text-lg">check_circle</span>
                            Correct
                        </span>
                        @elseif($answer['is_correct'] === false && !empty($answer['answer']))
                        <span class="inline-flex items-center gap-1 text-rose-600 text-xs font-black uppercase tracking-wider">
                            <span class="material-symbols-outlined !text-lg">cancel</span>
                            Incorrect
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1 text-slate-400 text-xs font-black uppercase tracking-wider">
                            <span class="material-symbols-outlined !text-lg">remove_circle</span>
                            Skipped
                        </span>
                        @endif
                    </div>
                </div>

                <!-- Question Text -->
                @if(isset($answer['question_text']))
                @php
                    $qText = is_array($answer['question_text']) ? collect($answer['question_text'])->map(fn($b) => $b['content'] ?? '')->join(' ') : $answer['question_text'];
                @endphp
                <h4 class="font-bold text-sm sm:text-base text-[rgb(var(--on-surface))] mb-3 leading-snug">
                    {!! $qText !!}
                </h4>
                @endif

                <!-- Answer Comparison Grid -->
                @php
                    $getVariantContent = function($variants, $key) {
                        if (!$key || !isset($variants[$key])) return '';
                        $var = $variants[$key];
                        if (is_array($var)) {
                            return collect($var)->map(fn($b) => $b['content'] ?? '')->join(' ');
                        }
                        return is_string($var) ? $var : '';
                    };

                    $userAnsKey = strtolower(trim($answer['answer'] ?? ''));
                    $userVarText = isset($answer['variants']) ? $getVariantContent($answer['variants'], $userAnsKey) : '';

                    $rightAnsKey = strtolower(trim($answer['correct_answer'] ?? ''));
                    $rightVarText = isset($answer['variants']) ? $getVariantContent($answer['variants'], $rightAnsKey) : '';
                @endphp

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs font-bold pt-2.5 border-t border-[rgb(var(--surface-container-high))/0.6]">
                    <!-- User's Answer -->
                    <div class="p-3 rounded-xl {{ $answer['is_correct'] ? 'bg-emerald-50 text-emerald-900 border border-emerald-200' : (empty($answer['answer']) ? 'bg-slate-50 text-slate-500 border border-slate-200' : 'bg-rose-50 text-rose-900 border border-rose-200') }}">
                        <span class="text-3xs uppercase tracking-widest block opacity-70 mb-1">Your Answer</span>
                        @if(empty($answer['answer']))
                        <span class="italic font-semibold">Skipped</span>
                        @else
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="font-black uppercase text-xs bg-black/10 px-2 py-0.5 rounded">{{ $answer['answer'] }}</span>
                            @if($userVarText)
                            <span class="font-bold text-xs opacity-90">{!! $userVarText !!}</span>
                            @endif
                        </div>
                        @endif
                    </div>

                    <!-- Correct Answer -->
                    <div class="p-3 rounded-xl bg-emerald-50 text-emerald-900 border border-emerald-200">
                        <span class="text-3xs uppercase tracking-widest block opacity-70 mb-1">Correct Answer</span>
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="font-black uppercase text-xs bg-emerald-200 text-emerald-900 px-2 py-0.5 rounded">{{ $answer['correct_answer'] ?? '-' }}</span>
                            @if($rightVarText)
                            <span class="font-bold text-xs text-emerald-900">{!! $rightVarText !!}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </section>

    <!-- Action Buttons Bar -->
    <section class="pt-2">
        <div class="flex items-center gap-3 sm:gap-4">
            <a href="{{ route('exam.start', $exam) }}" class="flex-1 py-3.5 sm:py-4 bg-[rgb(var(--secondary))] text-white hover:opacity-95 rounded-full font-black text-xs sm:text-sm active:scale-95 transition-all flex items-center justify-center gap-2 uppercase tracking-widest shadow-xl shadow-[rgb(var(--secondary))/0.25] no-underline">
                <span class="material-symbols-outlined !text-xl">replay</span>
                Retake Exam
            </a>
            <a href="{{ route('exam') }}" class="flex-1 py-3.5 sm:py-4 bg-[rgb(var(--surface-container-high))] text-[rgb(var(--on-surface))] hover:bg-[rgb(var(--surface-container-high))/0.8] rounded-full font-black text-xs sm:text-sm active:scale-95 transition-all flex items-center justify-center gap-2 uppercase tracking-widest no-underline">
                <span class="material-symbols-outlined !text-xl">quiz</span>
                All Exams
            </a>
        </div>
    </section>

</div>
@endsection

@push('scripts')
<script>
function filterResults(type, btn) {
    document.querySelectorAll('.filter-tab-btn').forEach(function(b) {
        b.classList.remove('active');
    });
    btn.classList.add('active');

    document.querySelectorAll('.review-card-item').forEach(function(card) {
        var status = card.getAttribute('data-status');
        if (type === 'all' || status === type) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
}
</script>
@endpush
