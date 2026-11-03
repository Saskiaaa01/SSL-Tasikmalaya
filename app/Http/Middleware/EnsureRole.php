<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            abort(401);
        }

        if (empty($roles)) {
            return $next($request);
        }

        $userRole = $request->user()->role ?? 'owner';
        if (in_array($userRole, $roles) || ($request->user()->isAdmin() && in_array('admin', $roles))) {
            return $next($request);
        }

        abort(403, 'Unauthorized action.');
    }
}
