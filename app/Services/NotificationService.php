<?php

namespace App\Services;

use App\Models\PlatformNotification;
use App\Models\User;

class NotificationService
{
    public function notifyUser(
        int $userId,
        string $title,
        string $message,
        string $type = 'sistema',
        string $severity = 'info',
        ?string $actionUrl = null
    ): PlatformNotification {
        return PlatformNotification::create([
            'user_id' => $userId,
            'type' => $type,
            'severity' => $severity,
            'title' => $title,
            'message' => $message,
            'action_url' => $actionUrl,
        ]);
    }

    public function notifyRole(
        string $role,
        string $title,
        string $message,
        string $type = 'sistema',
        string $severity = 'info',
        ?string $actionUrl = null
    ): void {
        $userIds = User::where('role', $role)->where('is_active', true)->pluck('id');
        foreach ($userIds as $uid) {
            $this->notifyUser($uid, $title, $message, $type, $severity, $actionUrl);
        }
    }

    public function markAsRead(int $notificationId, int $userId): void
    {
        PlatformNotification::where('id', $notificationId)
            ->where('user_id', $userId)
            ->update(['read_at' => now()]);
    }

    public function markAllAsRead(int $userId): void
    {
        PlatformNotification::where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}
