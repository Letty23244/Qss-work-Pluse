<?php

namespace App\Notifications;

use App\Models\ProductionRecord;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Notifications\Notification;

class ProductionSubmitted extends Notification
{
    public function __construct(public ProductionRecord $record) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $r = $this->record->loadMissing(['team', 'client', 'assignment']);

        return FilamentNotification::make()
            ->title('Production submitted')
            ->body(sprintf(
                '%s · %s shift · %s: %s %s produced, %s pending.',
                $r->team?->name, ucfirst($r->shift_type), $r->assignment?->title,
                $r->quantity_produced, $r->assignment?->unit, $r->quantity_pending
            ))
            ->getDatabaseMessage() + ['production_record_id' => $r->id, 'client_id' => $r->client_id];
    }
}
