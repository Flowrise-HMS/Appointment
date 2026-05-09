<?php

namespace Modules\Appointment\Classes\Services;

use Modules\Appointment\Enums\AppointmentStatus;
use Modules\Appointment\Models\Appointment;
use Modules\Appointment\Models\AppointmentResource;
use Modules\Appointment\Models\ScheduleBlock;

class AppointmentConflictService
{
    public function hasPractitionerConflict(string $branchId, ?string $practitionerId, string $startAt, string $endAt, ?string $ignoreAppointmentId = null): bool
    {
        if (! $practitionerId) {
            return false;
        }

        $query = Appointment::query()
            ->where('branch_id', $branchId)
            ->where('practitioner_primary_id', $practitionerId)
            ->whereNull('deleted_at')
            ->whereNotIn('status', [AppointmentStatus::CANCELLED, AppointmentStatus::NOSHOW])
            ->where(function ($query) use ($startAt, $endAt) {
                $query->whereBetween('start_at', [$startAt, $endAt])
                    ->orWhereBetween('end_at', [$startAt, $endAt])
                    ->orWhere(function ($subQuery) use ($startAt, $endAt) {
                        $subQuery->where('start_at', '<=', $startAt)->where('end_at', '>=', $endAt);
                    });
            });

        if ($ignoreAppointmentId) {
            $query->where('id', '!=', $ignoreAppointmentId);
        }

        $blocked = ScheduleBlock::query()
            ->where('branch_id', $branchId)
            ->where('practitioner_id', $practitionerId)
            ->where(function ($query) use ($startAt, $endAt) {
                $query->whereBetween('blocked_from', [$startAt, $endAt])
                    ->orWhereBetween('blocked_to', [$startAt, $endAt])
                    ->orWhere(function ($subQuery) use ($startAt, $endAt) {
                        $subQuery->where('blocked_from', '<=', $startAt)->where('blocked_to', '>=', $endAt);
                    });
            })
            ->exists();

        return $query->exists() || $blocked;
    }

    public function hasResourceConflict(string $branchId, ?string $resourceReference, string $startAt, string $endAt, ?string $ignoreAppointmentId = null): bool
    {
        if (! $resourceReference) {
            return false;
        }

        $query = AppointmentResource::query()
            ->where('branch_id', $branchId)
            ->where('resource_reference', $resourceReference)
            ->where(function ($query) use ($startAt, $endAt) {
                $query->whereBetween('allocated_from', [$startAt, $endAt])
                    ->orWhereBetween('allocated_to', [$startAt, $endAt])
                    ->orWhere(function ($subQuery) use ($startAt, $endAt) {
                        $subQuery->where('allocated_from', '<=', $startAt)->where('allocated_to', '>=', $endAt);
                    });
            });

        if ($ignoreAppointmentId) {
            $query->where('appointment_id', '!=', $ignoreAppointmentId);
        }

        return $query->exists();
    }
}
