<?php

namespace Modules\Appointment\Contracts;

interface PractitionerDirectoryContract
{
    public function isPractitionerAvailable(string $practitionerId, string $branchId, string $startAt, string $endAt): bool;

    public function canManageAppointments(string $practitionerId): bool;
}
