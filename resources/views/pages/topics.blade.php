@extends('layouts.app')
@section('title', 'Topics - Learning Universe')

<link href="{{ asset('css/topics.css') }}?v={{ filemtime(public_path('css/topics.css')) }}" rel="stylesheet">

@section('content')
<div class="w-full max-w-[1400px] mx-auto px-4 md:px-8 py-6 space-y-8">

    <!-- Wide Universe Hero Banner -->
    <section class="universe-banner group">
        <div class="absolute -top-12 -right-12 text-white/10 pointer-events-none transition-transform duration-1000 group-hover:rotate-45">
            <span class="material-symbols-outlined !text-[280px]">auto_awesome</span>
        </div>
        <div class="absolute -bottom-12 -left-12 text-white/10 pointer-events-none">
            <span class="material-symbols-outlined !text-[240px]">rocket_launch</span>
        </div>

        <div class="relative z-10 space-y-3 max-w-2xl">
            <div class="inline-flex items-center gap-1.5 px-4 py-1 rounded-full bg-white/20 backdrop-blur-md text-3xs font-black uppercase tracking-widest text-white">
                <span class="material-symbols-outlined !text-xs">auto_awesome</span>
                Learning Galaxies
            </div>
            <h1 class="text-3xl sm:text-5xl font-black italic uppercase tracking-tight text-white leading-tight">
                Your Learning Universe 🌟
            </h1>
            <p class="text-xs sm:text-sm font-semibold text-white/80 leading-relaxed">
                Embark on your journey through the grammar galaxies. Every topic mastered brings you closer to earning Star XP badges!
            </p>
        </div>
    </section>

    <!-- Spacious 2-Column Dashboard Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

        <!-- MAIN COLUMN (8 cols) -->
        <main class="lg:col-span-8 space-y-6">

            <!-- Search & Filter Bar -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <form method="GET" action="{{ route('topics') }}" class="search-input-wrapper w-full sm:max-w-md">
                    <span class="material-symbols-outlined !text-xl text-[rgb(var(--primary))] opacity-70">search</span>
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search topics..." class="bg-transparent border-none text-xs sm:text-sm font-bold text-[rgb(var(--on-surface))] placeholder-[rgb(var(--on-surface))/0.4] focus:outline-none w-full" data-auto-search />
                    @if($search)
                    <a href="{{ route('topics') }}" class="material-symbols-outlined !text-lg text-[rgb(var(--on-surface))/0.5] hover:text-[rgb(var(--on-surface))] no-underline">close</a>
                    @endif
                </form>

                <div class="text-3xs font-black uppercase tracking-widest text-[rgb(var(--on-surface))/0.5]">
                    Showing {{ $topics->count() }} of {{ $topics->total() }} Topics
                </div>
            </div>

            <!-- Topics Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                @forelse($topics as $i => $topic)
                <x-card
                    href="{{ route('topics.detail', $topic) }}"
                    badgeText="Topic {{ str_pad($topics->firstItem() + $i, 2, '0', STR_PAD_LEFT) }}"
                    title="{{ $topic->name }}"
                    description="Grammar Module"
                    progress="0"
                    iconName="{{ ['star', 'bolt', 'rocket_launch', 'auto_awesome', 'psychology', 'menu_book'][$i % 6] }}"
                />
                @empty
                <div class="col-span-full bg-[rgb(var(--surface-container-lowest))] border-2 border-dashed border-[rgb(var(--surface-container-high))] rounded-3xl p-12 text-center space-y-3">
                    <span class="material-symbols-outlined !text-6xl text-[rgb(var(--on-surface))/0.15]">rocket_launch</span>
                    <h4 class="font-black text-base text-[rgb(var(--on-surface))] uppercase">No Topics Found</h4>
                    <p class="text-xs font-semibold text-[rgb(var(--on-surface))/0.5] max-w-sm mx-auto">
                        {{ $search ? 'No topics match your search query.' : 'No topics available yet.' }}
                    </p>
                </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($topics->hasPages())
            <div class="pt-4 flex justify-center">
                {{ $topics->appends(['search' => $search])->links('vendor.pagination.custom') }}
            </div>
            @endif
        </main>

        <!-- SIDEBAR COLUMN (4 cols) -->
        <aside class="lg:col-span-4 space-y-6 lg:sticky lg:top-24">

            <!-- Student Star Progress Card -->
            <div class="sidebar-widget-card space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-amber-400/20 text-amber-600 flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined !text-2xl" style="font-variation-settings:'FILL' 1">stars</span>
                    </div>
                    <div>
                        <h4 class="font-black text-sm uppercase tracking-wide text-[rgb(var(--on-surface))]">Star Explorer Level</h4>
                        <p class="text-3xs font-bold text-[rgb(var(--on-surface))/0.5]">Complete topics to unlock new ranks</p>
                    </div>
                </div>

                @auth
                @php
                    $totalStars = app(\App\Modules\Star\Services\StarService::class)->getUserTotalStars(auth()->id());
                    $level = max(1, floor($totalStars / 50) + 1);
                @endphp
                <div class="bg-[rgb(var(--surface-container-high))/0.5] rounded-2xl p-3.5 flex items-center justify-between">
                    <div>
                        <span class="text-3xs font-black uppercase tracking-widest text-[rgb(var(--primary))]">Level {{ $level }}</span>
                        <div class="text-base font-black text-[rgb(var(--on-surface))]">{{ $totalStars }} Stars</div>
                    </div>
                    <a href="{{ route('achievements') }}" class="px-3.5 py-1.5 bg-[rgb(var(--primary))] text-white rounded-full text-3xs font-black uppercase tracking-widest no-underline hover:opacity-95">
                        View Badges
                    </a>
                </div>
                @else
                <a href="{{ route('login') }}" class="w-full py-3 bg-[rgb(var(--primary))] text-white rounded-full font-black text-xs uppercase tracking-widest flex items-center justify-center gap-2 no-underline shadow-lg shadow-[rgb(var(--primary))/0.2]">
                    <span>Log In to Save Progress</span>
                    <span class="material-symbols-outlined !text-lg">arrow_forward</span>
                </a>
                @endauth
            </div>

            <!-- Quick Exam Banner -->
            <div class="sidebar-widget-card bg-gradient-to-br from-indigo-600 to-violet-700 text-white border-none space-y-4 shadow-xl shadow-indigo-500/20">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined !text-3xl text-amber-300">quiz</span>
                    <div>
                        <h4 class="font-black text-base uppercase tracking-tight">Grade Final Exams</h4>
                        <p class="text-3xs font-semibold text-white/80">Test your full knowledge across grade levels</p>
                    </div>
                </div>
                <a href="{{ route('exam') }}" class="w-full py-3 bg-white text-indigo-950 rounded-full font-black text-xs uppercase tracking-widest flex items-center justify-center gap-2 no-underline active:scale-95 transition-all shadow-md">
                    <span>Take Final Exam</span>
                    <span class="material-symbols-outlined !text-lg">arrow_forward</span>
                </a>
            </div>

            <!-- Daily Study Tip -->
            <div class="sidebar-widget-card space-y-2.5">
                <div class="flex items-center gap-2 text-amber-600">
                    <span class="material-symbols-outlined !text-xl" style="font-variation-settings:'FILL' 1">lightbulb</span>
                    <h4 class="font-black text-xs uppercase tracking-wide">Daily Learning Tip</h4>
                </div>
                <p class="text-xs font-semibold text-[rgb(var(--on-surface))/0.6] leading-relaxed">
                    Study 1 topic and complete its knowledge check quiz every day to keep your 7-day streak active!
                </p>
            </div>

        </aside>

    </div>

</div>
@endsection

@push('scripts')
<script src="{{ asset('js/topics.js') }}?v={{ filemtime(public_path('js/topics.js')) }}"></script>
@endpush
