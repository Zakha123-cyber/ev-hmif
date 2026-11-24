<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;

class Candidate extends Model
{
    use HasFactory, HasRoles;
    protected $table = 'candidates';
    protected $primaryKey = 'id';

    /**
     * Misi dipecah per kalimat (titik diikuti spasi/baris baru), tanpa butir kosong.
     * Singkatan seperti "S.Kom" tetap utuh karena titiknya tidak diikuti spasi.
     *
     * @return array<string>
     */
    public function missionItems(): array
    {
        return collect(preg_split('/\.\s+/', (string) $this->mission))
            // Nomor daftar yang diketik admin ("1." / "2)") bukan isi misi.
            ->map(fn ($item) => rtrim(preg_replace('/^\d+[.)]\s*/', '', trim($item)), '.'))
            ->filter(fn ($item) => $item !== '' && ! ctype_digit($item))
            ->map(fn ($item) => $item . '.')
            ->values()
            ->all();
    }
}
