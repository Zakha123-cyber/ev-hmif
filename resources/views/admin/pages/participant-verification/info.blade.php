@extends('admin.layouts.app')

@section('title', 'Info akun peserta')

@php
    $p = $dataDetailParticipantVerified;
    $period = \App\Models\ElectionSetting::current()->periodLabel();
    // Templat pesan WhatsApp lama; hanya periodenya kini mengikuti pengaturan admin.
    $message = implode("\n", [
        'Hai Infornation..',
        '',
        "Tepat hari ini kita melakukan pemilihan calon ketua dan calon wakil ketua umum Himpunan Mahasiswa Informatika (HMIF) Periode {$period}. Berikut untuk akun pemilihanmu.",
        '',
        "Nama : {$p->participant_name}",
        "NIM : {$p->nim}",
        "Nomor WA : {$p->whatsapp_number}",
        "Username : *{$p->username_old}*",
        "Password : *{$p->password_old}*",
        '',
        'Sshhtt... Jangan kamu sebar akunmu ya...',
        'Satu suaramu menentukan Informatika kedepannya.',
        '',
        "'Pemilu mengingatkan kita tidak hanya tentang hak, tetapi tanggung jawab kewarganegaraan dalam demokrasi'. Robert F. Kennedy.",
        '',
        '[NO-REPLY MESSAGE]',
    ]);
@endphp

@section('actions')
    <a class="a-btn a-btn--ghost" href="{{ url('/back-admin-logged/participant-verification') }}">Kembali ke daftar</a>
@endsection

@section('content')
    <section class="a-card">
        <div class="a-card__head">
            <h2 class="a-card__title">{{ $p->participant_name }}</h2>
            <span class="a-badge a-badge--ok">Terverifikasi</span>
        </div>
        <div class="a-card__body">
            <dl class="a-dl">
                <div><dt>NIM</dt><dd>{{ $p->nim }}</dd></div>
                <div><dt>WhatsApp</dt><dd>{{ $p->whatsapp_number ?: '-' }}</dd></div>
                <div><dt>Username</dt><dd><strong>{{ $p->username_old }}</strong></dd></div>
                <div>
                    <dt>Password</dt>
                    <dd class="a-inputgroup" style="align-items: center">
                        <span id="info-password-mask" aria-hidden="true">{{ str_repeat('•', max(8, strlen((string) $p->password_old))) }}</span>
                        <span id="info-password" hidden><strong>{{ $p->password_old }}</strong></span>
                        <button type="button" class="a-btn a-btn--ghost a-btn--sm" data-reveal="#info-password" data-mask="#info-password-mask" aria-pressed="false" hidden>
                            @include('admin.components.icon', ['name' => 'eye'])
                            Tampilkan
                        </button>
                    </dd>
                </div>
            </dl>

            <div class="a-actions" style="margin-top: 20px">
                @if ($p->whatsapp_number)
                    <a class="a-btn a-btn--primary" target="_blank" rel="noopener"
                        href="https://api.whatsapp.com/send?phone={{ $p->whatsapp_number }}&text={{ rawurlencode($message) }}">
                        @include('admin.components.icon', ['name' => 'chat'])
                        Kirim akun lewat WhatsApp
                    </a>
                @endif
                <a class="a-btn a-btn--ghost" target="_blank" rel="noopener"
                    href="{{ url('/back-admin-logged/participant-verification/info/print/' . $p->id) }}">
                    @include('admin.components.icon', ['name' => 'print'])
                    Cetak kartu
                </a>
            </div>
            <p class="a-help" style="margin-top: 12px">Pesan WhatsApp berisi username dan password peserta. Pastikan nomornya benar sebelum mengirim.</p>
        </div>
    </section>
@endsection
