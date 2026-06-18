<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireTeamSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $team = $request->route('team');

        if (! $team || (int) $request->session()->get('team_id') !== (int) $team->id) {
            return redirect()->route('team.home', $team);
        }

        return $next($request);
    }
}
