@extends('layouts.app')
@section('title', text('subtopics.page_title', ['topic' => $topic->name]))

<link href="{{ asset('css/subtopics.css') }}?v={{ filemtime(public_path('css/subtopics.css')) }}" rel="stylesheet">

@section('content')
<div class="w-full max-w-[1400px] mx-auto px-4 md:px-8 py-6 space-y-8">

    <!-- Topic Hero Banner -->
    <section class="topic-banner-hero group">
        <div class="absolute -top-12 -right-12 text-white/10 pointer-events-none transition-transform duration-1000 group-hover:rotate-45">
            <span class="material-symbols-outlined !text-[280px]">menu_book</span>
        </div>
        <div class="absolute -bottom-10 -left-10 text-white/10 pointer-events-none">
            <span class="material-symbols-outlined !text-[240px]">rocket_launch</span>
        </div>

        <div class="relative z-10 space-y-4 max-w-2xl">
            <!-- Breadcrumbs -->
            <div class="flex items-center gap-2 text-3xs font-black uppercase tracking-widest text-white/80">
                <a href="{{ route('topics') }}" class="text-white hover:underline no-underline">{{ text('subtopics.breadcrumb_topics') }}</a>
                <span>/</span>
                <span class="font-extrabold text-amber-300">{{ text('subtopics.breadcrumb_subject') }}</span>
            </div>

            <!-- Title & Details -->
            <div class="space-y-1.5">
                <h1 class="text-3xl sm:text-5xl font-black italic uppercase tracking-tight text-white leading-tight">
                    {{ $topic->name }}
                </h1>
                <p class="text-xs sm:text-sm font-semibold text-white/80">
                    {{ text('subtopics.hero_desc', ['count' => $lessons->total()]) }}
                </p>
            </div>

            <!-- Stats & Quick Actions -->
            <div class="flex items-center gap-3 pt-2 flex-wrap">
                <span class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full bg-white/20 backdrop-blur-md text-xs font-black text-white">
                    <span class="material-symbols-outlined !text-sm">import_contacts</span>
                    {{ text('subtopics.hero_lessons', ['count' => $lessons->total()]) }}
                </span>
                <span class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full bg-amber-400/30 text-amber-200 text-xs font-black">
                    <span class="material-symbols-outlined !text-sm" style="font-variation-settings:'FILL' 1">stars</span>
                    {{ text('subtopics.hero_earn') }}
                </span>
            </div>
        </div>
    </section>

    <!-- Spacious 2-Column Dashboard Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

        <!-- MAIN COLUMN (8 cols) -->
        <main class="lg:col-span-8 space-y-6">

            <!-- Search & Filter Bar -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <form method="GET" action="{{ route('topics.detail', $topic) }}" class="topic-search-box w-full sm:max-w-md">
                    <span class="material-symbols-outlined !text-xl text-[rgb(var(--primary))] opacity-70">search</span>
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="{{ text('subtopics.search_placeholder', ['topic' => $topic->name]) }}" class="bg-transparent border-none text-xs sm:text-sm font-bold text-[rgb(var(--on-surface))] placeholder-[rgb(var(--on-surface))/0.4] focus:outline-none w-full" data-auto-search />
                    @if($search)
                    <a href="{{ route('topics.detail', $topic) }}" class="material-symbols-outlined !text-lg text-[rgb(var(--on-surface))/0.5] hover:text-[rgb(var(--on-surface))] no-underline">close</a>
                    @endif
                </form>

                <div class="text-3xs font-black uppercase tracking-widest text-[rgb(var(--on-surface))/0.5]">
                    {{ text('subtopics.showing', ['count' => $lessons->count(), 'total' => $lessons->total()]) }}
                </div>
            </div>

            <!-- Lessons List -->
            <div class="space-y-4">
                @forelse($lessons as $i => $lesson)
                @php
                    $stepNum = str_pad($lessons->firstItem() + $i, 2, '0', STR_PAD_LEFT);
                    $isActive = ($i === 0 && !$search);
                @endphp
                <div class="lesson-card-item {{ $isActive ? 'active-step' : '' }}">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-start sm:items-center gap-4">
                            <!-- Step Badge -->
                            <div class="lesson-step-badge">
                                {{ $stepNum }}
                            </div>

                            <!-- Lesson Details -->
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-4xs font-black uppercase tracking-widest text-[rgb(var(--primary))] bg-[rgb(var(--primary))/0.1] px-2.5 py-0.5 rounded-full">
                                        {{ text('subtopics.lesson_badge', ['num' => $stepNum]) }}
                                    </span>
                                    @if($isActive)
                                    <span class="text-4xs font-black uppercase tracking-widest text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-full flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 animate-ping"></span>
                                        {{ text('subtopics.lesson_recommended') }}
                                    </span>
                                    @endif
                                </div>
                                <h3 class="text-base sm:text-lg font-black text-[rgb(var(--on-surface))] leading-snug">
                                    {{ $lesson->name }}
                                </h3>
                                @if($lesson->description)
                                <p class="text-xs font-semibold text-[rgb(var(--on-surface))/0.6] line-clamp-2">
                                    {{ $lesson->description }}
                                </p>
                                @endif
                            </div>
                        </div>

                        <!-- Action Button -->
                        <div class="flex items-center gap-3 pt-2 sm:pt-0 border-t sm:border-t-0 border-[rgb(var(--surface-container-high))/0.6] justify-between sm:justify-end flex-shrink-0">
                            <a href="{{ route('lesson', $lesson->id) }}" class="px-5 py-2.5 sm:py-3 bg-[rgb(var(--primary))] hover:opacity-95 text-white rounded-full font-black text-xs uppercase tracking-widest active:scale-95 transition-all flex items-center gap-2 no-underline shadow-lg shadow-[rgb(var(--primary))/0.2]">
                                <span>{{ $isActive ? text('subtopics.lesson_start') : text('subtopics.lesson_study') }}</span>
                                <span class="material-symbols-outlined !text-lg">arrow_forward</span>
                            </a>
                        </div>
                    </div>
                </div>
                @empty
                <div class="bg-[rgb(var(--surface-container-lowest))] border-2 border-dashed border-[rgb(var(--surface-container-high))] rounded-3xl p-12 text-center space-y-3">
                    <span class="material-symbols-outlined !text-6xl text-[rgb(var(--on-surface))/0.15]">menu_book</span>
                    <h4 class="font-black text-base text-[rgb(var(--on-surface))] uppercase">{{ text('subtopics.empty_title') }}</h4>
                    <p class="text-xs font-semibold text-[rgb(var(--on-surface))/0.5] max-w-sm mx-auto">
                        {{ $search ? text('subtopics.empty_search') : text('subtopics.empty_none') }}
                    </p>
                </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($lessons->hasPages())
            <div class="pt-4 flex justify-center">
                {{ $lessons->appends(['search' => $search])->links('vendor.pagination.custom') }}
            </div>
            @endif
        </main>

        <!-- SIDEBAR COLUMN (4 cols) -->
        <aside class="lg:col-span-4 space-y-6 lg:sticky lg:top-24">

            <!-- Topic Stats Widget -->
            <div class="sidebar-widget-card space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-amber-400/20 text-amber-600 flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined !text-2xl" style="font-variation-settings:'FILL' 1">stars</span>
                    </div>
                    <div>
                        <h4 class="font-black text-sm uppercase tracking-wide text-[rgb(var(--on-surface))]">{{ text('subtopics.sidebar_title') }}</h4>
                        <p class="text-3xs font-bold text-[rgb(var(--on-surface))/0.5]">{{ text('subtopics.sidebar_subject') }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2 text-center pt-2">
                    <div class="bg-[rgb(var(--surface-container-high))/0.4] rounded-2xl p-3">
                        <div class="text-lg font-black text-[rgb(var(--on-surface))]">{{ $lessons->total() }}</div>
                        <div class="text-4xs font-black uppercase text-[rgb(var(--on-surface))/0.5]">{{ text('subtopics.sidebar_total') }}</div>
                    </div>
                    <div class="bg-[rgb(var(--surface-container-high))/0.4] rounded-2xl p-3">
                        <div class="text-lg font-black text-amber-600">+100</div>
                        <div class="text-4xs font-black uppercase text-[rgb(var(--on-surface))/0.5]">{{ text('subtopics.sidebar_xp') }}</div>
                    </div>
                </div>
            </div>

            <!-- Return to Topics Link -->
            <a href="{{ route('topics') }}" class="sidebar-widget-card flex items-center justify-between gap-3 text-[rgb(var(--on-surface))] hover:border-[rgb(var(--primary))/0.3] no-underline transition-all group">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined !text-2xl text-[rgb(var(--primary))] group-hover:-translate-x-1 transition-transform">arrow_back</span>
                    <div>
                        <h4 class="font-black text-xs uppercase tracking-wide">{{ text('subtopics.sidebar_back_title') }}</h4>
                        <p class="text-3xs font-bold text-[rgb(var(--on-surface))/0.5]">{{ text('subtopics.sidebar_back_desc') }}</p>
                    </div>
                </div>
                <span class="material-symbols-outlined !text-lg text-[rgb(var(--on-surface))/0.4]">chevron_right</span>
            </a>

        </aside>

    </div>

</div>
@endsection

@push('scripts')
<script src="{{ asset('js/subtopics.js') }}?v={{ filemtime(public_path('js/subtopics.js')) }}"></script>
@endpush
