<?php

use Illuminate\Support\Facades\Route;
use Modules\Appointment\Http\Controllers\Api\V1\AppointmentController;
use Modules\Appointment\Http\Controllers\Api\V1\AppointmentRecurrenceRuleController;
use Modules\Appointment\Http\Controllers\Api\V1\AppointmentScheduleController;
use Modules\Appointment\Http\Controllers\Api\V1\AppointmentSlotController;
use Modules\Appointment\Http\Controllers\Api\V1\ScheduleBlockController;
use Modules\Appointment\Http\Controllers\Api\V1\WaitlistController;
use Modules\Core\Services\ApiRouteRegistrar;

ApiRouteRegistrar::register(
    scope: 'appointments',
    routes: function () {
        Route::apiResource('appointments', AppointmentController::class)->names('appointment');
        Route::post('appointments/{appointment}/check-in', [AppointmentController::class, 'checkIn'])->name('appointment.check-in');
        Route::post('appointments/{appointment}/cancel', [AppointmentController::class, 'cancel'])->name('appointment.cancel');
        Route::post('appointments/bulk-reschedule', [AppointmentController::class, 'bulkReschedule'])->name('appointment.bulk-reschedule');
    },
);

ApiRouteRegistrar::register(
    scope: 'appointments',
    routes: function () {
        Route::get('waitlist', [WaitlistController::class, 'index'])->name('waitlist.index');
        Route::post('waitlist', [WaitlistController::class, 'store'])->name('waitlist.store');
        Route::post('waitlist/{waitlistEntry}/offer-slot', [WaitlistController::class, 'offerSlot'])->name('waitlist.offer-slot');
    },
);

ApiRouteRegistrar::register(
    scope: 'appointments',
    routes: function () {
        AppointmentScheduleController::routes('schedules', ['index', 'show', 'store', 'update', 'destroy'], ['generate-slots' => 'generateSlots']);
        AppointmentSlotController::routes('slots', ['index', 'show']);
        AppointmentRecurrenceRuleController::routes('recurrence-rules', ['index', 'show', 'store', 'update', 'destroy'], [
            'generate' => 'generate', 'cancel-series' => 'cancelSeries',
        ]);
        ScheduleBlockController::routes('schedule-blocks');
    },
);
