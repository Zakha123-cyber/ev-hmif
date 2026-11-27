@extends('admin.layouts.app')

@section('title', 'Tambah paslon')

@section('content')
    <section class="a-card">
        <div class="a-card__body">
            @include('admin.pages.candidate._form', [
                'candidate' => null,
                'action' => url('/back-admin-logged/candidate/store'),
                'method' => 'POST',
            ])
        </div>
    </section>
@endsection
