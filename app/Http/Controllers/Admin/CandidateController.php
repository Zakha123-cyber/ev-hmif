<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Candidate;

class CandidateController extends Controller
{
    public function __construct()
    {
        $this->middleware(['role:Admin']);
    }
    
    private $param;
    public function index(){
        try {
            $this->param['dataCandidate'] = Candidate::all();

            return view('admin.pages.candidate.list', $this->param);
        } catch (\Exception $e) {
            return redirect()->back()->withError($e->getMessage());
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->back()->withError('Terjadi kesalahan pada database', $e->getMessage());
        }
    }

    public function add(){
        try {
            return view('admin.pages.candidate.add');
        } catch (\Exception $e) {
            return redirect()->back()->withError($e->getMessage());
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->back()->withError('Terjadi kesalahan pada database', $e->getMessage());
        }
    }

    public function store(Request $request){
        $this->validate($request,
            [
                'candidate_pair' => 'required',
                'leader_name' => 'required',
                'vice_leader_name' => 'required',
                'vision' => 'required',
                'mission' => 'required',
            ],
            [
                'required' => ':attribute harus diisi.',
            ],
            [
                'candidate_pair' => 'Nomor Kandidat',
                'leader_name' => 'Nama Ketua',
                'vice_leader_name' => 'Nama Wakil Ketua',
                'vision' => 'Visi',
                'mission' => 'Misi',
            ],
        );
        try {
            $date = date('H-i-s');
            $random = \Str::random(5);

            $candidate = new Candidate();
            $candidate->candidate_pair = $request->candidate_pair;
            $candidate->leader_name = $request->leader_name;
            $candidate->vice_leader_name = $request->vice_leader_name;
            $candidate->vision = $request->vision;
            $candidate->mission = $request->mission;
            
            if ($request->file('poster')) {
                $request->file('poster')->move('assets/upload/kandidat', $date.$random.$request->file('poster')->getClientOriginalName());
                $candidate->poster = $date.$random.$request->file('poster')->getClientOriginalName();
            } else {
                $candidate->poster = 'default.png';
            }
            $candidate->save();

            return redirect('/back-admin-logged/candidate')->withStatus('Berhasil menambah data.');

        } catch (\Exception $e) {
            return redirect()->back()->withError($e->getMessage());
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->back()->withError('Terjadi kesalahan pada database', $e->getMessage());
        }
    }

    public function edit(Candidate $candidate)
    {
        try {
            $this->param['getDetailCandidate'] = Candidate::find($candidate->id);
            return view('admin.pages.candidate.edit', $this->param);
        } catch(\Throwable $e){
            return redirect()->back()->withError($e->getMessage());
        } catch(\Illuminate\Database\QueryException $e){
            return redirect()->back()->withError($e->getMessage());
        }
    }

    public function update(Request $request, Candidate $candidate){
        $this->validate($request,
            [
                'candidate_pair' => 'required',
                'leader_name' => 'required',
                'vice_leader_name' => 'required',
                'vision' => 'required',
                'mission' => 'required',
            ],
            [
                'required' => ':attribute harus diisi.',
            ],
            [
                'candidate_pair' => 'Nomor Kandidat',
                'leader_name' => 'Nama Ketua',
                'vice_leader_name' => 'Nama Wakil Ketua',
                'vision' => 'Visi',
                'mission' => 'Misi',
            ],
        );
        try {
            $date = date('H-i-s');
            $random = \Str::random(5);

            $candidate = Candidate::find($candidate->id);
            $candidate->candidate_pair = $request->candidate_pair;
            $candidate->leader_name = $request->leader_name;
            $candidate->vice_leader_name = $request->vice_leader_name;
            $candidate->vision = $request->vision;
            $candidate->mission = $request->mission;
            
            if ($request->file('poster')) {
                $request->file('poster')->move('assets/upload/kandidat', $date.$random.$request->file('poster')->getClientOriginalName());
                $candidate->poster = $date.$random.$request->file('poster')->getClientOriginalName();
            }
            
            $candidate->save();

            return redirect('/back-admin-logged/candidate')->withStatus('Berhasil menambah data.');

        } catch (\Exception $e) {
            return redirect()->back()->withError($e->getMessage());
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->back()->withError('Terjadi kesalahan pada database', $e->getMessage());
        }
    }

    public function destroy(Candidate $candidate)
    {
        try {
            Candidate::find($candidate->id)->delete();
            return redirect('/back-admin-logged/candidate')->withStatus('Berhasil menghapus data.');
        } catch(\Throwable $e){
            return redirect()->back()->withError($e->getMessage());
        } catch(\Illuminate\Database\QueryException $e){
            return redirect()->back()->withError($e->getMessage());
        }
    }
}
