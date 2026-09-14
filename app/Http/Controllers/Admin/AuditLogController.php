<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');

        $logs = AuditLog::with('user')
            ->when($search, function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
            })
            ->orderBy('id', 'desc')
            ->paginate(30);

        return view('results.admin.audit_logs.index', compact('logs', 'search'));
    }
}
