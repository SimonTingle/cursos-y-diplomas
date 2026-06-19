<?php

namespace App\Traits;

use App\Models\AuditLog;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            try {
                AuditLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'created',
                    'model_type' => class_basename($model),
                    'model_id' => $model->id,
                    'new_values' => self::filterSensitiveFields($model->getAttributes()),
                    'ip_address' => request()?->ip(),
                    'user_agent' => request()?->userAgent(),
                ]);
            } catch (\Exception $e) {
                if (strpos($e->getMessage(), 'no such table: audit_logs') === false) {
                    throw;
                }
            }
        });

        static::updated(function ($model) {
            $changes = $model->getChanges();
            if (empty($changes)) {
                return;
            }

            try {
                AuditLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'updated',
                    'model_type' => class_basename($model),
                    'model_id' => $model->id,
                    'old_values' => self::filterSensitiveFields($model->getOriginal()),
                    'new_values' => self::filterSensitiveFields($changes),
                    'ip_address' => request()?->ip(),
                    'user_agent' => request()?->userAgent(),
                ]);
            } catch (\Exception $e) {
                if (strpos($e->getMessage(), 'no such table: audit_logs') === false) {
                    throw;
                }
            }
        });

        static::deleted(function ($model) {
            try {
                AuditLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'deleted',
                    'model_type' => class_basename($model),
                    'model_id' => $model->id,
                    'old_values' => self::filterSensitiveFields($model->getAttributes()),
                    'ip_address' => request()?->ip(),
                    'user_agent' => request()?->userAgent(),
                ]);
            } catch (\Exception $e) {
                if (strpos($e->getMessage(), 'no such table: audit_logs') === false) {
                    throw;
                }
            }
        });
    }

    private static function filterSensitiveFields(array $values): array
    {
        $sensitive = ['password', 'remember_token', 'email_verified_at'];
        return array_filter($values,
            fn ($key) => !in_array($key, $sensitive),
            ARRAY_FILTER_USE_KEY
        );
    }
}
