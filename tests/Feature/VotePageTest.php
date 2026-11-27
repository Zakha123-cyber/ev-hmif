<?php

use Illuminate\Support\Carbon;

beforeEach(function () {
    seedRoles();
    $this->first = makeCandidate(1);
    $this->second = makeCandidate(2);
    $this->voter = makeUserWithRole('Participant', 'voter1');
});

// --- Belum dibuka ---

it('shows the opening date, a countdown and the candidates before voting opens', function () {
    setSchedule('2026-12-01 08:00', '2026-12-01 15:00');
    $this->travelTo(Carbon::parse('2026-11-30 10:00'));

    $response = $this->actingAs($this->voter)->get('/vote')->assertOk()->assertViewIs('vote_prepare');

    $response->assertSee('Pemilihan belum dibuka')
        ->assertSee('Selasa, 1 Desember 2026')
        ->assertSee('data-countdown=', false)
        ->assertSee('Kenali paslon');
    expect(substr_count($response->getContent(), 'data-envelope'))->toBe(2);
    expect(substr_count($response->getContent(), '<dialog id="surat-'))->toBe(2);
});

it('says the schedule has not been announced when none is set', function () {
    $this->actingAs($this->voter)->get('/vote')
        ->assertOk()
        ->assertSee('Jadwal belum diumumkan')
        ->assertDontSee('data-countdown', false);
});

// --- Kerangka bersama ---

it('uses the shared layout instead of Bootstrap and SweetAlert', function () {
    $response = $this->actingAs($this->voter)->get('/vote');

    $response->assertDontSee('site-header--solid', false)
        ->assertSee('data-site-header', false)
        ->assertSee('action="' . route('logout') . '"', false)
        ->assertSee('Keluar')
        ->assertSee('2025/2026')
        ->assertSee('Farda')
        ->assertDontSee('2024/2025')
        ->assertDontSee('bootstrap', false)
        ->assertDontSee('sweetalert', false)
        ->assertDontSee('jquery', false);
});

// --- Tanda terima ---

// actingAs menyimpan objek user lama; setelah POST, pakai data terbaru dari DB
// seperti yang terjadi pada request sungguhan.
function castVote($test, $candidate)
{
    $response = $test->actingAs($test->voter)->post('/vote-paslon', ['candidate_id' => $candidate->id]);
    $test->actingAs($test->voter->fresh());

    return $response;
}

function openVoting(): void
{
    setSchedule('2026-12-01 08:00', '2026-12-01 15:00');
    test()->travelTo(Carbon::parse('2026-12-01 10:00'));
}

it('flags a fresh vote and shows a receipt with the time but not the choice', function () {
    openVoting();

    castVote($this, $this->first)
        ->assertRedirect('/vote')
        ->assertSessionHas('just_voted', true);

    $this->get('/vote')
        ->assertOk()
        ->assertSee('Suaramu sudah tercatat')
        ->assertSee('Selasa, 1 Desember 2026 pukul 10.00 WIB')
        ->assertSee('data-just-voted', false)
        ->assertDontSee('Ketua 1')
        ->assertDontSee('Ketua 2')
        ->assertDontSee('Paslon 1');
});

it('shows the receipt without the drop animation on later visits', function () {
    openVoting();
    castVote($this, $this->first);
    $this->get('/vote');

    $this->get('/vote')->assertSee('Suaramu sudah tercatat')->assertDontSee('data-just-voted', false);
});

it('does not flag a second vote attempt as a fresh vote', function () {
    openVoting();
    castVote($this, $this->first);

    $this->post('/vote-paslon', ['candidate_id' => $this->second->id])->assertSessionMissing('just_voted');
});

it('shows the receipt without a time when the vote row is missing', function () {
    openVoting();
    $this->voter->update(['status' => 'voted']);

    $this->actingAs($this->voter)->get('/vote')
        ->assertOk()
        ->assertSee('Suaramu sudah tercatat')
        ->assertDontSee('receipt__time', false);
});

it('tells a voter when results will be announced while voting is still open', function () {
    openVoting();
    castVote($this, $this->first);

    $this->get('/vote')->assertSee('Hasil diumumkan setelah pemilihan ditutup Selasa, 1 Desember 2026 pukul 15.00 WIB.');
});

it('links a voter to the results once voting has closed', function () {
    openVoting();
    castVote($this, $this->first);
    $this->travelTo(Carbon::parse('2026-12-01 16:00'));

    $this->get('/vote')
        ->assertViewIs('vote_closed')
        ->assertSee('Suaramu sudah tercatat')
        ->assertSee('Lihat hasil')
        ->assertSee('href="' . url('/') . '#hasil"', false);
});

it('tells someone who did not vote that voting has closed', function () {
    setSchedule('2026-12-01 08:00', '2026-12-01 15:00');
    $this->travelTo(Carbon::parse('2026-12-01 16:00'));

    $this->actingAs($this->voter)->get('/vote')
        ->assertSee('Pemilihan sudah ditutup')
        ->assertSee('Kamu tidak memberikan suara pada pemilihan ini.')
        ->assertSee('Lihat hasil')
        ->assertDontSee('Suaramu sudah tercatat');
});

// --- Surat suara ---

it('renders one ballot form with a radio per candidate', function () {
    openVoting();

    $html = $this->actingAs($this->voter)->get('/vote')->assertOk()
        ->assertSee('data-ballot', false)
        ->assertSee('action="' . url('/vote-paslon') . '"', false)
        ->assertSee('value="' . $this->first->id . '"', false)
        ->assertSee('value="' . $this->second->id . '"', false)
        ->assertSee('Kirim suara')
        ->assertSee('Halo, voter1.')
        ->getContent();

    expect(substr_count($html, 'name="candidate_id"'))->toBe(2);
});

it('offers each candidate letter from the ballot without the button selecting a candidate', function () {
    openVoting();

    $html = $this->actingAs($this->voter)->get('/vote')
        ->assertSee('Visi &amp; misi', false)
        ->assertSee('data-letter-open="surat-' . $this->first->id . '"', false)
        ->assertSee('<dialog id="surat-' . $this->first->id . '"', false)
        ->getContent();

    $dom = new DOMDocument();
    @$dom->loadHTML($html);
    $buttonsInLabels = (new DOMXPath($dom))->query('//label//button');
    expect($buttonsInLabels->length)->toBe(0);
});

it('includes the confirmation letter', function () {
    openVoting();

    $this->actingAs($this->voter)->get('/vote')
        ->assertSee('id="konfirmasi"', false)
        ->assertSee('Ya, kirim suara')
        ->assertSee('Periksa lagi')
        ->assertSee('Setelah dikirim, pilihanmu tidak bisa diubah.');
});

it('lists candidates by their pair number', function () {
    openVoting();
    $this->first->forceFill(['candidate_pair' => 3])->save();

    $this->actingAs($this->voter)->get('/vote')->assertSeeInOrder(['Paslon 2', 'Paslon 3']);
});

it('shows a placeholder when a poster file is missing', function () {
    openVoting();

    $this->actingAs($this->voter)->get('/vote')->assertSee('Foto pasangan calon belum tersedia');
});

it('shows a validation error from the server above the send button', function () {
    openVoting();

    $this->actingAs($this->voter)
        ->withSession(['errors' => (new \Illuminate\Support\ViewErrorBag())->put('default', new \Illuminate\Support\MessageBag(['candidate_id' => 'Kandidat tidak valid.']))])
        ->get('/vote')
        ->assertSee('Kandidat tidak valid.');
});

it('says the candidates are not announced yet instead of showing an empty ballot', function () {
    openVoting();
    \App\Models\Candidate::query()->delete();

    $this->actingAs($this->voter)->get('/vote')
        ->assertSee('Daftar pasangan calon belum diumumkan.')
        ->assertDontSee('data-ballot', false);
});

// --- Kerahasiaan di komputer bersama ---

it('is never cached, so Back cannot bring back a filled-in ballot', function () {
    openVoting();

    $response = $this->actingAs($this->voter)->get('/vote');

    expect($response->headers->get('Cache-Control'))->toContain('no-store');
});

it('does not let the browser restore a previous choice on the ballot', function () {
    openVoting();

    $this->actingAs($this->voter)->get('/vote')->assertSee('data-ballot method="POST" action="' . url('/vote-paslon') . '" autocomplete="off"', false);
});
