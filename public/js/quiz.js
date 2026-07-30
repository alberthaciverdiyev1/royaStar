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

    window.selectAnswer = function(btn, questionId, chosenAnswer, rightAnswer) {
        var container = btn.closest('.quiz-question');
        var feedbackEl = document.getElementById('feedback_' + questionId);
        var allBtns = container.querySelectorAll('.quiz-option-btn');
        var alreadyAnswered = false;
        allBtns.forEach(function(b) {
            if (b.classList.contains('is-correct') || b.classList.contains('is-wrong')) {
                alreadyAnswered = true;
            }
        });
        if (alreadyAnswered) return;

        document.getElementById('answer_' + questionId).value = chosenAnswer;
        var isRight = (chosenAnswer.toLowerCase() === rightAnswer.toLowerCase());

        allBtns.forEach(function(b) {
            b.classList.add('is-disabled');
            var ans = b.getAttribute('data-answer');
            var icon = b.querySelector('.icon-status');

            if (ans.toLowerCase() === chosenAnswer.toLowerCase()) {
                if (isRight) {
                    b.classList.add('is-correct');
                    if (icon) { icon.textContent = 'check_circle'; icon.style.opacity = '1'; }
                } else {
                    b.classList.add('is-wrong');
                    if (icon) { icon.textContent = 'cancel'; icon.style.opacity = '1'; }
                }
            } else if (!isRight && ans.toLowerCase() === rightAnswer.toLowerCase()) {
                b.classList.add('is-correct-target');
                if (icon) { icon.textContent = 'check_circle'; icon.style.opacity = '1'; }
            }
        });

        if (feedbackEl) {
            feedbackEl.classList.remove('hidden');
            if (isRight) {
                feedbackEl.className = 'feedback-box correct';
                feedbackEl.innerHTML = '<span class="material-symbols-outlined !text-xl">auto_awesome</span>' +
                    '<div><strong class="font-black text-xs uppercase tracking-wide block">Correct Answer! ⭐</strong>' +
                    '<span class="text-[11px]">Great job!</span></div>';
            } else {
                feedbackEl.className = 'feedback-box wrong';
                feedbackEl.innerHTML = '<span class="material-symbols-outlined !text-xl">error</span>' +
                    '<div><strong class="font-black text-xs uppercase tracking-wide block">Incorrect Answer!</strong>' +
                    '<span class="text-[11px]">The correct answer is Option <strong>' + rightAnswer.toUpperCase() + '</strong>.</span></div>';
            }
        }
    };

    window.setOpenAnswer = function(questionId, value) {
        document.getElementById('answer_' + questionId).value = value;
    };

    window.checkOpenAnswer = function(questionId, expectedAnswer) {
        var inputVal = (document.getElementById('open_input_' + questionId).value || '').trim();
        var feedbackEl = document.getElementById('feedback_' + questionId);

        if (!inputVal) {
            alert('Please type an answer first!');
            return;
        }

        if (feedbackEl) {
            feedbackEl.classList.remove('hidden');
            var isMatch = inputVal.toLowerCase() === expectedAnswer.trim().toLowerCase();

            if (isMatch) {
                feedbackEl.className = 'feedback-box correct';
                feedbackEl.innerHTML = '<span class="material-symbols-outlined !text-xl">auto_awesome</span>' +
                    '<div><strong class="font-black text-xs uppercase tracking-wide block">Correct Answer! ⭐</strong>' +
                    '<span class="text-[11px]">Your answer matches the expected answer!</span></div>';
            } else {
                feedbackEl.className = 'feedback-box wrong';
                feedbackEl.innerHTML = '<span class="material-symbols-outlined !text-xl">info</span>' +
                    '<div><strong class="font-black text-xs uppercase tracking-wide block">Submitted for Evaluation</strong>' +
                    '<span class="text-[11px]">Expected answer: <strong>' + expectedAnswer + '</strong></span></div>';
            }
        }
    };
})();
