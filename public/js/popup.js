(function () {
    'use strict';

    var root = document.getElementById('site-popup');
    if (!root) return;

    var imageEl = document.getElementById('site-popup-image');
    var titleEl = document.getElementById('site-popup-title');
    var contentEl = document.getElementById('site-popup-content');
    var rememberEl = document.getElementById('site-popup-remember');

    var popups;
    try {
        popups = JSON.parse(root.getAttribute('data-popups') || '[]');
    } catch (e) {
        return;
    }
    if (!Array.isArray(popups) || popups.length === 0) return;

    // Dismissal is remembered per popup and per version: editing a popup in the
    // admin panel bumps its version, so visitors see the updated content again.
    function storageKey(popup) {
        return 'rs_popup_dismissed_' + popup.id + '_' + popup.version;
    }

    function isDismissed(popup) {
        try {
            return localStorage.getItem(storageKey(popup)) === '1';
        } catch (e) {
            return false;
        }
    }

    var available = popups.filter(function (popup) {
        return !isDismissed(popup);
    });
    if (available.length === 0) return;

    // Random pick on every visit (the server sends all active popups).
    var popup = available[Math.floor(Math.random() * available.length)];

    if (popup.image) {
        imageEl.src = popup.image;
        imageEl.alt = popup.title || '';
        imageEl.hidden = false;
        imageEl.addEventListener('error', function () {
            imageEl.hidden = true;
        });
    }

    if (popup.title) {
        titleEl.textContent = popup.title;
        titleEl.hidden = false;
    }

    if (popup.content) {
        contentEl.textContent = popup.content;
        contentEl.hidden = false;
    }

    // Nothing renderable (e.g. image failed to load) — don't show an empty card.
    if (imageEl.hidden && titleEl.hidden && contentEl.hidden) return;

    var shown = false;

    function show() {
        if (shown) return;
        shown = true;
        root.hidden = false;
        document.body.classList.add('site-popup-open');

        var closeBtn = root.querySelector('.site-popup__close');
        if (closeBtn) closeBtn.focus({ preventScroll: true });
    }

    function hide() {
        if (rememberEl.checked) {
            try {
                localStorage.setItem(storageKey(popup), '1');
            } catch (e) {
                // Private mode / storage disabled — simply hide for this visit.
            }
        }

        root.hidden = true;
        document.body.classList.remove('site-popup-open');
        document.removeEventListener('keydown', onKeydown);
    }

    function onKeydown(event) {
        if (event.key === 'Escape') hide();
    }

    root.querySelectorAll('[data-popup-close]').forEach(function (el) {
        el.addEventListener('click', hide);
    });

    document.addEventListener('keydown', onKeydown);

    // Slight delay so the popup doesn't compete with the initial page paint.
    setTimeout(show, 600);
})();
