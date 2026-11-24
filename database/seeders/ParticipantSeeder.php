<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ParticipantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $participants = new \App\Models\Participant();
        $participants->participant_name = 'Admin First';
        $participants->nim = '9999999999';
        $participants->whatsapp_number = '083111547153';
        $participants->username_old = 'secret';
        $participants->password_old = 'secret';
        $participants->status = 'verified';
        $participants->image = 'avatar.png';
        $participants->created_at = now();
        $participants->updated_at = now();
        $participants->save();

        $participants = new \App\Models\Participant();
        $participants->participant_name = 'Admin Second';
        $participants->nim = '8888888888';
        $participants->whatsapp_number = '083111547153';
        $participants->username_old = 'secret';
        $participants->password_old = 'secret';
        $participants->status = 'verified';
        $participants->image = 'avatar.png';
        $participants->created_at = now();
        $participants->updated_at = now();
        $participants->save();

        // $participants = new \App\Models\Participant();
        // $participants->participant_name = 'Users First';
        // $participants->nim = '287918473382';
        // $participants->whatsapp_number = '083856116340';
        // $participants->status = 'not verified';
        // $participants->image = 'avatar.png';
        // $participants->created_at = now();
        // $participants->updated_at = now();
        // $participants->save();

        // $participants = new \App\Models\Participant();
        // $participants->participant_name = 'Users Second';
        // $participants->nim = '129481024839';
        // $participants->whatsapp_number = '083856116340';
        // $participants->status = 'not verified';
        // $participants->image = 'avatar.png';
        // $participants->created_at = now();
        // $participants->updated_at = now();
        // $participants->save();
    }
}
