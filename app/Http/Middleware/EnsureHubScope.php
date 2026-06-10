<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sets the active hub scope for the current request based on user role.
 *
 * - Global roles (Head, M&E Lead): can switch hubs via session, defaults to 'all'
 * - Hub-scoped roles (Hub Admin, Data Entry, Complaint Investigator): locked to their hub_id
 * - Viewer: if hub_id is null → 'all', if hub_id is set → locked to that hub
 *
 * After this middleware runs, use:
 *   $request->activeHub()    → current hub_id or 'all'
 *   $request->isAllHubs()    → true if viewing all hubs
 *   session('active_hub')    → persisted hub selection
 */
class EnsureHubScope
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        if ($user->canSeeAllHubs()) {
            // Global roles: respect session selection, default to 'all'
            $activeHub = session('active_hub', 'all');
        } else {
            // Hub-scoped roles: force to their assigned hub, ignore session
            $activeHub = $user->hub_id;
            session(['active_hub' => $activeHub]);
        }

        // Make available on the request object
        $request->merge(['_active_hub' => $activeHub]);

        // Share with all Blade views
        view()->share('activeHub', $activeHub);
        view()->share('isAllHubs', $activeHub === 'all' || $activeHub === null);
        view()->share('authUser', $user);
        view()->share('canSwitchHub', $user->canSeeAllHubs());

        return $next($request);
    }
}
