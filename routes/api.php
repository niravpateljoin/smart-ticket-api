<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TicketController;


Route::prefix('tickets')->group(function () {
    Route::post('/', [TicketController::class, 'store']);
    Route::get('/', [TicketController::class, 'index']);
    Route::get('/{id}', [TicketController::class, 'show']);
    Route::patch('/{id}', [TicketController::class, 'update']);
    Route::delete('/{id}', [TicketController::class, 'destroy']);
    Route::post('/{id}/classify', [TicketController::class, 'classify']);
});

Route::get('/stats', [TicketController::class, 'stats']);
