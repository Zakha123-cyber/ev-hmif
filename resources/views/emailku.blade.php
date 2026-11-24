<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Informasi Pemilihan HMIF</title>
</head>

<body style="font-family: Arial, Helvetica, sans-serif; color: #000; line-height: 1.6; padding: 20px; font-size: 14px;">

    <h2 style="margin-bottom: 12px; font-size: 20px;">Hai Infortination..</h2>

    <p>
        Hari ini pemilihan Ketua dan Wakil Ketua Umum HMIF Periode 2025/2026 resmi dimulai.
        Berikut akun yang bisa kamu gunakan untuk memilih:
    </p>

    <table style="margin-top: 12px; margin-bottom: 22px; border-collapse: collapse;">
        <tr>
            <td style="padding: 3px 8px 3px 0;">Nama</td>
            <td style="padding: 3px 0;">: {{ $data->participant_name }}</td>
        </tr>
        <tr>
            <td style="padding: 3px 8px 3px 0;">NIM</td>
            <td style="padding: 3px 0;">: {{ substr($data->nim, 0, 2) . '241010' . substr($data->nim, 2) }}</td>
        </tr>
        <tr>
            <td style="padding: 3px 8px 3px 0;">Username</td>
            <td style="padding: 3px 0;">: {{ $data->username_old }}</td>
        </tr>
        <tr>
            <td style="padding: 3px 8px 3px 0;">Password</td>
            <td style="padding: 3px 0;">: {{ $data->password_old }}</td>
        </tr>
        <tr>
            <td style="padding: 3px 8px 3px 0;">Link</td>
            <td style="padding: 3px 0;">:
                <a href="https://election-vote.hmifunej.id/login" style="color:#0066cc;">
                    Klik untuk login
                </a>
            </td>
        </tr>
    </table>

    <p>
        Ssshhh... Jangan sebarkan akun ini ke siapa pun ya.
        Satu suaramu sangat berarti untuk menentukan arah Informatika ke depan.
    </p>

    <p style="font-style: italic; color: #555; margin-top: 10px;">
        “Pemilihan itu seperti Milea buat Dilan: penting dan nggak boleh dilewatkan.” — Dilan 1990
    </p>

    <p style="margin-top: 20px;">
        Jika mengalami kendala, hubungi WhatsApp berikut: <br>
        CS 1:
        <a href="http://wa.me/6283823093973" style="color:#0066cc;">083823093973</a> (Farda) <br>
        CS 2:
        <a href="http://wa.me/6283852061105" style="color:#0066cc;">083852061105</a> (Dewi)
    </p>

    <p style="color: #888; font-size: 12px; margin-top: 25px;">
        [NO-REPLY MESSAGE]
    </p>

</body>

</html>
