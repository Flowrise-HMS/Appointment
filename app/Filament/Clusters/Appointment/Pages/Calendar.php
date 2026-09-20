<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Pages;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\CreateAction;
use Filament\Pages\Page;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Modules\Appointment\Classes\Services\ScheduleBlockService;
use Modules\Appointment\Filament\Clusters\Appointment\AppointmentCluster;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\ScheduleBlocks\Schemas\ScheduleBlockForm;
use Modules\Appointment\Filament\Widgets\AppointmentsCalendar;
use Modules\Appointment\Filament\Widgets\ScheduleBlocksCalendar;
use Modules\Appointment\Models\ScheduleBlock;
use Modules\Core\Enums\NavigationGroup;
use Modules\Core\Support\OptionalClass;

class Calendar extends Page implements HasSchemas
{
    use HasPageShield, InteractsWithSchemas;

    protected Width|string|null $maxContentWidth = Width::Full;

    protected string $view = 'appointment::filament.clusters.appointment.pages.calendar';

    protected static ?string $cluster = AppointmentCluster::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static string|\UnitEnum|null $navigationGroup = NavigationGroup::APPOINTMENTS;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make('createScheduleBlock')
                ->label(__('New Schedule Block'))
                ->icon(Heroicon::Plus)
                ->model(ScheduleBlock::class)
                ->modalHeading(__('New Schedule Block'))
                ->slideOver()
                ->visible(fn (): bool => Gate::allows('create', ScheduleBlock::class))
                ->schema(fn (Schema $schema): Schema => ScheduleBlockForm::configure($schema))
                ->fillForm(fn (): array => ['practitioner_id' => $this->currentStaffId()])
                ->using(fn (array $data): ScheduleBlock => app(ScheduleBlockService::class)->create($data))
                ->after(fn () => $this->dispatch('calendar--refresh')),
        ];
    }

    /**
     * Staff profile linked to the signed-in user, when the Staff module is installed.
     */
    protected function currentStaffId(): ?string
    {
        return OptionalClass::when(
            'Modules\\Staff\\Models\\Staff',
            fn (string $class) => $class::query()->where('user_id', Auth::id())->value('id'),
            'Staff',
        );
    }

    protected function getHeaderWidgets(): array
    {
        return [
            AppointmentsCalendar::class,
            ScheduleBlocksCalendar::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 1;
    }
}
