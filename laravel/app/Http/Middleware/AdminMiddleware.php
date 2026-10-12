<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
{
    // Periksa apakah user sudah login dan benar-benar memiliki role 'admin'
    if (!Auth::check() || Auth::user()->role !== 'admin') {
        abort(403, 'Akses ditolak. Halaman ini khusus untuk Administrator.');
    }
    return $next($request);
}
}
