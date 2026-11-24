<?php

namespace App\Http\Controllers;

use App\Models\Count;
use App\Models\Candidate;
use App\Models\ElectionSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        return view($view, [
            'user' => Auth::user(),
            'candidates' => Candidate::all(),
            'setting' => $setting,
        ]);
    }

    public function vote(Request $request)
    {
        $request->validate([
            'candidate_id' => 'required',
        ]);

        Auth::user()->status = 'voted';
        Auth::user()->save();

        $cek = Count::where('user_id', Auth::user()->id)->exists();

        if (!$cek) {
            Count::create([
                'candidate_id' => $request->candidate_id,
                'user_id' => Auth::user()->id,
            ]);
            return redirect('/vote');
        } else {
            return redirect('/vote');
        }

    }
}
