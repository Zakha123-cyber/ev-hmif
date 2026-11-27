@extends('admin.layouts.app')

@section('title', 'Tambah peserta')

@section('content')
    <section class="a-card">
        <div class="a-card__body">
            @include('admin.pages.participant._form', [
                'participant' => null,
                'action' => url('/back-admin-logged/participant/store'),
                'method' => 'POST',
            ])
        </div>
    </section>
@endsection
