@if ($paginator->hasPages())
    <div class="pagination-container">
        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <span class="pagi-btn pagi-btn--disabled">
                <span class="material-symbols-outlined !text-lg">chevron_left</span>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="pagi-btn pagi-btn--nav">
                <span class="material-symbols-outlined !text-lg">chevron_left</span>
            </a>
        @endif

        {{-- Pages --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="pagi-dots">…</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="pagi-btn pagi-btn--active">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="pagi-btn pagi-btn--page">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="pagi-btn pagi-btn--nav">
                <span class="material-symbols-outlined !text-lg">chevron_right</span>
            </a>
        @else
            <span class="pagi-btn pagi-btn--disabled">
                <span class="material-symbols-outlined !text-lg">chevron_right</span>
            </span>
        @endif
    </div>
@endif
