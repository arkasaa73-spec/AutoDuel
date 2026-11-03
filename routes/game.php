<?php

use App\Http\Controllers\Admin\CarController as AdminCarController;
use App\Http\Controllers\Admin\RunController as AdminRunController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Support\Facades\Route;

Route::get('leaderboard', [LeaderboardController::class, 'index'])->name('leaderboard');

// {mode} превращается в enum GameMode автоматически, неверный режим даёт 404
Route::prefix('game/{mode}')->name('game.')->group(function () {
    Route::get('/', [GameController::class, 'show'])->name('show');
    Route::post('start', [GameController::class, 'start'])->middleware('throttle:30,1')->name('start');
    Route::post('answer', [GameController::class, 'answer'])->middleware('throttle:45,1')->name('answer');
});

// Админка: сначала вход (auth), потом проверка роли admin
Route::middleware(['auth', EnsureUserIsAdmin::class])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::resource('cars', AdminCarController::class)->except('show');
        Route::get('runs', [AdminRunController::class, 'index'])->name('runs.index');
        Route::delete('runs/{run}', [AdminRunController::class, 'destroy'])->name('runs.destroy');
    });
