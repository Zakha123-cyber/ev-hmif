<ol class="envelopes envelopes--{{ min($candidates->count(), 3) }}">
    @foreach ($candidates as $candidate)
        @php($letterId = 'surat-' . $candidate->id)
        <li>
            <button type="button" class="envelope" data-envelope commandfor="{{ $letterId }}" command="show-modal">
                <span class="envelope__flap" aria-hidden="true">
                    <img class="envelope__logo" src="{{ asset('frontend/images/home/logo-hmif-320.webp') }}" width="316" height="320" alt="" loading="lazy">
                </span>
                <span class="hex-number envelope__seal" aria-hidden="true">{{ $candidate->candidate_pair }}</span>
                <span class="envelope__label">Paslon {{ $candidate->candidate_pair }}</span>
                <span class="envelope__hint">Buka amplop</span>
            </button>

            @include('partials.candidate-letter', ['candidate' => $candidate])
        </li>
    @endforeach
</ol>
