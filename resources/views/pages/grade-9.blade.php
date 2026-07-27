@extends('layouts.app')
@section('title', 'Grade 9 - Mock Exam')

@section('content')
<section class="max-w-content mx-auto px-4 py-8 md:py-12">
    <header class="mb-8">
        <a href="{{ route('exam') }}" class="inline-flex items-center gap-2 text-3xs font-black uppercase tracking-widest text-[rgb(var(--on-surface-variant))] hover:text-[rgb(var(--primary))] transition-all mb-6 no-underline">
            <span class="material-symbols-outlined !text-sm">arrow_back</span>
            Back to Exams
        </a>
        <h1 class="text-3xl md:text-4xl font-black text-[rgb(var(--on-surface))] uppercase tracking-tight">Grade 9 Mock Exam</h1>
        <p class="text-sm font-bold text-[rgb(var(--on-surface-variant))] mt-2">Complete all sections to earn your certificate.</p>
    </header>

    <div class="grid gap-6">
        <div class="bg-[rgb(var(--surface-container-lowest))] rounded-3xl p-6 md:p-8 border border-[rgb(var(--surface-container-high))] shadow-lg shadow-black/5">
            <div class="flex items-start justify-between mb-5">
                <div>
                    <span class="text-3xs font-black uppercase tracking-widest text-[rgb(var(--primary))]">Section 01</span>
                    <h3 class="font-black uppercase text-lg mt-1 text-[rgb(var(--on-surface))]">Reading Comprehension</h3>
                </div>
                <span class="flex items-center gap-1 text-2xs font-black uppercase tracking-widest text-[rgb(var(--on-surface-variant))]">
                    <span class="material-symbols-outlined !text-sm">checklist</span>
                    0/10
                </span>
            </div>
            <div class="h-2 w-full bg-[rgb(var(--surface-container-high))] rounded-full overflow-hidden p-0.5">
                <div class="h-full rounded-full bg-[rgb(var(--primary))]" style="width: 0%"></div>
            </div>
            <div class="mt-6 flex items-center gap-3">
                <a href="{{ route('final-exam') }}" class="flex-1 py-3 px-6 bg-[rgb(var(--primary))] text-white rounded-full font-black text-xs uppercase tracking-widest text-center shadow-lg shadow-[rgb(var(--primary))/0.2] active:scale-95 transition-all inline-flex items-center justify-center gap-2">
                    Start Exam
                    <span class="material-symbols-outlined !text-lg">rocket_launch</span>
                </a>
                <span class="flex items-center gap-1 text-3xs font-black uppercase tracking-widest text-[rgb(var(--on-surface-variant))]">
                    <span class="material-symbols-outlined !text-base">schedule</span>
                    30 min
                </span>
            </div>
        </div>

        <div class="bg-[rgb(var(--surface-container-lowest))] rounded-3xl p-6 md:p-8 border border-[rgb(var(--surface-container-high))] shadow-lg shadow-black/5">
            <div class="flex items-start justify-between mb-5">
                <div>
                    <span class="text-3xs font-black uppercase tracking-widest text-[rgb(var(--primary))]">Section 02</span>
                    <h3 class="font-black uppercase text-lg mt-1 text-[rgb(var(--on-surface))]">Vocabulary & Grammar</h3>
                </div>
                <span class="flex items-center gap-1 text-2xs font-black uppercase tracking-widest text-[rgb(var(--on-surface-variant))]">
                    <span class="material-symbols-outlined !text-sm">checklist</span>
                    0/15
                </span>
            </div>
            <div class="h-2 w-full bg-[rgb(var(--surface-container-high))] rounded-full overflow-hidden p-0.5">
                <div class="h-full rounded-full bg-[rgb(var(--primary))]" style="width: 0%"></div>
            </div>
            <div class="mt-6 flex items-center gap-3">
                <a href="{{ route('final-exam') }}" class="flex-1 py-3 px-6 bg-[rgb(var(--primary))] text-white rounded-full font-black text-xs uppercase tracking-widest text-center shadow-lg shadow-[rgb(var(--primary))/0.2] active:scale-95 transition-all inline-flex items-center justify-center gap-2">
                    Start Exam
                    <span class="material-symbols-outlined !text-lg">rocket_launch</span>
                </a>
                <span class="flex items-center gap-1 text-3xs font-black uppercase tracking-widest text-[rgb(var(--on-surface-variant))]">
                    <span class="material-symbols-outlined !text-base">schedule</span>
                    45 min
                </span>
            </div>
        </div>

        <div class="bg-[rgb(var(--surface-container-lowest))] rounded-3xl p-6 md:p-8 border border-[rgb(var(--surface-container-high))] shadow-lg shadow-black/5">
            <div class="flex items-start justify-between mb-5">
                <div>
                    <span class="text-3xs font-black uppercase tracking-widest text-[rgb(var(--primary))]">Section 03</span>
                    <h3 class="font-black uppercase text-lg mt-1 text-[rgb(var(--on-surface))]">Writing Task</h3>
                </div>
                <span class="flex items-center gap-1 text-2xs font-black uppercase tracking-widest text-[rgb(var(--on-surface-variant))]">
                    <span class="material-symbols-outlined !text-sm">checklist</span>
                    0/1
                </span>
            </div>
            <div class="h-2 w-full bg-[rgb(var(--surface-container-high))] rounded-full overflow-hidden p-0.5">
                <div class="h-full rounded-full bg-[rgb(var(--primary))]" style="width: 0%"></div>
            </div>
            <div class="mt-6 flex items-center gap-3">
                <a href="{{ route('final-exam') }}" class="flex-1 py-3 px-6 bg-[rgb(var(--primary))] text-white rounded-full font-black text-xs uppercase tracking-widest text-center shadow-lg shadow-[rgb(var(--primary))/0.2] active:scale-95 transition-all inline-flex items-center justify-center gap-2">
                    Start Exam
                    <span class="material-symbols-outlined !text-lg">rocket_launch</span>
                </a>
                <span class="flex items-center gap-1 text-3xs font-black uppercase tracking-widest text-[rgb(var(--on-surface-variant))]">
                    <span class="material-symbols-outlined !text-base">schedule</span>
                    20 min
                </span>
            </div>
        </div>
    </div>

    <div class="text-3xs font-black uppercase tracking-widest text-[rgb(var(--on-surface-variant))/0.5] mt-10 text-center">
        Past Papers
    </div>

    <div class="grid gap-3 mt-4">
        <div class="past-paper-item">
            <span class="material-symbols-outlined !text-xl text-[rgb(var(--primary))]">description</span>
            <div class="flex-1 min-w-0">
                <h4 class="font-black text-xs uppercase text-[rgb(var(--on-surface))] truncate">2025 Grade 9 Mock Exam</h4>
                <p class="text-3xs font-bold text-[rgb(var(--on-surface-variant))/0.6] mt-0.5">Completed</p>
            </div>
            <span class="flex items-center gap-1 text-3xs font-black uppercase tracking-widest text-[rgb(var(--on-surface-variant))]">
                <span class="material-symbols-outlined !text-base">check_circle</span>
                85%
            </span>
        </div>
        <div class="past-paper-item">
            <span class="material-symbols-outlined !text-xl text-[rgb(var(--surface-container-high))]">description</span>
            <div class="flex-1 min-w-0">
                <h4 class="font-black text-xs uppercase tracking-wide text-[rgb(var(--on-surface))] truncate">2024 Grade 9 Mock Exam</h4>
                <p class="text-3xs font-bold text-[rgb(var(--on-surface-variant))/0.6] mt-0.5">Available</p>
            </div>
            <a href="{{ route('final-exam') }}" class="text-3xs font-black uppercase tracking-widest text-[rgb(var(--primary))] hover:underline">Start</a>
        </div>
    </div>
</section>
@endsection
