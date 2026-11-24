<?php

namespace Database\Seeders;

use App\Models\Participant;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Akun admin bawaan, dipasangkan ke peserta admin dari ParticipantSeeder lewat NIM.
     */
    private const ADMINS = [
        '9999999999' => 'superadmin@hmifunej.id',
        '8888888888' => 'admin@hmifunej.id',
    ];

    public function run(): void
    {
        $password = config('election.admin_seed_password');

        if (! $password) {
            $password = Str::password(16, symbols: false);
            $this->command?->warn("ADMIN_SEED_PASSWORD tidak diatur. Password admin acak: {$password}");
            $this->command?->warn('Simpan password ini sekarang — tidak akan ditampilkan lagi.');
        }

        foreach (self::ADMINS as $nim => $username) {
            $admin = User::create([
                'participant_id' => Participant::where('nim', $nim)->value('id'),
                'username' => $username,
                'password' => bcrypt($password),
                'remember_token' => Str::random(60),
                'status' => 'voted',
            ]);
            event(new Registered($admin));
            $admin->assignRole('Admin');
        }
    }
}
