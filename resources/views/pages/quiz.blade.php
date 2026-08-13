@extends('layouts.app')
@section('title', text('quiz.page_title', ['name' => $quiz->name]))

@push('styles')
<link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css" />
<link href="{{ asset('css/quiz.css') }}?v={{ filemtime(public_path('css/quiz.css')) }}" rel="stylesheet">
@endpush

@section('content')
<div class="quiz-wrapper space-y-4 sm:space-y-6" data-total-steps="{{ $totalSteps }}" data-check-url="{{ route('quiz.check-answer', $quiz->id) }}" data-i18n='@json([
    'checking' => text('quiz.checking'),
    'correct_title' => text('quiz.correct_title'),
    'correct_sub' => text('quiz.correct_sub'),
    'incorrect_title' => text('quiz.incorrect_title'),
    'incorrect_sub' => text('quiz.incorrect_sub'),
    'incorrect_open_sub' => text('quiz.incorrect_open_sub'),
    'explanation_video' => text('quiz.explanation_video'),
])'>

    <!-- Compact Header Banner -->
    <section class="quiz-hero-banner group">
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-white/20 text-[10px] font-black uppercase tracking-wider text-white">
                        <span class="material-symbols-outlined !text-xs">quiz</span>
                        {{ text('quiz.badge') }}
                    </span>
                    @if($quiz->lesson)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-white/15 text-[10px] font-black uppercase tracking-wider text-white/90">
                        <span class="material-symbols-outlined !text-xs">menu_book</span>
                        {{ $quiz->lesson->name ?? text('quiz.lesson') }}
                    </span>
                    @endif
                </div>
                <h2 class="text-lg sm:text-xl md:text-2xl font-black italic uppercase tracking-tight text-white leading-tight">
                    {{ $quiz->name }}
                </h2>
            </div>

            <!-- Progress & Step -->
            <div class="w-full sm:w-48 space-y-1">
                <div class="flex justify-between items-center text-[10px] font-black uppercase tracking-wider text-white/90">
                    <span>{{ text('quiz.progress') }}</span>
                    <span><span id="currentStep">1</span> / {{ $totalSteps }}</span>
                </div>
                <div class="h-2.5 w-full bg-black/20 rounded-full overflow-hidden border border-white/20 p-0.5">
                    <div id="progressBar" class="h-full rounded-full bg-[rgb(var(--tertiary))] transition-all duration-500" style="width: {{ $totalSteps > 0 ? (1 / $totalSteps * 100) : 0 }}%"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Quiz Form -->
    <form method="POST" action="{{ route('quiz.submit', $quiz->id) }}" id="quizForm">
        @csrf

        @foreach($questions as $index => $q)
        <section class="quiz-question" data-index="{{ $index }}" data-type="{{ $q['type'] }}" style="{{ $index > 0 ? 'display:none' : '' }}">
            <div class="bg-[rgb(var(--surface-container-lowest))] border-2 border-[rgb(var(--surface-container-high))] rounded-2xl p-4 sm:p-6 shadow-md space-y-4">

                <!-- Question Header & Text -->
                <div class="space-y-1.5">
                    <div class="flex items-center gap-1.5 text-[10px] font-black uppercase tracking-widest text-[rgb(var(--primary))]">
                        <span class="w-1.5 h-1.5 rounded-full bg-[rgb(var(--primary))]"></span>
                        <span>{{ text('quiz.question', ['num' => $index + 1]) }}</span>
                    </div>
                    @php
                        $questionContent = renderContentBlocks($q['question'] ?? null);
                    @endphp
                    <h3 class="text-base sm:text-lg font-bold tracking-tight text-[rgb(var(--on-surface))] leading-snug">
                        {!! $questionContent !!}
                    </h3>
                </div>

                <!-- Regular Multiple Choice Questions -->
                @if($q['type'] === 'regular')
                <div class="options-list">
                    @foreach(['a','b','c','d','e'] as $letter)
                        @php
                            $variantKey = 'variant_' . $letter;
                            $variant = $q[$variantKey] ?? null;
                            if (!$variant || (is_array($variant) && empty($variant))) continue;
                            $variantText = renderContentBlocks($variant);
                            if (empty(trim($variantText))) continue;
                        @endphp
                        <button type="button" class="quiz-option-btn option-btn-item" data-question="{{ $q['id'] }}" data-answer="{{ $letter }}" onclick="selectAnswer(this, {{ $q['id'] }}, '{{ $letter }}')">
                            <span class="letter-badge">{{ strtoupper($letter) }}</span>
                            <span class="font-semibold text-sm sm:text-base flex-1 tracking-wide">{!! $variantText !!}</span>
                            <span class="material-symbols-outlined !text-xl opacity-0 icon-status transition-opacity duration-300">check_circle</span>
                        </button>
                    @endforeach
                </div>

                <!-- Confirm step: committing the answer reveals right/wrong + video -->
                <div class="confirm-wrap">
                    <button type="button" class="confirm-btn" id="confirm_{{ $q['id'] }}" data-question="{{ $q['id'] }}" data-answer="" onclick="confirmAnswer(this)">
                        <span class="material-symbols-outlined !text-lg">task_alt</span>
                        {{ text('quiz.confirm') }}
                    </button>
                </div>
                @else
                <!-- Open Ended Question -->
                <div class="space-y-3">
                    <textarea name="open_answer_{{ $q['id'] }}" id="open_input_{{ $q['id'] }}" class="w-full min-h-[100px] rounded-xl p-3.5 bg-[rgb(var(--surface))] text-[rgb(var(--on-surface))] font-bold text-sm outline-none placeholder:text-[rgb(var(--on-surface))/0.4] border-2 border-[rgb(var(--surface-container-high))] focus:border-[rgb(var(--primary))/0.6] transition-all" placeholder="{{ text('quiz.open_placeholder') }}" oninput="setOpenAnswer({{ $q['id'] }}, this.value)"></textarea>
                </div>

                <!-- Confirm step for open answers -->
                <div class="confirm-wrap">
                    <button type="button" class="confirm-btn" id="confirm_open_{{ $q['id'] }}" data-question="{{ $q['id'] }}" onclick="confirmOpenAnswer(this)">
                        <span class="material-symbols-outlined !text-lg">task_alt</span>
                        {{ text('quiz.confirm') }}
                    </button>
                </div>
                @endif

                <!-- Hidden inputs for backend submission -->
                <input type="hidden" name="answers[{{ $index }}][question_id]" value="{{ $q['id'] }}">
                <input type="hidden" name="answers[{{ $index }}][answer]" id="answer_{{ $q['id'] }}" value="">

                <!-- Inline feedback (revealed by quiz.js after confirming) -->
                <div class="feedback-box" id="feedback_{{ $q['id'] }}" style="display:none"></div>
            </div>
        </section>
        @endforeach

        <!-- Navigation Buttons -->
        <section class="pt-2">
            <div class="flex items-center gap-3">
                <button type="button" id="prevBtn" onclick="navigateQuestion(-1)" style="display:none" class="flex-1 bg-[rgb(var(--surface-container-high))] text-[rgb(var(--on-surface))] hover:bg-[rgb(var(--surface-container-high))/0.8] rounded-full font-black uppercase tracking-widest py-3 px-5 active:scale-95 transition-all inline-flex items-center justify-center gap-1.5 text-xs">
                    <span class="material-symbols-outlined !text-lg">arrow_back</span>
                    {{ text('quiz.previous') }}
                </button>
                <button type="button" id="nextBtn" onclick="navigateQuestion(1)" class="flex-1 bg-[rgb(var(--primary))] text-white hover:opacity-95 rounded-full font-black uppercase tracking-widest py-3 px-5 shadow-lg shadow-[rgb(var(--primary))/0.2] active:scale-95 transition-all inline-flex items-center justify-center gap-1.5 text-xs">
                    {{ text('quiz.next') }}
                    <span class="material-symbols-outlined !text-lg">arrow_forward</span>
                </button>
                <button type="submit" id="submitBtn" style="display:none" class="flex-1 bg-[rgb(var(--secondary))] text-white hover:opacity-95 rounded-full font-black uppercase tracking-widest py-3 px-5 shadow-lg shadow-[rgb(var(--secondary))/0.2] active:scale-95 transition-all inline-flex items-center justify-center gap-1.5 text-xs">
                    {{ text('quiz.submit') }}
                    <span class="material-symbols-outlined !text-lg">rocket_launch</span>
                </button>
            </div>
        </section>
    </form>

</div>
@endsection

@push('scripts')
<script src="https://cdn.plyr.io/3.7.8/plyr.js"></script>
<script src="{{ asset('js/quiz.js') }}?v={{ filemtime(public_path('js/quiz.js')) }}"></script>
@endpush
