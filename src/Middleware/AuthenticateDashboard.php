<?php

namespace Baufragen\DataSync\Middleware;

use Baufragen\DataSync\DataSync;

class AuthenticateDashboard
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Illuminate\Http\Response|null
     */
    public function handle($request, $next)
    {
        return DataSync::check($request) ? $next($request) : abort(403);
    }
}