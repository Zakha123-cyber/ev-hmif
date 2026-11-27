@extends('admin.layouts.app')

@section('title', 'Ubah Paslon ' . $getDetailCandidate->candidate_pair)

@section('content')
    <section class="a-card">
        <div class="a-card__body">
            @include('admin.pages.candidate._form', [
                'candidate' => $getDetailCandidate,
                'action' => url('/back-admin-logged/candidate/update/' . $getDetailCandidate->id),
                'method' => 'PUT',
            ])
        </div>
    </section>
@endsection
