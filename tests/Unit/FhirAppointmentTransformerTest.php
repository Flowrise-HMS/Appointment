<?php

use Tests\TestCase;

uses(TestCase::class);

use Illuminate\Support\Collection;
use Modules\Appointment\Classes\Fhir\FhirAppointmentTransformer;
use Modules\Appointment\Enums\AppointmentParticipantStatus;
use Modules\Appointment\Enums\AppointmentStatus;
use Modules\Appointment\Enums\AppointmentType;
use Modules\Appointment\Models\Appointment;
use Modules\Appointment\Models\AppointmentParticipant;
use Modules\FHIR\Contracts\FhirResourceContract;

$transformer = new FhirAppointmentTransformer;

test('implements FhirResourceContract', function () use ($transformer) {
    expect($transformer)->toBeInstanceOf(FhirResourceContract::class);
});

test('resourceType returns Appointment', function () use ($transformer) {
    expect($transformer->resourceType())->toBe('Appointment');
});

test('toFhir contains required fields', function () use ($transformer) {
    $appointment = new class extends Appointment
    {
        public $timestamps = false;
    };
    $appointment->setAttribute('id', 'apt-0001-aaaa');
    $appointment->setAttribute('status', AppointmentStatus::BOOKED);
    $appointment->setAttribute('priority', 1);
    $appointment->setAttribute('reason_text', 'Annual checkup');

    $fhir = $transformer->toFhir($appointment);

    expect($fhir)->toHaveKey('resourceType', 'Appointment');
    expect($fhir)->toHaveKey('id', 'apt-0001-aaaa');
    expect($fhir)->toHaveKey('status', 'booked');
    expect($fhir)->toHaveKey('description', 'Annual checkup');
    expect($fhir['priority']['coding'][0]['code'])->toBe('1');
});

test('toFhir maps all statuses correctly', function () use ($transformer) {
    $statuses = [
        AppointmentStatus::PROPOSED,
        AppointmentStatus::PENDING,
        AppointmentStatus::BOOKED,
        AppointmentStatus::ARRIVED,
        AppointmentStatus::FULFILLED,
        AppointmentStatus::CANCELLED,
        AppointmentStatus::NOSHOW,
    ];

    foreach ($statuses as $status) {
        $appointment = new class extends Appointment
        {
            public $timestamps = false;
        };
        $appointment->setAttribute('id', 'apt-status-'.$status->value);
        $appointment->setAttribute('status', $status);
        $appointment->setAttribute('priority', 0);

        $fhir = $transformer->toFhir($appointment);

        expect($fhir['status'])->toBe($status->value);
    }
});

test('toFhir maps appointment type to coding', function () use ($transformer) {
    $appointment = new class extends Appointment
    {
        public $timestamps = false;
    };
    $appointment->setAttribute('id', 'apt-type-1');
    $appointment->setAttribute('status', AppointmentStatus::BOOKED);
    $appointment->setAttribute('priority', 0);
    $appointment->setAttribute('appointment_type', AppointmentType::EMERGENCY);

    $fhir = $transformer->toFhir($appointment);

    expect($fhir['appointmentType']['coding'][0]['system'])->toBe('http://terminology.hl7.org/CodeSystem/v2-0276');
    expect($fhir['appointmentType']['coding'][0]['code'])->toBe('emergency');
});

test('toFhir omits appointment type when null', function () use ($transformer) {
    $appointment = new class extends Appointment
    {
        public $timestamps = false;
    };
    $appointment->setAttribute('id', 'apt-notype-1');
    $appointment->setAttribute('status', AppointmentStatus::BOOKED);
    $appointment->setAttribute('priority', 0);

    $fhir = $transformer->toFhir($appointment);

    expect($fhir)->not->toHaveKey('appointmentType');
});

test('toFhir maps priority', function () use ($transformer) {
    $appointment = new class extends Appointment
    {
        public $timestamps = false;
    };
    $appointment->setAttribute('id', 'apt-pri-1');
    $appointment->setAttribute('status', AppointmentStatus::BOOKED);
    $appointment->setAttribute('priority', 5);

    $fhir = $transformer->toFhir($appointment);

    expect($fhir['priority']['coding'][0]['system'])->toBe('http://hl7.org/fhir/appointment-priority');
    expect($fhir['priority']['coding'][0]['code'])->toBe('5');
    expect($fhir['priority']['coding'][0]['display'])->toBe('5');
});

test('toFhir maps identifier from external_reference', function () use ($transformer) {
    $appointment = new class extends Appointment
    {
        public $timestamps = false;
    };
    $appointment->setAttribute('id', 'apt-ext-1');
    $appointment->setAttribute('status', AppointmentStatus::BOOKED);
    $appointment->setAttribute('priority', 0);
    $appointment->setAttribute('external_reference', 'EXT-REF-12345');

    $fhir = $transformer->toFhir($appointment);

    expect($fhir['identifier'][0]['system'])->toBe('urn:oid:flowrise-hms');
    expect($fhir['identifier'][0]['value'])->toBe('EXT-REF-12345');
});

test('toFhir omits identifier when no external_reference', function () use ($transformer) {
    $appointment = new class extends Appointment
    {
        public $timestamps = false;
    };
    $appointment->setAttribute('id', 'apt-noext-1');
    $appointment->setAttribute('status', AppointmentStatus::BOOKED);
    $appointment->setAttribute('priority', 0);

    $fhir = $transformer->toFhir($appointment);

    expect($fhir)->not->toHaveKey('identifier');
});

test('toFhir maps serviceCategory', function () use ($transformer) {
    $appointment = new class extends Appointment
    {
        public $timestamps = false;
    };
    $appointment->setAttribute('id', 'apt-sc-1');
    $appointment->setAttribute('status', AppointmentStatus::BOOKED);
    $appointment->setAttribute('priority', 0);
    $appointment->setAttribute('service_category_code', 'ROUTINE');

    $fhir = $transformer->toFhir($appointment);

    expect($fhir['serviceCategory'][0]['coding'][0]['system'])->toBe('http://terminology.hl7.org/CodeSystem/service-category');
    expect($fhir['serviceCategory'][0]['coding'][0]['code'])->toBe('ROUTINE');
});

test('toFhir maps serviceType reference', function () use ($transformer) {
    $appointment = new class extends Appointment
    {
        public $timestamps = false;
    };
    $appointment->setAttribute('id', 'apt-st-1');
    $appointment->setAttribute('status', AppointmentStatus::BOOKED);
    $appointment->setAttribute('priority', 0);
    $appointment->setAttribute('service_type_code', 'CARDIO');

    $fhir = $transformer->toFhir($appointment);

    expect($fhir['serviceType'][0]['reference'])->toBe('HealthcareService/CARDIO');
    expect($fhir['serviceType'][0]['display'])->toBe('CARDIO');
});

test('toFhir maps reason with code and text', function () use ($transformer) {
    $appointment = new class extends Appointment
    {
        public $timestamps = false;
    };
    $appointment->setAttribute('id', 'apt-reas-1');
    $appointment->setAttribute('status', AppointmentStatus::BOOKED);
    $appointment->setAttribute('priority', 0);
    $appointment->setAttribute('reason_code', 'ROUTINE');
    $appointment->setAttribute('reason_text', 'Annual physical examination');

    $fhir = $transformer->toFhir($appointment);

    expect($fhir['reason'][0]['concept']['coding'][0]['code'])->toBe('ROUTINE');
    expect($fhir['reason'][0]['text'])->toBe('Annual physical examination');
});

test('toFhir maps start and end dates with minutesDuration', function () use ($transformer) {
    $start = now();
    $end = now()->addMinutes(45);

    $appointment = new class extends Appointment
    {
        public $timestamps = false;
    };
    $appointment->setAttribute('id', 'apt-date-1');
    $appointment->setAttribute('status', AppointmentStatus::BOOKED);
    $appointment->setAttribute('priority', 0);
    $appointment->setAttribute('start_at', $start);
    $appointment->setAttribute('end_at', $end);

    $fhir = $transformer->toFhir($appointment);

    expect($fhir['start'])->toBe($start->toIso8601String());
    expect($fhir['end'])->toBe($end->toIso8601String());
    expect($fhir['minutesDuration'])->toBe(45);
});

test('toFhir omits start and end when not set', function () use ($transformer) {
    $appointment = new class extends Appointment
    {
        public $timestamps = false;
    };
    $appointment->setAttribute('id', 'apt-nodate-1');
    $appointment->setAttribute('status', AppointmentStatus::PROPOSED);
    $appointment->setAttribute('priority', 0);

    $fhir = $transformer->toFhir($appointment);

    expect($fhir)->not->toHaveKey('start');
    expect($fhir)->not->toHaveKey('end');
    expect($fhir)->not->toHaveKey('minutesDuration');
});

test('toFhir maps note from notes_encrypted', function () use ($transformer) {
    $appointment = new class extends Appointment
    {
        public $timestamps = false;
    };
    $appointment->setAttribute('id', 'apt-note-1');
    $appointment->setAttribute('status', AppointmentStatus::BOOKED);
    $appointment->setAttribute('priority', 0);
    $appointment->setAttribute('notes_encrypted', 'Patient prefers morning appointments');

    $fhir = $transformer->toFhir($appointment);

    expect($fhir['note'][0]['text'])->toBe('Patient prefers morning appointments');
});

test('toFhir maps subject to patient reference', function () use ($transformer) {
    $appointment = new class extends Appointment
    {
        public $timestamps = false;
    };
    $appointment->setAttribute('id', 'apt-pat-1');
    $appointment->setAttribute('status', AppointmentStatus::BOOKED);
    $appointment->setAttribute('priority', 0);
    $appointment->setAttribute('patient_id', 'patient-uuid');

    $fhir = $transformer->toFhir($appointment);

    expect($fhir['subject']['reference'])->toBe('Patient/patient-uuid');
});

test('toFhir omits subject when no patient', function () use ($transformer) {
    $appointment = new class extends Appointment
    {
        public $timestamps = false;
    };
    $appointment->setAttribute('id', 'apt-nopat-1');
    $appointment->setAttribute('status', AppointmentStatus::BOOKED);
    $appointment->setAttribute('priority', 0);

    $fhir = $transformer->toFhir($appointment);

    expect($fhir)->not->toHaveKey('subject');
});

test('toFhir maps cancellationReason for cancelled status', function () use ($transformer) {
    $appointment = new class extends Appointment
    {
        public $timestamps = false;
    };
    $appointment->setAttribute('id', 'apt-cancel-1');
    $appointment->setAttribute('status', AppointmentStatus::CANCELLED);
    $appointment->setAttribute('priority', 0);
    $appointment->setAttribute('cancellation_reason_code', 'patient-request');

    $fhir = $transformer->toFhir($appointment);

    expect($fhir['cancellationReason']['coding'][0]['code'])->toBe('patient-request');
});

test('toFhir maps cancellationReason for noshow status', function () use ($transformer) {
    $appointment = new class extends Appointment
    {
        public $timestamps = false;
    };
    $appointment->setAttribute('id', 'apt-noshow-1');
    $appointment->setAttribute('status', AppointmentStatus::NOSHOW);
    $appointment->setAttribute('priority', 0);
    $appointment->setAttribute('cancellation_reason_code', 'no-show');

    $fhir = $transformer->toFhir($appointment);

    expect($fhir['cancellationReason']['coding'][0]['code'])->toBe('no-show');
});

test('toFhir omits cancellationReason for non-cancelled status', function () use ($transformer) {
    $appointment = new class extends Appointment
    {
        public $timestamps = false;
    };
    $appointment->setAttribute('id', 'apt-nocancel-1');
    $appointment->setAttribute('status', AppointmentStatus::BOOKED);
    $appointment->setAttribute('priority', 0);
    $appointment->setAttribute('cancellation_reason_code', 'patient-request');

    $fhir = $transformer->toFhir($appointment);

    expect($fhir)->not->toHaveKey('cancellationReason');
});

test('toFhir maps participants', function () use ($transformer) {
    $participant = new class extends AppointmentParticipant
    {
        public $timestamps = false;
    };
    $participant->setAttribute('id', 'ap-1');
    $participant->setAttribute('participant_type_code', 'PPRF');
    $participant->setAttribute('actor_reference', 'Practitioner/prac-1');
    $participant->setAttribute('status', AppointmentParticipantStatus::ACCEPTED);
    $participant->setAttribute('required', true);

    $appointment = new class extends Appointment
    {
        public $timestamps = false;
    };
    $appointment->setAttribute('id', 'apt-part-1');
    $appointment->setAttribute('status', AppointmentStatus::BOOKED);
    $appointment->setAttribute('priority', 0);
    $appointment->setRelation('participants', new Collection([$participant]));

    $fhir = $transformer->toFhir($appointment);

    expect($fhir['participant'][0]['status'])->toBe('accepted');
    expect($fhir['participant'][0]['required'])->toBeTrue();
    expect($fhir['participant'][0]['type'][0]['coding'][0]['code'])->toBe('PPRF');
    expect($fhir['participant'][0]['actor']['reference'])->toBe('Practitioner/prac-1');
});

test('toFhir omits participant when not loaded', function () use ($transformer) {
    $appointment = new class extends Appointment
    {
        public $timestamps = false;
    };
    $appointment->setAttribute('id', 'apt-nopart-1');
    $appointment->setAttribute('status', AppointmentStatus::BOOKED);
    $appointment->setAttribute('priority', 0);

    $fhir = $transformer->toFhir($appointment);

    expect($fhir)->not->toHaveKey('participant');
});

test('toFhir maps specialty from primaryPractitioner', function () use ($transformer) {
    $appointment = new class extends Appointment
    {
        public $timestamps = false;
    };
    $appointment->setAttribute('id', 'apt-spec-1');
    $appointment->setAttribute('status', AppointmentStatus::BOOKED);
    $appointment->setAttribute('priority', 0);

    $specialty = new class
    {
        public string $code = 'CARDIO';
    };
    $practitioner = new class
    {
        public $specialties;
    };
    $practitioner->specialties = collect([$specialty]);
    $appointment->setRelation('primaryPractitioner', $practitioner);

    $fhir = $transformer->toFhir($appointment);

    expect($fhir['specialty'][0]['coding'][0]['code'])->toBe('CARDIO');
});

test('toFhir omits specialty when no specialties', function () use ($transformer) {
    $appointment = new class extends Appointment
    {
        public $timestamps = false;
    };
    $appointment->setAttribute('id', 'apt-nospec-1');
    $appointment->setAttribute('status', AppointmentStatus::BOOKED);
    $appointment->setAttribute('priority', 0);

    $practitioner = new class {};
    $appointment->setRelation('primaryPractitioner', $practitioner);

    $fhir = $transformer->toFhir($appointment);

    expect($fhir)->not->toHaveKey('specialty');
});

test('fromFhir extracts all fields correctly', function () use ($transformer) {
    $fhirResource = [
        'resourceType' => 'Appointment',
        'status' => 'booked',
        'appointmentType' => [
            'coding' => [
                ['system' => 'http://terminology.hl7.org/CodeSystem/v2-0276', 'code' => 'emergency'],
            ],
        ],
        'priority' => [
            'coding' => [
                ['code' => '1'],
            ],
        ],
        'description' => 'Annual checkup',
        'reason' => [
            [
                'concept' => [
                    'coding' => [['code' => 'ROUTINE']],
                ],
                'text' => 'Annual physical examination',
            ],
        ],
        'start' => '2026-07-10T09:00:00+00:00',
        'end' => '2026-07-10T10:00:00+00:00',
        'note' => [
            ['text' => 'Patient prefers morning'],
        ],
        'cancellationReason' => [
            'coding' => [['code' => 'patient-request']],
        ],
        'serviceCategory' => [
            ['coding' => [['system' => 'http://terminology.hl7.org/CodeSystem/service-category', 'code' => 'ROUTINE']]],
        ],
        'serviceType' => [
            ['reference' => 'HealthcareService/CARDIO'],
        ],
        'subject' => ['reference' => 'Patient/patient-uuid'],
    ];

    $attrs = $transformer->fromFhir($fhirResource);

    expect($attrs)->toHaveKey('status', 'booked');
    expect($attrs)->toHaveKey('appointment_type', 'emergency');
    expect($attrs)->toHaveKey('priority', 1);
    expect($attrs)->toHaveKey('reason_text', 'Annual checkup');
    expect($attrs)->toHaveKey('reason_code', 'ROUTINE');
    expect($attrs)->toHaveKey('start_at', '2026-07-10T09:00:00+00:00');
    expect($attrs)->toHaveKey('end_at', '2026-07-10T10:00:00+00:00');
    expect($attrs)->toHaveKey('notes_encrypted', 'Patient prefers morning');
    expect($attrs)->toHaveKey('cancellation_reason_code', 'patient-request');
    expect($attrs)->toHaveKey('service_category_code', 'ROUTINE');
    expect($attrs)->toHaveKey('service_type_code', 'CARDIO');
    expect($attrs)->toHaveKey('patient_id', 'patient-uuid');
});

test('fromFhir handles minimal resource', function () use ($transformer) {
    $fhirResource = [
        'resourceType' => 'Appointment',
        'status' => 'proposed',
        'subject' => ['reference' => 'Patient/patient-uuid'],
    ];

    $attrs = $transformer->fromFhir($fhirResource);

    expect($attrs)->toHaveKey('status', 'proposed');
    expect($attrs)->toHaveKey('patient_id', 'patient-uuid');
    expect($attrs)->not->toHaveKey('appointment_type');
    expect($attrs)->not->toHaveKey('start_at');
    expect($attrs)->not->toHaveKey('notes_encrypted');
});

test('fromFhir extracts reason_text from reason when no description', function () use ($transformer) {
    $fhirResource = [
        'resourceType' => 'Appointment',
        'status' => 'booked',
        'reason' => [
            ['text' => 'Follow-up visit'],
        ],
    ];

    $attrs = $transformer->fromFhir($fhirResource);

    expect($attrs)->toHaveKey('reason_text', 'Follow-up visit');
});

test('fromFhir concatenates multiple notes', function () use ($transformer) {
    $fhirResource = [
        'resourceType' => 'Appointment',
        'status' => 'booked',
        'note' => [
            ['text' => 'First note'],
            ['text' => 'Second note'],
        ],
    ];

    $attrs = $transformer->fromFhir($fhirResource);

    expect($attrs)->toHaveKey('notes_encrypted', "First note\nSecond note");
});

test('fromFhir extracts service_type_code from serviceType reference', function () use ($transformer) {
    $attrs = $transformer->fromFhir([
        'resourceType' => 'Appointment',
        'serviceType' => [
            ['reference' => 'HealthcareService/PHYSIO'],
        ],
    ]);

    expect($attrs)->toHaveKey('service_type_code', 'PHYSIO');
});

test('searchableParameters has expected keys', function () use ($transformer) {
    $params = $transformer->searchableParameters();

    expect($params)->toHaveKeys(['_id', 'status', 'patient', 'practitioner', 'date', 'identifier', 'service-type', 'location', 'actor', 'part-status']);
    expect($params['status'])->toHaveKey('column', 'status');
    expect($params['patient'])->toHaveKey('column', 'patient_id');
    expect($params['practitioner'])->toHaveKey('column', 'practitioner_primary_id');
    expect($params['date'])->toHaveKey('column', 'start_at');
    expect($params['identifier'])->toHaveKey('column', 'external_reference');
});

test('query method builds expected eager loads', function () use ($transformer) {
    $query = $transformer->query();

    expect($query->getEagerLoads())->toHaveKeys(['participants', 'patient', 'primaryPractitioner.specialties']);
});

test('validateBusinessRules passes valid appointment', function () use ($transformer) {
    $resource = [
        'resourceType' => 'Appointment',
        'status' => 'booked',
        'start' => '2026-07-10T09:00:00Z',
        'end' => '2026-07-10T10:00:00Z',
        'participant' => [
            ['actor' => ['reference' => 'Patient/abc']],
        ],
    ];

    $errors = $transformer->validateBusinessRules($resource);

    expect($errors)->toBeEmpty();
});

test('validateBusinessRules fails start without end', function () use ($transformer) {
    $resource = [
        'resourceType' => 'Appointment',
        'status' => 'booked',
        'start' => '2026-07-10T09:00:00Z',
    ];

    $errors = $transformer->validateBusinessRules($resource);

    expect($errors)->toHaveKey('Appointment.start');
});

test('validateBusinessRules passes proposed without start', function () use ($transformer) {
    $resource = [
        'resourceType' => 'Appointment',
        'status' => 'proposed',
    ];

    $errors = $transformer->validateBusinessRules($resource);

    expect($errors)->toBeEmpty();
});

test('validateBusinessRules fails without start/end for booked status', function () use ($transformer) {
    $resource = [
        'resourceType' => 'Appointment',
        'status' => 'booked',
    ];

    $errors = $transformer->validateBusinessRules($resource);

    expect($errors)->toHaveKey('Appointment.start');
});

test('validateBusinessRules fails cancellationReason for non-cancelled status', function () use ($transformer) {
    $resource = [
        'resourceType' => 'Appointment',
        'status' => 'booked',
        'start' => '2026-07-10T09:00:00Z',
        'end' => '2026-07-10T10:00:00Z',
        'cancellationReason' => [
            'coding' => [['code' => 'patient-request']],
        ],
    ];

    $errors = $transformer->validateBusinessRules($resource);

    expect($errors)->toHaveKey('Appointment.cancellationReason');
});

test('validateBusinessRules passes cancellationReason for cancelled status', function () use ($transformer) {
    $resource = [
        'resourceType' => 'Appointment',
        'status' => 'cancelled',
        'cancellationReason' => [
            'coding' => [['code' => 'patient-request']],
        ],
    ];

    $errors = $transformer->validateBusinessRules($resource);

    expect($errors)->toBeEmpty();
});

test('validateBusinessRules fails participant without type or actor', function () use ($transformer) {
    $resource = [
        'resourceType' => 'Appointment',
        'status' => 'booked',
        'start' => '2026-07-10T09:00:00Z',
        'end' => '2026-07-10T10:00:00Z',
        'participant' => [
            ['status' => 'accepted'],
        ],
    ];

    $errors = $transformer->validateBusinessRules($resource);

    expect($errors)->toHaveKey('Appointment.participant[0]');
});
