<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentSchedules\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\Appointment\Enums\SlotStatus;
use Modules\Appointment\Models\AppointmentSchedule;

class AppointmentScheduleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Schedule'))
                    ->columns(3)
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('branch.name')->label(__('Branch')),
                        TextEntry::make('practitioner.display_name')->label(__('Practitioner'))->placeholder('—'),
                        TextEntry::make('service.name')->label(__('Service'))->placeholder('—'),
                        TextEntry::make('location.name')->label(__('Location'))->placeholder('—'),
                        TextEntry::make('department.name')->label(__('Department'))->placeholder('—'),
                        TextEntry::make('slot_minutes')->label(__('Slot length'))->suffix(' min'),
                        TextEntry::make('planning_horizon_start')->label(__('Valid from'))->date()->placeholder('—'),
                        TextEntry::make('planning_horizon_end')->label(__('Valid until'))->date()->placeholder('—'),
                        TextEntry::make('effective_timezone')
                            ->label(__('Timezone'))
                            ->state(fn (AppointmentSchedule $record): string => $record->effectiveTimezone()),
                        IconEntry::make('is_active')->label(__('Active'))->boolean(),
                        TextEntry::make('hours')
                            ->label(__('Weekly hours'))
                            ->state(fn (AppointmentSchedule $record): string => static::hours($record))
                            ->columnSpanFull(),
                        TextEntry::make('free_slots')
                            ->label(__('Upcoming free slots'))
                            ->state(fn (AppointmentSchedule $record): int => $record->slots()
                                ->where('status', SlotStatus::FREE->value)
                                ->where('start_at', '>', now())
                                ->count()),
                    ]),
            ]);
    }

    public static function hours(AppointmentSchedule $schedule): string
    {
        $days = AppointmentScheduleForm::weekdayOptions();

        $lines = $schedule->availabilities
            ->map(fn ($window): string => ($days[$window->weekday] ?? '?').' '.substr((string) $window->start_time, 0, 5).'–'.substr((string) $window->end_time, 0, 5))
            ->all();

        return $lines === [] ? __('No hours set') : implode(', ', $lines);
    }
}
