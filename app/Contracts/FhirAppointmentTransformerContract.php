<?php

namespace Modules\Appointment\Contracts;

use Modules\Appointment\Models\Appointment;

interface FhirAppointmentTransformerContract
{
    /**
     * @return array<string, mixed>
     */
    public function toFhir(Appointment $appointment): array;
}
