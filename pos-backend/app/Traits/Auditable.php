<?php

namespace App\Traits;

use App\Models\PurchaseAuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    /**
     * Boot the trait
     */
    protected static function bootAuditable()
    {
        static::created(function ($model) {
            $model->logAudit('created');
        });

        static::updated(function ($model) {
            $model->logAudit('updated');
        });

        static::deleted(function ($model) {
            $model->logAudit('deleted');
        });

        static::restored(function ($model) {
            $model->logAudit('restored');
        });
    }

    /**
     * Log audit entry
     */
    public function logAudit($action, $changes = null, $oldValues = null, $newValues = null)
    {
        // Only log for Purchase model for now
        if (!$this instanceof \App\Models\Purchase) {
            return;
        }

        $data = [
            'purchase_id' => $this->id,
            'user_id' => Auth::id(),
            'action' => $action,
            'changes' => $changes ?: $this->getChanges(),
            'old_values' => $oldValues ?: $this->getOriginal(),
            'new_values' => $newValues ?: $this->getAttributes(),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent()
        ];

        PurchaseAuditLog::create($data);
    }

    /**
     * Get audit logs for this model
     */
    public function getAuditLogs()
    {
        if ($this instanceof \App\Models\Purchase) {
            return $this->auditLogs()->orderBy('created_at', 'desc')->get();
        }
        return collect();
    }

    /**
     * Get audit logs by action
     */
    public function getAuditLogsByAction($action)
    {
        if ($this instanceof \App\Models\Purchase) {
            return $this->auditLogs()->where('action', $action)->orderBy('created_at', 'desc')->get();
        }
        return collect();
    }
} 