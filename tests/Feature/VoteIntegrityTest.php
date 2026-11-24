<?php

use App\Models\Count;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;

beforeEach(function () {
    seedRoles();
    $this->first = makeCandidate(1);
    $this->second = makeCandidate(2);
    $this->voter = makeUserWithRole('Participant', 'pemilih');

    setSchedule('2026-12-01 08:00', '2026-12-01 15:00');
    $this->travelTo(Carbon::parse('2026-12-01 10:00'));
});

it('rejects a vote for a candidate that does not exist', function () {
    $this->actingAs($this->voter)
        ->post('/vote-paslon', ['candidate_id' => 9999])
        ->assertSessionHasErrors('candidate_id');

    expect(Count::count())->toBe(0);
    expect($this->voter->fresh()->status)->toBe('not voted');
});

it('rejects a vote without a candidate', function () {
    $this->actingAs($this->voter)
        ->post('/vote-paslon', [])
        ->assertSessionHasErrors('candidate_id');

    expect(Count::count())->toBe(0);
    expect($this->voter->fresh()->status)->toBe('not voted');
});

it('keeps only the first vote when a participant votes twice', function () {
    $this->actingAs($this->voter)->post('/vote-paslon', ['candidate_id' => $this->first->id]);
    $this->actingAs($this->voter)->post('/vote-paslon', ['candidate_id' => $this->second->id]);

    expect(Count::where('user_id', $this->voter->id)->count())->toBe(1);
    expect(Count::where('user_id', $this->voter->id)->value('candidate_id'))->toBe($this->first->id);
});

it('does not let the database store two votes for the same user', function () {
    Count::create(['candidate_id' => $this->first->id, 'user_id' => $this->voter->id]);

    expect(fn () => Count::create(['candidate_id' => $this->second->id, 'user_id' => $this->voter->id]))
        ->toThrow(QueryException::class);
});

it('treats a vote that loses the race to a concurrent vote as already voted', function () {
    // Simulasi: request lain sudah menyimpan suara, tapi status user belum sempat diperbarui.
    Count::create(['candidate_id' => $this->first->id, 'user_id' => $this->voter->id]);

    $this->actingAs($this->voter)
        ->post('/vote-paslon', ['candidate_id' => $this->second->id])
        ->assertRedirect('/vote');

    expect(Count::where('user_id', $this->voter->id)->count())->toBe(1);
    expect(Count::where('user_id', $this->voter->id)->value('candidate_id'))->toBe($this->first->id);
    expect($this->voter->fresh()->status)->toBe('voted');
});
