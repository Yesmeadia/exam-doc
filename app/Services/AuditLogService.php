<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogService
{
    /**
     * Record an audit log entry.
     */
    public static function log(string $action, ?Model $model = null, array $details = [], ?int $userId = null): AuditLog
    {
        $resolvedUserId = $userId ?? Auth::id();
        $ip = Request::ip();

        return AuditLog::create([
            'user_id' => $resolvedUserId,
            'action' => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model?->getKey(),
            'details' => $details,
            'ip_address' => $ip,
            'created_at' => now(),
        ]);
    }
}
