<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentSyncOutboxes\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\Appointment\Enums\SyncOutboxStatus;

class AppointmentSyncOutboxForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Envelope'))
                    ->schema([
                        Select::make('branch_id')
                            ->label(__('Branch'))
                            ->relationship('branch', 'name')
                            ->preload()
                            ->searchable()
                            ->required(),
                        TextInput::make('aggregate_type')
                            ->label(__('Aggregate type'))
                            ->required()
                            ->maxLength(64)
                            ->helperText(__('Use `appointment` unless integrating additional aggregates.')),
                        TextInput::make('aggregate_id')
                            ->label(__('Aggregate ID'))
                            ->uuid()
                            ->required(),
                        TextInput::make('event_name')
                            ->label(__('Event'))
                            ->required()
                            ->maxLength(191),
                        TextInput::make('idempotency_key')
                            ->label(__('Idempotency key'))
                            ->readOnly()
                            ->helperText(__('Derived hash from aggregate version + event; avoids duplicate enqueue.')),
                    ]),
                Section::make(__('Payload and timing'))
                    ->schema([
                        Textarea::make('payload')
                            ->label(__('Payload (JSON)'))
                            ->rows(12)
                            ->required()
                            ->formatStateUsing(function (?array $state): string {
                                return json_encode($state ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '{}';
                            })
                            ->dehydrateStateUsing(function (?string $state): array {
                                if ($state === null || trim($state) === '') {
                                    return [];
                                }

                                $decoded = json_decode($state, true);

                                return is_array($decoded) ? $decoded : [];
                            }),
                        DateTimePicker::make('available_at')
                            ->label(__('Available at'))
                            ->native(false)
                            ->seconds(false),
                        DateTimePicker::make('processed_at')
                            ->label(__('Processed at'))
                            ->native(false)
                            ->seconds(false),
                        TextInput::make('attempts')
                            ->numeric()
                            ->minValue(0)
                            ->required(),
                        Select::make('status')
                            ->label(__('Status'))
                            ->options(SyncOutboxStatus::class)
                            ->required(),
                        Textarea::make('last_error')
                            ->label(__('Last error'))
                            ->rows(4),
                    ]),
            ]);
    }
}
