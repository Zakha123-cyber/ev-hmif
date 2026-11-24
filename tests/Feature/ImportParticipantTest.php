<?php

use App\Models\Participant;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

beforeEach(function () {
    seedRoles();
    $this->admin = makeUserWithRole('Admin', 'admin1');
    $this->storedBefore = storedSpreadsheets();
});

afterEach(function () {
    // Bersihkan file yang (seharusnya tidak) tertinggal
    foreach (array_diff(storedSpreadsheets(), $this->storedBefore) as $path) {
        @unlink($path);
    }
});

/**
 * Lokasi tempat controller lama menyalin file upload: public/files/ dan,
 * karena path relatif, files/ di working directory.
 */
function storedSpreadsheets(): array
{
    return array_merge(glob(public_path('files/*')) ?: [], glob(base_path('files/*')) ?: []);
}

/**
 * Peserta hasil import (peserta admin dari makeUserWithRole() sudah punya akun).
 */
function imported()
{
    return Participant::whereDoesntHave('user')->orderBy('id');
}

function excelUpload(array $rows): UploadedFile
{
    $spreadsheet = new Spreadsheet;
    $spreadsheet->getActiveSheet()->fromArray($rows, null, 'A1', true);
    $base = tempnam(sys_get_temp_dir(), 'peserta');
    unlink($base);
    $path = $base . '.xlsx';
    (new Xlsx($spreadsheet))->save($path);

    return new UploadedFile($path, 'Data Peserta HMIF.xlsx', null, null, true);
}

function importExcel($test, array $rows)
{
    return $test->actingAs($test->admin)
        ->post('/back-admin-logged/participant/import', ['file' => excelUpload($rows)]);
}

it('skips a header row', function () {
    importExcel($this, [
        ['Nama', 'NIM', 'Status'],
        ['Budi', '2210001', 'not verified'],
    ])->assertSessionHasNoErrors();

    expect(imported()->count())->toBe(1);
    expect(imported()->first()->nim)->toBe('2210001');
});

it('skips rows without a valid NIM and reports them', function () {
    importExcel($this, [
        ['Budi', '2210001', 'not verified'],
        ['Tanpa NIM', '', 'not verified'],
        ['NIM Aneh', 'abc-123', 'not verified'],
    ])->assertSessionHas('error', fn ($m) => str_contains($m, '2') && str_contains($m, '3'));

    expect(imported()->pluck('nim')->all())->toBe(['2210001']);
});

it('stores numeric NIM cells as plain digits', function () {
    importExcel($this, [
        ['Budi', 222410101001, 'not verified'],
    ]);

    expect(imported()->first()->nim)->toBe('222410101001');
});

it('imports every participant as not verified so the broadcast can create their account', function () {
    importExcel($this, [
        ['Budi', '2210001', 'verified'],
        ['Sari', '2210002', ''],
        ['Andi', '2210003', ' Not Verified '],
    ]);

    expect(imported()->pluck('status')->unique()->values()->all())->toBe(['not verified']);
});

it('trims whitespace around names and NIMs', function () {
    importExcel($this, [
        ['  Budi Santoso  ', ' 2210001 ', 'not verified'],
    ]);

    $participant = imported()->first();
    expect($participant->participant_name)->toBe('Budi Santoso');
    expect($participant->nim)->toBe('2210001');
});

it('reports how many participants were imported and skipped', function () {
    makeUserWithRole('Participant', 'lama'); // peserta lama dengan NIM acak
    Participant::create(['participant_name' => 'Sudah Ada', 'nim' => '2210001', 'status' => 'not verified']);

    importExcel($this, [
        ['Sudah Ada', '2210001', 'not verified'],
        ['Baru', '2210002', 'not verified'],
    ])->assertSessionHas('status', fn ($m) => str_contains($m, '1 peserta diimport') && str_contains($m, '1 dilewati'));
});

it('does not keep a copy of the uploaded spreadsheet on the server', function () {
    importExcel($this, [
        ['Budi', '2210001', 'not verified'],
    ]);

    expect(array_diff(storedSpreadsheets(), $this->storedBefore))->toBeEmpty();
});
