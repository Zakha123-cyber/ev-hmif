<?php

namespace App\Http\Controllers;

use App\Models\Count;
use App\Models\Candidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class VoteController extends Controller
{
    public function __construct()
    {
        $this->middleware(['role:Participant']);
    }

    // public function vote_page()
    // {
    //     $now = Carbon::now();

    //     $this->param['user'] = Auth::user();
    //     $this->param['candidates'] = Candidate::all();

    //     if ($now->gte(Carbon::createFromTime(05, 0, 0))) {
    //         return view('vote_closed', $this->param);
    //     }

    //     return view('vote_page', $this->param);
    // }

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
