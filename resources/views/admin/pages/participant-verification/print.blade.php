<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>ElectionVote | Admin</title>
</head>
<body>
    <div style="width: 100vw;">
        <h3 style="text-align: center;">Data Participant Election Vote</h3>
        <table style="width: 100%; border: solid thin; border-collapse: collapse; padding: 10px;">
            <tbody>
                <tr>
                    <th scope="row" style="width: 25%; text-align: left; border: solid thin; padding: 10px;">
                        Nama Participant
                    </th>
                    <td style="text-align: left; border: solid thin; padding: 10px;">
                        {{$dataDetailParticipantVerified->participant_name}}
                    </td>
                </tr>
                <tr>
                    <th scope="row" style="width: 25%; text-align: left; border: solid thin; padding: 10px;">
                        NIM
                    </th>
                    <td style="text-align: left; border: solid thin; padding: 10px;">
                        {{$dataDetailParticipantVerified->nim}}
                    </td>
                </tr>
                <tr>
                    <th scope="row" style="width: 25%; text-align: left; border: solid thin; padding: 10px;">
                        Nomor Whatsapp
                    </th>
                    <td style="text-align: left; border: solid thin; padding: 10px;">
                        {{$dataDetailParticipantVerified->whatsapp_number}}
                    </td>
                </tr>
                <tr>
                    <th scope="row" style="width: 25%; text-align: left; border: solid thin; padding: 10px;">
                        Username
                    </th>
                    <td style="text-align: left; border: solid thin; padding: 10px; font-weight: bold;">
                        {{$dataDetailParticipantVerified->username_old}}
                    </td>
                </tr>
                <tr>
                    <th scope="row" style="width: 25%; text-align: left; border: solid thin; padding: 10px;">
                        Password
                    </th>
                    <td style="text-align: left; border: solid thin; padding: 10px; font-weight: bold;">
                        {{ $dataDetailParticipantVerified->password_old }}
                    </td>
                </tr>
            </tbody>
        </table>
        <span style="color: red; font-size: 8.3pt; padding-left: 10px; padding-top: -20px; padding-right: 20px;">
            *Catatan: Password diatas adalah password yang digunakan untuk login ke sistem E-Vote pemilihan ketua HMIF 2022/2023. Dan bersifat RAHASIA.
        </span>
    </div>
</body>
</html>
