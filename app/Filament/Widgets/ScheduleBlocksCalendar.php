<?php

namespace Modules\Appointment\Filament\Widgets;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Guava\Calendar\Enums\CalendarViewType;
use Guava\Calendar\Filament\Actions\CreateAction;
use Guava\Calendar\Filament\CalendarWidget;
use Guava\Calendar\ValueObjects\CalendarEvent;
use Guava\Calendar\ValueObjects\FetchInfo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\HtmlString;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\ScheduleBlocks\Schemas\ScheduleBlockInfolist;
use Modules\Appointment\Models\ScheduleBlock;
use Modules\Staff\Models\Staff;

class ScheduleBlocksCalendar extends CalendarWidget
{
    protected string|HtmlString|bool|null $heading = 'Schedule Blocks';

    protected static ?int $sort = 2;

    protected CalendarViewType $calendarView = CalendarViewType::DayGridMonth;

    protected bool $eventClickEnabled = true;

    protected ?string $defaultEventClickAction = 'view';

    protected function getEvents(FetchInfo $info): Collection|array|Builder
    {
        $practitionerId = $this->resolvePractitionerId();

        if (! $practitionerId) {
            return [];
        }

        return ScheduleBlock::query()
            ->where('practitioner_id', $practitionerId)
            ->where('blocked_from', '<=', $info->end)
            ->where('blocked_to', '>=', $info->start)
            ->get()
            ->map(fn (ScheduleBlock $block) => CalendarEvent::make($block)
                ->title($block->reason ?? 'Unavailable')
                ->start($block->blocked_from)
                ->end($block->blocked_to)
                ->backgroundColor('#ef4444')
                ->textColor('#ffffff')
                ->borderColor('#dc2626')
            );
    }

    public function getHeaderActions(): array
    {
        return [
            $this->createScheduleBlockAction(),
        ];
    }

    protected function createScheduleBlockAction(): CreateAction
    {
        $practitionerId = $this->resolvePractitionerId();
        $branchId = Context::get('current_branch_id', Auth::user()?->branch_id);

        return CreateAction::make('createScheduleBlock')
            ->model(ScheduleBlock::class)
            ->label(__('New Schedule Block'))
            ->icon('heroicon-m-plus')
            ->slideOver()
            ->modalWidth(Width::ExtraLarge)
            ->modalHeading(__('New Schedule Block'))
            ->visible(fn (): bool => $practitionerId !== null)
            ->schema(function (Schema $schema): Schema {
                return $schema->components([
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
                    TextInput::make('resource_reference')
                        ->label(__('Resource Reference')),
                    TextInput::make('reason')
                        ->label(__('Reason'))
                        ->required()
                        ->maxLength(1000),
                    DateTimePicker::make('blocked_from')
                        ->label(__('From'))
                        ->required(),
                    DateTimePicker::make('blocked_to')
                        ->label(__('To'))
                        ->required(),
                ]);
            })
            ->mutateFormDataUsing(function (array $data) use ($practitionerId, $branchId): array {
                $data['practitioner_id'] = $practitionerId;
                $data['branch_id'] = $branchId;

                return $data;
            });
    }

    protected function scheduleBlockSchema(Schema $schema): Schema
    {
        return ScheduleBlockInfolist::configure($schema);
    }

    protected function resolvePractitionerId(): ?string
    {
        $staff = Staff::where('user_id', Auth::id())->first();

        return $staff?->id;
    }
}
