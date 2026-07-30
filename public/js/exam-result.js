function filterQuestions(type, btn) {
    document.querySelectorAll('.filter-tab-btn').forEach(function(b) {
        b.classList.remove('active');
    });
    btn.classList.add('active');

    document.querySelectorAll('.q-item-card').forEach(function(card) {
        var status = card.getAttribute('data-status');
        if (type === 'all' || status === type) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
}
