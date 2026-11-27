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

            $this->param += ElectionStats::results();
            $this->param['setting'] = \App\Models\ElectionSetting::current();

            return view('admin.pages.dashboard.dashboard', $this->param);
        } catch (\Exception $e) {
            return redirect()->back()->withError($e->getMessage());
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->back()->withError('Terjadi kesalahan pada database', $e->getMessage());
        }
    }
}
