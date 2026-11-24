<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Imports\ImportParticipant;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Crypt;

use App\Models\Participant;
use App\Models\User;

use App\Http\Controllers\Admin\GeneratorController;
use App\Mail\BroadcastMail;
use Carbon\Exceptions\Exception;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Traits\HasRoles;
use PDF;

class ParticipantController extends Controller
{
    public function __construct()
    {
        $this->middleware(['role:Admin']);
    }
    
    private $param;
    public function index(){
        try {
            $this->param['dataParticipant'] = Participant::all();

            return view('admin.pages.participant.list', $this->param);
        } catch (\Exception $e) {
            return redirect()->back()->withError($e->getMessage());
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->back()->withError('Terjadi kesalahan pada database', $e->getMessage());
        }
    }

    public function add(){
        try {
            return view('admin.pages.participant.add');
        } catch (\Exception $e) {
            return redirect()->back()->withError($e->getMessage());
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->back()->withError('Terjadi kesalahan pada database', $e->getMessage());
        }
    }

    public function broadcast(){
        $participants = Participant::where("status", "not verified")->get();     
        foreach($participants as $participant){
            DB::beginTransaction();
            try{
                $username = (New GeneratorController)->generateUsername();
                $password = (New GeneratorController)->generatePassword();
                $status = "verified";      
                DB::table('participants')
                ->where('id', $participant->id)
                ->update([
                    'username_old' => $username,
                    'password_old' => $password,
                    'status' => $status
                ]);
                $user = User::create([
                    'participant_id' => $participant->id,
                    'username' => $username,
                    'password' => bcrypt($password),
                    'remember_token' => \Str::random(60),
                    'status' => 'not voted',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                event(new Registered($user));
                $user->assignRole('Participant');

                // Mail::to($participant->nim."@mail.unej.ac.id")->queue(new BroadcastMail($participant));
                Mail::to(substr($participant->nim, 0, 2)."241010".substr($participant->nim, 2)."@mail.unej.ac.id")->queue(new BroadcastMail($participant));
                // echo $participant->nim."@mail.unej.ac.id";
                echo substr($participant->nim, 0, 2)."241010".substr($participant->nim, 2)."@mail.unej.ac.id";
                echo $participant->status;
                echo "<br>";
                DB::commit();
            }catch(Exception $e){
                DB::rollBack();
                
            }
        }

        return redirect()->back()->withStatus('Berhasil mengirimkan email.');;
    }

    public function store(Request $request){
        $this->validate($request,
            [
                'participant_name' => 'required',
                'nim' => 'required',
                'whatsapp_number' => 'required',
                'status' => 'required',
            ],
            [
                'required' => ':attribute harus diisi.',
            ],
            [
                'participant_name' => 'Nama Participant',
                'nim' => 'NIM',
                'whatsapp_number' => 'Nomor Whatsapp',
                'status' => 'Status',
            ],
        );
        try {
            $date = date('H-i-s');
            $random = \Str::random(5);

            $participant = new Participant();
            $participant->participant_name = $request->participant_name;
            $participant->nim = $request->nim;
            $participant->whatsapp_number = $request->whatsapp_number;
            $participant->status = $request->status;
            
            if ($request->file('image')) {
                $request->file('image')->move('assets/upload/participant', $date.$random.$request->file('image')->getClientOriginalName());
                $participant->image = $date.$random.$request->file('image')->getClientOriginalName();
            } else {
                $participant->image = 'avatar.png';
            }
            $participant->save();

            return redirect('/back-admin-logged/participant')->withStatus('Berhasil menambah data.');

        } catch (\Exception $e) {
            return redirect()->back()->withError($e->getMessage());
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->back()->withError('Terjadi kesalahan pada database', $e->getMessage());
        }
    }
    
    public function email_backup(Participant $participant){
        $getDetailParticipant = Participant::where('id', '=', $participant->id)->first();
        try {
            if ($getDetailParticipant->email_backup != null) {
                Mail::to($getDetailParticipant->email_backup)->queue(new BroadcastMail($participant));
            } 
            else {
                // dd($getDetailParticipant->email_backup);
                return redirect()->back()->withStatus('Email Backup tidak ditemukan');
            }
            
        } catch (\Throwable $th) {
            return redirect()->back()->withError($th->getMessage());
        }
        return redirect()->back()->withStatus('Berhasil mengirimkan email backup.');
    }

    public function edit(Participant $participant){
        try {
            $this->param['getDetailParticipant'] = Participant::find($participant->id);
            return view('admin.pages.participant.edit', $this->param);
        } catch(\Throwable $e){
            return redirect()->back()->withError($e->getMessage());
        } catch(\Illuminate\Database\QueryException $e){
            return redirect()->back()->withError($e->getMessage());
        }
    }

    public function update(Request $request, Participant $participant){
        $this->validate($request,
            [
                'participant_name' => 'required',
                'nim' => 'required',
                'whatsapp_number' => 'required',
                'status' => 'required',
            ],
            [
                'required' => ':attribute harus diisi.',
            ],
            [
                'participant_name' => 'Nama Participant',
                'nim' => 'NIM',
                'whatsapp_number' => 'Nomor Whatsapp',
                'status' => 'Status',
            ],
        );
        try {
            $date = date('H-i-s');
            $random = \Str::random(5);

            $participant = Participant::find($participant->id);
            $participant->participant_name = $request->participant_name;
            $participant->nim = $request->nim;
            $participant->whatsapp_number = $request->whatsapp_number;
            $participant->status = $request->status;
            
            if ($request->file('image')) {
                $request->file('image')->move('assets/upload/participant', $date.$random.$request->file('image')->getClientOriginalName());
                $participant->image = $date.$random.$request->file('image')->getClientOriginalName();
            }
            
            $participant->save();

            return redirect('/back-admin-logged/participant')->withStatus('Berhasil menambah data.');

        } catch (\Exception $e) {
            return redirect()->back()->withError($e->getMessage());
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->back()->withError('Terjadi kesalahan pada database', $e->getMessage());
        }
    }

    public function destroy(Participant $participant){
        try {
            Participant::find($participant->id)->delete();
            return redirect('/back-admin-logged/participant')->withStatus('Berhasil menghapus data.');
        } catch(\Throwable $e){
            return redirect()->back()->withError($e->getMessage());
        } catch(\Illuminate\Database\QueryException $e){
            return redirect()->back()->withError($e->getMessage());
        }
    }

    public function import(Request $request){
        $this->validate($request,
            [
                'file' => 'required|mimes:csv,xls,xlsx',
            ],
            [
                'required' => ':attribute harus diisi.',
            ],
            [
                'file' => 'File Excel',
            ],
        );
        try {
            $file = $request->file('file');
            $fileName = rand().$file->getClientOriginalName();

            $file->move('files/',$fileName);
            Excel::import(new ImportParticipant, public_path('files/'.$fileName));

            return redirect('/back-admin-logged/participant')->withStatus('Berhasil mengimport data.');
        } catch(\Throwable $e){
            return redirect()->back()->withError($e->getMessage());
        } catch(\Illuminate\Database\QueryException $e){
            return redirect()->back()->withError($e->getMessage());
        }
    }

    public function verification(){
        try {
            $this->param['getParticipant'] = Participant::where('status', 'not verified')->get();
            $this->param['getRandUsername'] = (new GeneratorController)->generateUsername();
            $this->param['getRandPassword'] = (new GeneratorController)->generatePassword();
            
            $this->param['getParticpantVerified'] = Participant::where('status', 'verified')->get();

            return view('admin.pages.participant-verification.list', $this->param);
        } catch (\Exception $e) {
            return redirect()->back()->withError($e->getMessage());
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->back()->withError('Terjadi kesalahan pada database', $e->getMessage());
        }
    }

    public function sendVerified(Request $request){
        $this->validate($request,
            [
                'nim' => 'required',
                'username' => 'required',
                'password' => 'required',
                'whatsapp_number' => 'required',
            ],
            [
                'required' => ':attribute harus diisi.',
            ],
            [
                'nim' => 'Participant',
                'username' => 'Username',
                'password' => 'Password',
                'whatsapp_number' => 'Nomor Whatsapp',
            ],
        );
        try {
            $participant = Participant::find($request->nim);
            $participant->whatsapp_number = $request->whatsapp_number;
            $participant->username_old = $request->username;
            $participant->password_old = $request->password;
            $participant->status = $request->status;
            $participant->save();

            $user = User::create([
                'participant_id' => $request->nim,
                'username' => $request->username,
                'password' => bcrypt($request->password),
                'remember_token' => \Str::random(60),
                'status' => 'not voted',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            event(new Registered($user));
            $user->assignRole('Participant');

            return redirect('/back-admin-logged/participant-verification')->withStatus('Berhasil menambah data.');
        } catch (\Exception $e) {
            return redirect()->back()->withError($e->getMessage());
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->back()->withError('Terjadi kesalahan pada database', $e->getMessage());
        }
    }

    public function editVerification(Participant $participant){
        try {
            $this->param['getParticipant'] = Participant::all();
            $this->param['getDetailParticipant'] = Participant::find($participant->id);
            $this->param['getRandUsername'] = (new GeneratorController)->generateUsername();
            $this->param['getRandPassword'] = (new GeneratorController)->generatePassword();
            
            $this->param['getParticpantVerified'] = Participant::where('status', 'verified')->get();

            return view('admin.pages.participant-verification.edit', $this->param);
        } catch (\Exception $e) {
            return redirect()->back()->withError($e->getMessage());
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->back()->withError('Terjadi kesalahan pada database', $e->getMessage());
        }
    }

    public function updateVerification(Request $request, Participant $participant){
        $this->validate($request,
            [
                'username' => 'required',
                'password' => 'required',
                'whatsapp_number' => 'required',
            ],
            [
                'required' => ':attribute harus diisi.',
            ],
            [
                'username' => 'Username',
                'password' => 'Password',
                'whatsapp_number' => 'Nomor Whatsapp',
            ],
        );
        try {
            $participant = Participant::find($participant->id);
            $participant->whatsapp_number = $request->whatsapp_number;
            $participant->username_old = $request->username;
            $participant->password_old = $request->password;
            $participant->status = $request->status;
            $participant->save();

            $user = User::where('participant_id', $participant->id)->update([
                'username' => $request->username,
                'password' => bcrypt($request->password),
            ]);

            return redirect('/back-admin-logged/participant-verification')->withStatus('Berhasil memperbarui data.');
        } catch (\Exception $e) {
            return redirect()->back()->withError($e->getMessage());
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->back()->withError('Terjadi kesalahan pada database', $e->getMessage());
        }
    }

    public function infoVerification(Participant $participant){
        try {
            $this->param['dataDetailParticipantVerified'] = Participant::find($participant->id);

            return view('admin.pages.participant-verification.info', $this->param);
        } catch (\Exception $e) {
            return redirect()->back()->withError($e->getMessage());
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->back()->withError('Terjadi kesalahan pada database', $e->getMessage());
        }
    }

    public function printVerification(Participant $participant){
        try {
            $random = \Str::random(10);
            $participant = Participant::find($participant->id);

            $pdf = PDF::loadview('admin.pages.participant-verification.print', ['dataDetailParticipantVerified'=>$participant]);
        	return $pdf->stream($random.'-download-kartu-participant.pdf');
            exit(0);
            // return view('admin.pages.participant-verification.print', $this->param);
        } catch (\Exception $e) {
            return redirect()->back()->withError($e->getMessage());
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->back()->withError('Terjadi kesalahan pada database', $e->getMessage());
        }
    }
}
