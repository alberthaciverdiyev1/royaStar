@extends('layouts.app')
@section('title', text('achieve.page_title'))

<link href="{{ asset('css/achievements.css') }}?v={{ filemtime(public_path('css/achievements.css')) }}" rel="stylesheet">

@section('content')
<div class="w-full max-w-[1400px] mx-auto px-4 md:px-8 py-6 space-y-8">

    <!-- Hero Achievement Header -->
    <section class="achieve-hero-banner group">
        <div class="absolute -top-12 -right-12 text-white/10 pointer-events-none transition-transform duration-1000 group-hover:rotate-45">
            <span class="material-symbols-outlined !text-[280px]">military_tech</span>
        </div>
        <div class="absolute -bottom-10 -left-10 text-white/10 pointer-events-none">
            <span class="material-symbols-outlined !text-[220px]">auto_awesome</span>
        </div>

        <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-5 text-center md:text-left">
                @php
                    $level = max(1, floor($allTimeStars / 50) + 1);
                @endphp
                <div class="level-disc-badge flex-shrink-0 mx-auto md:mx-0">
                    <span class="text-2xl sm:text-3xl font-black text-white leading-none">LVL {{ $level }}</span>
                    <span class="text-[10px] font-black uppercase tracking-widest text-amber-300 mt-1">{{ text('achieve.level_label') }}</span>
                </div>

                <div class="space-y-1">
                    <div class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full bg-white/20 backdrop-blur-md text-3xs font-black uppercase tracking-widest text-white">
                        <span class="material-symbols-outlined !text-xs">military_tech</span>
                        {{ text('achieve.badge') }}
                    </div>
                    <h1 class="text-3xl sm:text-5xl font-black italic uppercase tracking-tight text-white leading-tight">
                        {{ text('achieve.title') }}
                    </h1>
                    <p class="text-xs sm:text-sm font-semibold text-white/80 max-w-md">
                        {{ text('achieve.desc') }}
                    </p>
                </div>
            </div>

            <!-- Total Stars Display -->
            <div class="bg-white/15 backdrop-blur-md border border-white/25 rounded-2xl p-4 sm:p-5 text-center min-w-[160px] flex-shrink-0">
                <span class="material-symbols-outlined !text-3xl text-amber-300 mb-1" style="font-variation-settings:'FILL' 1">stars</span>
                <div class="text-2xl sm:text-3xl font-black text-white tracking-tight">{{ $totalStars }}</div>
                <div class="text-3xs font-black uppercase tracking-widest text-white/70">
                    {{ $selectedMonth === 'all' ? text('achieve.all_time') : text('achieve.monthly') }}
                </div>
            </div>
        </div>
    </section>

    <!-- Month & Period Filter Selector Bar -->
    <section class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-[rgb(var(--surface-container-lowest))] border-2 border-[rgb(var(--surface-container-high))] rounded-2xl p-4 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-[rgb(var(--primary))/0.1] text-[rgb(var(--primary))] flex items-center justify-center flex-shrink-0">
                <span class="material-symbols-outlined !text-2xl">calendar_month</span>
            </div>
            <div>
                <h4 class="font-black text-xs sm:text-sm uppercase tracking-wide text-[rgb(var(--on-surface))]">{{ text('achieve.filter_title') }}</h4>
                <p class="text-3xs font-bold text-[rgb(var(--on-surface))/0.5]">{{ text('achieve.filter_desc') }}</p>
            </div>
        </div>

        <form method="GET" action="{{ route('achievements') }}" class="flex items-center gap-2 w-full sm:w-auto">
            <label for="month-select" class="text-3xs font-black uppercase tracking-widest text-[rgb(var(--on-surface))/0.6] whitespace-nowrap">{{ text('achieve.filter_period') }}</label>
            <select id="month-select" name="month" onchange="this.form.submit()" class="bg-[rgb(var(--surface-container-high))] border-2 border-[rgb(var(--surface-container-high))] text-[rgb(var(--on-surface))] text-xs font-black rounded-full px-4 py-2.5 focus:outline-none focus:border-[rgb(var(--primary))] transition-all cursor-pointer w-full sm:w-auto uppercase tracking-wide">
                @foreach($availableMonths as $val => $label)
                <option value="{{ $val }}" {{ $selectedMonth === $val ? 'selected' : '' }}>
                    {{ $label }}
                </option>
                @endforeach
            </select>
        </form>
    </section>

    <!-- Navigation Section Tabs -->
    <section class="flex items-center justify-center gap-3">
        <button type="button" id="tab-btn-achievements" class="main-nav-tab active flex items-center gap-2" onclick="switchMainTab('achievements')">
            <span class="material-symbols-outlined !text-lg">workspace_premium</span>
            <span>{{ text('achieve.tab_achievements') }}</span>
        </button>
        <button type="button" id="tab-btn-leaderboard" class="main-nav-tab flex items-center gap-2" onclick="switchMainTab('leaderboard')">
            <span class="material-symbols-outlined !text-lg">leaderboard</span>
            <span>{{ text('achieve.tab_leaderboard') }}</span>
        </button>
    </section>

    <!-- Spacious 2-Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

        <!-- MAIN COLUMN (8 cols): Badges or Leaderboard -->
        <main class="lg:col-span-8 space-y-8">

            <!-- TAB 1: MY ACHIEVEMENTS & GALLERY -->
            <div id="tab-content-achievements" class="space-y-8">
                <!-- Recent Unlocked Achievements Feed -->
                <section class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined !text-2xl text-[rgb(var(--primary))]">history</span>
                            <h3 class="text-base sm:text-lg font-black uppercase tracking-tight text-[rgb(var(--on-surface))]">
                                {{ text('achieve.timeline_title') }}
                            </h3>
                        </div>
                        <span class="text-3xs font-black uppercase tracking-widest text-[rgb(var(--on-surface))/0.4]">{{ text('achieve.timeline_latest') }}</span>
                    </div>

                    @if($earnedUserStars->isNotEmpty())
                    <div class="space-y-3">
                        @foreach($earnedUserStars->take(5) as $userStar)
                        @php
                            $starObj = $userStar->star;
                            $starName = $starObj?->name ?? text('achieve.star_default_name');
                            $starDesc = $starObj?->description ?? '';
                        @endphp
                        <div class="sidebar-widget-card p-4 flex items-center justify-between gap-4 transition-all">
                            <div class="flex items-center gap-3.5">
                                <div class="w-11 h-11 rounded-xl bg-amber-500/15 text-amber-600 flex items-center justify-center flex-shrink-0">
                                    <span class="material-symbols-outlined !text-2xl" style="font-variation-settings:'FILL' 1">stars</span>
                                </div>
                                <div>
                                    <h4 class="font-black text-sm text-[rgb(var(--on-surface))] uppercase tracking-wide">
                                        {{ $starName }}
                                    </h4>
                                    <p class="text-xs text-[rgb(var(--on-surface))/0.6] font-semibold">
                                        {{ $starDesc ?: text('achieve.star_default_desc') }}
                                    </p>
                                    <span class="text-4xs font-bold text-[rgb(var(--on-surface))/0.4] block mt-0.5">
                                        {{ text('achieve.unlocked') }} {{ $userStar->created_at ? $userStar->created_at->diffForHumans() : text('achieve.recently') }}
                                    </span>
                                </div>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-amber-400/20 text-amber-700 font-black text-xs">
                                    +{{ $starObj?->point ?? 10 }} Stars
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="bg-[rgb(var(--surface-container-lowest))] border-2 border-dashed border-[rgb(var(--surface-container-high))] rounded-2xl p-8 text-center space-y-3">
                        <span class="material-symbols-outlined !text-5xl text-[rgb(var(--primary))/0.3]">emoji_events</span>
                        <h4 class="font-black text-base text-[rgb(var(--on-surface))] uppercase">{{ text('achieve.empty_title') }}</h4>
                        <p class="text-xs font-bold text-[rgb(var(--on-surface))/0.5] max-w-sm mx-auto">
                            {{ text('achieve.empty_desc') }}
                        </p>
                    </div>
                    @endif
                </section>

                <!-- All Badges Gallery -->
                <section class="space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined !text-2xl text-[rgb(var(--primary))]">grid_view</span>
                            <h3 class="text-base sm:text-lg font-black uppercase tracking-tight text-[rgb(var(--on-surface))]">
                                {{ text('achieve.gallery_title') }}
                            </h3>
                        </div>
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <button type="button" class="achieve-tab-btn active" onclick="filterBadges('all', this)">{{ text('achieve.filter_all') }} ({{ $allStars->count() }})</button>
                            <button type="button" class="achieve-tab-btn" onclick="filterBadges('unlocked', this)">{{ text('achieve.filter_unlocked') }} ({{ count($earnedStarIds) }})</button>
                            <button type="button" class="achieve-tab-btn" onclick="filterBadges('locked', this)">{{ text('achieve.filter_locked') }} ({{ $allStars->count() - count($earnedStarIds) }})</button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($allStars as $star)
                        @php
                            $isUnlocked = in_array($star->id, $earnedStarIds);
                            $starTitle = $star->name->en ?? $star->type;
                            $starDetails = $star->description->en ?? '';
                        @endphp
                        <div class="badge-card {{ $isUnlocked ? 'unlocked' : 'locked' }} badge-item-card" data-status="{{ $isUnlocked ? 'unlocked' : 'locked' }}">
                            <div class="flex items-start justify-between gap-3">
                                <div class="badge-icon-box {{ $isUnlocked ? 'bg-amber-400 text-amber-950 shadow-md shadow-amber-400/30' : 'bg-slate-200 text-slate-500' }}">
                                    @if($star->type === 'quiz_perfect')
                                    <span class="material-symbols-outlined !text-2xl" style="font-variation-settings:'FILL' 1">emoji_events</span>
                                    @elseif($star->type === 'exam_excellent')
                                    <span class="material-symbols-outlined !text-2xl" style="font-variation-settings:'FILL' 1">trophy</span>
                                    @elseif($star->type === 'login_streak')
                                    <span class="material-symbols-outlined !text-2xl" style="font-variation-settings:'FILL' 1">local_fire_department</span>
                                    @else
                                    <span class="material-symbols-outlined !text-2xl" style="font-variation-settings:'FILL' 1">workspace_premium</span>
                                    @endif
                                </div>
                                <span class="text-3xs font-black uppercase tracking-widest px-2.5 py-1 rounded-full {{ $isUnlocked ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $isUnlocked ? text('achieve.badge_unlocked') : text('achieve.badge_locked') }}
                                </span>
                            </div>

                            <h4 class="font-black text-base text-[rgb(var(--on-surface))] uppercase tracking-tight mb-1">
                                {{ $starTitle }}
                            </h4>
                            <p class="text-xs font-semibold text-[rgb(var(--on-surface))/0.6] leading-relaxed mb-4">
                                {{ $starDetails ?: text('achieve.badge_default_desc') }}
                            </p>

                            <div class="flex items-center justify-between pt-3 border-t border-[rgb(var(--surface-container-high))/0.6] text-xs font-bold">
                                <span class="text-amber-600 flex items-center gap-1 font-black">
                                    <span class="material-symbols-outlined !text-base" style="font-variation-settings:'FILL' 1">star</span>
                                    +{{ $star->point }} {{ text('achieve.stars') }}
                                </span>
                                <span class="text-3xs font-black uppercase tracking-widest text-[rgb(var(--on-surface))/0.4]">
                                    {{ strtoupper(str_replace('_', ' ', $star->type)) }}
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </section>
            </div>

            <!-- TAB 2: GLOBAL LEADERBOARD & RANKINGS -->
            <div id="tab-content-leaderboard" class="space-y-8 hidden">
                <!-- Top 3 Podium Showcase -->
                @if($leaderboard->count() >= 3)
                <section class="grid grid-cols-3 gap-3 sm:gap-6 pt-4 items-end max-w-xl mx-auto">
                    @php $rank2 = $leaderboard->get(1); @endphp
                    @if($rank2)
                    <div class="podium-card rank-2 order-1">
                        <div class="w-12 h-12 rounded-full bg-slate-300 text-slate-800 font-black text-lg border-2 border-white shadow-md mx-auto mb-2 flex items-center justify-center overflow-hidden">
                            @if(!empty($rank2->avatar))
                                @if(str_contains($rank2->avatar, '/') || str_contains($rank2->avatar, 'http'))
                                    <img src="{{ $rank2->avatar }}" alt="Avatar" class="w-full h-full object-cover rounded-full" />
                                @else
                                    <span class="text-2xl select-none">{{ $rank2->avatar }}</span>
                                @endif
                            @else
                                {{ strtoupper(substr($rank2->name, 0, 1)) }}
                            @endif
                        </div>
                        <div class="inline-flex items-center gap-1 bg-slate-200 text-slate-800 text-4xs font-black uppercase tracking-widest px-2.5 py-0.5 rounded-full mb-1">
                            🥈 {{ text('achieve.place_2') }}
                        </div>
                        <h4 class="font-black text-xs sm:text-sm text-[rgb(var(--on-surface))] truncate px-1">
                            {{ $rank2->name }}
                        </h4>
                        <div class="text-amber-600 font-black text-xs sm:text-sm flex items-center justify-center gap-1 mt-1">
                            <span class="material-symbols-outlined !text-sm" style="font-variation-settings:'FILL' 1">star</span>
                            {{ $rank2->total_stars }}
                        </div>
                    </div>
                    @endif

                    @php $rank1 = $leaderboard->first(); @endphp
                    @if($rank1)
                    <div class="podium-card rank-1 order-2">
                        <div class="absolute -top-5 left-1/2 -translate-x-1/2 text-amber-500">
                            <span class="material-symbols-outlined !text-3xl" style="font-variation-settings:'FILL' 1">military_tech</span>
                        </div>
                        <div class="w-14 h-14 rounded-full bg-amber-400 text-amber-950 font-black text-xl border-4 border-amber-300 shadow-lg mx-auto mb-2 flex items-center justify-center overflow-hidden">
                            @if(!empty($rank1->avatar))
                                @if(str_contains($rank1->avatar, '/') || str_contains($rank1->avatar, 'http'))
                                    <img src="{{ $rank1->avatar }}" alt="Avatar" class="w-full h-full object-cover rounded-full" />
                                @else
                                    <span class="text-3xl select-none">{{ $rank1->avatar }}</span>
                                @endif
                            @else
                                {{ strtoupper(substr($rank1->name, 0, 1)) }}
                            @endif
                        </div>
                        <div class="inline-flex items-center gap-1 bg-amber-400/30 text-amber-900 text-4xs font-black uppercase tracking-widest px-2.5 py-0.5 rounded-full mb-1">
                            👑 {{ text('achieve.champion') }}
                        </div>
                        <h4 class="font-black text-sm sm:text-base text-[rgb(var(--on-surface))] truncate px-1">
                            {{ $rank1->name }}
                        </h4>
                        <div class="text-amber-600 font-black text-sm sm:text-base flex items-center justify-center gap-1 mt-1">
                            <span class="material-symbols-outlined !text-base" style="font-variation-settings:'FILL' 1">star</span>
                            {{ $rank1->total_stars }}
                        </div>
                    </div>
                    @endif

                    @php $rank3 = $leaderboard->get(2); @endphp
                    @if($rank3)
                    <div class="podium-card rank-3 order-3">
                        <div class="w-12 h-12 rounded-full bg-amber-700 text-white font-black text-lg border-2 border-white shadow-md mx-auto mb-2 flex items-center justify-center overflow-hidden">
                            @if(!empty($rank3->avatar))
                                @if(str_contains($rank3->avatar, '/') || str_contains($rank3->avatar, 'http'))
                                    <img src="{{ $rank3->avatar }}" alt="Avatar" class="w-full h-full object-cover rounded-full" />
                                @else
                                    <span class="text-2xl select-none">{{ $rank3->avatar }}</span>
                                @endif
                            @else
                                {{ strtoupper(substr($rank3->name, 0, 1)) }}
                            @endif
                        </div>
                        <div class="inline-flex items-center gap-1 bg-amber-700/20 text-amber-900 text-4xs font-black uppercase tracking-widest px-2.5 py-0.5 rounded-full mb-1">
                            🥉 {{ text('achieve.place_3') }}
                        </div>
                        <h4 class="font-black text-xs sm:text-sm text-[rgb(var(--on-surface))] truncate px-1">
                            {{ $rank3->name }}
                        </h4>
                        <div class="text-amber-600 font-black text-xs sm:text-sm flex items-center justify-center gap-1 mt-1">
                            <span class="material-symbols-outlined !text-sm" style="font-variation-settings:'FILL' 1">star</span>
                            {{ $rank3->total_stars }}
                        </div>
                    </div>
                    @endif
                </section>
                @endif

                <!-- Rankings List -->
                <section class="space-y-3">
                    <div class="space-y-2">
                        @foreach($leaderboard as $index => $u)
                        @php
                            $isMe = auth()->check() && auth()->id() === $u->id;
                            $userLvl = max(1, floor($u->total_stars / 50) + 1);
                        @endphp
                        <div class="leaderboard-row {{ $isMe ? 'is-me' : '' }} flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3.5">
                                <div class="w-8 text-center flex-shrink-0">
                                    @if($index === 0) 🥇
                                    @elseif($index === 1) 🥈
                                    @elseif($index === 2) 🥉
                                    @else <span class="text-xs font-black text-[rgb(var(--on-surface))/0.5]">#{{ $index + 1 }}</span>
                                    @endif
                                </div>

                                <div class="w-9 h-9 rounded-full bg-[rgb(var(--primary-fixed))] text-white font-bold text-xs flex items-center justify-center flex-shrink-0 border-2 border-white shadow-sm overflow-hidden">
                                    @if(!empty($u->avatar))
                                        @if(str_contains($u->avatar, '/') || str_contains($u->avatar, 'http'))
                                            <img src="{{ $u->avatar }}" alt="Avatar" class="w-full h-full object-cover rounded-full" />
                                        @else
                                            <span class="text-lg select-none">{{ $u->avatar }}</span>
                                        @endif
                                    @else
                                        {{ strtoupper(substr($u->name, 0, 1)) }}
                                    @endif
                                </div>

                                <div>
                                    <div class="flex items-center gap-2">
                                        <h4 class="font-black text-xs sm:text-sm text-[rgb(var(--on-surface))]">
                                            {{ $u->name }}
                                        </h4>
                                        @if($isMe)
                                        <span class="bg-[rgb(var(--secondary))] text-white text-4xs font-black uppercase tracking-widest px-2 py-0.5 rounded-full shadow-sm">
                                            {{ text('achieve.you') }}
                                        </span>
                                        @endif
                                    </div>
                                    <span class="text-4xs font-bold text-[rgb(var(--on-surface))/0.5] uppercase tracking-wider">
                                        {{ text('achieve.level_explorer', ['level' => $userLvl]) }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex items-center gap-1.5 bg-amber-400/15 text-amber-700 px-3 py-1 rounded-full font-black text-xs">
                                <span class="material-symbols-outlined !text-base" style="font-variation-settings:'FILL' 1">star</span>
                                <span>{{ $u->total_stars }} {{ text('achieve.stars') }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </section>
            </div>

        </main>

        <!-- SIDEBAR COLUMN (4 cols): Quick Stats & Actions -->
        <aside class="lg:col-span-4 space-y-6 lg:sticky lg:top-24">

            <!-- Quick Stats Bar -->
            <div class="sidebar-widget-card space-y-4">
                <h4 class="font-black text-xs uppercase tracking-widest text-[rgb(var(--on-surface))/0.6]">{{ text('achieve.sidebar_title') }}</h4>
                <div class="grid grid-cols-2 gap-3 text-center">
                    <div class="bg-[rgb(var(--surface-container-high))/0.4] rounded-2xl p-3">
                        <div class="text-xl font-black text-[rgb(var(--primary))]">{{ $earnedUserStars->count() }}</div>
                        <div class="text-4xs font-black uppercase text-[rgb(var(--on-surface))/0.5]">{{ text('achieve.sidebar_badges') }}</div>
                    </div>
                    <div class="bg-[rgb(var(--surface-container-high))/0.4] rounded-2xl p-3">
                        <div class="text-xl font-black text-amber-600">{{ $totalStars }}</div>
                        <div class="text-4xs font-black uppercase text-[rgb(var(--on-surface))/0.5]">{{ text('achieve.sidebar_period') }}</div>
                    </div>
                    <div class="bg-[rgb(var(--surface-container-high))/0.4] rounded-2xl p-3">
                        <div class="text-xl font-black text-emerald-600">{{ $quizCount }}</div>
                        <div class="text-4xs font-black uppercase text-[rgb(var(--on-surface))/0.5]">{{ text('achieve.sidebar_quizzes') }}</div>
                    </div>
                    <div class="bg-[rgb(var(--surface-container-high))/0.4] rounded-2xl p-3">
                        <div class="text-xl font-black text-indigo-600">{{ $examCount }}</div>
                        <div class="text-4xs font-black uppercase text-[rgb(var(--on-surface))/0.5]">{{ text('achieve.sidebar_exams') }}</div>
                    </div>
                </div>
            </div>

            <!-- Start Practice Callout -->
            <div class="sidebar-widget-card bg-gradient-to-br from-[rgb(var(--primary))] to-[rgb(var(--secondary))] text-white border-none space-y-4 shadow-xl">
                <div class="space-y-1">
                    <h4 class="font-black text-base uppercase tracking-tight">{{ text('achieve.cta_title') }}</h4>
                    <p class="text-xs font-semibold text-white/80">{{ text('achieve.cta_desc') }}</p>
                </div>
                <a href="{{ route('topics') }}" class="w-full py-3 bg-white text-[rgb(var(--primary))] rounded-full font-black text-xs uppercase tracking-widest flex items-center justify-center gap-2 no-underline active:scale-95 transition-all shadow-md">
                    <span>{{ text('achieve.cta_btn') }}</span>
                    <span class="material-symbols-outlined !text-lg">arrow_forward</span>
                </a>
            </div>

        </aside>

    </div>

</div>
@endsection

@push('scripts')
<script src="{{ asset('js/achievements.js') }}?v={{ filemtime(public_path('js/achievements.js')) }}"></script>
@endpush
