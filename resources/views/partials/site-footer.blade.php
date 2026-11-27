<footer class="site-footer">
    <div class="container site-footer__inner">
        <div class="site-footer__org">
            <img src="{{ asset('frontend/images/home/logo-hmif-320.webp') }}" width="316" height="320" loading="lazy"
                alt="Logo Himpunan Mahasiswa Informatika">
            <p>Himpunan Mahasiswa Informatika<br>Universitas Jember</p>
        </div>
        @php $contacts = \App\Models\ContactPerson::ordered()->get(); @endphp
        @if ($contacts->isNotEmpty())
            <div class="site-footer__help">
                <h2 class="site-footer__title">Kendala login atau belum menerima email akun?</h2>
                <p>Hubungi panitia lewat WhatsApp:</p>
                <ul>
                    @foreach ($contacts as $contact)
                        <li><a href="{{ $contact->waLink() }}">{{ $contact->name }}, {{ $contact->displayPhone() }}</a></li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
    <p class="container site-footer__copy">&copy; {{ now()->year }} Election Vote HMIF</p>
</footer>
