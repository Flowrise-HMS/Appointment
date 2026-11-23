<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        if (! $this->migrator->exists('appointment.enforce_schedules')) {
            $this->migrator->add('appointment.enforce_schedules', true);
        }
    }

    public function down(): void
    {
        if ($this->migrator->exists('appointment.enforce_schedules')) {
            $this->migrator->delete('appointment.enforce_schedules');
        }
    }
};
