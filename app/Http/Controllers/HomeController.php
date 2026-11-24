<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Candidate;
use App\Models\Count;

class HomeController extends Controller
{
    private $param;
    public function index(){
        
        $candidates = Candidate::all();
        $count_candidates = Candidate::all()->count();
        // $counts = Count::select('candidate_id', Count::raw('count(*) as count'))
        //     ->groupBy('candidate_id')
        //     ->get();

        // $this->param = [];
        
        // foreach ($counts as $count) {
        //     $this->param['paslon' . $count->candidate_id] = $count->count;
        // }

        $label_candidates = [];

        foreach ($candidates as $candidate) {
            array_push($label_candidates, "Paslon ".$candidate->id);
        }

        $value_candidates = [];

        foreach($candidates as $candidate) {
            $count = Count::where('candidate_id', $candidate->id)->count();
            array_push($value_candidates, $count);
        }
            

        $this->param['value_candidates'] = $value_candidates;
        $this->param['label_candidates'] = $label_candidates;
        $this->param['candidates'] = $candidates;
        // $this->param['counts'] = $counts;
        
        return view('welcome', $this->param);
    }
}
