@php
    // Kartu akun untuk DomPDF: hanya tabel dan CSS inline (tanpa flex/grid, tanpa aset luar).
    $p = $dataDetailParticipantVerified;
    $period = \App\Models\ElectionSetting::current()->periodLabel();
    $rows = [
        ['Nama', $p->participant_name, false],
        ['NIM', $p->nim, false],
        ['Nomor WhatsApp', $p->whatsapp_number, false],
        ['Username', $p->username_old, true],
        ['Password', $p->password_old, true],
    ];
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Kartu akun pemilih</title>
</head>
<body style="font-family: DejaVu Sans, sans-serif; color: #122423; font-size: 11pt;">
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 18px;">
        <tr>
            <td style="background: #0E3532; color: #ffffff; padding: 14px 16px;">
                <div style="font-size: 15pt; font-weight: bold;">Kartu akun pemilih</div>
                <div style="font-size: 10pt; color: #CFE3E0;">Pemilihan Ketua dan Wakil Ketua HMIF periode {{ $period }}</div>
            </td>
        </tr>
    </table>

    <table style="width: 100%; border-collapse: collapse;">
        @foreach ($rows as [$label, $value, $strong])
            <tr>
                <th scope="row" style="width: 30%; text-align: left; padding: 10px 12px; border: 1px solid #9FC9C3; background: #F2F7F6;">{{ $label }}</th>
                <td style="padding: 10px 12px; border: 1px solid #9FC9C3; {{ $strong ? 'font-weight: bold; font-size: 13pt;' : '' }}">{{ $value }}</td>
            </tr>
        @endforeach
    </table>

    <p style="margin-top: 14px; font-size: 9pt; color: #A3161B;">
        Catatan: username dan password di atas dipakai untuk login ke {{ url('/login') }} pada pemilihan ketua HMIF periode {{ $period }}.
        Bersifat RAHASIA, jangan dibagikan ke siapa pun.
    </p>
</body>
</html>
