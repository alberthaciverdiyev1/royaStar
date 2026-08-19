document.addEventListener('DOMContentLoaded', function() {
    // Admin-editable UI strings injected from the lesson Blade view.
    var i18n = (function() {
        var base = {
            rate_thanks: 'Thank You!',
            rate_success_desc: 'Your feedback has been saved successfully.',
            rate_submit: 'Submit Feedback',
            rate_error_generic: 'Something went wrong.',
            rate_error_retry: 'Something went wrong. Please try again.'
        };
        var root = document.querySelector('[data-i18n]');
        if (!root) return base;
        try {
            var parsed = JSON.parse(root.getAttribute('data-i18n') || '{}');
            for (var k in parsed) { if (parsed.hasOwnProperty(k)) base[k] = parsed[k]; }
        } catch (e) {}
        return base;
    })();

    // Plyr player setup. If the CDN script fails to load (blocked/slow), the
    // rating + progress features below must still work — so guard it.
    var players = [];
    if (typeof Plyr !== 'undefined') {
        try {
            players = Plyr.setup('.js-plyr-player', {
                controls: ['play-large', 'play', 'progress', 'current-time', 'mute', 'volume', 'fullscreen'],
                youtube: {
                    noCookie: true,
                    rel: 0,
                    showinfo: 0,
                    iv_load_policy: 3,
                    modestbranding: 1
                }
            });
        } catch (e) {
            players = [];
        }
    }

    // ── Lesson watch-progress tracking ──
    // The web lesson page reports watch progress so the student earns the
    // "lesson completed" star (and the lesson view counter) without needing an
    // API token. Progress is throttled; reaching 100% marks the lesson done.
    (function() {
        var lessonId = (document.querySelector('[data-lesson-id]') || {}).getAttribute
            ? document.querySelector('[data-lesson-id]').getAttribute('data-lesson-id')
            : null;
        var progressUrl = (document.querySelector('[data-progress-url]') || {}).getAttribute
            ? document.querySelector('[data-progress-url]').getAttribute('data-progress-url')
            : null;
        var csrfMeta = document.querySelector('meta[name="csrf-token"]');
        if (!lessonId || !progressUrl || !csrfMeta || !players.length) return;

        var csrfToken = csrfMeta.getAttribute('content');
        var lastReported = 0;

        function reportProgress(progress) {
            progress = Math.max(0, Math.min(100, Math.round(progress)));
            // Don't report the same or a lower value again; throttle non-100 updates.
            if (progress < lastReported) return;
            if (progress < 100 && progress - lastReported < 5) return;
            lastReported = progress;

            fetch(progressUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ progress: progress }),
            }).catch(function() { /* non-fatal: keep watching */ });
        }

        players.forEach(function(player) {
            player.on('timeupdate', function() {
                var dur = player.duration || 0;
                if (dur > 0) {
                    reportProgress((player.currentTime / dur) * 100);
                }
            });
            player.on('ended', function() {
                reportProgress(100);
            });
        });
    })();

    // ── Rating star handler ──
    var stars = document.querySelectorAll('.lesson-star-btn');
    var input = document.getElementById('ratingInput');
    if (stars.length && input) {
        function resetStars(selected) {
            stars.forEach(function(s, i) {
                var icon = s.querySelector('.material-symbols-outlined');
                if (i < selected) {
                    icon.style.fontVariationSettings = "'FILL' 1";
                    s.classList.add('active');
                } else {
                    icon.style.fontVariationSettings = "'FILL' 0";
                    s.classList.remove('active');
                }
            });
        }

        stars.forEach(function(star, idx) {
            star.addEventListener('click', function() {
                var val = idx + 1;
                input.value = val;
                resetStars(val);
            });
            star.addEventListener('mouseenter', function() {
                stars.forEach(function(s, i) {
                    var icon = s.querySelector('.material-symbols-outlined');
                    icon.style.fontVariationSettings = i <= idx ? "'FILL' 1" : "'FILL' 0";
                });
            });
            star.addEventListener('mouseleave', function() {
                var selected = parseInt(input?.value || 0);
                stars.forEach(function(s, i) {
                    var icon = s.querySelector('.material-symbols-outlined');
                    if (i >= selected) {
                        icon.style.fontVariationSettings = "'FILL' 0";
                    } else {
                        icon.style.fontVariationSettings = "'FILL' 1";
                    }
                });
            });
        });
    }

    // ── JS form submit + rocket animation ──
    var form = document.getElementById('rateForm');
    if (form) {
        var submitBtn = document.getElementById('rateSubmitBtn');
        var reviewInput = document.getElementById('reviewInput');

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            // A rating OR a written review is enough (the backend allows either).
            var rating = input ? input.value : '';
            var reviewText = reviewInput ? reviewInput.value.trim() : '';
            if (!rating && !reviewText) {
                var p = form.querySelector('.lesson-star-btn');
                if (p) {
                    p.style.animation = 'shake 0.4s ease';
                    setTimeout(function() { p.style.animation = ''; }, 500);
                }
                return;
            }

            // Disable button
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="material-symbols-outlined !text-lg animate-spin">progress_activity</span>';

            // Get CSRF token
            var csrfToken = form.querySelector('input[name="_token"]').value;
            var rateUrl = form.getAttribute('data-rate-url');

            fetch(rateUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    rating: rating ? parseInt(rating, 10) : null,
                    review: reviewInput ? reviewInput.value : '',
                }),
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    launchRocket();
                    // Replace form with success message
                    var card = form;
                    card.innerHTML =
                        '<div class="text-center space-y-3 py-4">' +
                            '<span class="material-symbols-outlined !text-5xl text-emerald-500" style="font-variation-settings:\'FILL\' 1">check_circle</span>' +
                            '<p class="text-lg font-black text-[rgb(var(--on-surface))]">' + i18n.rate_thanks + '</p>' +
                            '<p class="text-xs font-semibold text-[rgb(var(--on-surface))/0.6]">' + i18n.rate_success_desc + '</p>' +
                            '<div class="flex justify-center items-center gap-1 pt-2">' +
                                Array.from({length: parseInt(rating)}, function() {
                                    return '<span class="material-symbols-outlined !text-3xl text-[rgb(var(--tertiary))]" style="font-variation-settings:\'FILL\' 1">star</span>';
                                }).join('') +
                            '</div>' +
                        '</div>';
                } else {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<span>' + i18n.rate_submit + '</span><span class="material-symbols-outlined !text-lg">rocket_launch</span>';
                    alert(data.message || i18n.rate_error_generic);
                }
            })
            .catch(function() {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<span>' + i18n.rate_submit + '</span><span class="material-symbols-outlined !text-lg">rocket_launch</span>';
                alert(i18n.rate_error_retry);
            });
        });
    }

    // ── Rocket animation ──
    function launchRocket() {
        var container = document.getElementById('rocketContainer');
        var rocket = document.getElementById('rocket');
        var sparks = [
            document.getElementById('spark1'),
            document.getElementById('spark2'),
            document.getElementById('spark3'),
            document.getElementById('spark4'),
            document.getElementById('spark5'),
        ];

        var btnRect = submitBtn.getBoundingClientRect();
        var startX = btnRect.left + btnRect.width / 2;
        var startY = btnRect.top;

        var ROCKET_SIZE = 120;
        var DURATION = 4000;

        container.style.display = 'block';

        rocket.style.transition = 'none';
        rocket.style.left = (startX - ROCKET_SIZE / 2) + 'px';
        rocket.style.top = startY + 'px';
        rocket.style.fontSize = ROCKET_SIZE + 'px';
        rocket.style.lineHeight = '1';
        rocket.style.opacity = '1';
        rocket.style.transform = 'scale(1)';

        sparks.forEach(function(s, i) {
            s.style.transition = 'none';
            s.style.position = 'absolute';
            var spread = (i - 2) * 20;
            s.style.left = (startX + spread) + 'px';
            s.style.top = (startY + ROCKET_SIZE / 2 + 10) + 'px';
            s.style.opacity = '1';
        });

        requestAnimationFrame(function() {
            rocket.style.transition = 'all ' + DURATION + 'ms cubic-bezier(0.15, 0.85, 0.35, 1)';
            rocket.style.top = '-200px';
            rocket.style.opacity = '0';
            rocket.style.transform = 'scale(1.2)';

            sparks.forEach(function(s, i) {
                s.style.transition = 'all ' + DURATION + 'ms cubic-bezier(0.15, 0.85, 0.35, 1)';
                s.style.top = '-250px';
                s.style.opacity = '0';
                s.style.left = (startX + (i - 2) * 20 + (Math.random() - 0.5) * 80) + 'px';
            });
        });

        var trailInterval = setInterval(function() {
            for (var t = 0; t < 2; t++) {
                (function() {
                    var trail = document.createElement('div');
                    trail.className = 'absolute rounded-full';
                    var size = 6 + Math.random() * 10;
                    trail.style.width = size + 'px';
                    trail.style.height = size + 'px';
                    trail.style.background = ['#f59e0b', '#ef4444', '#f97316', '#dc2626'][Math.floor(Math.random() * 4)];
                    trail.style.left = (startX + (Math.random() - 0.5) * 40) + 'px';
                    trail.style.top = (startY + ROCKET_SIZE / 2 + Math.random() * 20) + 'px';
                    trail.style.opacity = '0.9';
                    trail.style.transition = 'all 1.8s ease-out';
                    trail.style.position = 'absolute';
                    trail.style.borderRadius = '50%';
                    container.appendChild(trail);
                    requestAnimationFrame(function() {
                        trail.style.top = (parseInt(trail.style.top) - 60 - Math.random() * 80) + 'px';
                        trail.style.opacity = '0';
                        trail.style.transform = 'translateX(' + (Math.random() - 0.5) * 50 + 'px)';
                    });
                    setTimeout(function() { trail.remove(); }, 2000);
                })();
            }
        }, 200);

        setTimeout(function() {
            clearInterval(trailInterval);
            container.style.display = 'none';
        }, DURATION + 500);
    }
});
