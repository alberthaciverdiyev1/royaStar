@extends('layouts.app', ['hideHeader' => true, 'hideNavbar' => true, 'isIndex' => true])
@section('title', text('login.page_title'))

@section('content')
<div class="login-bg-decor">
    <div class="absolute top-[10%] left-[5%] text-white/30">
        <span class="material-symbols-outlined !text-[120px] md:!text-[200px] rotate-12">star</span>
    </div>
    <div class="absolute bottom-[10%] right-[2%] text-white/20">
        <span class="material-symbols-outlined !text-[140px] md:!text-[250px] -rotate-12">auto_awesome</span>
    </div>
</div>

<section class="login-section">
    <div class="mb-8 md:mb-12 text-center">
        <div class="flex items-center justify-center mb-6">
            <div class="p-4 bg-[rgb(var(--primary))] rounded-3xl shadow-2xl shadow-[rgb(var(--primary))/0.2]">
                <span class="material-symbols-outlined text-white text-4xl">auto_awesome</span>
            </div>
        </div>
        <h1 class="text-2xl md:text-4xl font-black text-[rgb(var(--on-surface))] tracking-tighter uppercase italic">
            {{ text('login.brand_prefix') }} <span class="text-[rgb(var(--secondary))]">{{ text('login.brand_name') }}</span> {{ text('login.brand_suffix') }}
        </h1>
        <p class="text-[rgb(var(--on-surface))/0.5] font-medium mt-2 text-xs md:text-sm italic">{{ text('login.subtitle') }}</p>
    </div>

    <div class="login-card">
        <div class="mb-10">
            <h2 class="text-2xl md:text-3xl font-black text-[rgb(var(--on-surface))] uppercase tracking-tight italic">{{ text('login.welcome') }}</h2>
            <p class="text-[rgb(var(--on-surface))/0.4] text-xs md:text-sm mt-1 font-medium italic">{{ text('login.credentials') }}</p>
        </div>

        <form method="POST" action="{{ route('login.post') }}" class="space-y-6">
            @csrf
            <div class="space-y-3">
                <label class="text-2xs font-black uppercase tracking-widest text-[rgb(var(--primary))] ml-2">{{ text('login.email') }}</label>
                <input name="email" type="email" required value="{{ old('email') }}" class="login-input" placeholder="{{ text('login.email_placeholder') }}" />
                @error('email') <span class="text-error text-3xs font-black uppercase tracking-widest">{{ $message }}</span> @enderror
            </div>
            <div class="space-y-3">
                <div class="flex justify-between items-center px-2">
                    <label class="text-2xs font-black uppercase tracking-widest text-[rgb(var(--primary))]">{{ text('login.password') }}</label>
                    <a href="#" class="text-2xs font-black text-[rgb(var(--secondary))] uppercase hover:underline tracking-widest">{{ text('login.forgot') }}</a>
                </div>
                <div class="relative flex items-center">
                    <input name="password" type="password" required class="login-input pr-12" placeholder="••••••••" id="login-password" />
                    <button type="button" class="absolute right-5 text-[rgb(var(--on-surface))/0.3] hover:text-[rgb(var(--primary))] transition-colors toggle-password-login" data-target="login-password">
                        <span class="material-symbols-outlined !text-xl">visibility</span>
                    </button>
                </div>
                @error('password') <span class="text-error text-3xs font-black uppercase tracking-widest">{{ $message }}</span> @enderror
            </div>

            @if(session('success'))
            <div class="bg-[rgb(var(--success))/0.1] text-[rgb(var(--success))] text-2xs font-black uppercase tracking-widest px-5 py-4 rounded-2xl border border-[rgb(var(--success))/0.2] text-center">
                {{ session('success') }}
            </div>
            @endif

            @if(session('error'))
            <div class="bg-[rgb(var(--error))/0.1] text-[rgb(var(--error))] text-2xs font-black uppercase tracking-widest px-5 py-4 rounded-2xl border border-[rgb(var(--error))/0.2] text-center">
                {{ session('error') }}
            </div>
            @endif

            <button type="submit" class="btn-login">
                {{ text('login.login_now') }} <span class="material-symbols-outlined !text-xl">rocket_launch</span>
            </button>
        </form>

        <div class="mt-10 pt-8 border-t border-[rgb(var(--surface-container-high))] text-center">
            <p class="text-xs text-[rgb(var(--on-surface))/0.4] font-black uppercase tracking-widest">{{ text('login.no_account') }}</p>
            <a class="inline-block mt-3 text-[rgb(var(--secondary))] font-black uppercase text-2xs tracking-widest hover:underline" href="{{ route('signup') }}">{{ text('login.signup_cta') }}</a>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4 mt-12 w-full max-w-md px-2">
        <div class="hub-card group">
            <div class="hub-icon-box bg-[rgb(var(--primary))/0.1] text-[rgb(var(--primary))]">
                <span class="material-symbols-outlined">school</span>
            </div>
            <span class="hub-text">{{ text('login.hub_student') }}</span>
        </div>
        <div class="hub-card group">
            <div class="hub-icon-box bg-[rgb(var(--tertiary))/0.1] text-[rgb(var(--tertiary))]">
                <span class="material-symbols-outlined">workspace_premium</span>
            </div>
            <span class="hub-text">{{ text('login.hub_rewards') }}</span>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('js/login.js') }}?v={{ filemtime(public_path('js/login.js')) }}"></script>
@endpush
