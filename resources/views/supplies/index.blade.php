@extends('layouts.app')

@section('title', 'Catálogo de Insumos y Alimentos')
@section('page-title', 'Catálogo Maestro de Insumos y Alimentos')
@section('page-subtitle', 'Gestión de existencias, clasificación ABC y parámetros cuantitativos de Investigación de Operaciones')

@section('content')

<!-- 1. COCKPIT HEADER CON TELEMETRÍA DE INVENTARIO -->
<div class="cockpit-page-header">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge" style="background: rgba(216, 150, 86, 0.15); color: var(--amber); font-size: 0.72rem; border: 1px solid rgba(216, 150, 86, 0.35); border-radius: 9999px; padding: 3px 10px;">
                <i class="fa-solid fa-boxes-stacked me-1"></i> Control Central de Suministros
            </span>
            <span class="badge" style="background: rgba(16, 185, 129, 0.2); color: var(--ok-text); font-size: 0.72rem; border: 1px solid rgba(16, 185, 129, 0.4); border-radius: 9999px; padding: 3px 10px;">
                {{ $supplies->count() }} Insumos Catalogados
            </span>
        </div>
        <h3 class="cockpit-header-title">Catálogo de Alimentos &amp; Medicamentos</h3>
        <p class="cockpit-header-subtitle">Monitoreo de stock global, clasificación vital ABC y tiempos de reposición</p>
    </div>

    <div class="d-flex align-items-center gap-2 flex-wrap">
        @if(auth()->check() && auth()->user()->canManageSupplies())
        <a href="{{ route('supplies.create') }}" class="btn-accent">
            <i class="fa-solid fa-plus"></i> Registrar Nuevo Insumo
        </a>
        @endif
    </div>
</div>

<!-- 2. RESUMEN DE CATEGORÍAS Y ABC (Tarjetas Neón) -->
<div class="row g-3 mb-4">
    <div class="col-xl col-md-4 col-6">
        <div class="stat-card c-green">
            <div class="stat-icon"><i class="fa-solid fa-bowl-food"></i></div>
            <div class="stat-label">Alimentos</div>
            <div class="stat-value">{{ $resumenCategorias['alimentos'] }}</div>
        </div>
    </div>
    <div class="col-xl col-md-4 col-6">
        <div class="stat-card c-blue">
            <div class="stat-icon"><i class="fa-solid fa-kit-medical"></i></div>
            <div class="stat-label">Médicos / Farmacia</div>
            <div class="stat-value">{{ $resumenCategorias['medicos'] }}</div>
        </div>
    </div>
    <div class="col-xl col-md-4 col-6">
        <div class="stat-card c-red">
            <div class="stat-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
            <div class="stat-label">Clase A (Críticos)</div>
            <div class="stat-value">{{ $resumenCategorias['clase_a'] }}</div>
        </div>
    </div>
    <div class="col-xl col-md-4 col-6">
        <div class="stat-card c-amber">
            <div class="stat-icon"><i class="fa-solid fa-scale-balanced"></i></div>
            <div class="stat-label">Clase B (Moderados)</div>
            <div class="stat-value">{{ $resumenCategorias['clase_b'] }}</div>
        </div>
    </div>
    <div class="col-xl col-md-4 col-12">
        <div class="stat-card c-purple">
            <div class="stat-icon"><i class="fa-solid fa-box-archive"></i></div>
            <div class="stat-label">Clase C (Bajo Costo)</div>
            <div class="stat-value">{{ $resumenCategorias['clase_c'] }}</div>
        </div>
    </div>
</div>

<!-- 3. FILTROS EN VIDRIO OSCURO -->
<div class="glass-card p-3 mb-4">
    <form method="GET" action="{{ route('supplies.index') }}" class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
        <div class="d-flex flex-wrap gap-2 align-items-center flex-grow-1">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control-glass" style="max-width: 300px;" placeholder="Buscar insumo por nombre o código...">
            <select name="categoria" class="form-select-glass" style="max-width: 180px;">
                <option value="">Todas las Categorías</option>
                <option value="alimento" {{ request('categoria') == 'alimento' ? 'selected' : '' }}>Alimento</option>
                <option value="medico" {{ request('categoria') == 'medico' ? 'selected' : '' }}>Médico</option>
            </select>
            <select name="clasificacion_abc" class="form-select-glass" style="max-width: 180px;">
                <option value="">Toda Clase ABC</option>
                <option value="A" {{ request('clasificacion_abc') == 'A' ? 'selected' : '' }}>Clase A (Crítico)</option>
                <option value="B" {{ request('clasificacion_abc') == 'B' ? 'selected' : '' }}>Clase B (Moderado)</option>
                <option value="C" {{ request('clasificacion_abc') == 'C' ? 'selected' : '' }}>Clase C (Bajo Costo)</option>
            </select>
            <button type="submit" class="btn-accent"><i class="fa-solid fa-filter me-1"></i> Filtrar</button>
            @if(request()->hasAny(['q', 'categoria', 'clasificacion_abc']))
                <a href="{{ route('supplies.index') }}" class="btn-glass text-danger"><i class="fa-solid fa-xmark me-1"></i> Limpiar</a>
            @endif
        </div>
        <span class="text-muted small">Catálogo maestro sincronizado</span>
    </form>
</div>

<!-- 4. TABLA DE INSUMOS -->
<div class="glass-card p-4">
    <div class="table-responsive">
        <table class="dark-table">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Insumo</th>
                    <th>Categoría</th>
                    <th>Clasif. ABC</th>
                    <th>Stock Total en Red</th>
                    <th>Costo Unitario</th>
                    <th>Lead Time (Días)</th>
                    <th>Consumo Per Cápita</th>
                    <th class="text-end">Operaciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($supplies as $supply)
                <tr>
                    <td>
                        <code style="color: var(--amber); font-weight: 700; background: rgba(216, 150, 86, 0.12); border: 1px solid rgba(216, 150, 86, 0.25); padding: 3px 8px; border-radius: 6px;">{{ $supply->codigo }}</code>
                    </td>
                    <td>
                        <strong class="text-white d-block">{{ $supply->nombre }}</strong>
                        <small class="text-muted">{{ $supply->subcategoria }} &bull; Presentación: {{ $supply->presentacion }}</small>
                    </td>
                    <td>
                        <span class="badge" style="background: {{ $supply->categoria == 'alimento' ? 'rgba(16, 185, 129, 0.15)' : 'rgba(216, 150, 86, 0.15)' }}; color: {{ $supply->categoria == 'alimento' ? '#34d399' : '#e6a86c' }}; border: 1px solid {{ $supply->categoria == 'alimento' ? 'rgba(16, 185, 129, 0.3)' : 'rgba(216, 150, 86, 0.3)' }}; font-size: 0.72rem;">
                            <i class="fa-solid {{ $supply->categoria == 'alimento' ? 'fa-bowl-food' : 'fa-kit-medical' }} me-1"></i>
                            {{ ucfirst($supply->categoria) }}
                        </span>
                    </td>
                    <td>
                        <span class="bg-glass {{ $supply->clasificacion_abc == 'A' ? 'bg-err' : ($supply->clasificacion_abc == 'B' ? 'bg-warn' : 'bg-info2') }}" style="font-weight: 700;">
                            Clase {{ $supply->clasificacion_abc }}
                        </span>
                    </td>
                    <td>
                        <span class="fw-bold text-white fs-6">{{ number_format($supply->stock_total, 1) }}</span>
                        <small class="text-muted">{{ $supply->unidad_medida }}</small>
                    </td>
                    <td>
                        <span class="text-white fw-semibold">${{ number_format($supply->costo_unitario, 2) }}</span>
                    </td>
                    <td>
                        <span class="badge" style="background: var(--surface-quiet); color: var(--text-muted); font-size: 0.74rem;">
                            <i class="fa-regular fa-clock me-1 text-info"></i> {{ $supply->tiempo_entrega_dias }} días
                        </span>
                    </td>
                    <td>
                        <span class="text-muted" style="font-size: 0.78rem;">
                            {{ $supply->consumo_diario_por_persona }} {{ $supply->unidad_medida }}/hab/día
                        </span>
                    </td>
                    <td class="text-end">
                        <div class="d-inline-flex gap-2">
                            <a href="{{ route('supplies.show', $supply) }}" class="btn-glass" title="Ver detalles y lotes">
                                <i class="fa-solid fa-eye text-info"></i>
                            </a>
                            @if(auth()->check() && auth()->user()->canManageSupplies())
                            <a href="{{ route('supplies.edit', $supply) }}" class="btn-glass" title="Editar Insumo">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-5 text-muted">
                        <i class="fa-solid fa-boxes-packing fs-1 mb-3 text-secondary d-block"></i>
                        No se encontraron insumos con los filtros seleccionados.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
