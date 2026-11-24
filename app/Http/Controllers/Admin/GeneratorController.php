<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;

class GeneratorController extends Controller
{
    // Tanpa karakter yang mirip (0/O, 1/l/I) agar mudah diketik ulang dari email.
    private const ALPHABET = '23456789abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';
    private const LENGTH = 8;

    /**
     * String acak yang aman secara kriptografis (random_int, bukan rand()).
     */
    private function randomString(): string
    {
        $max = strlen(self::ALPHABET) - 1;
        $result = '';
        for ($i = 0; $i < self::LENGTH; $i++) {
            $result .= self::ALPHABET[random_int(0, $max)];
        }

        return $result;
    }

    public function generateUsername(){
        $randomString = $this->randomString();

        if($randomString == User::where('username', $randomString)->first()){
            $this->generateUsername();
        } else {
            return $randomString;
        }
    }

    public function generatePassword(){
        $randomString = $this->randomString();

        if($randomString == User::where('username', $randomString)->first()){
            $this->generatePassword();
        } else {
            return $randomString;
        }
    }
}
