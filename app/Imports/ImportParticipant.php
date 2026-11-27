<?php

namespace App\Imports;

use App\Models\Participant;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

/**
 * Format file: kolom A = Nama, kolom B = NIM, kolom C dst. diabaikan.
 * Baris judul (header) dan baris yang benar-benar kosong dilewati diam-diam.
 * Baris tanpa nama atau tanpa NIM angka ditolak dan dilaporkan nomor barisnya.
 *
 * Semua peserta hasil import berstatus "not verified": di aplikasi ini "verified"
 * berarti "sudah dibuatkan akun", dan akun dibuat lewat broadcast / verifikasi manual.
 */
class ImportParticipant implements ToCollection
{
    public int $imported = 0;

    /** Jumlah baris yang NIM-nya sudah terdaftar. */
    public int $skipped = 0;

    /** Nomor baris (1-based, sesuai Excel) yang tidak valid. */
    public array $invalidRows = [];

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 1;
            $name = trim((string) ($row[0] ?? ''));
            $nim = $this->normalizeNim($row[1] ?? null);

            // Baris kosong total (sisa format di bawah tabel) bukan data.
            if ($name === '' && trim((string) ($row[1] ?? '')) === '') {
                continue;
            }

            if ($nim === null) {
                // Baris judul pertama (mis. "Nama | NIM | Status") bukan kesalahan.
                if (! ($rowNumber === 1 && $this->looksLikeHeader($row))) {
                    $this->invalidRows[] = $rowNumber;
                }
                continue;
            }

            // Nama wajib: dipakai di email akun dan sapaan.
            if ($name === '') {
                $this->invalidRows[] = $rowNumber;
                continue;
            }

            // Lewati NIM yang sudah terdaftar (import ulang / baris ganda di file yang sama).
            if (Participant::where('nim', $nim)->exists()) {
                $this->skipped++;
                continue;
            }

            Participant::create([
                'participant_name' => $name,
                'nim' => $nim,
                'status' => 'not verified',
                'image' => 'avatar.png',
            ]);
            $this->imported++;
        }
    }

    /**
     * NIM hanya angka. Sel numerik Excel bisa terbaca sebagai int/float.
     */
    private function normalizeNim(mixed $value): ?string
    {
        if (is_float($value) && floor($value) === $value) {
            $value = number_format($value, 0, '', '');
        }

        $nim = trim((string) $value);

        return preg_match('/^\d+$/', $nim) ? $nim : null;
    }

    private function looksLikeHeader(Collection $row): bool
    {
        return str_contains(strtolower(trim((string) ($row[1] ?? ''))), 'nim');
    }
}
