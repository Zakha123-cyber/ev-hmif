<?php

namespace App\Http\Controllers;

use App\Models\Count;
use App\Models\Candidate;
use App\Models\ElectionSetting;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VoteController extends Controller
{
    public function __construct()
    {
        $this->middleware(['role:Participant']);
    }

    public function vote_page()
    {
        $setting = ElectionSetting::current();

        $view = match ($setting->status()) {
            ElectionSetting::STATUS_OPEN => 'vote_page',
            ElectionSetting::STATUS_CLOSED => 'vote_closed',
            default => 'vote_prepare',
        };

        $user = Auth::user();

        // no-store: tombol Kembali selalu meminta ulang ke server, jadi di komputer
        // bersama surat suara yang sudah dicoblos orang lain tidak muncul lagi.
        return response()->view($view, [
            'user' => $user,
            'candidates' => Candidate::orderBy('candidate_pair')->get(),
            'setting' => $setting,
            // Untuk tanda terima: kapan suara masuk, tanpa menyebut pilihannya.
            'votedAt' => Count::where('user_id', $user->id)->value('created_at'),
            'justVoted' => (bool) session('just_voted'),
        ])->header('Cache-Control', 'no-store, private');
    }

    public function vote(Request $request)
    {
        $request->validate(
            ['candidate_id' => 'required|integer|exists:candidates,id'],
            [
                'required' => 'Pilih salah satu kandidat.',
                'integer' => 'Kandidat tidak valid.',
                'exists' => 'Kandidat tidak valid.',
            ],
        );

        $recorded = false;

        try {
            DB::transaction(function () use ($request, &$recorded) {
                // Kunci baris user agar dua request bersamaan dari akun yang sama diproses bergantian.
                $user = User::whereKey(Auth::id())->lockForUpdate()->first();

                if (! Count::where('user_id', $user->id)->exists()) {
                    // Simpan suara dulu, baru tandai "voted" — keduanya dalam satu transaksi.
                    Count::create([
                        'candidate_id' => $request->candidate_id,
                        'user_id' => $user->id,
                    ]);
                    $recorded = true;
                }

                $user->status = 'voted';
                $user->save();
            });
        } catch (UniqueConstraintViolationException) {
            // Kalah balapan dengan request lain yang sudah menyimpan suara: anggap sudah memilih.
            User::whereKey(Auth::id())->update(['status' => 'voted']);
        }

        // Hanya suara yang baru tersimpan memutar animasi surat masuk kotak.
        return $recorded ? redirect('/vote')->with('just_voted', true) : redirect('/vote');
    }
}
