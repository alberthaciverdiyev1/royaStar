document.querySelectorAll('[data-auto-search]').forEach(function(input) {
    var timer;
    input.addEventListener('input', function() {
        clearTimeout(timer);
        timer = setTimeout(function() { input.closest('form').submit(); }, 350);
    });
});
