<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Candidate;
use App\Models\Participant;
use App\Services\ElectionStats;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['role:Admin']);
    }
    
    private $param;
    public function index(){
        try {
            $this->param['countAdmin'] = User::whereHas('roles', function($thisRole){
                $thisRole->where('name', 'Admin');
            })->count();
            
            $this->param['countParticipant'] = ElectionStats::participants();
            $this->param['countCandidate'] = Candidate::count();
            $this->param['countVoted'] = ElectionStats::voted();
            $this->param['countNotVoted'] = ElectionStats::notVoted();

            $this->param['getLiveCount'] = \DB::table('counts')
                                            ->select(\DB::raw('count(counts.candidate_id) as total_suara'), 'candidates.leader_name as nama_ketua')
                                            ->join('candidates', 'candidates.id', '=', 'counts.candidate_id')
                                            ->groupBy('counts.candidate_id')
                                            ->get();
            // dd($this->param['getLiveCount']);

            return view('admin.pages.dashboard.dashboard', $this->param);
        } catch (\Exception $e) {
            return redirect()->back()->withError($e->getMessage());
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->back()->withError('Terjadi kesalahan pada database', $e->getMessage());
        }
    }
}
