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

    $response->assertSee('site-header--solid', false)
        ->assertSee('action="' . route('logout') . '"', false)
        ->assertSee('Keluar')
        ->assertSee('2025/2026')
        ->assertSee('Farda')
        ->assertDontSee('2024/2025')
        ->assertDontSee('bootstrap', false)
        ->assertDontSee('sweetalert', false)
        ->assertDontSee('jquery', false);
});
