<?php

use App\Http\Controllers\AppointmentController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Маршруты для работы с записями
    Route::get('/appointments', [AppointmentController::class, 'index']);
    Route::post('/appointments', [AppointmentController::class, 'store']);
    Route::put('/appointments/{appointment}', [AppointmentController::class, 'update']);
    Route::delete('/appointments/{appointment}', [AppointmentController::class, 'destroy']);
    Route::get('/appointments/available-slots', [AppointmentController::class, 'getAvailableSlots']);
}); 