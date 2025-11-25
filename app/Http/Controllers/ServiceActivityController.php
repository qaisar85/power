<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ServiceActivityController extends Controller
{
    public function index(Request $request)
    {
        $company = Company::where('user_id', $request->user()->id)->firstOrFail();

        $action = $request->query('action');
        $actions = (array) $request->query('actions', []); // multi-select support
        $from = $request->query('from');
        $to = $request->query('to');
        $actor = $request->query('actor');

        $query = ActivityLog::where('company_id', $company->id);
        if (!empty($actions)) {
            $query->whereIn('action', $actions);
        } elseif ($action) {
            $query->where('action', $action);
        }
        if ($from) { $query->whereDate('created_at', '>=', $from); }
        if ($to) { $query->whereDate('created_at', '<=', $to); }
        if ($actor) {
            $query->whereHas('actor', function($q) use ($actor) {
                $q->where('name', 'like', "%{$actor}%");
            });
        }

        $logs = $query->orderByDesc('id')->paginate(50)->appends($request->query());

        $activities = $logs->through(function($log){
            return [
                'date' => optional($log->created_at)->toDateTimeString(),
                'actor' => $log->actor ? ($log->actor->name ?? 'user') : 'system',
                'action' => $log->action,
                'context' => $log->context,
            ];
        });

        return Inertia::render('ServiceDashboard/Activity/Index', [
            'company' => [ 'id' => $company->id, 'name' => $company->name ],
            'activities' => $activities,
            'filters' => [
                'action' => $action,
                'actions' => $actions,
                'from' => $from,
                'to' => $to,
                'actor' => $actor,
            ],
        ]);
    }
}