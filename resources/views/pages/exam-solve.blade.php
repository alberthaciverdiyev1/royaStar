@extends('layouts.app')
@section('title', text('exam_solve.page_title', ['name' => $exam->name]))

<link href="{{ asset('css/exam-solve.css') }}?v={{ filemtime(public_path('css/exam-solve.css')) }}" rel="stylesheet">

@section('content')
<div class="exam-wrapper space-y-4 sm:space-y-6" data-total-steps="{{ $totalSteps }}">

    <!-- Compact Header Banner -->
    <section class="exam-hero-banner group">
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-white/20 text-[10px] font-black uppercase tracking-wider text-white">
                        <span class="material-symbols-outlined !text-xs">assignment</span>
                        @if($exam->grade)
                        @php $gradeName = $exam->grade->name ?? ''; @endphp
                        {{ text('exam_solve.badge_grade', ['grade' => $gradeName]) }}
                        @else
                        {{ text('exam_solve.badge') }}
                        @endif
                    </span>
                    @if($exam->duration_minutes)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-white/15 text-[10px] font-black uppercase tracking-wider text-white/90">
                        <span class="material-symbols-outlined !text-xs">schedule</span>
                        {{ $exam->duration_minutes }} {{ text('exam_solve.min') }}
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
                    <span>{{ text('exam_solve.progress') }}</span>
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
        <section class="exam-question" data-index="{{ $index }}" data-type="{{ $q['type'] }}" style="{{ $index > 0 ? 'display:none' : '' }}">
            <div class="bg-[rgb(var(--surface-container-lowest))] border-2 border-[rgb(var(--surface-container-high))] rounded-2xl p-4 sm:p-6 shadow-md space-y-4">

                <!-- Question Header & Text -->
                <div class="space-y-1.5">
                    <div class="flex items-center gap-1.5 text-[10px] font-black uppercase tracking-widest text-[rgb(var(--primary))]">
                        <span class="w-1.5 h-1.5 rounded-full bg-[rgb(var(--primary))]"></span>
                        <span>{{ text('exam_solve.question', ['num' => $index + 1]) }}</span>
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
                        <button type="button" class="exam-option-btn option-btn-item" data-question="{{ $q['id'] }}" data-answer="{{ $letter }}" onclick="selectAnswer(this, {{ $q['id'] }}, '{{ $letter }}')">
                            <span class="letter-badge">{{ strtoupper($letter) }}</span>
                            <span class="font-semibold text-sm sm:text-base flex-1 tracking-wide">{!! $variantText !!}</span>
                            <span class="material-symbols-outlined !text-xl opacity-0 icon-status transition-opacity duration-300">check_circle</span>
                        </button>
                    @endforeach
                </div>
                @else
                <!-- Open Ended Question -->
                <div class="space-y-3">
                    <textarea name="open_answer_{{ $q['id'] }}" id="open_input_{{ $q['id'] }}" class="w-full min-h-[100px] rounded-xl p-3.5 bg-[rgb(var(--surface))] text-[rgb(var(--on-surface))] font-bold text-sm outline-none placeholder:text-[rgb(var(--on-surface))/0.4] border-2 border-[rgb(var(--surface-container-high))] focus:border-[rgb(var(--primary))/0.6] transition-all" placeholder="{{ text('exam_solve.open_placeholder') }}" oninput="setOpenAnswer({{ $q['id'] }}, this.value)"></textarea>
                </div>
                @endif

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
                    {{ text('exam_solve.previous') }}
                </button>
                <button type="button" id="nextBtn" onclick="navigateQuestion(1)" class="flex-1 bg-[rgb(var(--primary))] text-white hover:opacity-95 rounded-full font-black uppercase tracking-widest py-3 px-5 shadow-lg shadow-[rgb(var(--primary))/0.2] active:scale-95 transition-all inline-flex items-center justify-center gap-1.5 text-xs">
                    {{ text('exam_solve.next') }}
                    <span class="material-symbols-outlined !text-lg">arrow_forward</span>
                </button>
                <button type="submit" id="submitBtn" style="display:none" class="flex-1 bg-[rgb(var(--secondary))] text-white hover:opacity-95 rounded-full font-black uppercase tracking-widest py-3 px-5 shadow-lg shadow-[rgb(var(--secondary))/0.2] active:scale-95 transition-all inline-flex items-center justify-center gap-1.5 text-xs">
                    {{ text('exam_solve.submit') }}
                    <span class="material-symbols-outlined !text-lg">rocket_launch</span>
                </button>
            </div>
        </section>
    </form>

</div>
@endsection

@push('scripts')
<script src="{{ asset('js/exam-solve.js') }}?v={{ filemtime(public_path('js/exam-solve.js')) }}"></script>
@endpush
