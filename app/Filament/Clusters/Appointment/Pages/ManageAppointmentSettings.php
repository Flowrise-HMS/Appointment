<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Pages;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Modules\Appointment\Filament\Clusters\Appointment\AppointmentCluster;
use Modules\Appointment\Settings\AppointmentSettings;
use Modules\Core\Enums\NavigationGroup;

class ManageAppointmentSettings extends SettingsPage
{
    use HasPageShield;

    protected static ?string $cluster = AppointmentCluster::class;

    protected static string $settings = AppointmentSettings::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static string|\UnitEnum|null $navigationGroup = NavigationGroup::SETTINGS;

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Appointments';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Defaults'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('default_duration_minutes')
                            ->label(__('Default duration (minutes)'))
                            ->numeric()
                            ->minValue(1),
                        TextInput::make('default_status')
                            ->label(__('Default status')),
                        TextInput::make('default_type')
                            ->label(__('Default type')),
                        TextInput::make('calendar_first_day_of_week')
                            ->label(__('Calendar first day of week (0=Sun, 1=Mon)'))
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(6),
                    ]),
                Section::make(__('Features'))
                    ->columns(2)
                    ->schema([
                        Toggle::make('waitlist_enabled')
                            ->label(__('Waitlist')),
                        Toggle::make('telehealth_enabled')
                            ->label(__('Telehealth / virtual appointments')),
                        Toggle::make('recurrence_enabled')
                            ->label(__('Recurring appointments')),
                        Toggle::make('external_sync_enabled')
                            ->label(__('External sync outbox')),
                        Toggle::make('waitlist_api_enabled')
                            ->label(__('Waitlist API')),
                    ]),
            ]);
    }
}
