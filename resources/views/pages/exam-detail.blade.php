@extends('layouts.app')
@section('title', text('exam_detail.page_title', ['name' => $exam->name]))

<link href="{{ asset('css/exam-detail.css') }}?v={{ filemtime(public_path('css/exam-detail.css')) }}" rel="stylesheet">

@section('content')
<div class="exam-detail-wrapper space-y-4 sm:space-y-6">

    <!-- Back link -->
    <div class="flex items-center gap-2">
        <a href="{{ route('exam') }}" class="inline-flex items-center gap-1.5 text-3xs font-black uppercase tracking-widest text-[rgb(var(--on-surface-variant))] hover:text-[rgb(var(--primary))] transition-all no-underline">
            <span class="material-symbols-outlined !text-sm">arrow_back</span>
            {{ text('exam_detail.back') }}
        </a>
    </div>

    <!-- Exam Info Card — polished like quiz question card -->
    <div class="detail-card space-y-5">
        <div class="flex items-start justify-between gap-4">
            <div class="space-y-1.5">
                @if($exam->grade)
                @php $gradeName = $exam->grade->name ?? ''; @endphp
                <div class="flex items-center gap-1.5 text-[10px] font-black uppercase tracking-widest text-[rgb(var(--primary))]">
                    <span class="w-1.5 h-1.5 rounded-full bg-[rgb(var(--primary))]"></span>
                    <span>{{ text('exam_detail.badge', ['grade' => $gradeName]) }}</span>
                </div>
                @endif
                <h1 class="text-xl sm:text-2xl md:text-3xl font-black italic uppercase tracking-tight text-[rgb(var(--on-surface))] leading-tight">
                    {{ $exam->name }}
                </h1>
                @if($exam->description)
                <p class="text-sm font-semibold text-[rgb(var(--on-surface-variant))]">{{ $exam->description }}</p>
                @endif
            </div>
            <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-[rgb(var(--surface))] text-3xs font-black uppercase tracking-widest text-[rgb(var(--on-surface-variant))] flex-shrink-0">
                <span class="material-symbols-outlined !text-sm">checklist</span>
                {{ $exam->questions_count }} {{ text('exam_detail.questions') }}
            </div>
        </div>

        @if($pastScore !== null)
        <div class="space-y-1.5">
            <div class="flex justify-between items-end text-3xs font-black uppercase tracking-widest">
                <span class="text-[rgb(var(--on-surface))/0.5]">{{ text('exam_detail.last_score') }}</span>
                <span class="text-[rgb(var(--primary))]">{{ $pastScore }}%</span>
            </div>
            <div class="h-2.5 w-full bg-[rgb(var(--surface))] rounded-full overflow-hidden border border-[rgb(var(--surface-container-high))] p-0.5">
                <div class="h-full rounded-full bg-[rgb(var(--primary))] transition-all duration-500" style="width: {{ $pastScore }}%"></div>
            </div>
        </div>
        @endif

        <div class="flex items-center gap-3 pt-2">
            <a href="{{ route('exam.start', $exam) }}" class="flex-1 sm:flex-none py-3 px-8 bg-[rgb(var(--primary))] text-white rounded-full font-black text-xs uppercase tracking-widest text-center shadow-lg shadow-[rgb(var(--primary))/0.2] active:scale-95 transition-all inline-flex items-center justify-center gap-2 no-underline hover:opacity-95">
                <span class="material-symbols-outlined !text-lg">{{ $pastScore !== null ? 'refresh' : 'rocket_launch' }}</span>
                {{ $pastScore !== null ? text('exam_detail.retake') : text('exam_detail.start') }}
            </a>
            @if($exam->duration_minutes)
            <span class="flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-[rgb(var(--surface))] text-3xs font-black uppercase tracking-widest text-[rgb(var(--on-surface-variant))]">
                <span class="material-symbols-outlined !text-sm">schedule</span>
                {{ $exam->duration_minutes }} {{ text('exam_detail.min') }}
            </span>
            @endif
        </div>
    </div>

    @if($pastScore !== null)
    <div class="text-center">
        <a href="{{ route('exam.result', $exam) }}" class="inline-flex items-center gap-1.5 text-3xs font-black uppercase tracking-widest text-[rgb(var(--primary))] hover:opacity-80 transition-all">
            {{ text('exam_detail.view_result') }}
            <span class="material-symbols-outlined !text-sm">arrow_forward</span>
        </a>
    </div>
    @endif

</div>
@endsection
