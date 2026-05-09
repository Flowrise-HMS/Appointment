<?php

use Illuminate\Support\Facades\Route;
use Modules\Appointment\Http\Controllers\AppointmentController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('appointments', [AppointmentController::class, 'index'])->name('appointment.index');
});
