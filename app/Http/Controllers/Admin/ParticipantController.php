<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Imports\ImportParticipant;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Crypt;

use App\Models\Count;
use App\Models\Participant;
use App\Models\User;

use App\Services\CredentialGenerator;
use App\Mail\BroadcastMail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
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

    // SVG sengaja tidak diizinkan (bisa berisi JavaScript).
    private const IMAGE_RULES = 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240';

    /**
     * Simpan foto dengan nama acak (bukan nama asli dari pengguna) di folder public.
     */
    private function storeImage(Request $request): string
    {
        $file = $request->file('image');
        $name = $file->hashName();
        $file->move(public_path('assets/upload/participant'), $name);

        return $name;
    }
    
    private $param;
    public function index(){
        try {
            $this->param['dataParticipant'] = Participant::whereDoesntHave('user', fn ($user) => $user->role('Admin'))->get(); // akun admin bukan peserta (dan jangan sampai terhapus dari sini)

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
        // Cegah dua broadcast berjalan bersamaan (klik ganda / dua admin sekaligus).
        $lock = Cache::lock('participant-broadcast', 600);

        if (! $lock->get()) {
            return redirect()->back()->withError('Pengiriman email sedang berjalan. Tunggu hingga selesai.');
        }

        try {
            return $this->runBroadcast();
        } finally {
            $lock->release();
        }
    }

    private function runBroadcast(){
        // Hanya peserta yang belum punya akun: mencegah akun kedua untuk orang yang sama.
        $participants = Participant::where("status", "not verified")->whereDoesntHave('user')->orderBy('id')->get();

        // Pengiriman email sinkron bisa lama untuk banyak peserta.
        @set_time_limit(0);

        $generator = new CredentialGenerator;
        $succeeded = 0;
        $failed = [];

        foreach ($participants as $participant) {
            try {
                // Satu transaksi per peserta: jika akun/email gagal, peserta ini di-rollback
                // penuh (tetap "not verified") sehingga bisa dicoba lagi, peserta lain tetap jalan.
                DB::transaction(function () use ($participant, $generator) {
                    $username = $generator->username();
                    $password = $generator->password();

                    $participant->update([
                        'username_old' => $username,
                        'password_old' => $password,
                        'status' => 'verified',
                    ]);

                    $user = User::create([
                        'participant_id' => $participant->id,
                        'username' => $username,
                        'password' => bcrypt($password),
                        'remember_token' => \Str::random(60),
                        'status' => 'not voted',
                    ]);
                    event(new Registered($user));
                    $user->assignRole('Participant');

                    Mail::to($this->campusEmail($participant))->queue(new BroadcastMail($participant));
                });
                $succeeded++;
            } catch (\Throwable $e) {
                $participant->refresh();
                report($e);
                $failed[] = $participant->nim;
            }
        }

        $response = redirect()->back()->withStatus("Akun dibuat & email dikirim untuk {$succeeded} peserta.");

        if ($failed) {
            $response->withError(count($failed) . ' peserta gagal diproses (bisa dicoba lagi): ' . implode(', ', $failed));
        }

        return $response;
    }

    /**
     * Alamat email kampus dari NIM peserta.
     */
    private function campusEmail(Participant $participant): string
    {
        return substr($participant->nim, 0, 2) . '241010' . substr($participant->nim, 2) . '@mail.unej.ac.id';
    }

    public function store(Request $request){
        $this->validate($request,
            [
                'participant_name' => 'required',
                'nim' => 'required|unique:participants,nim',
                'whatsapp_number' => 'required',
                'status' => 'required',
                'image' => self::IMAGE_RULES,
                'email_backup' => 'nullable|email',
            ],
            [
                'required' => ':attribute harus diisi.',
                'unique' => ':attribute sudah terdaftar.',
                'image' => ':attribute harus berupa gambar.',
                'mimes' => ':attribute harus berformat JPG, PNG, atau WEBP.',
                'max' => ':attribute maksimal 10 MB.',
                'email' => ':attribute harus berupa alamat email yang valid.',
            ],
            [
                'participant_name' => 'Nama Participant',
                'nim' => 'NIM',
                'whatsapp_number' => 'Nomor Whatsapp',
                'status' => 'Status',
                'image' => 'Foto',
                'email_backup' => 'Email Backup',
            ],
        );
        try {
            $participant = new Participant();
            $participant->participant_name = $request->participant_name;
            $participant->nim = $request->nim;
            $participant->whatsapp_number = $request->whatsapp_number;
            $participant->email_backup = $request->email_backup;
            $participant->status = $request->status;
            
            if ($request->file('image')) {
                $participant->image = $this->storeImage($request);
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
        if (! $participant->email_backup) {
            return redirect()->back()->withError('Email backup peserta ini belum diisi. Isi lewat menu edit peserta.');
        }

        if (! $participant->user()->exists()) {
            return redirect()->back()->withError('Peserta ini belum memiliki akun, jadi belum ada yang bisa dikirim.');
        }

        try {
            Mail::to($participant->email_backup)->queue(new BroadcastMail($participant));
        } catch (\Throwable $th) {
            report($th);
            return redirect()->back()->withError('Gagal mengirim email backup: ' . $th->getMessage());
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
                'nim' => ['required', Rule::unique('participants', 'nim')->ignore($participant->id)],
                'whatsapp_number' => 'required',
                'status' => 'required',
                'image' => self::IMAGE_RULES,
                'email_backup' => 'nullable|email',
            ],
            [
                'required' => ':attribute harus diisi.',
                'unique' => ':attribute sudah terdaftar.',
                'image' => ':attribute harus berupa gambar.',
                'mimes' => ':attribute harus berformat JPG, PNG, atau WEBP.',
                'max' => ':attribute maksimal 10 MB.',
                'email' => ':attribute harus berupa alamat email yang valid.',
            ],
            [
                'participant_name' => 'Nama Participant',
                'nim' => 'NIM',
                'whatsapp_number' => 'Nomor Whatsapp',
                'status' => 'Status',
                'image' => 'Foto',
                'email_backup' => 'Email Backup',
            ],
        );
        try {
            $participant = Participant::find($participant->id);
            $participant->participant_name = $request->participant_name;
            $participant->nim = $request->nim;
            $participant->whatsapp_number = $request->whatsapp_number;
            $participant->email_backup = $request->email_backup;
            $participant->status = $request->status;
            
            if ($request->file('image')) {
                $participant->image = $this->storeImage($request);
            }
            
            $participant->save();

            return redirect('/back-admin-logged/participant')->withStatus('Berhasil memperbarui data.');

        } catch (\Exception $e) {
            return redirect()->back()->withError($e->getMessage());
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->back()->withError('Terjadi kesalahan pada database', $e->getMessage());
        }
    }

    public function destroy(Participant $participant){
        if (Count::whereIn('user_id', $participant->user()->select('id'))->exists()) {
            return redirect()->back()->withError('Peserta ini sudah memberikan suara sehingga tidak dapat dihapus.');
        }

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
                'mimes' => ':attribute harus berformat .xlsx, .xls, atau .csv.',
            ],
            [
                'file' => 'File Excel',
            ],
        );
        try {
            // Baca langsung dari file sementara upload: file berisi NIM peserta,
            // jadi jangan disalin ke folder public.
            $import = new ImportParticipant;
            DB::transaction(fn () => Excel::import($import, $request->file('file')));

            $response = redirect('/back-admin-logged/participant')
                ->withStatus("{$import->imported} peserta diimport, {$import->skipped} dilewati (NIM sudah terdaftar).");

            if ($import->invalidRows) {
                $response->withError(count($import->invalidRows) . ' baris tidak valid (NIM kosong/bukan angka) di baris: ' . implode(', ', $import->invalidRows));
            }

            return $response;
        } catch(\Throwable $e){
            return redirect()->back()->withError($e->getMessage());
        } catch(\Illuminate\Database\QueryException $e){
            return redirect()->back()->withError($e->getMessage());
        }
    }

    public function verification(){
        try {
            $this->param['getParticipant'] = Participant::where('status', 'not verified')->get();
            $this->param['getRandUsername'] = (new CredentialGenerator)->username();
            $this->param['getRandPassword'] = (new CredentialGenerator)->password();
            
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
                'nim' => 'required|exists:participants,id',
                'username' => 'required',
                'password' => 'required',
                'whatsapp_number' => 'required',
            ],
            [
                'required' => ':attribute harus diisi.',
                'exists' => ':attribute tidak ditemukan.',
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

            if ($participant->user()->exists()) {
                return redirect()->back()->withError('Peserta ini sudah memiliki akun. Gunakan menu edit untuk mengubah akunnya.');
            }

            DB::transaction(function () use ($participant, $request) {
                $participant->whatsapp_number = $request->whatsapp_number;
                $participant->username_old = $request->username;
                $participant->password_old = $request->password;
                // Peserta yang sudah dibuatkan akun selalu "verified" agar broadcast tidak membuat akun kedua.
                $participant->status = 'verified';
                $participant->save();

                $user = User::create([
                    'participant_id' => $participant->id,
                    'username' => $request->username,
                    'password' => bcrypt($request->password),
                    'remember_token' => \Str::random(60),
                    'status' => 'not voted',
                ]);
                event(new Registered($user));
                $user->assignRole('Participant');
            });

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
            $this->param['getRandUsername'] = (new CredentialGenerator)->username();
            $this->param['getRandPassword'] = (new CredentialGenerator)->password();
            
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
