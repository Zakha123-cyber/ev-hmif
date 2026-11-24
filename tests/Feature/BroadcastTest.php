<?php

use App\Models\Participant;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    seedRoles();
    $this->admin = makeUserWithRole('Admin', 'admin1');
    Mail::fake();

    $this->pending = Participant::create([
        'participant_name' => 'Belum Punya Akun',
        'nim' => '2210001',
        'status' => 'not verified',
    ]);
});

it('does not run the broadcast from a GET request', function () {
    $this->actingAs($this->admin)
        ->get('/back-admin-logged/participant/broadcast')
        ->assertStatus(405);

    expect(User::where('participant_id', $this->pending->id)->exists())->toBeFalse();
});

it('creates accounts for pending participants from a POST request', function () {
    $this->actingAs($this->admin)
        ->post('/back-admin-logged/participant/broadcast')
        ->assertRedirect();

    expect(User::where('participant_id', $this->pending->id)->exists())->toBeTrue();
    expect($this->pending->fresh()->status)->toBe('verified');
});

it('refuses to start a broadcast while another one is still running', function () {
    $lock = Cache::lock('participant-broadcast', 600);
    $lock->get();

    $this->actingAs($this->admin)
        ->post('/back-admin-logged/participant/broadcast')
        ->assertSessionHas('error');

    expect(User::where('participant_id', $this->pending->id)->exists())->toBeFalse();

    $lock->release();
});

it('forbids participants from running the broadcast', function () {
    $voter = makeUserWithRole('Participant', 'pemilih');

    $this->actingAs($voter)
        ->post('/back-admin-logged/participant/broadcast')
        ->assertForbidden();

    expect(User::where('participant_id', $this->pending->id)->exists())->toBeFalse();
});
