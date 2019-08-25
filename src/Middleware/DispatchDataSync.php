<?php

namespace Baufragen\DataSync\Middleware;

use Closure;

class DispatchDataSync
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
        $response = $next($request);

        $dataSyncHandler = app('dataSync.handler');

        if ($dataSyncHandler->hasOpenSyncs()) {
            $dataSyncHandler->dispatch();
        }

        return $response;
    }
}
