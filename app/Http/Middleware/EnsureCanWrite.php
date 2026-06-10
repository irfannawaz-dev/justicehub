<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks POST/PUT/PATCH/DELETE requests for read-only users (Viewer role).
 *
 * Apply to route groups that should enforce write protection.
 * GET requests always pass through (viewing is always allowed).
 */
class EnsureCanWrite
{
    public function handle(Request $request, Closure $next): Response
    {
        // GET/HEAD/OPTIONS always allowed (read-only operations)
        if ($request->isMethodSafe()) {
            return $next($request);
        }

        $user = $request->user();

        if ($user && !$user->canWrite()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your account has read-only access. You cannot create, edit, or delete data.',
                ], Response::HTTP_FORBIDDEN);
            }

            abort(Response::HTTP_FORBIDDEN, 'Your account has read-only access. You cannot create, edit, or delete data.');
        }

        return $next($request);
    }
}
