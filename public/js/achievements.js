function switchMainTab(tabName) {
    if (tabName === 'achievements') {
        document.getElementById('tab-content-achievements').classList.remove('hidden');
        document.getElementById('tab-content-leaderboard').classList.add('hidden');
        document.getElementById('tab-btn-achievements').classList.add('active');
        document.getElementById('tab-btn-leaderboard').classList.remove('active');
    } else {
        document.getElementById('tab-content-achievements').classList.add('hidden');
        document.getElementById('tab-content-leaderboard').classList.remove('hidden');
        document.getElementById('tab-btn-achievements').classList.remove('active');
        document.getElementById('tab-btn-leaderboard').classList.add('active');
    }
}

function filterBadges(type, btn) {
    document.querySelectorAll('.achieve-tab-btn').forEach(function(b) {
        b.classList.remove('active');
    });
    btn.classList.add('active');

    document.querySelectorAll('.badge-item-card').forEach(function(card) {
        var status = card.getAttribute('data-status');
        if (type === 'all' || status === type) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
}
