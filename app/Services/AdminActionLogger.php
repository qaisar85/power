<?php

namespace App\Services;

use App\Models\AdminActionLog;
use App\Models\Admin;

class AdminActionLogger
{
    public static function log(int $adminId, string $actionType, ?int $targetId = null, ?string $targetType = null, ?string $comment = null): void
    {
        AdminActionLog::create([
            'admin_id' => $adminId,
            'action_type' => $actionType,
            'target_id' => $targetId,
            'target_type' => $targetType,
            'comment' => $comment,
            'performed_at' => now(),
        ]);
    }
}