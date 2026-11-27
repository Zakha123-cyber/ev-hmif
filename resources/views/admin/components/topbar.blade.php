@php
    $admin = auth()->user();
    $adminName = $admin->details?->participant_name ?: $admin->username;
    [$statusLabel, $statusTone] = [
        'not_set' => ['Jadwal belum diatur', 'muted'],
        'upcoming' => ['Belum dibuka', 'warn'],
        'open' => ['Sedang dibuka', 'ok'],
        'closed' => ['Sudah ditutup', 'danger'],
    ][\App\Models\ElectionSetting::current()->status()];
@endphp
<header class="a-topbar">
    <button type="button" class="a-btn a-btn--ghost a-topbar__menu" data-drawer-open aria-controls="a-sidebar" aria-expanded="false">
        @include('admin.components.icon', ['name' => 'menu'])
        Menu
    </button>

    <a class="a-status a-status--{{ $statusTone }}" href="{{ url('/back-admin-logged/election-schedule') }}">
        <span class="a-status__dot" aria-hidden="true"></span>
        <span><span class="visually-hidden">Status pemilihan: </span>{{ $statusLabel }}</span>
    </a>

    <div class="a-topbar__user">
        <a class="a-topbar__link" href="{{ url('/') }}" target="_blank" rel="noopener">
            @include('admin.components.icon', ['name' => 'external'])
            Lihat situs
        </a>
        <span class="a-topbar__name">{{ $adminName }}</span>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="a-btn a-btn--ghost a-btn--sm">
                @include('admin.components.icon', ['name' => 'logout'])
                Keluar
            </button>
        </form>
    </div>
</header>
