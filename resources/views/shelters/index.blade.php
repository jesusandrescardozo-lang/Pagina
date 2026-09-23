@extends('layouts.app')

@section('title', 'Refugios y Albergues')
@section('page-title', 'Red de Refugios y Albergues Humanitarios')
@section('page-subtitle', 'Monitoreo demográfico, capacidad de alojamiento y telemetría de abastecimiento')

@section('content')

@php
    $totalCentros = $shelters->count();
    $capacidadTotal = $shelters->sum('capacidad_maxima');
    $poblacionTotal = $shelters->sum('poblacion_total');
    $ocupacionPromedio = $capacidadTotal > 0 ? round(($poblacionTotal / $capacidadTotal) * 100, 1) : 0;
    $enAlerta = $shelters->where('ocupacion_porcentaje', '>', 90)->count();
@endphp

<!-- COCKPIT HEADER CON TELEMETRÍA DEMOGRÁFICA -->
<div class="cockpit-page-header">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge" style="background: rgba(56, 189, 248, 0.2); color: #38bdf8; font-size: 0.72rem; border: 1px solid rgba(56, 189, 248, 0.4); border-radius: 9999px; padding: 3px 10px;">
                <i class="fa-solid fa-satellite-dish me-1"></i> Red de Protección Civil
            </span>
            <span class="badge" style="background: rgba(16, 185, 129, 0.2); color: #34d399; font-size: 0.72rem; border: 1px solid rgba(16, 185, 129, 0.4); border-radius: 9999px; padding: 3px 10px;">
                {{ $totalCentros }} Centros Activos
            </span>
        </div>
        <h3 class="cockpit-header-title">Censo y Gestión de Albergues</h3>
        <p class="cockpit-header-subtitle">Supervisión en tiempo real de capacidad instalada, camas ocupadas y atención prioritaria</p>
    </div>

    <div class="d-flex align-items-center gap-3 flex-wrap">
        <div class="d-flex gap-2">
            <div class="p-2 px-3 rounded-3" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); text-align: center;">
                <span class="text-muted d-block" style="font-size: 0.68rem; text-transform: uppercase;">Población Total</span>
                <strong class="text-white fs-6">{{ number_format($poblacionTotal) }}</strong>
            </div>
            <div class="p-2 px-3 rounded-3" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); text-align: center;">
                <span class="text-muted d-block" style="font-size: 0.68rem; text-transform: uppercase;">Ocupación Global</span>
                <strong class="text-info fs-6">{{ $ocupacionPromedio }}%</strong>
            </div>
            @if($enAlerta > 0)
            <div class="p-2 px-3 rounded-3" style="background: rgba(244,63,94,0.1); border: 1px solid rgba(244,63,94,0.3); text-align: center;">
                <span class="text-danger d-block" style="font-size: 0.68rem; text-transform: uppercase;">En Saturación</span>
                <strong class="text-danger fs-6">{{ $enAlerta }}</strong>
            </div>
            @endif
        </div>

        @if(auth()->check() && auth()->user()->isCoordinador())
        <a href="{{ route('shelters.create') }}" class="btn-accent">
            <i class="fa-solid fa-plus"></i> Registrar Refugio
        </a>
        @endif
    </div>
</div>

<!-- FILTROS EN VIDRIO OSCURO -->
<div class="glass-card p-3 mb-4">
    <form method="GET" action="{{ route('shelters.index') }}" class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
        <div class="d-flex flex-wrap gap-2 align-items-center flex-grow-1">
            <div class="position-relative" style="min-width: 280px;">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control-glass" placeholder="Buscar refugio por nombre, código o municipio...">
            </div>
            <select name="estado" class="form-select-glass" style="max-width: 200px;">
                <option value="">Todos los Estados</option>
                <option value="activo" {{ request('estado') == 'activo' ? 'selected' : '' }}>Activo Operativo</option>
                <option value="alerta_capacidad" {{ request('estado') == 'alerta_capacidad' ? 'selected' : '' }}>Alerta de Capacidad</option>
                <option value="evacuacion" {{ request('estado') == 'evacuacion' ? 'selected' : '' }}>En Evacuación</option>
                <option value="cerrado" {{ request('estado') == 'cerrado' ? 'selected' : '' }}>Cerrado</option>
            </select>
            <button type="submit" class="btn-accent"><i class="fa-solid fa-filter me-1"></i> Filtrar</button>
            @if(request()->hasAny(['q', 'estado']))
                <a href="{{ route('shelters.index') }}" class="btn-glass text-danger"><i class="fa-solid fa-xmark me-1"></i> Limpiar</a>
            @endif
        </div>
        <span class="text-muted small">Mostrando {{ $shelters->count() }} centros</span>
    </form>
</div>

<!-- CUADRÍCULA DE TARJETAS DE REFUGIOS -->
<div class="row g-4">
    @forelse($shelters as $shelter)
    <div class="col-md-6 col-lg-4">
        <div class="glass-card p-4 h-100 d-flex flex-column justify-content-between" style="border-top: 3px solid {{ $shelter->ocupacion_porcentaje > 90 ? '#f43f5e' : ($shelter->ocupacion_porcentaje > 70 ? '#f59e0b' : '#0ea5e9') }};">
            <div>
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="bg-glass bg-info2"><code>{{ $shelter->codigo }}</code></span>
                    <span class="bg-glass {{ $shelter->estado_badge['class'] }}">{{ $shelter->estado_badge['text'] }}</span>
                </div>
                <h5 class="fw-bold text-white mb-1" style="font-size: 1.15rem;">{{ $shelter->nombre }}</h5>
                <p class="text-muted mb-3" style="font-size: 0.78rem;">
                    <i class="fa-solid fa-location-dot me-1 text-info"></i> {{ $shelter->municipio }}, {{ $shelter->departamento }}
                </p>

                <!-- Métricas de Población & Capacidad -->
                <div class="p-3 rounded-3 mb-3" style="background: rgba(11, 15, 25, 0.7); border: 1px solid rgba(255, 255, 255, 0.08);">
                    <div class="d-flex justify-content-between align-items-baseline mb-1">
                        <span class="stat-label">Población / Capacidad</span>
                        <span class="fw-bold text-white fs-6">{{ $shelter->poblacion_total }} / {{ $shelter->capacidad_maxima }}</span>
                    </div>
                    <div class="progress-glass mb-2">
                        <div class="bar {{ $shelter->ocupacion_porcentaje > 90 ? 'bar-danger' : ($shelter->ocupacion_porcentaje > 70 ? 'bar-warning' : 'bar-success') }}" 
                             style="width: {{ min($shelter->ocupacion_porcentaje, 100) }}%"></div>
                    </div>
                    <div class="d-flex justify-content-between text-muted mb-2" style="font-size: 0.72rem;">
                        <span class="fw-bold {{ $shelter->ocupacion_porcentaje > 90 ? 'text-danger' : 'text-info' }}">{{ $shelter->ocupacion_porcentaje }}% saturación</span>
                        <span>{{ max(0, $shelter->capacidad_maxima - $shelter->poblacion_total) }} cupos libres</span>
                    </div>

                    <!-- Desglose Demográfico -->
                    <div class="row g-1 text-center pt-2 border-top border-secondary border-opacity-25" style="font-size: 0.72rem;">
                        <div class="col-4">
                            <span class="text-muted d-block">Adultos</span>
                            <strong class="text-white">{{ $shelter->poblacion_adultos }}</strong>
                        </div>
                        <div class="col-4">
                            <span class="text-muted d-block">Infantes</span>
                            <strong class="text-info">{{ $shelter->poblacion_ninos }}</strong>
                        </div>
                        <div class="col-4">
                            <span class="text-muted d-block">Atenc. Méd.</span>
                            <strong class="text-danger">{{ $shelter->poblacion_atencion_medica }}</strong>
                        </div>
                    </div>
                </div>

                <div style="font-size: 0.76rem;" class="text-muted mb-3">
                    <div><i class="fa-solid fa-user-shield me-1 text-info"></i> Responsable: <strong class="text-white">{{ $shelter->responsable_nombre }}</strong></div>
                    <div><i class="fa-solid fa-phone me-1 text-success"></i> Teléfono: {{ $shelter->responsable_telefono }}</div>
                </div>
            </div>

            <!-- Acciones -->
            <div class="d-flex gap-2 pt-3 border-top border-secondary border-opacity-25 mt-auto">
                <a href="{{ route('shelters.show', $shelter) }}" class="btn-accent flex-grow-1 text-center justify-content-center">
                    <i class="fa-solid fa-boxes-stacked me-1"></i> Ver Inventario
                </a>
                <a href="{{ route('dashboard', ['shelter_id' => $shelter->id]) }}" class="btn-glass" title="Operar en Dashboard">
                    <i class="fa-solid fa-arrows-to-eye text-info"></i>
                </a>
                @if(auth()->check() && auth()->user()->isCoordinador())
                <a href="{{ route('shelters.edit', $shelter) }}" class="btn-glass" title="Editar Refugio">
                    <i class="fa-solid fa-pen-to-square"></i>
                </a>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="glass-card p-5 text-center text-muted">
            <i class="fa-solid fa-tents fs-1 mb-3 text-info"></i>
            <h5>No se encontraron refugios con los filtros seleccionados</h5>
            <p>Intenta restablecer los filtros para ver todos los centros.</p>
            <a href="{{ route('shelters.index') }}" class="btn-glass"><i class="fa-solid fa-arrows-rotate me-1"></i> Ver Todos los Refugios</a>
        </div>
    </div>
    @endforelse
</div>

@endsection
