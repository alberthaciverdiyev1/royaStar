@php($vapidPublicKey = config('webpush.public_key'))
@if ($vapidPublicKey && auth()->user()?->type === 'student')
    <div
        id="rs-push-config"
        hidden
        data-public-key="{{ $vapidPublicKey }}"
        data-subscribe-url="{{ route('push.subscribe') }}"
        data-unsubscribe-url="{{ route('push.unsubscribe') }}"
        data-csrf="{{ csrf_token() }}"
    ></div>

    {{-- First prompt: ask for permission in a friendly modal. --}}
    <div class="rs-push" data-push-modal="prompt" hidden>
        <div class="rs-push__backdrop" data-push-close></div>
        <div class="rs-push__card rs-push__card--prompt" role="dialog" aria-modal="true" aria-labelledby="rs-push-title">
            <button type="button" class="rs-push__x" data-push-close aria-label="Bağla">
                <span class="material-symbols-outlined">close</span>
            </button>

            <div class="rs-push__art">
                <span class="rs-push__ring" aria-hidden="true"></span>
                <span class="material-symbols-outlined rs-push__icon">notifications_active</span>
            </div>

            <h2 id="rs-push-title" class="rs-push__title">{{ text('notifications.push.title') }}</h2>
            <p class="rs-push__desc">{{ text('notifications.push.desc') }}</p>

            <button type="button" class="rs-push__primary" data-push-allow>
                <span class="material-symbols-outlined">notifications</span>
                {{ text('notifications.push.allow') }}
            </button>
            <button type="button" class="rs-push__ghost" data-push-close>{{ text('notifications.push.later') }}</button>
        </div>
    </div>

    {{-- Second prompt: shown to students who dismissed the first one. --}}
    <div class="rs-push" data-push-modal="blocked" hidden>
        <div class="rs-push__backdrop" data-push-close></div>
        <div class="rs-push__card rs-push__card--blocked" role="dialog" aria-modal="true" aria-labelledby="rs-push-blocked-title">
            <button type="button" class="rs-push__x" data-push-close aria-label="Bağla">
                <span class="material-symbols-outlined">close</span>
            </button>

            <div class="rs-push__art rs-push__art--muted">
                <span class="material-symbols-outlined rs-push__icon">notifications_off</span>
            </div>

            <h2 id="rs-push-blocked-title" class="rs-push__title">{{ text('notifications.push.blocked_title') }}</h2>
            <p class="rs-push__desc">{{ text('notifications.push.blocked_desc') }}</p>
            <p class="rs-push__hint" data-push-hint hidden>{{ text('notifications.push.denied') }}</p>

            <button type="button" class="rs-push__primary rs-push__primary--muted" data-push-retry>
                {{ text('notifications.push.retry') }}
            </button>
            <button type="button" class="rs-push__ghost" data-push-close>{{ text('notifications.push.close') }}</button>
        </div>
    </div>
@endif
