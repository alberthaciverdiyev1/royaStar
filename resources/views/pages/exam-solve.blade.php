@extends('layouts.app')
@section('title', $exam->name . ' - Exam')

@push('styles')
<style>
/* Compact exam wrapper */
.exam-wrapper {
    max-width: 52rem;
    margin: 0 auto;
    padding: 0.75rem 1rem 2rem 1rem;
}
@media (min-width: 640px) {
    .exam-wrapper {
        padding: 1rem 1.5rem 3rem 1.5rem;
    }
}

/* Compact Banner */
.exam-hero-banner {
    position: relative;
    border-radius: 1.5rem;
    padding: 1rem 1.25rem;
    background: linear-gradient(135deg, rgb(var(--secondary)) 0%, rgb(var(--primary)) 100%);
    color: #ffffff;
    box-shadow: 0 10px 25px -10px rgba(var(--secondary), 0.3);
    overflow: hidden;
}
@media (min-width: 640px) {
    .exam-hero-banner {
        padding: 1.25rem 1.75rem;
        border-radius: 1.75rem;
    }
}

/* Option Buttons base & states */
.exam-option-btn {
    width: 100%;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    border-radius: 1rem;
    border: 2px solid rgba(var(--surface-container-high), 1);
    background-color: rgba(var(--surface-container-lowest), 1);
    color: rgba(var(--on-surface), 1);
    font-weight: 700;
    text-align: left;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.22, 1, 0.36, 1);
    position: relative;
}
@media (min-width: 640px) {
    .exam-option-btn {
        padding: 0.875rem 1.25rem;
    }
}
.exam-option-btn:hover:not(.is-disabled) {
    border-color: rgba(var(--primary), 0.4);
    transform: translateY(-1px);
    box-shadow: 0 6px 16px -4px rgba(var(--primary), 0.12);
}

.exam-option-btn .letter-badge {
    width: 2.125rem;
    height: 2.125rem;
    border-radius: 0.625rem;
    background: rgba(var(--primary), 0.1);
    color: rgba(var(--primary), 1);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 900;
    font-size: 0.875rem;
    flex-shrink: 0;
    transition: all 0.2s ease;
}

/* Correct state */
.exam-option-btn.is-correct {
    border-color: #10b981 !important;
    background-color: #ecfdf5 !important;
    color: #065f46 !important;
    box-shadow: 0 6px 18px -4px rgba(16, 185, 129, 0.25) !important;
}
.exam-option-btn.is-correct .letter-badge {
    background-color: #10b981 !important;
    color: #ffffff !important;
}

/* Wrong state */
.exam-option-btn.is-wrong {
    border-color: #ef4444 !important;
    background-color: #fef2f2 !important;
    color: #991b1b !important;
    box-shadow: 0 6px 18px -4px rgba(239, 68, 68, 0.2) !important;
}
.exam-option-btn.is-wrong .letter-badge {
    background-color: #ef4444 !important;
    color: #ffffff !important;
}

/* Target correct answer highlight when user chooses wrong */
.exam-option-btn.is-correct-target {
    border-color: #10b981 !important;
    background-color: #f0fdf4 !important;
    color: #166534 !important;
    animation: pulse-border 1.5s infinite;
}
.exam-option-btn.is-correct-target .letter-badge {
    background-color: #10b981 !important;
    color: #ffffff !important;
}

@keyframes pulse-border {
    0%, 100% { border-color: #10b981; }
    50% { border-color: #34d399; box-shadow: 0 0 12px rgba(52, 211, 153, 0.4); }
}

.exam-option-btn.is-disabled {
    cursor: default;
}

/* Feedback alert box */
.feedback-box {
    margin-top: 1rem;
    padding: 0.75rem 1rem;
    border-radius: 1rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    animation: slideDown 0.3s cubic-bezier(0.22, 1, 0.36, 1);
}
@keyframes slideDown {
    from { opacity: 0; transform: translateY(-6px); }
    to { opacity: 1; transform: translateY(0); }
}
.feedback-box.correct {
    background-color: #ecfdf5;
    border: 1.5px solid #a7f3d0;
    color: #065f46;
}
.feedback-box.wrong {
    background-color: #fef2f2;
    border: 1.5px solid #fecaca;
    color: #991b1b;
}

.options-list {
    display: flex;
    flex-direction: column;
    gap: 0.625rem;
    margin-top: 1rem;
}
</style>
@endpush

@section('content')
<div class="exam-wrapper space-y-4 sm:space-y-6">

    <!-- Compact Header Banner -->
    <section class="exam-hero-banner group">
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-white/20 text-[10px] font-black uppercase tracking-wider text-white">
                        <span class="material-symbols-outlined !text-xs">assignment</span>
                        @if($exam->grade)
                        @php $gradeName = $exam->grade->name[app()->getLocale()] ?? $exam->grade->name['az'] ?? ''; @endphp
                        {{ $gradeName }} Exam
                        @else
                        Exam
                        @endif
                    </span>
                    @if($exam->duration_minutes)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-white/15 text-[10px] font-black uppercase tracking-wider text-white/90">
                        <span class="material-symbols-outlined !text-xs">schedule</span>
                        {{ $exam->duration_minutes }} min
                    </span>
                    @endif
                </div>
                <h2 class="text-lg sm:text-xl md:text-2xl font-black italic uppercase tracking-tight text-white leading-tight">
                    {{ $exam->name }}
                </h2>
            </div>

            <!-- Progress & Step -->
            <div class="w-full sm:w-48 space-y-1">
                <div class="flex justify-between items-center text-[10px] font-black uppercase tracking-wider text-white/90">
                    <span>Progress</span>
                    <span><span id="currentStep">1</span> / {{ $totalSteps }}</span>
                </div>
                <div class="h-2.5 w-full bg-black/20 rounded-full overflow-hidden border border-white/20 p-0.5">
                    <div id="progressBar" class="h-full rounded-full bg-[rgb(var(--tertiary))] transition-all duration-500" style="width: {{ $totalSteps > 0 ? (1 / $totalSteps * 100) : 0 }}%"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Exam Form -->
    <form method="POST" action="{{ route('exam.submit', $exam) }}" id="examForm">
        @csrf

        @foreach($questions as $index => $q)
        @php
            $rightAnswer = strtolower(trim($q['right_answer'] ?? ''));
            $correctAnswerText = $q['correct_answer'] ?? '';
        @endphp
        <section class="exam-question" data-index="{{ $index }}" data-right-answer="{{ $rightAnswer }}" data-correct-text="{{ $correctAnswerText }}" data-type="{{ $q['type'] }}" style="{{ $index > 0 ? 'display:none' : '' }}">
            <div class="bg-[rgb(var(--surface-container-lowest))] border-2 border-[rgb(var(--surface-container-high))] rounded-2xl p-4 sm:p-6 shadow-md space-y-4">

                <!-- Question Header & Text -->
                <div class="space-y-1.5">
                    <div class="flex items-center gap-1.5 text-[10px] font-black uppercase tracking-widest text-[rgb(var(--primary))]">
                        <span class="w-1.5 h-1.5 rounded-full bg-[rgb(var(--primary))]"></span>
                        <span>Question {{ $index + 1 }}</span>
                    </div>
                    @php
                        $questionContent = is_array($q['question']) ? collect($q['question'])->map(fn($block) => $block['content'] ?? '')->join(' ') : $q['question'];
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
                            $variantText = is_array($variant) ? collect($variant)->map(fn($block) => $block['content'] ?? '')->join(' ') : $variant;
                            if (empty(trim($variantText))) continue;
                        @endphp
                        <button type="button" class="exam-option-btn option-btn-item" data-question="{{ $q['id'] }}" data-answer="{{ $letter }}" onclick="selectAnswer(this, {{ $q['id'] }}, '{{ $letter }}', '{{ $rightAnswer }}')">
                            <span class="letter-badge">{{ strtoupper($letter) }}</span>
                            <span class="font-semibold text-sm sm:text-base flex-1 tracking-wide">{!! $variantText !!}</span>
                            <span class="material-symbols-outlined !text-xl opacity-0 icon-status transition-opacity duration-300">check_circle</span>
                        </button>
                    @endforeach
                </div>
                @else
                <!-- Open Ended Question -->
                <div class="space-y-3">
                    <textarea name="open_answer_{{ $q['id'] }}" id="open_input_{{ $q['id'] }}" class="w-full min-h-[100px] rounded-xl p-3.5 bg-[rgb(var(--surface))] text-[rgb(var(--on-surface))] font-bold text-sm outline-none placeholder:text-[rgb(var(--on-surface))/0.4] border-2 border-[rgb(var(--surface-container-high))] focus:border-[rgb(var(--primary))/0.6] transition-all" placeholder="Type your answer here..." oninput="setOpenAnswer({{ $q['id'] }}, this.value)"></textarea>
                    <button type="button" onclick="checkOpenAnswer({{ $q['id'] }}, '{{ addslashes($correctAnswerText) }}')" class="px-5 py-2.5 rounded-full bg-[rgb(var(--primary))] text-white font-black text-xs uppercase tracking-widest hover:opacity-90 active:scale-95 transition-all inline-flex items-center gap-1.5 shadow">
                        <span class="material-symbols-outlined !text-base">verified</span>
                        Check Answer
                    </button>
                </div>
                @endif

                <!-- Feedback Alert Container -->
                <div id="feedback_{{ $q['id'] }}" class="hidden"></div>

                <!-- Hidden inputs for backend submission -->
                <input type="hidden" name="answers[{{ $index }}][question_id]" value="{{ $q['id'] }}">
                <input type="hidden" name="answers[{{ $index }}][answer]" id="answer_{{ $q['id'] }}" value="">
            </div>
        </section>
        @endforeach

        <!-- Navigation Buttons -->
        <section class="pt-2">
            <div class="flex items-center gap-3">
                <button type="button" id="prevBtn" onclick="navigateQuestion(-1)" style="display:none" class="flex-1 bg-[rgb(var(--surface-container-high))] text-[rgb(var(--on-surface))] hover:bg-[rgb(var(--surface-container-high))/0.8] rounded-full font-black uppercase tracking-widest py-3 px-5 active:scale-95 transition-all inline-flex items-center justify-center gap-1.5 text-xs">
                    <span class="material-symbols-outlined !text-lg">arrow_back</span>
                    Previous
                </button>
                <button type="button" id="nextBtn" onclick="navigateQuestion(1)" class="flex-1 bg-[rgb(var(--primary))] text-white hover:opacity-95 rounded-full font-black uppercase tracking-widest py-3 px-5 shadow-lg shadow-[rgb(var(--primary))/0.2] active:scale-95 transition-all inline-flex items-center justify-center gap-1.5 text-xs">
                    Next Question
                    <span class="material-symbols-outlined !text-lg">arrow_forward</span>
                </button>
                <button type="submit" id="submitBtn" style="display:none" class="flex-1 bg-[rgb(var(--secondary))] text-white hover:opacity-95 rounded-full font-black uppercase tracking-widest py-3 px-5 shadow-lg shadow-[rgb(var(--secondary))/0.2] active:scale-95 transition-all inline-flex items-center justify-center gap-1.5 text-xs">
                    Submit & Finish
                    <span class="material-symbols-outlined !text-lg">rocket_launch</span>
                </button>
            </div>
        </section>
    </form>

</div>
@endsection

@push('scripts')
<script>
(function() {
    var currentIndex = 0;
    var total = {{ $totalSteps }};
    var questions = document.querySelectorAll('.exam-question');
    var prevBtn = document.getElementById('prevBtn');
    var nextBtn = document.getElementById('nextBtn');
    var submitBtn = document.getElementById('submitBtn');
    var progressBar = document.getElementById('progressBar');
    var stepLabel = document.getElementById('currentStep');

    window.navigateQuestion = function(dir) {
        if (questions[currentIndex]) {
            questions[currentIndex].style.display = 'none';
        }
        currentIndex += dir;
        if (currentIndex < 0) currentIndex = 0;
        if (currentIndex >= total) currentIndex = total - 1;

        if (questions[currentIndex]) {
            questions[currentIndex].style.display = '';
        }

        prevBtn.style.display = currentIndex > 0 ? '' : 'none';
        if (currentIndex === total - 1) {
            nextBtn.style.display = 'none';
            submitBtn.style.display = '';
        } else {
            nextBtn.style.display = '';
            submitBtn.style.display = 'none';
        }

        if (total > 0) {
            progressBar.style.width = ((currentIndex + 1) / total * 100) + '%';
        }
        stepLabel.textContent = currentIndex + 1;
    };

    window.selectAnswer = function(btn, questionId, chosenAnswer, rightAnswer) {
        var container = btn.closest('.exam-question');
        var feedbackEl = document.getElementById('feedback_' + questionId);

        // Prevent re-answering if already locked
        var allBtns = container.querySelectorAll('.exam-option-btn');
        var alreadyAnswered = false;
        allBtns.forEach(function(b) {
            if (b.classList.contains('is-correct') || b.classList.contains('is-wrong')) {
                alreadyAnswered = true;
            }
        });
        if (alreadyAnswered) return;

        // Save selected answer to hidden input
        document.getElementById('answer_' + questionId).value = chosenAnswer;

        var isRight = (chosenAnswer.toLowerCase() === rightAnswer.toLowerCase());

        allBtns.forEach(function(b) {
            b.classList.add('is-disabled');
            var ans = b.getAttribute('data-answer');
            var icon = b.querySelector('.icon-status');

            if (ans.toLowerCase() === chosenAnswer.toLowerCase()) {
                if (isRight) {
                    b.classList.add('is-correct');
                    if (icon) {
                        icon.textContent = 'check_circle';
                        icon.style.opacity = '1';
                    }
                } else {
                    b.classList.add('is-wrong');
                    if (icon) {
                        icon.textContent = 'cancel';
                        icon.style.opacity = '1';
                    }
                }
            } else if (!isRight && ans.toLowerCase() === rightAnswer.toLowerCase()) {
                // Highlight the correct answer if user chose wrong
                b.classList.add('is-correct-target');
                if (icon) {
                    icon.textContent = 'check_circle';
                    icon.style.opacity = '1';
                }
            }
        });

        // Display Feedback Alert Box
        if (feedbackEl) {
            feedbackEl.classList.remove('hidden');
            if (isRight) {
                feedbackEl.className = 'feedback-box correct';
                feedbackEl.innerHTML = '<span class="material-symbols-outlined !text-xl">auto_awesome</span>' +
                    '<div><strong class="font-black text-xs uppercase tracking-wide block">Correct Answer! ⭐</strong>' +
                    '<span class="text-[11px]">Great job!</span></div>';
            } else {
                feedbackEl.className = 'feedback-box wrong';
                feedbackEl.innerHTML = '<span class="material-symbols-outlined !text-xl">error</span>' +
                    '<div><strong class="font-black text-xs uppercase tracking-wide block">Incorrect Answer!</strong>' +
                    '<span class="text-[11px]">The correct answer is Option <strong>' + rightAnswer.toUpperCase() + '</strong>.</span></div>';
            }
        }
    };

    window.setOpenAnswer = function(questionId, value) {
        document.getElementById('answer_' + questionId).value = value;
    };

    window.checkOpenAnswer = function(questionId, expectedAnswer) {
        var inputVal = (document.getElementById('open_input_' + questionId).value || '').trim();
        var feedbackEl = document.getElementById('feedback_' + questionId);

        if (!inputVal) {
            alert('Please type an answer first!');
            return;
        }

        if (feedbackEl) {
            feedbackEl.classList.remove('hidden');
            var isMatch = inputVal.toLowerCase() === expectedAnswer.trim().toLowerCase();

            if (isMatch) {
                feedbackEl.className = 'feedback-box correct';
                feedbackEl.innerHTML = '<span class="material-symbols-outlined !text-xl">auto_awesome</span>' +
                    '<div><strong class="font-black text-xs uppercase tracking-wide block">Correct Answer! ⭐</strong>' +
                    '<span class="text-[11px]">Your answer matches the expected answer!</span></div>';
            } else {
                feedbackEl.className = 'feedback-box wrong';
                feedbackEl.innerHTML = '<span class="material-symbols-outlined !text-xl">info</span>' +
                    '<div><strong class="font-black text-xs uppercase tracking-wide block">Submitted for Evaluation</strong>' +
                    '<span class="text-[11px]">Expected answer: <strong>' + expectedAnswer + '</strong></span></div>';
            }
        }
    };
})();
</script>
@endpush
