@php
    // COGITO + balon sapaan/arahan. Tanpa JS: kalimat pertama saja (dan aksi bila
    // hanya satu kalimat); tombol Lanjut/Tutup tersembunyi sampai home.js aktif.
    // $pose: salam|salute, $variant: corner|inline, $key: kunci sessionStorage,
    // $lines: string[], $action: ?['label' => …, 'href' => …]
    $action = $action ?? null;
    $single = count($lines) === 1;
@endphp
<aside class="cogito cogito--{{ $variant }}" data-cogito data-cogito-key="{{ $key }}" aria-label="Pesan dari COGITO">
    <div class="cogito__bubble" data-cogito-bubble>
        <p class="cogito__line" data-cogito-line>{{ $lines[0] }}</p>
        <script type="application/json" data-cogito-lines>@json($lines, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG)</script>
        <div class="cogito__actions">
            @if ($action)
                <a class="btn btn--primary cogito__action" data-cogito-action href="{{ $action['href'] }}" @unless ($single) hidden @endunless>{{ $action['label'] }}</a>
            @endif
            <button type="button" class="cogito__button" data-cogito-next hidden>Lanjut</button>
            <button type="button" class="cogito__button cogito__button--quiet" data-cogito-close hidden>Tutup</button>
        </div>
    </div>
    <button type="button" class="cogito__figure" data-cogito-toggle aria-label="Bicara dengan COGITO">
        <picture>
            <source media="(prefers-reduced-motion: reduce)" srcset="{{ asset('frontend/images/cogito/' . $pose . '-still.webp') }}">
            <img src="{{ asset('frontend/images/cogito/' . $pose . '.webp') }}" width="278" height="385" alt="" loading="lazy">
        </picture>
    </button>
</aside>
