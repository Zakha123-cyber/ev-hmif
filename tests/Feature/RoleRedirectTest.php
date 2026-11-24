<?php

use App\Models\Participant;
use App\Models\User;

beforeEach(function () {
    seedRoles();
});

it('sends admins to the dashboard after login', function () {
    $admin = makeUserWithRole('Admin', 'admin1');

    $this->actingAs($admin)
        ->get('/back-admin-logged/redirect-role')
        ->assertRedirect('/back-admin-logged/dashboard');
});

it('sends participants to the voting page after login', function () {
    $voter = makeUserWithRole('Participant', 'pemilih');

    $this->actingAs($voter)
        ->get('/back-admin-logged/redirect-role')
        ->assertRedirect('/vote');
});

it('logs out a user without any role instead of crashing', function () {
    $participant = Participant::create(['participant_name' => 'Tanpa Role', 'nim' => '2210001', 'status' => 'verified']);
    $user = User::create([
        'participant_id' => $participant->id,
        'username' => 'tanparole',
        'password' => bcrypt('secret'),
        'status' => 'not voted',
    ]);

    $this->actingAs($user)
        ->get('/back-admin-logged/redirect-role')
        ->assertRedirect('/login')
        ->assertSessionHasErrors('username');

    $this->assertGuest();
});

it('shows the reason on the login page when a user without a role is logged out', function () {
    $this->withSession(['errors' => (new \Illuminate\Support\ViewErrorBag)->put('default', new \Illuminate\Support\MessageBag(['username' => 'Akun ini belum memiliki akses. Hubungi panitia.']))])
        ->get('/login')
        ->assertSee('Akun ini belum memiliki akses. Hubungi panitia.');
});

it('tells the user when the username or password is wrong', function () {
    makeUserWithRole('Participant', 'pemilih');

    $this->from('/login')->post('/login', ['username' => 'pemilih', 'password' => 'salah'])
        ->assertRedirect('/login');

    $this->get('/login')->assertSee(trans('auth.failed'));
});

it('logs in with username and password and lands on the right page', function () {
    $voter = makeUserWithRole('Participant', 'pemilih');

    $this->post('/login', ['username' => 'pemilih', 'password' => 'secret'])
        ->assertRedirect('/back-admin-logged/redirect-role');

    $this->assertAuthenticatedAs($voter);
});
