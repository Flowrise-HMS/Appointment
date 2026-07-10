<?php

namespace Modules\Appointment\Classes\Fhir;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Modules\Appointment\Models\AppointmentParticipant;
use Modules\FHIR\Contracts\FhirResourceContract;

class FhirAppointmentResponseTransformer implements FhirResourceContract
{
    public function resourceType(): string
    {
        return 'AppointmentResponse';
    }

    public function toFhir(Model $model): array
    {
        $resource = [
            'resourceType' => 'AppointmentResponse',
            'id' => $model->id,
            'appointment' => ['reference' => "Appointment/{$model->appointment_id}"],
            'participantStatus' => $model->status->value,
        ];

        if ($model->participant_type_code) {
            $resource['participantType'] = [
                [
                    'coding' => [
                        [
                            'system' => 'http://terminology.hl7.org/CodeSystem/v3-ParticipationType',
                            'code' => $model->participant_type_code,
                        ],
                    ],
                ],
            ];
        }

        if ($model->actor_reference) {
            $resource['actor'] = ['reference' => $model->actor_reference];
        }

        if ($model->relationLoaded('appointment') && $model->appointment) {
            if ($model->appointment->start_at) {
                $resource['start'] = $model->appointment->start_at->toIso8601String();
            }
            if ($model->appointment->end_at) {
                $resource['end'] = $model->appointment->end_at->toIso8601String();
            }
        }

        return $resource;
    }

    public function fromFhir(array $fhirResource): array
    {
        $attrs = [];

        if (isset($fhirResource['appointment']['reference'])) {
            $attrs['appointment_id'] = str_replace('Appointment/', '', $fhirResource['appointment']['reference']);
        }

        if (isset($fhirResource['participantType'][0]['coding'][0]['code'])) {
            $attrs['participant_type_code'] = $fhirResource['participantType'][0]['coding'][0]['code'];
        }

        if (isset($fhirResource['actor']['reference'])) {
            $attrs['actor_reference'] = $fhirResource['actor']['reference'];
        }

        if (isset($fhirResource['participantStatus'])) {
            $attrs['status'] = $fhirResource['participantStatus'];
        }

        return $attrs;
    }

    public function findById(string $id): ?Model
    {
        return AppointmentParticipant::with('appointment')->find($id);
    }

    public function query(): Builder
    {
        return AppointmentParticipant::with('appointment');
    }

    public function searchableParameters(): array
    {
        return [
            '_id' => ['column' => 'id'],
            'appointment' => ['column' => 'appointment_id'],
            'actor' => ['column' => 'actor_reference'],
            'participant-status' => ['column' => 'status'],
            'participant-type' => ['column' => 'participant_type_code'],
        ];
    }

    public function validateBusinessRules(array $fhirResource): array
    {
        $errors = [];

        $hasType = isset($fhirResource['participantType']) && ! empty($fhirResource['participantType']);
        $hasActor = isset($fhirResource['actor']['reference']);

        if (! $hasType && ! $hasActor) {
            $errors['AppointmentResponse.participantType'] = 'Either participantType or actor SHALL be specified';
        }

        return $errors;
    }
}
