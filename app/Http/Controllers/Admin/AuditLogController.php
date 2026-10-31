<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Support\TabularExport;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(
            auth()->user()->hasRole('Super Admin') && auth()->user()->can('audit.view'),
            403,
            'Hanya Super Admin yang dapat melihat audit log.'
        );

        $query = $this->filteredQuery($request);

        if ($format = $this->exportFormat($request)) {
            $rows = (clone $query)->limit(10000)->get();
            $mapped = $rows->map(fn (AuditLog $log) => [
                $log->id,
                $log->created_at?->format('Y-m-d H:i:s'),
                $log->user?->email ?? '',
                $log->user?->name ?? '',
                $log->action,
                $log->description,
                $log->model_type ? class_basename($log->model_type) : '',
                $log->model_id,
                $log->ip_address,
            ]);

            TabularExport::logExport('audit-logs', $format, $mapped->count());

            return TabularExport::download(
                'audit-logs',
                ['ID', 'Waktu', 'Email', 'User', 'Action', 'Deskripsi', 'Model', 'Model ID', 'IP'],
                $mapped,
                $format,
                'Audit Log Super Admin'
            );
        }

        $logs = $query->paginate(25)->withQueryString();

        $actions = AuditLog::query()
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        $users = User::query()
            ->whereIn('id', AuditLog::query()->whereNotNull('user_id')->distinct()->pluck('user_id'))
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $stats = [
            'total' => AuditLog::count(),
            'today' => AuditLog::whereDate('created_at', today())->count(),
            'logins' => AuditLog::where('action', 'auth.login')->count(),
            'admin' => AuditLog::where(function ($q) {
                $q->where('action', 'like', 'admin.%')
                    ->orWhere('action', 'like', 'ops.%')
                    ->orWhere('action', 'like', 'master.%')
                    ->orWhere('action', 'like', 'report.%');
            })->count(),
        ];

        return view('admin.audit-logs.index', compact('logs', 'actions', 'users', 'stats'));
    }

    public function show(AuditLog $auditLog)
    {
        abort_unless(
            auth()->user()->hasRole('Super Admin') && auth()->user()->can('audit.view'),
            403,
            'Hanya Super Admin yang dapat melihat audit log.'
        );

        $auditLog->load('user');

        return view('admin.audit-logs.show', ['log' => $auditLog]);
    }

    protected function filteredQuery(Request $request)
    {
        $query = AuditLog::with('user')->latest('id');

        if ($search = trim((string) $request->get('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('action', 'like', "%{$search}%")
                    ->orWhere('model_type', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($u) use ($search) {
                        $u->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($action = $request->get('action')) {
            $query->where('action', $action);
        }

        if ($userId = $request->get('user_id')) {
            $query->where('user_id', $userId);
        }

        if ($from = $request->get('from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->get('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        return $query;
    }

    protected function exportFormat(Request $request): ?string
    {
        if ($request->filled('format')) {
            $format = strtolower((string) $request->input('format'));

            return in_array($format, ['csv', 'xlsx', 'pdf'], true) ? $format : null;
        }

        if ($request->boolean('export')) {
            return 'csv';
        }

        return null;
    }
}
