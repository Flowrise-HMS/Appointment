<?php

namespace Modules\Appointment\Classes\Services;

use Modules\Appointment\Models\ScheduleBlock;

class ScheduleBlockService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): ScheduleBlock
    {
        return ScheduleBlock::create($data);
    }
}
