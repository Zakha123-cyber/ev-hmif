<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // Panel admin tidak menampakkan diri ke tamu: 404, bukan arahan ke halaman login.
        if ($request->is('back-admin-logged/*') && ! $request->is('back-admin-logged/redirect-role')) {
            abort(404);
        }

        return $request->expectsJson() ? null : route('login');
    }
}
