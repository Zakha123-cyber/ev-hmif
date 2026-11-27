<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Kontak panitia (WhatsApp). Nomor disimpan seragam: hanya angka, diawali 62.
 */
class ContactPerson extends Model
{
    protected $table = 'contact_persons';

    protected $fillable = ['name', 'whatsapp', 'sort'];

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort')->orderBy('id');
    }

    /**
     * "0838-2309 3973", "+62 838…" atau "62838…" menjadi "6283823093973".
     * Mengembalikan null bila hasilnya bukan nomor Indonesia yang masuk akal.
     */
    public static function normalizeWhatsapp(?string $input): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $input);

        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '62' . $digits;
        }

        return preg_match('/^62\d{8,13}$/', $digits) ? $digits : null;
    }

    /** Nomor untuk dibaca orang: 0838 2309 3973. */
    public function displayPhone(): string
    {
        $local = '0' . substr($this->whatsapp, 2);

        return trim(implode(' ', str_split($local, 4)));
    }

    public function waLink(): string
    {
        return 'https://wa.me/' . $this->whatsapp;
    }
}
