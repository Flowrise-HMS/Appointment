<?php

namespace Modules\Appointment\Filament\Widgets;

use Guava\Calendar\Filament\CalendarWidget;
use Guava\Calendar\ValueObjects\FetchInfo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Modules\Appointment\Models\Appointment;

class AppointmentsCalendar extends CalendarWidget
{
    protected function getEvents(FetchInfo $info): Collection|array|Builder
    {
        return Appointment::query()
            ->where('start_at', '<=', $info->end)
            ->where('end_at', '>=', $info->start)
            ->with(['patient', 'location']);
    }
}
