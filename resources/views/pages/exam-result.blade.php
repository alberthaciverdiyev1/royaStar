@extends('layouts.app')
@section('title', text('exam_result.page_title', ['name' => $exam->name ?? 'Exam']))

<link href="{{ asset('css/exam-result.css') }}?v={{ filemtime(public_path('css/exam-result.css')) }}" rel="stylesheet">

@section('content')
@php
    $scorePercent = $result['score'] ?? 0;
    $totalCount = $result['total'] ?? 0;
    $correctCount = $result['correct'] ?? 0;
    $wrongCount = $result['wrong'] ?? 0;
    $skippedCount = $result['skipped'] ?? 0;
    $passingScore = $exam->passing_score ?? 60;
    $starsEarned = ($scorePercent >= $passingScore ? 30 : 0) + ($scorePercent >= 90 ? 50 : 0);
    $questionResults = [];
    foreach ($result['answers'] ?? [] as $ans) {
        $qText = $ans['question_text'] ?? '';
        $qTextStr = renderContentBlocks($qText);
        $variants = $ans['variants'] ?? [];

        $userLetter = $ans['answer'] ?? '';
        $correctLetter = $ans['correct_answer'] ?? '';

        // Get the actual text of user's and correct answer
        $userAnswerText = '';
        $correctAnswerText = '';
        if ($ans['type'] === 'regular') {
            $userAnswerText = '';
            $correctAnswerText = '';
            foreach (['a', 'b', 'c', 'd', 'e'] as $letter) {
                $v = $variants[$letter] ?? [];
                $vText = renderContentBlocks($v);
                if ($letter === $userLetter) $userAnswerText = $vText;
                if ($letter === $correctLetter) $correctAnswerText = $vText;
            }
        } else {
            $userAnswerText = $userLetter;
            $correctAnswerText = $correctLetter;
        }

        $questionResults[] = [
            'is_correct' => $ans['is_correct'],
            'user_letter' => $userLetter,
            'correct_letter' => $correctLetter,
            'user_answer_text' => $userAnswerText,
            'correct_answer_text' => $correctAnswerText,
            'question_text' => $qTextStr,
            'explanation_video_url' => $ans['explanation_video_url'] ?? null,
        ];
    }
@endphp

<div class="w-full max-w-[1400px] mx-auto px-4 md:px-8 py-6 space-y-8">

    <!-- Hero Result Header -->
    <section class="result-hero-card group">
        <div class="absolute -top-12 -right-12 text-white/10 pointer-events-none transition-transform duration-1000 group-hover:rotate-45">
            <span class="material-symbols-outlined !text-[280px]">trophy</span>
        </div>

        <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="space-y-3 text-center md:text-left max-w-xl">
                @auth
                <div class="flex items-center justify-center md:justify-start gap-2.5 mb-1">
                    <div class="w-8 h-8 rounded-full bg-white/20 backdrop-blur-md border-2 border-white/30 overflow-hidden flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
                        @if(!empty(auth()->user()->avatar))
                            @if(str_contains(auth()->user()->avatar, '/') || str_contains(auth()->user()->avatar, 'http'))
                                <img src="{{ auth()->user()->avatar }}" alt="" class="w-full h-full object-cover" />
                            @else
                                <span class="text-base select-none">{{ auth()->user()->avatar }}</span>
                            @endif
                        @else
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        @endif
                    </div>
                    <span class="text-3xs font-black uppercase tracking-widest text-white/80">{{ auth()->user()->name }}</span>
                </div>
                @endauth
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/20 backdrop-blur-md text-3xs font-black uppercase tracking-widest text-white">
                    <span class="material-symbols-outlined !text-xs">assignment_turned_in</span>
                    @if($exam->grade)
                    @php $gradeName = $exam->grade->name ?? ''; @endphp
                    {{ text('exam_result.badge_grade', ['grade' => $gradeName]) }}
                    @else
                    {{ text('exam_result.badge') }}
                    @endif
                </div>
                <h1 class="text-3xl sm:text-5xl font-black italic uppercase tracking-tight text-white leading-tight">
                    {{ $exam->name ?? text('exam_result.title_fallback') }}
                </h1>
                <p class="text-xs sm:text-sm font-semibold text-white/80">
                    {{ text('exam_result.desc') }}
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
                        <span class="text-4xs font-black uppercase tracking-widest text-amber-200">{{ text('exam_result.score') }}</span>
                    </div>
                </div>
                <div class="text-xs font-black uppercase tracking-widest text-white mt-2">
                    {{ text('exam_result.correct_of', ['count' => $correctCount, 'total' => $totalCount]) }}
                </div>
                @if($scorePercent >= $passingScore)
                <span class="mt-1 text-[10px] font-black uppercase tracking-widest bg-amber-400/20 text-amber-200 px-2 py-0.5 rounded-full">{{ text('exam_result.passed') }}</span>
                @else
                <span class="mt-1 text-[10px] font-black uppercase tracking-widest bg-white/10 text-white/60 px-2 py-0.5 rounded-full">{{ text('exam_result.not_passed') }}</span>
                @endif
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
                        {{ text('exam_result.breakdown') }}
                    </h3>
                </div>
                <div class="flex items-center gap-1.5 flex-wrap">
                    <button type="button" class="filter-tab-btn active" onclick="filterQuestions('all', this)">{{ text('exam_result.filter_all') }} ({{ $totalCount }})</button>
                    <button type="button" class="filter-tab-btn" onclick="filterQuestions('correct', this)">{{ text('exam_result.filter_correct') }} ({{ $correctCount }})</button>
                    <button type="button" class="filter-tab-btn" onclick="filterQuestions('wrong', this)">{{ text('exam_result.filter_wrong') }} ({{ $wrongCount }})</button>
                </div>
            </div>

            <!-- Questions List -->
            <div class="space-y-4">
                @foreach($questionResults as $index => $res)
                @php
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
                            {{ text('exam_result.question', ['num' => $index + 1]) }}
                        </span>
                        <span class="inline-flex items-center gap-1 text-3xs font-black uppercase tracking-widest px-3 py-1 rounded-full {{ $isCorrect ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                            @if($isCorrect)
                            <span class="material-symbols-outlined !text-sm">check_circle</span> {{ text('exam_result.correct_badge') }}
                            @else
                            <span class="material-symbols-outlined !text-sm">cancel</span> {{ text('exam_result.incorrect_badge') }}
                            @endif
                        </span>
                    </div>

                    <h4 class="font-bold text-sm sm:text-base text-[rgb(var(--on-surface))] mb-4 leading-relaxed">
                        {!! $res['question_text'] ?: text('exam_result.question_content') !!}
                    </h4>

                    <!-- Answer Options Comparison -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-3 border-t border-[rgb(var(--surface-container-high))/0.6] text-xs font-semibold">
                        <div class="p-3 rounded-xl {{ $isCorrect ? 'bg-emerald-50 text-emerald-900 border border-emerald-200' : 'bg-rose-50 text-rose-900 border border-rose-200' }}">
                            <span class="text-4xs font-black uppercase tracking-widest block opacity-70 mb-0.5">{{ text('exam_result.your_answer') }}</span>
                            <span class="font-black text-sm uppercase">{{ $userLetter ? strtoupper($userLetter) : text('exam_result.no_answer') }}</span>
                            @if($userAnswerText && $userLetter)
                            <span class="block text-xs mt-0.5 font-bold">{!! $userAnswerText !!}</span>
                            @endif
                        </div>

                        <div class="p-3 rounded-xl bg-emerald-50 text-emerald-900 border border-emerald-200">
                            <span class="text-4xs font-black uppercase tracking-widest block opacity-70 mb-0.5">{{ text('exam_result.correct_answer') }}</span>
                            <span class="font-black text-sm uppercase">{{ strtoupper($correctLetter) }}</span>
                            @if($correctAnswerText)
                            <span class="block text-xs mt-0.5 font-bold">{!! $correctAnswerText !!}</span>
                            @endif
                        </div>
                    </div>

                    @if($res['explanation_video_url'])
                    <div class="mt-3 pt-3 border-t border-[rgb(var(--surface-container-high))/0.6]">
                        <span class="inline-flex items-center gap-1.5 text-4xs font-black uppercase tracking-widest text-[rgb(var(--primary))] mb-1">
                            <span class="material-symbols-outlined !text-sm">play_circle</span>
                            {{ text('exam_result.explanation_video') }}
                        </span>
                        {!! renderVideoEmbed($res['explanation_video_url']) !!}
                    </div>
                    @endif
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
                    <span class="text-3xs font-black uppercase tracking-widest text-amber-600">{{ text('exam_result.rewards') }}</span>
                    <h4 class="font-black text-2xl text-[rgb(var(--on-surface))] mt-0.5">{{ text('exam_result.xp_stars', ['count' => $starsEarned]) }}</h4>
                    <p class="text-xs font-semibold text-[rgb(var(--on-surface))/0.5] mt-1">
                        {{ text('exam_result.rewards_desc') }}
                    </p>
                </div>
            </div>

            <!-- Quick Action Buttons -->
            <div class="sidebar-widget-card space-y-3">
                <a href="{{ route('exam.start', $exam) }}" class="w-full py-3 bg-[rgb(var(--primary))] text-white rounded-full font-black text-xs uppercase tracking-widest flex items-center justify-center gap-2 no-underline active:scale-95 transition-all shadow-md shadow-[rgb(var(--primary))/0.2]">
                    <span class="material-symbols-outlined !text-lg">refresh</span>
                    <span>{{ text('exam_result.retake') }}</span>
                </a>

                <a href="{{ route('exam') }}" class="w-full py-3 bg-[rgb(var(--surface-container-high))] text-[rgb(var(--on-surface))] hover:bg-[rgb(var(--surface-container-high))/0.8] rounded-full font-black text-xs uppercase tracking-widest flex items-center justify-center gap-2 no-underline active:scale-95 transition-all">
                    <span>{{ text('exam_result.back_exams') }}</span>
                    <span class="material-symbols-outlined !text-lg">arrow_forward</span>
                </a>
            </div>

        </aside>

    </div>

</div>
@endsection

@push('scripts')
<script src="{{ asset('js/exam-result.js') }}?v={{ filemtime(public_path('js/exam-result.js')) }}"></script>
@endpush
