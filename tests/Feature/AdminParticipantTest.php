<?php

use App\Models\Participant;

beforeEach(function () {
    seedRoles();
    $this->admin = makeUserWithRole('Admin', 'admin1');
});

it('offers search, import, a confirmed broadcast and labelled row actions', function () {
    Participant::create(['participant_name' => 'Rina', 'nim' => '221010001', 'status' => 'not verified']);

    $this->actingAs($this->admin)->get('/back-admin-logged/participant')
        ->assertOk()
        ->assertSee('data-table-filter="#peserta"', false)
        ->assertSee('Impor Excel')
        ->assertSee('<dialog', false)
        ->assertSee('id="import"', false)
        ->assertSee('Buat akun dan kirim email ke semua peserta yang belum punya akun?')
        ->assertSee('Kirim ulang email')
        ->assertSee('Belum terverifikasi');
});

it('reopens the import dialog with the file error after a failed import', function () {
    $this->actingAs($this->admin)
        ->from('/back-admin-logged/participant')
        ->post('/back-admin-logged/participant/import', []);

    $this->get('/back-admin-logged/participant')
        ->assertSee('data-open-on-load', false)
        ->assertSee('File Excel harus diisi.');
});

it('keeps WhatsApp and status after a validation error on the add form', function () {
    $this->actingAs($this->admin)
        ->from('/back-admin-logged/participant/add')
        ->post('/back-admin-logged/participant/store', [
            'participant_name' => 'Bagus',
            'nim' => '',
            'whatsapp_number' => '6281234',
            'status' => 'verified',
            'email_backup' => 'bukan-email',
        ]);

    $html = $this->get('/back-admin-logged/participant/add')->getContent();

    expect($html)->toContain('value="6281234"');
    expect($html)->toMatch('/<option value="verified"[^>]*selected/');
});

it('titles the edit page as editing and says updated afterwards', function () {
    $participant = Participant::create(['participant_name' => 'Rina', 'nim' => '221010001', 'whatsapp_number' => '62812', 'status' => 'not verified']);

    $this->actingAs($this->admin)->post('/back-admin-logged/participant/edit/' . $participant->id)
        ->assertSee('Ubah peserta')
        ->assertDontSee('Tambah Participant');

    $this->put('/back-admin-logged/participant/update/' . $participant->id, [
        'participant_name' => 'Rina A',
        'nim' => '221010001',
        'whatsapp_number' => '62812',
        'status' => 'not verified',
    ])->assertSessionHas('status', 'Berhasil memperbarui data.');
});

it('does not list admin accounts as participants, so an admin cannot delete themselves here', function () {
    Participant::create(['participant_name' => 'Rina', 'nim' => '221010001', 'status' => 'not verified']);

    $this->actingAs($this->admin)->get('/back-admin-logged/participant')
        ->assertSee('Rina')
        ->assertDontSee('participant/delete/' . $this->admin->participant_id, false);
});
