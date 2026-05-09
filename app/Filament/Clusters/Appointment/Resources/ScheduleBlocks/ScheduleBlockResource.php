<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\ScheduleBlocks;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Appointment\Filament\Clusters\Appointment\AppointmentCluster;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\ScheduleBlocks\Pages\CreateScheduleBlock;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\ScheduleBlocks\Pages\EditScheduleBlock;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\ScheduleBlocks\Pages\ListScheduleBlocks;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\ScheduleBlocks\Pages\ViewScheduleBlock;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\ScheduleBlocks\Schemas\ScheduleBlockForm;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\ScheduleBlocks\Schemas\ScheduleBlockInfolist;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\ScheduleBlocks\Tables\ScheduleBlocksTable;
use Modules\Appointment\Models\ScheduleBlock;
use Modules\Core\Enums\NavigationGroup;


class ScheduleBlockResource extends Resource
{
    protected static ?string $model = ScheduleBlock::class;

    protected static string|BackedEnum|null $navigationIcon = null;
    protected static string|\UnitEnum|null $navigationGroup = NavigationGroup::APPOINTMENTS;

    protected static ?string $cluster = AppointmentCluster::class;

    public static function form(Schema $schema): Schema
    {
        return ScheduleBlockForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ScheduleBlockInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ScheduleBlocksTable::configure($table);
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
            'index' => ListScheduleBlocks::route('/'),
            'create' => CreateScheduleBlock::route('/create'),
            'view' => ViewScheduleBlock::route('/{record}'),
            'edit' => EditScheduleBlock::route('/{record}/edit'),
        ];
    }
}
