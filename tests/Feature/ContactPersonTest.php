<?php

use App\Mail\BroadcastMail;
use App\Models\ContactPerson;
use App\Models\Participant;

beforeEach(function () {
    seedRoles();
    $this->admin = makeUserWithRole('Admin', 'admin1');
});

it('starts with the current committee contacts', function () {
    expect(ContactPerson::ordered()->pluck('name')->all())->toBe(['Farda', 'Dewi']);
});

it('normalises any WhatsApp format to 62 and shows it readably', function (string $input) {
    $this->actingAs($this->admin)
        ->post('/back-admin-logged/contact-person', ['name' => 'Raka', 'whatsapp' => $input, 'sort' => 3])
        ->assertSessionHasNoErrors();

    $contact = ContactPerson::where('name', 'Raka')->firstOrFail();
    expect($contact->whatsapp)->toBe('6281234567890');
    expect($contact->displayPhone())->toBe('0812 3456 7890');
    expect($contact->waLink())->toBe('https://wa.me/6281234567890');
})->with(['local' => '081234567890', 'country' => '6281234567890', 'plus and dashes' => '+62 812-3456-7890']);

it('rejects a missing name or an invalid number', function () {
    $this->actingAs($this->admin)
        ->post('/back-admin-logged/contact-person', ['name' => '', 'whatsapp' => '12ab'])
        ->assertSessionHasErrors(['name', 'whatsapp']);
});

it('lets an admin edit and delete a contact', function () {
    $farda = ContactPerson::where('name', 'Farda')->firstOrFail();

    $this->actingAs($this->admin)->get("/back-admin-logged/contact-person/{$farda->id}/edit")->assertOk()->assertSee('value="Farda"', false);
    $this->put("/back-admin-logged/contact-person/{$farda->id}", ['name' => 'Farda Putri', 'whatsapp' => '083823093973', 'sort' => 1])
        ->assertRedirect('/back-admin-logged/contact-person');
    expect($farda->fresh()->name)->toBe('Farda Putri');

    $this->delete("/back-admin-logged/contact-person/{$farda->id}")->assertRedirect('/back-admin-logged/contact-person');
    expect(ContactPerson::find($farda->id))->toBeNull();
});

it('lists contacts in the admin menu page with a confirmed delete', function () {
    $this->actingAs($this->admin)->get('/back-admin-logged/contact-person')
        ->assertOk()
        ->assertSee('Kontak panitia')
        ->assertSee('0838 2309 3973')
        ->assertSee('data-confirm=', false);
});

it('forbids participants from managing contacts', function () {
    $this->actingAs(makeUserWithRole('Participant', 'voter1'))->get('/back-admin-logged/contact-person')->assertForbidden();
});

it('shows the contacts in order on the homepage, the login page and the account email', function () {
    ContactPerson::query()->delete();
    ContactPerson::create(['name' => 'Kedua', 'whatsapp' => '6281111111112', 'sort' => 2]);
    ContactPerson::create(['name' => 'Pertama', 'whatsapp' => '6281111111111', 'sort' => 1]);

    $this->get('/')->assertSeeInOrder(['Pertama, 0811 1111 1111', 'Kedua, 0811 1111 1112'])->assertDontSee('Farda');
    $this->get('/login')->assertSee('wa.me/6281111111111', false)->assertDontSee('Farda');

    $participant = (new Participant())->forceFill(['participant_name' => 'Joko', 'nim' => '233061', 'username_old' => 'a', 'password_old' => 'b']);
    $html = (new BroadcastMail($participant))->render();
    expect($html)->toContain('Pertama, 0811 1111 1111')->not->toContain('Farda');
});

it('hides the help block everywhere when there are no contacts', function () {
    ContactPerson::query()->delete();

    $this->get('/')->assertDontSee('Kendala login atau belum menerima email akun?');
    $this->get('/login')->assertDontSee('Chat panitia');

    $participant = (new Participant())->forceFill(['participant_name' => 'Joko', 'nim' => '233061', 'username_old' => 'a', 'password_old' => 'b']);
    expect((new BroadcastMail($participant))->render())->not->toContain('Ada kendala?');
});

it('keeps COGITO from pointing to WhatsApp contacts that do not exist', function () {
    ContactPerson::query()->delete();
    $voter = makeUserWithRole('Participant', 'voter1');

    $lines = \App\Support\CogitoLines::vote('prepare', \App\Models\ElectionSetting::current(), $voter)['lines'];

    expect($lines)->each->toBeArray();
    expect(collect($lines)->pluck('text')->implode(' '))->not->toContain('WhatsApp');
});
