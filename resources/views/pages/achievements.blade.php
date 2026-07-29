@extends('layouts.app')
@section('title', 'Achievements & Star Leaderboard')

@push('styles')
<style>
.achievements-wrapper {
    max-width: 64rem;
    margin: 0 auto;
    padding: 1.25rem 1rem 4rem 1rem;
}
@media (min-width: 640px) {
    .achievements-wrapper {
        padding: 2rem 1.5rem 5rem 1.5rem;
    }
}

/* Hero Celestial Card */
.achieve-hero-banner {
    position: relative;
    border-radius: 2.25rem;
    padding: 2.5rem 1.5rem;
    background: radial-gradient(circle at 80% 20%, rgba(255, 255, 255, 0.15) 0%, transparent 60%),
                linear-gradient(135deg, rgb(var(--primary)) 0%, rgb(var(--secondary)) 100%);
    color: #ffffff;
    box-shadow: 0 25px 50px -12px rgba(var(--primary), 0.35);
    overflow: hidden;
}
@media (min-width: 768px) {
    .achieve-hero-banner {
        border-radius: 3rem;
        padding: 3.5rem 3rem;
    }
}

.level-disc-badge {
    width: 6rem;
    height: 6rem;
    border-radius: 9999px;
    background: rgba(0, 0, 0, 0.2);
    backdrop-filter: blur(12px);
    border: 3px solid rgba(255, 255, 255, 0.3);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}
@media (min-width: 768px) {
    .level-disc-badge {
        width: 7.5rem;
        height: 7.5rem;
    }
}

/* Main Section Tabs Switcher */
.main-nav-tab {
    padding: 0.75rem 1.75rem;
    border-radius: 9999px;
    font-size: 0.85rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    border: 2px solid rgba(var(--surface-container-high), 1);
    background: rgba(var(--surface-container-lowest), 1);
    color: rgba(var(--on-surface), 0.6);
    cursor: pointer;
    transition: all 0.25s cubic-bezier(0.22, 1, 0.36, 1);
}
.main-nav-tab.active {
    background: rgb(var(--primary));
    border-color: rgb(var(--primary));
    color: #ffffff;
    box-shadow: 0 8px 25px rgba(var(--primary), 0.3);
    transform: scale(1.02);
}

/* Badge Cards */
.badge-card {
    background-color: rgba(var(--surface-container-lowest), 1);
    border: 2px solid rgba(var(--surface-container-high), 1);
    border-radius: 1.5rem;
    padding: 1.25rem;
    transition: all 0.3s cubic-bezier(0.22, 1, 0.36, 1);
    position: relative;
    overflow: hidden;
}
.badge-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);
}
.badge-card.unlocked {
    border-color: rgba(var(--tertiary), 0.4);
    background: linear-gradient(135deg, rgba(var(--tertiary), 0.06) 0%, rgba(var(--surface-container-lowest), 1) 100%);
}
.badge-card.locked {
    opacity: 0.65;
    filter: grayscale(0.7);
}

.badge-icon-box {
    width: 3.75rem;
    height: 3.75rem;
    border-radius: 1.25rem;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;
    transition: transform 0.3s ease;
}
.badge-card:hover .badge-icon-box {
    transform: scale(1.1) rotate(6deg);
}

/* Leaderboard Card Styling */
.leaderboard-row {
    background-color: rgba(var(--surface-container-lowest), 1);
    border: 2px solid rgba(var(--surface-container-high), 1);
    border-radius: 1.25rem;
    padding: 1rem 1.25rem;
    transition: all 0.2s ease;
}
.leaderboard-row:hover {
    border-color: rgba(var(--primary), 0.3);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
}
.leaderboard-row.is-me {
    border-color: rgb(var(--secondary));
    background: linear-gradient(135deg, rgba(var(--secondary), 0.06) 0%, rgba(var(--surface-container-lowest), 1) 100%);
}

/* Podium Cards */
.podium-card {
    background-color: rgba(var(--surface-container-lowest), 1);
    border: 2px solid rgba(var(--surface-container-high), 1);
    border-radius: 2rem;
    padding: 1.5rem 1rem;
    text-align: center;
    position: relative;
    transition: all 0.3s ease;
}
.podium-card.rank-1 {
    border-color: rgba(245, 158, 11, 0.5);
    background: linear-gradient(180deg, rgba(245, 158, 11, 0.12) 0%, rgba(var(--surface-container-lowest), 1) 100%);
    transform: scale(1.05);
    box-shadow: 0 15px 35px rgba(245, 158, 11, 0.2);
}
.podium-card.rank-2 {
    border-color: rgba(148, 163, 184, 0.5);
    background: linear-gradient(180deg, rgba(148, 163, 184, 0.1) 0%, rgba(var(--surface-container-lowest), 1) 100%);
}
.podium-card.rank-3 {
    border-color: rgba(180, 83, 9, 0.4);
    background: linear-gradient(180deg, rgba(180, 83, 9, 0.08) 0%, rgba(var(--surface-container-lowest), 1) 100%);
}

/* Filter Tab Pills */
.achieve-tab-btn {
    padding: 0.4rem 1rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    border: 2px solid rgba(var(--surface-container-high), 1);
    background: rgba(var(--surface-container-lowest), 1);
    color: rgba(var(--on-surface), 0.6);
    cursor: pointer;
    transition: all 0.2s ease;
}
.achieve-tab-btn.active {
    background: rgb(var(--primary));
    border-color: rgb(var(--primary));
    color: #ffffff;
    box-shadow: 0 4px 14px rgba(var(--primary), 0.25);
}
</style>
@endpush

@section('content')
<div class="achievements-wrapper space-y-8">

    <!-- Hero Achievement Header -->
    <section class="achieve-hero-banner group">
        <!-- Floating Celestial Orbs -->
        <div class="absolute -top-12 -right-12 text-white/10 pointer-events-none transition-transform duration-1000 group-hover:rotate-45">
            <span class="material-symbols-outlined !text-[240px]">military_tech</span>
        </div>
        <div class="absolute -bottom-10 -left-10 text-white/10 pointer-events-none">
            <span class="material-symbols-outlined !text-[180px]">auto_awesome</span>
        </div>

        <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-6">

            <div class="flex items-center gap-5 text-center md:text-left">
                <!-- Level Badge -->
                @php
                    $level = max(1, floor($allTimeStars / 50) + 1);
                @endphp
                <div class="level-disc-badge flex-shrink-0 mx-auto md:mx-0">
                    <span class="text-2xl sm:text-3xl font-black text-white leading-none">LVL {{ $level }}</span>
                    <span class="text-[10px] font-black uppercase tracking-widest text-amber-300 mt-1">Explorer</span>
                </div>

                <div class="space-y-1">
                    <div class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full bg-white/20 backdrop-blur-md text-3xs font-black uppercase tracking-widest text-white">
                        <span class="material-symbols-outlined !text-xs">military_tech</span>
                        Star Rewards Hub
                    </div>
                    <h2 class="text-2xl sm:text-4xl font-black italic uppercase tracking-tight text-white leading-tight">
                        Achievements & Rankings 🌟
                    </h2>
                    <p class="text-xs sm:text-sm font-semibold text-white/80 max-w-md">
                        Earn Star points by completing quizzes and lessons. Monthly stars reset on the last day at 23:59!
                    </p>
                </div>
            </div>

            <!-- Total Stars Display -->
            <div class="bg-white/15 backdrop-blur-md border border-white/25 rounded-2xl p-4 sm:p-5 text-center min-w-[160px] flex-shrink-0">
                <span class="material-symbols-outlined !text-3xl text-amber-300 mb-1" style="font-variation-settings:'FILL' 1">stars</span>
                <div class="text-2xl sm:text-3xl font-black text-white tracking-tight">{{ $totalStars }}</div>
                <div class="text-3xs font-black uppercase tracking-widest text-white/70">
                    {{ $selectedMonth === 'all' ? 'All-Time Stars' : 'Monthly Stars' }}
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
                <h4 class="font-black text-xs sm:text-sm uppercase tracking-wide text-[rgb(var(--on-surface))]">Monthly Cycle & Historical Rankings</h4>
                <p class="text-3xs font-bold text-[rgb(var(--on-surface))/0.5]">Monthly stars reset on the last day of each month at 23:59</p>
            </div>
        </div>

        <form method="GET" action="{{ route('achievements') }}" class="flex items-center gap-2 w-full sm:w-auto">
            <label for="month-select" class="text-3xs font-black uppercase tracking-widest text-[rgb(var(--on-surface))/0.6] whitespace-nowrap">Period:</label>
            <select id="month-select" name="month" onchange="this.form.submit()" class="bg-[rgb(var(--surface-container-high))] border-2 border-[rgb(var(--surface-container-high))] text-[rgb(var(--on-surface))] text-xs font-black rounded-full px-4 py-2.5 focus:outline-none focus:border-[rgb(var(--primary))] transition-all cursor-pointer w-full sm:w-auto uppercase tracking-wide">
                @foreach($availableMonths as $val => $label)
                <option value="{{ $val }}" {{ $selectedMonth === $val ? 'selected' : '' }}>
                    {{ $label }}
                </option>
                @endforeach
            </select>
        </form>
    </section>

    <!-- Main Navigation Section Tabs -->
    <section class="flex items-center justify-center gap-3">
        <button type="button" id="tab-btn-achievements" class="main-nav-tab active flex items-center gap-2" onclick="switchMainTab('achievements')">
            <span class="material-symbols-outlined !text-lg">workspace_premium</span>
            <span>My Achievements</span>
        </button>
        <button type="button" id="tab-btn-leaderboard" class="main-nav-tab flex items-center gap-2" onclick="switchMainTab('leaderboard')">
            <span class="material-symbols-outlined !text-lg">leaderboard</span>
            <span>Star Leaderboard 🏆</span>
        </button>
    </section>

    <!-- TAB 1: MY ACHIEVEMENTS & GALLERY -->
    <div id="tab-content-achievements" class="space-y-8">
        <!-- Quick Stats Bar -->
        <section class="grid grid-cols-2 sm:grid-cols-4 gap-3.5">
            <div class="bg-[rgb(var(--surface-container-lowest))] border-2 border-[rgb(var(--surface-container-high))] rounded-2xl p-4 text-center space-y-1 shadow-sm">
                <span class="material-symbols-outlined !text-2xl text-[rgb(var(--primary))]">workspace_premium</span>
                <div class="text-xl sm:text-2xl font-black text-[rgb(var(--on-surface))]">{{ $earnedUserStars->count() }}</div>
                <div class="text-3xs font-black uppercase tracking-widest text-[rgb(var(--on-surface))/0.5]">Badges Unlocked</div>
            </div>
            <div class="bg-[rgb(var(--surface-container-lowest))] border-2 border-[rgb(var(--surface-container-high))] rounded-2xl p-4 text-center space-y-1 shadow-sm">
                <span class="material-symbols-outlined !text-2xl text-amber-500">auto_awesome</span>
                <div class="text-xl sm:text-2xl font-black text-amber-600">{{ $totalStars }}</div>
                <div class="text-3xs font-black uppercase tracking-widest text-[rgb(var(--on-surface))/0.5]">Period XP Points</div>
            </div>
            <div class="bg-[rgb(var(--surface-container-lowest))] border-2 border-[rgb(var(--surface-container-high))] rounded-2xl p-4 text-center space-y-1 shadow-sm">
                <span class="material-symbols-outlined !text-2xl text-emerald-600">rocket_launch</span>
                <div class="text-xl sm:text-2xl font-black text-emerald-600">{{ $quizCount }}</div>
                <div class="text-3xs font-black uppercase tracking-widest text-[rgb(var(--on-surface))/0.5]">Quizzes Completed</div>
            </div>
            <div class="bg-[rgb(var(--surface-container-lowest))] border-2 border-[rgb(var(--surface-container-high))] rounded-2xl p-4 text-center space-y-1 shadow-sm">
                <span class="material-symbols-outlined !text-2xl text-indigo-600">quiz</span>
                <div class="text-xl sm:text-2xl font-black text-indigo-600">{{ $examCount }}</div>
                <div class="text-3xs font-black uppercase tracking-widest text-[rgb(var(--on-surface))/0.5]">Exams Taken</div>
            </div>
        </section>

        <!-- Recent Unlocked Achievements Feed -->
        <section class="space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined !text-2xl text-[rgb(var(--primary))]">history</span>
                    <h3 class="text-base sm:text-lg font-black uppercase tracking-tight text-[rgb(var(--on-surface))]">
                        Recent Activity Timeline
                    </h3>
                </div>
                <span class="text-3xs font-black uppercase tracking-widest text-[rgb(var(--on-surface))/0.4]">Latest Unlocks</span>
            </div>

            @if($earnedUserStars->isNotEmpty())
            <div class="space-y-3">
                @foreach($earnedUserStars->take(5) as $userStar)
                @php
                    $starObj = $userStar->star;
                    $starName = is_array($starObj?->name) ? ($starObj?->name[app()->getLocale()] ?? $starObj?->name['az'] ?? 'Star Award') : ($starObj?->name ?? 'Star Award');
                    $starDesc = is_array($starObj?->description) ? ($starObj?->description[app()->getLocale()] ?? $starObj?->description['az'] ?? '') : ($starObj?->description ?? '');
                @endphp
                <div class="bg-[rgb(var(--surface-container-lowest))] border-2 border-[rgb(var(--surface-container-high))] hover:border-[rgb(var(--primary))/0.3] rounded-2xl p-4 flex items-center justify-between gap-4 transition-all shadow-sm">
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-xl bg-amber-500/15 text-amber-600 flex items-center justify-center flex-shrink-0">
                            <span class="material-symbols-outlined !text-2xl" style="font-variation-settings:'FILL' 1">stars</span>
                        </div>
                        <div>
                            <h4 class="font-black text-sm text-[rgb(var(--on-surface))] uppercase tracking-wide">
                                {{ $starName }}
                            </h4>
                            <p class="text-xs text-[rgb(var(--on-surface))/0.6] font-semibold">
                                {{ $starDesc ?: 'Awarded for exceptional learning progress!' }}
                            </p>
                            <span class="text-4xs font-bold text-[rgb(var(--on-surface))/0.4] block mt-0.5">
                                Unlocked {{ $userStar->created_at ? $userStar->created_at->diffForHumans() : 'Recently' }}
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
                <h4 class="font-black text-base text-[rgb(var(--on-surface))] uppercase">No Achievements For Selected Period</h4>
                <p class="text-xs font-bold text-[rgb(var(--on-surface))/0.5] max-w-sm mx-auto">
                    Solve quizzes and exams in this period to claim your Star Badges!
                </p>
                <a href="{{ route('topics') }}" class="inline-flex items-center gap-2 px-6 py-2.5 bg-[rgb(var(--primary))] text-white rounded-full font-black text-xs uppercase tracking-widest no-underline active:scale-95 transition-all">
                    Explore Topics
                </a>
            </div>
            @endif
        </section>

        <!-- All Badges Gallery & Filtering -->
        <section class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined !text-2xl text-[rgb(var(--primary))]">grid_view</span>
                    <h3 class="text-base sm:text-lg font-black uppercase tracking-tight text-[rgb(var(--on-surface))]">
                        Badges & Trophies Gallery
                    </h3>
                </div>
                <div class="flex items-center gap-1.5 flex-wrap">
                    <button type="button" class="achieve-tab-btn active" onclick="filterBadges('all', this)">All ({{ $allStars->count() }})</button>
                    <button type="button" class="achieve-tab-btn" onclick="filterBadges('unlocked', this)">Unlocked ({{ count($earnedStarIds) }})</button>
                    <button type="button" class="achieve-tab-btn" onclick="filterBadges('locked', this)">Locked ({{ $allStars->count() - count($earnedStarIds) }})</button>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($allStars as $star)
                @php
                    $isUnlocked = in_array($star->id, $earnedStarIds);
                    $starTitle = is_array($star->name) ? ($star->name[app()->getLocale()] ?? $star->name['az'] ?? $star->type) : ($star->name ?? $star->type);
                    $starDetails = is_array($star->description) ? ($star->description[app()->getLocale()] ?? $star->description['az'] ?? '') : ($star->description ?? '');
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
                            {{ $isUnlocked ? 'Unlocked ✓' : 'Locked' }}
                        </span>
                    </div>

                    <h4 class="font-black text-base text-[rgb(var(--on-surface))] uppercase tracking-tight mb-1">
                        {{ $starTitle }}
                    </h4>
                    <p class="text-xs font-semibold text-[rgb(var(--on-surface))/0.6] leading-relaxed mb-4">
                        {{ $starDetails ?: 'Complete learning milestones to claim this badge.' }}
                    </p>

                    <div class="flex items-center justify-between pt-3 border-t border-[rgb(var(--surface-container-high))/0.6] text-xs font-bold">
                        <span class="text-amber-600 flex items-center gap-1 font-black">
                            <span class="material-symbols-outlined !text-base" style="font-variation-settings:'FILL' 1">star</span>
                            +{{ $star->point }} Stars
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
        <section class="grid grid-cols-3 gap-3 sm:gap-6 pt-4 items-end max-w-2xl mx-auto">

            <!-- 2nd Place (Silver) -->
            @php $rank2 = $leaderboard->get(1); @endphp
            @if($rank2)
            <div class="podium-card rank-2 order-1">
                <div class="w-12 h-12 rounded-full bg-slate-300 text-slate-800 font-black text-lg border-2 border-white shadow-md mx-auto mb-2 flex items-center justify-center">
                    {{ strtoupper(substr($rank2->name, 0, 1)) }}
                </div>
                <div class="inline-flex items-center gap-1 bg-slate-200 text-slate-800 text-4xs font-black uppercase tracking-widest px-2.5 py-0.5 rounded-full mb-1">
                    🥈 2nd Place
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

            <!-- 1st Place (Gold) -->
            @php $rank1 = $leaderboard->first(); @endphp
            @if($rank1)
            <div class="podium-card rank-1 order-2">
                <div class="absolute -top-5 left-1/2 -translate-x-1/2 text-amber-500">
                    <span class="material-symbols-outlined !text-3xl" style="font-variation-settings:'FILL' 1">military_tech</span>
                </div>
                <div class="w-14 h-14 rounded-full bg-amber-400 text-amber-950 font-black text-xl border-4 border-amber-300 shadow-lg mx-auto mb-2 flex items-center justify-center">
                    {{ strtoupper(substr($rank1->name, 0, 1)) }}
                </div>
                <div class="inline-flex items-center gap-1 bg-amber-400/30 text-amber-900 text-4xs font-black uppercase tracking-widest px-2.5 py-0.5 rounded-full mb-1">
                    👑 Champion
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

            <!-- 3rd Place (Bronze) -->
            @php $rank3 = $leaderboard->get(2); @endphp
            @if($rank3)
            <div class="podium-card rank-3 order-3">
                <div class="w-12 h-12 rounded-full bg-amber-700 text-white font-black text-lg border-2 border-white shadow-md mx-auto mb-2 flex items-center justify-center">
                    {{ strtoupper(substr($rank3->name, 0, 1)) }}
                </div>
                <div class="inline-flex items-center gap-1 bg-amber-700/20 text-amber-900 text-4xs font-black uppercase tracking-widest px-2.5 py-0.5 rounded-full mb-1">
                    🥉 3rd Place
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

        <!-- Full Rankings Table / List -->
        <section class="space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined !text-2xl text-[rgb(var(--primary))]">format_list_numbered</span>
                    <h3 class="text-base sm:text-lg font-black uppercase tracking-tight text-[rgb(var(--on-surface))]">
                        Student Leaderboard — {{ $availableMonths[$selectedMonth] ?? 'Selected Period' }}
                    </h3>
                </div>
                <span class="text-3xs font-black uppercase tracking-widest text-[rgb(var(--on-surface))/0.4]">Top 50 Ranking</span>
            </div>

            <div class="space-y-2">
                @foreach($leaderboard as $index => $u)
                @php
                    $isMe = auth()->check() && auth()->id() === $u->id;
                    $userLvl = max(1, floor($u->total_stars / 50) + 1);
                @endphp
                <div class="leaderboard-row {{ $isMe ? 'is-me' : '' }} flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <!-- Rank Number Badge -->
                        <div class="w-8 text-center flex-shrink-0">
                            @if($index === 0)
                            <span class="text-xl">🥇</span>
                            @elseif($index === 1)
                            <span class="text-xl">🥈</span>
                            @elseif($index === 2)
                            <span class="text-xl">🥉</span>
                            @else
                            <span class="text-xs sm:text-sm font-black text-[rgb(var(--on-surface))/0.5]">#{{ $index + 1 }}</span>
                            @endif
                        </div>

                        <!-- User Avatar -->
                        <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-[rgb(var(--primary-fixed))] text-white font-bold text-xs flex items-center justify-center flex-shrink-0 border-2 border-white shadow-sm">
                            {{ strtoupper(substr($u->name, 0, 1)) }}
                        </div>

                        <!-- User Info -->
                        <div>
                            <div class="flex items-center gap-2">
                                <h4 class="font-black text-xs sm:text-sm text-[rgb(var(--on-surface))]">
                                    {{ $u->name }}
                                </h4>
                                @if($isMe)
                                <span class="bg-[rgb(var(--secondary))] text-white text-4xs font-black uppercase tracking-widest px-2 py-0.5 rounded-full shadow-sm">
                                    YOU
                                </span>
                                @endif
                            </div>
                            <span class="text-4xs font-bold text-[rgb(var(--on-surface))/0.5] uppercase tracking-wider">
                                Level {{ $userLvl }} Explorer
                            </span>
                        </div>
                    </div>

                    <!-- Stars Score -->
                    <div class="flex items-center gap-1.5 bg-amber-400/15 text-amber-700 px-3 py-1 rounded-full font-black text-xs sm:text-sm">
                        <span class="material-symbols-outlined !text-base" style="font-variation-settings:'FILL' 1">star</span>
                        <span>{{ $u->total_stars }} Stars</span>
                    </div>
                </div>
                @endforeach
            </div>
        </section>

    </div>

</div>
@endsection

@push('scripts')
<script>
function switchMainTab(tabName) {
    if (tabName === 'achievements') {
        document.getElementById('tab-content-achievements').classList.remove('hidden');
        document.getElementById('tab-content-leaderboard').classList.add('hidden');
        document.getElementById('tab-btn-achievements').classList.add('active');
        document.getElementById('tab-btn-leaderboard').classList.remove('active');
    } else {
        document.getElementById('tab-content-achievements').classList.add('hidden');
        document.getElementById('tab-content-leaderboard').classList.remove('hidden');
        document.getElementById('tab-btn-achievements').classList.remove('active');
        document.getElementById('tab-btn-leaderboard').classList.add('active');
    }
}

function filterBadges(type, btn) {
    document.querySelectorAll('.achieve-tab-btn').forEach(function(b) {
        b.classList.remove('active');
    });
    btn.classList.add('active');

    document.querySelectorAll('.badge-item-card').forEach(function(card) {
        var status = card.getAttribute('data-status');
        if (type === 'all' || status === type) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
}
</script>
@endpush
