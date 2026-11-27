@extends('admin.layouts.app')

@section('title', 'Ubah peserta')

@section('content')
    <section class="a-card">
        <div class="a-card__body">
            @include('admin.pages.participant._form', [
                'participant' => $getDetailParticipant,
                'action' => url('/back-admin-logged/participant/update/' . $getDetailParticipant->id),
                'method' => 'PUT',
            ])
        </div>
    </section>
@endsection
