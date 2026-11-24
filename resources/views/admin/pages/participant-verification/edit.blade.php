@extends('admin.layouts.app')
@section('extraCSS')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/css/selectize.bootstrap3.min.css" integrity="sha256-ze/OEYGcFbPRmvCnrSeKbRTtjG4vGLHXgOqsyLFTRjg=" crossorigin="anonymous" />
@endsection
@section('content')
<div>
    <div class="card mb-8">
        <div class="card-header flex flex-row justify-between">
            <h1 class="h6">Update Verifikasi Participant</h1>
        </div>
        <div class="card-body">
            <form method="POST" action="{{url('/back-admin-logged/participant-verification/update/'.$getDetailParticipant->id)}}">
                @method('PUT')
                @csrf
                <div>
                    <label class="text-gray-700 ml-1"> NIM Participant : </label>
                    <select name="nim" id="selectSearch" disabled class="form-input w-full block border-teal-500 rounded mt-1 pt-3 pb-2 px-3 border-2 @error('nim') border-red-500 @enderror focus:outline-none focus:border-teal-500" >
                        <option value="">Cari NIM ...</option>
                        @foreach ($getParticipant as $item)
                            <option value="{{$item->id}}" {{$getDetailParticipant->nim == $item->nim ? 'selected' : ''}} >{{$item->nim}} - {{$item->participant_name}}</option>
                        @endforeach
                    </select>
                    @error('nim')
                    <span class="pl-1 text-xs text-red-600 text-bold">
                        {{$message}}
                    </span>
                    @enderror
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="mt-3">
                        <label class="text-gray-700 ml-1"> Username : </label>
                        <div class="grid grid-cols-12">
                            <div class="col-span-11">
                                <input type="text" id="new_username" name="username" class="form-input w-full block rounded mt-1 p-3 border-2 @error('username') border-red-500 @enderror focus:outline-none focus:border-teal-500" placeholder="Username" value="{{old('username', $getDetailParticipant->username_old)}}" readonly>
                            </div>
                            <div class="col-span-1" style="padding: 10px;">
                                <button type="button" onclick="getUsername()" class="bg-teal-500 h-10 w-10 rounded hover:bg-teal-600"><i class="fa fa-recycle text-white"></i></button>
                            </div>
                        </div>
                        @error('username')
                        <span class="pl-1 text-xs text-red-600 text-bold">
                            {{$message}}
                        </span>
                        @enderror
                    </div>
                    <div class="mt-3">
                        <label class="text-gray-700 ml-1"> Password : </label>
                        <div class="grid grid-cols-12">
                            <div class="col-span-11">
                                <input type="password" id="new_password" name="password" class="form-input w-full block rounded mt-1 p-3 border-2 @error('password') border-red-500 @enderror focus:outline-none focus:border-teal-500" placeholder="Password" value="{{old('password', $getDetailParticipant->password_old)}}" readonly>
                            </div>
                            <div class="col-span-1" style="padding: 10px;">
                                <button type="button" onclick="getPassword()" class="bg-teal-500 h-10 w-10 rounded hover:bg-teal-600"><i class="fa fa-recycle text-white"></i></button>
                            </div>
                        </div>
                        @error('password')
                        <span class="pl-1 text-xs text-red-600 text-bold">
                            {{$message}}
                        </span>
                        @enderror
                    </div>
                </div>
                <div class="mt-3">
                    <label class="text-gray-700 ml-1"> Nomor Whatsapp : </label>
                    <input type="text" name="whatsapp_number" class="form-input w-full block rounded mt-1 p-3 border-2 @error('whatsapp_number') border-red-500 @enderror focus:outline-none focus:border-teal-500" placeholder="Nomor Whatsapp" value="{{old('whatsapp_number', $getDetailParticipant->whatsapp_number)}}">
                    @error('whatsapp_number')
                    <span class="pl-1 text-xs text-red-600 text-bold">
                        {{$message}}
                    </span>
                    @enderror
                </div>
                <div class="mt-3">
                    <label class="text-gray-700 ml-1">Status : </label>
                    <select name="status" class="form-input mt-1 p-3 border-2 @error('status') border-red-500 @enderror focus:outline-none focus:border-teal-500 form-select appearance-none block w-full px-3 py-1.5 text-base font-normal text-gray-700 bg-white bg-clip-padding bg-no-repeat border border-solid border-gray-300 rounded transition ease-in-out m-0">
                        <option value="verified" {{$getDetailParticipant->status == 'verified' ? 'selected' : ''}} >Verified</option>
                        <option value="not verified" {{$getDetailParticipant->status == 'not verified' ? 'selected' : ''}}>Not Verified</option>
                    </select>
                    @error('status')
                    <span class="pl-1 text-xs text-red-600 text-bold">
                        {{$message}}
                    </span>
                    @enderror
                </div>
                <div class="mt-5">
                    <button type="submit" class="btn-shadow">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@section('extraJS')
<script src="https://unpkg.com/flowbite@1.5.3/dist/flowbite.js"></script>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/js/standalone/selectize.min.js" integrity="sha256-+C0A5Ilqmu4QcSPxrlGpaZxJ04VjsRjKu+G82kl5UJk=" crossorigin="anonymous"></script>
<script>
    $(document).ready(function () {
        $('#selectSearch').selectize({
            sortField: 'text'
        });
    });

    function getUsername(){
        $.ajax({
            type:'GET',
            url:'/generateNewUsername',
            success:function(data){
                $('#new_username').val(data);
            }
        });
    }

    function getPassword(){
        $.ajax({
            type:'GET',
            url:'/generateNewPassword',
            success:function(data){
                $('#new_password').val(data);
            }
        });
    }
</script>
@endsection