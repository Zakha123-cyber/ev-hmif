@extends('admin.layouts.app')

@section('title', 'Ubah kontak panitia')

@section('content')
    <section class="a-card">
        <div class="a-card__body">
            <form class="a-form" method="POST" action="{{ url('/back-admin-logged/contact-person/' . $contact->id) }}">
                @csrf
                @method('PUT')
                @include('admin.pages.contact-person._fields', ['contact' => $contact])
                <div class="a-form__foot">
                    <button type="submit" class="a-btn a-btn--accent">Simpan kontak</button>
                    <a class="a-btn a-btn--ghost" href="{{ url('/back-admin-logged/contact-person') }}">Batal</a>
                </div>
            </form>
        </div>
    </section>
@endsection
