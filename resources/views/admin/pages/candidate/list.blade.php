@extends('admin.layouts.app')
@section('content')
<div>
    <a href="{{url('/back-admin-logged/candidate/add')}}" class="btn-shadow w-56">Tambah Kandidat</a>
    <div class="mt-6 grid grid-cols-2 gap-6 xl:grid-cols-1">
        @forelse ($dataCandidate as $item)
        <div class="bg-white col-span-2 border rounded-lg shadow-md relative">
            <div class="absolute right-0 justify-end px-4 pt-3">
                <button id="dropdownButton-{{$item->id}}" data-dropdown-toggle="dropdown-{{$item->id}}" class="inline-block text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:ring-4 focus:outline-none focus:ring-gray-200 dark:focus:ring-gray-700 rounded-lg text-sm p-1.5" type="button">
                    <span class="sr-only">Buka Menu</span>
                    <svg class="w-6 h-6" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z"></path></svg>
                </button>
                <div id="dropdown-{{$item->id}}" class="hidden z-10 px-3 text-base list-none bg-white rounded divide-y divide-gray-100 shadow dark:bg-gray-700">
                    <ul class="py-1" aria-labelledby="dropdownButton-{{$item->id}}">
                    <li>
                        <form action="{{url('/back-admin-logged/candidate/edit',$item->id)}}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="block py-2 px-4 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white">Edit</button>
                        </form>
                    </li>
                    <li>
                        <form action="{{url('/back-admin-logged/candidate/delete',$item->id)}}" method="POST" class="inline">
                            @method('delete')
                            @csrf
                            <button type="submit" class="block py-2 px-4 text-sm text-red-600 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white" onclick="return confirm('Hapus Data ?')">Delete</button>
                        </form>
                    </li>
                    </ul>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-4">
                <div class="rounded-lg bg-white shadow-lg">
                    <img class="h-full w-full md:h-auto object-cover rounded-l-lg md:rounded-none md:rounded-l-lg" src="{{asset('assets/upload/kandidat/'.$item->poster)}}" alt="poster-{{$loop->iteration}}" />
                </div>
                <div class="p-6 col-span-2 justify-start">
                    <h5 class="text-gray-900 text-xl font-bold my-3">Pasangan Calon {{$item->candidate_pair}}</h5>
                    <div class="overflow-x-auto relative shadow-md sm:rounded-lg">
                        <table class="w-full text-sm text-left">
                            <tbody>
                                <tr class="border-b">
                                    <th scope="row" class="py-4 pl-6 border font-semibold whitespace-nowrap">
                                        Nama Ketua
                                    </th>
                                    <td class="py-4 px-6 border">
                                        {{$item->leader_name}}
                                    </td>
                                </tr>
                                <tr class="border-b">
                                    <th scope="row" class="py-4 pl-6 border font-semibold whitespace-nowrap">
                                        Nama Wakil Ketua
                                    </th>
                                    <td class="py-4 px-6 border">
                                        {{$item->vice_leader_name}}
                                    </td>
                                </tr>
                                <tr class="border-b">
                                    <th scope="row" class="py-4 pl-6 border font-semibold whitespace-nowrap">
                                        Visi
                                    </th>
                                    <td class="py-4 px-6 border">
                                        {{$item->vision}}
                                    </td>
                                </tr>
                                <tr class="border-b">
                                    <th scope="row" class="py-4 pl-6 border font-semibold whitespace-nowrap">
                                        Misi
                                    </th>
                                    <td class="py-4 px-6 border">
                                        {!!$item->mission!!}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>  
        @empty
        <div class="col-span-2 w-full items-center justify-center bg-grey-200 py-48">
            <div class="flex flex-col">
                <div class="flex flex-col items-center">
                    <div class="text-indigo-500 font-bold text-7xl">
                        No Matching Records Found
                    </div>
                </div>
            </div>
        </div>
        @endforelse
    </div>

    
</div>
@endsection
@section('extraJS')
<script src="https://unpkg.com/flowbite@1.5.3/dist/flowbite.js"></script>
@endsection