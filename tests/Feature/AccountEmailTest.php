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
