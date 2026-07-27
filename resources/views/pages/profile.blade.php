@extends('layouts.app')
@section('title', 'My Profile')

@section('content')
<section class="max-w-content mx-auto px-4 py-8 md:py-12">
    <!-- Avatar -->
    <div class="avatar-container">
        <div class="avatar-frame">
            <div class="avatar-inner">
                <span class="material-symbols-outlined !text-6xl text-white">person</span>
            </div>
            <button class="avatar-edit-btn" title="Change avatar">
                <span class="material-symbols-outlined !text-lg text-white">photo_camera</span>
            </button>
        </div>
        <h1 class="profile-name">{{ $profile['name'] ?? 'Star Student' }}</h1>
        <p class="profile-email">{{ $profile['email'] ?? 'student@example.com' }}</p>
        <div class="profile-stars-badge">
            <span class="material-symbols-outlined !text-lg text-[rgb(var(--tertiary))]" style="font-variation-settings:'FILL' 1">star</span>
            <span class="font-black text-sm uppercase tracking-widest text-[rgb(var(--on-surface))]">{{ $totalStars ?? 0 }} Stars</span>
        </div>
    </div>

    <!-- Account Details -->
    <div class="profile-card mt-8">
        <h3 class="profile-section-title">
            <span class="material-symbols-outlined !text-xl">badge</span>
            Account Details
        </h3>
        <form method="POST" action="{{ route('profile.update') }}" class="space-y-5">
            @csrf
            @method('PUT')
            <div>
                <label class="form-label">Full Name</label>
                <input type="text" name="name" value="{{ $profile['name'] ?? '' }}" class="profile-input" placeholder="Your name" />
            </div>
            <div>
                <label class="form-label">Email</label>
                <input type="email" name="email" value="{{ $profile['email'] ?? '' }}" class="profile-input" placeholder="your@email.com" />
            </div>
            <div>
                <label class="form-label">Phone</label>
                <input type="tel" name="phone" value="{{ $profile['phone'] ?? '' }}" class="profile-input" placeholder="+994 XX XXX XX XX" />
            </div>
            <button type="submit" class="w-full py-3.5 bg-[rgb(var(--secondary))] text-white rounded-full font-black text-xs uppercase tracking-widest shadow-lg shadow-[rgb(var(--secondary))/0.2] active:scale-95 transition-all inline-flex items-center justify-center gap-2">
                <span class="material-symbols-outlined !text-lg">save</span>
                Save Changes
            </button>
        </form>
    </div>

    <!-- Password Change -->
    <div class="profile-card mt-6">
        <h3 class="profile-section-title">
            <span class="material-symbols-outlined !text-xl">lock</span>
            Change Password
        </h3>
        <form method="POST" action="{{ route('profile.password') }}">
            @csrf
            @method('PUT')
            <div class="space-y-5">
                <div>
                    <label class="form-label">Current Password</label>
                    <input type="password" name="current_password" class="profile-input" placeholder="Enter current password" />
                </div>
                <div>
                    <label class="form-label">New Password</label>
                    <input type="password" name="new_password" class="profile-input" placeholder="Enter new password" />
                </div>
                <div>
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" name="new_password_confirmation" class="profile-input" placeholder="Confirm new password" />
                </div>
                <button type="submit" class="w-full py-3.5 bg-[rgb(var(--primary))] text-white rounded-full font-black text-xs uppercase tracking-widest shadow-lg shadow-[rgb(var(--primary))/0.2] active:scale-95 transition-all inline-flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined !text-lg">lock_reset</span>
                    Update Password
                </button>
            </div>
        </form>
    </div>

    <!-- Theme Picker -->
    <div class="profile-card mt-6">
        <h3 class="profile-section-title">
            <span class="material-symbols-outlined !text-xl">palette</span>
            Theme
        </h3>
        <p class="text-3xs font-bold text-[rgb(var(--on-surface-variant))/0.6] mb-4 px-1">Choose your favorite theme color</p>
        <div class="flex flex-wrap gap-3">
            <button class="theme-btn theme-default" data-theme="default" title="Default Purple">
                <span class="material-symbols-outlined !text-xl">check</span>
            </button>
            <button class="theme-btn theme-pink" data-theme="pink" title="Pink"></button>
            <button class="theme-btn theme-yellow" data-theme="yellow" title="Yellow"></button>
            <button class="theme-btn theme-green" data-theme="green" title="Green"></button>
            <button class="theme-btn theme-red" data-theme="red" title="Red"></button>
        </div>
    </div>

    <!-- Logout -->
    <div class="mt-10 text-center">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-logout">
                <span class="material-symbols-outlined !text-xl">logout</span>
                Log Out
            </button>
        </form>
    </div>
</section>
@endsection

@push('scripts')
<script>
    (function() {
        var themeBtns = document.querySelectorAll('.theme-btn');
        var currentTheme = document.documentElement.getAttribute('data-theme') || 'default';

        themeBtns.forEach(function(btn) {
            var theme = btn.getAttribute('data-theme');
            if (theme === currentTheme) {
                btn.classList.add('theme-active');
            }

            btn.addEventListener('click', function() {
                themeBtns.forEach(function(b) { b.classList.remove('theme-active'); });
                this.classList.add('theme-active');
                document.documentElement.setAttribute('data-theme', theme);
                try { localStorage.setItem('theme', theme); } catch(e) {}
            });
        });
    })();
</script>
@endpush
