<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user');

        if ($request->filled('modulo')) {
            $query->where('modulo', $request->input('modulo'));
        }

        if ($request->filled('accion')) {
            $query->where('accion', $request->input('accion'));
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->input('fecha_desde'));
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->input('fecha_hasta'));
        }

        $logs = $query->latest('created_at')->paginate(20);
        $modulos = AuditLog::select('modulo')->distinct()->pluck('modulo');

        return view('audit.index', compact('logs', 'modulos'));
    }

    public function export(Request $request)
    {
        $query = AuditLog::with('user');

        if ($request->filled('modulo')) {
            $query->where('modulo', $request->input('modulo'));
        }

        $logs = $query->latest('created_at')->get();

        $response = new StreamedResponse(function () use ($logs) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Fecha', 'Usuario', 'Modulo', 'Accion', 'Descripcion', 'IP']);

            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log->id,
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->user ? $log->user->name : 'Sistema/Invitado',
                    $log->modulo,
                    $log->accion,
                    $log->descripcion,
                    $log->ip_address,
                ]);
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="bitacora_auditoria_' . date('Ymd_His') . '.csv"');

        return $response;
    }
}
