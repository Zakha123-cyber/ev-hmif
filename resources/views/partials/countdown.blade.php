@php
    // Hitung mundur heksagon. Angka awal dari server; home.js memperbaruinya saat
    // halaman dibuka lagi dan memuat ulang halaman saat waktunya tiba.
    $left = now()->diff($target);
@endphp
<div class="countdown" data-countdown="{{ $target->toIso8601String() }}" data-now="{{ now()->toIso8601String() }}">
    <p class="countdown__caption">{{ $caption }}</p>
    <p class="countdown__tiles">
        <span class="countdown__unit"><span class="countdown__value" data-unit="days">{{ $left->days }}</span> <span class="countdown__name">hari</span></span>
        <span class="countdown__unit"><span class="countdown__value" data-unit="hours">{{ sprintf('%02d', $left->h) }}</span> <span class="countdown__name">jam</span></span>
        <span class="countdown__unit"><span class="countdown__value" data-unit="minutes">{{ sprintf('%02d', $left->i) }}</span> <span class="countdown__name">menit</span></span>
    </p>
</div>
