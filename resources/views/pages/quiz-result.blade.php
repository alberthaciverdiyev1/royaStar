@extends('layouts.app')
@section('title', 'Quiz Result - ' . ($quiz->name ?? 'Quiz'))

@push('styles')
<style>
/* Hero Result Banner */
.result-hero-card {
    position: relative;
    border-radius: 2.5rem;
    padding: 3rem 2rem;
    background: radial-gradient(circle at 80% 20%, rgba(255, 255, 255, 0.15) 0%, transparent 60%),
                linear-gradient(135deg, rgb(var(--primary)) 0%, rgb(var(--secondary)) 100%);
    color: #ffffff;
    box-shadow: 0 25px 50px -12px rgba(var(--primary), 0.35);
    overflow: hidden;
    margin-bottom: 2rem;
}
@media (min-width: 768px) {
    .result-hero-card {
        border-radius: 3.5rem;
        padding: 4rem 3.5rem;
    }
}

/* Question Breakdown Cards */
.question-review-card {
    background-color: rgba(var(--surface-container-lowest), 1);
    border: 2px solid rgba(var(--surface-container-high), 1);
    border-radius: 1.75rem;
    padding: 1.5rem;
    transition: all 0.25s ease;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
}
.question-review-card.correct {
    border-color: rgba(16, 185, 129, 0.35);
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.03) 0%, rgba(var(--surface-container-lowest), 1) 100%);
}
.question-review-card.wrong {
    border-color: rgba(239, 68, 68, 0.35);
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.03) 0%, rgba(var(--surface-container-lowest), 1) 100%);
}

.filter-tab-btn {
    padding: 0.5rem 1.25rem;
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
    background: rgb(var(--primary));
    border-color: rgb(var(--primary));
    color: #ffffff;
    box-shadow: 0 4px 14px rgba(var(--primary), 0.25);
}

.sidebar-widget-card {
    background-color: rgba(var(--surface-container-lowest), 1);
    border: 2px solid rgba(var(--surface-container-high), 1);
    border-radius: 1.75rem;
    padding: 1.5rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
}
</style>
@endpush

@section('content')
<div class="w-full max-w-[1400px] mx-auto px-4 md:px-8 py-6 space-y-8">

    <!-- Hero Result Header -->
    <section class="result-hero-card group">
        <div class="absolute -top-12 -right-12 text-white/10 pointer-events-none transition-transform duration-1000 group-hover:rotate-45">
            <span class="material-symbols-outlined !text-[280px]">emoji_events</span>
        </div>

        <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="space-y-3 text-center md:text-left max-w-xl">
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/20 backdrop-blur-md text-3xs font-black uppercase tracking-widest text-white">
                    <span class="material-symbols-outlined !text-xs">quiz</span>
                    Quiz Summary & Assessment
                </div>
                <h1 class="text-3xl sm:text-5xl font-black italic uppercase tracking-tight text-white leading-tight">
                    {{ $quiz->name ?? 'Quiz Completed' }}
                </h1>
                <p class="text-xs sm:text-sm font-semibold text-white/80">
                    Awesome effort! Review your answers below to strengthen your grammar mastery.
                </p>
            </div>

            <!-- Score Circle -->
            <div class="flex flex-col items-center justify-center p-6 bg-white/15 backdrop-blur-md border border-white/25 rounded-3xl min-w-[200px] flex-shrink-0 text-center shadow-xl">
                <div class="relative w-28 h-28 flex items-center justify-center">
                    <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                        <path class="text-white/20" stroke-width="3.5" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        <path class="text-amber-300 transition-all duration-1000" stroke-dasharray="{{ $scorePercent }}, 100" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    </svg>
                    <div class="absolute flex flex-col items-center">
                        <span class="text-2xl font-black text-white">{{ $scorePercent }}%</span>
                        <span class="text-4xs font-black uppercase tracking-widest text-amber-200">Score</span>
                    </div>
                </div>
                <div class="text-xs font-black uppercase tracking-widest text-white mt-2">
                    {{ $correctCount }} of {{ $totalCount }} Correct
                </div>
            </div>
        </div>
    </section>

    <!-- Spacious 2-Column Dashboard Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

        <!-- MAIN COLUMN (8 cols): Question Breakdown -->
        <main class="lg:col-span-8 space-y-6">

            <!-- Filter Tabs & Title -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined !text-2xl text-[rgb(var(--primary))]">fact_check</span>
                    <h3 class="text-base sm:text-lg font-black uppercase tracking-tight text-[rgb(var(--on-surface))]">
                        Question Breakdown
                    </h3>
                </div>
                <div class="flex items-center gap-1.5 flex-wrap">
                    <button type="button" class="filter-tab-btn active" onclick="filterQuestions('all', this)">All ({{ $totalCount }})</button>
                    <button type="button" class="filter-tab-btn" onclick="filterQuestions('correct', this)">Correct ({{ $correctCount }})</button>
                    <button type="button" class="filter-tab-btn" onclick="filterQuestions('wrong', this)">Wrong ({{ $wrongCount }})</button>
                </div>
            </div>

            <!-- Questions List -->
            <div class="space-y-4">
                @foreach($questionResults as $index => $res)
                @php
                    $q = $res['question'];
                    $isCorrect = $res['is_correct'];
                    $userLetter = $res['user_letter'];
                    $correctLetter = $res['correct_letter'];
                    $userAnswerText = $res['user_answer_text'];
                    $correctAnswerText = $res['correct_answer_text'];
                    $statusClass = $isCorrect ? 'correct' : 'wrong';
                @endphp
                <div class="question-review-card {{ $statusClass }} q-item-card" data-status="{{ $isCorrect ? 'correct' : 'wrong' }}">
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <span class="text-xs font-black uppercase tracking-widest text-[rgb(var(--on-surface))/0.5]">
                            Question #{{ $index + 1 }}
                        </span>
                        <span class="inline-flex items-center gap-1 text-3xs font-black uppercase tracking-widest px-3 py-1 rounded-full {{ $isCorrect ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                            @if($isCorrect)
                            <span class="material-symbols-outlined !text-sm">check_circle</span> Correct
                            @else
                            <span class="material-symbols-outlined !text-sm">cancel</span> Incorrect
                            @endif
                        </span>
                    </div>

                    <h4 class="font-bold text-sm sm:text-base text-[rgb(var(--on-surface))] mb-4 leading-relaxed">
                        {{ $q->question_az ?? $q->question_en ?? 'Question Content' }}
                    </h4>

                    <!-- Answer Options Comparison -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-3 border-t border-[rgb(var(--surface-container-high))/0.6] text-xs font-semibold">
                        <div class="p-3 rounded-xl {{ $isCorrect ? 'bg-emerald-50 text-emerald-900 border border-emerald-200' : 'bg-rose-50 text-rose-900 border border-rose-200' }}">
                            <span class="text-4xs font-black uppercase tracking-widest block opacity-70 mb-0.5">Your Answer:</span>
                            <span class="font-black text-sm uppercase">{{ $userLetter ? strtoupper($userLetter) : 'No Answer' }}</span>
                            @if($userAnswerText)
                            <span class="block text-xs mt-0.5 font-bold">"{{ $userAnswerText }}"</span>
                            @endif
                        </div>

                        <div class="p-3 rounded-xl bg-emerald-50 text-emerald-900 border border-emerald-200">
                            <span class="text-4xs font-black uppercase tracking-widest block opacity-70 mb-0.5">Correct Answer:</span>
                            <span class="font-black text-sm uppercase">{{ strtoupper($correctLetter) }}</span>
                            @if($correctAnswerText)
                            <span class="block text-xs mt-0.5 font-bold">"{{ $correctAnswerText }}"</span>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

        </main>

        <!-- SIDEBAR COLUMN (4 cols): Actions & Star Award Widget -->
        <aside class="lg:col-span-4 space-y-6 lg:sticky lg:top-24">

            <!-- Star XP Awarded Widget -->
            <div class="sidebar-widget-card space-y-4 text-center">
                <div class="w-14 h-14 rounded-2xl bg-amber-400/20 text-amber-600 flex items-center justify-center mx-auto">
                    <span class="material-symbols-outlined !text-3xl" style="font-variation-settings:'FILL' 1">stars</span>
                </div>
                <div>
                    <span class="text-3xs font-black uppercase tracking-widest text-amber-600">Rewards Earned</span>
                    <h4 class="font-black text-2xl text-[rgb(var(--on-surface))] mt-0.5">+{{ $starsEarned }} XP Stars</h4>
                    <p class="text-xs font-semibold text-[rgb(var(--on-surface))/0.5] mt-1">
                        Great job completing this quiz! Your star score has been updated in your profile.
                    </p>
                </div>
            </div>

            <!-- Quick Action Buttons -->
            <div class="sidebar-widget-card space-y-3">
                <a href="{{ route('quiz', $quiz->id) }}" class="w-full py-3 bg-[rgb(var(--primary))] text-white rounded-full font-black text-xs uppercase tracking-widest flex items-center justify-center gap-2 no-underline active:scale-95 transition-all shadow-md shadow-[rgb(var(--primary))/0.2]">
                    <span class="material-symbols-outlined !text-lg">refresh</span>
                    <span>Retake Quiz</span>
                </a>

                @if($quiz->lesson)
                <a href="{{ route('topics.detail', $quiz->lesson->topic_id) }}" class="w-full py-3 bg-[rgb(var(--surface-container-high))] text-[rgb(var(--on-surface))] hover:bg-[rgb(var(--surface-container-high))/0.8] rounded-full font-black text-xs uppercase tracking-widest flex items-center justify-center gap-2 no-underline active:scale-95 transition-all">
                    <span>Back to Topic</span>
                    <span class="material-symbols-outlined !text-lg">arrow_forward</span>
                </a>
                @endif
            </div>

        </aside>

    </div>

</div>
@endsection

@push('scripts')
<script>
function filterQuestions(type, btn) {
    document.querySelectorAll('.filter-tab-btn').forEach(function(b) {
        b.classList.remove('active');
    });
    btn.classList.add('active');

    document.querySelectorAll('.q-item-card').forEach(function(card) {
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
