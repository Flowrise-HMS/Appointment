<?php

namespace Modules\Appointment\Classes\Fhir;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Modules\Appointment\Models\Appointment;
use Modules\Appointment\Models\AppointmentParticipant;
use Modules\FHIR\Contracts\FhirResourceContract;

class FhirAppointmentTransformer implements FhirResourceContract
{
    public function resourceType(): string
    {
        return 'Appointment';
    }

    public function toFhir(Model $model): array
    {
        $resource = [
            'resourceType' => 'Appointment',
            'id' => $model->id,
            'status' => $model->status->value,
            'priority' => [
                'coding' => [[
                    'system' => 'http://hl7.org/fhir/appointment-priority',
                    'code' => (string) $model->priority,
                    'display' => (string) $model->priority,
                ]],
            ],
            'description' => $model->reason_text,
            'created' => optional($model->created_at)->toIso8601String(),
        ];

        if ($model->external_reference) {
            $resource['identifier'] = [[
                'system' => 'urn:oid:flowrise-hms',
                'value' => $model->external_reference,
            ]];
        }

        if ($model->service_category_code) {
            $resource['serviceCategory'] = [[
                'coding' => [[
                    'system' => 'http://terminology.hl7.org/CodeSystem/service-category',
                    'code' => $model->service_category_code,
                ]],
            ]];
        }

        if ($model->service_type_code) {
            $resource['serviceType'] = [[
                'reference' => "HealthcareService/{$model->service_type_code}",
                'display' => $model->service_type_code,
            ]];
        }

        if ($model->reason_code) {
            $resource['reason'] = [[
                'concept' => [
                    'coding' => [[
                        'code' => $model->reason_code,
                    ]],
                ],
                'text' => $model->reason_text,
            ]];
        }

        if ($model->appointment_type) {
            $resource['appointmentType'] = [
                'coding' => [[
                    'system' => 'http://terminology.hl7.org/CodeSystem/v2-0276',
                    'code' => $model->appointment_type->value,
                ]],
            ];
        }

        if ($model->start_at) {
            $resource['start'] = $model->start_at->toIso8601String();
        }

        if ($model->end_at) {
            $resource['end'] = $model->end_at->toIso8601String();
        }

        if ($model->start_at && $model->end_at) {
            $resource['minutesDuration'] = (int) $model->start_at->diffInMinutes($model->end_at);
        }

        if ($model->notes_encrypted) {
            $resource['note'] = [[
                'text' => $model->notes_encrypted,
            ]];
        }

        if ($model->patient_id) {
            $resource['subject'] = ['reference' => "Patient/{$model->patient_id}"];
        }

        if (in_array($model->status->value, ['cancelled', 'noshow']) && $model->cancellation_reason_code) {
            $resource['cancellationReason'] = [
                'coding' => [[
                    'code' => $model->cancellation_reason_code,
                ]],
            ];
        }

        if ($model->relationLoaded('participants')) {
            $resource['participant'] = $model->participants
                ->map(fn (AppointmentParticipant $p) => $this->mapParticipant($p))
                ->values()
                ->all();
        }

        if ($model->relationLoaded('primaryPractitioner') && $model->primaryPractitioner) {
            $specialties = $model->primaryPractitioner->specialties ?? collect();
            if ($specialties->isNotEmpty()) {
                $resource['specialty'] = $specialties->map(fn ($s) => [
                    'coding' => [[
                        'code' => $s->code ?? $s->name ?? (string) $s,
                    ]],
                ])->values()->all();
            }
        }

        return $resource;
    }

    private function mapParticipant(AppointmentParticipant $p): array
    {
        $entry = [
            'status' => $p->status->value,
            'required' => $p->required,
        ];

        if ($p->participant_type_code) {
            $entry['type'] = [[
                'coding' => [[
                    'system' => 'http://terminology.hl7.org/CodeSystem/v3-ParticipationType',
                    'code' => $p->participant_type_code,
                ]],
            ]];
        }

        if ($p->actor_reference) {
            $entry['actor'] = ['reference' => $p->actor_reference];
        }

        return $entry;
    }

    public function fromFhir(array $fhirResource): array
    {
        $attrs = [];

        if (isset($fhirResource['status'])) {
            $attrs['status'] = $fhirResource['status'];
        }

        if (isset($fhirResource['appointmentType']['coding'][0]['code'])) {
            $attrs['appointment_type'] = $fhirResource['appointmentType']['coding'][0]['code'];
        }

        if (isset($fhirResource['priority']['coding'][0]['code'])) {
            $attrs['priority'] = (int) $fhirResource['priority']['coding'][0]['code'];
        }

        if (isset($fhirResource['description'])) {
            $attrs['reason_text'] = $fhirResource['description'];
        } elseif (isset($fhirResource['reason'][0]['text'])) {
            $attrs['reason_text'] = $fhirResource['reason'][0]['text'];
        }

        if (isset($fhirResource['reason'][0]['concept']['coding'][0]['code'])) {
            $attrs['reason_code'] = $fhirResource['reason'][0]['concept']['coding'][0]['code'];
        }

        if (isset($fhirResource['start'])) {
            $attrs['start_at'] = $fhirResource['start'];
        }

        if (isset($fhirResource['end'])) {
            $attrs['end_at'] = $fhirResource['end'];
        }

        if (isset($fhirResource['note'][0]['text'])) {
            $attrs['notes_encrypted'] = collect($fhirResource['note'])->pluck('text')->implode("\n");
        }

        if (isset($fhirResource['cancellationReason']['coding'][0]['code'])) {
            $attrs['cancellation_reason_code'] = $fhirResource['cancellationReason']['coding'][0]['code'];
        }

        if (isset($fhirResource['serviceCategory'][0]['coding'][0]['code'])) {
            $attrs['service_category_code'] = $fhirResource['serviceCategory'][0]['coding'][0]['code'];
        }

        if (isset($fhirResource['serviceType'][0]['reference'])) {
            $attrs['service_type_code'] = str_replace('HealthcareService/', '', $fhirResource['serviceType'][0]['reference']);
        }

        if (isset($fhirResource['subject']['reference'])) {
            $attrs['patient_id'] = str_replace('Patient/', '', $fhirResource['subject']['reference']);
        }

        return $attrs;
    }

    public function findById(string $id): ?Model
    {
        return Appointment::withTrashed()
            ->with(['participants', 'patient', 'primaryPractitioner.specialties'])
            ->find($id);
    }

    public function query(): Builder
    {
        return Appointment::with(['participants', 'patient', 'primaryPractitioner.specialties']);
    }

    public function searchableParameters(): array
    {
        return [
            '_id' => ['column' => 'id'],
            'status' => ['column' => 'status'],
            'patient' => ['column' => 'patient_id'],
            'practitioner' => ['column' => 'practitioner_primary_id'],
            'date' => ['column' => 'start_at'],
            'identifier' => ['column' => 'external_reference'],
            'service-type' => ['column' => 'service_type_code'],
            'location' => ['column' => 'location_id'],
            'actor' => ['relation' => 'participants', 'column' => 'actor_reference'],
            'part-status' => ['relation' => 'participants', 'column' => 'status'],
        ];
    }

    public function validateBusinessRules(array $fhirResource): array
    {
        $errors = [];
        $status = $fhirResource['status'] ?? null;
        $hasStart = isset($fhirResource['start']);
        $hasEnd = isset($fhirResource['end']);

        if ($hasStart xor $hasEnd) {
            $errors['Appointment.start'] = 'Either start and end SHALL both be specified, or neither';
        }

        if (! $hasStart && ! $hasEnd && ! in_array($status, ['proposed', 'cancelled', 'noshow', null])) {
            $errors['Appointment.start'] = "Only proposed, cancelled, or noshow appointments can omit start/end dates (status: {$status})";
        }

        if (isset($fhirResource['cancellationReason']) && ! in_array($status, ['cancelled', 'noshow'])) {
            $errors['Appointment.cancellationReason'] = 'cancellationReason is only valid for cancelled or noshow appointments';
        }

        if (isset($fhirResource['participant'])) {
            foreach ($fhirResource['participant'] as $i => $p) {
                $hasType = isset($p['type']) && ! empty($p['type']);
                $hasActor = isset($p['actor']['reference']);
                if (! $hasType && ! $hasActor) {
                    $errors["Appointment.participant[{$i}]"] = "Each participant SHALL have at least one of type or actor (participant {$i})";
                }
            }
        }

        return $errors;
    }
}
