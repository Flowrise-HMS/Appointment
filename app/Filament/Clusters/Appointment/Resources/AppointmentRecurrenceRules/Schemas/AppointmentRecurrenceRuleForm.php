<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentRecurrenceRules\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Modules\Appointment\Enums\RecurrenceFrequency;
use Modules\Appointment\Models\Appointment;

class AppointmentRecurrenceRuleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Repeat'))
                    ->description(__('The parent appointment is the first occurrence. Saving the rule books the following occurrences; slots that clash with the practitioner\'s other bookings are skipped.'))
                    ->columns(2)
                    ->schema([
                        Select::make('appointment_id')
                            ->label(__('Parent appointment'))
                            ->relationship('appointment', 'start_at')
                            ->getOptionLabelFromRecordUsing(fn (Appointment $record): string => static::appointmentLabel($record))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->hidden(fn ($livewire): bool => $livewire instanceof RelationManager)
                            ->columnSpanFull(),
                        Select::make('frequency')
                            ->label(__('Frequency'))
                            ->options(RecurrenceFrequency::class)
                            ->default(RecurrenceFrequency::WEEKLY->value)
                            ->required()
                            ->live()
                            ->native(false),
                        TextInput::make('interval')
                            ->label(__('Every'))
                            ->helperText(__('e.g. 2 with Weekly = every second week'))
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->maxValue(52)
                            ->default(1)
                            ->required(),
                        CheckboxList::make('by_day')
                            ->label(__('On days'))
                            ->helperText(__('Leave empty to repeat on the parent appointment\'s weekday.'))
                            ->options([
                                'MON' => __('Mon'),
                                'TUE' => __('Tue'),
                                'WED' => __('Wed'),
                                'THU' => __('Thu'),
                                'FRI' => __('Fri'),
                                'SAT' => __('Sat'),
                                'SUN' => __('Sun'),
                            ])
                            ->columns(7)
                            ->visible(fn (Get $get): bool => static::frequencyValue($get('frequency')) === RecurrenceFrequency::WEEKLY->value)
                            ->columnSpanFull(),
                        TextInput::make('occurrence_count')
                            ->label(__('Additional occurrences'))
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->maxValue(104)
                            ->requiredWithout('until_at'),
                        DateTimePicker::make('until_at')
                            ->label(__('Repeat until'))
                            ->requiredWithout('occurrence_count'),
                        Select::make('timezone')
                            ->label(__('Timezone'))
                            ->helperText(__('Defaults to the branch timezone.'))
                            ->options(fn (): array => array_combine(\DateTimeZone::listIdentifiers(), \DateTimeZone::listIdentifiers()))
                            ->searchable(),
                        Toggle::make('is_active')
                            ->label(__('Active'))
                            ->default(true)
                            ->hiddenOn('create'),
                    ]),
            ]);
    }

    public static function appointmentLabel(Appointment $record): string
    {
        $patient = $record->patient;
        $name = $patient ? trim(implode(' ', array_filter([$patient->first_name, $patient->last_name]))) : '';

        return trim(($record->start_at?->format('d M Y H:i') ?? '').' · '.($name ?: __('Unknown patient')), ' ·');
    }

    private static function frequencyValue(mixed $value): ?string
    {
        return $value instanceof RecurrenceFrequency ? $value->value : (is_string($value) ? $value : null);
    }
}
