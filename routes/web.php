<?php

declare(strict_types=1);

// MEMORA solutions — routes du package Statut.

use Illuminate\Support\Facades\Route;
use Memora\Statut\Http\Controllers\StatutController;

if (config('statut.enabled')) {
    Route::middleware((array) config('statut.route_middleware', ['web']))
        ->get(config('statut.route_path', '/statut'), StatutController::class)
        ->name('statut.index');
}
