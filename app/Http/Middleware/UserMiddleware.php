<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class UserMiddleware
{
    public function handle($request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (Auth::user()->role !== 'user') {
            abort(403, 'Halaman ini hanya dapat diakses oleh pengguna.');
        }

        return $next($request);
    }
}
