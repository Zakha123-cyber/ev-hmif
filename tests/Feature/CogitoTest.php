<?php

use Illuminate\Support\Carbon;

beforeEach(function () {
    seedRoles();
    makeCandidate(1);
    makeCandidate(2);
});

/**
 * Isi COGITO dari HTML: kalimat (JSON), kalimat pertama yang terlihat tanpa JS, dan aksi.
 *
 * @return array{lines: string[], first: string, action: ?array{label: string, href: string}, node: DOMElement, xpath: DOMXPath}
 */
function cogito(string $html): array
{
    $dom = new DOMDocument();
    @$dom->loadHTML($html);
    $xpath = new DOMXPath($dom);
    $nodes = $xpath->query('//aside[@data-cogito]');
    expect($nodes->length)->toBe(1);
    $node = $nodes->item(0);

    $json = $xpath->query('.//script[@data-cogito-lines]', $node)->item(0)->textContent;
    $action = $xpath->query('.//a[@data-cogito-action]', $node)->item(0);

    return [
        'lines' => json_decode($json, true),
        'first' => trim($xpath->query('.//p[@data-cogito-line]', $node)->item(0)->textContent),
        'action' => $action ? ['label' => trim($action->textContent), 'href' => $action->getAttribute('href')] : null,
        'node' => $node,
        'xpath' => $xpath,
    ];
}

it('greets and says the schedule is not announced yet', function () {
    $c = cogito($this->get('/')->getContent());

    expect($c['lines'])->toBe([
        'Halo! Aku COGITO, maskot HMIF.',
        'Jadwal pemilihan belum diumumkan. Akun memilih dikirim ke email kampusmu.',
        'Sambil menunggu, buka amplop paslon untuk kenalan.',
    ]);
    expect($c['action'])->toBeNull();
});

it('tells visitors when voting opens', function () {
    setSchedule('2026-12-01 08:00', '2026-12-01 15:00');
    $this->travelTo(Carbon::parse('2026-11-30 10:00'));

    expect(cogito($this->get('/')->getContent())['lines'])->toBe([
        'Halo! Aku COGITO, maskot HMIF.',
        'Pemilihan dibuka Selasa, 1 Desember pukul 08.00 WIB.',
        'Cek email kampusmu untuk akun memilih.',
    ]);
});

it('sends guests to the login page while voting is open', function () {
    setSchedule('2026-12-01 08:00', '2026-12-01 15:00');
    $this->travelTo(Carbon::parse('2026-12-01 10:00'));

    $c = cogito($this->get('/')->getContent());

    expect($c['lines'][1])->toBe('Pemilihan sedang berlangsung sampai Selasa, 1 Desember pukul 15.00 WIB.');
    expect($c['lines'][2])->toBe('Masuk dengan akun dari email kampusmu.');
    expect($c['action'])->toBe(['label' => 'Masuk untuk memilih', 'href' => route('login')]);
});

it('points a logged-in voter who has not voted to the ballot', function () {
    setSchedule('2026-12-01 08:00', '2026-12-01 15:00');
    $this->travelTo(Carbon::parse('2026-12-01 10:00'));

    $c = cogito($this->actingAs(makeUserWithRole('Participant', 'voter1'))->get('/')->getContent());

    expect($c['lines'])->toBe(['Halo, voter1! Surat suaramu sudah menunggu.']);
    expect($c['action'])->toBe(['label' => 'Buka surat suara', 'href' => url('/vote')]);
});

it('thanks a voter without naming the candidate they chose', function () {
    setSchedule('2026-12-01 08:00', '2026-12-01 15:00');
    $this->travelTo(Carbon::parse('2026-12-01 10:00'));
    $voter = makeUserWithRole('Participant', 'voter1');
    $voter->update(['status' => 'voted']);

    $c = cogito($this->actingAs($voter)->get('/')->getContent());

    expect($c['lines'])->toBe(['Terima kasih sudah memilih! Hasil diumumkan setelah pemilihan ditutup Selasa, 1 Desember pukul 15.00 WIB.']);
    expect($c['node']->textContent)->not->toContain('Ketua 1')->not->toContain('Paslon');
});

it('points to the results once voting has closed', function () {
    setSchedule('2026-12-01 08:00', '2026-12-01 15:00');
    $this->travelTo(Carbon::parse('2026-12-01 16:00'));

    $c = cogito($this->get('/')->getContent());

    expect($c['lines'])->toBe([
        'Pemilihan sudah ditutup. Terima kasih untuk semua yang memilih!',
        'Lihat hasil penghitungannya di bawah.',
    ]);
    expect($c['action'])->toBe(['label' => 'Lihat hasil', 'href' => '#hasil']);
});

it('offers the admin panel instead of the login to an admin', function () {
    setSchedule('2026-12-01 08:00', '2026-12-01 15:00');
    $this->travelTo(Carbon::parse('2026-12-01 10:00'));

    $c = cogito($this->actingAs(makeUserWithRole('Admin', 'admin1'))->get('/')->getContent());

    expect($c['action'])->toBe(['label' => 'Panel admin', 'href' => url('/back-admin-logged/redirect-role')]);
    expect($c['lines'])->not->toContain('Masuk dengan akun dari email kampusmu.');
});

it('reads without JavaScript: first line visible, no dead buttons, still frame for reduced motion', function () {
    $c = cogito($this->get('/')->getContent());
    $x = $c['xpath'];

    expect($c['first'])->toBe('Halo! Aku COGITO, maskot HMIF.');
    foreach (['data-cogito-next', 'data-cogito-close'] as $attr) {
        expect($x->query(".//button[@{$attr}][@hidden]", $c['node'])->length)->toBe(1);
    }
    expect($x->query('.//img[@width="278"][@height="385"][contains(@src,"cogito/salam.webp")]', $c['node'])->length)->toBe(1);
    expect($x->query('.//source[@media="(prefers-reduced-motion: reduce)"][contains(@srcset,"salam-still.webp")]', $c['node'])->length)->toBe(1);
});

it('hides the action behind the last line when there are several lines', function () {
    setSchedule('2026-12-01 08:00', '2026-12-01 15:00');
    $this->travelTo(Carbon::parse('2026-12-01 10:00'));

    $c = cogito($this->get('/')->getContent());

    expect($c['xpath']->query('.//a[@data-cogito-action][@hidden]', $c['node'])->length)->toBe(1);
});

it('no longer carries the broken 3D mascot', function () {
    $this->get('/')->assertDontSee('model-viewer', false);
    expect(config('election.mascot_path'))->toBeNull();
});

// --- Perbaikan review ---

it('does not tell a logged-in voter to look for an account before voting opens', function () {
    setSchedule('2026-12-01 08:00', '2026-12-01 15:00');
    $this->travelTo(Carbon::parse('2026-11-30 10:00'));

    $c = cogito($this->actingAs(makeUserWithRole('Participant', 'voter1'))->get('/')->getContent());

    expect($c['lines'])->toBe([
        'Halo, voter1!',
        'Pemilihan dibuka Selasa, 1 Desember pukul 08.00 WIB.',
        'Kamu sudah masuk. Surat suara muncul saat pemilihan dibuka.',
    ]);
});

it('does not tell a logged-in voter to look for an account when no schedule is set', function () {
    $c = cogito($this->actingAs(makeUserWithRole('Participant', 'voter1'))->get('/')->getContent());

    expect($c['lines'])->toBe([
        'Halo, voter1!',
        'Jadwal pemilihan belum diumumkan panitia.',
        'Kamu sudah masuk. Surat suara muncul saat pemilihan dibuka.',
    ]);
});

it('uses the participant name when there is one', function () {
    setSchedule('2026-12-01 08:00', '2026-12-01 15:00');
    $this->travelTo(Carbon::parse('2026-12-01 10:00'));
    $voter = makeUserWithRole('Participant', 'voter1');
    $voter->details->update(['participant_name' => 'Rina Ayu']);

    expect(cogito($this->actingAs($voter->fresh())->get('/')->getContent())['lines'][0])->toBe('Halo, Rina Ayu! Surat suaramu sudah menunggu.');
});

it('remembers "Tutup" per situation, so new guidance after logging in or voting is shown', function () {
    setSchedule('2026-12-01 08:00', '2026-12-01 15:00');
    $this->travelTo(Carbon::parse('2026-12-01 10:00'));
    $key = fn ($html) => cogito($html)['node']->getAttribute('data-cogito-key');

    $guest = $key($this->get('/')->getContent());
    $voter = makeUserWithRole('Participant', 'voter1');
    $before = $key($this->actingAs($voter)->get('/')->getContent());
    $voter->update(['status' => 'voted']);
    $after = $key($this->actingAs($voter)->get('/')->getContent());

    expect([$guest, $before, $after])->toBe(['home:open:guest', 'home:open:voter', 'home:open:voted']);
});
