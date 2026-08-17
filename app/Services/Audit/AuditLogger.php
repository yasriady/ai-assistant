<?php

namespace App\Services\Audit;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogger
{
    /**
     * @param  array<string, mixed>|null  $oldValue
     * @param  array<string, mixed>|null  $newValue
     */
    public function log(
        string $action,
        Model|string $entity,
        ?int $entityId = null,
        ?array $oldValue = null,
        ?array $newValue = null,
        ?string $reason = null,
        ?int $userId = null,
    ): AuditLog {
        if ($entity instanceof Model) {
            $entityType = $entity->getMorphClass();
            $entityId = $entity->getKey();
        } else {
            $entityType = $entity;
        }

        return AuditLog::query()->create([
            'user_id' => $userId ?? Auth::id(),
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => (int) $entityId,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'reason' => $reason,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'created_at' => now(),
        ]);
    }
}
