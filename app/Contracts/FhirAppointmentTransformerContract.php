<?php

namespace Modules\Appointment\Contracts;

use Modules\Appointment\Models\Appointment;

/**
 * @deprecated Use Modules\FHIR\Contracts\FhirResourceContract instead.
 *             The new FhirAppointmentTransformer implements FhirResourceContract directly.
 */
interface FhirAppointmentTransformerContract
{
    /**
     * @return array<string, mixed>
     */
    public function toFhir(Appointment $appointment): array;
}
