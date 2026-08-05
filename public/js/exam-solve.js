(function() {
    var currentIndex = 0;
    var wrapper = document.querySelector('.exam-wrapper');
    var total = parseInt(wrapper ? wrapper.getAttribute('data-total-steps') : '0');
    var questions = document.querySelectorAll('.exam-question');
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
})();
