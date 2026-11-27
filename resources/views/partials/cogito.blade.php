@php
    // COGITO + balon. home.js menggilir kalimat secara acak; tanpa JS hanya kalimat
    // pertama (beserta aksinya) yang tampil dan tombol Tutup tetap tersembunyi.
    // $pose: salam|salute, $variant: corner|inline,
    // $lines: list<['text' => string, 'action' => ?['label' => …, 'href' => …]]>
    $first = $lines[0];
@endphp
<aside class="cogito cogito--{{ $variant }}" data-cogito aria-label="Pesan dari COGITO">
    <div class="cogito__bubble" data-cogito-bubble>
        <p class="cogito__line" data-cogito-line>{{ $first['text'] }}</p>
        <script type="application/json" data-cogito-lines>@json($lines, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG)</script>
        <div class="cogito__actions">
            <a class="btn btn--primary cogito__action" data-cogito-action href="{{ $first['action']['href'] ?? '#' }}" @unless ($first['action']) hidden @endunless>{{ $first['action']['label'] ?? '' }}</a>
            <button type="button" class="cogito__button cogito__button--quiet" data-cogito-close hidden>Tutup</button>
        </div>
    </div>
    <button type="button" class="cogito__figure" data-cogito-toggle aria-label="COGITO, ketuk untuk kalimat lain">
        <picture>
            <source media="(prefers-reduced-motion: reduce)" srcset="{{ asset('frontend/images/cogito/' . $pose . '-still.webp') }}">
            <img src="{{ asset('frontend/images/cogito/' . $pose . '.webp') }}" width="278" height="385" alt="">
        </picture>
    </button>
</aside>
