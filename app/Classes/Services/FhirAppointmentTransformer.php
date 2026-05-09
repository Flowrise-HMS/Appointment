<?php

namespace Modules\Appointment\Classes\Services;

use Modules\Appointment\Contracts\FhirAppointmentTransformerContract;
use Modules\Appointment\Models\Appointment;
use Modules\Appointment\Models\AppointmentParticipant;

class FhirAppointmentTransformer implements FhirAppointmentTransformerContract
{
    public function toFhir(Appointment $appointment): array
    {
        return [
            'resourceType' => 'Appointment',
            'id' => $appointment->id,
            'status' => $appointment->status->value,
            'serviceCategory' => $appointment->service_category_code ? [[
                'coding' => [[
                    'system' => 'http://terminology.hl7.org/CodeSystem/service-category',
                    'code' => $appointment->service_category_code,
                ]],
            ]] : [],
            'serviceType' => $appointment->service_type_code ? [[
                'coding' => [[
                    'system' => 'http://snomed.info/sct',
                    'code' => $appointment->service_type_code,
                ]],
            ]] : [],
            'priority' => $appointment->priority,
            'description' => $appointment->reason_text,
            'start' => optional($appointment->start_at)->toIso8601String(),
            'end' => optional($appointment->end_at)->toIso8601String(),
            'comment' => $appointment->notes_encrypted,
            'participant' => $appointment->participants
                ->map(function (AppointmentParticipant $participant) {
                    return [
                        'type' => $participant->participant_type_code ? [[
                            'coding' => [[
                                'system' => 'http://terminology.hl7.org/CodeSystem/v3-ParticipationType',
                                'code' => $participant->participant_type_code,
                            ]],
                        ]] : [],
                        'actor' => ['reference' => $participant->actor_reference],
                        'required' => $participant->required ? 'required' : 'optional',
                        'status' => $participant->status->value,
                    ];
                })
                ->values()
                ->all(),
        ];
    }
}
