<?php

use App\Http\Controllers\GameController;
use Illuminate\Support\Facades\Route;

// {mode} превращается в enum GameMode автоматически, неверный режим даёт 404
Route::prefix('game/{mode}')->name('game.')->group(function () {
    Route::get('/', [GameController::class, 'show'])->name('show');
    Route::post('start', [GameController::class, 'start'])->middleware('throttle:30,1')->name('start');
    Route::post('answer', [GameController::class, 'answer'])->middleware('throttle:45,1')->name('answer');
});
