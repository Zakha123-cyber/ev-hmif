{{-- Perolehan suara per paslon (bilah CSS). $results dari ElectionStats::results(). --}}
@if ($results->isEmpty())
    <p class="a-empty">Belum ada paslon. <a href="{{ url('/back-admin-logged/candidate/add') }}">Tambah paslon</a></p>
@else
    <ol class="a-tally">
        @foreach ($results as $row)
            <li class="a-tally__row">
                <span class="hex-number a-tally__seal" aria-hidden="true">{{ $row['candidate']->candidate_pair }}</span>
                <div class="a-tally__line">
                    <span class="a-tally__name">Paslon {{ $row['candidate']->candidate_pair }}: {{ $row['candidate']->leader_name }} &amp; {{ $row['candidate']->vice_leader_name }}</span>
                    <span class="a-tally__count"><strong>{{ $row['votes'] }} suara</strong> ({{ $row['percent'] }}%)</span>
                </div>
                <div class="a-bar" aria-hidden="true"><span style="width: {{ $row['percent'] }}%"></span></div>
            </li>
        @endforeach
    </ol>
@endif
