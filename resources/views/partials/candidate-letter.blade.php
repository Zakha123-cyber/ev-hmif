@php
    // Surat paslon: dipakai amplop di halaman awal dan tombol "Visi & misi" di /vote.
    $letterId = 'surat-' . $candidate->id;
    [$posterWidth, $posterHeight] = $candidate->posterSize();
    $missions = $candidate->missionItems();
@endphp
<dialog id="{{ $letterId }}" class="letter" aria-labelledby="{{ $letterId }}-judul">
    <div class="letter__paper">
        <header class="letter__kop">
            <img src="{{ asset('frontend/images/home/logo-hmif-320.webp') }}" width="316" height="320" alt="" loading="lazy">
            <p><strong>Himpunan Mahasiswa Informatika</strong><span>Universitas Jember</span></p>
        </header>

        <h3 id="{{ $letterId }}-judul" class="letter__title" tabindex="-1" autofocus>Pasangan Calon Nomor Urut {{ $candidate->candidate_pair }}</h3>

        @if ($candidate->hasPoster())
            <img class="letter__poster" src="{{ $candidate->posterUrl() }}"
                width="{{ $posterWidth }}" height="{{ $posterHeight }}" loading="lazy"
                alt="Poster pasangan calon nomor {{ $candidate->candidate_pair }}, {{ $candidate->leader_name }} dan {{ $candidate->vice_leader_name }}">
        @else
            <div class="letter__poster letter__placeholder">Foto pasangan calon belum tersedia</div>
        @endif

        <dl class="letter__names">
            <div>
                <dt>Ketua</dt>
                <dd>{{ $candidate->leader_name }}</dd>
            </div>
            <div>
                <dt>Wakil ketua</dt>
                <dd>{{ $candidate->vice_leader_name }}</dd>
            </div>
        </dl>

        <h4 class="letter__heading">Visi</h4>
        <p class="letter__vision">{{ $candidate->vision }}</p>

        @if ($missions)
            <h4 class="letter__heading">Misi</h4>
            <ol class="letter__mission">
                @foreach ($missions as $mission)
                    <li>{{ $mission }}</li>
                @endforeach
            </ol>
        @endif

        <form method="dialog">
            <button class="letter__close">Tutup</button>
        </form>
    </div>
</dialog>
