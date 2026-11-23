<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentSchedules\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Gate;
use Modules\Appointment\Classes\Services\SlotGenerationService;
use Modules\Appointment\Models\AppointmentSchedule;

class GenerateSlotsAction
{
    public static function make(): Action
    {
        return Action::make('generateSlots')
            ->label(__('Generate slots'))
            ->icon(Heroicon::OutlinedArrowPath)
            ->visible(fn (AppointmentSchedule $record): bool => $record->is_active && Gate::allows('update', $record))
            ->action(fn (AppointmentSchedule $record) => static::run($record));
    }

    public static function run(AppointmentSchedule $schedule): void
    {
        $result = app(SlotGenerationService::class)->generate($schedule->refresh());

        Notification::make()
            ->title(__('Slots generated'))
            ->body(__(':created new slots; :removed stale free slots removed.', $result))
            ->success()
            ->send();
    }
}
