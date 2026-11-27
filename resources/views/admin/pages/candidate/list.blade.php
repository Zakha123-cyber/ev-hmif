@extends('admin.layouts.app')

@section('title', 'Paslon')

@section('actions')
    <a class="a-btn a-btn--accent" href="{{ url('/back-admin-logged/candidate/add') }}">
        @include('admin.components.icon', ['name' => 'plus'])
        Tambah paslon
    </a>
@endsection

@section('content')
    @if ($dataCandidate->isEmpty())
        <section class="a-card">
            <p class="a-empty">
                Belum ada paslon. Tambahkan paslon agar muncul di halaman awal dan surat suara.
            </p>
        </section>
    @else
        <ul class="a-candidates">
            @foreach ($dataCandidate->sortBy('candidate_pair') as $item)
                @php [$w, $h] = $item->posterSize(); @endphp
                <li class="a-card a-candidate">
                    <div class="a-candidate__poster">
                        @if ($item->hasPoster())
                            <img src="{{ $item->posterUrl() }}" width="{{ $w }}" height="{{ $h }}" loading="lazy"
                                alt="Poster Paslon {{ $item->candidate_pair }}">
                        @else
                            <div class="a-candidate__noposter">Belum ada poster</div>
                        @endif
                    </div>
                    <div class="a-candidate__body">
                        <div class="a-candidate__head">
                            <span class="hex-number a-candidate__seal" aria-hidden="true">{{ $item->candidate_pair }}</span>
                            <h2 class="a-card__title">Paslon {{ $item->candidate_pair }}</h2>
                        </div>
                        <dl class="a-dl">
                            <div><dt>Ketua</dt><dd>{{ $item->leader_name }}</dd></div>
                            <div><dt>Wakil ketua</dt><dd>{{ $item->vice_leader_name }}</dd></div>
                            <div><dt>Visi</dt><dd>{{ $item->vision }}</dd></div>
                            <div>
                                <dt>Misi</dt>
                                <dd>
                                    @if ($missions = $item->missionItems())
                                        <ol class="a-candidate__missions">
                                            @foreach ($missions as $mission)
                                                <li>{{ $mission }}</li>
                                            @endforeach
                                        </ol>
                                    @else
                                        <span class="a-help">Belum diisi.</span>
                                    @endif
                                </dd>
                            </div>
                        </dl>
                        <div class="a-actions">
                            <form action="{{ url('/back-admin-logged/candidate/edit', $item->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="a-btn a-btn--primary a-btn--sm">
                                    @include('admin.components.icon', ['name' => 'edit'])
                                    Ubah
                                </button>
                            </form>
                            <form action="{{ url('/back-admin-logged/candidate/delete', $item->id) }}" method="POST"
                                data-confirm="Hapus Paslon {{ $item->candidate_pair }} ({{ $item->leader_name }} &amp; {{ $item->vice_leader_name }})? Tindakan ini tidak bisa dibatalkan."
                                data-confirm-label="Ya, hapus paslon">
                                @csrf
                                @method('delete')
                                <button type="submit" class="a-btn a-btn--danger a-btn--sm">
                                    @include('admin.components.icon', ['name' => 'trash'])
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
@endsection
