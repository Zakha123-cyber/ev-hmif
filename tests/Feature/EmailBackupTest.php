<?php

use App\Mail\BroadcastMail;
use App\Models\Participant;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    seedRoles();
    $this->admin = makeUserWithRole('Admin', 'admin1');
    Mail::fake();
});

function participantForm(array $overrides = []): array
{
    return array_merge([
        'participant_name' => 'Budi',
        'nim' => '2210001',
        'whatsapp_number' => '62812',
        'status' => 'not verified',
    ], $overrides);
}

it('saves an optional backup email when adding a participant', function () {
    $this->actingAs($this->admin)
        ->post('/back-admin-logged/participant/store', participantForm(['email_backup' => 'budi@gmail.com']))
        ->assertSessionHasNoErrors();

    expect(Participant::where('nim', '2210001')->value('email_backup'))->toBe('budi@gmail.com');
});

it('saves a backup email when editing a participant', function () {
    $participant = Participant::create(participantForm());

    $this->actingAs($this->admin)
        ->put("/back-admin-logged/participant/update/{$participant->id}", participantForm(['email_backup' => 'budi@gmail.com']))
        ->assertSessionHasNoErrors();

    expect($participant->fresh()->email_backup)->toBe('budi@gmail.com');
});

it('rejects an invalid backup email', function () {
    $this->actingAs($this->admin)
        ->post('/back-admin-logged/participant/store', participantForm(['email_backup' => 'bukan-email']))
        ->assertSessionHasErrors('email_backup');
});

it('sends the account email to the backup address', function () {
    $voter = makeUserWithRole('Participant', 'pemilih');
    $participant = $voter->details;
    $participant->forceFill(['email_backup' => 'budi@gmail.com', 'username_old' => 'pemilih', 'password_old' => 'rahasia'])->save();

    $this->actingAs($this->admin)
        ->post("/back-admin-logged/participant/email-backup/{$participant->id}")
        ->assertSessionHas('status');

    Mail::assertQueued(BroadcastMail::class, fn ($mail) => $mail->hasTo('budi@gmail.com'));
});

it('reports an error when the participant has no backup email', function () {
    $voter = makeUserWithRole('Participant', 'pemilih');

    $this->actingAs($this->admin)
        ->post("/back-admin-logged/participant/email-backup/{$voter->participant_id}")
        ->assertSessionHas('error');

    Mail::assertNothingQueued();
});

it('does not send a backup email to a participant without an account', function () {
    $participant = Participant::create(participantForm(['email_backup' => 'budi@gmail.com']));

    $this->actingAs($this->admin)
        ->post("/back-admin-logged/participant/email-backup/{$participant->id}")
        ->assertSessionHas('error');

    Mail::assertNothingQueued();
});
