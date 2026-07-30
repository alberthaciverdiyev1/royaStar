@extends('layouts.app')
@section('title', 'My Profile')

@section('content')
<section class="max-w-6xl mx-auto px-4 py-8 md:py-12 space-y-6 md:space-y-0 md:grid md:grid-cols-2 md:gap-6">

    <!-- ═══ Profile Header ═══ -->
    <div class="profile-header md:col-span-2">
        <div class="profile-header__bg"></div>
        <div class="profile-header__content">
            <div class="profile-avatar relative group">
                <div class="profile-avatar__circle overflow-hidden flex items-center justify-center bg-gradient-to-br from-indigo-500 to-purple-600 shadow-xl border-4 border-white/20">
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
                <button type="button" onclick="openAvatarModal()" class="profile-avatar__edit shadow-lg transition-transform hover:scale-110 bg-[rgb(var(--primary))] text-white border-2 border-white" title="Fotoşəkil Yüklə / Dəyiş">
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
        <form method="POST" action="{{ route('profile.update') }}" id="profileForm">
            @csrf
            @method('PUT')
            
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

<!-- ═══ THEME-MATCHED AVATAR MODAL ═══ -->
<div id="avatarModal" class="fixed inset-0 hidden items-center justify-center bg-black/60 backdrop-blur-md p-4 transition-all" style="z-index: 99999 !important;">
    <div class="w-full max-w-md rounded-3xl bg-[rgb(var(--surface-container-lowest))] shadow-2xl border border-[rgb(var(--surface-container-high))] overflow-hidden flex flex-col max-h-[90vh] text-[rgb(var(--on-surface))]" style="background-color: rgb(var(--surface-container-lowest)) !important;">
        
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-[rgb(var(--surface-container-high))] bg-[rgb(var(--surface-container))] px-6 py-4 shrink-0">
            <div class="flex items-center gap-2.5">
                <span class="material-symbols-outlined text-[rgb(var(--primary))] !text-2xl">photo_camera</span>
                <h3 class="text-base font-black text-[rgb(var(--on-surface))]">Profil Fotoşəkili Yüklə</h3>
            </div>
            <button type="button" onclick="closeAvatarModal()" class="w-8 h-8 rounded-full flex items-center justify-center text-[rgb(var(--on-surface-variant))] hover:bg-[rgb(var(--surface-container-high))] transition-colors">
                <span class="material-symbols-outlined !text-xl">close</span>
            </button>
        </div>

        <!-- Body -->
        <div class="p-6 space-y-6 overflow-y-auto max-h-[75vh]">
            
            <!-- Upload Form -->
            <form method="POST" action="{{ route('profile.avatar') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <!-- Live Single Preview Circle -->
                <div class="flex flex-col items-center justify-center gap-2">
                    <div class="w-24 h-24 rounded-full border-4 border-[rgb(var(--primary))] shadow-md bg-[rgb(var(--surface-container))] flex items-center justify-center overflow-hidden relative">
                        @if(!empty($profile['avatar']) && (str_contains($profile['avatar'], '/') || str_contains($profile['avatar'], 'http')))
                            <img id="modalAvatarPreview" src="{{ $profile['avatar'] }}" alt="Preview" class="w-full h-full object-cover rounded-full" />
                            <span id="modalAvatarPlaceholder" style="display: none !important;"></span>
                            <span id="modalAvatarPreviewText" style="display: none !important;"></span>
                        @elseif(!empty($profile['avatar']))
                            <img id="modalAvatarPreview" src="" alt="Preview" class="hidden w-full h-full object-cover rounded-full" style="display: none !important;" />
                            <span id="modalAvatarPreviewText" class="text-4xl select-none">{{ $profile['avatar'] }}</span>
                            <span id="modalAvatarPlaceholder" style="display: none !important;"></span>
                        @else
                            <img id="modalAvatarPreview" src="" alt="Preview" class="hidden w-full h-full object-cover rounded-full" style="display: none !important;" />
                            <span id="modalAvatarPreviewText" style="display: none !important;"></span>
                            <span id="modalAvatarPlaceholder" class="material-symbols-outlined !text-5xl text-[rgb(var(--on-surface-variant))/0.4]">account_circle</span>
                        @endif
                    </div>
                    <span class="text-xs font-bold text-[rgb(var(--on-surface-variant))] uppercase tracking-wider">Fotoşəkil Önizləməsi</span>
                </div>

                <!-- Input -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-[rgb(var(--on-surface))]">Kompüter və ya Telefondan Şəkil Seçin</label>
                    <input 
                        type="file" 
                        name="avatar_file" 
                        id="modalAvatarFileInput" 
                        accept="image/*"
                        onchange="previewSelectedPhoto(this)"
                        required
                        class="w-full text-xs text-[rgb(var(--on-surface-variant))] border border-[rgb(var(--surface-container-high))] rounded-xl p-2 bg-[rgb(var(--surface-container))] file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-[rgb(var(--primary))] file:text-white hover:file:opacity-90 cursor-pointer"
                    />
                    <p class="text-[11px] text-[rgb(var(--on-surface-variant))/0.7] font-medium">⚡ Şəkil avtomatik olaraq 256x256 WebP formatında sıxlaşdırılacaq (~15KB).</p>
                </div>

                <button type="submit" class="profile-btn profile-btn--primary w-full py-3 text-xs font-bold shadow-md">
                    <span class="material-symbols-outlined !text-lg">save</span>
                    Yadda Saxla
                </button>
            </form>

            <!-- 4x4 Grid Form -->
            <div class="pt-4 border-t border-[rgb(var(--surface-container-high))] space-y-3">
                <span class="block text-xs font-bold uppercase tracking-wider text-[rgb(var(--on-surface-variant))] text-center">
                    🌟 Və ya Hazır İkon Seçin (4x4)
                </span>
                
                <form method="POST" action="{{ route('profile.avatar') }}" id="emojiAvatarForm">
                    @csrf
                    <input type="hidden" name="avatar" id="modalEmojiInput" value="" />
                    
                    <div style="display: grid !important; grid-template-columns: repeat(4, 1fr) !important; gap: 8px !important;">
                        @php
                            $presetAvatars = ['🦊', '🦁', '🚀', '👑', '🐻', '🐼', '🦄', '⚡', '🌟', '🎓', '🐯', '🦅', '🐱', '🐶', '🤖', '👾'];
                        @endphp
                        @foreach($presetAvatars as $emoji)
                        <button 
                            type="button" 
                            onclick="selectEmojiAvatar('{{ $emoji }}')"
                            style="height: 44px !important; width: 100% !important; display: flex !important; align-items: center !important; justify-content: center !important; margin: 0 !important;"
                            class="rounded-xl bg-[rgb(var(--surface-container))] hover:bg-[rgb(var(--primary-container))] border border-[rgb(var(--surface-container-high))] text-xl transition-transform hover:scale-105 active:scale-95 shadow-xs"
                        >
                            {{ $emoji }}
                        </button>
                        @endforeach
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
@endsection

<script src="{{ asset('js/profile.js') }}?v={{ filemtime(public_path('js/profile.js')) }}"></script>
