@extends('admin.layouts.app')
@section('extraCSS')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/css/selectize.bootstrap3.min.css" integrity="sha256-ze/OEYGcFbPRmvCnrSeKbRTtjG4vGLHXgOqsyLFTRjg=" crossorigin="anonymous" />
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
    <div class="card mb-8">
        <div class="card-header flex flex-row justify-between">
            <h1 class="h6">Verifikasi Participant</h1>
        </div>
        <div class="card-body">
            <form method="POST" action="{{url('/back-admin-logged/participant-verification/verified')}}">
                @csrf
                <div>
                    <label class="text-gray-700 ml-1"> NIM Participant : </label>
                    <select name="nim" id="selectSearch" class="form-input w-full block border-teal-500 rounded mt-1 pt-3 pb-2 px-3 border-2 @error('nim') border-red-500 @enderror focus:outline-none focus:border-teal-500" >
                        <option value="">Cari NIM ...</option>
                        @foreach ($getParticipant as $item)
                            <option value="{{$item->id}}">{{$item->nim}} - {{$item->participant_name}}</option>
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
                        <input type="text" name="username" class="form-input w-full block rounded mt-1 p-3 border-2 @error('username') border-red-500 @enderror focus:outline-none focus:border-teal-500" placeholder="Username" value="{{old('username', $getRandUsername)}}" readonly>
                        @error('username')
                        <span class="pl-1 text-xs text-red-600 text-bold">
                            {{$message}}
                        </span>
                        @enderror
                    </div>
                    <div class="mt-3">
                        <label class="text-gray-700 ml-1"> Password : </label>
                        <input type="password" name="password" class="form-input w-full block rounded mt-1 p-3 border-2 @error('password') border-red-500 @enderror focus:outline-none focus:border-teal-500" placeholder="Password" value="{{old('password', $getRandPassword)}}" readonly>
                        @error('password')
                        <span class="pl-1 text-xs text-red-600 text-bold">
                            {{$message}}
                        </span>
                        @enderror
                    </div>
                </div>
                <div class="mt-3">
                    <label class="text-gray-700 ml-1"> Nomor Whatsapp : </label>
                    <input type="text" name="whatsapp_number" class="form-input w-full block rounded mt-1 p-3 border-2 @error('whatsapp_number') border-red-500 @enderror focus:outline-none focus:border-teal-500" placeholder="Nomor Whatsapp" value="62">
                    @error('whatsapp_number')
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
    <div class="card">
        <div class="card-header flex flex-row justify-between">
            <h1 class="h6">List Participant (Verified)</h1>
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
                        @foreach ($getParticpantVerified as $item)  
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
                                <form action="{{url('/back-admin-logged/participant-verification/edit',$item->id)}}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="bg-teal-500 h-10 w-10 rounded hover:bg-teal-600"><i class="fa fa-pencil text-white"></i></button>
                                </form><br><br>
                                <form action="{{url('/back-admin-logged/participant-verification/info',$item->id)}}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="bg-yellow-600 h-10 w-10 rounded hover:bg-yellow-700"><i class="fa fa-clipboard text-white"></i></button>
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
</script>

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
@endsection