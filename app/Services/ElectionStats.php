<?php

namespace App\Services;

use App\Models\Participant;
use App\Models\User;

/**
 * Statistik pemilihan untuk panel admin. Akun admin (yang juga tercatat sebagai
 * peserta dengan status "voted") tidak ikut dihitung.
 */
class ElectionStats
{
    /** Semua peserta pemilihan, dengan atau tanpa akun. */
    public static function participants(): int
    {
        return Participant::whereDoesntHave('user', fn ($user) => $user->role('Admin'))->count();
    }

    public static function voted(): int
    {
        return User::role('Participant')->where('status', 'voted')->count();
    }

    /** Peserta yang sudah punya akun tetapi belum memilih. */
    public static function notVoted(): int
    {
        return User::role('Participant')->where('status', 'not voted')->count();
    }
}
