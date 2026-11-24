<?php

beforeEach(function () {
    seedRoles();
});

it('renders the login screen', function () {
    $this->get('/login')->assertOk()->assertSee('name="username"', false);
});

it('authenticates a participant with username and password', function () {
    $voter = makeUserWithRole('Participant', 'pemilih');

    $this->post('/login', ['username' => 'pemilih', 'password' => 'secret'])
        ->assertRedirect('/back-admin-logged/redirect-role');

    $this->assertAuthenticatedAs($voter);
});

it('does not authenticate with a wrong password', function () {
    makeUserWithRole('Participant', 'pemilih');

    $this->post('/login', ['username' => 'pemilih', 'password' => 'salah'])
        ->assertSessionHasErrors('username');

    $this->assertGuest();
});

it('locks out after too many failed attempts', function () {
    makeUserWithRole('Participant', 'pemilih');

    for ($i = 0; $i < 5; $i++) {
        $this->post('/login', ['username' => 'pemilih', 'password' => 'salah']);
    }

    $this->post('/login', ['username' => 'pemilih', 'password' => 'secret'])
        ->assertSessionHasErrors('username');

    $this->assertGuest();
});

it('logs a user out', function () {
    $voter = makeUserWithRole('Participant', 'pemilih');

    $this->actingAs($voter)->post('/logout')->assertRedirect('/');

    $this->assertGuest();
});

it('no longer exposes unused account pages from the starter kit', function (string $method, string $uri) {
    $this->call($method, $uri)->assertNotFound();
})->with([
    'register form' => ['GET', '/register'],
    'register submit' => ['POST', '/register'],
    'forgot password' => ['GET', '/forgot-password'],
    'reset password' => ['GET', '/reset-password/token123'],
    'verify email' => ['GET', '/verify-email'],
    'confirm password' => ['GET', '/confirm-password'],
]);
