<?php

namespace App\Services;

use App\Models\User;

/**
 * Membuat username & password peserta.
 */
class CredentialGenerator
{
    // Tanpa karakter yang mirip (0/O, 1/l/I) agar mudah diketik ulang dari email.
    private const ALPHABET = '23456789abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';
    private const LENGTH = 8;

    /**
     * Username acak yang belum dipakai akun mana pun.
     */
    public function username(): string
    {
        do {
            $username = $this->randomString();
        } while (User::where('username', $username)->exists());

        return $username;
    }

    public function password(): string
    {
        return $this->randomString();
    }

    /**
     * String acak yang aman secara kriptografis (random_int).
     */
    protected function randomString(): string
    {
        $max = strlen(self::ALPHABET) - 1;
        $result = '';
        for ($i = 0; $i < self::LENGTH; $i++) {
            $result .= self::ALPHABET[random_int(0, $max)];
        }

        return $result;
    }
}
