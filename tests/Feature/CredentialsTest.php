<?php

use App\Services\CredentialGenerator;
use App\Models\User;
use Database\Seeders\ParticipantSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Support\Facades\Hash;

const OLD_HARDCODED_ADMIN_PASSWORD = 'JayalahHimpunanku';

// --- Seeder admin ---

it('seeds admin accounts with the password from configuration', function () {
    config(['election.admin_seed_password' => 'PasswordDariEnv123']);

    $this->seed([RoleSeeder::class, ParticipantSeeder::class, UserSeeder::class]);

    $admins = User::role('Admin')->get();
    expect($admins)->toHaveCount(2);
    foreach ($admins as $admin) {
        expect(Hash::check('PasswordDariEnv123', $admin->password))->toBeTrue();
    }
});

it('never seeds admin accounts with the old hardcoded password', function () {
    config(['election.admin_seed_password' => null]);

    $this->seed([RoleSeeder::class, ParticipantSeeder::class, UserSeeder::class]);

    foreach (User::role('Admin')->get() as $admin) {
        expect(Hash::check(OLD_HARDCODED_ADMIN_PASSWORD, $admin->password))->toBeFalse();
    }
});

// --- Generator username / password peserta ---

it('generates participant usernames of at least 8 characters', function () {
    $username = (new CredentialGenerator)->username();

    expect(strlen($username))->toBeGreaterThanOrEqual(8);
    expect($username)->toMatch('/^[a-zA-Z0-9]+$/');
});

it('generates participant passwords of at least 8 characters', function () {
    $password = (new CredentialGenerator)->password();

    expect(strlen($password))->toBeGreaterThanOrEqual(8);
    expect($password)->toMatch('/^[a-zA-Z0-9]+$/');
});

it('avoids look-alike characters so participants can type credentials from the email', function () {
    $generator = new CredentialGenerator;
    $sample = '';
    for ($i = 0; $i < 50; $i++) {
        $sample .= $generator->username() . $generator->password();
    }

    expect($sample)->not->toMatch('/[0O1lI]/');
});
