<?php

beforeEach(function () {
    seedRoles();
    $this->admin = makeUserWithRole('Admin', 'admin1');
});

it('renders the new Indonesian shell with the current page marked', function () {
    $html = $this->actingAs($this->admin)->get('/back-admin-logged/election-schedule')
        ->assertOk()
        ->assertSee('Ringkasan')
        ->assertSee('Jadwal &amp; periode', false)
        ->assertSee('Verifikasi akun')
        ->assertSee('Hasil suara')
        ->assertSee('Keluar')
        ->assertSee('frontend/css/admin.css', false)
        ->getContent();

    expect($html)->toMatch('#<a[^>]+election-schedule[^>]+aria-current="page"#');
});

it('no longer loads the old admin libraries', function () {
    $this->actingAs($this->admin)->get('/back-admin-logged/election-schedule')
        ->assertDontSee('kit-pro.fontawesome', false)
        ->assertDontSee('jquery', false)
        ->assertDontSee('datatables', false)
        ->assertDontSee('flowbite', false)
        ->assertDontSee('apexcharts', false)
        ->assertDontSee('chart.js', false)
        ->assertDontSee('css-admin/style.css', false);
});

it('shows success and error messages together', function () {
    $this->actingAs($this->admin)
        ->withSession(['status' => 'Tersimpan A', 'error' => 'Gagal B'])
        ->get('/back-admin-logged/election-schedule')
        ->assertSee('Tersimpan A')
        ->assertSee('Gagal B');
});

it('falls back to the username when the admin has no name', function () {
    $this->admin->details->update(['participant_name' => '']);

    $this->actingAs($this->admin->fresh())->get('/back-admin-logged/election-schedule')
        ->assertOk()
        ->assertSee('admin1');
});
