(function() {
    var currentIndex = 0;
    var wrapper = document.querySelector('.quiz-wrapper');
    var total = parseInt(wrapper ? wrapper.getAttribute('data-total-steps') : '0');
    var questions = document.querySelectorAll('.quiz-question');
    var prevBtn = document.getElementById('prevBtn');
    var nextBtn = document.getElementById('nextBtn');
    var submitBtn = document.getElementById('submitBtn');
    var progressBar = document.getElementById('progressBar');
    var stepLabel = document.getElementById('currentStep');

    // Editable UI strings injected from the Blade view (admin-customizable).
    // Fallbacks mirror the config defaults if the attribute is missing.
    var i18n = (function() {
        var base = {
            checking: 'Checking...',
            correct_title: 'Correct!',
            correct_sub: 'Your answer is right.',
            incorrect_title: 'Incorrect!',
            incorrect_sub: 'The correct answer is highlighted in green.',
            incorrect_open_sub: 'The expected answer is shown on the result page.',
            explanation_video: 'İzah Videosu'
        };
        if (!wrapper) return base;
        try {
            var parsed = JSON.parse(wrapper.getAttribute('data-i18n') || '{}');
            for (var k in parsed) { if (parsed.hasOwnProperty(k)) base[k] = parsed[k]; }
        } catch (e) {}
        return base;
    })();

    window.navigateQuestion = function(dir) {
        if (questions[currentIndex]) {
            questions[currentIndex].style.display = 'none';
        }
        currentIndex += dir;
        if (currentIndex < 0) currentIndex = 0;
        if (currentIndex >= total) currentIndex = total - 1;

        if (questions[currentIndex]) {
            questions[currentIndex].style.display = '';
        }

        prevBtn.style.display = currentIndex > 0 ? '' : 'none';
        if (currentIndex === total - 1) {
            nextBtn.style.display = 'none';
            submitBtn.style.display = '';
        } else {
            nextBtn.style.display = '';
            submitBtn.style.display = 'none';
        }

        if (total > 0) {
            progressBar.style.width = ((currentIndex + 1) / total * 100) + '%';
        }
        stepLabel.textContent = currentIndex + 1;
    };

    function escapeHtml(s) {
        return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    // ── Feedback rendering ─────────────────────────────────────────────
    // These are styled entirely by self-contained classes in public/css/quiz.css
    // (no Tailwind arbitrary classes — public/js is not scanned by Tailwind).

    // Status row: icon + title + optional subtitle. Pass isCorrect = null for
    // the "pending/checking" state (spinner icon).
    function fbStatusHtml(isCorrect, title, sub) {
        var icon = isCorrect === null ? 'progress_activity' : (isCorrect ? 'check_circle' : 'cancel');
        var iconClass = isCorrect === null ? ' fb-status-icon--spin' : '';
        return '<div class="fb-status">'
            + '<span class="material-symbols-outlined fb-status-icon' + iconClass + '">' + icon + '</span>'
            + '<div class="fb-status-text">'
            + '<span class="fb-status-title">' + escapeHtml(title) + '</span>'
            + (sub ? '<span class="fb-status-sub">' + escapeHtml(sub) + '</span>' : '')
            + '</div>'
            + '</div>';
    }

    // Video card: "İzah Videosu" header + the same Plyr player used on the
    // lesson page (cdn.plyr.io is loaded by quiz.blade.php). Dynamically
    // injected players are set up by initPlayer() after the feedback renders.
    function videoEmbedHtml(url) {
        if (!url) return '';
        var m = url.match(/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([A-Za-z0-9_-]{6,})/);
        var media;
        if (m) {
            // Plyr video-embed wrapper (YouTube), same as the lesson player.
            media = '<div class="plyr__video-embed js-plyr-player">'
                + '<iframe src="https://www.youtube-nocookie.com/embed/' + m[1]
                + '?origin=' + encodeURIComponent(window.location.origin)
                + '&amp;iv_load_policy=3&amp;modestbranding=1&amp;playsinline=1&amp;showinfo=0&amp;rel=0&amp;enablejsapi=1"'
                + ' allowfullscreen allowtransparency allow="autoplay"></iframe>'
                + '</div>';
        } else {
            media = '<video class="js-plyr-player" controls playsinline>'
                + '<source src="' + escapeHtml(url) + '">'
                + '</video>';
        }
        return '<div class="fb-video">'
            + '<div class="fb-video-head">'
            + '<span class="material-symbols-outlined fb-video-head-icon">play_circle</span>'
            + '<span class="fb-video-head-label">' + escapeHtml(i18n.explanation_video) + '</span>'
            + '</div>'
            + '<div class="fb-video-frame">' + media + '</div>'
            + '</div>';
    }

    // Initialize the Plyr player for a just-injected feedback box.
    // NOTE: this Plyr build only sets up players when given a selector string,
    // NodeList or array — a bare element is ignored — so we collect the fresh
    // players into an array and pass them in one call.
    function initPlayer(scope) {
        if (!window.Plyr || !scope) return;
        var playerEls = scope.querySelectorAll('.js-plyr-player');
        if (!playerEls.length) return;
        var fresh = [];
        playerEls.forEach(function(el) {
            if (!el.getAttribute('data-plyr')) {
                el.setAttribute('data-plyr', '1');
                fresh.push(el);
            }
        });
        if (fresh.length) {
            Plyr.setup(fresh, {
                controls: ['play-large', 'play', 'progress', 'current-time', 'mute', 'volume', 'fullscreen'],
                youtube: {
                    noCookie: true,
                    rel: 0,
                    showinfo: 0,
                    iv_load_policy: 3,
                    modestbranding: 1
                }
            });
        }
    }

    function setPendingFeedback(fb, message) {
        if (!fb) return;
        fb.style.display = 'block';
        fb.className = 'feedback-box pending';
        fb.innerHTML = fbStatusHtml(null, message, '');
    }

    function showRegularFeedback(container, result) {
        var fb = container.querySelector('.feedback-box');
        if (!fb) return;
        var isCorrect = !!result.correct;

        fb.style.display = 'block';
        fb.className = 'feedback-box ' + (isCorrect ? 'correct' : 'wrong');

        var status = isCorrect
            ? fbStatusHtml(true, i18n.correct_title, i18n.correct_sub)
            : fbStatusHtml(false, i18n.incorrect_title, i18n.incorrect_sub);

        fb.innerHTML = status + videoEmbedHtml(result.explanation_video_url || '');
        initPlayer(fb);
    }

    function showOpenFeedback(container, result) {
        var fb = container.querySelector('.feedback-box');
        if (!fb) return;
        var isCorrect = !!result.correct;

        fb.style.display = 'block';
        fb.className = 'feedback-box ' + (isCorrect ? 'correct' : 'wrong');

        var status = isCorrect
            ? fbStatusHtml(true, i18n.correct_title, i18n.correct_sub)
            : fbStatusHtml(false, i18n.incorrect_title, i18n.incorrect_open_sub);

        fb.innerHTML = status + videoEmbedHtml(result.explanation_video_url || '');
        initPlayer(fb);
    }

    // Reveal the correct option + the picked one using the SERVER response
    // (the correct letter is never present in the page HTML before confirming).
    function lockAndHighlight(container, result, pickedBtn) {
        var allBtns = container.querySelectorAll('.quiz-option-btn');
        var correctLetter = (result.correct_answer || '').toLowerCase();

        allBtns.forEach(function(b) {
            var letter = (b.getAttribute('data-answer') || '').toLowerCase();
            var icon = b.querySelector('.icon-status');
            b.classList.remove('is-selected');
            b.classList.add('is-disabled');

            if (letter === correctLetter) {
                b.classList.add('is-correct-target');
                if (icon) { icon.textContent = 'check_circle'; icon.style.opacity = '1'; }
            } else if (b !== pickedBtn) {
                if (icon) { icon.textContent = 'radio_button_unchecked'; icon.style.opacity = '0'; }
            }
        });

        if (result.correct) {
            pickedBtn.classList.add('is-correct');
        } else {
            pickedBtn.classList.add('is-wrong');
            var icon = pickedBtn.querySelector('.icon-status');
            if (icon) { icon.textContent = 'cancel'; icon.style.opacity = '1'; }
        }
    }

    function postCheck(checkUrl, payload) {
        var form = document.getElementById('quizForm');
        var csrfToken = form ? form.querySelector('input[name="_token"]').value : '';
        return fetch(checkUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify(payload),
        }).then(function(r) {
            if (!r.ok) { throw new Error('Server rejected check'); }
            return r.json();
        });
    }

    // Step 1 — pick an option: highlight it, reveal the Confirm button.
    window.selectAnswer = function(btn, questionId, chosenAnswer) {
        var container = btn.closest('.quiz-question');
        if (!container || container.getAttribute('data-answered') === '1') return;

        document.getElementById('answer_' + questionId).value = chosenAnswer;

        var allBtns = container.querySelectorAll('.quiz-option-btn');
        allBtns.forEach(function(b) {
            var icon = b.querySelector('.icon-status');
            b.classList.remove('is-selected');
            if (icon) { icon.textContent = 'radio_button_unchecked'; icon.style.opacity = '0'; }
        });

        btn.classList.add('is-selected');
        var icon = btn.querySelector('.icon-status');
        if (icon) { icon.textContent = 'check_circle'; icon.style.opacity = '1'; }

        var confirmBtn = document.getElementById('confirm_' + questionId);
        if (confirmBtn) {
            confirmBtn.setAttribute('data-answer', chosenAnswer);
            confirmBtn.style.display = 'inline-flex';
        }
    };

    // Step 2 — commit the answer: ask the server, reveal right/wrong + video.
    window.confirmAnswer = function(btn) {
        var container = btn.closest('.quiz-question');
        if (!container || container.getAttribute('data-answered') === '1') return;
        container.setAttribute('data-answered', '1');

        var questionId = parseInt(btn.getAttribute('data-question'));
        var chosenAnswer = btn.getAttribute('data-answer') || '';

        var allBtns = container.querySelectorAll('.quiz-option-btn');
        var pickedBtn = null;
        allBtns.forEach(function(b) {
            b.classList.add('is-disabled');
            if ((b.getAttribute('data-answer') || '') === chosenAnswer) pickedBtn = b;
        });

        btn.style.display = 'none';

        var fb = container.querySelector('.feedback-box');
        setPendingFeedback(fb, i18n.checking);

        var checkUrl = document.querySelector('.quiz-wrapper').getAttribute('data-check-url');
        postCheck(checkUrl, { question_id: questionId, answer: chosenAnswer })
            .then(function(result) {
                lockAndHighlight(container, result, pickedBtn);
                showRegularFeedback(container, result);
            })
            .catch(function() {
                // Network/server failure → allow retry.
                container.removeAttribute('data-answered');
                allBtns.forEach(function(b) { b.classList.remove('is-disabled'); });
                btn.style.display = 'inline-flex';
                if (fb) { fb.style.display = 'none'; }
            });
    };

    window.setOpenAnswer = function(questionId, value) {
        document.getElementById('answer_' + questionId).value = value;
        var confirmBtn = document.getElementById('confirm_open_' + questionId);
        if (!confirmBtn) return;
        confirmBtn.style.display = (value && value.trim()) ? 'inline-flex' : 'none';
    };

    window.confirmOpenAnswer = function(btn) {
        var container = btn.closest('.quiz-question');
        if (!container || container.getAttribute('data-answered') === '1') return;
        container.setAttribute('data-answered', '1');

        var questionId = parseInt(btn.getAttribute('data-question'));
        var input = document.getElementById('open_input_' + questionId);
        var value = input ? input.value : '';

        btn.style.display = 'none';
        if (input) { input.disabled = true; }

        var fb = container.querySelector('.feedback-box');
        setPendingFeedback(fb, i18n.checking);

        var checkUrl = document.querySelector('.quiz-wrapper').getAttribute('data-check-url');
        postCheck(checkUrl, { question_id: questionId, answer: value })
            .then(function(result) {
                showOpenFeedback(container, result);
            })
            .catch(function() {
                container.removeAttribute('data-answered');
                btn.style.display = 'inline-flex';
                if (input) { input.disabled = false; }
                if (fb) { fb.style.display = 'none'; }
            });
    };
})();
