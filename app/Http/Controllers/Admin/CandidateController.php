<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Candidate;
use App\Models\Count;

class CandidateController extends Controller
{
    // SVG sengaja tidak diizinkan (bisa berisi JavaScript).
    private const POSTER_RULES = 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240';

    public function __construct()
    {
        $this->middleware(['role:Admin']);
    }

    /**
     * Simpan poster dengan nama acak (bukan nama asli dari pengguna) di folder public.
     */
    private function storePoster(Request $request): string
    {
        $file = $request->file('poster');
        $name = $file->hashName();
        $file->move(public_path('assets/upload/kandidat'), $name);

        return $name;
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
                'poster' => self::POSTER_RULES,
            ],
            [
                'required' => ':attribute harus diisi.',
                'image' => ':attribute harus berupa gambar.',
                'mimes' => ':attribute harus berformat JPG, PNG, atau WEBP.',
                'max' => ':attribute maksimal 10 MB.',
            ],
            [
                'candidate_pair' => 'Nomor Kandidat',
                'leader_name' => 'Nama Ketua',
                'vice_leader_name' => 'Nama Wakil Ketua',
                'vision' => 'Visi',
                'mission' => 'Misi',
                'poster' => 'Poster',
            ],
        );
        try {
            $candidate = new Candidate();
            $candidate->candidate_pair = $request->candidate_pair;
            $candidate->leader_name = $request->leader_name;
            $candidate->vice_leader_name = $request->vice_leader_name;
            $candidate->vision = $request->vision;
            $candidate->mission = $request->mission;
            
            if ($request->file('poster')) {
                $candidate->poster = $this->storePoster($request);
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
                'poster' => self::POSTER_RULES,
            ],
            [
                'required' => ':attribute harus diisi.',
                'image' => ':attribute harus berupa gambar.',
                'mimes' => ':attribute harus berformat JPG, PNG, atau WEBP.',
                'max' => ':attribute maksimal 10 MB.',
            ],
            [
                'candidate_pair' => 'Nomor Kandidat',
                'leader_name' => 'Nama Ketua',
                'vice_leader_name' => 'Nama Wakil Ketua',
                'vision' => 'Visi',
                'mission' => 'Misi',
                'poster' => 'Poster',
            ],
        );
        try {
            $candidate = Candidate::find($candidate->id);
            $candidate->candidate_pair = $request->candidate_pair;
            $candidate->leader_name = $request->leader_name;
            $candidate->vice_leader_name = $request->vice_leader_name;
            $candidate->vision = $request->vision;
            $candidate->mission = $request->mission;
            
            if ($request->file('poster')) {
                $candidate->poster = $this->storePoster($request);
            }
            
            $candidate->save();

            return redirect('/back-admin-logged/candidate')->withStatus('Berhasil memperbarui data.');

        } catch (\Exception $e) {
            return redirect()->back()->withError($e->getMessage());
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->back()->withError('Terjadi kesalahan pada database', $e->getMessage());
        }
    }

    public function destroy(Candidate $candidate)
    {
        if (Count::where('candidate_id', $candidate->id)->exists()) {
            return redirect()->back()->withError('Kandidat ini sudah memiliki suara sehingga tidak dapat dihapus.');
        }

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
