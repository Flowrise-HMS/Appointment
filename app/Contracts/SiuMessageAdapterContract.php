<?php

namespace Modules\Appointment\Contracts;

use Modules\Appointment\Models\Appointment;

interface SiuMessageAdapterContract
{
    public function toSiu(Appointment $appointment, string $triggerEvent): string;

    /**
     * @return array<string, mixed>
     */
    public function fromSiu(string $message): array;
}
