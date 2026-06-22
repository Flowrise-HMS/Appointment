<?php

namespace Modules\Appointment\Filament;

use Coolsam\Modules\Concerns\ModuleFilamentPlugin;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Modules\Core\Settings\FeatureSettings;

class AppointmentPlugin implements Plugin
{
    use ModuleFilamentPlugin {
        register as protected traitRegister;
    }

    public function getModuleName(): string
    {
        return 'Appointment';
    }

    public function getId(): string
    {
        return 'appointment';
    }

    public function register(Panel $panel): void
    {
        if (! $this->appointmentsEnabled()) {
            return;
        }

        $this->traitRegister($panel);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    protected function appointmentsEnabled(): bool
    {
        try {
            return app(FeatureSettings::class)->appointments_enabled;
        } catch (\Throwable) {
            return true;
        }
    }
}
