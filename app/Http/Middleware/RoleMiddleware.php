<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Debug logging
        Log::debug('RoleMiddleware check', [
            'is_authenticated' => auth()->check(),
            'user_id' => auth()->id(),
            'user_role' => auth()->user()?->role,
            'required_roles' => $roles,
            'path' => $request->path(),
            'session_id' => session()->getId(),
        ]);

        if (!auth()->check()) {
            Log::warning('RoleMiddleware: User not authenticated', [
                'path' => $request->path(),
                'ip' => $request->ip(),
            ]);
            return redirect()->route('login');
        }

        $userRole = auth()->user()->role;

        if (!in_array($userRole, $roles)) {
            Log::warning('RoleMiddleware: Role mismatch', [
                'user_id' => auth()->id(),
                'user_role' => $userRole,
                'required_roles' => $roles,
                'path' => $request->path(),
            ]);
            abort(403, 'Unauthorized access.');
        }

        return $next($request);
    }
}
