<?php

namespace Stepanenko3\NovaCommandRunner;

use Laravel\Nova\Nova;
use Laravel\Nova\Tool;
use Illuminate\Http\Request;
use Laravel\Nova\Menu\MenuSection;

class CommandRunnerTool extends Tool
{
    public function boot()
    {
        Nova::script('nova-command-runner', __DIR__ . '/../dist/js/tool.js');
    }

    public function menu(Request $request)
    {
        return MenuSection::make(__(config('nova-command-runner.navigation_label', 'Command Runner')))
            ->path('/' . config('nova-command-runner.path', 'command-runner'));
    }
}
