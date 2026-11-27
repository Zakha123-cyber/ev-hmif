<?php

use App\Models\Count;

beforeEach(function () {
    seedRoles();
    $this->admin = makeUserWithRole('Admin', 'admin1');
    $this->first = makeCandidate(1);
    $this->second = makeCandidate(2);

    $voters = collect(['v1', 'v2', 'v3'])->map(fn ($u) => makeUserWithRole('Participant', $u));
    $voters[0]->update(['status' => 'voted']);
    Count::create(['user_id' => $voters[0]->id, 'candidate_id' => $this->first->id]);
});

it('shows real participation numbers without the old double subtraction', function () {
    $this->actingAs($this->admin)->get('/back-admin-logged/dashboard')
        ->assertOk()
        ->assertSee('1 dari 3')
        ->assertViewHas('countVoted', 1)
        ->assertViewHas('countNotVoted', 2);
});

it('drops the fake website-traffic cards', function () {
    $this->actingAs($this->admin)->get('/back-admin-logged/dashboard')
        ->assertDontSee('num-4', false)
        ->assertDontSee('Halaman Dilihat')
        ->assertDontSee('Pengunjung Baru');
});

it('greets the admin by name', function () {
    $this->actingAs($this->admin)->get('/back-admin-logged/dashboard')->assertSee('Halo, admin1');
});

it('lists every pair on the results page, including one without votes', function () {
    $this->actingAs($this->admin)->get('/back-admin-logged/live-count')
        ->assertOk()
        ->assertSeeInOrder(['Paslon 1', '1 suara', 'Paslon 2', '0 suara'])
        ->assertViewHas('countParticipant');
});
