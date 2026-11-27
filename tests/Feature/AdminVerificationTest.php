<?php

use App\Models\Participant;

beforeEach(function () {
    seedRoles();
    $this->admin = makeUserWithRole('Admin', 'admin1');
    $this->waiting = Participant::create(['participant_name' => 'Fajar', 'nim' => '221010006', 'status' => 'not verified']);
    $this->voter = makeUserWithRole('Participant', 'voter1');
    $this->voter->details->update(['username_old' => 'voter1', 'password_old' => 'Rahasia123', 'whatsapp_number' => '62812']);
});

it('builds the account form with participant ids and regenerate buttons', function () {
    $this->actingAs($this->admin)->get('/back-admin-logged/participant-verification')
        ->assertOk()
        ->assertSee('name="nim"', false)
        ->assertSee('<option value="' . $this->waiting->id . '"', false)
        ->assertSee('221010006 - Fajar')
        ->assertSee('Acak ulang')
        ->assertSee('data-regenerate="' . url('/back-admin-logged/generate-username') . '"', false)
        ->assertSee('Info akun');
});

it('uses the current period in the WhatsApp message instead of 2022/2023', function () {
    $this->actingAs($this->admin)->post('/back-admin-logged/participant-verification/info/' . $this->voter->participant_id)
        ->assertOk()
        ->assertSee('2025/2026')
        ->assertDontSee('2022/2023')
        ->assertSee('Cetak kartu');
});

it('keeps the password hidden on the info page until asked', function () {
    $html = $this->actingAs($this->admin)->post('/back-admin-logged/participant-verification/info/' . $this->voter->participant_id)->getContent();

    expect($html)->toMatch('/<span[^>]*id="info-password"[^>]*hidden/');
});

it('prints the account card as a PDF with the current period', function () {
    $response = $this->actingAs($this->admin)->get('/back-admin-logged/participant-verification/info/print/' . $this->voter->participant_id);

    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toContain('pdf');
});

it('rejects an unknown status when updating an account', function () {
    $this->actingAs($this->admin)->put('/back-admin-logged/participant-verification/update/' . $this->voter->participant_id, [
        'username' => 'voter1',
        'password' => 'Rahasia123',
        'whatsapp_number' => '62812',
        'status' => 'banned',
    ])->assertSessionHasErrors('status');
});

it('does not list admin accounts among verified participants', function () {
    $this->actingAs($this->admin)->get('/back-admin-logged/participant-verification')
        ->assertSee('participant-verification/edit/' . $this->voter->participant_id, false)
        ->assertDontSee('participant-verification/edit/' . $this->admin->participant_id, false);
});
