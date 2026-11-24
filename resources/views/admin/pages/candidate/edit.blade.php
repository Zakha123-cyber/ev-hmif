@extends('admin.layouts.app')
@section('content')
<div>
    <div class="card mb-8">
        <div class="card-header flex flex-row justify-between">
            <h1 class="h6">Edit Kandidat</h1>
        </div>
        <div class="card-body">
            <form method="POST" action="{{url('/back-admin-logged/candidate/update/'. $getDetailCandidate->id)}}" enctype="multipart/form-data">
                @method('put')
                @csrf
                <div>
                    <label class="text-gray-700 ml-1"> Nomor Pasangan Calon : </label>
                    <input type="number" name="candidate_pair" class="form-input w-full block rounded mt-1 p-3 border-2 @error('candidate_pair') border-red-500 @enderror focus:outline-none focus:border-teal-500" placeholder="Nomor Pasangan Calon" value="{{old('candidate_pair', $getDetailCandidate->candidate_pair)}}">
                    @error('candidate_pair')
                    <span class="pl-1 text-xs text-red-600 text-bold">
                        {{$message}}
                    </span>
                    @enderror
                </div>
                <div class="mt-3">
                    <label class="text-gray-700 ml-1"> Nama Ketua : </label>
                    <input type="text" name="leader_name" class="form-input w-full block rounded mt-1 p-3 border-2 @error('leader_name') border-red-500 @enderror focus:outline-none focus:border-teal-500" placeholder="Nama Ketua" value="{{old('leader_name', $getDetailCandidate->leader_name)}}">
                    @error('leader_name')
                    <span class="pl-1 text-xs text-red-600 text-bold">
                        {{$message}}
                    </span>
                    @enderror
                </div>
                <div class="mt-3">
                    <label class="text-gray-700 ml-1"> Nama Wakil Ketua : </label>
                    <input type="text" name="vice_leader_name" class="form-input w-full block rounded mt-1 p-3 border-2 @error('vice_leader_name') border-red-500 @enderror focus:outline-none focus:border-teal-500" placeholder="Nama Wakil Ketua" value="{{old('vice_leader_name', $getDetailCandidate->vice_leader_name)}}">
                    @error('vice_leader_name')
                    <span class="pl-1 text-xs text-red-600 text-bold">
                        {{$message}}
                    </span>
                    @enderror
                </div>
                <div class="mt-3">
                    <label class="text-gray-700 ml-1"> Visi : </label>
                    <input type="text" name="vision" class="form-input w-full block rounded mt-1 p-3 border-2 @error('vision') border-red-500 @enderror focus:outline-none focus:border-teal-500" placeholder="Visi" value="{{old('vision', $getDetailCandidate->vision)}}">
                    @error('vision')
                    <span class="pl-1 text-xs text-red-600 text-bold">
                        {{$message}}
                    </span>
                    @enderror
                </div>
                <div class="mt-3">
                    <label class="text-gray-700 ml-1"> Misi : </label>
                    <input type="text" name="mission" class="form-input w-full block rounded mt-1 p-3 border-2 @error('mission') border-red-500 @enderror focus:outline-none focus:border-teal-500" placeholder="Misi" value="{{old('mission', $getDetailCandidate->mission)}}">
                    @error('mission')
                    <span class="pl-1 text-xs text-red-600 text-bold">
                        {{$message}}
                    </span>
                    @enderror
                </div>
                <div class="mt-3 grid grid-cols-2 gap-6 xl:grid-cols-1 items-center">
                    <div>
                        <label class="text-gray-700 ml-1">Poster : </label>
                        <div class='flex items-center justify-center w-full mt-2'>
                            <label class='flex flex-col border-4 border-dashed w-full h-32 hover:bg-gray-100 hover:border-teal-500 group'>
                                <div class='flex flex-col items-center justify-center pt-7 text-center'>
                                    <svg class="w-10 h-10 mt-8 text-teal-500 group-hover:text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    <p class='text-sm text-gray-400 group-hover:text-teal-600 pt-1 tracking-wider' id="fileName">Pilih Gambar</p>
                                </div>
                            <input type='file' class="hidden" name="poster" id="logos" />
                            </label>
                        </div>
                    </div>
                    <div>
                        <label class="text-gray-700 ml-1">Preview : </label>
                        <div class='flex items-center justify-center w-full mt-2'>
                            <label class='flex flex-col border-4 border-dashed w-full h-auto border-teal-500 group bg-gray-300'>
                                    <div class='flex flex-col items-center justify-center py-1'>
                                        <img id="preview" src="{{asset('assets/upload/kandidat/'. $getDetailCandidate->poster)}}" alt="preview" class="object-cover h-32">
                                    </div>
                            </label>
                        </div>
                    </div>
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
<script>
    logos.onchange = evt => {
        const [file] = logos.files;
        if (file) {
            preview.src = URL.createObjectURL(file);
            fileName.innerHTML = document.getElementById("logos").value.split("\\").pop();
        }
    }
</script>
@endsection