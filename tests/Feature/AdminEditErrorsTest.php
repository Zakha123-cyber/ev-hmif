<?php

use App\Models\Participant;

beforeEach(function () {
    seedRoles();
    $this->admin = makeUserWithRole('Admin', 'admin1');
});

it('returns to the participant edit page with the error and the typed values', function () {
    Participant::create(['participant_name' => 'Ada', 'nim' => '221010001', 'whatsapp_number' => '62811', 'status' => 'not verified']);
    $rina = Participant::create(['participant_name' => 'Rina', 'nim' => '221010002', 'whatsapp_number' => '62812', 'status' => 'not verified']);
    $edit = url('/back-admin-logged/participant/edit/' . $rina->id);

    $this->actingAs($this->admin)->from($edit)
        ->put('/back-admin-logged/participant/update/' . $rina->id, [
            'participant_name' => 'Rina Baru',
            'nim' => '221010001',
            'whatsapp_number' => '62812',
            'status' => 'not verified',
        ])
        ->assertRedirect($edit);

    $this->get($edit)->assertOk()->assertSee('NIM sudah terdaftar.')->assertSee('value="Rina Baru"', false);
});

it('returns to the candidate edit page with the poster error', function () {
    $candidate = makeCandidate(1);
    $edit = url('/back-admin-logged/candidate/edit/' . $candidate->id);

    $this->actingAs($this->admin)->from($edit)
        ->put('/back-admin-logged/candidate/update/' . $candidate->id, [
            'candidate_pair' => 1, 'leader_name' => '', 'vice_leader_name' => 'W', 'vision' => 'V', 'mission' => 'M',
        ])
        ->assertRedirect($edit);

    $this->get($edit)->assertOk()->assertSee('Nama Ketua harus diisi.');
});

it('returns to the account edit page with the error', function () {
    $voter = makeUserWithRole('Participant', 'voter1');
    $edit = url('/back-admin-logged/participant-verification/edit/' . $voter->participant_id);

    $this->actingAs($this->admin)->from($edit)
        ->put('/back-admin-logged/participant-verification/update/' . $voter->participant_id, [
            'username' => 'voter1', 'password' => 'x', 'whatsapp_number' => '', 'status' => 'verified',
        ])
        ->assertRedirect($edit);

    $this->get($edit)->assertOk()->assertSee('Nomor Whatsapp harus diisi.');
});

it('opens the edit pages from plain links', function () {
    $candidate = makeCandidate(1);

    $this->actingAs($this->admin)->get('/back-admin-logged/candidate')
        ->assertSee('href="' . url('/back-admin-logged/candidate/edit/' . $candidate->id) . '"', false);
});
