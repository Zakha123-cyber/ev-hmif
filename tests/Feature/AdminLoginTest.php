<?php

beforeEach(function () {
    seedRoles();
    $this->admin = makeUserWithRole('Admin', 'admin1');
    $this->voter = makeUserWithRole('Participant', 'pemilih');
    $this->adminLogin = '/' . config('election.admin_login_path');
});

it('uses login-admin-hmif as the default admin login path', function () {
    expect(config('election.admin_login_path'))->toBe('login-admin-hmif');
});

it('refuses admin accounts on the participant login with the usual wrong-password message', function () {
    $this->from('/login')->post('/login', ['username' => 'admin1', 'password' => 'secret'])
        ->assertRedirect('/login')
        ->assertSessionHasErrors(['username' => 'Username atau password salah. Cek lagi email akunmu, lalu coba lagi.']);

    $this->assertGuest();
});

it('still lets participants log in on the participant login', function () {
    $this->post('/login', ['username' => 'pemilih', 'password' => 'secret']);

    $this->assertAuthenticatedAs($this->voter);
});

it('lets admins log in on the admin login and lands on the dashboard', function () {
    $this->get($this->adminLogin)->assertOk()->assertSee('Masuk panel admin');

    $this->post($this->adminLogin, ['username' => 'admin1', 'password' => 'secret'])
        ->assertRedirect('/back-admin-logged/redirect-role');

    $this->assertAuthenticatedAs($this->admin);
});

it('refuses participant accounts on the admin login', function () {
    $this->from($this->adminLogin)->post($this->adminLogin, ['username' => 'pemilih', 'password' => 'secret'])
        ->assertRedirect($this->adminLogin)
        ->assertSessionHasErrors('username');

    $this->assertGuest();
});

it('counts admin attempts on the participant login toward the lockout', function () {
    for ($i = 0; $i < 5; $i++) {
        $this->post('/login', ['username' => 'admin1', 'password' => 'secret']);
    }

    $this->post($this->adminLogin, ['username' => 'admin1', 'password' => 'secret'])->assertSessionHasErrors('username');
    $this->assertGuest();
});

it('hides the admin area from guests with a 404', function () {
    $this->get('/back-admin-logged/dashboard')->assertNotFound();
    $this->get('/back-admin-logged/participant')->assertNotFound();
});

it('does not link the admin login from the public pages', function () {
    $this->get('/')->assertDontSee(config('election.admin_login_path'));
    $this->get('/login')->assertDontSee(config('election.admin_login_path'));
});
