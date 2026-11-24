<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;

class Participant extends Model
{
    use HasFactory, HasRoles;
    protected $table = 'participants';
    protected $primaryKey = 'id';
    protected $fillable = ['participant_name', 'nim', 'whatsapp_number', 'status', 'image'];

    public function user(){
        return $this->hasOne('App\Models\User', 'participant_id', 'id');
    }
}
