<?php

namespace Modules\Appointment\Console\Commands;

use Illuminate\Console\Command;
use Modules\Appointment\Enums\SyncOutboxStatus;
use Modules\Appointment\Models\AppointmentSyncOutbox;

class ProcessAppointmentSyncOutboxCommand extends Command
{
    protected $signature = 'appointment:process-sync-outbox {--limit=50 : Maximum rows to process in one run}';

    protected $description = 'Dispatch pending appointment sync outbox rows (stub: marks eligible rows completed until real integrations exist).';

    public function handle(): int
    {
        $limit = max(1, (int) $this->option('limit'));

        $processed = 0;

        AppointmentSyncOutbox::withoutGlobalScopes()
            ->due()
            ->orderBy('available_at')
            ->orderBy('created_at')
            ->limit($limit)
            ->get()
            ->each(function (AppointmentSyncOutbox $row) use (&$processed): void {
                // Stub processor: replace with HTTP/client dispatch + retries/backoff.
                $row->update([
                    'status' => SyncOutboxStatus::COMPLETED,
                    'processed_at' => now(),
                    'last_error' => null,
                ]);
                $processed++;
            });

        $this->info("Processed {$processed} outbox row(s).");

        return self::SUCCESS;
    }
}
