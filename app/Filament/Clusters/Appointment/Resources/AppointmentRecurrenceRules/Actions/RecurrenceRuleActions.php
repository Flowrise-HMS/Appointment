<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentRecurrenceRules\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Gate;
use Modules\Appointment\Classes\Services\RecurrenceExpansionService;
use Modules\Appointment\Models\AppointmentRecurrenceRule;

/**
 * Shared "generate occurrences" / "cancel series" behaviour for the recurrence
 * resource pages, its table and the appointment relation manager.
 */
class RecurrenceRuleActions
{
    /**
     * Expand the rule and tell the user what was booked or skipped.
     */
    public static function expandAndNotify(AppointmentRecurrenceRule $rule): void
    {
        $result = app(RecurrenceExpansionService::class)->expandWithReport($rule->refresh());

        if (! $rule->is_active) {
            return;
        }

        Notification::make()
            ->title(__('Recurring appointments'))
            ->body($result->summary())
            ->color($result->conflicts === [] ? 'success' : 'warning')
            ->icon($result->conflicts === [] ? Heroicon::OutlinedCheckCircle : Heroicon::OutlinedExclamationTriangle)
            ->send();
    }

    public static function generate(): Action
    {
        return Action::make('generateOccurrences')
            ->label(__('Generate occurrences'))
            ->icon(Heroicon::OutlinedArrowPath)
            ->visible(fn (AppointmentRecurrenceRule $record): bool => $record->is_active && Gate::allows('update', $record))
            ->action(fn (AppointmentRecurrenceRule $record) => static::expandAndNotify($record));
    }

    public static function cancelSeries(): Action
    {
        return Action::make('cancelSeries')
            ->label(__('Cancel series'))
            ->icon(Heroicon::OutlinedXCircle)
            ->color('danger')
            ->requiresConfirmation()
            ->modalDescription(__('Deactivates the rule and cancels its future occurrences. Past visits are kept.'))
            ->visible(fn (AppointmentRecurrenceRule $record): bool => $record->is_active && Gate::allows('update', $record))
            ->action(function (AppointmentRecurrenceRule $record): void {
                $cancelled = app(RecurrenceExpansionService::class)->cancelSeries($record);

                Notification::make()
                    ->title(__('Series cancelled'))
                    ->body(trans_choice(':count future occurrence cancelled.|:count future occurrences cancelled.', $cancelled, ['count' => $cancelled]))
                    ->success()
                    ->send();
            });
    }
}
