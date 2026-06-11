<?php

namespace App\Http\Middleware;

use App\Enums\TeamPermission;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        // Get permission enum
        $perm = TeamPermission::tryFrom($permission);

        if (!$perm || !$user->hasPermission($perm)) {
            abort(403, 'Unauthorized.');
        }

        return $next($request);
    }
}
