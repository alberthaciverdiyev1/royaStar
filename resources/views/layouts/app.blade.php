<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>@yield('title', 'Welcome') | Teacher Roya's Stars</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=block" rel="stylesheet">
    @vite('resources/css/app.css')
    <link href="{{ asset('css/site.css') }}?v={{ filemtime(public_path('css/site.css')) }}" rel="stylesheet">

    <script>
        (function() {
            var theme = 'default';
            try { theme = localStorage.getItem('theme') || 'default'; } catch(e) {}
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            margin: 0;
            -webkit-tap-highlight-color: transparent;
            overflow-x: hidden;
        }
        .material-symbols-outlined {
            font-family: 'Material Symbols Outlined' !important;
            font-size: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        @@media (max-width: 768px) {
            .logo-text { font-size: 11px !important; }
            .logo-icon { font-size: 18px !important; }
            h1 { font-size: 1.2rem !important; }
            section, .signup-card, .login-card, .lesson-card, .question-container, .feedback-container {
                padding-left: 1rem !important;
                padding-right: 1rem !important;
                border-radius: 24px !important;
            }
        }
        .celestial-bg {
            background: radial-gradient(circle at top right, rgb(var(--surface-container-high)), rgb(var(--surface)));
            min-height: 100vh;
            background-attachment: fixed;
        }
        ::-webkit-scrollbar { width: 0px; }
        @@media (min-width: 1024px) {
            .mobile-nav { display: none; }
        }
        .min-w-touch { min-width: 44px; min-height: 44px; }
    </style>
    @stack('styles')
</head>
<body class="celestial-bg text-[rgb(var(--on-surface))] relative transition-colors duration-300">

    @if (!($hideHeader ?? false))
    <header class="fixed top-0 w-full z-[100] bg-[rgb(var(--surface-container-lowest))/0.8] backdrop-blur-xl border-b border-[rgb(var(--surface-container-high))]">
        <div class="flex items-center justify-between px-4 md:px-10 py-3 w-full max-w-[1400px] mx-auto">
            <a href="{{ route('home') }}" class="flex items-center gap-1 group no-underline whitespace-nowrap flex-shrink-0 cursor-pointer">
                <span class="material-symbols-outlined text-[rgb(var(--secondary))] logo-icon md:!text-2xl transition-transform group-hover:rotate-12">auto_awesome</span>
                <h1 class="logo-text md:text-lg font-black tracking-tighter uppercase leading-none">
                    <span class="text-[rgb(var(--primary))]">Teacher </span><span class="text-[rgb(var(--secondary))]">Roya's</span> <span class="text-[rgb(var(--primary))]">Stars</span>
                </h1>
            </a>

            @php
                $currentPath = request()->path();
                $desktopActive = 'text-[rgb(var(--secondary))] font-black border-b-2 border-[rgb(var(--secondary))] pb-1 flex items-center gap-2 transition-all text-xs uppercase tracking-widest';
                $desktopInactive = 'text-[rgb(var(--on-surface))] opacity-40 font-bold hover:opacity-100 flex items-center gap-2 transition-all text-xs uppercase tracking-widest';
            @endphp
            <nav class="hidden lg:flex items-center gap-10 absolute left-1/2 -translate-x-1/2">
                <a href="{{ route('home') }}" class="no-underline">
                    <div class="{{ $currentPath === '/' ? $desktopActive : $desktopInactive }}">
                        <span class="material-symbols-outlined !text-lg">home</span>
                        <span>Home</span>
                    </div>
                </a>
                <a href="{{ route('topics') }}" class="no-underline">
                    <div class="{{ $currentPath === 'topics' ? $desktopActive : $desktopInactive }}">
                        <span class="material-symbols-outlined !text-lg">rocket_launch</span>
                        <span>Topics</span>
                    </div>
                </a>
                <a href="{{ route('exam') }}" class="no-underline">
                    <div class="{{ $currentPath === 'exam' ? $desktopActive : $desktopInactive }}">
                        <span class="material-symbols-outlined !text-lg">quiz</span>
                        <span>Exam</span>
                    </div>
                </a>
                <a href="{{ route('achievements') }}" class="no-underline">
                    <div class="{{ $currentPath === 'achievements' ? $desktopActive : $desktopInactive }}">
                        <span class="material-symbols-outlined !text-lg">military_tech</span>
                        <span>Achievements</span>
                    </div>
                </a>
            </nav>

            <div class="flex justify-end flex-shrink-0 items-center gap-2">
                @auth
                <a href="{{ route('profile') }}" class="flex items-center gap-2 group no-underline">
                    <div class="w-8 h-8 md:w-9 md:h-9 rounded-full bg-[rgb(var(--primary-fixed))] flex items-center justify-center text-white font-bold text-2xs border-2 border-white shadow-sm group-hover:scale-105 transition-transform overflow-hidden">
                        @if(!empty(auth()->user()->avatar))
                            @if(str_contains(auth()->user()->avatar, '/') || str_contains(auth()->user()->avatar, 'http'))
                                <img src="{{ auth()->user()->avatar }}" alt="Avatar" class="w-full h-full object-cover" />
                            @else
                                <span class="text-base select-none">{{ auth()->user()->avatar }}</span>
                            @endif
                        @else
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        @endif
                    </div>
                    <span class="text-3xs md:text-2xs font-black uppercase tracking-widest text-[rgb(var(--primary))] opacity-70 group-hover:opacity-100 transition-opacity">{{ auth()->user()->name }}</span>
                </a>
                @else
                <a href="{{ route('login') }}" class="text-[rgb(var(--on-surface))] opacity-50 hover:opacity-100 font-bold text-xs uppercase tracking-widest no-underline px-3 py-2 transition-all">
                    Log In
                </a>
                <a href="{{ route('signup') }}" class="bg-[rgb(var(--secondary))] text-white px-5 py-2 rounded-full font-bold text-xs shadow-lg shadow-[rgb(var(--secondary))/0.1] active:scale-95 transition-all uppercase tracking-widest no-underline inline-block">
                    Sign Up
                </a>
                @endauth
            </div>
        </div>
    </header>
    @endif

    <main class="{{ ($noMainPadding ?? false) ? '' : (($hideHeader ?? false) ? '' : 'pt-16 md:pt-28') }}{{ ($hideNavbar ?? false) ? '' : ' pb-32 lg:pb-12' }} w-full relative">
        @yield('content')
    </main>

    @if (!($hideNavbar ?? false))
    @php
        $mobileActive = 'bg-[rgb(var(--secondary))] text-white rounded-full px-3 py-1.5 flex flex-col items-center transition-all duration-300 shadow-md scale-95';
        $mobileInactive = 'text-[rgb(var(--on-surface))] opacity-50 flex flex-col items-center px-3 py-1.5 transition-all duration-300';
    @endphp
    <nav class="mobile-nav lg:hidden fixed bottom-0 left-0 w-full z-50 bg-[rgb(var(--surface-container-lowest))/0.95] backdrop-blur-xl rounded-t-2xl shadow-[0_-4px_20px_rgba(0,0,0,0.05)] px-2 pb-5 pt-2 flex justify-around items-center border-t border-[rgb(var(--surface-container-high))]">
        <a href="{{ route('home') }}" class="no-underline">
            <div class="{{ $currentPath === '/' ? $mobileActive : $mobileInactive }}">
                <span class="material-symbols-outlined !text-2xl">home</span>
                <span class="text-3xs font-bold uppercase tracking-widest mt-0.5">Home</span>
            </div>
        </a>
        <a href="{{ route('topics') }}" class="no-underline">
            <div class="{{ $currentPath === 'topics' ? $mobileActive : $mobileInactive }}">
                <span class="material-symbols-outlined !text-2xl">rocket_launch</span>
                <span class="text-3xs font-bold uppercase tracking-widest mt-0.5">Topics</span>
            </div>
        </a>
        <a href="{{ route('exam') }}" class="no-underline">
            <div class="{{ $currentPath === 'exam' ? $mobileActive : $mobileInactive }}">
                <span class="material-symbols-outlined !text-2xl">quiz</span>
                <span class="text-3xs font-bold uppercase tracking-widest mt-0.5">Exam</span>
            </div>
        </a>
        <a href="{{ route('achievements') }}" class="no-underline">
            <div class="{{ $currentPath === 'achievements' ? $mobileActive : $mobileInactive }}">
                <span class="material-symbols-outlined !text-2xl">military_tech</span>
                <span class="text-3xs font-bold uppercase tracking-widest mt-0.5">Badges</span>
            </div>
        </a>
        <a href="{{ route('profile') }}" class="no-underline">
            <div class="{{ $currentPath === 'profile' ? $mobileActive : $mobileInactive }}">
                <span class="material-symbols-outlined !text-2xl">person</span>
                <span class="text-3xs font-bold uppercase tracking-widest mt-0.5">Profile</span>
            </div>
        </a>
    </nav>
    @endif

    @stack('scripts')
</body>
</html>
