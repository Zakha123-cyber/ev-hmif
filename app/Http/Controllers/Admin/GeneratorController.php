<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CredentialGenerator;

/**
 * Endpoint admin untuk tombol "generate ulang" di form verifikasi peserta.
 */
class GeneratorController extends Controller
{
    public function __construct(private CredentialGenerator $generator)
    {
        $this->middleware(['role:Admin']);
    }

    public function generateUsername()
    {
        return response($this->generator->username(), 200, ['Content-Type' => 'text/plain']);
    }

    public function generatePassword()
    {
        return response($this->generator->password(), 200, ['Content-Type' => 'text/plain']);
    }
}
