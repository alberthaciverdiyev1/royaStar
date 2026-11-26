@extends('layouts.app')

@section('title', text('notifications.page_title'))

@section('content')
    <div class="max-w-[900px] mx-auto px-4 md:px-8 w-full">
        <div class="rs-npage">
            <div class="rs-npage__head">
                <div>
                    <span class="rs-npage__eyebrow">
                        <span class="material-symbols-outlined">notifications</span>
                        {{ text('notifications.bell_title') }}
                    </span>
                    <h1 class="rs-npage__title">{{ text('notifications.page_title') }}</h1>
                </div>

                @if ($unreadCount > 0)
                    <form method="POST" action="{{ route('notifications.read-all') }}" data-notif-read-all>
                        @csrf
                        <button type="submit" class="rs-npage__mark-all">
                            <span class="material-symbols-outlined">done_all</span>
                            {{ text('notifications.mark_all') }}
                        </button>
                    </form>
                @endif
            </div>

            <div class="rs-npage__tabs">
                <a href="{{ route('notifications') }}"
                   class="rs-npage__tab {{ $filter === 'all' ? 'is-active' : '' }}">
                    {{ text('notifications.all') }}
                </a>
                <a href="{{ route('notifications', ['filter' => 'unread']) }}"
                   class="rs-npage__tab {{ $filter === 'unread' ? 'is-active' : '' }}">
                    {{ text('notifications.unread') }}
                    @if ($unreadCount > 0)
                        <span class="rs-npage__count">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                    @endif
                </a>
            </div>

            @if ($notifications->isEmpty())
                <div class="rs-npage__empty">
                    <span class="material-symbols-outlined">notifications_paused</span>
                    <p>{{ $filter === 'unread' ? text('notifications.empty_unread') : text('notifications.empty') }}</p>
                </div>
            @else
                <div class="rs-npage__list">
                    @foreach ($notifications as $notification)
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
                            class="rs-npage__item {{ $isRead ? 'is-read' : '' }}"
                            data-notif-item
                            data-id="{{ $notification->id }}"
                            @unless ($isRead) data-read-url="{{ route('notifications.read', $notification->id) }}" @endunless
                        >
                            <span class="rs-npage__icon rs-notif__icon--{{ $notification->type }}">
                                <span class="material-symbols-outlined">{{ $itemIcon }}</span>
                            </span>

                            <span class="rs-npage__body">
                                <span class="rs-npage__item-title">{{ $notification->title }}</span>
                                @if ($notification->body)
                                    <span class="rs-npage__item-text">{{ $notification->body }}</span>
                                @endif
                                <span class="rs-npage__time">{{ $notification->created_at?->diffForHumans() }}</span>
                            </span>

                            @unless ($isRead)
                                <span class="rs-npage__dot" aria-hidden="true"></span>
                            @endunless
                        </a>
                    @endforeach
                </div>

                @if ($notifications->hasPages())
                    <div class="rs-npage__pagination">
                        @if ($notifications->onFirstPage())
                            <span class="rs-npage__page is-disabled">&larr;</span>
                        @else
                            <a href="{{ $notifications->previousPageUrl() }}" class="rs-npage__page">&larr;</a>
                        @endif

                        <span class="rs-npage__page-info">{{ $notifications->currentPage() }} / {{ $notifications->lastPage() }}</span>

                        @if ($notifications->hasMorePages())
                            <a href="{{ $notifications->nextPageUrl() }}" class="rs-npage__page">&rarr;</a>
                        @else
                            <span class="rs-npage__page is-disabled">&rarr;</span>
                        @endif
                    </div>
                @endif
            @endif
        </div>
    </div>
@endsection
