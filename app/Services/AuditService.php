<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Throwable;

class AuditService
{
    public function record(string $action, Model|string|null $entity = null, array $old = [], array $new = [], ?Request $request = null): void
    {
        try {
            $entityType = $entity instanceof Model ? class_basename($entity) : $entity;
            $entityId = $entity instanceof Model ? $entity->getKey() : null;

            AuditLog::create([
                'actor_user_id' => \Illuminate\Support\Facades\Auth::id(),
                'action' => $action,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'old_values' => $old ?: null,
                'new_values' => $new ?: null,
                'ip_address' => ($request ?? request())->ip(),
                'user_agent' => mb_substr((string) ($request ?? request())->userAgent(), 0, 500),
            ]);
        } catch (Throwable) {
            // Auditing errors are logged by Laravel; they should not break patient care workflows.
        }
    }
}
