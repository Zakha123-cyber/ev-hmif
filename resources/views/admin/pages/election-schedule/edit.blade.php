@extends('admin.layouts.app')
@section('content')
@php
    $status = $setting->status();
    $statusLabel = [
        'not_set' => ['Belum diatur', 'bg-gray-300 text-gray-800'],
        'upcoming' => ['Belum dibuka', 'bg-yellow-300 text-yellow-800'],
        'open' => ['Sedang dibuka', 'bg-green-300 text-green-800'],
        'closed' => ['Sudah ditutup', 'bg-red-300 text-red-800'],
    ][$status];
@endphp
<div>
    <div class="card mb-8">
        <div class="card-header flex flex-row justify-between items-center">
            <h1 class="h6">Jadwal Pemilihan</h1>
            <span class="py-2 px-4 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusLabel[1] }}">
                {{ $statusLabel[0] }}
            </span>
        </div>
        <div class="card-body">
            <p class="text-gray-600 text-sm mb-4">
                Peserta hanya bisa mengirim suara di antara Waktu Dibuka dan Waktu Ditutup (zona waktu WIB).
                Jika jadwal belum diatur, pemilihan dianggap belum dibuka.
            </p>
            <form method="POST" action="{{ url('/back-admin-logged/election-schedule') }}">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-gray-700 ml-1"> Waktu Dibuka : </label>
                        <input type="datetime-local" name="vote_start" class="form-input w-full block rounded mt-1 p-3 border-2 @error('vote_start') border-red-500 @enderror focus:outline-none focus:border-teal-500" value="{{ old('vote_start', $setting->vote_start?->format('Y-m-d\TH:i')) }}">
                        @error('vote_start')
                        <span class="pl-1 text-xs text-red-600 text-bold">
                            {{ $message }}
                        </span>
                        @enderror
                    </div>
                    <div>
                        <label class="text-gray-700 ml-1"> Waktu Ditutup : </label>
                        <input type="datetime-local" name="vote_end" class="form-input w-full block rounded mt-1 p-3 border-2 @error('vote_end') border-red-500 @enderror focus:outline-none focus:border-teal-500" value="{{ old('vote_end', $setting->vote_end?->format('Y-m-d\TH:i')) }}">
                        @error('vote_end')
                        <span class="pl-1 text-xs text-red-600 text-bold">
                            {{ $message }}
                        </span>
                        @enderror
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
