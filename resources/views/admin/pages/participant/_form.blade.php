{{-- Form peserta bersama untuk tambah & ubah. $participant (null saat tambah), $action, $method --}}
@php
    $value = fn ($field, $default = null) => old($field, $participant?->{$field} ?? $default);
    $status = old('status', $participant?->status ?? 'not verified');
    $hasImage = $participant?->image && is_file(public_path('assets/upload/participant/' . $participant->image));
    $preview = $hasImage ? asset('assets/upload/participant/' . $participant->image) : null;
@endphp
<form class="a-form" method="POST" action="{{ $action }}" enctype="multipart/form-data">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="a-field">
        <label class="a-label" for="participant_name">Nama lengkap</label>
        <input class="a-input" id="participant_name" name="participant_name" type="text" value="{{ $value('participant_name') }}"
            autocomplete="off" @error('participant_name') aria-invalid="true" @enderror>
        @error('participant_name')
            <p class="a-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="a-form__row a-form__row--2">
        <div class="a-field">
            <label class="a-label" for="nim">NIM</label>
            <input class="a-input" id="nim" name="nim" type="text" inputmode="numeric" value="{{ $value('nim') }}"
                aria-describedby="nim-help" @error('nim') aria-invalid="true" @enderror>
            <p class="a-help" id="nim-help">Email akun dikirim ke alamat kampus yang dibentuk dari NIM ini.</p>
            @error('nim')
                <p class="a-error">{{ $message }}</p>
            @enderror
        </div>
        <div class="a-field">
            <label class="a-label" for="whatsapp_number">Nomor WhatsApp</label>
            <input class="a-input" id="whatsapp_number" name="whatsapp_number" type="tel" inputmode="numeric"
                value="{{ $value('whatsapp_number', '62') }}" aria-describedby="wa-help" @error('whatsapp_number') aria-invalid="true" @enderror>
            <p class="a-help" id="wa-help">Diawali 62, misalnya 6281234567890.</p>
            @error('whatsapp_number')
                <p class="a-error">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="a-form__row a-form__row--2">
        <div class="a-field">
            <label class="a-label" for="email_backup">Email cadangan <small>(opsional)</small></label>
            <input class="a-input" id="email_backup" name="email_backup" type="email" value="{{ $value('email_backup') }}"
                aria-describedby="email-help" @error('email_backup') aria-invalid="true" @enderror>
            <p class="a-help" id="email-help">Email pribadi, dipakai bila email kampus bermasalah.</p>
            @error('email_backup')
                <p class="a-error">{{ $message }}</p>
            @enderror
        </div>
        <div class="a-field">
            <label class="a-label" for="status">Status</label>
            <select class="a-input" id="status" name="status" @error('status') aria-invalid="true" @enderror>
                <option value="not verified" @selected($status === 'not verified')>Belum terverifikasi</option>
                <option value="verified" @selected($status === 'verified')>Terverifikasi</option>
            </select>
            @error('status')
                <p class="a-error">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="a-field">
        <span class="a-label" id="image-label">Foto <small>(opsional, JPG/PNG/WEBP, maks. 10 MB)</small></span>
        <div class="a-upload" data-upload>
            <img src="{{ $preview }}" alt="Pratinjau foto" data-preview width="120" height="150" @unless ($preview) hidden @endunless>
            <div class="a-upload__empty" data-preview-empty @if ($preview) hidden @endif>Belum ada foto</div>
            <label class="a-upload__pick">
                <input type="file" name="image" accept="image/jpeg,image/png,image/webp" data-file-preview aria-labelledby="image-label">
                <strong>@include('admin.components.icon', ['name' => 'upload']) Pilih foto</strong>
                <span class="a-help" data-file-name>{{ $participant ? 'Biarkan kosong untuk memakai foto sekarang.' : 'Belum ada file dipilih.' }}</span>
            </label>
        </div>
        @error('image')
            <p class="a-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="a-form__foot">
        <button type="submit" class="a-btn a-btn--accent">Simpan peserta</button>
        <a class="a-btn a-btn--ghost" href="{{ url('/back-admin-logged/participant') }}">Batal</a>
    </div>
</form>
