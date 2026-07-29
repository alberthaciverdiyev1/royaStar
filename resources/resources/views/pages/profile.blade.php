@extends('layouts.app')
@section('title', 'My Profile')

@section('content')
<section class="max-w-6xl mx-auto px-4 py-8 md:py-12 space-y-6 md:space-y-0 md:grid md:grid-cols-2 md:gap-6">

    <!-- ═══ Profile Header ═══ -->
    <div class="profile-header md:col-span-2">
        <div class="profile-header__bg"></div>
        <div class="profile-header__content">
            <div class="profile-avatar relative group">
                <div class="profile-avatar__circle overflow-hidden flex items-center justify-center bg-gradient-to-br from-indigo-500 to-purple-600 shadow-xl">
                    @if(!empty($profile['avatar']))
                        @if(str_contains($profile['avatar'], '/') || str_contains($profile['avatar'], 'http'))
                            <img src="{{ $profile['avatar'] }}" alt="Avatar" class="w-full h-full object-cover rounded-full" />
                        @else
                            <span class="text-4xl md:text-5xl select-none animate-bounce-short">{{ $profile['avatar'] }}</span>
                        @endif
                    @else
                        <span class="profile-avatar__letter text-3xl font-black text-white">{{ strtoupper(substr($profile['name'] ?? 'S', 0, 1)) }}</span>
                    @endif
                </div>
                <button type="button" onclick="openAvatarModal()" class="profile-avatar__edit shadow-lg transition-transform hover:scale-110" title="Avatarı dəyiş">
                    <span class="material-symbols-outlined !text-base">photo_camera</span>
                </button>
            </div>
            <h1 class="profile-header__name">{{ $profile['name'] ?? 'Star Student' }}</h1>
            <p class="profile-header__email">{{ $profile['email'] ?? 'student@example.com' }}</p>
            <div class="profile-header__stars">
                <span class="material-symbols-outlined !text-xl" style="font-variation-settings:'FILL' 1">star</span>
                <span>{{ $totalStars ?? 0 }} Ulduz XP</span>
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
                <h3 class="profile-section__title">Şəxsi Məlumatlar</h3>
                <p class="profile-section__desc">Profil məlumatlarınızı yeniləyin</p>
            </div>
        </div>
        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" id="profileForm">
            @csrf
            @method('PUT')
            
            <input type="hidden" name="avatar" id="hiddenAvatarInput" value="{{ $profile['avatar'] ?? '' }}" />

            <div class="profile-form-grid">
                <div class="profile-field">
                    <label class="profile-field__label">Ad və Soyad</label>
                    <div class="profile-field__input-wrap">
                        <span class="material-symbols-outlined !text-lg text-[rgb(var(--on-surface))/0.3]">person</span>
                        <input type="text" name="name" value="{{ $profile['name'] ?? '' }}" class="profile-field__input" placeholder="Adınız" />
                    </div>
                </div>
                <div class="profile-field">
                    <label class="profile-field__label">E-poçt</label>
                    <div class="profile-field__input-wrap">
                        <span class="material-symbols-outlined !text-lg text-[rgb(var(--on-surface))/0.3]">mail</span>
                        <input type="email" name="email" value="{{ $profile['email'] ?? '' }}" class="profile-field__input" placeholder="yaz@example.com" />
                    </div>
                </div>
                <div class="profile-field">
                    <label class="profile-field__label">Telefon</label>
                    <div class="profile-field__input-wrap">
                        <span class="material-symbols-outlined !text-lg text-[rgb(var(--on-surface))/0.3]">call</span>
                        <input type="tel" name="phone" value="{{ $profile['phone'] ?? '' }}" class="profile-field__input" placeholder="+994 XX XXX XX XX" />
                    </div>
                </div>
                <div class="profile-field">
                    <label class="profile-field__label">Məktəb</label>
                    <div class="profile-field__input-wrap">
                        <span class="material-symbols-outlined !text-lg text-[rgb(var(--on-surface))/0.3]">school</span>
                        <input type="text" name="school_name" value="{{ $student->school_name ?? '' }}" class="profile-field__input" placeholder="Məktəbin adı" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="profile-field">
                        <label class="profile-field__label">Şəhər / Rayon</label>
                        <div class="profile-field__input-wrap">
                            <span class="material-symbols-outlined !text-lg text-[rgb(var(--on-surface))/0.3]">location_city</span>
                            <select name="city_id" class="profile-field__input profile-field__select">
                                <option value="">Şəhər Seçin</option>
                                @foreach($cities as $city)
                                <option value="{{ $city->id }}" {{ ($student->city_id ?? '') == $city->id ? 'selected' : '' }}>{{ $city->name[app()->getLocale()] ?? $city->name['az'] ?? '' }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="profile-field">
                        <label class="profile-field__label">Sinif</label>
                        <div class="profile-field__input-wrap">
                            <span class="material-symbols-outlined !text-lg text-[rgb(var(--on-surface))/0.3]">tag</span>
                            <select name="grade_id" class="profile-field__input profile-field__select">
                                <option value="">Sinif Seçin</option>
                                @foreach($grades as $grade)
                                <option value="{{ $grade->id }}" {{ ($student->grade_id ?? '') == $grade->id ? 'selected' : '' }}>{{ $grade->name[app()->getLocale()] ?? $grade->name['az'] ?? '' }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="profile-field">
                    <label class="profile-field__label">Doğum Tarixi</label>
                    <div class="profile-field__input-wrap">
                        <span class="material-symbols-outlined !text-lg text-[rgb(var(--on-surface))/0.3]">calendar_month</span>
                        <input type="date" name="birth_date" value="{{ isset($student->birth_date) ? $student->birth_date->format('Y-m-d') : '' }}" class="profile-field__input" />
                    </div>
                </div>
            </div>
            <button type="submit" class="profile-btn profile-btn--secondary w-full mt-6">
                <span class="material-symbols-outlined !text-lg">save</span>
                Yadda Saxla
            </button>
        </form>
    </div>

    <!-- ═══ Change Password ═══ -->
    <div class="profile-section">
        <div class="profile-section__header">
            <span class="material-symbols-outlined !text-2xl text-[rgb(var(--secondary))]">lock</span>
            <div>
                <h3 class="profile-section__title">Şifrəni Dəyiş</h3>
                <p class="profile-section__desc">Şifrəniz minimum 8 simvoldan ibarət olmalıdır</p>
            </div>
        </div>
        <form method="POST" action="{{ route('profile.password') }}">
            @csrf
            @method('PUT')
            <div class="profile-form-grid">
                <div class="profile-field">
                    <label class="profile-field__label">Cari Şifrə</label>
                    <div class="profile-field__input-wrap">
                        <span class="material-symbols-outlined !text-lg text-[rgb(var(--on-surface))/0.3]">lock_open</span>
                        <input type="password" name="current_password" class="profile-field__input" placeholder="••••••••" required />
                    </div>
                </div>
                <div class="profile-field">
                    <label class="profile-field__label">Yeni Şifrə</label>
                    <div class="profile-field__input-wrap">
                        <span class="material-symbols-outlined !text-lg text-[rgb(var(--on-surface))/0.3]">key</span>
                        <input type="password" name="new_password" class="profile-field__input" placeholder="••••••••" required minlength="8" />
                    </div>
                </div>
                <div class="profile-field">
                    <label class="profile-field__label">Yeni Şifrənin Təkrarı</label>
                    <div class="profile-field__input-wrap">
                        <span class="material-symbols-outlined !text-lg text-[rgb(var(--on-surface))/0.3]">verified</span>
                        <input type="password" name="new_password_confirmation" class="profile-field__input" placeholder="••••••••" required minlength="8" />
                    </div>
                </div>
            </div>
            <button type="submit" class="profile-btn profile-btn--primary w-full mt-6">
                <span class="material-symbols-outlined !text-lg">lock_reset</span>
                Şifrəni Yenilə
            </button>
        </form>
    </div>

    <!-- ═══ Recent Achievements ═══ -->
    @if(isset($starHistory) && $starHistory->count() > 0)
    <div class="profile-section md:col-span-2">
        <div class="profile-section__header">
            <span class="material-symbols-outlined !text-2xl text-[rgb(var(--tertiary))]" style="font-variation-settings:'FILL' 1">star</span>
            <div>
                <h3 class="profile-section__title">Son Nailiyyətlər</h3>
                <p class="profile-section__desc">Qazandığınız son ulduz mükafatları</p>
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
                Çıxış Et
            </button>
        </form>
    </div>

</section>

<!-- ═══ AVATAR SELECTOR MODAL ═══ -->
<div id="avatarModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 backdrop-blur-sm p-4 transition-all">
    <div class="w-full max-w-md rounded-3xl bg-[rgb(var(--surface-container-lowest))] p-6 shadow-2xl border border-[rgb(var(--surface-container-high))] space-y-6">
        
        <!-- Modal Header -->
        <div class="flex items-center justify-between border-b border-[rgb(var(--surface-container-high))] pb-4">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-[rgb(var(--primary))] !text-2xl">account_circle</span>
                <h3 class="text-lg font-black text-[rgb(var(--on-surface))]">Avatar Seçin</h3>
            </div>
            <button type="button" onclick="closeAvatarModal()" class="w-8 h-8 rounded-full flex items-center justify-center text-[rgb(var(--on-surface-variant))] hover:bg-[rgb(var(--surface-container-high))] transition-colors">
                <span class="material-symbols-outlined !text-xl">close</span>
            </button>
        </div>

        <!-- Option 1: Preset Emoji Avatars -->
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-[rgb(var(--on-surface-variant))] mb-3">
                🌟 Sevimli İkonlar
            </label>
            <div class="grid grid-cols-4 gap-3">
                @php
                    $presetAvatars = ['🦊', '🦁', '🚀', '👑', '🐻', '🐼', '🦄', '⚡', '🌟', '🎓', '🐯', '🦅', '🐱', '🐶', '🤖', '👾'];
                @endphp
                @foreach($presetAvatars as $emoji)
                <button 
                    type="button" 
                    onclick="selectEmojiAvatar('{{ $emoji }}')"
                    class="h-14 rounded-2xl bg-[rgb(var(--surface-container))] hover:bg-[rgb(var(--primary-container))] border border-[rgb(var(--surface-container-high))] flex items-center justify-center text-3xl transition-transform hover:scale-110 active:scale-95 shadow-sm"
                >
                    {{ $emoji }}
                </button>
                @endforeach
            </div>
        </div>

        <!-- Option 2: Upload Custom Photo -->
        <div class="pt-2 border-t border-[rgb(var(--surface-container-high))]">
            <label class="block text-xs font-bold uppercase tracking-wider text-[rgb(var(--on-surface-variant))] mb-3">
                📁 Şəxsi Şəkil Yükləın
            </label>
            
            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="flex items-center gap-3">
                    <input 
                        type="file" 
                        name="avatar_file" 
                        id="modalAvatarFileInput" 
                        accept="image/*"
                        required
                        class="block w-full text-xs text-[rgb(var(--on-surface-variant))] file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[rgb(var(--primary))/0.1] file:text-[rgb(var(--primary))] hover:file:bg-[rgb(var(--primary))/0.2] transition-all cursor-pointer"
                    />
                    <button type="submit" class="profile-btn profile-btn--secondary shrink-0 px-4 py-2.5 text-xs">
                        Yüklə
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
function openAvatarModal() {
    var modal = document.getElementById('avatarModal');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
}

function closeAvatarModal() {
    var modal = document.getElementById('avatarModal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}

function selectEmojiAvatar(emoji) {
    var hiddenInput = document.getElementById('hiddenAvatarInput');
    var form = document.getElementById('profileForm');
    if (hiddenInput && form) {
        hiddenInput.value = emoji;
        form.submit();
    }
}

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
