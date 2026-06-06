<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditLogObserver
{
    public function created(Model $model): void
    {
        if ($this->shouldAudit($model)) {
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'created',
                'model' => class_basename($model),
                'record_id' => $model->id,
                'changes' => $model->toArray(),
            ]);
        }
    }

    public function updated(Model $model): void
    {
        if ($this->shouldAudit($model)) {
            $changes = [];
            foreach ($model->getChanges() as $key => $value) {
                $changes[$key] = [
                    'old' => $model->getOriginal($key),
                    'new' => $value,
                ];
            }

            if (! empty($changes)) {
                AuditLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'updated',
                    'model' => class_basename($model),
                    'record_id' => $model->id,
                    'changes' => $changes,
                ]);
            }
        }
    }

    public function deleted(Model $model): void
    {
        if ($this->shouldAudit($model)) {
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'deleted',
                'model' => class_basename($model),
                'record_id' => $model->id,
                'changes' => $model->toArray(),
            ]);
        }
    }

    private function shouldAudit(Model $model): bool
    {
        $auditableModels = [
            'Student',
            'Payment',
            'Expense',
            'ExpenseCategory',
            'FeeStructure',
            'Term',
            'AcademicYear',
            'SchoolClass',
            'StudentEnrollment',
        ];

        return in_array(class_basename($model), $auditableModels) && auth()->check();
    }
}
