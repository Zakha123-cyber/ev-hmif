{{-- Form paslon bersama untuk tambah & ubah. $candidate (null saat tambah), $action, $method --}}
@php
    $value = fn ($field) => old($field, $candidate?->{$field});
    $preview = $candidate?->hasPoster() ? $candidate->posterUrl() : null;
    $fields = [
        ['candidate_pair', 'Nomor urut paslon', 'number', null],
        ['leader_name', 'Nama ketua', 'text', null],
        ['vice_leader_name', 'Nama wakil ketua', 'text', null],
    ];
@endphp
<form class="a-form" method="POST" action="{{ $action }}" enctype="multipart/form-data">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    @foreach ($fields as [$name, $label, $type])
        <div class="a-field">
            <label class="a-label" for="{{ $name }}">{{ $label }}</label>
            <input class="a-input" id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ $value($name) }}"
                @if ($type === 'number') min="1" inputmode="numeric" style="max-width: 140px" @endif
                @error($name) aria-invalid="true" @enderror>
            @error($name)
                <p class="a-error">{{ $message }}</p>
            @enderror
        </div>
    @endforeach

    <div class="a-field">
        <label class="a-label" for="vision">Visi</label>
        <textarea class="a-input" id="vision" name="vision" rows="3" @error('vision') aria-invalid="true" @enderror>{{ $value('vision') }}</textarea>
        @error('vision')
            <p class="a-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="a-field">
        <label class="a-label" for="mission">Misi</label>
        <textarea class="a-input" id="mission" name="mission" rows="5" aria-describedby="mission-help" @error('mission') aria-invalid="true" @enderror>{{ $value('mission') }}</textarea>
        <p class="a-help" id="mission-help">Pisahkan tiap misi dengan titik. Di halaman awal misi tampil sebagai daftar bernomor.</p>
        @error('mission')
            <p class="a-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="a-field">
        <span class="a-label" id="poster-label">Poster <small>(opsional, JPG/PNG/WEBP, maks. 10 MB)</small></span>
        <div class="a-upload" data-upload>
            <img src="{{ $preview }}" alt="Pratinjau poster" data-preview width="120" height="150" @unless ($preview) hidden @endunless>
            <div class="a-upload__empty" data-preview-empty @if ($preview) hidden @endif>Belum ada gambar</div>
            <label class="a-upload__pick">
                <input type="file" name="poster" accept="image/jpeg,image/png,image/webp" data-file-preview aria-labelledby="poster-label">
                <strong>@include('admin.components.icon', ['name' => 'upload']) Pilih gambar</strong>
                <span class="a-help" data-file-name>{{ $candidate ? 'Biarkan kosong untuk memakai poster sekarang.' : 'Belum ada file dipilih.' }}</span>
            </label>
        </div>
        @error('poster')
            <p class="a-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="a-form__foot">
        <button type="submit" class="a-btn a-btn--accent">Simpan paslon</button>
        <a class="a-btn a-btn--ghost" href="{{ url('/back-admin-logged/candidate') }}">Batal</a>
    </div>
</form>
