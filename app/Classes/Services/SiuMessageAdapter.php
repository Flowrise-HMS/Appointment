<?php

namespace Modules\Appointment\Classes\Services;

use Modules\Appointment\Contracts\SiuMessageAdapterContract;
use Modules\Appointment\Models\Appointment;

class SiuMessageAdapter implements SiuMessageAdapterContract
{
    public function toSiu(Appointment $appointment, string $triggerEvent): string
    {
        $event = strtoupper($triggerEvent);
        $timestamp = now()->format('YmdHis');
        $start = optional($appointment->start_at)->format('YmdHis');
        $end = optional($appointment->end_at)->format('YmdHis');

        return implode("\r", [
            "MSH|^~\\&|FLOWRISE|FLOWRISE_HMS|DOWNSTREAM|DOWNSTREAM_FACILITY|{$timestamp}||SIU^{$event}|{$appointment->id}|P|2.5",
            "SCH|||{$appointment->id}|||||{$appointment->status}|{$start}|{$end}",
        ]);
    }

    public function fromSiu(string $message): array
    {
        $segments = preg_split('/\r\n|\r|\n/', trim($message)) ?: [];
        $msh = collect($segments)->first(fn (string $line) => str_starts_with($line, 'MSH|'));
        $sch = collect($segments)->first(fn (string $line) => str_starts_with($line, 'SCH|'));

        return [
            'raw' => $message,
            'msh' => $msh ? explode('|', $msh) : [],
            'sch' => $sch ? explode('|', $sch) : [],
        ];
    }
}
