<?php

namespace App\Services;

use App\Models\Candidate;
use App\Models\Count;
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

    /**
     * Perolehan suara semua paslon (yang belum mendapat suara tetap tercantum),
     * urut nomor pasangan.
     *
     * @return array{results: \Illuminate\Support\Collection, totalVotes: int}
     */
    public static function results(): array
    {
        $votes = Count::selectRaw('candidate_id, COUNT(*) as total')->groupBy('candidate_id')->pluck('total', 'candidate_id');
        $totalVotes = (int) $votes->sum();

        $results = Candidate::orderBy('candidate_pair')->get()->map(fn (Candidate $candidate) => [
            'candidate' => $candidate,
            'votes' => $count = (int) ($votes[$candidate->id] ?? 0),
            'percent' => $totalVotes > 0 ? (int) round($count / $totalVotes * 100) : 0,
        ]);

        return ['results' => $results, 'totalVotes' => $totalVotes];
    }
}
