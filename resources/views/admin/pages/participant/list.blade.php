@extends('admin.layouts.app')
@section('extraCSS')
	<link href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css" rel="stylesheet">
	<link href="https://cdn.datatables.net/responsive/2.2.3/css/responsive.dataTables.min.css" rel="stylesheet">
    <style>
        .dataTables_length{
            padding-top: 20px;
            padding-left: 20px;
        }
        .dataTables_filter{
            padding-top: 20px;
            padding-right: 20px;
        }
        .dataTables_info{
            padding-bottom: 20px;
            padding-left: 20px;
        }
        .dataTables_paginate{
            padding-bottom: 20px;
            padding-right: 20px;
        }
        .dataTables_wrapper{
            border: 1px solid lightgrey;
        }
        .sorting_asc, .sorting_desc{
            border: none !important;
            filter: invert(49%) sepia(73%) saturate(378%) hue-rotate(131deg) brightness(92%) contrast(94%);
        }
        .sorting{
            border: none !important;
        }

		.dataTables_wrapper select,
            .dataTables_wrapper .dataTables_filter input {
                color: #4a5568; 			
                padding-left: 1rem; 		
                padding-right: 1rem; 		
                padding-top: .5rem; 		
                padding-bottom: .5rem;
                line-height: 1.25;
                border-width: 2px;
                border-radius: .25rem; 		
                border-color: #edf2f7;
                background-color: #edf2f7;
            }

            table.dataTable.hover tbody tr:hover, table.dataTable.display tbody tr:hover {
                background-color: #ebf4ff;
            }
            
            .dataTables_wrapper .dataTables_paginate .paginate_button {
                font-weight: 700;
                border-radius: .25rem;
                border: 1px solid transparent;
            }
            
            .dataTables_wrapper .dataTables_paginate .paginate_button.current	{
                color: #fff !important;
                box-shadow: 0 1px 3px 0 rgba(0,0,0,.1), 0 1px 2px 0 rgba(0,0,0,.06);
                font-weight: 700;
                border-radius: .25rem;
                background: #2b9c9c !important;
                border: 1px solid transparent;
            }

            .dataTables_wrapper .dataTables_paginate .paginate_button:hover		{
                color: #fff !important;				/*text-white*/
                box-shadow: 0 1px 3px 0 rgba(0,0,0,.1), 0 1px 2px 0 rgba(0,0,0,.06);	 /*shadow*/
                font-weight: 700;
                border-radius: .25rem;
                background: #2b9c9c !important;
                border: 1px solid transparent;
            }
            
            table.dataTable.no-footer {
                border-bottom: 1px solid #e2e8f0;
                margin-top: 0.75em;
                margin-bottom: 0.75em;
            }
            
            /*Change colour of responsive icon*/
            table.dataTable.dtr-inline.collapsed>tbody>tr>td:first-child:before, table.dataTable.dtr-inline.collapsed>tbody>tr>th:first-child:before {
                background-color: #2b9c9c !important;
            }
	</style>
@endsection
@section('content')
<div>
    <a href="{{url('/back-admin-logged/participant/add')}}" class="btn-shadow w-56 py-3 inline">Tambah Participant</a>
    &nbsp;
    <span onclick="modalHandler(true)" class="btn-shadow btn-info w-56 py-3 inline cursor-pointer">Import Participant</span>
    <br><br>
    <div class="card">
        <div class="card-header flex flex-row justify-between">
            <h1 class="h6">List Participant</h1>
            <form method="POST" action="{{ url('/back-admin-logged/participant/broadcast') }}" class="inline" onsubmit="return confirmBroadcast(this)">
                @csrf
                <button type="submit" id="sendEmailBtn" class="btn-shadow w-56 py-3 inline">Send Email</button>
            </form>
        </div>
        <div class="card-body">
            <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                <table id="table" class="min-w-full divide-y divide-gray-200" style="width:100%; padding-top: 1em;  padding-bottom: 1em;">
                    <thead>
                        <tr>
                            <th data-priority="1">No</th>
                            <th data-priority="2">Nama Participant</th>
                            <th data-priority="3">NIM</th>
                            <th data-priority="4">Nomor Whatsapp</th>
                            <th data-priority="5">Status</th>
                            <th data-priority="6">Opsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($dataParticipant as $item)  
                        <tr style="background-color: {{$loop->iteration%2 != 0 ? 'rgb(245, 245, 245)' : 'white'}} ;">
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="text-sm text-gray-900">{{$loop->iteration}}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="text-sm text-gray-900">{{Str::limit($item->participant_name, 100)}}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="text-sm text-gray-900">{{Str::limit($item->nim, 100)}}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="text-sm text-gray-900">{{Str::limit($item->whatsapp_number, 100)}}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="text-sm text-gray-900">
                                    <span class="py-3 px-5 inline-flex text-xs leading-5 font-semibold rounded-full {{$item->status == 'verified' ? 'bg-green-300 text-green-800' : 'bg-yellow-300 text-yellow-800'}}">
                                        {{Str::limit($item->status, 100)}}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center">
                                <form action="{{url('/back-admin-logged/participant/edit',$item->id)}}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="bg-teal-500 h-10 w-10 rounded hover:bg-teal-600"><i class="fa fa-pencil text-white"></i></button>
                                </form><br><br>
                                <form action="{{url('/back-admin-logged/participant/email-backup',$item->id)}}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="bg-yellow-500 h-10 w-10 rounded hover:bg-yellow-600"><i class="fa fa-envelope text-white"></i></button>
                                </form><br><br>
                                <form action="{{url('/back-admin-logged/participant/delete',$item->id)}}" method="POST" class="inline">
                                    @method('delete')
                                    @csrf
                                    <button type="submit" class="bg-red-600 h-10 w-10 rounded hover:bg-red-700" onclick="return confirm('Hapus Data ?')"><i class="fa fa-trash text-white"></i></button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div>
    <div class="bg-gray-700 bg-opacity-50 transition duration-150 ease-in-out z-10 fixed right-0 top-0 bottom-0 left-0 hidden" id="modal">
        <div role="alert" class="container mx-auto w-11/12 md:w-2/3 max-w-lg grid content-center ">
            <div class="relative py-8 px-5 md:px-10 bg-white shadow-md rounded border border-gray-400">
                <button class="cursor-pointer absolute top-0 right-0 mt-4 mr-5 text-gray-400 hover:text-gray-600 transition duration-150 ease-in-out rounded focus:ring-2 focus:outline-none focus:ring-gray-600" onclick="modalHandler()" aria-label="close modal" role="button">
                    <svg  xmlns="http://www.w3.org/2000/svg"  class="icon icon-tabler icon-tabler-x" width="20" height="20" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" />
                        <line x1="18" y1="6" x2="6" y2="18" />
                        <line x1="6" y1="6" x2="18" y2="18" />
                    </svg>
                </button>
                <div class="w-full flex justify-start text-gray-600 mb-3">
                    <svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" width="40" height="40" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 490.701 490.701" style="enable-background:new 0 0 490.701 490.701;" xml:space="preserve">
                        <g>
                            <g>
                                <path d="M143.701,152.374l152.8,109.7c3.7,2.7,8.7,3,12.7,0.9c4.1-2.1,6.7-6.3,6.7-10.9v-49.6c55.9,0.4,104.9,6,151.9,88.8
                                    c2.2,3.9,6.3,6.2,10.7,6.2c1,0,2.1-0.1,3.1-0.4c5.4-1.4,9.1-6.3,9.1-11.8c0-32.1-2.8-79.4-26.6-120.3
                                    c-28.1-48.1-77.9-75.5-148.2-81.5v-50.3c0-4.6-2.6-8.8-6.6-10.9c-4.1-2.1-9-1.7-12.7,0.9l-152.9,109.3c-3.2,2.3-5.1,6-5.1,10
                                    C138.601,146.374,140.501,150.074,143.701,152.374z M291.401,56.974v38c0,6.5,5.1,11.9,11.6,12.2c118,5.9,151.8,70.7,160.5,133.9
                                    c-50.8-63.1-106.1-63.1-159.9-63.1l0,0c-3.2,0-6.4,1.3-8.7,3.6s-3.6,5.4-3.6,8.7v38l-119.5-85.8L291.401,56.974z"/>
                                <path d="M12.201,469.774h370.8c6.8,0,12.3-5.5,12.3-12.3v-188.1c0-6.8-5.5-12.3-12.3-12.3s-12.3,5.5-12.3,12.3v175.9h-346.3
                                    v-290.4h62.4c6.8,0,12.3-5.5,12.3-12.3s-5.5-12.3-12.3-12.3h-74.6c-6.8,0-12.2,5.5-12.2,12.3v314.9
                                    C-0.099,464.374,5.401,469.774,12.201,469.774z"/>
                            </g>
                        </g>
                    </svg>                      
                    <h2 class="pt-5 pl-3 text-gray-800 font-lg font-bold tracking-normal leading-tight mb-4">Import Data dari Excel</h2>
                </div>
                <div>
                    <form action="{{url('/back-admin-logged/participant/import')}}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div>
                            <label for="file" class="text-gray-800 text-sm font-bold leading-tight tracking-normal">Pilih file (.xlsx / .xls / .csv)</label>
                            <p class="text-gray-600 text-xs mt-1">
                                Kolom A = Nama, kolom B = NIM (angka). Baris judul boleh ada.
                                NIM yang sudah terdaftar dilewati. Semua peserta masuk sebagai <em>not verified</em>.
                            </p>
                            <input id="file" type="file" name="file" class="mb-5 mt-2 py-2 text-gray-600 focus:outline-none focus:border focus:border-indigo-700 font-normal w-full flex items-center pl-3 text-sm border-gray-300 rounded border"/>
                        </div>
                        <div class="flex items-center justify-end w-full">
                            <button type="submit" class="focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-700 transition duration-150 ease-in-out hover:bg-teal-600 bg-teal-700 rounded text-white px-8 py-2 text-sm">Submit</button>
                            <button class="focus:outline-none focus:ring-2 focus:ring-offset-2  focus:ring-gray-400 ml-3 bg-gray-100 transition duration-150 text-gray-600 ease-in-out hover:border-gray-400 hover:bg-gray-300 border rounded px-8 py-2 text-sm" onclick="modalHandler()">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('extraJS')
<script src="https://unpkg.com/flowbite@1.5.3/dist/flowbite.js"></script>

<script type="text/javascript" src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.3/js/dataTables.responsive.min.js"></script>
<script>
    $(document).ready(function() {

        var table = $('#table').DataTable({
                responsive: true
            })
            .columns.adjust()
            .responsive.recalc();
    });
</script>
<script>
    let modal = document.getElementById("modal");
    function modalHandler(val) {
        if (val) {
            fadeIn(modal);
        } else {
            fadeOut(modal);
        }
    }
    function fadeOut(el) {
        el.style.opacity = 1;
        (function fade() {
            if ((el.style.opacity -= 0.1) < 0) {
                el.style.display = "none";
            } else {
                requestAnimationFrame(fade);
            }
        })();
    }
    function fadeIn(el, display) {
        el.style.opacity = 0;
        el.style.display = display || "flex";
        (function fade() {
            let val = parseFloat(el.style.opacity);
            if (!((val += 0.2) > 1)) {
                el.style.opacity = val;
                requestAnimationFrame(fade);
            }
        })();
    }
</script>
<script>
    function confirmBroadcast(form) {
        if (!confirm('Buat akun & kirim email ke semua peserta yang belum punya akun?')) {
            return false;
        }
        const button = form.querySelector('button');
        button.style.opacity = "0.5";
        button.style.pointerEvents = "none";
        button.textContent = 'Sending...';
        return true;
    }
</script>
@endsection