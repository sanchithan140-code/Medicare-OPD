<?php

namespace App\Services;

use App\Models\Notification;

class NotificationService
{
    public function create(int $userId, string $type, string $title, string $message, ?string $relatedType = null, ?int $relatedId = null): Notification
    {
        return Notification::create([
            'user_id' => $userId,
            'notification_type' => $type,
            'title' => $title,
            'message' => $message,
            'related_type' => $relatedType,
            'related_id' => $relatedId,
            'delivery_status' => 'PORTAL_ONLY',
        ]);
    }
}
