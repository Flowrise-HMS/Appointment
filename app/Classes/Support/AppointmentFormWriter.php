<?php

namespace Modules\Appointment\Classes\Support;

use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;
use Modules\Appointment\Classes\Services\AppointmentSchedulingService;
use Modules\Appointment\Exceptions\AppointmentConflictException;
use Modules\Appointment\Models\Appointment;

/**
 * Routes Filament create/edit writes through AppointmentSchedulingService so
 * the practitioner conflict check and the sync outbox apply to the admin
 * panel exactly as they do to the REST API and the clinical quick actions.
 */
class AppointmentFormWriter
{
    /**
     * @param  array<string, mixed>  $data
     */
    public static function create(array $data): Appointment
    {
        try {
            return app(AppointmentSchedulingService::class)->schedule($data);
        } catch (AppointmentConflictException $exception) {
            throw self::halt($exception);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function update(Appointment $appointment, array $data): Appointment
    {
        try {
            return app(AppointmentSchedulingService::class)->reschedule($appointment, $data);
        } catch (AppointmentConflictException $exception) {
            throw self::halt($exception);
        }
    }

    /**
     * Show the conflict as a danger notification and stop the Filament action
     * without leaving the form (Halt keeps the modal / page open).
     */
    protected static function halt(AppointmentConflictException $exception): Halt
    {
        Notification::make()
            ->danger()
            ->title(__('Scheduling conflict'))
            ->body($exception->getMessage())
            ->persistent()
            ->send();

        return new Halt;
    }
}
