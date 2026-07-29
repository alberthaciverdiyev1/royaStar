@extends('layouts.app')
@section('title', $topic->name . ' - Lessons & Learning Path')

@push('styles')
<style>
/* Celestial Hero Banner */
.topic-banner-hero {
    position: relative;
    border-radius: 2.5rem;
    padding: 3rem 2rem;
    background: radial-gradient(circle at 80% 20%, rgba(255, 255, 255, 0.15) 0%, transparent 60%),
                linear-gradient(135deg, rgb(var(--primary)) 0%, rgb(var(--secondary)) 100%);
    color: #ffffff;
    box-shadow: 0 25px 50px -12px rgba(var(--primary), 0.35);
    overflow: hidden;
    margin-bottom: 2rem;
}
@media (min-width: 768px) {
    .topic-banner-hero {
        border-radius: 3.5rem;
        padding: 4rem 3.5rem;
    }
}

/* Search Bar */
.topic-search-box {
    background-color: rgba(var(--surface-container-lowest), 1);
    border: 2px solid rgba(var(--surface-container-high), 1);
    border-radius: 9999px;
    padding: 0.6rem 1.25rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
    transition: all 0.25s ease;
}
.topic-search-box:focus-within {
    border-color: rgb(var(--primary));
    box-shadow: 0 8px 30px rgba(var(--primary), 0.15);
}

/* Lesson Cards & Path */
.lesson-card-item {
    background-color: rgba(var(--surface-container-lowest), 1);
    border: 2px solid rgba(var(--surface-container-high), 1);
    border-radius: 1.75rem;
    padding: 1.5rem;
    transition: all 0.3s cubic-bezier(0.22, 1, 0.36, 1);
    position: relative;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
}
.lesson-card-item:hover {
    transform: translateY(-4px);
    border-color: rgba(var(--primary), 0.35);
    box-shadow: 0 14px 35px rgba(var(--primary), 0.12);
}
.lesson-card-item.active-step {
    border-color: rgb(var(--primary));
    background: linear-gradient(135deg, rgba(var(--primary), 0.04) 0%, rgba(var(--surface-container-lowest), 1) 100%);
}

.lesson-step-badge {
    width: 3.25rem;
    height: 3.25rem;
    border-radius: 1.25rem;
    background: rgba(var(--primary), 0.1);
    color: rgb(var(--primary));
    font-weight: 900;
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: all 0.3s ease;
}
.lesson-card-item:hover .lesson-step-badge {
    background: rgb(var(--primary));
    color: #ffffff;
    transform: scale(1.1) rotate(-6deg);
}

.sidebar-widget-card {
    background-color: rgba(var(--surface-container-lowest), 1);
    border: 2px solid rgba(var(--surface-container-high), 1);
    border-radius: 1.75rem;
    padding: 1.5rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
}
</style>
@endpush

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
                <a href="{{ route('topics') }}" class="text-white hover:underline no-underline">Topics</a>
                <span>/</span>
                <span class="text-amber-300 font-extrabold">{{ $topic->subject?->name ?? 'Subject' }}</span>
            </div>

            <!-- Title & Details -->
            <div class="space-y-1.5">
                <h1 class="text-3xl sm:text-5xl font-black italic uppercase tracking-tight text-white leading-tight">
                    {{ $topic->name }}
                </h1>
                <p class="text-xs sm:text-sm font-semibold text-white/80">
                    Explore {{ $lessons->total() }} interactive lessons to master this topic and earn Star XP points!
                </p>
            </div>

            <!-- Stats & Quick Actions -->
            <div class="flex items-center gap-3 pt-2 flex-wrap">
                <span class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full bg-white/20 backdrop-blur-md text-xs font-black text-white">
                    <span class="material-symbols-outlined !text-sm">import_contacts</span>
                    {{ $lessons->total() }} Lessons
                </span>
                <span class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full bg-amber-400/30 text-amber-200 text-xs font-black">
                    <span class="material-symbols-outlined !text-sm" style="font-variation-settings:'FILL' 1">stars</span>
                    Earn Star XP
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
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search lessons in {{ $topic->name }}..." class="bg-transparent border-none text-xs sm:text-sm font-bold text-[rgb(var(--on-surface))] placeholder-[rgb(var(--on-surface))/0.4] focus:outline-none w-full" data-auto-search />
                    @if($search)
                    <a href="{{ route('topics.detail', $topic) }}" class="material-symbols-outlined !text-lg text-[rgb(var(--on-surface))/0.5] hover:text-[rgb(var(--on-surface))] no-underline">close</a>
                    @endif
                </form>

                <div class="text-3xs font-black uppercase tracking-widest text-[rgb(var(--on-surface))/0.5]">
                    Showing {{ $lessons->count() }} of {{ $lessons->total() }} Lessons
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
                                        Lesson {{ $stepNum }}
                                    </span>
                                    @if($isActive)
                                    <span class="text-4xs font-black uppercase tracking-widest text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-full flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 animate-ping"></span>
                                        Recommended Next
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
                                <span>{{ $isActive ? 'Start Learning' : 'Study Lesson' }}</span>
                                <span class="material-symbols-outlined !text-lg">arrow_forward</span>
                            </a>
                        </div>
                    </div>
                </div>
                @empty
                <div class="bg-[rgb(var(--surface-container-lowest))] border-2 border-dashed border-[rgb(var(--surface-container-high))] rounded-3xl p-12 text-center space-y-3">
                    <span class="material-symbols-outlined !text-6xl text-[rgb(var(--on-surface))/0.15]">menu_book</span>
                    <h4 class="font-black text-base text-[rgb(var(--on-surface))] uppercase">No Lessons Found</h4>
                    <p class="text-xs font-semibold text-[rgb(var(--on-surface))/0.5] max-w-sm mx-auto">
                        {{ $search ? 'Try clearing your search query to see all lessons.' : 'No lessons have been added to this topic yet.' }}
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
                        <h4 class="font-black text-sm uppercase tracking-wide text-[rgb(var(--on-surface))]">Topic Overview</h4>
                        <p class="text-3xs font-bold text-[rgb(var(--on-surface))/0.5]">{{ $topic->subject?->name ?? 'Grammar Module' }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2 text-center pt-2">
                    <div class="bg-[rgb(var(--surface-container-high))/0.4] rounded-2xl p-3">
                        <div class="text-lg font-black text-[rgb(var(--on-surface))]">{{ $lessons->total() }}</div>
                        <div class="text-4xs font-black uppercase text-[rgb(var(--on-surface))/0.5]">Total Lessons</div>
                    </div>
                    <div class="bg-[rgb(var(--surface-container-high))/0.4] rounded-2xl p-3">
                        <div class="text-lg font-black text-amber-600">+100</div>
                        <div class="text-4xs font-black uppercase text-[rgb(var(--on-surface))/0.5]">XP Potential</div>
                    </div>
                </div>
            </div>

            <!-- Return to Topics Link -->
            <a href="{{ route('topics') }}" class="sidebar-widget-card flex items-center justify-between gap-3 text-[rgb(var(--on-surface))] hover:border-[rgb(var(--primary))/0.3] no-underline transition-all group">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined !text-2xl text-[rgb(var(--primary))] group-hover:-translate-x-1 transition-transform">arrow_back</span>
                    <div>
                        <h4 class="font-black text-xs uppercase tracking-wide">All Topics</h4>
                        <p class="text-3xs font-bold text-[rgb(var(--on-surface))/0.5]">Return to main learning path</p>
                    </div>
                </div>
                <span class="material-symbols-outlined !text-lg text-[rgb(var(--on-surface))/0.4]">chevron_right</span>
            </a>

        </aside>

    </div>

</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('[data-auto-search]').forEach(function(input) {
    var timer;
    input.addEventListener('input', function() {
        clearTimeout(timer);
        timer = setTimeout(function() { input.closest('form').submit(); }, 350);
    });
});
</script>
@endpush
