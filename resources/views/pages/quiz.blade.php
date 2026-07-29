@extends('layouts.app')
@section('title', $quiz->name . ' - Quiz')

@section('content')
<section class="quiz-banner group">
    <div class="absolute -top-10 -right-10 text-white opacity-10 transform transition-transform duration-1000 group-hover:rotate-45">
        <span class="material-symbols-outlined !text-[180px]">star</span>
    </div>
    <div class="relative z-10 space-y-6">
        <span class="quiz-banner-badge">Practice Quiz</span>
        <h2 class="quiz-banner-title">{{ $quiz->name }}</h2>
        <div class="space-y-3">
            <div class="flex justify-between items-end text-2xs font-black uppercase tracking-widest opacity-80">
                <span>Mission Status</span>
                <span>Step <span id="currentStep">1</span> of {{ $totalSteps }}</span>
            </div>
            <div class="h-3 w-full bg-white/20 rounded-full overflow-hidden border border-white/10 p-0.5">
                <div id="progressBar" class="h-full rounded-full bg-[rgb(var(--tertiary))] shadow-lg shadow-[rgb(var(--tertiary))/0.3] transition-all duration-500" style="width: {{ $totalSteps > 0 ? (1 / $totalSteps * 100) : 0 }}%"></div>
            </div>
        </div>
    </div>
</section>

<form method="POST" action="{{ route('quiz.submit', $quiz->id) }}" id="quizForm">
    @csrf

    @foreach($questions as $index => $q)
    <section class="mb-10 px-4 quiz-question" data-index="{{ $index }}" style="{{ $index > 0 ? 'display:none' : '' }}">
        <div class="question-container">
            @php
                $questionContent = is_array($q['question']) ? collect($q['question'])->map(fn($block) => $block['content'] ?? '')->join(' ') : $q['question'];
            @endphp
            <h3 class="question-text">{!! $questionContent !!}</h3>

            @if($q['type'] === 'regular')
            <div class="grid gap-4 md:gap-5">
                @foreach(['a','b','c','d','e'] as $letter)
                    @php
                        $variantKey = 'variant_' . $letter;
                        $variant = $q[$variantKey] ?? null;
                        if (!$variant || (is_array($variant) && empty($variant))) continue;
                        $variantText = is_array($variant) ? collect($variant)->map(fn($block) => $block['content'] ?? '')->join(' ') : $variant;
                        if (empty(trim($variantText))) continue;
                    @endphp
                    <button type="button" class="option-btn option-default" data-question="{{ $q['id'] }}" data-answer="{{ $letter }}" onclick="selectAnswer(this, {{ $q['id'] }}, '{{ $letter }}')">
                        <span class="option-letter-box">{{ strtoupper($letter) }}</span>
                        <span class="font-black text-lg flex-1 uppercase tracking-wide">{!! $variantText !!}</span>
                        <span class="material-symbols-outlined !text-3xl opacity-0 check-icon">check_circle</span>
                    </button>
                @endforeach
            </div>
            @else
            {{-- Open question --}}
            <div class="mt-4">
                <textarea name="open_answer_{{ $q['id'] }}" data-question="{{ $q['id'] }}" class="w-full min-h-[120px] rounded-2xl p-5 bg-[rgb(var(--surface))] text-[rgb(var(--on-surface))] font-bold text-sm outline-none placeholder:text-[rgb(var(--on-surface))/0.3] transition-all border border-[rgb(var(--surface-container-high))] focus:border-[rgb(var(--primary))/0.5]" placeholder="Type your answer..." oninput="setOpenAnswer({{ $q['id'] }}, this.value)"></textarea>
            </div>
            @endif

            <input type="hidden" name="answers[{{ $index }}][question_id]" value="{{ $q['id'] }}">
            <input type="hidden" name="answers[{{ $index }}][answer]" id="answer_{{ $q['id'] }}" value="">
        </div>
    </section>
    @endforeach

    <section class="px-4 max-w-4xl mx-auto pb-20">
        <div class="flex gap-4">
            <button type="button" id="prevBtn" onclick="navigateQuestion(-1)" style="display:none" class="flex-1 bg-[rgb(var(--surface-container-high))] text-[rgb(var(--on-surface))] rounded-full font-black uppercase tracking-widest py-4 px-8 active:scale-95 transition-all inline-flex items-center justify-center gap-2 text-sm">
                <span class="material-symbols-outlined !text-2xl">arrow_back</span>
                Previous
            </button>
            <button type="button" id="nextBtn" onclick="navigateQuestion(1)" class="flex-1 bg-[rgb(var(--primary))] text-white rounded-full font-black uppercase tracking-widest py-4 px-8 shadow-xl shadow-[rgb(var(--primary))/0.2] active:scale-95 transition-all inline-flex items-center justify-center gap-2 text-sm">
                Next
                <span class="material-symbols-outlined !text-2xl">arrow_forward</span>
            </button>
            <button type="submit" id="submitBtn" style="display:none" class="flex-1 bg-[rgb(var(--secondary))] text-white rounded-full font-black uppercase tracking-widest py-4 px-8 shadow-xl shadow-[rgb(var(--secondary))/0.2] active:scale-95 transition-all inline-flex items-center justify-center gap-2 text-sm">
                Submit & Finish
                <span class="material-symbols-outlined !text-2xl">rocket_launch</span>
            </button>
        </div>
    </section>
</form>
@endsection

@push('scripts')
<script>
(function() {
    var currentIndex = 0;
    var total = {{ $totalSteps }};
    var questions = document.querySelectorAll('.quiz-question');
    var prevBtn = document.getElementById('prevBtn');
    var nextBtn = document.getElementById('nextBtn');
    var submitBtn = document.getElementById('submitBtn');
    var progressBar = document.getElementById('progressBar');
    var stepLabel = document.getElementById('currentStep');

    window.navigateQuestion = function(dir) {
        questions[currentIndex].style.display = 'none';
        currentIndex += dir;
        if (currentIndex < 0) currentIndex = 0;
        if (currentIndex >= total) currentIndex = total - 1;
        questions[currentIndex].style.display = '';

        prevBtn.style.display = currentIndex > 0 ? '' : 'none';
        if (currentIndex === total - 1) {
            nextBtn.style.display = 'none';
            submitBtn.style.display = '';
        } else {
            nextBtn.style.display = '';
            submitBtn.style.display = 'none';
        }

        progressBar.style.width = ((currentIndex + 1) / total * 100) + '%';
        stepLabel.textContent = currentIndex + 1;
    };

    window.selectAnswer = function(btn, questionId, answer) {
        var container = btn.closest('.question-container');
        container.querySelectorAll('.option-btn').forEach(function(b) {
            b.classList.remove('option-selected');
            b.classList.add('option-default');
            b.querySelector('.check-icon').style.opacity = '0';
        });
        btn.classList.remove('option-default');
        btn.classList.add('option-selected');
        btn.querySelector('.check-icon').style.opacity = '1';
        document.getElementById('answer_' + questionId).value = answer;
    };

    window.setOpenAnswer = function(questionId, value) {
        document.getElementById('answer_' + questionId).value = value;
    };
})();
</script>
@endpush
