/* ============================================================
   RoyaStars — floating site music player
   Supports YouTube links (IFrame API) and direct audio URLs.
   Playback state, volume and position survive page navigation.
   ============================================================ */
(function () {
    'use strict';

    var root = document.getElementById('rs-music-player');
    if (!root) return;

    // Turbo keeps this element alive between pages; initialise only once.
    if (root.dataset.mpInitialized === '1') return;
    root.dataset.mpInitialized = '1';

    var tracks;
    try {
        tracks = JSON.parse(root.getAttribute('data-tracks') || '[]');
    } catch (e) {
        return;
    }
    if (!Array.isArray(tracks) || tracks.length === 0) return;

    var els = {
        cover: root.querySelector('[data-cover]'),
        coverFallback: root.querySelector('[data-cover-fallback]'),
        title: root.querySelector('[data-title]'),
        artist: root.querySelector('[data-artist]'),
        current: root.querySelector('[data-current-time]'),
        duration: root.querySelector('[data-duration]'),
        progress: root.querySelector('[data-progress]'),
        progressFill: root.querySelector('[data-progress-fill]'),
        progressThumb: root.querySelector('[data-progress-thumb]'),
        volume: root.querySelector('[data-volume]'),
        volumeIcon: root.querySelector('[data-volume-icon]'),
        playlist: root.querySelector('[data-playlist]'),
        playlistList: root.querySelector('[data-playlist-list]'),
        playlistCount: root.querySelector('[data-playlist-count]'),
        playlistToggle: root.querySelector('[data-playlist-toggle]'),
        miniCover: root.querySelector('[data-mini-cover]'),
        miniFallback: root.querySelector('[data-mini-fallback]'),
        ytHost: document.getElementById('rs-music-yt-host')
    };

    var index = 0;
    var mode = null;            // 'youtube' | 'audio'
    var playing = false;
    var currentTime = 0;
    var duration = 0;
    var volume = parseInt(readStorage('rs_music_volume'), 10);
    if (isNaN(volume)) volume = 30;
    volume = clamp(volume, 0, 100);
    var muted = readStorage('rs_music_muted') === '1';

    // Turbo replaces <body> on every navigation, which would reload the
    // YouTube iframe and cut the music. The hidden host lives directly under
    // <html>, outside the swapped body, so playback keeps running.
    var ytWrapper = root.querySelector('.rs-mp__yt');
    if (ytWrapper) document.documentElement.appendChild(ytWrapper);

    var audio = null;
    var ticker = null;
    var yt = null;
    var ytReady = false;
    var ytLoading = false;
    var ytCallbacks = [];
    var pendingAutoPlay = false;
    var pendingSwitch = null;
    var lastPositionSave = 0;
    var errorSkips = {};
    var autoplayBlocked = false;

    /* ── Storage helpers ───────────────────────────────────── */
    function readStorage(key) {
        try { return localStorage.getItem(key); } catch (e) { return null; }
    }

    function writeStorage(key, value) {
        try { localStorage.setItem(key, value); } catch (e) { /* ignore */ }
    }

    function readSession(key) {
        try { return sessionStorage.getItem(key); } catch (e) { return null; }
    }

    function writeSession(key, value) {
        try { sessionStorage.setItem(key, value); } catch (e) { /* ignore */ }
    }

    function clamp(value, min, max) {
        return Math.min(max, Math.max(min, value));
    }

    /* ── Initial track (restore previous one when possible) ── */
    var savedTrackId = readStorage('rs_music_track_id');
    if (savedTrackId !== null) {
        for (var i = 0; i < tracks.length; i++) {
            if (String(tracks[i].id) === savedTrackId) {
                index = i;
                break;
            }
        }
    }

    function currentTrack() {
        return tracks[index];
    }

    function positionKey(id) {
        return 'rs_music_pos_' + id;
    }

    function loadPosition(id) {
        var value = parseFloat(readSession(positionKey(id)));
        return isFinite(value) && value > 0 ? value : 0;
    }

    function savePosition(seconds, force) {
        var now = Date.now();
        if (!force && now - lastPositionSave < 900) return;
        lastPositionSave = now;

        var track = currentTrack();
        if (!track || !isFinite(seconds) || seconds <= 0) return;

        writeSession(positionKey(track.id), String(Math.floor(seconds)));
    }

    /* ── Playback UI ───────────────────────────────────────── */
    function setPlayingUI(value) {
        playing = !!value;
        root.classList.toggle('is-playing', playing);

        root.querySelectorAll('[data-play-icon]').forEach(function (icon) {
            icon.textContent = playing ? 'pause' : 'play_arrow';
        });

        root.querySelectorAll('[data-play]').forEach(function (button) {
            button.setAttribute('aria-label', playing ? 'Dayandır' : 'Çal');
        });

        if (playing) {
            startTicker();
        } else {
            stopTicker();
            savePosition(currentTime, true);
        }

        if ('mediaSession' in navigator) {
            try {
                navigator.mediaSession.playbackState = playing ? 'playing' : 'paused';
            } catch (e) { /* ignore */ }
        }
    }

    function startTicker() {
        if (ticker) return;
        ticker = window.setInterval(syncProgress, 500);
    }

    function stopTicker() {
        if (!ticker) return;
        window.clearInterval(ticker);
        ticker = null;
    }

    function syncProgress() {
        if (mode !== 'youtube' || !ytReady || !playing) return;

        updateDurationFromYouTube();

        var time = 0;
        try { time = yt.getCurrentTime() || 0; } catch (e) { time = 0; }

        currentTime = time;
        updateProgressUI();
        savePosition(currentTime);
    }

    function formatTime(seconds) {
        if (!isFinite(seconds) || seconds < 0) seconds = 0;
        var total = Math.floor(seconds);
        var mins = Math.floor(total / 60);
        var secs = total % 60;
        return mins + ':' + (secs < 10 ? '0' : '') + secs;
    }

    function updateProgressUI() {
        var percent = duration > 0 ? clamp((currentTime / duration) * 100, 0, 100) : 0;

        if (els.progressFill) els.progressFill.style.width = percent + '%';
        if (els.progressThumb) els.progressThumb.style.left = percent + '%';
        if (els.current) els.current.textContent = formatTime(currentTime);
        if (els.duration) els.duration.textContent = formatTime(duration);

        if (els.progress) {
            els.progress.setAttribute('aria-valuenow', String(Math.round(percent)));
            els.progress.setAttribute('aria-valuetext', formatTime(currentTime) + ' / ' + formatTime(duration));
        }
    }

    function updateAudioProgress() {
        if (!audio) return;
        duration = isFinite(audio.duration) ? audio.duration : 0;
        currentTime = audio.currentTime || 0;
        updateProgressUI();
        if (!audio.paused) savePosition(currentTime);
    }

    function updateDurationFromYouTube() {
        if (!yt || !ytReady || typeof yt.getDuration !== 'function') return;

        var value = 0;
        try { value = yt.getDuration() || 0; } catch (e) { value = 0; }

        if (isFinite(value) && value > 0 && Math.abs(value - duration) > 0.4) {
            duration = value;
            updateProgressUI();
        }
    }

    /* ── Track rendering ───────────────────────────────────── */
    function setImage(img, fallback, src) {
        if (!img) return;

        if (src) {
            img.onerror = function () {
                img.hidden = true;
                img.removeAttribute('src');
                if (fallback) fallback.hidden = false;
            };
            img.src = src;
            img.hidden = false;
            if (fallback) fallback.hidden = true;
        } else {
            img.onerror = null;
            img.removeAttribute('src');
            img.hidden = true;
            if (fallback) fallback.hidden = false;
        }
    }

    function renderTrack(track) {
        if (!track) return;

        var title = track.title || 'Adsız mahnı';
        var artist = track.artist || (track.source_type === 'youtube' ? 'YouTube' : 'Audio');

        if (els.title) {
            els.title.textContent = title;
            els.title.title = title;
        }
        if (els.artist) els.artist.textContent = artist;

        setImage(els.cover, els.coverFallback, track.thumbnail);
        setImage(els.miniCover, els.miniFallback, track.thumbnail);

        duration = 0;
        currentTime = 0;
        updateProgressUI();
        updateMediaSession(track);
    }

    function renderPlaylist() {
        if (!els.playlistList) return;

        els.playlistList.innerHTML = '';

        tracks.forEach(function (track, i) {
            var item = document.createElement('button');
            item.type = 'button';
            item.className = 'rs-mp__track' + (i === index ? ' is-active' : '');
            item.setAttribute('aria-label', (track.title || '') + (track.artist ? ' — ' + track.artist : ''));

            var num = document.createElement('span');
            num.className = 'rs-mp__track-num';
            num.textContent = String(i + 1).padStart(2, '0');

            var cover = document.createElement('span');
            cover.className = 'rs-mp__track-cover';

            if (track.thumbnail) {
                var img = document.createElement('img');
                img.src = track.thumbnail;
                img.alt = '';
                img.loading = 'lazy';
                img.onerror = function () {
                    img.remove();
                    cover.appendChild(musicIcon());
                };
                cover.appendChild(img);
            } else {
                cover.appendChild(musicIcon());
            }

            var text = document.createElement('span');
            text.className = 'rs-mp__track-text';

            var title = document.createElement('span');
            title.className = 'rs-mp__track-title';
            title.textContent = track.title || 'Adsız mahnı';
            text.appendChild(title);

            if (track.artist) {
                var artist = document.createElement('span');
                artist.className = 'rs-mp__track-artist';
                artist.textContent = track.artist;
                text.appendChild(artist);
            }

            var eq = document.createElement('span');
            eq.className = 'rs-mp__track-eq';
            eq.setAttribute('aria-hidden', 'true');
            eq.appendChild(document.createElement('i'));
            eq.appendChild(document.createElement('i'));
            eq.appendChild(document.createElement('i'));

            item.appendChild(num);
            item.appendChild(cover);
            item.appendChild(text);
            item.appendChild(eq);

            item.addEventListener('click', function () {
                playTrack(i, true, false);
            });

            els.playlistList.appendChild(item);
        });

        if (els.playlistCount) els.playlistCount.textContent = String(tracks.length);
    }

    function musicIcon() {
        var icon = document.createElement('span');
        icon.className = 'material-symbols-outlined';
        icon.textContent = 'music_note';
        return icon;
    }

    function togglePlaylist(forceOpen) {
        if (!els.playlist) return;

        var open = typeof forceOpen === 'boolean' ? forceOpen : els.playlist.hidden;
        els.playlist.hidden = !open;

        if (els.playlistToggle) {
            els.playlistToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        }
    }

    /* ── Volume ────────────────────────────────────────────── */
    function applyVolume() {
        if (audio) {
            audio.volume = volume / 100;
            audio.muted = muted;
        }

        if (yt && ytReady) {
            try {
                yt.setVolume(muted ? 0 : volume);
                if (muted) yt.mute();
                else yt.unMute();
            } catch (e) { /* player not ready */ }
        }

        updateVolumeUI();
    }

    function updateVolumeUI() {
        var shown = muted ? 0 : volume;

        if (els.volume) {
            els.volume.value = String(shown);
            els.volume.style.setProperty('--vol', shown + '%');
        }

        if (els.volumeIcon) {
            els.volumeIcon.textContent = (muted || volume === 0)
                ? 'volume_off'
                : (volume < 50 ? 'volume_down' : 'volume_up');
        }

        root.classList.toggle('is-muted', muted || volume === 0);
    }

    function toggleMute() {
        muted = !muted;
        writeStorage('rs_music_muted', muted ? '1' : '0');
        applyVolume();
    }

    /* ── Audio element ─────────────────────────────────────── */
    function ensureAudio() {
        if (audio) return;

        audio = new Audio();
        audio.preload = 'metadata';

        audio.addEventListener('playing', function () { setPlayingUI(true); });
        audio.addEventListener('play', function () { savePlaying('1'); });
        audio.addEventListener('pause', function () { setPlayingUI(false); });
        audio.addEventListener('timeupdate', updateAudioProgress);
        audio.addEventListener('loadedmetadata', updateAudioProgress);
        audio.addEventListener('ended', onTrackEnded);
        audio.addEventListener('error', onTrackError);
    }

    function pauseAudio() {
        if (audio && !audio.paused) {
            try { audio.pause(); } catch (e) { /* ignore */ }
        }
    }

    function playAudio() {
        if (!audio) return;

        var promise = audio.play();
        if (promise && typeof promise.catch === 'function') {
            promise.catch(function (error) {
                setPlayingUI(false);

                // Browsers block unmuted autoplay until the first gesture.
                if (error && error.name === 'NotAllowedError') armAutoplayGesture();
            });
        }
    }

    function onFirstGesture(event) {
        document.removeEventListener('pointerdown', onFirstGesture, true);
        document.removeEventListener('keydown', onFirstGesture, true);

        // Clicks inside the player are handled by its own controls.
        if (root.contains(event.target)) return;
        if (playing || readSession('rs_music_playing') === '0') return;

        if (mode === 'youtube') playYouTube();
        else if (mode === 'audio' && audio) playAudio();
    }

    function armAutoplayGesture() {
        if (autoplayBlocked || readSession('rs_music_playing') === '0') return;

        autoplayBlocked = true;
        document.addEventListener('pointerdown', onFirstGesture, true);
        document.addEventListener('keydown', onFirstGesture, true);
    }

    function resumeAudioPosition(seconds) {
        if (!audio || !isFinite(seconds) || seconds <= 0) return;

        var apply = function () {
            try {
                var max = isFinite(audio.duration) && audio.duration > 0
                    ? Math.max(0, audio.duration - 0.25)
                    : seconds;
                audio.currentTime = Math.min(seconds, max);
            } catch (e) { /* metadata not ready */ }
        };

        if (audio.readyState >= 1) {
            apply();
            return;
        }

        var onLoaded = function () {
            audio.removeEventListener('loadedmetadata', onLoaded);
            apply();
        };
        audio.addEventListener('loadedmetadata', onLoaded);
    }

    /* ── YouTube IFrame API ────────────────────────────────── */
    function ensureYouTube(callback) {
        if (window.YT && window.YT.Player) {
            callback();
            return;
        }

        ytCallbacks.push(callback);

        if (ytLoading) return;
        ytLoading = true;

        var previousReady = window.onYouTubeIframeAPIReady;
        window.onYouTubeIframeAPIReady = function () {
            if (typeof previousReady === 'function') {
                try { previousReady(); } catch (e) { /* ignore */ }
            }

            ytLoading = false;
            var queue = ytCallbacks.slice();
            ytCallbacks = [];
            queue.forEach(function (fn) {
                try { fn(); } catch (e) { /* ignore */ }
            });
        };

        var tag = document.createElement('script');
        tag.src = 'https://www.youtube.com/iframe_api';
        tag.async = true;
        document.head.appendChild(tag);
    }

    function createYouTube(track, startAt, autoplay) {
        if (!els.ytHost || !window.YT || !window.YT.Player) return;

        pendingAutoPlay = !!autoplay;

        yt = new window.YT.Player(els.ytHost, {
            height: '1',
            width: '1',
            videoId: track.youtube_id,
            playerVars: {
                autoplay: 0,
                controls: 0,
                disablekb: 1,
                fs: 0,
                loop: 0,
                modestbranding: 1,
                playsinline: 1,
                rel: 0,
                iv_load_policy: 3,
                origin: window.location.origin
            },
            events: {
                onReady: function (event) {
                    yt = event.target;
                    ytReady = true;

                    applyVolume();

                    // A different track may have been picked while the player
                    // was still booting.
                    if (pendingSwitch) {
                        var next = pendingSwitch;
                        pendingSwitch = null;

                        if (next.autoplay) {
                            try {
                                yt.loadVideoById({ videoId: next.track.youtube_id, startSeconds: next.startAt });
                            } catch (e) { /* ignore */ }
                        } else {
                            try {
                                yt.cueVideoById({ videoId: next.track.youtube_id, startSeconds: next.startAt });
                            } catch (e) { /* ignore */ }
                            setPlayingUI(false);
                        }

                        updateDurationFromYouTube();
                        return;
                    }

                    if (startAt > 0) {
                        try { yt.seekTo(startAt, true); } catch (e) { /* ignore */ }
                    }

                    updateDurationFromYouTube();

                    if (pendingAutoPlay) playYouTube();
                    else setPlayingUI(false);

                    pendingAutoPlay = false;
                },
                onStateChange: function (event) {
                    var state = event.data;

                    if (state === 1) {
                        setPlayingUI(true);
                        savePlaying('1');
                        updateDurationFromYouTube();
                    } else if (state === 2) {
                        setPlayingUI(false);
                    } else if (state === 0) {
                        onTrackEnded();
                    }

                    updateDurationFromYouTube();
                },
                onError: function () {
                    onTrackError();
                }
            }
        });
    }

    function playYouTube() {
        if (!yt || !ytReady) return;
        try { yt.playVideo(); } catch (e) { /* ignore */ }

        window.setTimeout(function () {
            if (!playing) armAutoplayGesture();
        }, 2500);
    }

    function pauseYouTube() {
        if (!yt || !ytReady) return;
        try { yt.pauseVideo(); } catch (e) { /* ignore */ }
    }

    /* ── Track control ─────────────────────────────────────── */
    function playTrack(newIndex, autoplay, resume) {
        if (!tracks[newIndex]) return;

        index = newIndex;
        var track = tracks[index];
        var startAt = resume ? loadPosition(track.id) : 0;

        writeStorage('rs_music_track_id', String(track.id));
        renderTrack(track);
        renderPlaylist();

        if (track.source_type === 'youtube' && track.youtube_id) {
            mode = 'youtube';
            pauseAudio();

            ensureYouTube(function () {
                applyVolume();

                if (yt && !ytReady) {
                    // Player is still initialising — apply this switch onReady.
                    pendingSwitch = { track: track, startAt: startAt, autoplay: autoplay };
                    pendingAutoPlay = !!autoplay;
                    return;
                }

                if (!yt) {
                    createYouTube(track, startAt, autoplay);
                    return;
                }

                if (autoplay) {
                    try {
                        yt.loadVideoById({ videoId: track.youtube_id, startSeconds: startAt });
                    } catch (e) {
                        createYouTube(track, startAt, autoplay);
                    }
                } else {
                    try { yt.cueVideoById({ videoId: track.youtube_id, startSeconds: startAt }); } catch (e) { /* ignore */ }
                    setPlayingUI(false);
                }

                updateDurationFromYouTube();
            });

            return;
        }

        mode = 'audio';
        pauseYouTube();

        ensureAudio();
        audio.src = track.url;
        audio.load();
        resumeAudioPosition(startAt);
        applyVolume();

        if (autoplay) {
            playAudio();
            window.setTimeout(function () {
                if (!playing) armAutoplayGesture();
            }, 2500);
        } else {
            setPlayingUI(false);
        }
    }

    function togglePlay() {
        if (mode === 'youtube') {
            if (!ytReady) {
                playTrack(index, true, false);
                return;
            }

            if (playing) {
                pauseYouTube();
                savePlaying('0');
            } else {
                playYouTube();
                savePlaying('1');
            }
            return;
        }

        if (!audio) {
            playTrack(index, true, false);
            return;
        }

        if (audio.paused) {
            playAudio();
            savePlaying('1');
        } else {
            audio.pause();
            savePlaying('0');
        }
    }

    function nextTrack(autoplay) {
        if (tracks.length === 1) {
            restartCurrent(autoplay);
            return;
        }

        playTrack((index + 1) % tracks.length, autoplay !== false, false);
    }

    function previousTrack() {
        if (tracks.length === 1) {
            restartCurrent(true);
            return;
        }

        playTrack((index - 1 + tracks.length) % tracks.length, true, false);
    }

    function restartCurrent(autoplay) {
        if (mode === 'youtube' && ytReady) {
            try {
                yt.seekTo(0, true);
                if (autoplay !== false) yt.playVideo();
            } catch (e) { /* ignore */ }
        } else if (audio) {
            try {
                audio.currentTime = 0;
                if (autoplay !== false) playAudio();
            } catch (e) { /* ignore */ }
        }
    }

    function onTrackEnded() {
        if (tracks.length <= 1) {
            restartCurrent(true);
            return;
        }

        nextTrack(true);
    }

    function onTrackError() {
        var track = currentTrack();
        if (!track) return;

        if (errorSkips[track.id]) {
            setPlayingUI(false);
            return;
        }

        errorSkips[track.id] = true;
        setPlayingUI(false);

        // Skip a broken track automatically; stop when every track failed.
        if (tracks.length > 1) {
            window.setTimeout(function () { nextTrack(true); }, 350);
        }
    }

    function savePlaying(value) {
        writeSession('rs_music_playing', value);
    }

    /* ── Seeking ───────────────────────────────────────────── */
    function seekToFraction(fraction) {
        if (duration <= 0) return;

        var seconds = clamp(fraction, 0, 1) * duration;

        if (mode === 'youtube' && ytReady) {
            try { yt.seekTo(seconds, true); } catch (e) { /* ignore */ }
        } else if (audio) {
            try { audio.currentTime = seconds; } catch (e) { /* ignore */ }
        }

        currentTime = seconds;
        updateProgressUI();
    }

    function seekFromPointer(event) {
        if (!els.progress) return;

        var rect = els.progress.getBoundingClientRect();
        if (rect.width <= 0) return;

        var clientX = event.touches && event.touches[0] ? event.touches[0].clientX : event.clientX;
        seekToFraction((clientX - rect.left) / rect.width);
    }

    /* ── Media Session (lock screen / OS controls) ─────────── */
    function updateMediaSession(track) {
        if (!('mediaSession' in navigator) || typeof MediaMetadata === 'undefined') return;

        try {
            navigator.mediaSession.metadata = new MediaMetadata({
                title: track.title || '',
                artist: track.artist || '',
                album: 'RoyaStars',
                artwork: track.thumbnail
                    ? [{ src: track.thumbnail, sizes: '480x360', type: 'image/jpeg' }]
                    : []
            });
        } catch (e) { /* ignore */ }
    }

    function bindMediaSession() {
        if (!('mediaSession' in navigator) || !navigator.mediaSession.setActionHandler) return;

        try {
            navigator.mediaSession.setActionHandler('play', togglePlay);
            navigator.mediaSession.setActionHandler('pause', togglePlay);
            navigator.mediaSession.setActionHandler('previoustrack', previousTrack);
            navigator.mediaSession.setActionHandler('nexttrack', function () { nextTrack(true); });
        } catch (e) { /* ignore */ }
    }

    /* ── Collapsed state ───────────────────────────────────── */
    function initialCollapsedState() {
        var stored = readStorage('rs_music_collapsed');
        if (stored !== null) return stored === '1';

        return window.matchMedia('(max-width: 640px)').matches;
    }

    function syncNavOffset() {
        // Auth screens hide the mobile bottom bar — the player may sit lower.
        root.classList.toggle('rs-mp--no-nav', !document.querySelector('.mobile-nav'));
    }

    function setCollapsed(collapsed, persist) {
        root.classList.toggle('is-collapsed', collapsed);

        if (persist) {
            writeStorage('rs_music_collapsed', collapsed ? '1' : '0');
        }

        if (collapsed) togglePlaylist(false);
    }

    /* ── Events ────────────────────────────────────────────── */
    root.querySelectorAll('[data-play]').forEach(function (button) {
        button.addEventListener('click', togglePlay);
    });

    var prevButton = root.querySelector('[data-prev]');
    if (prevButton) prevButton.addEventListener('click', previousTrack);

    var nextButton = root.querySelector('[data-next]');
    if (nextButton) nextButton.addEventListener('click', function () { nextTrack(true); });

    var muteButton = root.querySelector('[data-mute]');
    if (muteButton) muteButton.addEventListener('click', toggleMute);

    if (els.volume) {
        els.volume.addEventListener('input', function () {
            volume = clamp(parseInt(els.volume.value, 10) || 0, 0, 100);
            writeStorage('rs_music_volume', String(volume));

            if (muted && volume > 0) {
                muted = false;
                writeStorage('rs_music_muted', '0');
            }

            applyVolume();
        });
    }

    var collapseButton = root.querySelector('[data-collapse]');
    if (collapseButton) {
        collapseButton.addEventListener('click', function () { setCollapsed(true, true); });
    }

    var expandButton = root.querySelector('[data-expand]');
    if (expandButton) {
        expandButton.addEventListener('click', function () { setCollapsed(false, true); });
    }

    if (els.playlistToggle) {
        els.playlistToggle.addEventListener('click', function () { togglePlaylist(); });
    }

    if (els.progress) {
        var seeking = false;

        els.progress.addEventListener('pointerdown', function (event) {
            seeking = true;
            if (els.progress.setPointerCapture) {
                try { els.progress.setPointerCapture(event.pointerId); } catch (e) { /* ignore */ }
            }
            seekFromPointer(event);
        });

        els.progress.addEventListener('pointermove', function (event) {
            if (seeking) seekFromPointer(event);
        });

        ['pointerup', 'pointercancel'].forEach(function (name) {
            els.progress.addEventListener(name, function () { seeking = false; });
        });

        els.progress.addEventListener('keydown', function (event) {
            if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') return;

            event.preventDefault();
            var delta = event.key === 'ArrowRight' ? 5 : -5;
            var nextTime = clamp(currentTime + delta, 0, duration || 0);
            seekToFraction(duration > 0 ? nextTime / duration : 0);
        });
    }

    // The player survives Turbo navigations; only refresh page-dependent bits.
    document.addEventListener('turbo:load', function () {
        // The new page may not allow music at all — stop and clean up.
        if (!document.getElementById('rs-music-player')) {
            stopTicker();
            pauseAudio();

            if (yt && typeof yt.destroy === 'function') {
                try { yt.destroy(); } catch (e) { /* ignore */ }
            }
            yt = null;
            ytReady = false;

            if (ytWrapper && ytWrapper.parentNode) {
                ytWrapper.parentNode.removeChild(ytWrapper);
            }
            return;
        }

        syncNavOffset();
        root.classList.add('is-ready');
    });

    window.addEventListener('pagehide', function () {
        if (mode === 'youtube' && ytReady) {
            try { currentTime = yt.getCurrentTime() || currentTime; } catch (e) { /* ignore */ }
        }
        savePosition(currentTime, true);
    });

    /* ── Bootstrap ─────────────────────────────────────────── */
    function init() {
        root.hidden = false;
        syncNavOffset();
        setCollapsed(initialCollapsedState(), false);
        updateVolumeUI();
        renderPlaylist();
        bindMediaSession();
        applyVolume();

        var storedPlaying = readSession('rs_music_playing');
        var autoplay = storedPlaying === null ? true : storedPlaying === '1';

        playTrack(index, autoplay, true);

        // Let the entrance transition run after the first paint.
        window.requestAnimationFrame(function () {
            root.classList.add('is-ready');
        });
    }

    init();
})();
