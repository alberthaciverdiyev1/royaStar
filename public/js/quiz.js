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
                + '<span class="material-symbols-outlined !text-sm">play_circle</span>Izah Videosu</div>'
                + '<div class="rounded-xl overflow-hidden border border-[rgb(var(--surface-container-high))/0.6] bg-black/5">'
                + '<iframe class="w-full aspect-video" src="https://www.youtube.com/embed/' + m[1] + '?rel=0" '
                + 'title="Explanation video" frameborder="0" '
                + 'allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" '
                + 'referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe></div></div>';
        }
        return '<div class="mt-3 pt-3 border-t border-[rgb(var(--surface-container-high))/0.6]">'
            + '<div class="inline-flex items-center gap-1.5 text-3xs font-black uppercase tracking-widest text-[rgb(var(--primary))] mb-1.5">'
            + '<span class="material-symbols-outlined !text-sm">play_circle</span>Izah Videosu</div>'
            + '<video class="w-full aspect-video rounded-xl bg-black/5" src="' + escapeHtml(url) + '" controls preload="none"></video></div>';
    }

    function showRegularFeedback(container, chosenAnswer) {
        var fb = container.querySelector('.feedback-box');
        if (!fb) return;
        var correctLetter = (container.getAttribute('data-correct') || '').toLowerCase();
        var videoUrl = container.getAttribute('data-video') || '';
        var isCorrect = correctLetter !== '' && chosenAnswer.toLowerCase() === correctLetter;

        fb.style.display = 'flex';
        fb.className = 'feedback-box ' + (isCorrect ? 'correct' : 'wrong');

        var html = isCorrect
            ? '<span class="material-symbols-outlined !text-xl">check_circle</span>'
              + '<div><span class="font-black">Correct!</span><span class="block text-xs opacity-80">Your answer is right.</span></div>'
            : '<span class="material-symbols-outlined !text-xl">cancel</span>'
              + '<div><span class="font-black">Incorrect!</span><span class="block text-xs opacity-80">The correct answer is highlighted in green.</span></div>';

        html += videoEmbedHtml(videoUrl);
        fb.innerHTML = html;
    }

    window.selectAnswer = function(btn, questionId, chosenAnswer) {
        var container = btn.closest('.quiz-question');
        var allBtns = container.querySelectorAll('.quiz-option-btn');
        var correctLetter = (container.getAttribute('data-correct') || '').toLowerCase();

        document.getElementById('answer_' + questionId).value = chosenAnswer;

        // Lock the question: highlight the correct option, mark the picked one.
        allBtns.forEach(function(b) {
            var letter = (b.getAttribute('data-answer') || '').toLowerCase();
            var icon = b.querySelector('.icon-status');
            b.classList.remove('is-selected');
            b.classList.add('is-disabled');

            if (letter === correctLetter) {
                b.classList.add('is-correct-target');
                if (icon) { icon.textContent = 'check_circle'; icon.style.opacity = '1'; }
            } else if (b === btn) {
                b.classList.add('is-wrong');
                if (icon) { icon.textContent = 'cancel'; icon.style.opacity = '1'; }
            } else {
                if (icon) { icon.textContent = 'radio_button_unchecked'; icon.style.opacity = '0'; }
            }
        });

        if (correctLetter === chosenAnswer.toLowerCase()) {
            btn.classList.add('is-correct');
        } else {
            btn.classList.add('is-wrong');
        }

        showRegularFeedback(container, chosenAnswer);
    };

    window.setOpenAnswer = function(questionId, value) {
        document.getElementById('answer_' + questionId).value = value;
        var input = document.getElementById('open_input_' + questionId);
        if (!input) return;
        var container = input.closest('.quiz-question');
        var fb = container ? container.querySelector('.feedback-box') : null;
        if (!fb) return;

        if (!value || !value.trim()) {
            fb.style.display = 'none';
            return;
        }

        var modelAnswer = container.getAttribute('data-correct') || '';
        var videoUrl = container.getAttribute('data-video') || '';

        fb.style.display = 'flex';
        fb.className = 'feedback-box correct';

        var html = '<span class="material-symbols-outlined !text-xl">lightbulb</span>'
            + '<div><span class="font-black">Expected answer:</span>'
            + (modelAnswer ? '<span class="block text-xs font-bold mt-0.5">' + escapeHtml(modelAnswer) + '</span>' : '')
            + '</div>';
        html += videoEmbedHtml(videoUrl);
        fb.innerHTML = html;
    };
})();
