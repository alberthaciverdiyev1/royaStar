@extends('layouts.app')
@section('title', 'Practice Quiz')

@section('content')
<section class="quiz-banner group">
    <div class="absolute -top-10 -right-10 text-white opacity-10 transform transition-transform duration-1000 group-hover:rotate-45">
        <span class="material-symbols-outlined !text-[180px]">star</span>
    </div>
    <div class="relative z-10 space-y-6">
        <span class="quiz-banner-badge">Practice Quiz</span>
        <h2 class="quiz-banner-title">{{ $quiz['name'] ?? 'Countable & Uncountable Nouns' }}</h2>
        <div class="space-y-3">
            <div class="flex justify-between items-end text-2xs font-black uppercase tracking-widest opacity-80">
                <span>Mission Status</span>
                <span>Step {{ $currentStep ?? 1 }} of {{ $totalSteps ?? 5 }}</span>
            </div>
            <div class="h-3 w-full bg-white/20 rounded-full overflow-hidden border border-white/10 p-0.5">
                <div class="h-full rounded-full bg-[rgb(var(--tertiary))] shadow-lg shadow-[rgb(var(--tertiary))/0.3]" style="width: {{ ($currentStep ?? 1) / ($totalSteps ?? 5) * 100 }}%"></div>
            </div>
        </div>
    </div>
</section>

<section class="mb-10 px-4">
    <div class="question-container">
        <h3 class="question-text">
            {{ $question['text'] ?? 'Which of these is <span class="underline decoration-4 underline-offset-8 text-[rgb(var(--secondary))]">uncountable</span>?' }}
        </h3>
        <div class="grid gap-4 md:gap-5">
            @foreach($options ?? [] as $key => $option)
            <button class="option-btn option-default">
                <span class="option-letter-box">{{ $key }}</span>
                <span class="font-black text-lg flex-1 uppercase tracking-wide">{{ $option }}</span>
                <span class="material-symbols-outlined !text-3xl opacity-0">check_circle</span>
            </button>
            @endforeach
        </div>
    </div>
</section>

<section class="px-4 max-w-4xl mx-auto pb-20">
    <div class="rounded-3xl md:rounded-4xl p-6 md:p-10 bg-[rgb(var(--surface-container-lowest))] border border-[rgb(var(--surface-container-high))] shadow-xl shadow-black/5 relative overflow-hidden">
        <div class="text-center space-y-2">
            <h4 class="text-xl md:text-2xl font-black uppercase tracking-tight text-[rgb(var(--on-surface))]">Rate this Lesson</h4>
            <p class="text-2xs font-black uppercase tracking-widest text-[rgb(var(--on-surface))/0.6]">How was your learning journey today?</p>
        </div>
        <div class="flex justify-center gap-2 md:gap-4 my-6">
            @for($i = 1; $i <= 5; $i++)
            <button class="transition-all hover:scale-125 active:scale-75 text-[rgb(var(--tertiary))] min-w-touch min-h-touch flex items-center justify-center">
                <span class="material-symbols-outlined !text-[44px]">star</span>
            </button>
            @endfor
        </div>
        <div class="space-y-4">
            <label class="text-xs font-black uppercase tracking-widest px-2 text-[rgb(var(--primary))]">Tell Teacher Roya what you liked?</label>
            <textarea class="w-full min-h-[120px] rounded-2xl p-5 bg-[rgb(var(--surface))] text-[rgb(var(--on-surface))] font-bold text-sm outline-none placeholder:text-[rgb(var(--on-surface))/0.3] transition-all border-[rgb(var(--surface-container-high))] focus:border-[rgb(var(--primary))/0.2]" placeholder="Your message from across the galaxy..."></textarea>
        </div>
        <form method="POST" action="{{ route('quiz.submit', ['id' => $quiz['id'] ?? 0]) }}">
            @csrf
            <button type="submit" class="w-full bg-[rgb(var(--secondary))] text-white rounded-full font-black uppercase tracking-widest py-4 px-8 shadow-xl shadow-[rgb(var(--secondary))/0.2] active:scale-95 transition-all inline-flex items-center justify-center gap-2 text-sm mt-6">
                Submit & Finish
                <span class="material-symbols-outlined !text-2xl">rocket_launch</span>
            </button>
        </form>
    </div>
</section>
@endsection
