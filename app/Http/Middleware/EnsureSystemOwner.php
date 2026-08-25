<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSystemOwner
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->is_system_owner, 403, 'Esta sección es exclusiva del dueño del sistema.');
        return $next($request);
    }
}
