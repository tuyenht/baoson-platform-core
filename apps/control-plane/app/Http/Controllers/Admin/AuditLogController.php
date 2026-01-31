<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuditLogController extends Controller
{
    public function index(Request $request): Response
    {
        $q = trim((string) $request->query('q', ''));
        $event = trim((string) $request->query('event', ''));

        $logs = AuditLog::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($q2) use ($q) {
                    $q2->where('event', 'like', "%{$q}%")
                        ->orWhere('actor_email', 'like', "%{$q}%")
                        ->orWhere('ip', 'like', "%{$q}%")
                        ->orWhere('path', 'like', "%{$q}%");
                });
            })
            ->when($event !== '', fn ($query) => $query->where('event', $event))
            ->orderByDesc('occurred_at')
            ->paginate(50)
            ->withQueryString();

        return Inertia::render('Admin/AuditLogs/Index', [
            'filters' => [
                'q' => $q,
                'event' => $event,
            ],
            'logs' => $logs,
        ]);
    }
}


