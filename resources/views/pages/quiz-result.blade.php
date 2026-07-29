@extends('layouts.app')
@section('title', 'Quiz Result - ' . $quiz->name)

@section('content')
<section class="max-w-content mx-auto px-4 py-8 md:py-12">
    <div class="result-banner">
        <div class="result-icon">
            @if($result['score'] >= 90)
            <span class="material-symbols-outlined !text-7xl md:!text-8xl text-[rgb(var(--tertiary))] drop-shadow-lg">trophy</span>
            @elseif($result['score'] >= 60)
            <span class="material-symbols-outlined !text-7xl md:!text-8xl text-[rgb(var(--primary))] drop-shadow-lg">emoji_events</span>
            @else
            <span class="material-symbols-outlined !text-7xl md:!text-8xl text-[rgb(var(--on-surface-variant))] drop-shadow-lg">psychology</span>
            @endif
        </div>
        <h2 class="result-title">
            @if($result['score'] >= 90) Excellent! @elseif($result['score'] >= 75) Great Job! @elseif($result['score'] >= 60) Good Effort! @else Keep Trying! @endif
        </h2>
        <p class="result-subtitle">{{ $quiz->name }}</p>
        <div class="result-score">
            <span class="result-score-value">{{ $result['score'] }}</span>
            <span class="result-score-label">/ 100</span>
        </div>
        <div class="result-stars flex justify-center gap-2">
            @php $stars = $result['score'] >= 90 ? 3 : ($result['score'] >= 75 ? 2 : ($result['score'] >= 50 ? 1 : 0)); @endphp
            @for($i = 0; $i < $stars; $i++)
            <span class="material-symbols-outlined !text-4xl text-[rgb(var(--tertiary))]" style="font-variation-settings:'FILL' 1">star</span>
            @endfor
            @for($i = $stars; $i < 3; $i++)
            <span class="material-symbols-outlined !text-4xl text-[rgb(var(--on-surface))/0.1]">star</span>
            @endfor
        </div>
        <div class="result-grade">
            @if($result['score'] >= 90)
            <span class="text-[rgb(var(--grade-a))]">A — Excellent!</span>
            @elseif($result['score'] >= 75)
            <span class="text-[rgb(var(--grade-b))]">B — Great Job!</span>
            @elseif($result['score'] >= 60)
            <span class="text-[rgb(var(--grade-c))]">C — Good Effort!</span>
            @else
            <span class="text-[rgb(var(--grade-d))]">D — Keep Trying!</span>
            @endif
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-3 gap-3 mt-8 mb-10">
        <div class="text-center p-4 rounded-2xl bg-[rgb(var(--surface-container-lowest))] border border-[rgb(var(--surface-container-high))]">
            <div class="text-2xl font-black text-[rgb(var(--success))]">{{ $result['correct'] }}</div>
            <div class="text-3xs font-black uppercase tracking-widest text-[rgb(var(--on-surface-variant))]">Correct</div>
        </div>
        <div class="text-center p-4 rounded-2xl bg-[rgb(var(--surface-container-lowest))] border border-[rgb(var(--surface-container-high))]">
            <div class="text-2xl font-black text-[rgb(var(--error))]">{{ $result['wrong'] }}</div>
            <div class="text-3xs font-black uppercase tracking-widest text-[rgb(var(--on-surface-variant))]">Wrong</div>
        </div>
        <div class="text-center p-4 rounded-2xl bg-[rgb(var(--surface-container-lowest))] border border-[rgb(var(--surface-container-high))]">
            <div class="text-2xl font-black text-[rgb(var(--on-surface-variant))]">{{ $result['skipped'] }}</div>
            <div class="text-3xs font-black uppercase tracking-widest text-[rgb(var(--on-surface-variant))]">Skipped</div>
        </div>
    </div>

    {{-- Review Answers --}}
    <div class="review-section">
        <h3 class="text-lg font-black uppercase tracking-tight text-[rgb(var(--on-surface))] mb-6 flex items-center gap-2">
            <span class="material-symbols-outlined !text-2xl text-[rgb(var(--primary))]">rate_review</span>
            Review Answers
        </h3>

        @foreach($result['answers'] as $i => $answer)
        <div class="question-card">
            <div class="flex items-start justify-between mb-3">
                <span class="text-3xs font-black uppercase tracking-widest text-[rgb(var(--primary))]">Question {{ $i + 1 }}</span>
                @if($answer['is_correct'] === true)
                <span class="material-symbols-outlined !text-2xl text-[rgb(var(--success))]">check_circle</span>
                @elseif($answer['is_correct'] === false)
                <span class="material-symbols-outlined !text-2xl text-[rgb(var(--error))]">cancel</span>
                @else
                <span class="material-symbols-outlined !text-2xl text-[rgb(var(--on-surface-variant))]">remove_circle</span>
                @endif
            </div>
            @if(isset($answer['question_text']))
            @php
                $qText = is_array($answer['question_text']) ? collect($answer['question_text'])->map(fn($b) => $b['content'] ?? '')->join(' ') : $answer['question_text'];
            @endphp
            <h4 class="font-black text-sm uppercase text-[rgb(var(--on-surface))] mb-2">{!! $qText !!}</h4>
            @endif
            <div class="space-y-1">
                @if(empty($answer['answer']))
                <p class="text-3xs font-bold text-[rgb(var(--on-surface-variant))]">Skipped</p>
                @else
                <p class="text-3xs font-bold {{ $answer['is_correct'] ? 'text-[rgb(var(--success))]' : 'text-[rgb(var(--error))]' }}">
                    Your answer: {{ strtoupper($answer['answer']) }} {{ $answer['is_correct'] ? '— Correct!' : '' }}
                </p>
                @endif
                @if(!$answer['is_correct'] && $answer['correct_answer'])
                <p class="text-3xs font-bold text-[rgb(var(--success))]">Correct answer: {{ strtoupper($answer['correct_answer']) }}</p>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    {{-- Actions --}}
    <div class="flex gap-4 mt-10 max-w-md mx-auto">
        <a href="{{ route('quiz', $quiz->id) }}" class="flex-1 py-4 bg-[rgb(var(--primary))] text-white rounded-full font-black text-sm active:scale-95 transition-all flex items-center justify-center gap-3 uppercase tracking-widest shadow-lg shadow-[rgb(var(--primary))/0.2]">
            <span class="material-symbols-outlined !text-xl">replay</span>
            Try Again
        </a>
        <a href="{{ route('topics') }}" class="flex-1 py-4 bg-[rgb(var(--surface-container-high))] text-[rgb(var(--on-surface))] rounded-full font-black text-sm active:scale-95 transition-all flex items-center justify-center gap-3 uppercase tracking-widest">
            <span class="material-symbols-outlined !text-xl">home</span>
            Topics
        </a>
    </div>
</section>
@endsection
