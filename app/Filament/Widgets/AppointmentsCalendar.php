<?php

namespace Modules\Appointment\Filament\Widgets;

use Carbon\WeekDay;
use Guava\Calendar\Filament\CalendarWidget;
use Guava\Calendar\ValueObjects\FetchInfo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Modules\Appointment\Models\Appointment;
use Modules\Appointment\Settings\AppointmentSettings;

class AppointmentsCalendar extends CalendarWidget
{
    protected string $view = 'appointment::filament.widgets.calendar-widget';

    protected string|HtmlString|bool|null $heading = 'Appointments';

    protected string|HtmlString|null $description = 'Track and review scheduled patient visits, clinical consultations, and provider bookings across clinic locations.';

    public function getDescription(): string|HtmlString|null
    {
        return $this->description ? __($this->description) : null;
    }

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
