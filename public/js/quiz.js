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

    function videoEmbedHtml(url) {
        if (!url) return '';
        var m = url.match(/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([A-Za-z0-9_-]{6,})/);
        if (m) {
            return '<div class="mt-3 pt-3 border-t border-[rgb(var(--surface-container-high))/0.6]">'
                + '<div class="inline-flex items-center gap-1.5 text-3xs font-black uppercase tracking-widest text-[rgb(var(--primary))] mb-1.5">'
                + '<span class="material-symbols-outlined !text-sm">play_circle</span>İzah Videosu</div>'
                + '<div class="rounded-xl overflow-hidden border border-[rgb(var(--surface-container-high))/0.6] bg-black/5">'
                + '<iframe class="w-full aspect-video" src="https://www.youtube.com/embed/' + m[1] + '?rel=0" '
                + 'title="Explanation video" frameborder="0" '
                + 'allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" '
                + 'referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe></div></div>';
        }
        return '<div class="mt-3 pt-3 border-t border-[rgb(var(--surface-container-high))/0.6]">'
            + '<div class="inline-flex items-center gap-1.5 text-3xs font-black uppercase tracking-widest text-[rgb(var(--primary))] mb-1.5">'
            + '<span class="material-symbols-outlined !text-sm">play_circle</span>İzah Videosu</div>'
            + '<video class="w-full aspect-video rounded-xl bg-black/5" src="' + escapeHtml(url) + '" controls preload="none"></video></div>';
    }

    function setPendingFeedback(fb, message) {
        if (!fb) return;
        fb.style.display = 'flex';
        fb.className = 'feedback-box pending';
        fb.innerHTML = '<span class="material-symbols-outlined !text-xl animate-spin">progress_activity</span>'
            + '<div><span class="font-black">' + escapeHtml(message) + '</span></div>';
    }

    function showRegularFeedback(container, result) {
        var fb = container.querySelector('.feedback-box');
        if (!fb) return;
        var isCorrect = !!result.correct;

        fb.style.display = 'flex';
        fb.className = 'feedback-box ' + (isCorrect ? 'correct' : 'wrong');

        var html = isCorrect
            ? '<span class="material-symbols-outlined !text-xl">check_circle</span>'
              + '<div><span class="font-black">Correct!</span><span class="block text-xs opacity-80">Your answer is right.</span></div>'
            : '<span class="material-symbols-outlined !text-xl">cancel</span>'
              + '<div><span class="font-black">Incorrect!</span><span class="block text-xs opacity-80">The correct answer is highlighted in green.</span></div>';

        html += videoEmbedHtml(result.explanation_video_url || '');
        fb.innerHTML = html;
    }

    function showOpenFeedback(container, result) {
        var fb = container.querySelector('.feedback-box');
        if (!fb) return;
        var isCorrect = !!result.correct;

        fb.style.display = 'flex';
        fb.className = 'feedback-box ' + (isCorrect ? 'correct' : 'wrong');

        var html = isCorrect
            ? '<span class="material-symbols-outlined !text-xl">check_circle</span>'
              + '<div><span class="font-black">Correct!</span><span class="block text-xs opacity-80">Your answer is right.</span></div>'
            : '<span class="material-symbols-outlined !text-xl">cancel</span>'
              + '<div><span class="font-black">Incorrect!</span><span class="block text-xs opacity-80">The expected answer is shown on the result page.</span></div>';

        html += videoEmbedHtml(result.explanation_video_url || '');
        fb.innerHTML = html;
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
        setPendingFeedback(fb, 'Checking...');

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
        setPendingFeedback(fb, 'Checking...');

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
