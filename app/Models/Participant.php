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
    protected $fillable = ['participant_name', 'nim', 'whatsapp_number', 'email_backup', 'username_old', 'password_old', 'status', 'image'];

    public function user(){
        return $this->hasOne('App\Models\User', 'participant_id', 'id');
    }

    /** Kode prodi Informatika di NIM lengkap (2 digit angkatan + 241010 + nomor urut). */
    private const PRODI_CODE = '241010';

    /**
     * NIM lengkap untuk alamat email kampus. Data peserta bisa berisi NIM pendek
     * (angkatan + nomor urut, mis. 233061) atau NIM lengkap (232410103061);
     * keduanya menghasilkan NIM lengkap yang sama.
     */
    public function campusNim(): string
    {
        $nim = (string) $this->nim;

        if (preg_match('/^\d{2}' . self::PRODI_CODE . '\d+$/', $nim)) {
            return $nim;
        }

        return substr($nim, 0, 2) . self::PRODI_CODE . substr($nim, 2);
    }

    public function campusEmail(): string
    {
        return $this->campusNim() . '@mail.unej.ac.id';
    }
}
