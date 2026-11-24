<?php

use App\Services\CredentialGenerator;
use App\Models\User;

/**
 * Generator dengan urutan "acak" yang sudah ditentukan, untuk mensimulasikan bentrok username.
 */
function scriptedGenerator(array $sequence): CredentialGenerator
{
    return new class($sequence) extends CredentialGenerator {
        public function __construct(private array $sequence) {}

        protected function randomString(): string
        {
            return array_shift($this->sequence);
        }
    };
}

beforeEach(function () {
    seedRoles();
});

it('never returns a username that is already taken', function () {
    makeUserWithRole('Participant', 'takenAAA');

    $username = scriptedGenerator(['takenAAA', 'freeBBBB'])->username();

    expect($username)->toBe('freeBBBB');
});

it('keeps trying until it finds a free username', function () {
    makeUserWithRole('Participant', 'takenAAA');
    makeUserWithRole('Participant', 'takenBBB');

    $username = scriptedGenerator(['takenAAA', 'takenBBB', 'freeCCCC'])->username();

    expect($username)->toBe('freeCCCC');
    expect(User::where('username', $username)->exists())->toBeFalse();
});

it('does not check passwords against usernames', function () {
    // Password boleh sama dengan username orang lain — yang penting acak.
    makeUserWithRole('Participant', 'samePASS');

    expect(scriptedGenerator(['samePASS'])->password())->toBe('samePASS');
});

it('generates 8 character credentials without look-alike characters', function () {
    $generator = new CredentialGenerator;

    $username = $generator->username();
    $password = $generator->password();

    expect($username)->toMatch('/^[2-9a-km-zA-HJ-NP-Z]{8}$/');
    expect($password)->toMatch('/^[2-9a-km-zA-HJ-NP-Z]{8}$/');
});

it('lets an admin request a fresh username and password from the admin panel', function () {
    $admin = makeUserWithRole('Admin', 'admin1');

    $username = $this->actingAs($admin)->get('/back-admin-logged/generate-username')->assertOk()->getContent();
    $password = $this->actingAs($admin)->get('/back-admin-logged/generate-password')->assertOk()->getContent();

    expect(strlen($username))->toBe(8);
    expect(strlen($password))->toBe(8);
});

it('points the "generate again" buttons on the verification page to the admin endpoints', function () {
    $admin = makeUserWithRole('Admin', 'admin1');
    $voter = makeUserWithRole('Participant', 'pemilih');

    $this->actingAs($admin)
        ->post("/back-admin-logged/participant-verification/edit/{$voter->participant_id}")
        ->assertOk()
        ->assertSee('/back-admin-logged/generate-username', false)
        ->assertSee('/back-admin-logged/generate-password', false)
        ->assertDontSee('/generateNew', false);
});

it('forbids participants from using the admin credential generator', function () {
    $voter = makeUserWithRole('Participant', 'pemilih');

    $this->actingAs($voter)->get('/back-admin-logged/generate-username')->assertForbidden();
    $this->actingAs($voter)->get('/back-admin-logged/generate-password')->assertForbidden();
});

it('does not expose the credential generator as public URLs', function () {
    $this->get('/generateNewUsername')->assertNotFound();
    $this->get('/generateNewPassword')->assertNotFound();
});
