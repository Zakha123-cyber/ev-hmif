<?php

use App\Models\Count;
use Illuminate\Support\Carbon;

beforeEach(function () {
    seedRoles();
});

function voteFor($candidate, int $times): void
{
    static $voterNumber = 0;
    for ($i = 0; $i < $times; $i++) {
        $voter = makeUserWithRole('Participant', 'pemilih' . (++$voterNumber));
        Count::create(['candidate_id' => $candidate->id, 'user_id' => $voter->id]);
    }
}

// --- View model ---

it('reports each schedule state to the homepage', function (?array $schedule, string $now, string $expected) {
    if ($schedule) {
        setSchedule(...$schedule);
    }
    $this->travelTo(Carbon::parse($now));

    $this->get('/')->assertOk()->assertViewHas('status', $expected);
})->with([
    'not set' => [null, '2026-12-01 10:00', 'not_set'],
    'upcoming' => [['2026-12-01 08:00', '2026-12-01 15:00'], '2026-11-30 08:00', 'upcoming'],
    'open' => [['2026-12-01 08:00', '2026-12-01 15:00'], '2026-12-01 10:00', 'open'],
    'closed' => [['2026-12-01 08:00', '2026-12-01 15:00'], '2026-12-01 16:00', 'closed'],
]);

it('lists candidates in ballot number order', function () {
    makeCandidate(2);
    makeCandidate(1);

    $this->get('/')->assertViewHas('candidates', fn ($candidates) => $candidates->pluck('candidate_pair')->all() === [1, 2]);
});

it('computes vote totals and percentages once voting has closed', function () {
    $first = makeCandidate(1);
    $second = makeCandidate(2);
    voteFor($first, 3);
    voteFor($second, 1);
    setSchedule('2026-12-01 08:00', '2026-12-01 15:00');
    $this->travelTo(Carbon::parse('2026-12-01 16:00'));

    $this->get('/')
        ->assertViewHas('totalVotes', 4)
        ->assertViewHas('results', function ($results) use ($first, $second) {
            return $results[0]['candidate']->is($first) && $results[0]['votes'] === 3 && $results[0]['percent'] === 75
                && $results[1]['candidate']->is($second) && $results[1]['votes'] === 1 && $results[1]['percent'] === 25;
        });
});

it('reports zero percent instead of dividing by zero when nobody voted', function () {
    makeCandidate(1);
    makeCandidate(2);
    setSchedule('2026-12-01 08:00', '2026-12-01 15:00');
    $this->travelTo(Carbon::parse('2026-12-01 16:00'));

    $this->get('/')
        ->assertOk()
        ->assertViewHas('totalVotes', 0)
        ->assertViewHas('results', fn ($results) => collect($results)->pluck('percent')->all() === [0, 0]);
});

it('has no mascot when the 3D model file is missing', function () {
    config(['election.mascot_path' => 'frontend/3d/__tidak-ada.glb']);

    $this->get('/')->assertViewHas('mascotUrl', null);
});

// --- Tampilan per keadaan jadwal ---

it('tells visitors the schedule has not been announced yet', function () {
    $this->get('/')->assertSee('Jadwal pemilihan belum diumumkan panitia.');
});

it('shows the opening day, hours and time left before voting opens', function () {
    setSchedule('2026-12-01 08:00', '2026-12-01 15:00');
    $this->travelTo(Carbon::parse('2026-11-30 08:00'));

    $this->get('/')
        ->assertSee('Selasa, 1 Desember 2026')
        ->assertSee('08.00')
        ->assertSee('15.00 WIB')
        ->assertSee('1 hari lagi');
});

it('invites guests to log in and vote while voting is open', function () {
    setSchedule('2026-12-01 08:00', '2026-12-01 15:00');
    $this->travelTo(Carbon::parse('2026-12-01 10:00'));

    $this->get('/')
        ->assertSee('Pemilihan sedang berlangsung')
        ->assertSee('Masuk untuk memilih')
        ->assertSee('href="' . url('/login') . '"', false);
});

it('points to the results once voting has closed', function () {
    $first = makeCandidate(1);
    voteFor($first, 3);
    voteFor(makeCandidate(2), 1);
    setSchedule('2026-12-01 08:00', '2026-12-01 15:00');
    $this->travelTo(Carbon::parse('2026-12-01 16:00'));

    $this->get('/')
        ->assertSee('Pemilihan ditutup')
        ->assertSee('href="#hasil"', false)
        ->assertSee('3 suara')
        ->assertSee('75%');
});

it('says no votes were recorded when voting closed without votes', function () {
    makeCandidate(1);
    setSchedule('2026-12-01 08:00', '2026-12-01 15:00');
    $this->travelTo(Carbon::parse('2026-12-01 16:00'));

    $this->get('/')->assertSee('Belum ada suara tercatat.');
});

it('sends a logged-in admin to their own page instead of the ballot', function () {
    $admin = makeUserWithRole('Admin', 'admin1');

    $this->actingAs($admin)->get('/')
        ->assertSee('/back-admin-logged/redirect-role', false)
        ->assertDontSee('href="' . url('/vote') . '"', false);
});

// --- Surat suara ---

it('says the candidates have not been announced when there are none', function () {
    $this->get('/')->assertSee('Daftar pasangan calon belum diumumkan.');
});

it('shows a numbered placeholder instead of a broken image when the poster file is missing', function () {
    makeCandidate(1); // poster "default.png" tidak ada di disk

    $this->get('/')
        ->assertDontSee('assets/upload/kandidat/default.png', false)
        ->assertSee('letter__placeholder', false);
});

it('shows the candidate names, roles and vision', function () {
    $candidate = makeCandidate(1);

    $this->get('/')->assertSeeInOrder(['Ketua', 'Ketua 1', 'Wakil', 'Wakil 1', 'Visi']);
});

// --- Kerangka halaman ---

it('renders the page frame without the old libraries', function () {
    config(['election.mascot_path' => 'frontend/3d/__tidak-ada.glb']);

    $this->get('/')
        ->assertSee('Periode 2025/2026')
        ->assertSee('data-slider-toggle', false)
        ->assertSee('wa.me/6283823093973', false)
        ->assertSee('wa.me/6283852061105', false)
        ->assertSee('frontend/images/home/logo-election-vote.webp', false)
        ->assertSee('frontend/images/home/logo-hmif-320.webp', false)
        ->assertSee('fetchpriority="high"', false)
        ->assertDontSee('Chart.min.js', false)
        ->assertDontSee('jquery', false)
        ->assertDontSee('model-viewer', false);
});

it('shows the rotatable COGITO mascot once the 3D model file exists', function () {
    // File uji tersendiri: jangan pernah menyentuh cogito.glb milik pengguna.
    config(['election.mascot_path' => 'frontend/3d/__test-mascot.glb']);
    $path = public_path('frontend/3d/__test-mascot.glb');
    file_put_contents($path, 'glb');

    try {
        $this->get('/')
            ->assertSee('<model-viewer', false)
            ->assertSee('frontend/3d/__test-mascot.glb', false)
            ->assertSee('@google/model-viewer@3.5.0', false)
            ->assertDontSee('auto-rotate', false);
    } finally {
        unlink($path);
    }
});

it('looks for the mascot at public/frontend/3d/cogito.glb by default', function () {
    expect(config('election.mascot_path'))->toBe('frontend/3d/cogito.glb');
});

// --- Amplop dan surat ---

function envelopeMarkup(string $html, int $pair): string
{
    preg_match_all('#<button[^>]*class="envelope"[^>]*>.*?</button>#s', $html, $m);

    return collect($m[0])->first(fn ($b) => str_contains($b, "Paslon {$pair}<")) ?? '';
}

it('centers an intro that tells visitors to open the envelopes', function () {
    $this->get('/')->assertSee('Buka amplop untuk mengenal tiap pasangan calon.');
});

it('shows one sealed envelope per candidate without their names', function () {
    makeCandidate(1);
    makeCandidate(2);
    $html = $this->get('/')->getContent();

    expect(substr_count($html, 'class="envelope"'))->toBe(2);
    expect(envelopeMarkup($html, 1))->toContain('Paslon 1')->toContain('Buka amplop')
        ->not->toContain('Ketua 1')->not->toContain('Wakil 1');
    expect($html)->not->toContain('aria-label="Nomor urut');
});

it('links each envelope to its own letter dialog', function () {
    $candidate = makeCandidate(1);

    $this->get('/')
        ->assertSee('commandfor="surat-' . $candidate->id . '" command="show-modal"', false)
        ->assertSee('<dialog id="surat-' . $candidate->id . '" class="letter"', false);
});

it('writes the letter with a letterhead, title, names, vision and numbered missions', function () {
    $candidate = makeCandidate(1);
    $candidate->forceFill(['mission' => 'Membangun himpunan. Mendukung anggota.'])->save();

    $this->get('/')->assertSeeInOrder([
        'letter__kop', 'Himpunan Mahasiswa Informatika', 'Pasangan Calon Nomor Urut 1',
        'Ketua', 'Ketua 1', 'Wakil', 'Wakil 1', 'Visi', 'Misi',
        '<ol class="letter__mission">', 'Membangun himpunan.', 'Mendukung anggota.',
    ], false);
});

it('shows the candidate poster inside the letter when the file exists', function () {
    $file = '__test-poster.png';
    $path = public_path('assets/upload/kandidat/' . $file);
    file_put_contents($path, 'png');
    makeCandidate(1)->forceFill(['poster' => $file])->save();

    try {
        $this->get('/')
            ->assertSee('class="letter__poster" src="' . asset('assets/upload/kandidat/' . $file) . '"', false)
            ->assertDontSee('letter__placeholder', false);
    } finally {
        unlink($path);
    }
});

it('lets visitors close the letter with a button', function () {
    makeCandidate(1);

    $this->get('/')->assertSee('<form method="dialog">', false)->assertSee('Tutup');
});
