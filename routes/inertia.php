<?php

use Illuminate\Support\Facades\Route;
use Stepanenko3\NovaCommandRunner\Http\Controllers\CommandsController;

Route::get('/', [CommandsController::class, 'show']);
