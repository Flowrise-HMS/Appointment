<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentSchedules\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\Core\Classes\Services\BranchService;
use Modules\Core\Models\Service;
use Modules\Core\Support\ModuleAvailability;

class AppointmentScheduleForm
{
    /**
     * @return array<int, string>
     */
    public static function weekdayOptions(): array
    {
        return [
            1 => __('Monday'),
            2 => __('Tuesday'),
            3 => __('Wednesday'),
            4 => __('Thursday'),
            5 => __('Friday'),
            6 => __('Saturday'),
            7 => __('Sunday'),
        ];
    }

    public static function configure(Schema $schema): Schema
    {
        $staffEnabled = ModuleAvailability::staffEnabled();

        return $schema
            ->components([
                Section::make(__('Schedule'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label(__('Name'))
                            ->placeholder(__('e.g. Dr Mensah – OPD mornings'))
                            ->required()
                            ->maxLength(255),
                        Select::make('branch_id')
                            ->label(__('Branch'))
                            ->relationship('branch', 'name')
                            ->searchable()
                            ->preload()
                            ->default(fn () => app(BranchService::class)->getDefaultBranchId())
                            ->required(),
                        Select::make('practitioner_id')
                            ->label(__('Practitioner'))
                            ->relationship('practitioner', 'staff_number')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record?->display_name)
                            ->searchable()
                            ->preload()
                            ->visible($staffEnabled)
                            ->helperText(__('When set, this practitioner can only be booked inside the generated slots (if schedule enforcement is on).')),
                        Select::make('service_id')
                            ->label(__('Service'))
                            ->options(fn () => Service::query()->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->helperText(__('Slots of this schedule are offered for this service in online booking.')),
                        Select::make('location_id')
                            ->label(__('Location'))
                            ->relationship('location', 'name')
                            ->searchable()
                            ->preload(),
                        Select::make('department_id')
                            ->label(__('Department'))
                            ->relationship('department', 'name')
                            ->searchable()
                            ->preload(),
                        TextInput::make('slot_minutes')
                            ->label(__('Slot length (minutes)'))
                            ->numeric()
                            ->integer()
                            ->minValue(5)
                            ->maxValue(480)
                            ->default(15)
                            ->required(),
                        TextInput::make('generation_days')
                            ->label(__('Generate slots for (days ahead)'))
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->maxValue(180)
                            ->default(28)
                            ->required(),
                        DatePicker::make('planning_horizon_start')
                            ->label(__('Valid from')),
                        DatePicker::make('planning_horizon_end')
                            ->label(__('Valid until'))
                            ->afterOrEqual('planning_horizon_start'),
                        Select::make('timezone')
                            ->label(__('Timezone'))
                            ->helperText(__('Defaults to the branch timezone.'))
                            ->options(fn (): array => array_combine(\DateTimeZone::listIdentifiers(), \DateTimeZone::listIdentifiers()))
                            ->searchable(),
                        Toggle::make('is_active')
                            ->label(__('Active'))
                            ->default(true),
                        Textarea::make('comment')
                            ->label(__('Comment'))
                            ->columnSpanFull(),
                    ]),
                Section::make(__('Weekly availability'))
                    ->schema([
                        Repeater::make('availabilities')
                            ->relationship()
                            ->hiddenLabel()
                            ->columns(3)
                            ->defaultItems(0)
                            ->addActionLabel(__('Add hours'))
                            ->schema([
                                Select::make('weekday')
                                    ->label(__('Day'))
                                    ->options(static::weekdayOptions())
                                    ->required(),
                                TimePicker::make('start_time')
                                    ->label(__('From'))
                                    ->seconds(false)
                                    ->required(),
                                TimePicker::make('end_time')
                                    ->label(__('To'))
                                    ->seconds(false)
                                    ->after('start_time')
                                    ->required(),
                            ]),
                    ]),
            ]);
    }
}
