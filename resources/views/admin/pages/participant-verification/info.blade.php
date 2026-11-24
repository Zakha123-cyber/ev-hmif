@extends('admin.layouts.app')
@section('content')
<div>
    <div class="mt-6 grid grid-cols-2 gap-6 xl:grid-cols-1">
        <div class="bg-white col-span-2 border rounded-lg shadow-md relative">
            <div class="grid grid-cols-3 gap-4">
                <div class="p-6 col-span-3 justify-start">
                    <h5 class="text-gray-900 text-xl text-center font-bold my-3">Detail Participant</h5>
                    <div class="overflow-x-auto mt-8 relative shadow-md sm:rounded-lg">
                        <table class="w-full text-sm text-left">
                            <tbody>
                                <tr class="border-b">
                                    <th scope="row" class="py-4 pl-6 border font-semibold whitespace-nowrap">
                                        Nama Participant
                                    </th>
                                    <td class="py-4 px-6 border">
                                        {{$dataDetailParticipantVerified->participant_name}}
                                    </td>
                                </tr>
                                <tr class="border-b">
                                    <th scope="row" class="py-4 pl-6 border font-semibold whitespace-nowrap">
                                        NIM
                                    </th>
                                    <td class="py-4 px-6 border">
                                        {{$dataDetailParticipantVerified->nim}}
                                    </td>
                                </tr>
                                <tr class="border-b">
                                    <th scope="row" class="py-4 pl-6 border font-semibold whitespace-nowrap">
                                        Nomor Whatsapp
                                    </th>
                                    <td class="py-4 px-6 border">
                                        {{$dataDetailParticipantVerified->whatsapp_number}}
                                    </td>
                                </tr>
                                <tr class="border-b">
                                    <th scope="row" class="py-4 pl-6 border font-semibold whitespace-nowrap">
                                        Username
                                    </th>
                                    <td class="py-4 px-6 border">
                                        {{$dataDetailParticipantVerified->username_old}}
                                    </td>
                                </tr>
                                <tr class="border-b">
                                    <th scope="row" class="py-4 pl-6 border font-semibold whitespace-nowrap">
                                        Password
                                    </th>
                                    <td class="py-4 px-6 border">
                                        {{ str_repeat("*", strlen($dataDetailParticipantVerified->password_old)) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-8 text-right">
                        <a href="https://api.whatsapp.com/send?phone={{$dataDetailParticipantVerified->whatsapp_number}}&text=Hai Infornation..%0a%0aTepat hari ini kita melakukan pemilihan calon ketua dan calon wakil ketua umum Himpunan Mahasiswa Informatika (HMIF) Periode 2022/2023. Berikut untuk akun pemilihanmu.%0a%0aNama : {{$dataDetailParticipantVerified->participant_name}}%0aNIM : {{$dataDetailParticipantVerified->nim}}%0aNomor WA : {{$dataDetailParticipantVerified->whatsapp_number}}%0aUsername : *{{$dataDetailParticipantVerified->username_old}}*%0aPassword : *{{$dataDetailParticipantVerified->password_old}}*%0a%0aSshhtt... Jangan kamu sebar akunmu ya...%0aSatu suaramu menentukan Informatika kedepannya.%0a%0a'Pemilu mengingatkan kita tidak hanya tentang hak, tetapi tanggung jawab kewarganegaraan dalam demokrasi'. Robert F. Kennedy.%0a%0a[NO-REPLY MESSAGE]" target="__blank" class="btn-shadow bg-teal-500 hover:bg-teal-600 w-56 py-3 inline" >Kirim Whatsapp</a> &nbsp;
                        <a href="{{url('/back-admin-logged/participant-verification/info/print/'.$dataDetailParticipantVerified->id)}}" class="btn-shadow bg-yellow-500 hover:bg-yellow-600 w-56 py-3 inline">Cetak Kartu</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
</div>
@endsection
@section('extraJS')
<script src="https://unpkg.com/flowbite@1.5.3/dist/flowbite.js"></script>
@endsection

{{-- <a
    href="
        https://api.whatsapp.com/send?
            phone=6281217245488
            &
            text=
                %0aHai Infornation..
                %0a
                %0aTepat hari ini kita melakukan pemilihan calon ketua dan calon wakil ketua umum Himpunan Mahasiswa Informatika (HMIF) Periode 2022/2023. Berikut untuk akun pemilihanmu.
                %0a
                %0aNama : {{$dataDetailParticipantVerified->participant_name}}
                %0aNIM : {{$dataDetailParticipantVerified->nim}}
                %0aNomor WA : {{$dataDetailParticipantVerified->whatsapp_number}}
                %0aUsername : {{$dataDetailParticipantVerified->username_old}}
                %0aPassword : {{$dataDetailParticipantVerified->password_old}}
                %0a
                %0aSshhtt... Jangan kamu sebar akunmu ya...
                %0aSatu suaramu menentukan Informatika kedepannya.
                %0a
                %0a'Pemilu mengingatkan kita tidak hanya tentang hak, tetapi tanggung jawab kewarganegaraan dalam demokrasi'. Robert F. Kennedy.
                %0a
                %0a[NO-REPLY MESSAGE]"
    target="__blank"
    class="btn-shadow bg-teal-500 hover:bg-teal-600 w-56 py-3 inline"
>Kirim Whatsapp</a> &nbsp; --}}