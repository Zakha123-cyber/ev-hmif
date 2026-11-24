<?php

namespace App\Http\Middleware;

use App\Models\ElectionSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tolak pengiriman suara di luar jadwal pemilihan yang diatur admin.
 */
class EnsureVotingOpen
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! ElectionSetting::current()->isOpen()) {
            return redirect('/vote')->withError('Pemilihan sedang tidak dibuka.');
        }

        return $next($request);
    }
}
