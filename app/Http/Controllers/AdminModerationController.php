<?php

namespace App\Http\Controllers;

use App\Models\ModerationTask;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Services\AdminActionLogger;
use App\Notifications\ModerationStatusChanged;
use App\Notifications\NewModerationTask;
use App\Models\Admin;
use App\Models\AdminCategoryAssignment;

class AdminModerationController extends Controller
{
    public function index(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        abort_unless($admin && $admin->can('moderation'), 403);

        $status = $request->string('status')->toString() ?: 'pending';
        $type = $request->string('type')->toString();
        $sort = $request->string('sort')->toString() ?: 'date';
        $priority = $request->has('priority') ? (int) $request->input('priority') : null;

        $query = ModerationTask::query()
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($type, fn($q) => $q->where('item_type', $type))
            ->when(!is_null($priority), fn($q) => $q->where('priority', $priority));

        // Regional & category gating for non-super_admin
        if (!$admin->hasRole('super_admin')) {
            if (!empty($admin->country ?? null)) {
                $query->where('country', $admin->country);
            }
            if (!empty($admin->region ?? null)) {
                $query->where('region', $admin->region);
            }
            $assignments = AdminCategoryAssignment::where('admin_id', $admin->id)->get();
            $subsectorIds = $assignments->pluck('subsector_id')->filter()->values()->all();
            $categoryCodes = $assignments->pluck('category_code')->filter()->values()->all();
            if (count($subsectorIds) || count($categoryCodes)) {
                $query->where(function ($qq) use ($subsectorIds, $categoryCodes) {
                    if (count($subsectorIds)) {
                        $qq->whereIn('subsector_id', $subsectorIds);
                    }
                    if (count($categoryCodes)) {
                        $qq->orWhereIn('category_code', $categoryCodes);
                    }
                });
            }
        }

        $tasks = $query
            ->when($sort === 'priority', fn($q) => $q->orderByDesc('priority')->orderByDesc('id'), fn($q) => $q->orderByDesc('id'))
            ->paginate(20)
            ->through(function (ModerationTask $t) {
                return [
                    'id' => $t->id,
                    'item_type' => $t->item_type,
                    'item_id' => $t->item_id,
                    'submitted_by' => $t->submitted_by,
                    'submitted_by_name' => optional($t->submitter)->name,
                    'status' => $t->status,
                    'moderator_id' => $t->moderator_id,
                    'moderator_name' => optional($t->moderator)->name,
                    'comment' => $t->comment,
                    'priority' => (int) $t->priority,
                    'country' => $t->country,
                    'region' => $t->region,
                    'category_code' => $t->category_code,
                    'subsector_id' => $t->subsector_id,
                    'created_at' => $t->created_at?->toDateTimeString(),
                ];
            })
            ->appends([
                'status' => $status,
                'type' => $type,
                'sort' => $sort,
                'priority' => $priority,
            ]);

        return Inertia::render('Admin/Moderation/Index', [
            'filters' => [
                'status' => $status,
                'type' => $type,
                'sort' => $sort,
                'priority' => $priority,
            ],
            'tasks' => $tasks,
            'allowedPermissions' => $admin->getAllPermissions()->pluck('name'),
            'moderators' => Admin::all()
                ->filter(fn($a) => $a->can('moderation'))
                ->map(fn($m) => ['id' => $m->id, 'name' => $m->name])
                ->values(),
            'isChief' => $admin->hasRole('super_admin'),
        ]);
    }

    public function approve(ModerationTask $task, Request $request)
    {
        $admin = Auth::guard('admin')->user();
        abort_unless($admin && $admin->can('moderation'), 403);

        $comment = (string) $request->input('comment');
        $task->status = 'approved';
        $task->moderator_id = $task->moderator_id ?: $admin->id;
        $task->comment = $comment;
        $task->save();

        AdminActionLogger::log($admin, 'moderation.approve', [
            'task_id' => $task->id,
            'item_type' => $task->item_type,
            'item_id' => $task->item_id,
        ]);

        $submitter = User::find($task->submitted_by);
        if ($submitter) {
            $submitter->notify(new ModerationStatusChanged($task));
        }

        return redirect()->back()->with('success', 'Task approved');
    }

    public function decline(ModerationTask $task, Request $request)
    {
        $admin = Auth::guard('admin')->user();
        abort_unless($admin && $admin->can('moderation'), 403);

        $data = $request->validate([
            'reason' => ['required', 'string']
        ]);

        $task->status = 'declined';
        $task->moderator_id = $task->moderator_id ?: $admin->id;
        $task->comment = $data['reason'];
        $task->save();

        AdminActionLogger::log($admin, 'moderation.decline', [
            'task_id' => $task->id,
            'reason' => $data['reason'],
        ]);

        $submitter = User::find($task->submitted_by);
        if ($submitter) {
            $submitter->notify(new ModerationStatusChanged($task));
        }

        return redirect()->back()->with('success', 'Task declined');
    }

    public function requestRevision(ModerationTask $task, Request $request)
    {
        $admin = Auth::guard('admin')->user();
        abort_unless($admin && $admin->can('moderation'), 403);

        $data = $request->validate([
            'comment' => ['required', 'string']
        ]);

        $task->status = 'revision_requested';
        $task->moderator_id = $task->moderator_id ?: $admin->id;
        $task->comment = $data['comment'];
        $task->save();

        AdminActionLogger::log($admin, 'moderation.revision', [
            'task_id' => $task->id,
            'comment' => $data['comment'],
        ]);

        $submitter = User::find($task->submitted_by);
        if ($submitter) {
            $submitter->notify(new ModerationStatusChanged($task));
        }

        return redirect()->back()->with('success', 'Revision requested');
    }

    public function assignSelf(ModerationTask $task)
    {
        $admin = Auth::guard('admin')->user();
        abort_unless($admin && $admin->can('moderation'), 403);

        $task->moderator_id = $admin->id;
        $task->save();

        AdminActionLogger::log($admin, 'moderation.assign.self', [
            'task_id' => $task->id,
        ]);

        return redirect()->back()->with('success', 'Task assigned to you');
    }

    public function assignModerator(ModerationTask $task, Request $request)
    {
        $admin = Auth::guard('admin')->user();
        // Only super_admin (Chief Moderator) can assign others
        abort_unless($admin && $admin->hasRole('super_admin'), 403);

        $data = $request->validate([
            'moderator_id' => ['required', 'integer']
        ]);

        $task->moderator_id = $data['moderator_id'];
        $task->save();

        AdminActionLogger::log($admin, 'moderation.assign.moderator', [
            'task_id' => $task->id,
            'moderator_id' => $data['moderator_id'],
        ]);

        return redirect()->back()->with('success', 'Moderator assigned');
    }

    public function report(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        abort_unless($admin && $admin->can('moderation'), 403);

        $filters = [
            'item_type' => $request->string('item_type')->toString(),
            'from' => $request->string('from')->toString(),
            'to' => $request->string('to')->toString(),
        ];

        $apply = function ($builder) use ($filters, $admin) {
            $builder
                ->when($filters['item_type'], fn($q) => $q->where('item_type', $filters['item_type']))
                ->when($filters['from'], fn($q) => $q->where('created_at', '>=', $filters['from']))
                ->when($filters['to'], fn($q) => $q->where('created_at', '<=', $filters['to']));

            if (!$admin->hasRole('super_admin')) {
                if (!empty($admin->country ?? null)) {
                    $builder->where('country', $admin->country);
                }
                if (!empty($admin->region ?? null)) {
                    $builder->where('region', $admin->region);
                }
                $assignments = \App\Models\AdminCategoryAssignment::where('admin_id', $admin->id)->get();
                $subsectorIds = $assignments->pluck('subsector_id')->filter()->values()->all();
                $categoryCodes = $assignments->pluck('category_code')->filter()->values()->all();
                if (count($subsectorIds) || count($categoryCodes)) {
                    $builder->where(function ($qq) use ($subsectorIds, $categoryCodes) {
                        if (count($subsectorIds)) {
                            $qq->whereIn('subsector_id', $subsectorIds);
                        }
                        if (count($categoryCodes)) {
                            $qq->orWhereIn('category_code', $categoryCodes);
                        }
                    });
                }
            }
            return $builder;
        };

        $statusCounts = $apply(ModerationTask::query())
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $priorityCounts = $apply(ModerationTask::query())
            ->selectRaw('priority, COUNT(*) as total')
            ->groupBy('priority')
            ->pluck('total', 'priority');

        $daily = $apply(ModerationTask::query())
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->orderBy('day', 'desc')
            ->limit(30)
            ->get();

        return Inertia::render('Admin/Moderation/Report', [
            'filters' => $filters,
            'statusCounts' => $statusCounts,
            'priorityCounts' => $priorityCounts,
            'daily' => $daily,
        ]);
    }
}