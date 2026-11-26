@php
    $unreadNotificationCount = $unreadNotificationCount ?? 0;
    $latestNotifications = $latestNotifications ?? collect();
@endphp

<div class="rs-notif" data-notif-root>
    <button
        type="button"
        class="rs-notif__btn"
        data-notif-toggle
        aria-label="{{ text('notifications.bell_title') }}"
        aria-expanded="false"
        aria-haspopup="true"
    >
        <span class="material-symbols-outlined">notifications</span>
        <span class="rs-notif__badge" data-notif-badge @if ($unreadNotificationCount < 1) hidden @endif>
            {{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}
        </span>
    </button>

    <div class="rs-notif__panel" data-notif-panel hidden>
        <div class="rs-notif__head">
            <span class="rs-notif__heading">{{ text('notifications.bell_title') }}</span>
            <a href="{{ route('notifications') }}" class="rs-notif__see-all">{{ text('notifications.see_all') }}</a>
        </div>

        <div class="rs-notif__list">
            @forelse ($latestNotifications as $notification)
                @php
                    $isRead = !empty($notification->read_at);
                    $itemIcon = match ($notification->type) {
                        'lesson_video' => 'play_circle',
                        'quiz' => 'quiz',
                        'exam' => 'school',
                        default => 'notifications',
                    };
                @endphp
                <a
                    href="{{ $notification->url ?: route('notifications') }}"
                    class="rs-notif__item {{ $isRead ? 'is-read' : '' }}"
                    data-notif-item
                    data-id="{{ $notification->id }}"
                    @unless ($isRead) data-read-url="{{ route('notifications.read', $notification->id) }}" @endunless
                >
                    <span class="rs-notif__icon rs-notif__icon--{{ $notification->type }}">
                        <span class="material-symbols-outlined">{{ $itemIcon }}</span>
                    </span>
                    <span class="rs-notif__item-body">
                        <span class="rs-notif__item-title">{{ $notification->title }}</span>
                        @if ($notification->body)
                            <span class="rs-notif__item-text">{{ \Illuminate\Support\Str::limit($notification->body, 64) }}</span>
                        @endif
                        <span class="rs-notif__time">{{ $notification->created_at?->diffForHumans() }}</span>
                    </span>
                    @unless ($isRead)
                        <span class="rs-notif__dot" aria-hidden="true"></span>
                    @endunless
                </a>
            @empty
                <p class="rs-notif__empty">{{ text('notifications.empty') }}</p>
            @endforelse
        </div>
    </div>
</div>

<script src="{{ asset('js/notifications.js') }}?v={{ filemtime(public_path('js/notifications.js')) }}" defer></script>
