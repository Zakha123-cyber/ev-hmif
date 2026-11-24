<?php

use App\Imports\ImportParticipant;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;

beforeEach(function () {
    seedRoles();
    $this->admin = makeUserWithRole('Admin', 'admin1');
    Mail::fake();
});

function makeParticipant(string $nim, string $status = 'not verified'): Participant
{
    return Participant::create([
        'participant_name' => "Peserta {$nim}",
        'nim' => $nim,
        'status' => $status,
    ]);
}

function giveAccount(Participant $participant, string $username): User
{
    $user = User::create([
        'participant_id' => $participant->id,
        'username' => $username,
        'password' => bcrypt('secret'),
        'status' => 'not voted',
    ]);
    $user->assignRole('Participant');

    return $user;
}

function importCsv(string $content): void
{
    $path = tempnam(sys_get_temp_dir(), 'peserta') . '.csv';
    file_put_contents($path, $content);
    Excel::import(new ImportParticipant, $path);
    unlink($path);
}

// --- Database menolak duplikat ---

it('does not allow two participants with the same NIM in the database', function () {
    makeParticipant('2210001');

    expect(fn () => makeParticipant('2210001'))->toThrow(QueryException::class);
});

it('does not allow two accounts for the same participant in the database', function () {
    $participant = makeParticipant('2210001');
    giveAccount($participant, 'akunsatu');

    expect(fn () => giveAccount($participant, 'akundua'))->toThrow(QueryException::class);
});

// --- Import Excel ---

it('skips participants whose NIM already exists when importing', function () {
    importCsv("Budi,2210001,not verified\nSari,2210002,not verified\n");
    importCsv("Budi,2210001,not verified\nSari,2210002,not verified\nAndi,2210003,not verified\n");

    expect(Participant::where('nim', '2210001')->count())->toBe(1);
    expect(Participant::where('nim', '2210002')->count())->toBe(1);
    expect(Participant::where('nim', '2210003')->count())->toBe(1);
});

it('skips duplicate NIMs inside the same import file', function () {
    importCsv("Budi,2210001,not verified\nBudi Lagi,2210001,not verified\n");

    expect(Participant::where('nim', '2210001')->count())->toBe(1);
});

// --- Tambah / edit peserta manual ---

it('rejects adding a participant with a NIM that already exists', function () {
    makeParticipant('2210001');

    $this->actingAs($this->admin)
        ->post('/back-admin-logged/participant/store', [
            'participant_name' => 'Duplikat',
            'nim' => '2210001',
            'whatsapp_number' => '62812',
            'status' => 'not verified',
        ])
        ->assertSessionHasErrors('nim');

    expect(Participant::where('nim', '2210001')->count())->toBe(1);
});

it('rejects changing a participant NIM to one that already exists', function () {
    makeParticipant('2210001');
    $other = makeParticipant('2210002');

    $this->actingAs($this->admin)
        ->put("/back-admin-logged/participant/update/{$other->id}", [
            'participant_name' => $other->participant_name,
            'nim' => '2210001',
            'whatsapp_number' => '62812',
            'status' => 'not verified',
        ])
        ->assertSessionHasErrors('nim');

    expect($other->fresh()->nim)->toBe('2210002');
});

it('still allows saving a participant without changing their own NIM', function () {
    $participant = makeParticipant('2210001');

    $this->actingAs($this->admin)
        ->put("/back-admin-logged/participant/update/{$participant->id}", [
            'participant_name' => 'Nama Baru',
            'nim' => '2210001',
            'whatsapp_number' => '62812',
            'status' => 'not verified',
        ])
        ->assertSessionHasNoErrors();

    expect($participant->fresh()->participant_name)->toBe('Nama Baru');
});

// --- Verifikasi manual ---

it('rejects manual verification for a participant who already has an account', function () {
    $participant = makeParticipant('2210001');
    giveAccount($participant, 'akunlama');

    $this->actingAs($this->admin)
        ->post('/back-admin-logged/participant-verification/verified', [
            'nim' => $participant->id,
            'username' => 'akunbaru',
            'password' => 'rahasia',
            'whatsapp_number' => '62812',
            'status' => 'verified',
        ])
        ->assertSessionHas('error');

    expect(User::where('participant_id', $participant->id)->count())->toBe(1);
});

it('always marks a manually verified participant as verified', function () {
    $participant = makeParticipant('2210001');

    $this->actingAs($this->admin)
        ->post('/back-admin-logged/participant-verification/verified', [
            'nim' => $participant->id,
            'username' => 'akunbaru',
            'password' => 'rahasia',
            'whatsapp_number' => '62812',
            'status' => 'not verified',
        ]);

    expect($participant->fresh()->status)->toBe('verified');
    expect(User::where('participant_id', $participant->id)->count())->toBe(1);
});

// --- Broadcast email ---

it('does not create a second account in broadcast for a participant who already has one', function () {
    // Peserta sudah punya akun, tapi statusnya kembali "not verified" (mis. diedit admin)
    $participant = makeParticipant('2210001', 'not verified');
    giveAccount($participant, 'akunlama');
    $fresh = makeParticipant('2210002', 'not verified');

    $this->actingAs($this->admin)->post('/back-admin-logged/participant/broadcast');

    expect(User::where('participant_id', $participant->id)->count())->toBe(1);
    expect(User::where('participant_id', $fresh->id)->count())->toBe(1);
});
