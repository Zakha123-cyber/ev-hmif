<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Traits\HasRoles;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = User::create([
            'participant_id' => 1,
            'username' => 'superadmin@hmifunej.id',
            'password' => bcrypt('JayalahHimpunanku'),
            'remember_token' => \Str::random(60),
            'status' => 'voted',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        event(new Registered($admin));
        $admin->assignRole('Admin');

        $admin = User::create([
            'participant_id' => 2,
            'username' => 'admin@hmifunej.id',
            'password' => bcrypt('JayalahHimpunanku'),
            'remember_token' => \Str::random(60),
            'status' => 'voted',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        event(new Registered($admin));
        $admin->assignRole('Admin');

        // $partisipan = User::create([
        //     'participant_id' => 3,
        //     'username' => 'alif@gmail.com',
        //     'password' => bcrypt('12345678'),
        //     'remember_token' => \Str::random(60),
        //     'status' => 'not voted',
        //     'created_at' => now(),
        //     'updated_at' => now()
        // ]);
        // event(new Registered($partisipan));
        // $partisipan->assignRole('Participant');
    }
}
