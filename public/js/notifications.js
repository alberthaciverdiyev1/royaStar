(function () {
    'use strict';

    var LISTENER_FLAG = '__rsNotificationsBound';

    function csrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function getConfig() {
        return document.getElementById('rs-push-config');
    }

    function storageGet(key) {
        try {
            return window.localStorage.getItem(key);
        } catch (e) {
            return null;
        }
    }

    function storageSet(key, value) {
        try {
            window.localStorage.setItem(key, value);
        } catch (e) {
            // Private mode / storage disabled — the prompt simply shows again.
        }
    }

    /* ------------------------------------------------------------------
       Header bell + notification centre
    ------------------------------------------------------------------ */

    function closePanels(except) {
        document.querySelectorAll('[data-notif-panel]').forEach(function (panel) {
            if (panel === except) return;
            panel.hidden = true;

            var root = panel.closest('[data-notif-root]');
            var toggle = root && root.querySelector('[data-notif-toggle]');
            if (toggle) toggle.setAttribute('aria-expanded', 'false');
        });
    }

    function setUnreadBadge(count) {
        document.querySelectorAll('[data-notif-badge]').forEach(function (badge) {
            badge.textContent = count > 99 ? '99+' : String(count);
            badge.hidden = count < 1;
        });
    }

    function markItemRead(item) {
        var url = item.getAttribute('data-read-url');
        var href = item.getAttribute('href');

        if (!url || !window.fetch) {
            return false;
        }

        fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(function (response) {
            return response.ok ? response.json() : null;
        }).then(function (data) {
            if (data && typeof data.unread === 'number') {
                setUnreadBadge(data.unread);
            }
        }).catch(function () {}).finally(function () {
            if (href) window.location.href = href;
        });

        return true;
    }

    function togglePanel(toggle) {
        var root = toggle.closest('[data-notif-root]');
        var panel = root && root.querySelector('[data-notif-panel]');
        if (!panel) return;

        var willOpen = panel.hidden;
        closePanels(panel);
        panel.hidden = !willOpen;
        toggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
    }

    /* ------------------------------------------------------------------
       Web Push subscription
    ------------------------------------------------------------------ */

    function urlBase64ToUint8Array(base64String) {
        var padding = '='.repeat((4 - (base64String.length % 4)) % 4);
        var base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
        var raw = window.atob(base64);
        var output = new Uint8Array(raw.length);

        for (var i = 0; i < raw.length; i++) {
            output[i] = raw.charCodeAt(i);
        }

        return output;
    }

    function registerServiceWorker() {
        if (!window.__rsSwPromise) {
            window.__rsSwPromise = navigator.serviceWorker.register('/sw.js', { scope: '/' })
                .catch(function () {
                    return null;
                });
        }

        return window.__rsSwPromise;
    }

    function postSubscription(config, subscription) {
        var json = subscription.toJSON();

        return fetch(config.dataset.subscribeUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': config.dataset.csrf || csrfToken(),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                endpoint: json.endpoint,
                keys: json.keys,
                content_encoding: (window.PushManager && window.PushManager.supportedContentEncodings
                    && window.PushManager.supportedContentEncodings[0]) || 'aes128gcm'
            })
        });
    }

    function ensureSubscribed(config) {
        return registerServiceWorker().then(function (registration) {
            if (!registration) return;

            return registration.pushManager.getSubscription().then(function (existing) {
                if (existing) {
                    return postSubscription(config, existing);
                }

                return registration.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: urlBase64ToUint8Array(config.dataset.publicKey)
                }).then(function (subscription) {
                    return postSubscription(config, subscription);
                });
            });
        }).catch(function () {
            // Browser refused the subscription — the in-site bell still works.
        });
    }

    function pushModal(name) {
        return document.querySelector('[data-push-modal="' + name + '"]');
    }

    function showPushModal(name) {
        var modal = pushModal(name);
        if (!modal) return;

        modal.hidden = false;
        document.body.style.overflow = 'hidden';

        var focusable = modal.querySelector('[data-push-allow], [data-push-retry]');
        if (focusable) focusable.focus({ preventScroll: true });
    }

    function closePushModal(name) {
        var modal = pushModal(name);
        if (!modal) return;

        modal.hidden = true;
        document.body.style.overflow = '';
    }

    function todayKey() {
        var now = new Date();
        return now.getFullYear() + '-' + (now.getMonth() + 1) + '-' + now.getDate();
    }

    function showBlockedPrompt(config) {
        // At most once per day, so the reminder does not become annoying.
        if (storageGet('rs_push_blocked_shown') === todayKey()) return;

        storageSet('rs_push_blocked_shown', todayKey());

        window.setTimeout(function () {
            showPushModal('blocked');
        }, 1500);
    }

    function evaluatePushPermission(config) {
        if (!('Notification' in window) || !('serviceWorker' in navigator) || !('PushManager' in window)) {
            return;
        }

        var permission = Notification.permission;

        if (permission === 'granted') {
            ensureSubscribed(config);
            return;
        }

        if (permission === 'denied') {
            showBlockedPrompt(config);
            return;
        }

        if (storageGet('rs_push_prompt_dismissed')) {
            // The student closed the first prompt before — use the other modal.
            showBlockedPrompt(config);
            return;
        }

        window.setTimeout(function () {
            showPushModal('prompt');
        }, 1200);
    }

    function requestPermissionAndSubscribe(config, grantedCallback, deniedCallback) {
        Notification.requestPermission().then(function (permission) {
            if (permission === 'granted') {
                ensureSubscribed(config);
                if (grantedCallback) grantedCallback();
                return;
            }

            if (permission === 'denied') {
                if (deniedCallback) deniedCallback();
            }
        });
    }

    function initPush() {
        var config = getConfig();
        if (!config || config.getAttribute('data-rs-push-init') === '1') {
            return;
        }

        config.setAttribute('data-rs-push-init', '1');
        evaluatePushPermission(config);
    }

    /* ------------------------------------------------------------------
       Event wiring (once per document; survives Turbo body swaps)
    ------------------------------------------------------------------ */

    function handleClick(event) {
        var toggle = event.target.closest('[data-notif-toggle]');
        if (toggle) {
            event.preventDefault();
            togglePanel(toggle);
            return;
        }

        var item = event.target.closest('[data-notif-item]');
        if (item && item.hasAttribute('data-read-url')) {
            event.preventDefault();
            markItemRead(item);
            return;
        }

        if (event.target.closest('[data-push-close]')) {
            var modal = event.target.closest('[data-push-modal]');
            if (modal) {
                event.preventDefault();
                if (modal.getAttribute('data-push-modal') === 'prompt') {
                    storageSet('rs_push_prompt_dismissed', '1');
                }
                closePushModal(modal.getAttribute('data-push-modal'));
            }
            return;
        }

        var config = getConfig();

        if (event.target.closest('[data-push-allow]')) {
            event.preventDefault();
            if (!config) return;

            requestPermissionAndSubscribe(config, function () {
                closePushModal('prompt');
            }, function () {
                storageSet('rs_push_prompt_dismissed', '1');
                closePushModal('prompt');
            });
            return;
        }

        if (event.target.closest('[data-push-retry]')) {
            event.preventDefault();
            if (!config) return;

            if (Notification.permission === 'denied') {
                var hint = document.querySelector('[data-push-hint]');
                if (hint) hint.hidden = false;
                return;
            }

            requestPermissionAndSubscribe(config, function () {
                closePushModal('blocked');
            });
            return;
        }

        // Click outside the bell closes the dropdown.
        if (!event.target.closest('[data-notif-root]')) {
            closePanels();
        }
    }

    function handleSubmit(event) {
        var form = event.target.closest('[data-notif-read-all]');
        if (!form || !window.fetch) return;

        event.preventDefault();

        fetch(form.action, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new FormData(form)
        }).then(function (response) {
            if (response.ok) {
                window.location.reload();
            }
        }).catch(function () {
            form.submit();
        });
    }

    function handleKeydown(event) {
        if (event.key !== 'Escape') return;

        closePanels();

        var prompt = pushModal('prompt');
        if (prompt && !prompt.hidden) {
            // Closing the first prompt switches to the "not granted" variant later.
            storageSet('rs_push_prompt_dismissed', '1');
        }

        closePushModal('prompt');
        closePushModal('blocked');
    }

    function init() {
        initPush();
    }

    if (!window[LISTENER_FLAG]) {
        window[LISTENER_FLAG] = true;
        document.addEventListener('click', handleClick);
        document.addEventListener('submit', handleSubmit);
        document.addEventListener('keydown', handleKeydown);
    }

    // Native first load and the synthetic DOMContentLoaded that the layout
    // dispatches after every Turbo navigation both reach this listener.
    document.addEventListener('DOMContentLoaded', init);
    if (document.readyState !== 'loading') {
        init();
    }
})();
