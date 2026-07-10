<?php

use Tests\TestCase;

uses(TestCase::class);

use Modules\Appointment\Classes\Fhir\FhirAppointmentResponseTransformer;
use Modules\Appointment\Enums\AppointmentParticipantStatus;
use Modules\Appointment\Models\Appointment;
use Modules\Appointment\Models\AppointmentParticipant;
use Modules\FHIR\Contracts\FhirResourceContract;

$transformer = new FhirAppointmentResponseTransformer;

test('implements FhirResourceContract', function () use ($transformer) {
    expect($transformer)->toBeInstanceOf(FhirResourceContract::class);
});

test('resourceType returns AppointmentResponse', function () use ($transformer) {
    expect($transformer->resourceType())->toBe('AppointmentResponse');
});

test('toFhir contains required fields', function () use ($transformer) {
    $participant = new class extends AppointmentParticipant
    {
        public $timestamps = false;
    };
    $participant->setAttribute('id', 'ap-0001');
    $participant->setAttribute('appointment_id', 'apt-uuid');
    $participant->setAttribute('participant_type_code', 'PPRF');
    $participant->setAttribute('actor_reference', 'Practitioner/prac-1');
    $participant->setAttribute('status', AppointmentParticipantStatus::ACCEPTED);
    $participant->setAttribute('required', true);

    $fhir = $transformer->toFhir($participant);

    expect($fhir)->toHaveKey('resourceType', 'AppointmentResponse');
    expect($fhir)->toHaveKey('id', 'ap-0001');
    expect($fhir)->toHaveKey('appointment', ['reference' => 'Appointment/apt-uuid']);
    expect($fhir)->toHaveKey('participantStatus', 'accepted');
    expect($fhir)->toHaveKey('participantType');
    expect($fhir)->toHaveKey('actor', ['reference' => 'Practitioner/prac-1']);
});

test('toFhir maps participantType coding', function () use ($transformer) {
    $participant = new class extends AppointmentParticipant
    {
        public $timestamps = false;
    };
    $participant->setAttribute('id', 'ap-type-1');
    $participant->setAttribute('appointment_id', 'apt-uuid');
    $participant->setAttribute('participant_type_code', 'PPRF');
    $participant->setAttribute('actor_reference', 'Practitioner/prac-1');
    $participant->setAttribute('status', AppointmentParticipantStatus::ACCEPTED);
    $participant->setAttribute('required', false);

    $fhir = $transformer->toFhir($participant);

    expect($fhir['participantType'][0]['coding'][0]['system'])->toBe('http://terminology.hl7.org/CodeSystem/v3-ParticipationType');
    expect($fhir['participantType'][0]['coding'][0]['code'])->toBe('PPRF');
});

test('toFhir omits participantType when null', function () use ($transformer) {
    $participant = new class extends AppointmentParticipant
    {
        public $timestamps = false;
    };
    $participant->setAttribute('id', 'ap-notype-1');
    $participant->setAttribute('appointment_id', 'apt-uuid');
    $participant->setAttribute('participant_type_code', null);
    $participant->setAttribute('actor_reference', 'Practitioner/prac-1');
    $participant->setAttribute('status', AppointmentParticipantStatus::ACCEPTED);
    $participant->setAttribute('required', false);

    $fhir = $transformer->toFhir($participant);

    expect($fhir)->not->toHaveKey('participantType');
});

test('toFhir omits actor when null', function () use ($transformer) {
    $participant = new class extends AppointmentParticipant
    {
        public $timestamps = false;
    };
    $participant->setAttribute('id', 'ap-noactor-1');
    $participant->setAttribute('appointment_id', 'apt-uuid');
    $participant->setAttribute('participant_type_code', 'PPRF');
    $participant->setAttribute('actor_reference', null);
    $participant->setAttribute('status', AppointmentParticipantStatus::ACCEPTED);
    $participant->setAttribute('required', false);

    $fhir = $transformer->toFhir($participant);

    expect($fhir)->not->toHaveKey('actor');
});

test('toFhir omits optional fields when null', function () use ($transformer) {
    $participant = new class extends AppointmentParticipant
    {
        public $timestamps = false;
    };
    $participant->setAttribute('id', 'ap-min-1');
    $participant->setAttribute('appointment_id', 'apt-uuid');
    $participant->setAttribute('participant_type_code', null);
    $participant->setAttribute('actor_reference', null);
    $participant->setAttribute('status', AppointmentParticipantStatus::NEEDS_ACTION);
    $participant->setAttribute('required', false);

    $fhir = $transformer->toFhir($participant);

    expect($fhir)->not->toHaveKey('participantType');
    expect($fhir)->not->toHaveKey('actor');
});

test('toFhir maps start and end from appointment relation', function () use ($transformer) {
    $start = now();
    $end = now()->addHour();

    $appointment = new class extends Appointment
    {
        public $timestamps = false;
    };
    $appointment->setAttribute('id', 'apt-date-1');
    $appointment->setAttribute('start_at', $start);
    $appointment->setAttribute('end_at', $end);

    $participant = new class extends AppointmentParticipant
    {
        public $timestamps = false;
    };
    $participant->setAttribute('id', 'ap-date-1');
    $participant->setAttribute('appointment_id', 'apt-date-1');
    $participant->setAttribute('participant_type_code', 'PPRF');
    $participant->setAttribute('actor_reference', 'Practitioner/prac-1');
    $participant->setAttribute('status', AppointmentParticipantStatus::ACCEPTED);
    $participant->setAttribute('required', false);
    $participant->setRelation('appointment', $appointment);

    $fhir = $transformer->toFhir($participant);

    expect($fhir['start'])->toBe($start->toIso8601String());
    expect($fhir['end'])->toBe($end->toIso8601String());
});

test('toFhir omits start and end when appointment not loaded', function () use ($transformer) {
    $participant = new class extends AppointmentParticipant
    {
        public $timestamps = false;
    };
    $participant->setAttribute('id', 'ap-nodate-1');
    $participant->setAttribute('appointment_id', 'apt-uuid');
    $participant->setAttribute('participant_type_code', 'PPRF');
    $participant->setAttribute('actor_reference', 'Practitioner/prac-1');
    $participant->setAttribute('status', AppointmentParticipantStatus::ACCEPTED);
    $participant->setAttribute('required', false);

    $fhir = $transformer->toFhir($participant);

    expect($fhir)->not->toHaveKey('start');
    expect($fhir)->not->toHaveKey('end');
});

test('fromFhir extracts all fields correctly', function () use ($transformer) {
    $fhirResource = [
        'resourceType' => 'AppointmentResponse',
        'appointment' => ['reference' => 'Appointment/apt-uuid'],
        'participantType' => [
            ['coding' => [
                ['system' => 'http://terminology.hl7.org/CodeSystem/v3-ParticipationType', 'code' => 'PPRF'],
            ]],
        ],
        'actor' => ['reference' => 'Practitioner/prac-uuid'],
        'participantStatus' => 'accepted',
    ];

    $attrs = $transformer->fromFhir($fhirResource);

    expect($attrs)->toHaveKey('appointment_id', 'apt-uuid');
    expect($attrs)->toHaveKey('participant_type_code', 'PPRF');
    expect($attrs)->toHaveKey('actor_reference', 'Practitioner/prac-uuid');
    expect($attrs)->toHaveKey('status', 'accepted');
});

test('fromFhir handles minimal resource', function () use ($transformer) {
    $fhirResource = [
        'resourceType' => 'AppointmentResponse',
        'appointment' => ['reference' => 'Appointment/apt-uuid'],
        'participantStatus' => 'needs-action',
    ];

    $attrs = $transformer->fromFhir($fhirResource);

    expect($attrs)->toHaveKey('appointment_id', 'apt-uuid');
    expect($attrs)->toHaveKey('status', 'needs-action');
    expect($attrs)->not->toHaveKey('participant_type_code');
    expect($attrs)->not->toHaveKey('actor_reference');
});

test('searchableParameters has expected keys', function () use ($transformer) {
    $params = $transformer->searchableParameters();

    expect($params)->toHaveKeys(['_id', 'appointment', 'actor', 'participant-status', 'participant-type']);
    expect($params['appointment'])->toHaveKey('column', 'appointment_id');
    expect($params['actor'])->toHaveKey('column', 'actor_reference');
    expect($params['participant-status'])->toHaveKey('column', 'status');
    expect($params['participant-type'])->toHaveKey('column', 'participant_type_code');
});

test('query method builds expected eager loads', function () use ($transformer) {
    $query = $transformer->query();

    expect($query->getEagerLoads())->toHaveKey('appointment');
});

test('validateBusinessRules passes with actor', function () use ($transformer) {
    $resource = [
        'resourceType' => 'AppointmentResponse',
        'appointment' => ['reference' => 'Appointment/abc'],
        'actor' => ['reference' => 'Practitioner/xyz'],
        'participantStatus' => 'accepted',
    ];

    $errors = $transformer->validateBusinessRules($resource);

    expect($errors)->toBeEmpty();
});

test('validateBusinessRules passes with participantType', function () use ($transformer) {
    $resource = [
        'resourceType' => 'AppointmentResponse',
        'appointment' => ['reference' => 'Appointment/abc'],
        'participantType' => [
            ['coding' => [['code' => 'PPRF']]],
        ],
        'participantStatus' => 'accepted',
    ];

    $errors = $transformer->validateBusinessRules($resource);

    expect($errors)->toBeEmpty();
});

test('validateBusinessRules fails without type or actor', function () use ($transformer) {
    $resource = [
        'resourceType' => 'AppointmentResponse',
        'appointment' => ['reference' => 'Appointment/abc'],
        'participantStatus' => 'accepted',
    ];

    $errors = $transformer->validateBusinessRules($resource);

    expect($errors)->toHaveKey('AppointmentResponse.participantType');
});
