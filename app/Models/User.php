<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'participant_id',
        'username',
        'password',
        'remember_token',
        'status',
    ];
    
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function details(){
        return $this->belongsTo('App\Models\Participant', 'participant_id', 'id');
    }
}
