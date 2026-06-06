<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Inertia\Inertia;

class AuditLogController extends Controller
{
    public function index()
    {
        return Inertia::render('AuditLogs/Index', [
            'logs' => AuditLog::latest()->get(),
        ]);
    }

    public function show(AuditLog $auditLog)
    {
        return Inertia::render('AuditLogs/Show', [
            'log' => $auditLog,
        ]);
    }
}
