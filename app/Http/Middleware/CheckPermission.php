<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!auth()->user()->hasAnyPermission($permissions)) {
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}
