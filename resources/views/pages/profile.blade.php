@extends('layouts.app')
@section('title', 'My Profile')

@section('content')
<section class="max-w-6xl mx-auto px-4 py-8 md:py-12 space-y-6 md:space-y-0 md:grid md:grid-cols-2 md:gap-6">

    <!-- ═══ Profile Header ═══ -->
    <div class="profile-header md:col-span-2">
        <div class="profile-header__bg"></div>
        <div class="profile-header__content">
            <div class="profile-avatar">
                <div class="profile-avatar__circle">
                    <span class="profile-avatar__letter">{{ strtoupper(substr($profile['name'] ?? 'S', 0, 1)) }}</span>
                </div>
                <button class="profile-avatar__edit" title="Change avatar">
                    <span class="material-symbols-outlined !text-base">photo_camera</span>
                </button>
            </div>
            <h1 class="profile-header__name">{{ $profile['name'] ?? 'Star Student' }}</h1>
            <p class="profile-header__email">{{ $profile['email'] ?? 'student@example.com' }}</p>
            <div class="profile-header__stars">
                <span class="material-symbols-outlined !text-xl" style="font-variation-settings:'FILL' 1">star</span>
                <span>{{ $totalStars ?? 0 }} Stars</span>
            </div>
            <div class="flex items-center justify-center gap-2 mt-4 pt-4 border-t border-white/10">
                <span class="material-symbols-outlined !text-base text-white/50">palette</span>
                <button class="profile-theme profile-theme--default" data-theme="default" title="Default">
                    <span class="material-symbols-outlined !text-sm">check</span>
                </button>
                <button class="profile-theme profile-theme--pink" data-theme="pink" title="Pink"></button>
                <button class="profile-theme profile-theme--yellow" data-theme="yellow" title="Yellow"></button>
                <button class="profile-theme profile-theme--green" data-theme="green" title="Green"></button>
                <button class="profile-theme profile-theme--red" data-theme="red" title="Red"></button>
            </div>
        </div>
    </div>

    <!-- ═══ Account Details ═══ -->
    <div class="profile-section">
        <div class="profile-section__header">
            <span class="material-symbols-outlined !text-2xl text-[rgb(var(--primary))]">badge</span>
            <div>
                <h3 class="profile-section__title">Account Details</h3>
                <p class="profile-section__desc">Update your personal information</p>
            </div>
        </div>
        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PUT')
            <div class="profile-form-grid">
                <div class="profile-field">
                    <label class="profile-field__label">Full Name</label>
                    <div class="profile-field__input-wrap">
                        <span class="material-symbols-outlined !text-lg text-[rgb(var(--on-surface))/0.3]">person</span>
                        <input type="text" name="name" value="{{ $profile['name'] ?? '' }}" class="profile-field__input" placeholder="Your name" />
                    </div>
                </div>
                <div class="profile-field">
                    <label class="profile-field__label">Email</label>
                    <div class="profile-field__input-wrap">
                        <span class="material-symbols-outlined !text-lg text-[rgb(var(--on-surface))/0.3]">mail</span>
                        <input type="email" name="email" value="{{ $profile['email'] ?? '' }}" class="profile-field__input" placeholder="your@email.com" />
                    </div>
                </div>
                <div class="profile-field">
                    <label class="profile-field__label">Phone</label>
                    <div class="profile-field__input-wrap">
                        <span class="material-symbols-outlined !text-lg text-[rgb(var(--on-surface))/0.3]">call</span>
                        <input type="tel" name="phone" value="{{ $profile['phone'] ?? '' }}" class="profile-field__input" placeholder="+994 XX XXX XX XX" />
                    </div>
                </div>
                <div class="profile-field">
                    <label class="profile-field__label">School</label>
                    <div class="profile-field__input-wrap">
                        <span class="material-symbols-outlined !text-lg text-[rgb(var(--on-surface))/0.3]">school</span>
                        <input type="text" name="school_name" value="{{ $student->school_name ?? '' }}" class="profile-field__input" placeholder="School name" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="profile-field">
                        <label class="profile-field__label">City</label>
                        <div class="profile-field__input-wrap">
                            <span class="material-symbols-outlined !text-lg text-[rgb(var(--on-surface))/0.3]">location_city</span>
                            <select name="city_id" class="profile-field__input profile-field__select">
                                <option value="">Select City</option>
                                @foreach($cities as $city)
                                <option value="{{ $city->id }}" {{ ($student->city_id ?? '') == $city->id ? 'selected' : '' }}>{{ $city->name[app()->getLocale()] ?? $city->name['az'] ?? '' }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="profile-field">
                        <label class="profile-field__label">Grade</label>
                        <div class="profile-field__input-wrap">
                            <span class="material-symbols-outlined !text-lg text-[rgb(var(--on-surface))/0.3]">tag</span>
                            <select name="grade_id" class="profile-field__input profile-field__select">
                                <option value="">Select Grade</option>
                                @foreach($grades as $grade)
                                <option value="{{ $grade->id }}" {{ ($student->grade_id ?? '') == $grade->id ? 'selected' : '' }}>{{ $grade->name[app()->getLocale()] ?? $grade->name['az'] ?? '' }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="profile-field">
                    <label class="profile-field__label">Birth Date</label>
                    <div class="profile-field__input-wrap">
                        <span class="material-symbols-outlined !text-lg text-[rgb(var(--on-surface))/0.3]">calendar_month</span>
                        <input type="date" name="birth_date" value="{{ isset($student->birth_date) ? $student->birth_date->format('Y-m-d') : '' }}" class="profile-field__input" />
                    </div>
                </div>
            </div>
            <button type="submit" class="profile-btn profile-btn--secondary w-full mt-6">
                <span class="material-symbols-outlined !text-lg">save</span>
                Save Changes
            </button>
        </form>
    </div>

    <!-- ═══ Change Password ═══ -->
    <div class="profile-section">
        <div class="profile-section__header">
            <span class="material-symbols-outlined !text-2xl text-[rgb(var(--secondary))]">lock</span>
            <div>
                <h3 class="profile-section__title">Change Password</h3>
                <p class="profile-section__desc">Make sure it's at least 8 characters</p>
            </div>
        </div>
        <form method="POST" action="{{ route('profile.password') }}">
            @csrf
            @method('PUT')
            <div class="profile-form-grid">
                <div class="profile-field">
                    <label class="profile-field__label">Current Password</label>
                    <div class="profile-field__input-wrap">
                        <span class="material-symbols-outlined !text-lg text-[rgb(var(--on-surface))/0.3]">lock_open</span>
                        <input type="password" name="current_password" class="profile-field__input" placeholder="••••••••" />
                    </div>
                </div>
                <div class="profile-field">
                    <label class="profile-field__label">New Password</label>
                    <div class="profile-field__input-wrap">
                        <span class="material-symbols-outlined !text-lg text-[rgb(var(--on-surface))/0.3]">key</span>
                        <input type="password" name="new_password" class="profile-field__input" placeholder="••••••••" />
                    </div>
                </div>
                <div class="profile-field">
                    <label class="profile-field__label">Confirm New Password</label>
                    <div class="profile-field__input-wrap">
                        <span class="material-symbols-outlined !text-lg text-[rgb(var(--on-surface))/0.3]">verified</span>
                        <input type="password" name="new_password_confirmation" class="profile-field__input" placeholder="••••••••" />
                    </div>
                </div>
            </div>
            <button type="submit" class="profile-btn profile-btn--primary w-full mt-6">
                <span class="material-symbols-outlined !text-lg">lock_reset</span>
                Update Password
            </button>
        </form>
    </div>

    <!-- ═══ Recent Achievements ═══ -->
    @if(isset($starHistory) && $starHistory->count() > 0)
    <div class="profile-section md:col-span-2">
        <div class="profile-section__header">
            <span class="material-symbols-outlined !text-2xl text-[rgb(var(--tertiary))]" style="font-variation-settings:'FILL' 1">star</span>
            <div>
                <h3 class="profile-section__title">Recent Achievements</h3>
                <p class="profile-section__desc">Your latest star rewards</p>
            </div>
        </div>
        <div class="grid gap-3">
            @foreach($starHistory as $entry)
            <div class="flex items-center gap-4 p-4 rounded-2xl bg-[rgb(var(--surface))] border border-[rgb(var(--surface-container-high))]">
                <div class="w-10 h-10 rounded-full bg-[rgb(var(--tertiary))/0.1] flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined !text-xl text-[rgb(var(--tertiary))]" style="font-variation-settings:'FILL' 1">star</span>
                </div>
                <div class="flex-1 min-w-0">
                    <h4 class="font-black text-xs uppercase tracking-wide text-[rgb(var(--on-surface))] truncate">
                        {{ str_replace('_', ' ', ucfirst($entry['type'])) }}
                    </h4>
                    <p class="text-3xs font-bold text-[rgb(var(--on-surface-variant))] mt-0.5">
                        {{ $entry['created_at']->diffForHumans() }}
                    </p>
                </div>
                <span class="text-sm font-black text-[rgb(var(--tertiary))]">+{{ $entry['point'] }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- ═══ Logout ═══ -->
    <div class="text-center pt-4 pb-10 md:col-span-2">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="profile-btn-logout">
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
    var btns = document.querySelectorAll('.profile-theme');
    var current = document.documentElement.getAttribute('data-theme') || 'default';

    btns.forEach(function(btn) {
        btn.classList.remove('active');
        var theme = btn.getAttribute('data-theme');
        if (theme === current) btn.classList.add('active');

        btn.addEventListener('click', function() {
            btns.forEach(function(b) { b.classList.remove('active'); });
            this.classList.add('active');
            document.documentElement.setAttribute('data-theme', theme);
            try { localStorage.setItem('theme', theme); } catch(e) {}
        });
    });
})();
</script>
@endpush
