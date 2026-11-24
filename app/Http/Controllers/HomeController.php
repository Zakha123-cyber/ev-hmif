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
        $candidates = Candidate::all();

        $param = [
            'candidates' => $candidates,
            'setting' => $setting,
            'results_visible' => $setting->status() === ElectionSetting::STATUS_CLOSED,
        ];

        // Hasil suara hanya dihitung & dikirim ke view setelah pemilihan ditutup,
        // supaya tidak bisa diintip lewat source halaman.
        if ($param['results_visible']) {
            $param['label_candidates'] = $candidates->map(fn ($c) => 'Paslon ' . $c->id)->all();
            $param['value_candidates'] = $candidates->map(fn ($c) => Count::where('candidate_id', $c->id)->count())->all();
        }

        return view('welcome', $param);
    }
}
