<?php

namespace Stepanenko3\NovaCommandRunner\Http\Middleware;

use Laravel\Nova\Nova;
use Laravel\Nova\Tool;
use Stepanenko3\NovaCommandRunner\CommandRunnerTool;

class Authorize
{
    public function handle(
        $request,
        $next,
    ) {
        $tool = collect(Nova::registeredTools())->first([$this, 'matchesTool']);

        return optional($tool)->authorize($request) ? $next($request) : abort(403);
    }

    public function matchesTool(
        Tool $tool
    ): bool {
        return $tool instanceof CommandRunnerTool;
    }
}
