<?php

// Batas percobaan login berlapis:
// - per username + IP: 5 kali salah, kunci 1 menit (bawaan Breeze)
// - per username saja: 20 kali salah dalam 1 jam dari IP mana pun, agar ganti-ganti IP tidak membantu menebak password

beforeEach(function () {
    seedRoles();
    $this->voter = makeUserWithRole('Participant', 'pemilih');
    $this->admin = makeUserWithRole('Admin', 'admin1');
    $this->adminLogin = '/' . config('election.admin_login_path');
});

function loginFrom(string $ip, string $url, string $username, string $password)
{
    return test()->withServerVariables(['REMOTE_ADDR' => $ip])->post($url, ['username' => $username, 'password' => $password]);
}

/** 20 tebakan salah, dibagi ke 5 IP (4 per IP, jadi kunci per IP tidak pernah aktif). */
function guessFromManyIps(string $url, string $username): void
{
    for ($i = 0; $i < 20; $i++) {
        loginFrom('10.0.0.' . intdiv($i, 4), $url, $username, 'tebakan-' . $i);
    }
}

it('locks an account after 20 wrong passwords even when they come from many IPs', function () {
    guessFromManyIps('/login', 'pemilih');

    loginFrom('10.9.9.9', '/login', 'pemilih', 'secret')
        ->assertSessionHasErrors(['username' => 'Akun ini dikunci sementara karena terlalu banyak percobaan masuk. Coba lagi dalam 60 menit atau hubungi panitia.']);

    $this->assertGuest();
});

it('applies the account lock on the admin login too', function () {
    guessFromManyIps($this->adminLogin, 'admin1');

    loginFrom('10.9.9.9', $this->adminLogin, 'admin1', 'secret')->assertSessionHasErrors('username');

    $this->assertGuest();
});

it('does not lock other accounts', function () {
    guessFromManyIps('/login', 'pemilih');
    $other = makeUserWithRole('Participant', 'pemilih2');

    loginFrom('10.9.9.9', '/login', 'pemilih2', 'secret');

    $this->assertAuthenticatedAs($other);
});

it('still allows 19 wrong passwords across IPs before the account lock', function () {
    for ($i = 0; $i < 19; $i++) {
        loginFrom('10.0.0.' . intdiv($i, 4), '/login', 'pemilih', 'tebakan-' . $i);
    }

    loginFrom('10.9.9.9', '/login', 'pemilih', 'secret');

    $this->assertAuthenticatedAs($this->voter);
});

it('resets the account counter after a successful login', function () {
    for ($i = 0; $i < 19; $i++) {
        loginFrom('10.0.0.' . intdiv($i, 4), '/login', 'pemilih', 'tebakan-' . $i);
    }
    loginFrom('10.9.9.9', '/login', 'pemilih', 'secret');
    $this->post('/logout');

    for ($i = 0; $i < 19; $i++) {
        loginFrom('10.1.0.' . intdiv($i, 4), '/login', 'pemilih', 'tebakan-' . $i);
    }
    loginFrom('10.9.9.8', '/login', 'pemilih', 'secret');

    $this->assertAuthenticatedAs($this->voter);
});

it('keeps the short per-IP lock after 5 wrong passwords', function () {
    for ($i = 0; $i < 5; $i++) {
        loginFrom('10.0.0.1', '/login', 'pemilih', 'salah');
    }

    loginFrom('10.0.0.1', '/login', 'pemilih', 'secret')->assertSessionHasErrors('username');
    $this->assertGuest();

    // IP lain belum terkunci (baru 5 dari 20 percobaan untuk akun ini)
    loginFrom('10.0.0.2', '/login', 'pemilih', 'secret');
    $this->assertAuthenticatedAs($this->voter);
});
