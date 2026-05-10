<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\WaitlistEntries;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Appointment\Filament\Clusters\Appointment\AppointmentCluster;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\WaitlistEntries\Pages\CreateWaitlistEntry;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\WaitlistEntries\Pages\EditWaitlistEntry;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\WaitlistEntries\Pages\ListWaitlistEntries;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\WaitlistEntries\Pages\ViewWaitlistEntry;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\WaitlistEntries\Schemas\WaitlistEntryForm;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\WaitlistEntries\Schemas\WaitlistEntryInfolist;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\WaitlistEntries\Tables\WaitlistEntriesTable;
use Modules\Appointment\Models\WaitlistEntry;
use Modules\Core\Enums\NavigationGroup;

class WaitlistEntryResource extends Resource
{
    protected static ?string $model = WaitlistEntry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = AppointmentCluster::class;

    protected static string|\UnitEnum|null $navigationGroup = NavigationGroup::APPOINTMENTS;

    public static function form(Schema $schema): Schema
    {
        return WaitlistEntryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return WaitlistEntryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WaitlistEntriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWaitlistEntries::route('/'),
            'create' => CreateWaitlistEntry::route('/create'),
            'view' => ViewWaitlistEntry::route('/{record}'),
            'edit' => EditWaitlistEntry::route('/{record}/edit'),
        ];
    }
}
