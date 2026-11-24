<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Count;
use App\Models\ElectionSetting;

class HomeController extends Controller
{
    public function index()
    {
        $setting = ElectionSetting::current();
        $status = $setting->status();
        $candidates = Candidate::orderBy('candidate_pair')->get();

        $results = null;
        $totalVotes = null;

        // Hasil suara hanya dihitung & dikirim ke view setelah pemilihan ditutup,
        // supaya tidak bisa diintip lewat source halaman.
        if ($status === ElectionSetting::STATUS_CLOSED) {
            $votes = Count::selectRaw('candidate_id, COUNT(*) as total')
                ->groupBy('candidate_id')
                ->pluck('total', 'candidate_id');
            $totalVotes = (int) $votes->sum();

            $results = $candidates->map(function (Candidate $candidate) use ($votes, $totalVotes) {
                $count = (int) ($votes[$candidate->id] ?? 0);

                return [
                    'candidate' => $candidate,
                    'votes' => $count,
                    'percent' => $totalVotes > 0 ? (int) round($count / $totalVotes * 100) : 0,
                ];
            })->all();
        }

        return view('welcome', [
            'setting' => $setting,
            'status' => $status,
            'candidates' => $candidates,
            'results' => $results,
            'totalVotes' => $totalVotes,
            'mascotUrl' => $this->mascotUrl(),
        ]);
    }

    /**
     * URL maskot COGITO, hanya jika file .glb sudah ditaruh di folder public.
     */
    private function mascotUrl(): ?string
    {
        $path = config('election.mascot_path');

        return $path && file_exists(public_path($path)) ? asset($path) : null;
    }
}
