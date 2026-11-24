<?php

use App\Mail\BroadcastMail;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
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

it('keeps processing the remaining participants when one of them fails', function () {
    $failing = Participant::create(['participant_name' => 'Gagal', 'nim' => '2210002', 'status' => 'not verified']);
    $after = Participant::create(['participant_name' => 'Setelahnya', 'nim' => '2210003', 'status' => 'not verified']);

    // Simulasikan kegagalan (mis. SMTP error) untuk satu peserta saja.
    Event::listen(Registered::class, function (Registered $event) use ($failing) {
        if ($event->user->participant_id === $failing->id) {
            throw new RuntimeException('SMTP error');
        }
    });

    $this->actingAs($this->admin)
        ->post('/back-admin-logged/participant/broadcast')
        ->assertRedirect();

    // Peserta sebelum & sesudah yang gagal tetap diproses
    expect(User::where('participant_id', $this->pending->id)->exists())->toBeTrue();
    expect(User::where('participant_id', $after->id)->exists())->toBeTrue();

    // Peserta yang gagal di-rollback penuh sehingga bisa dicoba lagi
    expect(User::where('participant_id', $failing->id)->exists())->toBeFalse();
    expect($failing->fresh()->status)->toBe('not verified');
    expect($failing->fresh()->username_old)->toBeNull();
});

it('reports how many participants succeeded and which ones failed', function () {
    $failing = Participant::create(['participant_name' => 'Gagal', 'nim' => '2210002', 'status' => 'not verified']);

    Event::listen(Registered::class, function (Registered $event) use ($failing) {
        if ($event->user->participant_id === $failing->id) {
            throw new RuntimeException('SMTP error');
        }
    });

    $this->actingAs($this->admin)
        ->post('/back-admin-logged/participant/broadcast')
        ->assertSessionHas('status', fn ($message) => str_contains($message, '1 peserta'))
        ->assertSessionHas('error', fn ($message) => str_contains($message, '2210002'));
});

it('shows both the success summary and the failure list to the admin', function () {
    $this->actingAs($this->admin)
        ->withSession(['status' => 'Akun dibuat untuk 1 peserta.', 'error' => '1 peserta gagal diproses: 2210002'])
        ->get('/back-admin-logged/participant')
        ->assertSee('Akun dibuat untuk 1 peserta.')
        ->assertSee('1 peserta gagal diproses: 2210002');
});

it('sends one email per new account', function () {
    Participant::create(['participant_name' => 'Kedua', 'nim' => '2210002', 'status' => 'not verified']);

    $this->actingAs($this->admin)->post('/back-admin-logged/participant/broadcast');

    Mail::assertQueued(BroadcastMail::class, 2);
});

it('forbids participants from running the broadcast', function () {
    $voter = makeUserWithRole('Participant', 'pemilih');

    $this->actingAs($voter)
        ->post('/back-admin-logged/participant/broadcast')
        ->assertForbidden();

    expect(User::where('participant_id', $this->pending->id)->exists())->toBeFalse();
});
