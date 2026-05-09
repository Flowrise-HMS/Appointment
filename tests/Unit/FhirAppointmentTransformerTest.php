<?php

namespace Modules\Appointment\Tests\Unit;

use Modules\Appointment\Classes\Services\FhirAppointmentTransformer;
use Modules\Appointment\Contracts\FhirAppointmentTransformerContract;
use PHPUnit\Framework\TestCase;

class FhirAppointmentTransformerTest extends TestCase
{
    public function test_transformer_implements_expected_contract(): void
    {
        $transformer = new FhirAppointmentTransformer;

        $this->assertInstanceOf(FhirAppointmentTransformerContract::class, $transformer);
        $this->assertTrue(method_exists($transformer, 'toFhir'));
    }
}
