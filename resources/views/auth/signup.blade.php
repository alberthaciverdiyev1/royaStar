@extends('layouts.app', ['hideHeader' => true, 'hideNavbar' => true, 'isIndex' => true])
@section('title', text('signup.page_title'))

@section('content')
<div class="signup-bg-decor">
    <div class="absolute top-[10%] right-[15%] text-[rgb(var(--tertiary))/0.1]">
        <span class="material-symbols-outlined !text-9xl rotate-12">auto_awesome</span>
    </div>
    <div class="absolute bottom-[5%] left-[5%] text-[rgb(var(--primary))/0.1]">
        <span class="material-symbols-outlined !text-[160px] -rotate-12">cloud</span>
    </div>
</div>

<section class="signup-section">
    <div class="signup-card">
        <div class="text-center mb-10">
            <h1 class="signup-title">{{ text('signup.title') }}</h1>
            <p class="signup-subtitle">{{ text('signup.subtitle') }}</p>
        </div>

        <form method="POST" action="{{ route('signup.post') }}" class="space-y-5" id="signupForm">
            @csrf
            <div class="space-y-1">
                <label class="signup-label">{{ text('signup.full_name') }}</label>
                <input name="name" type="text" required value="{{ old('name') }}" class="signup-input" placeholder="{{ text('signup.name_placeholder') }}" />
                @error('name') <span class="text-[rgb(var(--error))] text-3xs font-black uppercase tracking-widest">{{ $message }}</span> @enderror
            </div>
            <div class="space-y-1">
                <label class="signup-label">{{ text('signup.phone') }}</label>
                <input name="phone" type="tel" value="{{ old('phone') }}" class="signup-input" placeholder="+994" />
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div class="space-y-1">
                    <label class="signup-label">{{ text('signup.city') }}</label>
                    <select name="city_id" class="signup-input">
                        <option value="">{{ text('signup.select_city') }}</option>
                        @foreach($cities as $city)
                        <option value="{{ $city->id }}" {{ old('city_id') == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="signup-label">{{ text('signup.grade') }}</label>
                    <select name="grade_id" class="signup-input">
                        <option value="">{{ text('signup.select_grade') }}</option>
                        @foreach($grades as $grade)
                        <option value="{{ $grade->id }}" {{ old('grade_id') == $grade->id ? 'selected' : '' }}>{{ $grade->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="space-y-1">
                <label class="signup-label">{{ text('signup.email') }}</label>
                <input name="email" type="email" required value="{{ old('email') }}" class="signup-input" placeholder="{{ text('signup.email_placeholder') }}" />
                @error('email') <span class="text-[rgb(var(--error))] text-3xs font-black uppercase tracking-widest">{{ $message }}</span> @enderror
            </div>
            <div class="space-y-1">
                <label class="signup-label">{{ text('signup.password') }}</label>
                <div class="relative flex items-center">
                    <input name="password" type="password" required class="signup-input pr-12" placeholder="••••••••" id="signup-password" />
                    <button type="button" class="absolute right-4 text-[rgb(var(--on-surface))/0.6] hover:text-[rgb(var(--primary))] transition-colors toggle-password" data-target="signup-password">
                        <span class="material-symbols-outlined !text-lg">visibility_off</span>
                    </button>
                </div>
                @error('password') <span class="text-[rgb(var(--error))] text-3xs font-black uppercase tracking-widest">{{ $message }}</span> @enderror
            </div>
            <div class="space-y-1">
                <label class="signup-label">{{ text('signup.confirm_password') }}</label>
                <div class="relative flex items-center">
                    <input name="password_confirmation" type="password" required class="signup-input pr-12" placeholder="••••••••" id="signup-confirm" />
                    <button type="button" class="absolute right-4 text-[rgb(var(--on-surface))/0.6] hover:text-[rgb(var(--primary))] transition-colors toggle-password" data-target="signup-confirm">
                        <span class="material-symbols-outlined !text-lg">visibility_off</span>
                    </button>
                </div>
                <span id="password-match-error" class="text-[rgb(var(--error))] text-3xs font-black uppercase tracking-widest hidden">{{ text('signup.match_error') }}</span>
            </div>

            @if(session('error'))
            <div class="bg-[rgb(var(--error))/0.1] text-[rgb(var(--error))] text-2xs font-black uppercase tracking-widest px-5 py-4 rounded-2xl border border-[rgb(var(--error))/0.2] text-center">
                {{ session('error') }}
            </div>
            @endif

            <button class="btn-signup" type="submit">
                {{ text('signup.btn') }}
                <span class="material-symbols-outlined !text-xl">arrow_forward</span>
            </button>
        </form>

        <div class="mt-8 text-center">
            <p class="text-sm font-medium text-[rgb(var(--on-surface))/0.6]">
                {{ text('signup.has_account') }}
                <a class="font-black hover:underline ml-1 uppercase text-xs text-[rgb(var(--secondary))]" href="{{ route('login') }}">{{ text('signup.login') }}</a>
            </p>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('js/signup.js') }}?v={{ filemtime(public_path('js/signup.js')) }}"></script>
@endpush
