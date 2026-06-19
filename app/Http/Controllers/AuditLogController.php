<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->user()->hasPermission('view_audit_logs')) {
            abort(403, __('Unauthorized to view audit logs'));
        }

        try {
            $query = AuditLog::with('user')
                ->where('model_type', 'User');

            if ($request->filter_action) {
                $query->where('action', $request->filter_action);
            }

            if ($request->filter_date_from) {
                $query->whereDate('created_at', '>=', $request->filter_date_from);
            }

            if ($request->filter_date_to) {
                $query->whereDate('created_at', '<=', $request->filter_date_to);
            }

            $logs = $query->orderByDesc('created_at')->paginate(50);
        } catch (\Throwable $e) {
            \Log::warning('Audit logs query failed: ' . $e->getMessage());
            $logs = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 50);
        }

        return view('admin.audit-logs.index', compact('logs'));
    }

    public function show(AuditLog $log)
    {
        if (!auth()->user()->hasPermission('view_audit_logs')) {
            abort(403);
        }

        return view('admin.audit-logs.show', compact('log'));
    }
}
