<?php

use Stepanenko3\NovaCommandRunner\Http\Controllers\CommandsController;
use Illuminate\Support\Facades\Route;

Route::controller(CommandsController::class)->group(function (): void {
    Route::get('/', 'index');
    Route::post('run', 'run');
});
