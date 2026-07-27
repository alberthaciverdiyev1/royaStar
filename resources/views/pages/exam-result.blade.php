@extends('layouts.app')
@section('title', 'Exam Result')

@section('content')
<section class="max-w-content mx-auto px-4 py-8 md:py-12">
    <div class="result-banner">
        <div class="result-icon">
            <span class="material-symbols-outlined !text-7xl md:!text-8xl text-[rgb(var(--tertiary))] drop-shadow-lg">trophy</span>
        </div>
        <h2 class="result-title">Congratulations!</h2>
        <p class="result-subtitle spacerocket">You've completed the Grade 9 Mock Exam</p>
        <div class="result-score">
            <span class="result-score-value">85</span>
            <span class="result-score-label">/ 100</span>
        </div>
        <div class="result-stars flex justify-center gap-2">
            @for($i = 0; $i < 3; $i++)
            <span class="material-symbols-outlined !text-4xl text-[rgb(var(--tertiary))]" style="font-variation-settings:'FILL' 1">star</span>
            @endfor
        </div>
        <div class="result-grade">
            @php $score = 85; @endphp
            @if($score >= 90)
            <span class="text-[rgb(var(--grade-a))]">A — Excellent!</span>
            @elseif($score >= 75)
            <span class="text-[rgb(var(--grade-b))]">B — Great Job!</span>
            @elseif($score >= 60)
            <span class="text-[rgb(var(--grade-c))]">C — Good Effort!</span>
            @else
            <span class="text-[rgb(var(--grade-d))]">D — Keep Trying!</span>
            @endif
        </div>
    </div>

    <!-- Review Section -->
    <div class="review-section mt-10">
        <h3 class="text-lg font-black uppercase tracking-tight text-[rgb(var(--on-surface))] mb-6 flex items-center gap-2">
            <span class="material-symbols-outlined !text-2xl text-[rgb(var(--primary))]">rate_review</span>
            Review Answers
        </h3>

        <div class="question-card">
            <div class="flex items-start justify-between mb-3">
                <span class="text-3xs font-black uppercase tracking-widest text-[rgb(var(--primary))]">Question 1</span>
                <span class="material-symbols-outlined !text-2xl text-[rgb(var(--success))]">check_circle</span>
            </div>
            <h4 class="font-black text-sm uppercase text-[rgb(var(--on-surface))] mb-2">What is the main idea of the passage?</h4>
            <div class="text-3xs font-bold text-[rgb(var(--on-surface-variant))]">
                <span class="text-[rgb(var(--success))]">Your answer: B</span> &mdash; Correct!
            </div>
        </div>

        <div class="question-card">
            <div class="flex items-start justify-between mb-3">
                <span class="text-3xs font-black uppercase tracking-widest text-[rgb(var(--primary))]">Question 2</span>
                <span class="material-symbols-outlined !text-2xl text-[rgb(var(--error))]">cancel</span>
            </div>
            <h4 class="font-black text-sm uppercase text-[rgb(var(--on-surface))] mb-2">When was the Library of Alexandria founded?</h4>
            <div class="space-y-1">
                <p class="text-3xs font-bold text-[rgb(var(--error))]">Your answer: A — 2nd Century BCE</p>
                <p class="text-3xs font-bold text-[rgb(var(--success))]">Correct answer: C — 3rd Century BCE</p>
            </div>
        </div>
    </div>

    <!-- Feedback Form -->
    <div class="mt-10">
        <div class="rounded-3xl md:rounded-4xl p-6 md:p-10 bg-[rgb(var(--surface-container-lowest))] border border-[rgb(var(--surface-container-high))] shadow-xl shadow-black/5">
            <div class="text-center space-y-2 mb-8">
                <h4 class="text-xl font-black uppercase tracking-tight text-[rgb(var(--on-surface))]">Mission Debrief</h4>
                <p class="text-3xs font-black uppercase tracking-widest text-[rgb(var(--on-surface))/0.6]">How was your exam experience?</p>
            </div>
            <form method="POST" action="{{ route('exam.submit', ['id' => 0]) }}">
                @csrf
                <input type="hidden" name="score" value="85" />
                <div class="text-xs font-black uppercase tracking-widest px-2 text-[rgb(var(--primary))] mb-4">Rate your experience</div>
                <div class="flex justify-center gap-2 md:gap-4 mb-6">
                    @for($i = 1; $i <= 5; $i++)
                    <button type="button" class="transition-all hover:scale-125 active:scale-75 text-[rgb(var(--tertiary))] min-w-touch min-h-touch flex items-center justify-center">
                        <span class="material-symbols-outlined !text-[44px]">star</span>
                    </button>
                    @endfor
                </div>
                <div class="space-y-4">
                    <label class="text-xs font-black uppercase tracking-widest px-2 text-[rgb(var(--primary))]">Share your thoughts with Teacher Roya</label>
                    <textarea name="review" rows="4" class="w-full min-h-[120px] rounded-2xl p-5 bg-[rgb(var(--surface))] text-[rgb(var(--on-surface))] font-bold text-sm outline-none placeholder:text-[rgb(var(--on-surface))/0.3] transition-all border border-[rgb(var(--surface-container-high))] focus:border-[rgb(var(--primary))/0.5]" placeholder="Your mission debrief..."></textarea>
                </div>
                <button type="submit" class="w-full py-4 bg-[rgb(var(--secondary))] text-white rounded-full font-black text-sm active:scale-95 transition-all flex items-center justify-center gap-3 uppercase tracking-widest shadow-lg shadow-[rgb(var(--secondary))/0.2] hover:bg-[rgb(var(--secondary))/0.9] mt-8">
                    Launch Feedback to Teacher Roya
                    <span class="material-symbols-outlined !text-xl">rocket_launch</span>
                </button>
            </form>
        </div>
    </div>
</section>

<x-success-modal />
@endsection
