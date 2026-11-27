{{-- Isian kontak panitia, dipakai form tambah & ubah. $contact null saat tambah. --}}
<div class="a-form__row a-form__row--2">
    <div class="a-field">
        <label class="a-label" for="name">Nama</label>
        <input class="a-input" id="name" name="name" type="text" maxlength="60" value="{{ old('name', $contact?->name) }}"
            @error('name') aria-invalid="true" @enderror>
        @error('name')
            <p class="a-error">{{ $message }}</p>
        @enderror
    </div>
    <div class="a-field">
        <label class="a-label" for="whatsapp">Nomor WhatsApp</label>
        <input class="a-input" id="whatsapp" name="whatsapp" type="tel" inputmode="tel" placeholder="081234567890"
            value="{{ old('whatsapp', $contact ? $contact->displayPhone() : '') }}" aria-describedby="whatsapp-help"
            @error('whatsapp') aria-invalid="true" @enderror>
        <p class="a-help" id="whatsapp-help">Boleh 08…, 62…, atau +62 dengan spasi/tanda hubung.</p>
        @error('whatsapp')
            <p class="a-error">{{ $message }}</p>
        @enderror
    </div>
</div>
<div class="a-field" style="max-width: 160px">
    <label class="a-label" for="sort">Urutan tampil</label>
    <input class="a-input" id="sort" name="sort" type="number" min="0" max="999" inputmode="numeric"
        value="{{ old('sort', $contact?->sort ?? $nextSort ?? 1) }}" aria-describedby="sort-help" @error('sort') aria-invalid="true" @enderror>
    <p class="a-help" id="sort-help">Angka kecil tampil lebih dulu.</p>
    @error('sort')
        <p class="a-error">{{ $message }}</p>
    @enderror
</div>
