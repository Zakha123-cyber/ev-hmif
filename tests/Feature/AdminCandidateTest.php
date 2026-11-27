<?php

beforeEach(function () {
    seedRoles();
    $this->admin = makeUserWithRole('Admin', 'admin1');
});

it('escapes the mission text instead of rendering raw HTML', function () {
    $candidate = makeCandidate(1);
    $candidate->forceFill(['mission' => '<script>alert(1)</script>. Misi dua.'])->save();

    $html = $this->actingAs($this->admin)->get('/back-admin-logged/candidate')->assertOk()->getContent();

    expect($html)->toContain('&lt;script&gt;alert(1)&lt;/script&gt;')->not->toContain('<script>alert(1)</script>');
});

it('offers labelled edit and delete actions, delete with a confirmation', function () {
    makeCandidate(1);

    $this->actingAs($this->admin)->get('/back-admin-logged/candidate')
        ->assertSee('Ubah')
        ->assertSee('Hapus')
        ->assertSee('data-confirm=', false)
        ->assertSee('Tambah paslon');
});

it('invites adding a pair when there are none', function () {
    $this->actingAs($this->admin)->get('/back-admin-logged/candidate')->assertSee('Belum ada paslon');
});

it('says updated, not added, after editing a pair', function () {
    $candidate = makeCandidate(1);

    $this->actingAs($this->admin)->put('/back-admin-logged/candidate/update/' . $candidate->id, [
        'candidate_pair' => 1,
        'leader_name' => 'Ketua Baru',
        'vice_leader_name' => 'Wakil Baru',
        'vision' => 'Visi',
        'mission' => 'Misi',
    ])->assertSessionHas('status', 'Berhasil memperbarui data.');
});
