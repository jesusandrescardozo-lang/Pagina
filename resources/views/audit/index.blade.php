@extends('layouts.app')

@section('title', 'Bitácora de Auditoría')
@section('page-title', 'Bitácora de Auditoría y Trazabilidad')
@section('page-subtitle', 'Registro inmutable de todas las operaciones y eventos de seguridad en la plataforma')

@section('content')

<!-- 1. COCKPIT HEADER CON TELEMETRÍA DE AUDITORÍA -->
<div class="cockpit-page-header">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge" style="background: rgba(139, 92, 246, 0.2); color: #a78bfa; font-size: 0.72rem; border: 1px solid rgba(139, 92, 246, 0.4); border-radius: 9999px; padding: 3px 10px;">
                <i class="fa-solid fa-shield-halved me-1"></i> Trazabilidad SHA-256
            </span>
            <span class="badge" style="background: rgba(14, 165, 233, 0.2); color: #38bdf8; font-size: 0.72rem; border: 1px solid rgba(14, 165, 233, 0.4); border-radius: 9999px; padding: 3px 10px;">
                {{ $logs->total() }} Eventos Registrados
            </span>
        </div>
        <h3 class="cockpit-header-title">Registro y Bitácora de Seguridad</h3>
        <p class="cockpit-header-subtitle">Monitoreo de accesos, modificaciones de inventario y acciones de usuarios</p>
    </div>

    <div class="d-flex align-items-center gap-2 flex-wrap">
        <a href="{{ route('audit.export') }}" class="btn-glass text-info">
            <i class="fa-solid fa-file-csv me-1"></i> Exportar CSV
        </a>
    </div>
</div>

<!-- 2. FILTROS EN VIDRIO OSCURO -->
<div class="glass-card p-4 mb-4">
    <form method="GET" action="{{ route('audit.index') }}" class="row g-3 align-items-end">
        <div class="col-md-3">
            <label class="form-label-glass">Módulo</label>
            <select name="modulo" class="form-select-glass">
                <option value="">-- Todos los Módulos --</option>
                @foreach($modulos as $m)
                <option value="{{ $m }}" {{ request('modulo') == $m ? 'selected' : '' }}>{{ $m }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label-glass">Fecha Desde</label>
            <input type="date" name="fecha_desde" class="form-control-glass" value="{{ request('fecha_desde') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label-glass">Fecha Hasta</label>
            <input type="date" name="fecha_hasta" class="form-control-glass" value="{{ request('fecha_hasta') }}">
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn-accent flex-grow-1 justify-content-center">
                <i class="fa-solid fa-filter me-1"></i> Filtrar
            </button>
            <a href="{{ route('audit.index') }}" class="btn-glass" title="Restablecer">
                <i class="fa-solid fa-rotate-left"></i>
            </a>
        </div>
    </form>
</div>

<!-- 3. TABLA DE BITÁCORA -->
<div class="glass-card p-4">
    <div class="table-responsive">
        <table class="dark-table">
            <thead>
                <tr>
                    <th>Fecha y Hora</th>
                    <th>Usuario</th>
                    <th>Módulo</th>
                    <th>Acción</th>
                    <th>Descripción</th>
                    <th class="text-end">IP Origen</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td style="font-size: 0.76rem; color: rgba(226, 232, 240, 0.7); white-space: nowrap;">
                        <i class="fa-regular fa-clock me-1 text-info"></i> {{ $log->created_at->format('d/m/Y H:i:s') }}
                    </td>
                    <td>
                        <strong class="text-white">{{ $log->user->name ?? 'Sistema / Automático' }}</strong>
                    </td>
                    <td>
                        <span class="badge" style="background: rgba(14, 165, 233, 0.15); color: #38bdf8; font-size: 0.72rem; border: 1px solid rgba(14, 165, 233, 0.3);">
                            {{ $log->modulo }}
                        </span>
                    </td>
                    <td>
                        <span class="bg-glass bg-ok" style="font-size: 0.70rem;">{{ $log->accion }}</span>
                    </td>
                    <td style="max-width: 320px; font-size: 0.78rem;" class="text-muted">
                        {{ $log->descripcion }}
                    </td>
                    <td class="text-end">
                        <code style="color: #38bdf8; font-size: 0.76rem;">{{ $log->ip_address ?: '127.0.0.1' }}</code>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="fa-solid fa-shield-halved fs-1 mb-3 text-secondary d-block"></i>
                        No se encontraron registros de auditoría con los filtros aplicados.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top border-secondary border-opacity-25">
        <span class="text-muted small">Página {{ $logs->currentPage() }} de {{ $logs->lastPage() }}</span>
        <div>
            {{ $logs->withQueryString()->links() }}
        </div>
    </div>
</div>

@endsection
