<?php

use Illuminate\Support\Carbon;

beforeEach(function () {
    seedRoles();
    makeCandidate(1);
    makeCandidate(2);
});

/**
 * Isi COGITO dari HTML.
 *
 * @return array{texts: string[], lines: array, first: string, firstAction: ?array, node: DOMElement, xpath: DOMXPath}
 */
function cogito(string $html): array
{
    $dom = new DOMDocument();
    @$dom->loadHTML($html);
    $xpath = new DOMXPath($dom);
    $nodes = $xpath->query('//aside[@data-cogito]');
    expect($nodes->length)->toBe(1);
    $node = $nodes->item(0);

    $lines = json_decode($xpath->query('.//script[@data-cogito-lines]', $node)->item(0)->textContent, true);
    $link = $xpath->query('.//a[@data-cogito-action]', $node)->item(0);

    return [
        'texts' => array_column($lines, 'text'),
        'lines' => $lines,
        'first' => trim($xpath->query('.//p[@data-cogito-line]', $node)->item(0)->textContent),
        'firstAction' => $link && ! $link->hasAttribute('hidden')
            ? ['label' => trim($link->textContent), 'href' => $link->getAttribute('href')]
            : null,
        'node' => $node,
        'xpath' => $xpath,
    ];
}

function actionOf(array $c, string $text): ?array
{
    foreach ($c['lines'] as $line) {
        if ($line['text'] === $text) {
            return $line['action'];
        }
    }
    throw new RuntimeException("Kalimat tidak ada: {$text}");
}

function openSchedule(): void
{
    setSchedule('2026-12-01 08:00', '2026-12-01 15:00');
    test()->travelTo(Carbon::parse('2026-12-01 10:00'));
}

it('mixes greetings, account info, guidance and jokes for a guest before the schedule exists', function () {
    $c = cogito($this->get('/')->getContent());

    expect($c['first'])->toBe('Halo! Aku COGITO, maskot HMIF.');
    expect($c['texts'])->toContain(
        'Jadwal pemilihan belum diumumkan. Sabar ya, aku juga menunggu.',
        'Akun memilih dikirim ke email kampusmu (@mail.unej.ac.id). Cek juga folder spam, ya.',
        'Kendala login atau belum terima email akun? Chat panitia lewat WhatsApp di bagian paling bawah.',
        'Bingung pilih siapa? Buka amplop paslon untuk membaca visi dan misinya.',
        'Topi ini bukan cuma gaya. Oke, sebagian besar gaya.',
    );
    expect(count($c['texts']))->toBeGreaterThanOrEqual(6);
});

it('mentions the real opening time before voting opens', function () {
    setSchedule('2026-12-01 08:00', '2026-12-01 15:00');
    $this->travelTo(Carbon::parse('2026-11-30 10:00'));

    expect(cogito($this->get('/')->getContent())['texts'])->toContain('Pemilihan dibuka Selasa, 1 Desember pukul 08.00 WIB. Pasang pengingat, ya!');
});

it('offers guests a line that leads to the login page while voting is open', function () {
    openSchedule();

    $c = cogito($this->get('/')->getContent());

    expect($c['texts'])->toContain('Pemilihan sedang berlangsung sampai Selasa, 1 Desember pukul 15.00 WIB.');
    expect(actionOf($c, 'Sudah punya akun? Masuk dan pilih paslonmu.'))->toBe(['label' => 'Masuk untuk memilih', 'href' => route('login')]);
});

it('opens with the ballot for a logged-in voter who has not voted', function () {
    openSchedule();

    $c = cogito($this->actingAs(makeUserWithRole('Participant', 'voter1'))->get('/')->getContent());

    expect($c['first'])->toBe('Halo, voter1! Surat suaramu sudah menunggu.');
    expect($c['firstAction'])->toBe(['label' => 'Buka surat suara', 'href' => url('/vote')]);
});

it('never tells a logged-in voter to look for an account in their email', function (string $start) {
    if ($start !== 'none') {
        setSchedule($start, '2026-12-01 15:00');
        $this->travelTo(Carbon::parse('2026-11-30 10:00'));
    }

    $c = cogito($this->actingAs(makeUserWithRole('Participant', 'voter1'))->get('/')->getContent());

    expect($c['first'])->toBe('Halo, voter1! Kamu sudah masuk. Surat suara muncul saat pemilihan dibuka.');
    expect(collect($c['texts'])->filter(fn ($t) => str_contains($t, 'email kampusmu'))->all())->toBe([]);
})->with(['upcoming' => '2026-12-01 08:00', 'not set' => 'none']);

it('uses the participant name when there is one', function () {
    openSchedule();
    $voter = makeUserWithRole('Participant', 'voter1');
    $voter->details->update(['participant_name' => 'Rina Ayu']);

    expect(cogito($this->actingAs($voter->fresh())->get('/')->getContent())['first'])->toBe('Halo, Rina Ayu! Surat suaramu sudah menunggu.');
});

it('thanks a voter without ever naming a candidate', function () {
    openSchedule();
    $voter = makeUserWithRole('Participant', 'voter1');
    $voter->update(['status' => 'voted']);

    $c = cogito($this->actingAs($voter)->get('/')->getContent());

    expect($c['first'])->toBe('Terima kasih sudah memilih, voter1!');
    expect($c['texts'])->toContain('Hasil diumumkan setelah pemilihan ditutup Selasa, 1 Desember pukul 15.00 WIB.');
    expect($c['node']->textContent)->not->toContain('Ketua 1')->not->toContain('Paslon 1');
});

it('leads with the results once voting has closed', function () {
    setSchedule('2026-12-01 08:00', '2026-12-01 15:00');
    $this->travelTo(Carbon::parse('2026-12-01 16:00'));

    $c = cogito($this->get('/')->getContent());

    expect($c['first'])->toBe('Pemilihan sudah ditutup. Terima kasih untuk semua yang memilih!');
    expect($c['firstAction'])->toBe(['label' => 'Lihat hasil', 'href' => '#hasil']);
    expect($c['texts'])->not->toContain('Akun memilih dikirim ke email kampusmu (@mail.unej.ac.id). Cek juga folder spam, ya.');
});

it('offers the admin panel and never the login to an admin', function () {
    openSchedule();

    $c = cogito($this->actingAs(makeUserWithRole('Admin', 'admin1'))->get('/')->getContent());

    expect($c['firstAction'])->toBe(['label' => 'Panel admin', 'href' => url('/back-admin-logged/redirect-role')]);
    expect(collect($c['lines'])->pluck('action.label')->filter()->all())->not->toContain('Masuk untuk memilih');
});

it('reads without JavaScript: first line and its action visible, no dead buttons', function () {
    openSchedule();
    $c = cogito($this->actingAs(makeUserWithRole('Participant', 'voter1'))->get('/')->getContent());
    $x = $c['xpath'];

    expect($c['first'])->toBe('Halo, voter1! Surat suaramu sudah menunggu.');
    expect($c['firstAction'])->not->toBeNull();
    expect($x->query('.//button[@data-cogito-close][@hidden]', $c['node'])->length)->toBe(1);
    expect($x->query('.//button[@data-cogito-next]', $c['node'])->length)->toBe(0);
    expect($x->query('.//img[@width="278"][@height="385"][contains(@src,"cogito/salam.webp")]', $c['node'])->length)->toBe(1);
    expect($x->query('.//source[@media="(prefers-reduced-motion: reduce)"][contains(@srcset,"salam-still.webp")]', $c['node'])->length)->toBe(1);
});

it('keeps lines unique, without em dashes, and without screen-reader chatter', function () {
    openSchedule();
    $c = cogito($this->get('/')->getContent());

    expect($c['texts'])->toBe(array_values(array_unique($c['texts'])));
    expect(implode(' ', $c['texts']))->not->toContain('—');
    expect($c['xpath']->query('.//*[@aria-live]', $c['node'])->length)->toBe(0);
});


it('no longer carries the broken 3D mascot', function () {
    $this->get('/')->assertDontSee('model-viewer', false);
    expect(config('election.mascot_path'))->toBeNull();
});

it('greets by name only, never by the generated username', function () {
    openSchedule();
    $voter = makeUserWithRole('Participant', 'acak8xyz');
    $voter->details->update(['participant_name' => '']);

    $c = cogito($this->actingAs($voter->fresh())->get('/')->getContent());

    expect($c['first'])->toBe('Halo! Surat suaramu sudah menunggu.');
    expect(implode(' ', $c['texts']))->not->toContain('acak8xyz');
});
