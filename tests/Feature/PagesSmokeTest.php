<?php

/**
 * Setiap halaman yang bisa dibuka harus tetap tampil (mis. setelah view/komponen dihapus).
 */
beforeEach(function () {
    seedRoles();
    $this->candidate = makeCandidate(1);
    $this->admin = makeUserWithRole('Admin', 'admin1');
    $this->voter = makeUserWithRole('Participant', 'pemilih');
});

it('renders every admin page', function (string $uri) {
    $this->actingAs($this->admin)->get($uri)->assertOk();
})->with([
    '/back-admin-logged/dashboard',
    '/back-admin-logged/candidate',
    '/back-admin-logged/candidate/add',
    '/back-admin-logged/participant',
    '/back-admin-logged/participant/add',
    '/back-admin-logged/participant-verification',
    '/back-admin-logged/live-count',
    '/back-admin-logged/election-schedule',
]);

it('renders the admin pages opened through a form button', function () {
    $participantId = $this->voter->participant_id;

    $this->actingAs($this->admin)->post("/back-admin-logged/candidate/edit/{$this->candidate->id}")->assertOk();
    $this->actingAs($this->admin)->post("/back-admin-logged/participant/edit/{$participantId}")->assertOk();
    $this->actingAs($this->admin)->post("/back-admin-logged/participant-verification/edit/{$participantId}")->assertOk();
    $this->actingAs($this->admin)->post("/back-admin-logged/participant-verification/info/{$participantId}")->assertOk();
});

it('renders the public pages', function () {
    $this->get('/')->assertOk();
    $this->get('/login')->assertOk();
    $this->actingAs($this->voter)->get('/vote')->assertOk();
});
