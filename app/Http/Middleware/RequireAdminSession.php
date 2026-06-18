<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireAdminSession
{
    public function handle(Request $request, Closure $next): Response
    {
        return $request->session()->get('is_admin') === true
            ? $next($request)
            : redirect()->route('admin.login');
    }
}
