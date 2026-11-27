<?php

namespace Database\Seeders;

use App\Models\Candidate;
use App\Models\Count;
use App\Models\ElectionSetting;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * Data uji manual (lokal saja):
 *   php artisan db:seed --class=DemoSeeder
 *
 * - admin.demo (password acak, ditampilkan & disimpan di storage/app/demo/akun-uji.txt)
 * - pemilih01-pemilih12: akun aktif, belum memilih, password demo1234
 * - pemilih13-pemilih25: sudah memilih (8 untuk paslon 1, 5 untuk paslon 2)
 * - jadwal: sedang berlangsung (mulai 1 jam lalu, selesai besok)
 *
 * Paslon yang sudah ada di database dipakai apa adanya. Aman dijalankan ulang:
 * akun diperbarui, bukan digandakan; jadwal & password admin.demo di-reset.
 * NIM uji diawali 0000000 agar tidak bentrok dengan NIM mahasiswa sungguhan.
 */
class DemoSeeder extends Seeder
{
    private const VOTER_PASSWORD = 'demo1234';
    private const NOT_VOTED = 12;
    private const VOTED_FOR = [1 => 8, 2 => 5];
    private const CREDENTIALS_FILE = 'demo/akun-uji.txt';

    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->error('DemoSeeder tidak boleh dijalankan di production. Dibatalkan.');

            return;
        }

        $adminPassword = DB::transaction(function () {
            Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
            Role::firstOrCreate(['name' => 'Participant', 'guard_name' => 'web']);

            if (Candidate::count() === 0) {
                $this->call(CandidateSeeder::class);
            }

            $adminPassword = Str::password(12, symbols: false);
            $this->account('000000000000', 'Admin Uji', 'admin.demo', $adminPassword, 'Admin', 'voted');

            $number = 0;
            for ($i = 1; $i <= self::NOT_VOTED; $i++) {
                $this->voter(++$number, 'not voted');
            }

            foreach (self::VOTED_FOR as $pair => $total) {
                $candidate = Candidate::where('candidate_pair', $pair)->firstOrFail();
                for ($i = 1; $i <= $total; $i++) {
                    $user = $this->voter(++$number, 'voted');
                    Count::firstOrCreate(['user_id' => $user->id], ['candidate_id' => $candidate->id]);
                }
            }

            ElectionSetting::current()->fill([
                'vote_start' => now()->subHour(),
                'vote_end' => now()->addDay(),
            ])->save();

            return $adminPassword;
        });

        $this->report($adminPassword);
    }

    private function voter(int $number, string $status): User
    {
        $username = sprintf('pemilih%02d', $number);

        return $this->account(sprintf('0000000%05d', $number), sprintf('Pemilih Uji %02d', $number), $username, self::VOTER_PASSWORD, 'Participant', $status);
    }

    private function account(string $nim, string $name, string $username, string $password, string $role, string $status): User
    {
        $participant = Participant::updateOrCreate(['nim' => $nim], [
            'participant_name' => $name,
            'username_old' => $username,
            'password_old' => $password,
            'status' => 'verified',
            'image' => 'avatar.png',
        ]);

        $user = User::updateOrCreate(['participant_id' => $participant->id], [
            'username' => $username,
            'password' => bcrypt($password),
            'remember_token' => Str::random(60),
            'status' => $status,
        ]);
        $user->syncRoles([$role]);

        return $user;
    }

    private function report(string $adminPassword): void
    {
        $rows = [
            ['admin.demo', $adminPassword, 'Admin'],
            ['pemilih01 - pemilih12', self::VOTER_PASSWORD, 'Peserta, belum memilih'],
            ['pemilih13 - pemilih20', self::VOTER_PASSWORD, 'Peserta, sudah memilih Paslon 1'],
            ['pemilih21 - pemilih25', self::VOTER_PASSWORD, 'Peserta, sudah memilih Paslon 2'],
        ];

        $text = "Akun uji Election Vote (lokal), dibuat " . now()->format('Y-m-d H:i') . "\n"
            . "Jadwal: sedang berlangsung sampai " . now()->addDay()->format('Y-m-d H:i') . " (ubah di panel admin)\n"
            . "Admin masuk lewat /" . config('election.admin_login_path') . " (bukan /login); peserta lewat /login\n\n"
            . collect($rows)->map(fn ($r) => str_pad($r[0], 24) . str_pad($r[1], 16) . $r[2])->implode("\n") . "\n";
        Storage::disk('local')->put(self::CREDENTIALS_FILE, $text);

        $this->command?->table(['Username', 'Password', 'Peran'], $rows);
        $this->command?->info('Admin masuk lewat /' . config('election.admin_login_path') . ' (bukan /login).');
        $this->command?->info('Daftar akun juga disimpan di storage/app/' . self::CREDENTIALS_FILE);
    }
}
