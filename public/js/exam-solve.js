(function() {
    // All exam questions are displayed on a single page. The student scrolls
    // through them, answers each one, then submits at the bottom.
    // (This is exam-only — quizzes keep their step-by-step flow.)

    // Select an answer for regular questions. The student may change their pick
    // until the exam is submitted (no feedback shown mid-exam).
    window.selectAnswer = function(btn, questionId, chosenAnswer) {
        var container = btn.closest('.exam-question');
        var allBtns = container.querySelectorAll('.exam-option-btn');

        document.getElementById('answer_' + questionId).value = chosenAnswer;

        allBtns.forEach(function(b) {
            var icon = b.querySelector('.icon-status');
            b.classList.remove('is-selected');
            b.classList.remove('is-correct', 'is-wrong', 'is-correct-target');

            if (b === btn) {
                b.classList.add('is-selected');
                if (icon) { icon.textContent = 'check_circle'; icon.style.opacity = '1'; }
            } else {
                if (icon) { icon.textContent = 'radio_button_unchecked'; icon.style.opacity = '0'; }
            }
        });
    };

    window.setOpenAnswer = function(questionId, value) {
        document.getElementById('answer_' + questionId).value = value;
    };

    // Scroll-to-top button for long single-page exams.
    var scrollTopBtn = document.getElementById('scrollTopBtn');
    if (scrollTopBtn) {
        scrollTopBtn.addEventListener('click', function() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }
})();
