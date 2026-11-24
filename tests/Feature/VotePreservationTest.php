<?php

use App\Models\Candidate;
use App\Models\Count;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Database\QueryException;

beforeEach(function () {
    seedRoles();
    $this->admin = makeUserWithRole('Admin', 'admin1');
    $this->candidate = makeCandidate(1);
    $this->voter = makeUserWithRole('Participant', 'pemilih');
    Count::create(['candidate_id' => $this->candidate->id, 'user_id' => $this->voter->id]);
});

// --- Database menolak penghapusan yang menghilangkan suara ---

it('does not let the database delete a candidate that has votes', function () {
    expect(fn () => $this->candidate->delete())->toThrow(QueryException::class);
    expect(Count::count())->toBe(1);
});

it('does not let the database delete a user who has voted', function () {
    expect(fn () => $this->voter->delete())->toThrow(QueryException::class);
    expect(Count::count())->toBe(1);
});

// --- Hapus kandidat dari panel admin ---

it('refuses to delete a candidate that has votes from the admin panel', function () {
    $this->actingAs($this->admin)
        ->delete("/back-admin-logged/candidate/delete/{$this->candidate->id}")
        ->assertSessionHas('error');

    expect(Candidate::find($this->candidate->id))->not->toBeNull();
    expect(Count::count())->toBe(1);
});

it('still deletes a candidate without votes from the admin panel', function () {
    $empty = makeCandidate(2);

    $this->actingAs($this->admin)
        ->delete("/back-admin-logged/candidate/delete/{$empty->id}")
        ->assertSessionHasNoErrors()
        ->assertSessionMissing('error');

    expect(Candidate::find($empty->id))->toBeNull();
});

// --- Hapus peserta dari panel admin ---

it('refuses to delete a participant who has voted from the admin panel', function () {
    $participantId = $this->voter->participant_id;

    $this->actingAs($this->admin)
        ->delete("/back-admin-logged/participant/delete/{$participantId}")
        ->assertSessionHas('error');

    expect(Participant::find($participantId))->not->toBeNull();
    expect(User::find($this->voter->id))->not->toBeNull();
    expect(Count::count())->toBe(1);
});

it('still deletes a participant who has not voted from the admin panel', function () {
    $notVoted = makeUserWithRole('Participant', 'belummemilih');
    $participantId = $notVoted->participant_id;

    $this->actingAs($this->admin)
        ->delete("/back-admin-logged/participant/delete/{$participantId}")
        ->assertSessionMissing('error');

    expect(Participant::find($participantId))->toBeNull();
    expect(User::find($notVoted->id))->toBeNull();
    expect(Count::count())->toBe(1);
});
