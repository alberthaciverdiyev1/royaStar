@php
    $musicPlayerTracks = $musicPlayerTracks ?? [];
@endphp

@if (count($musicPlayerTracks) > 0)
    <link rel="stylesheet" href="{{ asset('css/music-player.css') }}?v={{ filemtime(public_path('css/music-player.css')) }}">

    <div
        id="rs-music-player"
        class="rs-mp"
        role="region"
        aria-label="Musiqi pleyeri"
        data-turbo-permanent
        data-tracks="{{ json_encode($musicPlayerTracks, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT) }}"
        hidden
    >
        {{-- Hidden host for the YouTube IFrame player (audio only). --}}
        <div class="rs-mp__yt" aria-hidden="true">
            <div id="rs-music-yt-host"></div>
        </div>

        {{-- Expanded card --}}
        <div class="rs-mp__panel" data-panel>
            <span class="rs-mp__aura" aria-hidden="true"></span>

            <div class="rs-mp__head">
                <span class="rs-mp__label">
                    <span class="rs-mp__dot" aria-hidden="true"></span>
                    İndi çalır
                </span>
                <button
                    type="button"
                    class="rs-mp__icon-btn rs-mp__collapse"
                    data-collapse
                    aria-label="Pleyeri kiçilt"
                    title="Kiçilt"
                >
                    <span class="material-symbols-outlined">keyboard_arrow_down</span>
                </button>
            </div>

            <div class="rs-mp__now">
                <div class="rs-mp__cover">
                    <img data-cover alt="" hidden>
                    <span class="material-symbols-outlined rs-mp__cover-fallback" data-cover-fallback>music_note</span>
                </div>
                <div class="rs-mp__meta">
                    <p class="rs-mp__title" data-title title=""></p>
                    <p class="rs-mp__artist" data-artist></p>
                    <span class="rs-mp__bars" data-eq aria-hidden="true"><i></i><i></i><i></i><i></i></span>
                </div>
            </div>

            <div class="rs-mp__progress-row">
                <span class="rs-mp__time" data-current-time>0:00</span>
                <div
                    class="rs-mp__progress"
                    data-progress
                    role="slider"
                    aria-label="Mahnının gedişatı"
                    aria-valuemin="0"
                    aria-valuemax="100"
                    aria-valuenow="0"
                    tabindex="0"
                >
                    <span class="rs-mp__progress-fill" data-progress-fill></span>
                    <span class="rs-mp__progress-thumb" data-progress-thumb></span>
                </div>
                <span class="rs-mp__time" data-duration>0:00</span>
            </div>

            <div class="rs-mp__controls">
                <button type="button" class="rs-mp__icon-btn" data-prev aria-label="Əvvəlki mahnı" title="Əvvəlki">
                    <span class="material-symbols-outlined">skip_previous</span>
                </button>

                <button type="button" class="rs-mp__play" data-play aria-label="Çal / Dayandır">
                    <span class="material-symbols-outlined" data-play-icon>play_arrow</span>
                </button>

                <button type="button" class="rs-mp__icon-btn" data-next aria-label="Növbəti mahnı" title="Növbəti">
                    <span class="material-symbols-outlined">skip_next</span>
                </button>

                <div class="rs-mp__volume">
                    <button type="button" class="rs-mp__icon-btn rs-mp__mute" data-mute aria-label="Səsi söndür / aç" title="Səs">
                        <span class="material-symbols-outlined" data-volume-icon>volume_up</span>
                    </button>
                    <input
                        type="range"
                        class="rs-mp__volume-range"
                        data-volume
                        min="0"
                        max="100"
                        step="1"
                        value="30"
                        aria-label="Səs səviyyəsi"
                    >
                </div>

                <button
                    type="button"
                    class="rs-mp__icon-btn rs-mp__queue-toggle"
                    data-playlist-toggle
                    aria-label="Pleylisti göstər"
                    aria-expanded="false"
                    title="Pleylist"
                >
                    <span class="material-symbols-outlined">queue_music</span>
                </button>
            </div>

            <div class="rs-mp__playlist" data-playlist hidden>
                <div class="rs-mp__playlist-head">
                    <span>Pleylist</span>
                    <span class="rs-mp__playlist-count" data-playlist-count></span>
                </div>
                <div class="rs-mp__playlist-list" data-playlist-list></div>
            </div>
        </div>

        {{-- Collapsed mini bubble --}}
        <div class="rs-mp__mini" data-mini>
            <button type="button" class="rs-mp__mini-cover" data-expand aria-label="Pleyeri aç" title="Pleyeri aç">
                <img data-mini-cover alt="" hidden>
                <span class="material-symbols-outlined rs-mp__mini-fallback" data-mini-fallback>music_note</span>
                <span class="rs-mp__mini-bars" data-eq aria-hidden="true"><i></i><i></i><i></i></span>
            </button>
            <button type="button" class="rs-mp__mini-play" data-play aria-label="Çal / Dayandır">
                <span class="material-symbols-outlined" data-play-icon>play_arrow</span>
            </button>
        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('js/music-player.js') }}?v={{ filemtime(public_path('js/music-player.js')) }}" defer></script>
    @endpush
@endif
