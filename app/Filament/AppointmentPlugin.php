<?php

namespace Modules\Appointment\Filament;

use Coolsam\Modules\Concerns\ModuleFilamentPlugin;
use Filament\Contracts\Plugin;
use Filament\Panel;

class AppointmentPlugin implements Plugin
{
    use ModuleFilamentPlugin;

    public function getModuleName(): string
    {
        return 'Appointment';
    }

    public function getId(): string
    {
        return 'appointment';
    }

    public function boot(Panel $panel): void
    {
        // TODO: Implement boot() method.
    }
}
