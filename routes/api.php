<?php

use Illuminate\Support\Facades\Route;
use Modules\Appointment\Http\Controllers\Api\V1\AppointmentController;
use Modules\Appointment\Http\Controllers\Api\V1\WaitlistController;
use Modules\Core\Http\Middleware\SetCurrentApiBranch;

Route::middleware(['auth:sanctum', SetCurrentApiBranch::class])->prefix('v1')->group(function () {
    Route::apiResource('appointments', AppointmentController::class)->names('appointment');
    Route::post('appointments/{appointment}/check-in', [AppointmentController::class, 'checkIn'])->name('appointment.check-in');
    Route::post('appointments/{appointment}/cancel', [AppointmentController::class, 'cancel'])->name('appointment.cancel');
    Route::post('appointments/bulk-reschedule', [AppointmentController::class, 'bulkReschedule'])->name('appointment.bulk-reschedule');

    Route::get('waitlist', [WaitlistController::class, 'index'])->name('waitlist.index');
    Route::post('waitlist', [WaitlistController::class, 'store'])->name('waitlist.store');
    Route::post('waitlist/{waitlistEntry}/offer-slot', [WaitlistController::class, 'offerSlot'])->name('waitlist.offer-slot');
});
