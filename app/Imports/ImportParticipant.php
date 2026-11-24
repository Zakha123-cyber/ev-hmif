<?php

namespace App\Imports;

use App\Models\Participant;
use Maatwebsite\Excel\Concerns\ToModel;

class ImportParticipant implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Participant([
            'participant_name' => $row[0],
            'nim' => $row[1],
            // 'whatsapp_number' => $row[2],
            'status' => $row[2],
            'image' => 'avatar.png',
        ]);
    }
}
