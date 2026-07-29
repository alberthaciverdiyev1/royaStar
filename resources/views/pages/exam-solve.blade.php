@extends('layouts.app')
@section('title', $exam->name . ' - Exam')

@section('content')
<section class="max-w-content mx-auto px-4 py-8 md:py-12">
    <header class="mb-8">
        <a href="{{ route('exam.detail', $exam) }}" class="inline-flex items-center gap-2 text-3xs font-black uppercase tracking-widest text-[rgb(var(--on-surface-variant))] hover:text-[rgb(var(--primary))] transition-all mb-6 no-underline">
            <span class="material-symbols-outlined !text-sm">arrow_back</span>
            Back to Exam
        </a>
        <div class="flex flex-wrap items-center gap-3 mb-4">
            @if($exam->grade)
            @php $gradeName = $exam->grade->name[app()->getLocale()] ?? $exam->grade->name['az'] ?? ''; @endphp
            <span class="text-3xs font-black uppercase tracking-widest inline-flex items-center gap-2 px-4 py-1.5 rounded-full border bg-[rgb(var(--primary))/0.05] text-[rgb(var(--primary))] border-[rgb(var(--primary))/0.1]">
                {{ $gradeName }}
            </span>
            @endif
            @if($exam->duration_minutes)
            <span class="text-3xs font-black uppercase tracking-widest inline-flex items-center gap-1 px-4 py-1.5 rounded-full border bg-[rgb(var(--tertiary))/0.05] text-[rgb(var(--tertiary))] border-[rgb(var(--tertiary))/0.1]">
                <span class="material-symbols-outlined !text-sm">schedule</span>
                {{ $exam->duration_minutes }} min
            </span>
            @endif
        </div>
        <h1 class="text-3xl md:text-4xl font-black text-[rgb(var(--on-surface))] uppercase tracking-tight">{{ $exam->name }}</h1>
        <p class="text-sm font-bold text-[rgb(var(--on-surface-variant))] mt-2">Read each question carefully and choose the best answer.</p>
    </header>

    <!-- Progress bar -->
    <div class="mb-8 bg-[rgb(var(--surface-container-lowest))] rounded-2xl p-4 md:p-6 border border-[rgb(var(--surface-container-high))]">
        <div class="flex justify-between items-end text-2xs font-black uppercase tracking-widest text-[rgb(var(--on-surface-variant))] mb-3">
            <span>Progress</span>
            <span>Question <span id="currentStep">1</span> of {{ $questions->count() }}</span>
        </div>
        <div class="h-3 w-full bg-[rgb(var(--surface-container-high))] rounded-full overflow-hidden p-0.5">
            <div id="progressBar" class="h-full rounded-full bg-[rgb(var(--tertiary))] shadow-lg shadow-[rgb(var(--tertiary))/0.3] transition-all duration-500" style="width: {{ $questions->count() > 0 ? (1 / $questions->count() * 100) : 0 }}%"></div>
        </div>
    </div>

    <form method="POST" action="{{ route('exam.submit', $exam) }}" id="examForm">
        @csrf

        @foreach($questions as $index => $q)
        <div class="exam-question mb-8" data-index="{{ $index }}" style="{{ $index > 0 ? 'display:none' : '' }}">
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
                <div class="mt-4">
                    <textarea name="open_answer_{{ $q['id'] }}" data-question="{{ $q['id'] }}" class="w-full min-h-[120px] rounded-2xl p-5 bg-[rgb(var(--surface))] text-[rgb(var(--on-surface))] font-bold text-sm outline-none placeholder:text-[rgb(var(--on-surface))/0.3] transition-all border border-[rgb(var(--surface-container-high))] focus:border-[rgb(var(--primary))/0.5]" placeholder="Type your answer..." oninput="setOpenAnswer({{ $q['id'] }}, this.value)"></textarea>
                </div>
                @endif

                <input type="hidden" name="answers[{{ $index }}][question_id]" value="{{ $q['id'] }}">
                <input type="hidden" name="answers[{{ $index }}][answer]" id="answer_{{ $q['id'] }}" value="">
            </div>
        </div>
        @endforeach

        <!-- Navigation -->
        <div class="flex gap-4 max-w-md mx-auto mt-8">
            <button type="button" id="prevBtn" onclick="navigateExam(-1)" style="display:none" class="flex-1 py-4 bg-[rgb(var(--surface-container-high))] text-[rgb(var(--on-surface))] rounded-full font-black text-sm active:scale-95 transition-all flex items-center justify-center gap-3 uppercase tracking-widest">
                <span class="material-symbols-outlined !text-xl">arrow_back</span>
                Previous
            </button>
            <button type="button" id="nextBtn" onclick="navigateExam(1)" class="flex-1 py-4 bg-[rgb(var(--primary))] text-white rounded-full font-black text-sm active:scale-95 transition-all flex items-center justify-center gap-3 uppercase tracking-widest shadow-lg shadow-[rgb(var(--primary))/0.2]">
                Next
                <span class="material-symbols-outlined !text-xl">arrow_forward</span>
            </button>
            <button type="submit" id="submitBtn" style="display:none" class="flex-1 py-4 bg-[rgb(var(--secondary))] text-white rounded-full font-black text-sm active:scale-95 transition-all flex items-center justify-center gap-3 uppercase tracking-widest shadow-lg shadow-[rgb(var(--secondary))/0.2] hover:bg-[rgb(var(--secondary))/0.9]">
                Submit Exam
                <span class="material-symbols-outlined !text-xl">rocket_launch</span>
            </button>
        </div>
    </form>
</section>
@endsection

@push('scripts')
<script>
(function() {
    var currentIndex = 0;
    var total = {{ $questions->count() }};
    var questions = document.querySelectorAll('.exam-question');
    var prevBtn = document.getElementById('prevBtn');
    var nextBtn = document.getElementById('nextBtn');
    var submitBtn = document.getElementById('submitBtn');
    var progressBar = document.getElementById('progressBar');
    var stepLabel = document.getElementById('currentStep');

    window.navigateExam = function(dir) {
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
