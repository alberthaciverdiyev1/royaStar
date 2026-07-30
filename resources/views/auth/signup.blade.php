@extends('layouts.app', ['hideHeader' => true, 'hideNavbar' => true, 'isIndex' => true])
@section('title', 'Sign Up')

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
            <h1 class="signup-title">Join the Galaxy</h1>
            <p class="signup-subtitle">Start your celestial learning journey today.</p>
        </div>

        <form method="POST" action="{{ route('signup.post') }}" class="space-y-5" id="signupForm">
            @csrf
            <div class="space-y-1">
                <label class="signup-label">Full Name</label>
                <input name="name" type="text" required value="{{ old('name') }}" class="signup-input" placeholder="Enter your full name" />
                @error('name') <span class="text-[rgb(var(--error))] text-3xs font-black uppercase tracking-widest">{{ $message }}</span> @enderror
            </div>
            <div class="space-y-1">
                <label class="signup-label">Phone Number</label>
                <input name="phone" type="tel" value="{{ old('phone') }}" class="signup-input" placeholder="+994" />
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div class="space-y-1">
                    <label class="signup-label">City</label>
                    <select name="city_id" class="signup-input">
                        <option value="">Select City</option>
                        @foreach($cities as $city)
                        <option value="{{ $city->id }}" {{ old('city_id') == $city->id ? 'selected' : '' }}>{{ $city->name[app()->getLocale()] ?? $city->name['az'] ?? '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="signup-label">Grade</label>
                    <select name="grade_id" class="signup-input">
                        <option value="">Select Grade</option>
                        @foreach($grades as $grade)
                        <option value="{{ $grade->id }}" {{ old('grade_id') == $grade->id ? 'selected' : '' }}>{{ $grade->name[app()->getLocale()] ?? $grade->name['az'] ?? '' }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="space-y-1">
                <label class="signup-label">Email Address</label>
                <input name="email" type="email" required value="{{ old('email') }}" class="signup-input" placeholder="your@email.com" />
                @error('email') <span class="text-[rgb(var(--error))] text-3xs font-black uppercase tracking-widest">{{ $message }}</span> @enderror
            </div>
            <div class="space-y-1">
                <label class="signup-label">Secure Password</label>
                <div class="relative flex items-center">
                    <input name="password" type="password" required class="signup-input pr-12" placeholder="••••••••" id="signup-password" />
                    <button type="button" class="absolute right-4 text-[rgb(var(--on-surface))/0.6] hover:text-[rgb(var(--primary))] transition-colors toggle-password" data-target="signup-password">
                        <span class="material-symbols-outlined !text-lg">visibility_off</span>
                    </button>
                </div>
                @error('password') <span class="text-[rgb(var(--error))] text-3xs font-black uppercase tracking-widest">{{ $message }}</span> @enderror
            </div>
            <div class="space-y-1">
                <label class="signup-label">Confirm Password</label>
                <div class="relative flex items-center">
                    <input name="password_confirmation" type="password" required class="signup-input pr-12" placeholder="••••••••" id="signup-confirm" />
                    <button type="button" class="absolute right-4 text-[rgb(var(--on-surface))/0.6] hover:text-[rgb(var(--primary))] transition-colors toggle-password" data-target="signup-confirm">
                        <span class="material-symbols-outlined !text-lg">visibility_off</span>
                    </button>
                </div>
                <span id="password-match-error" class="text-[rgb(var(--error))] text-3xs font-black uppercase tracking-widest hidden">Passwords do not match</span>
            </div>

            @if(session('error'))
            <div class="bg-[rgb(var(--error))/0.1] text-[rgb(var(--error))] text-2xs font-black uppercase tracking-widest px-5 py-4 rounded-2xl border border-[rgb(var(--error))/0.2] text-center">
                {{ session('error') }}
            </div>
            @endif

            <button class="btn-signup" type="submit">
                Sign Up Now
                <span class="material-symbols-outlined !text-xl">arrow_forward</span>
            </button>
        </form>

        <div class="mt-8 text-center">
            <p class="text-sm font-medium text-[rgb(var(--on-surface))/0.6]">
                Already have an account?
                <a class="font-black hover:underline ml-1 uppercase text-xs text-[rgb(var(--secondary))]" href="{{ route('login') }}">Log In</a>
            </p>
        </div>
    </div>
</section>
@endsection

<script src="{{ asset('js/signup.js') }}?v={{ filemtime(public_path('js/signup.js')) }}"></script>
