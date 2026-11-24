<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;

class Count extends Model
{
    use HasFactory, HasRoles;
    protected $table = 'counts';
    protected $primaryKey = 'id';
    protected $fillable = [
        'candidate_id',
        'user_id',
    ];
}
