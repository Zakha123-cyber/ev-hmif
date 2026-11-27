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

it('sends the account email to the campus address and also the backup address when filled', function () {
    $voter = makeUserWithRole('Participant', 'pemilih');
    $participant = $voter->details;
    $participant->forceFill(['nim' => '233061', 'email_backup' => 'budi@gmail.com', 'username_old' => 'pemilih', 'password_old' => 'rahasia'])->save();

    $this->actingAs($this->admin)
        ->post("/back-admin-logged/participant/email-backup/{$participant->id}")
        ->assertSessionHas('status');

    Mail::assertQueued(BroadcastMail::class, fn ($mail) => $mail->hasTo('232410103061@mail.unej.ac.id'));
    Mail::assertQueued(BroadcastMail::class, fn ($mail) => $mail->hasTo('budi@gmail.com'));
});

it('resends to the campus address when there is no backup email', function () {
    $voter = makeUserWithRole('Participant', 'pemilih');
    $voter->details->forceFill(['nim' => '233061'])->save();

    $this->actingAs($this->admin)
        ->post("/back-admin-logged/participant/email-backup/{$voter->participant_id}")
        ->assertSessionHas('status')
        ->assertSessionMissing('error');

    Mail::assertQueued(BroadcastMail::class, 1);
    Mail::assertQueued(BroadcastMail::class, fn ($mail) => $mail->hasTo('232410103061@mail.unej.ac.id'));
});

it('creates the account first for an imported participant, then emails it', function () {
    $participant = Participant::create(participantForm(['nim' => '233061']));

    $this->actingAs($this->admin)
        ->post("/back-admin-logged/participant/email-backup/{$participant->id}")
        ->assertSessionHas('status');

    $participant->refresh();
    expect($participant->status)->toBe('verified');
    expect($participant->user)->not->toBeNull();
    expect($participant->user->hasRole('Participant'))->toBeTrue();
    expect(\Illuminate\Support\Facades\Hash::check($participant->password_old, $participant->user->password))->toBeTrue();
    Mail::assertQueued(BroadcastMail::class, fn ($mail) => $mail->hasTo('232410103061@mail.unej.ac.id'));
});

it('labels the row action by whether the participant already has an account', function () {
    Participant::create(participantForm(['nim' => '233061']));
    makeUserWithRole('Participant', 'pemilih');

    $this->actingAs($this->admin)->get('/back-admin-logged/participant')
        ->assertSee('Buat akun &amp; kirim<span class="visually-hidden"> Budi</span>', false)
        ->assertSee('Kirim ulang email<span class="visually-hidden"> pemilih</span>', false);
});

// --- Pilihan tujuan: email kampus atau email cadangan ---

it('sends only to the campus address when that is chosen', function () {
    $voter = makeUserWithRole('Participant', 'pemilih');
    $voter->details->forceFill(['nim' => '233061', 'email_backup' => 'budi@gmail.com'])->save();

    $this->actingAs($this->admin)
        ->post("/back-admin-logged/participant/email-backup/{$voter->participant_id}", ['target' => 'campus'])
        ->assertSessionHas('status', fn ($s) => str_contains($s, '232410103061@mail.unej.ac.id'));

    Mail::assertQueued(BroadcastMail::class, 1);
    Mail::assertQueued(BroadcastMail::class, fn ($mail) => $mail->hasTo('232410103061@mail.unej.ac.id'));
});

it('sends only to the backup address when that is chosen', function () {
    $voter = makeUserWithRole('Participant', 'pemilih');
    $voter->details->forceFill(['nim' => '233061', 'email_backup' => 'budi@gmail.com'])->save();

    $this->actingAs($this->admin)
        ->post("/back-admin-logged/participant/email-backup/{$voter->participant_id}", ['target' => 'backup', 'email_backup' => 'budi@gmail.com'])
        ->assertSessionHas('status', fn ($s) => str_contains($s, 'budi@gmail.com'));

    Mail::assertQueued(BroadcastMail::class, 1);
    Mail::assertQueued(BroadcastMail::class, fn ($mail) => $mail->hasTo('budi@gmail.com'));
});

it('saves a newly typed backup address and sends to it', function () {
    $voter = makeUserWithRole('Participant', 'pemilih');

    $this->actingAs($this->admin)
        ->post("/back-admin-logged/participant/email-backup/{$voter->participant_id}", ['target' => 'backup', 'email_backup' => 'baru@gmail.com'])
        ->assertSessionHas('status');

    expect($voter->details->fresh()->email_backup)->toBe('baru@gmail.com');
    Mail::assertQueued(BroadcastMail::class, fn ($mail) => $mail->hasTo('baru@gmail.com'));
});

it('asks for a valid backup address before sending to it', function (string $email) {
    $voter = makeUserWithRole('Participant', 'pemilih');

    $this->actingAs($this->admin)
        ->post("/back-admin-logged/participant/email-backup/{$voter->participant_id}", ['target' => 'backup', 'email_backup' => $email])
        ->assertSessionHasErrors('email_backup');

    Mail::assertNothingQueued();
})->with(['empty' => '', 'invalid' => 'bukan-email']);

it('offers the destination choice in a dialog on the participant list', function () {
    makeUserWithRole('Participant', 'pemilih')->details->forceFill(['nim' => '233061', 'email_backup' => 'budi@gmail.com'])->save();

    $this->actingAs($this->admin)->get('/back-admin-logged/participant')
        ->assertSee('id="kirim-email"', false)
        ->assertSee('name="target"', false)
        ->assertSee('data-campus-email="232410103061@mail.unej.ac.id"', false)
        ->assertSee('data-backup-email="budi@gmail.com"', false);
});

it('reopens the dialog for the same participant with the backup email error', function () {
    $voter = makeUserWithRole('Participant', 'pemilih');

    $this->actingAs($this->admin)->from('/back-admin-logged/participant')
        ->post("/back-admin-logged/participant/email-backup/{$voter->participant_id}", ['participant_id' => $voter->participant_id, 'target' => 'backup', 'email_backup' => '']);

    $this->get('/back-admin-logged/participant')
        ->assertSee('data-open-on-load', false)
        ->assertSee('Isi email cadangan dulu.')
        ->assertSee('Kirim ulang email ke pemilih');
});
