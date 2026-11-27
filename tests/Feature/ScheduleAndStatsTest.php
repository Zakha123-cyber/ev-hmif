<?php

use App\Models\Count;
use App\Models\Participant;
use Illuminate\Support\Carbon;

beforeEach(function () {
    seedRoles();
});

// --- Halaman login mengikuti jadwal admin, bukan jam 15:30 ---

it('does not say the election is over on the login page in the afternoon before the election', function () {
    setSchedule('2026-12-02 08:00', '2026-12-02 15:00');
    $this->travelTo(Carbon::parse('2026-12-01 16:00'));

    $this->get('/login')->assertOk()->assertDontSee('Pemilihan sudah selesai');
});

it('says the election is over on the login page once voting has closed', function () {
    setSchedule('2026-12-01 08:00', '2026-12-01 12:00');
    $this->travelTo(Carbon::parse('2026-12-01 13:00'));

    $this->get('/login')->assertOk()->assertSee('Pemilihan sudah selesai');
});

it('shows the normal login title while voting is open', function () {
    setSchedule('2026-12-01 08:00', '2026-12-01 17:00');
    $this->travelTo(Carbon::parse('2026-12-01 16:00'));

    $this->get('/login')->assertOk()->assertSee('Masuk untuk memilih')->assertSee('Sedang berlangsung')->assertDontSee('Pemilihan sudah selesai');
});

// --- Statistik hanya menghitung peserta, bukan admin ---

beforeEach(function () {
    $this->admin = makeUserWithRole('Admin', 'admin1');
    $candidate = makeCandidate(1);

    $voted = makeUserWithRole('Participant', 'sudah');
    Count::create(['candidate_id' => $candidate->id, 'user_id' => $voted->id]);
    $voted->update(['status' => 'voted']);

    makeUserWithRole('Participant', 'belum1');
    makeUserWithRole('Participant', 'belum2');
    // Peserta tanpa akun
    Participant::create(['participant_name' => 'Tanpa Akun', 'nim' => '2219999', 'status' => 'not verified']);
});

it('counts only participants in the dashboard statistics', function () {
    $this->actingAs($this->admin)
        ->get('/back-admin-logged/dashboard')
        ->assertOk()
        ->assertViewHas('countVoted', 1)
        ->assertViewHas('countNotVoted', 2)
        ->assertViewHas('countParticipant', 4);
});

it('counts only participants in the live count statistics', function () {
    $this->actingAs($this->admin)
        ->get('/back-admin-logged/live-count')
        ->assertOk()
        ->assertViewHas('countVoted', 1)
        ->assertViewHas('countNotVoted', 2)
        ->assertViewHas('countParticipant', 4);
});
