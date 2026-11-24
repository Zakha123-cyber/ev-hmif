<?php

use App\Models\Count;
use App\Models\ElectionSetting;
use Illuminate\Support\Carbon;

beforeEach(function () {
    seedRoles();
    $this->candidate = makeCandidate(1);
});

// --- POST /vote-paslon harus menolak suara di luar jadwal ---

it('rejects a vote when no schedule has been set', function () {
    $voter = makeUserWithRole('Participant', 'voter1');
    $this->travelTo(Carbon::parse('2026-12-01 10:00'));

    $this->actingAs($voter)
        ->post('/vote-paslon', ['candidate_id' => $this->candidate->id])
        ->assertRedirect('/vote');

    expect(Count::count())->toBe(0);
    expect($voter->fresh()->status)->toBe('not voted');
});

it('rejects a vote before voting opens', function () {
    setSchedule('2026-12-01 08:00', '2026-12-01 15:00');
    $voter = makeUserWithRole('Participant', 'voter1');
    $this->travelTo(Carbon::parse('2026-12-01 07:59'));

    $this->actingAs($voter)
        ->post('/vote-paslon', ['candidate_id' => $this->candidate->id])
        ->assertRedirect('/vote');

    expect(Count::count())->toBe(0);
    expect($voter->fresh()->status)->toBe('not voted');
});

it('rejects a vote after voting closes', function () {
    setSchedule('2026-12-01 08:00', '2026-12-01 15:00');
    $voter = makeUserWithRole('Participant', 'voter1');
    $this->travelTo(Carbon::parse('2026-12-01 15:00:01'));

    $this->actingAs($voter)
        ->post('/vote-paslon', ['candidate_id' => $this->candidate->id])
        ->assertRedirect('/vote');

    expect(Count::count())->toBe(0);
    expect($voter->fresh()->status)->toBe('not voted');
});

it('accepts a vote while voting is open', function () {
    setSchedule('2026-12-01 08:00', '2026-12-01 15:00');
    $voter = makeUserWithRole('Participant', 'voter1');
    $this->travelTo(Carbon::parse('2026-12-01 10:00'));

    $this->actingAs($voter)
        ->post('/vote-paslon', ['candidate_id' => $this->candidate->id])
        ->assertRedirect('/vote');

    expect(Count::where('user_id', $voter->id)->count())->toBe(1);
    expect($voter->fresh()->status)->toBe('voted');
});

// --- GET /vote menampilkan halaman sesuai jadwal ---

it('shows the "not open yet" page when no schedule has been set', function () {
    $voter = makeUserWithRole('Participant', 'voter1');

    $this->actingAs($voter)->get('/vote')->assertOk()->assertViewIs('vote_prepare');
});

it('shows the "not open yet" page before voting opens', function () {
    setSchedule('2026-12-01 08:00', '2026-12-01 15:00');
    $voter = makeUserWithRole('Participant', 'voter1');
    $this->travelTo(Carbon::parse('2026-12-01 07:00'));

    $this->actingAs($voter)->get('/vote')->assertOk()->assertViewIs('vote_prepare');
});

it('shows the ballot while voting is open', function () {
    setSchedule('2026-12-01 08:00', '2026-12-01 15:00');
    $voter = makeUserWithRole('Participant', 'voter1');
    $this->travelTo(Carbon::parse('2026-12-01 10:00'));

    $this->actingAs($voter)->get('/vote')->assertOk()->assertViewIs('vote_page');
});

it('shows the closed page after voting closes', function () {
    setSchedule('2026-12-01 08:00', '2026-12-01 15:00');
    $voter = makeUserWithRole('Participant', 'voter1');
    $this->travelTo(Carbon::parse('2026-12-01 16:00'));

    $this->actingAs($voter)->get('/vote')->assertOk()->assertViewIs('vote_closed');
});

// --- Admin mengatur jadwal ---

it('lets an admin view the schedule settings page', function () {
    $admin = makeUserWithRole('Admin', 'admin1');

    $this->actingAs($admin)
        ->get('/back-admin-logged/election-schedule')
        ->assertOk()
        ->assertViewIs('admin.pages.election-schedule.edit');
});

it('lets an admin save the voting schedule', function () {
    $admin = makeUserWithRole('Admin', 'admin1');

    $this->actingAs($admin)
        ->put('/back-admin-logged/election-schedule', [
            'vote_start' => '2026-12-01T08:00',
            'vote_end' => '2026-12-01T15:00',
        ])
        ->assertRedirect('/back-admin-logged/election-schedule')
        ->assertSessionHasNoErrors();

    $setting = ElectionSetting::current();
    expect($setting->vote_start->format('Y-m-d H:i'))->toBe('2026-12-01 08:00');
    expect($setting->vote_end->format('Y-m-d H:i'))->toBe('2026-12-01 15:00');
});

it('requires the closing time to be after the opening time', function () {
    $admin = makeUserWithRole('Admin', 'admin1');

    $this->actingAs($admin)
        ->put('/back-admin-logged/election-schedule', [
            'vote_start' => '2026-12-01T15:00',
            'vote_end' => '2026-12-01T08:00',
        ])
        ->assertSessionHasErrors('vote_end');

    expect(ElectionSetting::current()->vote_start)->toBeNull();
});

it('forbids participants from changing the schedule', function () {
    $voter = makeUserWithRole('Participant', 'voter1');

    $this->actingAs($voter)
        ->put('/back-admin-logged/election-schedule', [
            'vote_start' => '2026-12-01T08:00',
            'vote_end' => '2026-12-01T15:00',
        ])
        ->assertForbidden();
});
