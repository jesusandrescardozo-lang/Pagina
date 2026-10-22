@extends('layouts.app')

@section('title', $shelter->nombre . ' - Detalle Operativo')
@section('page-title', $shelter->nombre)
@section('page-subtitle', 'Control detallado de inventario, lotes FEFO y días de cobertura para ' . $shelter->codigo)

@section('content')
<!-- COCKPIT HEADER DEL REFUGIO -->
<div class="cockpit-page-header">
    <div>
        <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
            <span class="badge" style="background: rgba(230,168,108,0.15); border: 1px solid rgba(230,168,108,0.35); color: var(--amber); font-family:'JetBrains Mono',monospace; font-size:0.75rem;">
                <i class="fa-solid fa-hashtag me-1"></i>{{ $shelter->codigo }}
            </span>
            <span class="badge {{ $shelter->estado_badge['class'] }}" style="font-size:0.75rem;">
                <i class="fa-solid fa-circle-dot me-1"></i>{{ $shelter->estado_badge['text'] }}
            </span>
            <span class="badge" style="background: var(--surface-quiet); border: 1px solid var(--quiet-border); color: var(--text-muted); font-size:0.72rem;">
                <i class="fa-solid fa-location-dot me-1 text-info"></i>{{ $shelter->municipio }}, {{ $shelter->departamento }}
            </span>
        </div>
        <h2 class="cockpit-header-title text-white mb-1">{{ $shelter->nombre }}</h2>
        <p class="cockpit-header-subtitle">
            <i class="fa-solid fa-map-pin me-1 text-info opacity-75"></i>{{ $shelter->direccion }}
            @if($shelter->responsable_nombre)
                <span class="ms-2 opacity-75">&bull; Resp: <strong>{{ $shelter->responsable_nombre }}</strong> ({{ $shelter->responsable_telefono }})</span>
            @endif
        </p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('movements.create', ['shelter_id' => $shelter->id, 'tipo' => 'entrada_donacion']) }}" class="btn-accent" style="font-size:0.8rem;">
            <i class="fa-solid fa-hand-holding-heart"></i> Ingresar Donación
        </a>
        <a href="{{ route('movements.create', ['shelter_id' => $shelter->id, 'tipo' => 'salida_consumo']) }}" class="btn-glass text-warning" style="font-size:0.8rem;">
            <i class="fa-solid fa-utensils"></i> Registrar Consumo
        </a>
        <a href="{{ route('transfers.create', ['destino_id' => $shelter->id]) }}" class="btn-glass text-info" style="font-size:0.8rem;">
            <i class="fa-solid fa-truck-ramp-box"></i> Rebalancear
        </a>
        <a href="{{ route('shelters.edit', $shelter) }}" class="btn-glass" style="font-size:0.8rem;">
            <i class="fa-solid fa-pen-to-square"></i> Editar
        </a>
        <a href="{{ route('shelters.index') }}" class="btn-glass" style="font-size:0.8rem;">
            <i class="fa-solid fa-arrow-left"></i> Volver
        </a>
    </div>
</div>

<!-- TELEMETRÍA DE CAPACIDAD Y DEMOGRAFÍA -->
<div class="row g-3 mb-4">
    <!-- Ocupación General -->
    <div class="col-lg-4 col-md-6">
        <div class="glass-card p-3 h-100 position-relative overflow-hidden">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="stat-label mb-0"><i class="fa-solid fa-chart-pie me-1 text-info"></i> Capacidad Operativa</span>
                <span class="badge {{ $shelter->ocupacion_porcentaje > 90 ? 'bg-danger text-white' : ($shelter->ocupacion_porcentaje > 70 ? 'bg-warning text-dark' : 'bg-success text-white') }}" style="font-size:0.7rem;">
                    {{ $shelter->ocupacion_porcentaje }}% Ocupado
                </span>
            </div>
            <div class="d-flex align-items-baseline gap-2 mb-2">
                <span class="fs-2 fw-bold text-white font-monospace">{{ $shelter->poblacion_total }}</span>
                <span class="text-muted" style="font-size:0.85rem;">/ {{ $shelter->capacidad_maxima }} plazas máx</span>
            </div>
            <div class="progress-glass" style="height: 10px;">
                <div class="bar {{ $shelter->ocupacion_porcentaje > 90 ? 'bar-danger' : ($shelter->ocupacion_porcentaje > 70 ? 'bar-warning' : 'bar-success') }}" 
                     style="width: {{ min($shelter->ocupacion_porcentaje, 100) }}%"></div>
            </div>
            <div class="d-flex justify-content-between text-muted mt-2" style="font-size:0.7rem;">
                <span>Disponibilidad libre: <strong>{{ max(0, $shelter->capacidad_maxima - $shelter->poblacion_total) }}</strong></span>
                <span>{{ $shelter->estado_badge['text'] }}</span>
            </div>
        </div>
    </div>

    <!-- Demografía Adultos y Niños -->
    <div class="col-lg-4 col-md-6">
        <div class="glass-card p-3 h-100">
            <span class="stat-label mb-2"><i class="fa-solid fa-users me-1 text-info"></i> Distribución por Grupo Etario</span>
            <div class="row g-2 text-center mt-1">
                <div class="col-6">
                    <div class="p-2 rounded-3" style="background: rgba(230, 168, 108, 0.08); border: 1px solid rgba(230, 168, 108, 0.25);">
                        <span class="text-muted d-block" style="font-size:0.7rem; text-transform:uppercase;">Adultos</span>
                        <span class="fs-4 fw-bold text-white">{{ $shelter->poblacion_adultos }}</span>
                        <span class="d-block text-muted" style="font-size:0.65rem;">
                            {{ $shelter->poblacion_total > 0 ? round(($shelter->poblacion_adultos / $shelter->poblacion_total) * 100) : 0 }}% del total
                        </span>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-2 rounded-3" style="background: rgba(230, 168, 108, 0.12); border: 1px solid rgba(230, 168, 108, 0.3);">
                        <span class="text-muted d-block" style="font-size:0.7rem; text-transform:uppercase;">Niños / Lactantes</span>
                        <span class="fs-4 fw-bold" style="color:var(--accent-light);">{{ $shelter->poblacion_ninos }}</span>
                        <span class="d-block text-muted" style="font-size:0.65rem;">Prioridad Nutricional</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Demografía Vulnerable / Médica -->
    <div class="col-lg-4 col-md-12">
        <div class="glass-card p-3 h-100">
            <span class="stat-label mb-2"><i class="fa-solid fa-heart-pulse me-1 text-danger"></i> Población Vulnerable y Salud</span>
            <div class="row g-2 text-center mt-1">
                <div class="col-6">
                    <div class="p-2 rounded-3" style="background: rgba(245, 158, 11, 0.08); border: 1px solid rgba(245, 158, 11, 0.25);">
                        <span class="text-muted d-block" style="font-size:0.7rem; text-transform:uppercase;">Adultos Mayores</span>
                        <span class="fs-4 fw-bold text-warning">{{ $shelter->poblacion_adultos_mayores }}</span>
                        <span class="d-block text-muted" style="font-size:0.65rem;">Dieta geriátrica</span>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-2 rounded-3" style="background: rgba(244, 63, 94, 0.1); border: 1px solid rgba(244, 63, 94, 0.3);">
                        <span class="text-muted d-block" style="font-size:0.7rem; text-transform:uppercase;">Atención Médica</span>
                        <span class="fs-4 fw-bold text-danger">{{ $shelter->poblacion_atencion_medica }}</span>
                        <span class="d-block text-muted" style="font-size:0.65rem;">Pacientes activos</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- INVENTARIO DE ALIMENTOS -->
<div class="glass-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <div class="section-heading mb-1 text-success" style="font-size:0.9rem;">
                <i class="fa-solid fa-bowl-food me-1"></i> Inventario de Alimentos y Nutrición
            </div>
            <small class="text-muted">Cálculo de demanda per cápita y días de cobertura alimentaria humanitaria.</small>
        </div>
        <span class="badge" style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.35); color: var(--ok-text); font-size:0.75rem;">
            <i class="fa-solid fa-check-circle me-1"></i>{{ $inventariosAlimentos->count() }} insumos registrados
        </span>
    </div>

    <div class="table-responsive">
        <table class="dark-table">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Insumo Nutricional</th>
                    <th>Clase ABC</th>
                    <th>Stock Actual</th>
                    <th>Consumo Diario Est.</th>
                    <th>Días Autonomía (I.O.)</th>
                    <th>Semáforo</th>
                    <th>Lotes y Caducidades (FEFO)</th>
                    <th class="text-end">Operación</th>
                </tr>
            </thead>
            <tbody>
                @forelse($inventariosAlimentos as $inv)
                <tr>
                    <td><code>{{ $inv->supply->codigo }}</code></td>
                    <td>
                        <strong class="text-white">{{ $inv->supply->nombre }}</strong>
                        <small class="d-block text-muted">{{ $inv->supply->subcategoria }}</small>
                    </td>
                    <td>
                        <span class="bg-glass {{ $inv->supply->abc_badge['class'] }}">{{ $inv->supply->clasificacion_abc }}</span>
                    </td>
                    <td>
                        <span class="fw-bold text-white fs-6">{{ number_format($inv->stock_actual, 1) }}</span> 
                        <small class="text-muted">{{ $inv->supply->unidad_medida }}</small>
                    </td>
                    <td>
                        {{ $inv->consumo_diario_estimado }} <small class="text-muted">{{ $inv->supply->unidad_medida }}/día</small>
                    </td>
                    <td>
                        <strong style="color: {{ $inv->nivel_alerta['color'] }}; font-size: 1.05rem;">
                            {{ $inv->dias_autonomia > 100 ? '> 90' : $inv->dias_autonomia }} días
                        </strong>
                    </td>
                    <td>
                        <span class="bg-glass {{ $inv->nivel_alerta['badge'] }}" style="font-size:0.68rem;">
                            {{ $inv->nivel_alerta['estado'] }}
                        </span>
                    </td>
                    <td style="max-width: 220px;">
                        @forelse($inv->activeBatchesFefo as $batch)
                            <div class="d-flex justify-content-between align-items-center mb-1" style="font-size: 0.7rem;">
                                <span class="text-muted font-monospace">{{ $batch->numero_lote }}:</span>
                                <span class="text-white fw-bold">{{ $batch->cantidad_disponible }}</span>
                                <span class="bg-glass {{ $batch->estado_badge['class'] }}" style="padding: 1px 6px; font-size:0.63rem;">
                                    {{ $batch->fecha_vencimiento ? $batch->fecha_vencimiento->format('d/m/y') : 'S/V' }}
                                </span>
                            </div>
                        @empty
                            <span class="text-muted" style="font-size: 0.72rem;">Sin lotes vigentes</span>
                        @endforelse
                    </td>
                    <td class="text-end">
                        <a href="{{ route('movements.create', ['shelter_id' => $shelter->id, 'supply_id' => $inv->supply->id, 'tipo' => 'salida_consumo']) }}" 
                           class="btn-glass text-warning" style="padding: 5px 9px; font-size: 0.72rem;" title="Registrar Consumo">
                            <i class="fa-solid fa-utensils"></i>
                        </a>
                        <a href="{{ route('movements.create', ['shelter_id' => $shelter->id, 'supply_id' => $inv->supply->id, 'tipo' => 'entrada_donacion']) }}" 
                           class="btn-glass text-success" style="padding: 5px 9px; font-size: 0.72rem;" title="Ingresar Donación">
                            <i class="fa-solid fa-plus"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center text-muted py-4">No hay alimentos registrados para este refugio.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- INVENTARIO DE INSUMOS MÉDICOS -->
<div class="glass-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <div class="section-heading mb-1 text-danger" style="font-size:0.9rem;">
                <i class="fa-solid fa-kit-medical me-1"></i> Inventario de Insumos Médicos y Fármacos
            </div>
            <small class="text-muted">Garantía de stock para pacientes crónicos, heridos y emergencias sanitarias en el refugio.</small>
        </div>
        <span class="badge" style="background: rgba(244, 63, 94, 0.15); border: 1px solid rgba(244, 63, 94, 0.35); color: var(--danger-text); font-size:0.75rem;">
            <i class="fa-solid fa-shield-heart me-1"></i>{{ $inventariosMedicos->count() }} fármacos / kits
        </span>
    </div>

    <div class="table-responsive">
        <table class="dark-table">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Insumo Médico</th>
                    <th>Clase ABC</th>
                    <th>Stock Actual</th>
                    <th>Consumo Diario Est.</th>
                    <th>Días Autonomía (I.O.)</th>
                    <th>Semáforo</th>
                    <th>Lotes y Caducidades (FEFO)</th>
                    <th class="text-end">Operación</th>
                </tr>
            </thead>
            <tbody>
                @forelse($inventariosMedicos as $inv)
                <tr>
                    <td><code>{{ $inv->supply->codigo }}</code></td>
                    <td>
                        <strong class="text-white">{{ $inv->supply->nombre }}</strong>
                        <small class="d-block text-muted">{{ $inv->supply->subcategoria }}</small>
                    </td>
                    <td>
                        <span class="bg-glass {{ $inv->supply->abc_badge['class'] }}">{{ $inv->supply->clasificacion_abc }}</span>
                    </td>
                    <td>
                        <span class="fw-bold text-white fs-6">{{ number_format($inv->stock_actual, 1) }}</span> 
                        <small class="text-muted">{{ $inv->supply->unidad_medida }}</small>
                    </td>
                    <td>
                        {{ $inv->consumo_diario_estimado }} <small class="text-muted">{{ $inv->supply->unidad_medida }}/día</small>
                    </td>
                    <td>
                        <strong style="color: {{ $inv->nivel_alerta['color'] }}; font-size: 1.05rem;">
                            {{ $inv->dias_autonomia > 100 ? '> 90' : $inv->dias_autonomia }} días
                        </strong>
                    </td>
                    <td>
                        <span class="bg-glass {{ $inv->nivel_alerta['badge'] }}" style="font-size:0.68rem;">
                            {{ $inv->nivel_alerta['estado'] }}
                        </span>
                    </td>
                    <td style="max-width: 220px;">
                        @forelse($inv->activeBatchesFefo as $batch)
                            <div class="d-flex justify-content-between align-items-center mb-1" style="font-size: 0.7rem;">
                                <span class="text-muted font-monospace">{{ $batch->numero_lote }}:</span>
                                <span class="text-white fw-bold">{{ $batch->cantidad_disponible }}</span>
                                <span class="bg-glass {{ $batch->estado_badge['class'] }}" style="padding: 1px 6px; font-size:0.63rem;">
                                    {{ $batch->fecha_vencimiento ? $batch->fecha_vencimiento->format('d/m/y') : 'S/V' }}
                                </span>
                            </div>
                        @empty
                            <span class="text-muted" style="font-size: 0.72rem;">Sin lotes vigentes</span>
                        @endforelse
                    </td>
                    <td class="text-end">
                        <a href="{{ route('movements.create', ['shelter_id' => $shelter->id, 'supply_id' => $inv->supply->id, 'tipo' => 'salida_consumo']) }}" 
                           class="btn-glass text-warning" style="padding: 5px 9px; font-size: 0.72rem;" title="Dispensar Fármaco">
                            <i class="fa-solid fa-pills"></i>
                        </a>
                        <a href="{{ route('movements.create', ['shelter_id' => $shelter->id, 'supply_id' => $inv->supply->id, 'tipo' => 'entrada_donacion']) }}" 
                           class="btn-glass text-success" style="padding: 5px 9px; font-size: 0.72rem;" title="Ingresar Medicamento">
                            <i class="fa-solid fa-plus"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center text-muted py-4">No hay insumos médicos registrados en este centro.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- HISTORIAL RECIENTE KARDEX DEL REFUGIO -->
<div class="glass-card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div class="section-heading mb-0 text-info" style="font-size:0.9rem;">
            <i class="fa-solid fa-clock-rotate-left me-1"></i> Bitácora Kardex en este Refugio
        </div>
        <a href="{{ route('movements.index', ['shelter_id' => $shelter->id]) }}" class="btn-glass" style="font-size: 0.75rem;">
            <i class="fa-solid fa-list me-1"></i> Ver Historial Completo
        </a>
    </div>
    <div class="table-responsive">
        <table class="dark-table">
            <thead>
                <tr>
                    <th>Fecha / Hora</th>
                    <th>Tipo</th>
                    <th>Insumo</th>
                    <th>Cantidad</th>
                    <th>Stock Ant.</th>
                    <th>Stock Nuevo</th>
                    <th>Referencia</th>
                </tr>
            </thead>
            <tbody>
                @forelse($movimientosRecientes as $m)
                <tr>
                    <td style="font-size:0.75rem; font-family:'JetBrains Mono',monospace;">{{ $m->fecha_movimiento->format('d/m/Y H:i') }}</td>
                    <td>
                        <span class="bg-glass {{ $m->tipo_badge['class'] }}" style="font-size:0.68rem;">
                            <i class="fa-solid {{ $m->tipo_badge['icon'] }}"></i> {{ $m->tipo_badge['text'] }}
                        </span>
                    </td>
                    <td><strong class="text-white">{{ $m->supply->nombre }}</strong></td>
                    <td class="{{ $m->es_entrada ? 'text-success' : 'text-danger' }} fw-bold">
                        {{ $m->es_entrada ? '+' : '-' }}{{ $m->cantidad }} {{ $m->supply->unidad_medida }}
                    </td>
                    <td class="text-muted">{{ $m->stock_anterior }}</td>
                    <td class="fw-bold text-white">{{ $m->stock_nuevo }}</td>
                    <td class="text-muted" style="font-size:0.75rem;">{{ $m->documento_referencia ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">No hay movimientos registrados para este refugio aún.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
