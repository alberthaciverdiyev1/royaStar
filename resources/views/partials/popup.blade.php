@if (!empty($popups) && count($popups) > 0)
    @push('styles')
        <link href="{{ asset('css/popup.css') }}?v={{ filemtime(public_path('css/popup.css')) }}" rel="stylesheet">
    @endpush

    {{-- Data is escaped by Blade; the script parses it back from the attribute. --}}
    <div
        id="site-popup"
        class="site-popup"
        role="dialog"
        aria-modal="true"
        aria-labelledby="site-popup-title"
        data-popups="{{ json_encode($popups, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT) }}"
        hidden
    >
        <div class="site-popup__backdrop" data-popup-close></div>

        <div class="site-popup__card" role="document">
            <button type="button" class="site-popup__close" data-popup-close aria-label="Bağla">
                <span class="material-symbols-outlined">close</span>
            </button>

            <img id="site-popup-image" class="site-popup__image" alt="" hidden>

            <div class="site-popup__body">
                <h2 id="site-popup-title" class="site-popup__title" hidden></h2>
                <div id="site-popup-content" class="site-popup__content" hidden></div>
            </div>

            <div class="site-popup__footer">
                <label class="site-popup__remember">
                    <input type="checkbox" id="site-popup-remember">
                    <span>Bir daha göstərmə</span>
                </label>
                <button type="button" class="site-popup__ok" data-popup-close>Bağla</button>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('js/popup.js') }}?v={{ filemtime(public_path('js/popup.js')) }}"></script>
    @endpush
@endif
