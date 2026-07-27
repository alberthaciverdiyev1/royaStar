@extends('layouts.app')
@section('title', 'Final Exam')

@section('content')
<section class="max-w-content mx-auto px-4 py-8 md:py-12">
    <header class="mb-8">
        <a href="{{ route('exam') }}" class="inline-flex items-center gap-2 text-3xs font-black uppercase tracking-widest text-[rgb(var(--on-surface-variant))] hover:text-[rgb(var(--primary))] transition-all mb-6 no-underline">
            <span class="material-symbols-outlined !text-sm">arrow_back</span>
            Back to Exams
        </a>
        <div class="flex flex-wrap items-center gap-3 mb-4">
            <span class="text-3xs font-black uppercase tracking-widest inline-flex items-center gap-2 px-4 py-1.5 rounded-full border bg-[rgb(var(--primary))/0.05] text-[rgb(var(--primary))] border-[rgb(var(--primary))/0.1]">
                Grade 9 Mock Exam
            </span>
            <span class="text-3xs font-black uppercase tracking-widest inline-flex items-center gap-1 px-4 py-1.5 rounded-full border bg-[rgb(var(--tertiary))/0.05] text-[rgb(var(--tertiary))] border-[rgb(var(--tertiary))/0.1]">
                <span class="material-symbols-outlined !text-sm">schedule</span>
                60 min
            </span>
        </div>
        <h1 class="text-3xl md:text-4xl font-black text-[rgb(var(--on-surface))] uppercase tracking-tight">Final Exam</h1>
        <p class="text-sm font-bold text-[rgb(var(--on-surface-variant))] mt-2">Read each question carefully and choose the best answer.</p>
    </header>

    <!-- Progress bar -->
    <div class="mb-8 bg-[rgb(var(--surface-container-lowest))] rounded-2xl p-4 md:p-6 border border-[rgb(var(--surface-container-high))]">
        <div class="flex justify-between items-end text-2xs font-black uppercase tracking-widest text-[rgb(var(--on-surface-variant))] mb-3">
            <span>Progress</span>
            <span>Question 1 of 26</span>
        </div>
        <div class="h-3 w-full bg-[rgb(var(--surface-container-high))] rounded-full overflow-hidden p-0.5">
            <div class="h-full rounded-full bg-[rgb(var(--tertiary))] shadow-lg shadow-[rgb(var(--tertiary))/0.3]" style="width: 4%"></div>
        </div>
    </div>

    <!-- Reading Section -->
    <div class="mb-10">
        <h2 class="text-lg font-black uppercase tracking-tight text-[rgb(var(--on-surface))] mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined !text-2xl text-[rgb(var(--primary))]">menu_book</span>
            Section 1: Reading
        </h2>
        <div class="bg-[rgb(var(--surface-container-lowest))] rounded-3xl p-6 md:p-8 border border-[rgb(var(--surface-container-high))] shadow-lg shadow-black/5 mb-6">
            <p class="text-sm font-bold leading-relaxed text-[rgb(var(--on-surface))] opacity-80">
                Read the passage below and answer the questions that follow.
            </p>
            <div class="mt-4 p-5 bg-[rgb(var(--surface))/0.5] rounded-2xl border border-[rgb(var(--surface-container-high))]">
                <p class="text-sm italic leading-relaxed text-[rgb(var(--on-surface-variant))]">
                    "The ancient library of Alexandria was one of the most significant libraries of the ancient world. It was founded in the 3rd century BCE in Alexandria, Egypt..."
                </p>
            </div>
        </div>

        <div class="question-container">
            <h3 class="question-text">What is the main idea of the passage?</h3>
            <div class="grid gap-4 md:gap-5">
                @php $readingOptions = ['A' => 'The Library of Alexandria was destroyed by fire', 'B' => 'The Library of Alexandria was an important center of knowledge', 'C' => 'Ancient Egypt had many libraries', 'D' => 'Books were rare in ancient times'] @endphp
                @foreach($readingOptions as $key => $opt)
                <button class="option-btn option-default">
                    <span class="option-letter-box">{{ $key }}</span>
                    <span class="font-black text-lg flex-1 uppercase tracking-wide">{{ $opt }}</span>
                    <span class="material-symbols-outlined !text-3xl opacity-0">check_circle</span>
                </button>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Audio Section -->
    <div class="mb-10">
        <h2 class="text-lg font-black uppercase tracking-tight text-[rgb(var(--on-surface))] mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined !text-2xl text-[rgb(var(--secondary))]">headphones</span>
            Section 2: Listening
        </h2>
        <div class="audio-player">
            <div class="audio-player-icon">
                <span class="material-symbols-outlined !text-3xl">play_circle</span>
            </div>
            <div class="flex-1 min-w-0">
                <h4 class="font-black text-xs uppercase tracking-wide text-[rgb(var(--on-surface))]">Audio Track 01</h4>
                <p class="text-3xs font-bold text-[rgb(var(--on-surface))/0.5) mt-0.5">Listen and answer the questions</p>
            </div>
            <span class="text-3xs font-black uppercase tracking-widest text-[rgb(var(--on-surface-variant))]">0:00 / 2:30</span>
        </div>
    </div>

    <!-- Writing Section -->
    <div class="mb-10">
        <h2 class="text-lg font-black uppercase tracking-tight text-[rgb(var(--on-surface))] mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined !text-2xl text-[rgb(var(--tertiary))]">edit_note</span>
            Section 3: Writing
        </h2>
        <div class="bg-[rgb(var(--surface-container-lowest))] rounded-3xl p-6 md:p-8 border border-[rgb(var(--surface-container-high))] shadow-lg shadow-black/5">
            <p class="text-sm font-bold text-[rgb(var(--on-surface))] mb-4">Write an essay (150-200 words) on the following topic:</p>
            <div class="p-5 bg-[rgb(var(--surface))/0.5] rounded-2xl border border-[rgb(var(--surface-container-high))] mb-5">
                <p class="text-sm font-bold leading-relaxed text-[rgb(var(--primary))]">"Describe a memorable experience that taught you an important life lesson."</p>
            </div>
            <textarea class="w-full min-h-[180px] rounded-2xl p-5 bg-[rgb(var(--surface))] text-[rgb(var(--on-surface))] font-bold text-sm outline-none placeholder:text-[rgb(var(--on-surface))/0.3] transition-all border border-[rgb(var(--surface-container-high))] focus:border-[rgb(var(--primary))/0.5]" placeholder="Start writing your essay here..."></textarea>
        </div>
    </div>

    <!-- Submit -->
    <form method="POST" action="{{ route('exam.submit', ['id' => 0]) }}" class="max-w-md mx-auto">
        @csrf
        <button type="submit" class="w-full py-4 bg-[rgb(var(--secondary))] text-white rounded-full font-black text-sm active:scale-95 transition-all flex items-center justify-center gap-3 uppercase tracking-widest shadow-lg shadow-[rgb(var(--secondary))/0.2] hover:bg-[rgb(var(--secondary))/0.9]">
            Submit Exam
            <span class="material-symbols-outlined !text-xl">rocket_launch</span>
        </button>
    </form>
</section>
@endsection
