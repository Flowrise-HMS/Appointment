<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\Appointments\Pages;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Livewire\Attributes\Url;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\Appointments\AppointmentResource;
use Modules\Appointment\Filament\Widgets\AppointmentsCalendar;

class ListAppointments extends ListRecords
{
    protected static string $resource = AppointmentResource::class;

    #[Url(as: 'calendar')]
    public bool $calendarMode = false;

    public function getMaxContentWidth(): Width|string|null
    {
        if ($this->calendarMode) {
            return Width::Full;
        }

        return parent::getMaxContentWidth();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('toggleCalendarView')
                ->label(fn (): string => $this->calendarMode ? __('Table') : __('Calendar'))
                ->icon(fn (): Heroicon => $this->calendarMode ? Heroicon::Bars3 : Heroicon::CalendarDays)
                ->color('gray')
                ->action(fn (): mixed => $this->calendarMode = ! $this->calendarMode),
            CreateAction::make(),
        ];
    }

    public function content(Schema $schema): Schema
    {
        if ($this->calendarMode) {
            return $schema
                ->components([
                    Livewire::make(AppointmentsCalendar::class),
                ]);
        }

        return parent::content($schema);
    }

    protected function getTableQuery(): Builder | Relation | null
    {
        $query = parent::getTableQuery();

        if ($this->calendarMode && $query instanceof Builder) {
            return $query->whereRaw('1 = 0');
        }

        return $query;
    }
}
