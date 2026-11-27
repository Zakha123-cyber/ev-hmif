<?php

use App\Mail\BroadcastMail;
use App\Models\Participant;

it('builds a readable account email with the credentials, login link and current period', function () {
    $participant = (new Participant())->forceFill([
        'participant_name' => 'Joko',
        'nim' => '233061',
        'username_old' => 'Ab3dEf7h',
        'password_old' => 'Rahasia99',
    ]);

    $mail = new BroadcastMail($participant);
    $html = $mail->render();

    expect($mail->build()->subject)->toBe('Akun pemilihan Ketua HMIF 2025/2026');
    expect($html)
        ->toContain('Halo, Joko!')
        ->toContain('232410103061')
        ->toContain('Ab3dEf7h')
        ->toContain('Rahasia99')
        ->toContain(url('/login'))
        ->toContain('2025/2026')
        ->not->toContain('—');
});

it('embeds the HMIF logo inside the email instead of linking to the site', function () {
    config(['mail.default' => 'array']);
    $participant = (new Participant())->forceFill(['participant_name' => 'Joko', 'nim' => '233061', 'username_old' => 'a', 'password_old' => 'b']);

    \Illuminate\Support\Facades\Mail::to('joko@example.test')->send(new BroadcastMail($participant));

    $email = app('mail.manager')->mailer('array')->getSymfonyTransport()->messages()->last()->getOriginalMessage();
    expect($email->getHtmlBody())->toContain('src="cid:');
    expect(collect($email->getAttachments())->map(fn ($a) => $a->getMediaType() . '/' . $a->getMediaSubtype())->all())->toContain('image/png');
});

it('derives the same campus address from a short or a full NIM', function (string $nim) {
    $participant = (new Participant())->forceFill(['nim' => $nim]);

    expect($participant->campusEmail())->toBe('232410103061@mail.unej.ac.id');
    expect($participant->campusNim())->toBe('232410103061');
})->with(['short' => '233061', 'full' => '232410103061']);

it('mails a participant imported with a full NIM at the right campus address', function () {
    seedRoles();
    \Illuminate\Support\Facades\Mail::fake();
    $admin = makeUserWithRole('Admin', 'admin1');
    $participant = Participant::create(['participant_name' => 'Zakha', 'nim' => '232410103061', 'status' => 'not verified']);

    $this->actingAs($admin)->post("/back-admin-logged/participant/email-backup/{$participant->id}")
        ->assertSessionHas('status', fn ($s) => str_contains($s, '232410103061@mail.unej.ac.id'));

    \Illuminate\Support\Facades\Mail::assertQueued(BroadcastMail::class, fn ($mail) => $mail->hasTo('232410103061@mail.unej.ac.id'));
});
