<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

/** Records who created / edited / deleted a record and what changed. */
trait Auditable
{
    public static function bootAuditable(): void
    {
        foreach (['created', 'updated', 'deleted'] as $event) {
            static::$event(function (Model $m) use ($event) {
                $skip = array_merge($m->getHidden(), ['updated_at', 'created_at']);

                $new = $old = null;
                if ($event === 'created') {
                    $new = Arr::except($m->getAttributes(), $skip);
                } elseif ($event === 'updated') {
                    $new = Arr::except($m->getChanges(), $skip);
                    if (empty($new)) return;
                    $old = Arr::only($m->getRawOriginal(), array_keys($new));
                } else {
                    $old = Arr::except($m->getAttributes(), $skip);
                }

                AuditLog::create([
                    'user_id'        => Auth::id(),
                    'event'          => $event,
                    'auditable_type' => $m::class,
                    'auditable_id'   => $m->getKey(),
                    'old_values'     => $old,
                    'new_values'     => $new,
                    'ip_address'     => request()?->ip(),
                ]);
            });
        }
    }
}
