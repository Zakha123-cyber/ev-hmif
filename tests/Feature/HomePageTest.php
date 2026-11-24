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
    expect(file_exists(public_path('frontend/3d/cogito.glb')))->toBeFalse();

    $this->get('/')->assertViewHas('mascotUrl', null);
});
