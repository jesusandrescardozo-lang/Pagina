@extends('layouts.app')

@section('title', 'Transferencia ' . $transfer->codigo_transferencia)
@section('page-title', 'Transferencia ' . $transfer->codigo_transferencia)
@section('page-subtitle', 'Detalle del envío, trazabilidad de estado y movimientos de Kardex asociados')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <a href="{{ route('transfers.index') }}" class="btn-glass" style="font-size:0.8rem;">
        <i class="fa-solid fa-arrow-left me-1"></i> Volver a Transferencias
    </a>
    <div class="d-flex gap-2 align-items-center">
        <span class="bg-glass {{ $transfer->prioridad_badge['class'] }}" style="font-size: 0.7rem; border-radius: 9999px; padding: 3px 10px;">
            <i class="fa-solid fa-flag me-1"></i> {{ $transfer->prioridad_badge['text'] }}
        </span>
        <span class="bg-glass {{ $transfer->estado_badge['class'] }}" style="font-size: 0.7rem; border-radius: 9999px; padding: 3px 10px;">
            <i class="fa-solid fa-circle-info me-1"></i> {{ $transfer->estado_badge['text'] }}
        </span>
    </div>
</div>

<div class="row g-3">
    <!-- Datos principales -->
    <div class="col-lg-7">
        <div class="glass-panel p-4">
            <h6 class="text-uppercase mb-3" style="font-size: 0.75rem; letter-spacing: 1px; color: rgba(241, 240, 255, 0.6);">
                <i class="fa-solid fa-truck-ramp-box me-1"></i> Datos del Envío
            </h6>

            <div class="row g-3">
                <div class="col-md-6">
                    <small class="d-block text-uppercase" style="font-size: 0.65rem; color: rgba(241,240,255,0.5);">Refugio Origen</small>
                    <strong class="text-info">
                        {{ $transfer->refugioOrigen ? "[{$transfer->refugioOrigen->codigo}] {$transfer->refugioOrigen->nombre}" : '—' }}
                    </strong>
                </div>
                <div class="col-md-6">
                    <small class="d-block text-uppercase" style="font-size: 0.65rem; color: rgba(241,240,255,0.5);">Refugio Destino</small>
                    <strong class="text-success">
                        {{ $transfer->refugioDestino ? "[{$transfer->refugioDestino->codigo}] {$transfer->refugioDestino->nombre}" : '—' }}
                    </strong>
                </div>
                <div class="col-md-6">
                    <small class="d-block text-uppercase" style="font-size: 0.65rem; color: rgba(241,240,255,0.5);">Insumo</small>
                    <strong>{{ $transfer->supply ? "{$transfer->supply->codigo} — {$transfer->supply->nombre}" : '—' }}</strong>
                </div>
                <div class="col-md-6">
                    <small class="d-block text-uppercase" style="font-size: 0.65rem; color: rgba(241,240,255,0.5);">Cantidad</small>
                    <strong class="text-warning">{{ number_format($transfer->cantidad, 1) }} {{ $transfer->supply->unidad_medida ?? '' }}</strong>
                </div>
                <div class="col-md-6">
                    <small class="d-block text-uppercase" style="font-size: 0.65rem; color: rgba(241,240,255,0.5);">Fecha de Solicitud</small>
                    <strong>{{ $transfer->fecha_solicitud ? $transfer->fecha_solicitud->format('d/m/Y H:i') : '—' }}</strong>
                </div>
                <div class="col-md-6">
                    <small class="d-block text-uppercase" style="font-size: 0.65rem; color: rgba(241,240,255,0.5);">Vehículo / Convoy</small>
                    <strong>{{ $transfer->vehiculo_transporte ?? 'Sin asignar' }}</strong>
                </div>
                <div class="col-md-6">
                    <small class="d-block text-uppercase" style="font-size: 0.65rem; color: rgba(241,240,255,0.5);">Salida</small>
                    <strong>{{ $transfer->fecha_salida ? $transfer->fecha_salida->format('d/m/Y H:i') : '—' }}</strong>
                </div>
                <div class="col-md-6">
                    <small class="d-block text-uppercase" style="font-size: 0.65rem; color: rgba(241,240,255,0.5);">Llegada</small>
                    <strong>{{ $transfer->fecha_llegada ? $transfer->fecha_llegada->format('d/m/Y H:i') : '—' }}</strong>
                </div>
                <div class="col-md-6">
                    <small class="d-block text-uppercase" style="font-size: 0.65rem; color: rgba(241,240,255,0.5);">Solicitado por</small>
                    <strong>{{ $transfer->solicitadoPor->name ?? '—' }}</strong>
                </div>
                <div class="col-md-6">
                    <small class="d-block text-uppercase" style="font-size: 0.65rem; color: rgba(241,240,255,0.5);">Aprobado / Despachado por</small>
                    <strong>{{ $transfer->aprobadoPor->name ?? '—' }}</strong>
                </div>
                @if($transfer->observaciones)
                <div class="col-12">
                    <small class="d-block text-uppercase" style="font-size: 0.65rem; color: rgba(241,240,255,0.5);">Observaciones</small>
                    <span>{{ $transfer->observaciones }}</span>
                </div>
                @endif
            </div>

            <!-- Lotes FEFO despachados -->
            @if(!empty($transfer->lotes_despacho))
            <hr style="border-color: rgba(255,255,255,0.08);">
            <h6 class="text-uppercase mb-2" style="font-size: 0.72rem; letter-spacing: 1px; color: rgba(241, 240, 255, 0.6);">
                <i class="fa-solid fa-boxes-stacked me-1"></i> Lotes Despachados (FEFO)
            </h6>
            <ul class="mb-0" style="font-size: 0.78rem; color: rgba(226,232,240,0.8);">
                @foreach($transfer->lotes_despacho as $lote)
                <li>
                    Lote <strong>{{ $lote['numero_lote'] }}</strong> —
                    {{ number_format($lote['cantidad'], 1) }} {{ $transfer->supply->unidad_medida ?? '' }}
                    @if(!empty($lote['fecha_vencimiento']))
                        <span class="text-warning">(Vence: {{ \Illuminate\Support\Carbon::parse($lote['fecha_vencimiento'])->format('d/m/Y') }})</span>
                    @endif
                </li>
                @endforeach
            </ul>
            @endif
        </div>
    </div>

    <!-- Historial + acciones -->
    <div class="col-lg-5">
        <div class="glass-panel p-4 mb-3">
            <h6 class="text-uppercase mb-3" style="font-size: 0.75rem; letter-spacing: 1px; color: rgba(241, 240, 255, 0.6);">
                <i class="fa-solid fa-diagram-project me-1"></i> Flujo de Estados
            </h6>
            <ul class="list-unstyled mb-0" style="font-size: 0.8rem;">
                @php
                    $flujo = ['solicitada' => 'Solicitada', 'aprobada' => 'Aprobada', 'en_transito' => 'En Tránsito', 'completada' => 'Completada'];
                    $orden = array_keys($flujo);
                    $actualIdx = array_search($transfer->estado, $orden, true);
                    if ($transfer->estado === 'cancelada') { $actualIdx = -1; }
                @endphp
                @foreach($flujo as $idx => $etapa)
                    @if($transfer->estado === 'cancelada')
                        <li class="py-1" style="color: rgba(241,240,255,0.35);"><i class="fa-regular fa-circle me-2"></i> {{ $etapa }}</li>
                    @elseif($actualIdx !== false && $idx <= $actualIdx)
                        <li class="py-1 text-success"><i class="fa-solid fa-circle-check me-2"></i> {{ $etapa }}</li>
                    @else
                        <li class="py-1" style="color: rgba(241,240,255,0.45);"><i class="fa-regular fa-circle me-2"></i> {{ $etapa }}</li>
                    @endif
                @endforeach
                @if($transfer->estado === 'cancelada')
                    <li class="py-1 text-danger"><i class="fa-solid fa-circle-xmark me-2"></i> Cancelada</li>
                @endif
            </ul>

            <!-- Acciones (mismas reglas de la vista de listado) -->
            @auth
            <div class="d-flex gap-2 mt-3 flex-wrap">
                @if($transfer->estado === 'solicitada' && (auth()->user()->isAdmin() || auth()->user()->isCoordinador()))
                    <form action="{{ route('transfers.approve', $transfer) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn-accent py-1 px-2" style="font-size: 0.74rem;">
                            <i class="fa-solid fa-check me-1"></i> Aprobar
                        </button>
                    </form>
                @endif

                @if($transfer->estado === 'aprobada' && (auth()->user()->isAdmin() || auth()->user()->isAlmacenista()))
                    <form action="{{ route('transfers.dispatch', $transfer) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn-accent py-1 px-2" style="font-size: 0.74rem; background: linear-gradient(135deg, #d97706, #f59e0b);">
                            <i class="fa-solid fa-truck-moving me-1"></i> Despachar
                        </button>
                    </form>
                @endif

                @if($transfer->estado === 'en_transito' && (auth()->user()->isAdmin() || auth()->user()->isAlmacenista() || auth()->user()->isCoordinador()))
                    <form action="{{ route('transfers.receive', $transfer) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn-accent py-1 px-2" style="font-size: 0.74rem; background: linear-gradient(135deg, #059669, #10b981);">
                            <i class="fa-solid fa-box-open me-1"></i> Confirmar Recepción
                        </button>
                    </form>
                @endif
            </div>
            @endauth
        </div>

        <!-- Responsables -->
        <div class="glass-panel p-4">
            <h6 class="text-uppercase mb-3" style="font-size: 0.75rem; letter-spacing: 1px; color: rgba(241, 240, 255, 0.6);">
                <i class="fa-solid fa-file-lines me-1"></i> Trazabilidad (Kardex)
            </h6>
            @forelse($movimientos as $m)
                <div class="mb-2" style="font-size: 0.78rem;">
                    <span class="{{ str_starts_with($m->tipo, 'entrada') ? 'text-success' : 'text-danger' }}">
                        <i class="fa-solid {{ str_starts_with($m->tipo, 'entrada') ? 'fa-arrow-down' : 'fa-arrow-up' }} me-1"></i>
                        {{ ucwords(str_replace('_', ' ', $m->tipo)) }}
                    </span>
                    — {{ $m->shelter->nombre ?? '—' }}<br>
                    <small style="color: rgba(226,232,240,0.55);">
                        {{ number_format($m->cantidad, 1) }} {{ $transfer->supply->unidad_medida ?? '' }}
                        | Stock: {{ number_format($m->stock_anterior, 1) }} → {{ number_format($m->stock_nuevo, 1) }}
                        | {{ $m->fecha_movimiento->format('d/m/Y H:i') }}
                        | {{ $m->user->name ?? 'Sistema' }}
                    </small>
                </div>
            @empty
                <small style="color: rgba(226,232,240,0.5);">Sin movimientos registrados todavía (se generan al despachar y recepcionar).</small>
            @endforelse
        </div>
    </div>
</div>

@endsection
