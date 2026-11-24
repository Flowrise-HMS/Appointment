<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentRecurrenceRules\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\Appointment\Enums\RecurrenceFrequency;
use Modules\Appointment\Models\AppointmentRecurrenceRule;

class AppointmentRecurrenceRuleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Recurrence'))
                    ->columns(3)
                    ->schema([
                        TextEntry::make('appointment')
                            ->label(__('Parent appointment'))
                            ->state(fn (AppointmentRecurrenceRule $record): string => $record->appointment
                                ? AppointmentRecurrenceRuleForm::appointmentLabel($record->appointment)
                                : '—'),
                        TextEntry::make('summary')
                            ->label(__('Pattern'))
                            ->state(fn (AppointmentRecurrenceRule $record): string => AppointmentRecurrenceRuleInfolist::describe($record)),
                        IconEntry::make('is_active')
                            ->label(__('Active'))
                            ->boolean(),
                        TextEntry::make('occurrence_count')
                            ->label(__('Additional occurrences'))
                            ->placeholder('—'),
                        TextEntry::make('until_at')
                            ->label(__('Repeat until'))
                            ->dateTime()
                            ->placeholder('—'),
                        TextEntry::make('timezone')
                            ->label(__('Timezone')),
                        TextEntry::make('generated')
                            ->label(__('Booked occurrences'))
                            ->state(fn (AppointmentRecurrenceRule $record): int => $record->generatedAppointments()->count()),
                    ]),
            ]);
    }

    public static function describe(AppointmentRecurrenceRule $record): string
    {
        $frequency = enum_try_from(RecurrenceFrequency::class, (string) $record->frequency);
        $label = $frequency?->getLabel() ?? (string) $record->frequency;
        $interval = max(1, (int) $record->interval);
        $text = $interval > 1 ? __(':label, every :n', ['label' => $label, 'n' => $interval]) : $label;

        $days = array_flip(RecurrenceFrequency::weekdayCodes());
        $byDay = array_map(fn (int $day): string => $days[$day], $record->byDayNumbers());

        if ($frequency === RecurrenceFrequency::WEEKLY && $byDay !== []) {
            $text .= ' ('.implode(', ', $byDay).')';
        }

        return $text;
    }
}
