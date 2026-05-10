<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentSyncOutboxes\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AppointmentSyncOutboxInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('branch.name')
                    ->label(__('Branch')),
                TextEntry::make('aggregate_type'),
                TextEntry::make('aggregate_id')
                    ->copyable(),
                TextEntry::make('appointment.start_at')
                    ->label(__('Appointment start'))
                    ->dateTime()
                    ->placeholder('—'),
                TextEntry::make('event_name'),
                TextEntry::make('idempotency_key')
                    ->copyable(),
                TextEntry::make('payload')
                    ->columnSpanFull()
                    ->formatStateUsing(fn (?array $state): string => json_encode($state ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '{}'),
                TextEntry::make('available_at')
                    ->dateTime()
                    ->placeholder('—'),
                TextEntry::make('processed_at')
                    ->dateTime()
                    ->placeholder('—'),
                TextEntry::make('attempts')
                    ->numeric(),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('last_error')
                    ->placeholder('—')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
            ]);
    }
}
