<?php

namespace Stepanenko3\NovaCommandRunner\Http\Middleware;

use Stepanenko3\NovaCommandRunner\CommandRunner;

class Authorize
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Illuminate\Http\Response
     */
    public function handle($request, $next)
    {
        return resolve(CommandRunner::class)->authorize($request) ? $next($request) : abort(403);
    }
}
