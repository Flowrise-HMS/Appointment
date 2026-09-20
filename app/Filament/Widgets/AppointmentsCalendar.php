<?php

namespace Modules\Appointment\Filament\Widgets;

use Carbon\WeekDay;
use Guava\Calendar\Filament\CalendarWidget;
use Guava\Calendar\ValueObjects\FetchInfo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Modules\Appointment\Models\Appointment;
use Modules\Appointment\Settings\AppointmentSettings;

class AppointmentsCalendar extends CalendarWidget
{
    public function getFirstDay(): WeekDay
    {
        return enum_try_from(WeekDay::class, (int) app(AppointmentSettings::class)->calendar_first_day_of_week) ?? WeekDay::Monday;
    }

    protected function getEvents(FetchInfo $info): Collection|array|Builder
    {
        return Appointment::query()
            ->where('start_at', '<=', $info->end)
            ->where('end_at', '>=', $info->start)
            ->with(['patient', 'location']);
    }
}
