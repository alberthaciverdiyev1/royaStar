@extends('layouts.app')
@section('title', $exam->name . ' - Exam Detail')

@section('content')
<section class="max-w-content mx-auto px-4 py-8 md:py-12">
    <header class="mb-8">
        <a href="{{ route('exam') }}" class="inline-flex items-center gap-2 text-3xs font-black uppercase tracking-widest text-[rgb(var(--on-surface-variant))] hover:text-[rgb(var(--primary))] transition-all mb-6 no-underline">
            <span class="material-symbols-outlined !text-sm">arrow_back</span>
            Back to Exams
        </a>
        <h1 class="text-3xl md:text-4xl font-black text-[rgb(var(--on-surface))] uppercase tracking-tight">{{ $exam->name }}</h1>
        @if($exam->description)
        <p class="text-sm font-bold text-[rgb(var(--on-surface-variant))] mt-2">{{ $exam->description }}</p>
        @endif
    </header>

    <div class="grid gap-6">
        {{-- Exam Info Card --}}
        <div class="bg-[rgb(var(--surface-container-lowest))] rounded-3xl p-6 md:p-8 border border-[rgb(var(--surface-container-high))] shadow-lg shadow-black/5">
            <div class="flex items-start justify-between mb-5">
                <div>
                    @if($exam->grade)
                    @php $gradeName = $exam->grade->name[app()->getLocale()] ?? $exam->grade->name['az'] ?? ''; @endphp
                    <span class="text-3xs font-black uppercase tracking-widest text-[rgb(var(--primary))]">{{ $gradeName }}</span>
                    @endif
                    <h3 class="font-black uppercase text-lg mt-1 text-[rgb(var(--on-surface))]">{{ $exam->name }}</h3>
                </div>
                <span class="flex items-center gap-1 text-2xs font-black uppercase tracking-widest text-[rgb(var(--on-surface-variant))]">
                    <span class="material-symbols-outlined !text-sm">checklist</span>
                    {{ $exam->questions_count }} questions
                </span>
            </div>

            @if($pastScore !== null)
            <div class="mb-5">
                <div class="h-2 w-full bg-[rgb(var(--surface-container-high))] rounded-full overflow-hidden p-0.5">
                    <div class="h-full rounded-full bg-[rgb(var(--primary))]" style="width: {{ $pastScore }}%"></div>
                </div>
                <span class="text-3xs font-black uppercase tracking-widest text-[rgb(var(--on-surface-variant))] mt-1">Last Score: {{ $pastScore }}%</span>
            </div>
            @endif

            <div class="mt-6 flex items-center gap-3">
                <a href="{{ route('exam.start', $exam) }}" class="flex-1 py-3 px-6 bg-[rgb(var(--primary))] text-white rounded-full font-black text-xs uppercase tracking-widest text-center shadow-lg shadow-[rgb(var(--primary))/0.2] active:scale-95 transition-all inline-flex items-center justify-center gap-2">
                    {{ $pastScore !== null ? 'Retake Exam' : 'Start Exam' }}
                    <span class="material-symbols-outlined !text-lg">rocket_launch</span>
                </a>
                @if($exam->duration_minutes)
                <span class="flex items-center gap-1 text-3xs font-black uppercase tracking-widest text-[rgb(var(--on-surface-variant))]">
                    <span class="material-symbols-outlined !text-base">schedule</span>
                    {{ $exam->duration_minutes }} min
                </span>
                @endif
            </div>
        </div>

        @if($pastScore !== null)
        <div class="text-center mt-4">
            <a href="{{ route('exam.result', $exam) }}" class="text-3xs font-black uppercase tracking-widest text-[rgb(var(--primary))] hover:underline">
                View Last Result →
            </a>
        </div>
        @endif
    </div>
</section>
@endsection
