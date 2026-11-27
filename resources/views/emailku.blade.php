@php
    // Email akun peserta. Ditulis dengan tabel + gaya inline agar tampil rapi di
    // Gmail, Outlook, dan aplikasi email HP (CSS eksternal/flex tidak didukung).
    $period = $setting->periodLabel();
    $campusNim = $data->campusNim();
    // Ke halaman awal situs resmi dulu (jadwal, paslon), baru masuk.
    // Alamat dari config('election.site_url'), tidak bergantung dari mana email dikirim.
    $siteUrl = config('election.site_url');
    // Logo disisipkan ke dalam email (CID), bukan ditautkan ke situs: tetap tampil
    // walau situs belum online / APP_URL masih localhost. $message hanya ada saat
    // email benar-benar dikirim; saat dirender untuk pratinjau dipakai URL biasa.
    $logoPath = resource_path('mail/hmif-logo.png');
    $logoSrc = isset($message) ? $message->embed($logoPath) : asset('assets/logos/' . rawurlencode('Logo HMIF Compress.png'));
    $when = fn ($date) => $date->copy()->locale('id')->translatedFormat('l, j F Y') . ' pukul ' . $date->format('H.i') . ' WIB';

    $deep = '#0E3532';
    $teal = '#31BDAA';
    $ink = '#122423';
    $muted = '#3F5553';
    $font = "'Segoe UI', Roboto, Helvetica, Arial, sans-serif";
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light only">
    <title>Akun pemilihan Ketua HMIF {{ $period }}</title>
</head>

<body style="margin:0; padding:0; background:#F2F7F6; color:{{ $ink }}; font-family:{{ $font }};">
    {{-- Teks pratinjau di daftar kotak masuk (tidak tampil di isi email) --}}
    <div style="display:none; max-height:0; overflow:hidden; opacity:0;">
        Username dan password untuk memilih Ketua dan Wakil Ketua Umum HMIF periode {{ $period }}.
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#F2F7F6;">
        <tr>
            <td align="center" style="padding:24px 12px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:560px; background:#FFFFFF; border-radius:10px; overflow:hidden; border:1px solid #D3E4E1;">

                    {{-- Kepala --}}
                    <tr>
                        <td style="background:{{ $deep }}; padding:24px 28px; border-bottom:6px solid {{ $teal }};">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="padding-right:14px; vertical-align:middle;">
                                        <img src="{{ $logoSrc }}" width="48" height="48" alt="HMIF" style="display:block; border:0;">
                                    </td>
                                    <td style="vertical-align:middle; color:#FFFFFF;">
                                        <div style="font-size:13px; color:#CFE3E0; letter-spacing:.2px;">Election Vote HMIF</div>
                                        <div style="font-size:22px; font-weight:800; line-height:1.25;">Akun pemilihanmu sudah siap</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Pembuka --}}
                    <tr>
                        <td style="padding:28px 28px 8px; font-size:15px; line-height:1.6;">
                            <p style="margin:0 0 12px; font-size:18px; font-weight:700;">Halo, {{ $data->participant_name }}!</p>
                            <p style="margin:0 0 12px;">
                                Pemilihan Ketua dan Wakil Ketua Umum HMIF periode <strong>{{ $period }}</strong> sudah tiba.
                                Berikut akun yang kamu pakai untuk memberikan suara.
                            </p>
                            @if ($setting->vote_start && $setting->vote_end)
                                <p style="margin:0; padding:12px 14px; background:#E8F4F2; border-radius:8px; color:{{ $ink }};">
                                    <strong>Waktu memilih</strong><br>
                                    {{ $when($setting->vote_start) }}<br>
                                    sampai {{ $when($setting->vote_end) }}
                                </p>
                            @endif
                        </td>
                    </tr>

                    {{-- Kartu akun --}}
                    <tr>
                        <td style="padding:20px 28px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border:2px dashed #9FC9C3; border-radius:10px;">
                                <tr>
                                    <td style="padding:16px 18px 6px; font-size:13px; color:{{ $muted }};">Nama</td>
                                    <td style="padding:16px 18px 6px; font-size:15px; font-weight:600; text-align:right;">{{ $data->participant_name }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:6px 18px 14px; font-size:13px; color:{{ $muted }}; border-bottom:1px solid #D3E4E1;">NIM</td>
                                    <td style="padding:6px 18px 14px; font-size:15px; font-weight:600; text-align:right; border-bottom:1px solid #D3E4E1;">{{ $campusNim }}</td>
                                </tr>
                                <tr>
                                    <td colspan="2" style="padding:16px 18px 4px; font-size:13px; color:{{ $muted }};">Username</td>
                                </tr>
                                <tr>
                                    <td colspan="2" style="padding:0 18px 12px; font-family:'Courier New', Courier, monospace; font-size:24px; font-weight:700; letter-spacing:1px; color:{{ $ink }};">{{ $data->username_old }}</td>
                                </tr>
                                <tr>
                                    <td colspan="2" style="padding:4px 18px 4px; font-size:13px; color:{{ $muted }};">Password</td>
                                </tr>
                                <tr>
                                    <td colspan="2" style="padding:0 18px 18px; font-family:'Courier New', Courier, monospace; font-size:24px; font-weight:700; letter-spacing:1px; color:{{ $ink }};">{{ $data->password_old }}</td>
                                </tr>
                            </table>
                            <p style="margin:10px 0 0; font-size:13px; color:{{ $muted }}; line-height:1.5;">
                                Password membedakan huruf besar dan kecil. Salin persis seperti di atas.
                            </p>
                        </td>
                    </tr>

                    {{-- Tombol masuk --}}
                    <tr>
                        <td align="center" style="padding:8px 28px 24px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center" bgcolor="#FFE500" style="border-radius:8px;">
                                        <a href="{{ $siteUrl }}" target="_blank"
                                            style="display:inline-block; padding:15px 34px; font-size:17px; font-weight:800; color:{{ $ink }}; text-decoration:none; border-radius:8px;">
                                            Buka situs pemilihan
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            <p style="margin:10px 0 0; font-size:12px; color:{{ $muted }};">
                                Atau buka: <a href="{{ $siteUrl }}" style="color:#1C6B63;">{{ $siteUrl }}</a>
                            </p>
                        </td>
                    </tr>

                    {{-- Langkah --}}
                    <tr>
                        <td style="padding:0 28px 8px; font-size:15px; line-height:1.6;">
                            <p style="margin:0 0 8px; font-weight:700;">Cara memilih</p>
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                @foreach (['Buka situs pemilihan lewat tombol di atas. Kamu bisa melihat jadwal dan mengenal paslon lebih dulu.', 'Tekan Masuk, lalu ketik username dan password dari email ini.', 'Ketuk foto paslon pilihanmu, lalu tekan Kirim suara. Suara hanya bisa dikirim sekali.'] as $i => $step)
                                    <tr>
                                        <td style="vertical-align:top; padding:0 10px 8px 0;">
                                            <span style="display:inline-block; width:24px; height:24px; line-height:24px; text-align:center; border-radius:50%; background:{{ $teal }}; color:{{ $ink }}; font-size:13px; font-weight:800;">{{ $i + 1 }}</span>
                                        </td>
                                        <td style="vertical-align:top; padding:0 0 8px;">{{ $step }}</td>
                                    </tr>
                                @endforeach
                            </table>
                        </td>
                    </tr>

                    {{-- Pengingat --}}
                    <tr>
                        <td style="padding:8px 28px 4px; font-size:15px; line-height:1.6;">
                            <p style="margin:0 0 10px;">
                                Ssshhh... jangan sebarkan akun ini ke siapa pun ya. Satu suaramu sangat berarti untuk menentukan arah Informatika ke depan.
                            </p>
                            <p style="margin:0; padding-left:12px; border-left:3px solid {{ $teal }}; font-style:italic; color:{{ $muted }};">
                                "Pemilihan itu seperti Milea buat Dilan: penting dan nggak boleh dilewatkan." (Dilan 1990)
                            </p>
                        </td>
                    </tr>

                    {{-- Bantuan: kontak panitia dari menu Kontak panitia (disembunyikan bila kosong) --}}
                    @php $contacts = \App\Models\ContactPerson::ordered()->get(); @endphp
                    @if ($contacts->isNotEmpty())
                        <tr>
                            <td style="padding:20px 28px 26px; font-size:14px; line-height:1.6;">
                                <p style="margin:0 0 6px; font-weight:700;">Ada kendala?</p>
                                <p style="margin:0;">
                                    Hubungi panitia lewat WhatsApp:
                                    @foreach ($contacts as $contact)
                                        <br><a href="{{ $contact->waLink() }}" style="color:#1C6B63; font-weight:600;">{{ $contact->name }}, {{ $contact->displayPhone() }}</a>
                                    @endforeach
                                </p>
                            </td>
                        </tr>
                    @endif

                    {{-- Kaki --}}
                    <tr>
                        <td style="background:#F7FAFA; padding:16px 28px; font-size:12px; line-height:1.5; color:{{ $muted }}; border-top:1px solid #D3E4E1;">
                            Email ini dikirim otomatis oleh panitia pemilihan HMIF Universitas Jember. Mohon tidak membalas email ini.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
