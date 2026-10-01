@extends('layouts.app')

@section('title', 'Transferencias y Rebalanceo Inter-Refugios')
@section('page-title', 'Rebalanceo Logístico y Transferencias')
@section('page-subtitle', 'Algoritmo de redistribución de insumos escasos entre refugios con superávit y déficit')

@section('content')

@php
    $enTransito = $transfers->where('estado', 'en_transito')->count();
    $solicitadas = $transfers->where('estado', 'solicitada')->count();
    $completadas = $transfers->where('estado', 'completada')->count();
@endphp

<!-- 1. COCKPIT HEADER CON TELEMETRÍA LOGÍSTICA -->
<div class="cockpit-page-header">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge" style="background: rgba(245, 158, 11, 0.2); color: #fbbf24; font-size: 0.72rem; border: 1px solid rgba(245, 158, 11, 0.4); border-radius: 9999px; padding: 3px 10px;">
                <i class="fa-solid fa-truck-ramp-box me-1"></i> Red de Distribución Inter-Refugios
            </span>
            <span class="badge" style="background: rgba(216, 150, 86, 0.15); color: #e6a86c; font-size: 0.72rem; border: 1px solid rgba(216, 150, 86, 0.35); border-radius: 9999px; padding: 3px 10px;">
                Optimización Heurística de Cobertura
            </span>
        </div>
        <h3 class="cockpit-header-title">Envíos y Rebalanceo Logístico</h3>
        <p class="cockpit-header-subtitle">Gestión de convoys humanitarios, redistribución de emergencia y control de tránsitos</p>
    </div>

    <div class="d-flex align-items-center gap-3 flex-wrap">
        <div class="d-flex gap-2">
            <div class="p-2 px-3 rounded-3" style="background: rgba(245,158,11,0.1); border: 1px solid rgba(245,158,11,0.3); text-align: center;">
                <span class="text-warning d-block" style="font-size: 0.68rem; text-transform: uppercase;">En Tránsito</span>
                <strong class="text-warning fs-6"><i class="fa-solid fa-truck-moving me-1"></i> {{ $enTransito }}</strong>
            </div>
            <div class="p-2 px-3 rounded-3" style="background: rgba(230,168,108,0.1); border: 1px solid rgba(230,168,108,0.3); text-align: center;">
                <span class="d-block" style="font-size: 0.68rem; text-transform: uppercase; color: var(--accent-light);">Por Aprobar</span>
                <strong class="fs-6" style="color: var(--accent-light);">{{ $solicitadas }}</strong>
            </div>
            <div class="p-2 px-3 rounded-3" style="background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); text-align: center;">
                <span class="text-success d-block" style="font-size: 0.68rem; text-transform: uppercase;">Completadas</span>
                <strong class="text-success fs-6">{{ $completadas }}</strong>
            </div>
        </div>

        <a href="{{ route('transfers.create') }}" class="btn-accent">
            <i class="fa-solid fa-plus"></i> Nueva Transferencia
        </a>
    </div>
</div>

<!-- 2. SUGERENCIAS HEURÍSTICAS DE REBALANCEO DE INVESTIGACIÓN DE OPERACIONES -->
@if(!empty($sugerencias))
<div class="glass-card p-4 mb-4" style="border-left: 4px solid var(--accent);">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h5 class="fw-bold text-white mb-1"><i class="fa-solid fa-route text-info me-2"></i> Rutas Recomendadas por el Algoritmo I.O.</h5>
            <p class="text-muted m-0" style="font-size: 0.78rem;">
                Detecta refugios en riesgo inminente (&lt; 2.5 días) y propone envíos automáticos desde refugios con excedente seguro (&gt; 6 días).
            </p>
        </div>
        <span class="bg-glass bg-amber">{{ count($sugerencias) }} acciones calculadas</span>
    </div>

    <div class="row g-3">
        @foreach($sugerencias as $sug)
        <div class="col-md-6 col-lg-4">
            <div class="p-3 rounded-3 h-100 d-flex flex-column justify-content-between" style="background: rgba(11, 15, 25, 0.7); border: 1px solid rgba(255, 255, 255, 0.08);">
                <div>
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <strong class="text-white">{{ $sug['supply']->nombre }}</strong>
                        <span class="bg-glass {{ $sug['prioridad'] === 'urgente' ? 'bg-err' : 'bg-warn' }}">{{ strtoupper($sug['prioridad']) }}</span>
                    </div>
                    <div class="text-muted mb-2" style="font-size: 0.76rem;">
                        <div><i class="fa-solid fa-arrow-up-from-bracket text-success me-1"></i> Origen: <strong class="text-white">{{ $sug['refugio_origen']->nombre }}</strong></div>
                        <div><i class="fa-solid fa-arrow-down-to-bracket text-danger me-1"></i> Destino: <strong class="text-white">{{ $sug['refugio_destino']->nombre }}</strong></div>
                    </div>
                    <div class="p-2 rounded-2 mb-2" style="background: rgba(255, 255, 255, 0.04); font-size: 0.74rem;">
                        <span class="stat-label mb-0 text-info">Cantidad Sugerida:</span>
                        <strong class="fs-6 text-white">{{ $sug['cantidad_sugerida'] }} {{ $sug['unidad_medida'] }}</strong>
                    </div>
                    <p class="text-muted" style="font-size: 0.72rem;">{{ $sug['motivo'] }}</p>
                </div>

                <a href="{{ route('transfers.create', ['origen_id' => $sug['refugio_origen']->id, 'destino_id' => $sug['refugio_destino']->id, 'supply_id' => $sug['supply']->id, 'cantidad' => $sug['cantidad_sugerida'], 'prioridad' => $sug['prioridad']]) }}" 
                   class="btn-accent w-100 justify-content-center text-center mt-2" style="padding: 7px; font-size: 0.78rem;">
                    <i class="fa-solid fa-paper-plane me-1"></i> Solicitar Esta Transferencia
                </a>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

<!-- 3. FILTROS EN VIDRIO OSCURO -->
<div class="glass-card p-3 mb-4">
    <form method="GET" action="{{ route('transfers.index') }}" class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
        <div class="d-flex flex-wrap gap-2 align-items-center flex-grow-1">
            <select name="estado" class="form-select-glass" style="max-width: 200px;">
                <option value="">Todos los Estados</option>
                <option value="solicitada" {{ request('estado') == 'solicitada' ? 'selected' : '' }}>Solicitadas</option>
                <option value="aprobada" {{ request('estado') == 'aprobada' ? 'selected' : '' }}>Aprobadas</option>
                <option value="en_transito" {{ request('estado') == 'en_transito' ? 'selected' : '' }}>En Tránsito (Convoy)</option>
                <option value="completada" {{ request('estado') == 'completada' ? 'selected' : '' }}>Completadas</option>
                <option value="cancelada" {{ request('estado') == 'cancelada' ? 'selected' : '' }}>Canceladas</option>
            </select>
            <select name="prioridad" class="form-select-glass" style="max-width: 180px;">
                <option value="">Toda Prioridad</option>
                <option value="urgente" {{ request('prioridad') == 'urgente' ? 'selected' : '' }}>Urgente</option>
                <option value="alta" {{ request('prioridad') == 'alta' ? 'selected' : '' }}>Alta</option>
                <option value="media" {{ request('prioridad') == 'media' ? 'selected' : '' }}>Media</option>
                <option value="baja" {{ request('prioridad') == 'baja' ? 'selected' : '' }}>Baja</option>
            </select>
            <button type="submit" class="btn-accent"><i class="fa-solid fa-filter me-1"></i> Filtrar</button>
            @if(request()->hasAny(['estado', 'prioridad']))
                <a href="{{ route('transfers.index') }}" class="btn-glass text-danger"><i class="fa-solid fa-xmark me-1"></i> Limpiar</a>
            @endif
        </div>
        <span class="text-muted small">Total: {{ $transfers->count() }} órdenes registradas</span>
    </form>
</div>

<!-- 4. TABLA DE HISTORIAL DE TRANSFERENCIAS -->
<div class="glass-card p-4">
    <div class="table-responsive">
        <table class="dark-table">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Prioridad</th>
                    <th>Refugio Origen</th>
                    <th>Refugio Destino</th>
                    <th>Insumo &amp; Cantidad</th>
                    <th>Vehículo / Escolta</th>
                    <th>Estado</th>
                    <th>Fecha Solicitud</th>
                    <th class="text-end">Operaciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transfers as $t)
                <tr>
                    <td>
                        <code style="color: #e6a86c; font-weight: 700; background: rgba(216, 150, 86, 0.12); border: 1px solid rgba(216, 150, 86, 0.25); padding: 3px 8px; border-radius: 6px;">{{ $t->codigo_transferencia }}</code>
                    </td>
                    <td>
                        <span class="bg-glass {{ $t->prioridad_badge['class'] }}">
                            {{ strtoupper($t->prioridad) }}
                        </span>
                    </td>
                    <td>
                        <span class="text-white">{{ $t->refugioOrigen->nombre }}</span>
                    </td>
                    <td>
                        <strong class="text-white">{{ $t->refugioDestino->nombre }}</strong>
                    </td>
                    <td>
                        <span class="text-info fw-bold">{{ number_format($t->cantidad, 1) }} {{ $t->supply->unidad_medida }}</span>
                        <small class="d-block text-muted">{{ $t->supply->nombre }}</small>
                    </td>
                    <td>
                        <span class="text-muted small">
                            <i class="fa-solid fa-truck me-1 text-info"></i> {{ $t->vehiculo_placa ?: 'Sin asignar' }}
                        </span>
                    </td>
                    <td>
                        <span class="bg-glass {{ $t->estado_badge['class'] }}">
                            @if($t->estado == 'en_transito')
                                <i class="fa-solid fa-spinner fa-spin me-1"></i>
                            @endif
                            {{ $t->estado_badge['text'] }}
                        </span>
                    </td>
                    <td style="font-size: 0.76rem; color: rgba(226, 232, 240, 0.7);">
                        {{ $t->fecha_solicitud->format('d/m/Y H:i') }}
                    </td>
                    <td class="text-end">
                        <div class="d-inline-flex gap-2 align-items-center">
                            @if($t->estado === 'solicitada' && auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isCoordinador()))
                            <form action="{{ route('transfers.approve', $t) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn-accent py-1 px-2" style="font-size: 0.74rem;">
                                    <i class="fa-solid fa-check me-1"></i> Aprobar
                                </button>
                            </form>
                            @endif

                            @if($t->estado === 'aprobada' && auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isAlmacenista()))
                            <form action="{{ route('transfers.dispatch', $t) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn-accent py-1 px-2" style="font-size: 0.74rem; background: linear-gradient(135deg, #d97706, #f59e0b);">
                                    <i class="fa-solid fa-truck-moving me-1"></i> Despachar
                                </button>
                            </form>
                            @endif

                            @if($t->estado === 'en_transito' && auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isAlmacenista() || auth()->user()->isCoordinador()))
                            <form action="{{ route('transfers.receive', $t) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn-accent py-1 px-2" style="font-size: 0.74rem; background: linear-gradient(135deg, #059669, #10b981);">
                                    <i class="fa-solid fa-box-open me-1"></i> Confirmar Recepción
                                </button>
                            </form>
                            @endif

                            <a href="{{ route('transfers.show', $t) }}" class="btn-glass py-1 px-2" style="font-size: 0.74rem;">
                                <i class="fa-solid fa-eye text-info"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-5 text-muted">
                        <i class="fa-solid fa-truck-ramp-box fs-1 mb-3 text-secondary d-block"></i>
                        No se encontraron transferencias registradas con los filtros seleccionados.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
