<?php

namespace App\Http\Controllers;

use App\Models\AdminActionLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminLogController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'action_type' => $request->string('action_type')->toString(),
            'admin_id' => $request->integer('admin_id'),
            'from' => $request->string('from')->toString(),
            'to' => $request->string('to')->toString(),
        ];

        $query = AdminActionLog::query()->with('admin')->orderByDesc('performed_at');

        if (!empty($filters['action_type'])) {
            $query->where('action_type', $filters['action_type']);
        }
        if (!empty($filters['admin_id'])) {
            $query->where('admin_id', $filters['admin_id']);
        }
        if (!empty($filters['from'])) {
            $query->where('performed_at', '>=', $filters['from']);
        }
        if (!empty($filters['to'])) {
            $query->where('performed_at', '<=', $filters['to']);
        }

        $logs = $query->paginate(15)->appends(array_filter($filters));

        return Inertia::render('Admin/Logs', [
            'logs' => $logs,
            'filters' => $filters,
        ]);
    }
}