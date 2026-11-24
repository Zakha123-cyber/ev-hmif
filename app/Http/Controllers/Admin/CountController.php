<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Participant;
use App\Services\ElectionStats;

class CountController extends Controller
{
    public function __construct()
    {
        $this->middleware(['role:Admin']);
    }
    
    private $param;
    public function index(){
        try {
            $this->param['getLiveCount'] = \DB::table('counts')
            ->select(\DB::raw('count(counts.candidate_id) as total_suara'), 'candidates.leader_name as nama_ketua')
            ->join('candidates', 'candidates.id', '=', 'counts.candidate_id')
            ->groupBy('counts.candidate_id')
            ->get();
            $this->param['countVoted'] = ElectionStats::voted();
            $this->param['countNotVoted'] = ElectionStats::notVoted();
            $this->param['countParticipant'] = ElectionStats::participants();
            
            return view('admin.pages.count.list', $this->param);
        } catch (\Exception $e) {
            return redirect()->back()->withError($e->getMessage());
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->back()->withError('Terjadi kesalahan pada database', $e->getMessage());
        }
    }
}
