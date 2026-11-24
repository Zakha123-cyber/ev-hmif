<?php

use App\Models\Count;
use Illuminate\Support\Carbon;

beforeEach(function () {
    seedRoles();
    $first = makeCandidate(1);
    $second = makeCandidate(2);

    // Paslon 1 = 3 suara, Paslon 2 = 1 suara
    foreach ([$first, $first, $first, $second] as $i => $candidate) {
        $voter = makeUserWithRole('Participant', "voter{$i}");
        Count::create(['candidate_id' => $candidate->id, 'user_id' => $voter->id]);
    }
});

it('hides vote results on the home page when no schedule has been set', function () {
    $this->get('/')
        ->assertOk()
        ->assertViewHas('results', null)
        ->assertDontSee('3 suara');
});

it('hides vote results on the home page before voting opens', function () {
    setSchedule('2026-12-01 08:00', '2026-12-01 15:00');
    $this->travelTo(Carbon::parse('2026-12-01 07:00'));

    $this->get('/')
        ->assertOk()
        ->assertViewHas('results', null)
        ->assertDontSee('3 suara');
});

it('hides vote results on the home page while voting is open', function () {
    setSchedule('2026-12-01 08:00', '2026-12-01 15:00');
    $this->travelTo(Carbon::parse('2026-12-01 15:00'));

    $this->get('/')
        ->assertOk()
        ->assertViewHas('results', null)
        ->assertDontSee('3 suara');
});

it('shows vote results on the home page after voting closes', function () {
    setSchedule('2026-12-01 08:00', '2026-12-01 15:00');
    $this->travelTo(Carbon::parse('2026-12-01 15:00:01'));

    $this->get('/')
        ->assertOk()
        ->assertSee('3 suara');
});
