<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        // Priority: session > user preference > default
        if ($locale = session('locale')) {
            App::setLocale($locale);
        } elseif ($user = $request->user()) {
            $locale = $user->meta['locale'] ?? 'en';
            App::setLocale($locale);
            session(['locale' => $locale]);
        }

        return $next($request);
    }
}
