@extends('layouts.app')

@section('title', 'Kardex y Movimientos de Inventario')
@section('page-title', 'Libro Mayor de Kardex y Movimientos')
@section('page-subtitle', 'Trazabilidad estricta de entradas (donaciones/compras) y salidas (consumo/mermas) con control FEFO')

@section('content')

@php
    $totalMovimientos = $movements->count();
    $entradasCount = $movements->where('es_entrada', true)->count();
    $salidasCount = $movements->where('es_entrada', false)->count();
@endphp

<!-- 1. COCKPIT HEADER CON TELEMETRÍA KARDEX -->
<div class="cockpit-page-header">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge" style="background: rgba(216, 150, 86, 0.15); color: var(--amber); font-size: 0.72rem; border: 1px solid rgba(216, 150, 86, 0.35); border-radius: 9999px; padding: 3px 10px;">
                <i class="fa-solid fa-clipboard-check me-1"></i> Auditoría FEFO Activa
            </span>
            <span class="badge" style="background: rgba(16, 185, 129, 0.2); color: var(--ok-text); font-size: 0.72rem; border: 1px solid rgba(16, 185, 129, 0.4); border-radius: 9999px; padding: 3px 10px;">
                Registro Criptográfico Inmutable
            </span>
        </div>
        <h3 class="cockpit-header-title">Kardex de Entradas y Salidas</h3>
        <p class="cockpit-header-subtitle">Historial cronológico de abastecimiento, raciones servidas y mermas por caducidad</p>
    </div>

    <div class="d-flex align-items-center gap-3 flex-wrap">
        <div class="d-flex gap-2">
            <div class="p-2 px-3 rounded-3" style="background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); text-align: center;">
                <span class="text-success d-block" style="font-size: 0.68rem; text-transform: uppercase;">Entradas</span>
                <strong class="text-success fs-6">+{{ $entradasCount }}</strong>
            </div>
            <div class="p-2 px-3 rounded-3" style="background: rgba(244,63,94,0.1); border: 1px solid rgba(244,63,94,0.3); text-align: center;">
                <span class="text-danger d-block" style="font-size: 0.68rem; text-transform: uppercase;">Salidas</span>
                <strong class="text-danger fs-6">-{{ $salidasCount }}</strong>
            </div>
        </div>

        @if(auth()->check() && auth()->user()->canManageKardex())
        <a href="{{ route('movements.create') }}" class="btn-accent">
            <i class="fa-solid fa-plus"></i> Registrar Movimiento
        </a>
        @endif
    </div>
</div>

<!-- 2. FILTROS EN VIDRIO OSCURO -->
<div class="glass-card p-3 mb-4">
    <form method="GET" action="{{ route('movements.index') }}" class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
        <div class="d-flex flex-wrap gap-2 align-items-center flex-grow-1">
            <select name="shelter_id" class="form-select-glass" style="max-width: 250px;">
                <option value="">🌐 Todos los Refugios</option>
                @foreach($shelters as $sh)
                    <option value="{{ $sh->id }}" {{ ((request()->has('shelter_id') ? request('shelter_id') : session('shelter_id')) == $sh->id) ? 'selected' : '' }}>
                        [{{ $sh->codigo }}] {{ $sh->nombre }}
                    </option>
                @endforeach
            </select>

            <select name="supply_id" class="form-select-glass" style="max-width: 220px;">
                <option value="">Todos los Insumos</option>
                @foreach($supplies as $sp)
                    <option value="{{ $sp->id }}" {{ request('supply_id') == $sp->id ? 'selected' : '' }}>{{ $sp->nombre }}</option>
                @endforeach
            </select>

            <select name="tipo" class="form-select-glass" style="max-width: 220px;">
                <option value="">Todos los Tipos de Operación</option>
                <option value="entrada_donacion" {{ request('tipo') == 'entrada_donacion' ? 'selected' : '' }}>Entrada: Donación</option>
                <option value="entrada_compra" {{ request('tipo') == 'entrada_compra' ? 'selected' : '' }}>Entrada: Compra</option>
                <option value="entrada_transferencia" {{ request('tipo') == 'entrada_transferencia' ? 'selected' : '' }}>Entrada: Transferencia</option>
                <option value="salida_consumo" {{ request('tipo') == 'salida_consumo' ? 'selected' : '' }}>Salida: Consumo / Ración</option>
                <option value="salida_transferencia" {{ request('tipo') == 'salida_transferencia' ? 'selected' : '' }}>Salida: Despacho a otro centro</option>
                <option value="salida_merma_caducidad" {{ request('tipo') == 'salida_merma_caducidad' ? 'selected' : '' }}>Salida: Merma / Caducidad</option>
            </select>

            <button type="submit" class="btn-accent"><i class="fa-solid fa-filter me-1"></i> Filtrar</button>
            @if(request()->hasAny(['shelter_id', 'supply_id', 'tipo']))
                <a href="{{ route('movements.index') }}" class="btn-glass text-danger"><i class="fa-solid fa-xmark me-1"></i> Limpiar</a>
            @endif
        </div>
        <span class="text-muted small">Mostrando {{ $movements->count() }} operaciones</span>
    </form>
</div>

<!-- 3. TABLA KARDEX DE ALTA GAMA -->
<div class="glass-card p-4">
    <div class="table-responsive">
        <table class="dark-table">
            <thead>
                <tr>
                    <th>Fecha y Hora</th>
                    <th>Refugio</th>
                    <th>Insumo</th>
                    <th>Operación</th>
                    <th>Lote / Vencimiento</th>
                    <th>Cantidad</th>
                    <th>Stock Previo</th>
                    <th>Stock Final</th>
                    <th>Motivo / Referencia</th>
                    <th class="text-end">Operador</th>
                </tr>
            </thead>
            <tbody>
                @forelse($movements as $mov)
                <tr>
                    <td style="font-size: 0.76rem; color: var(--text-muted); white-space: nowrap;">
                        <i class="fa-regular fa-calendar-check text-info me-1"></i>
                        {{ $mov->fecha_movimiento->format('d/m/Y H:i') }}
                    </td>
                    <td>
                        <strong class="text-white">{{ $mov->shelter->nombre }}</strong>
                    </td>
                    <td>
                        <strong class="text-white d-block">{{ $mov->supply->nombre }}</strong>
                        <small class="text-muted">{{ $mov->supply->codigo }} &bull; {{ $mov->supply->subcategoria }}</small>
                    </td>
                    <td>
                        <span class="bg-glass {{ $mov->tipo_badge['class'] }}">
                            <i class="fa-solid {{ $mov->tipo_badge['icon'] }}"></i> {{ $mov->tipo_badge['text'] }}
                        </span>
                    </td>
                    <td>
                        @if($mov->batch)
                            <code style="color: var(--amber); font-weight: 700;">{{ $mov->batch->numero_lote }}</code>
                            @if($mov->batch->fecha_vencimiento)
                                <small class="d-block text-muted">Vence: {{ $mov->batch->fecha_vencimiento->format('d/m/Y') }}</small>
                            @endif
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        <span class="fw-bold fs-6 {{ $mov->es_entrada ? 'text-success' : 'text-danger' }}">
                            {{ $mov->es_entrada ? '+' : '-' }}{{ number_format($mov->cantidad, 1) }} {{ $mov->supply->unidad_medida }}
                        </span>
                    </td>
                    <td class="text-muted" style="font-size: 0.8rem;">
                        {{ number_format($mov->stock_anterior, 1) }}
                    </td>
                    <td>
                        <strong class="text-white">{{ number_format($mov->stock_resultante, 1) }}</strong>
                    </td>
                    <td style="max-width: 220px; font-size: 0.78rem;" class="text-muted">
                        {{ $mov->motivo ?: 'Sin observación' }}
                    </td>
                    <td class="text-end">
                        <span class="badge" style="background: var(--surface-quiet); color: var(--text-muted); font-size: 0.72rem; padding: 4px 8px;">
                            <i class="fa-solid fa-user-check me-1 text-info"></i> {{ $mov->user->name ?? 'Sistema' }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center py-5 text-muted">
                        <i class="fa-solid fa-clipboard-list fs-1 mb-3 text-secondary d-block"></i>
                        No se encontraron movimientos registrados con los filtros aplicados.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
