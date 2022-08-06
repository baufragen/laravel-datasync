<?php

namespace Baufragen\DataSync\Middleware;

use Baufragen\DataSync\DataSync;
use Closure;

class WithoutDataSync
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $previousState = DataSync::isEnabled();

        $response = $next($request);

        if ($previousState) {
            DataSync::enable();
        }

        return $response;
    }
}
