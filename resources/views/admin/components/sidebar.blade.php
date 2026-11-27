@php
    $current = request()->segment(2);
    $groups = [
        '' => [['dashboard', 'Ringkasan', 'home']],
        'Persiapan' => [
            ['election-schedule', 'Jadwal & periode', 'calendar'],
            ['candidate', 'Paslon', 'ballot'],
            ['participant', 'Peserta', 'users'],
            ['participant-verification', 'Verifikasi akun', 'check'],
        ],
        'Pemilihan' => [['live-count', 'Hasil suara', 'chart']],
    ];
@endphp
<aside class="a-sidebar" id="a-sidebar" data-drawer aria-label="Menu admin">
    <div class="a-sidebar__brand">
        <img src="{{ asset('frontend/images/home/logo-hmif-320.webp') }}" width="316" height="320" alt="">
        <p>
            <strong>Panel admin</strong>
            <span>Pemilihan HMIF {{ \App\Models\ElectionSetting::current()->periodLabel() }}</span>
        </p>
        <button type="button" class="a-sidebar__close" data-drawer-close>
            @include('admin.components.icon', ['name' => 'close'])
            <span class="visually-hidden">Tutup menu</span>
        </button>
    </div>

    <nav class="a-nav" aria-label="Halaman admin">
        @foreach ($groups as $heading => $items)
            @if ($heading !== '')
                <p class="a-nav__heading">{{ $heading }}</p>
            @endif
            <ul>
                @foreach ($items as [$slug, $label, $icon])
                    <li>
                        <a href="{{ url('/back-admin-logged/' . $slug) }}" class="a-nav__link" @if ($current === $slug) aria-current="page" @endif>
                            @include('admin.components.icon', ['name' => $icon])
                            {{ $label }}
                        </a>
                    </li>
                @endforeach
            </ul>
        @endforeach
    </nav>
</aside>
