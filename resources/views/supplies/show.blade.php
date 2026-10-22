@extends('layouts.app')

@section('title', $supply->nombre . ' - Análisis I.O.')
@section('page-title', $supply->nombre)
@section('page-subtitle', 'Análisis Cuantitativo de Inventario (EOQ, ROP y Newsvendor) - Código: ' . $supply->codigo)

@section('content')
<!-- COCKPIT HEADER DEL INSUMO -->
<div class="cockpit-page-header">
    <div>
        <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
            <span class="badge" style="background: rgba(230,168,108,0.15); border: 1px solid rgba(230,168,108,0.35); color: var(--amber); font-family:'JetBrains Mono',monospace; font-size:0.75rem;">
                <i class="fa-solid fa-barcode me-1"></i>{{ $supply->codigo }}
            </span>
            <span class="badge {{ $supply->categoria_badge['class'] }}" style="font-size:0.75rem;">
                <i class="fa-solid {{ $supply->categoria_badge['icon'] }} me-1"></i> {{ $supply->categoria_badge['text'] }}
            </span>
            <span class="badge {{ $supply->abc_badge['class'] }}" style="font-size:0.75rem;">
                Clase {{ $supply->clasificacion_abc }}
            </span>
            @if($supply->es_perecedero)
                <span class="badge" style="background: rgba(245,158,11,0.15); border: 1px solid rgba(245,158,11,0.35); color: var(--amber); font-size:0.72rem;">
                    <i class="fa-solid fa-hourglass-half me-1"></i> FEFO Activo
                </span>
            @endif
        </div>
        <h2 class="cockpit-header-title text-white mb-1">{{ $supply->nombre }}</h2>
        <p class="cockpit-header-subtitle">
            <span class="text-info">{{ $supply->subcategoria }}</span> &bull; Unidad de Medida: <strong>{{ $supply->unidad_medida }}</strong>
        </p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('supplies.edit', $supply) }}" class="btn-glass" style="font-size:0.8rem;">
            <i class="fa-solid fa-pen-to-square me-1"></i> Modificar Parámetros
        </a>
        <a href="{{ route('supplies.index') }}" class="btn-glass" style="font-size:0.8rem;">
            <i class="fa-solid fa-arrow-left me-1"></i> Catálogo
        </a>
    </div>
</div>

<!-- TELEMETRÍA DE PARÁMETROS CUANTITATIVOS -->
<div class="glass-card p-4 mb-4">
    <div class="section-heading text-info mb-3">
        <i class="fa-solid fa-sliders me-1"></i> Parámetros Operativos del Insumo en Red
    </div>
    <div class="row g-3 text-center">
        <div class="col-6 col-md-2">
            <div class="p-2 rounded-3" style="background: var(--surface-quiet); border: 1px solid var(--quiet-border);">
                <span class="stat-label">Costo Unitario (C)</span>
                <div class="fs-5 fw-bold text-white font-monospace">${{ number_format($supply->costo_unitario, 2) }}</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="p-2 rounded-3" style="background: var(--surface-quiet); border: 1px solid var(--quiet-border);">
                <span class="stat-label">Costo de Pedido (S)</span>
                <div class="fs-5 fw-bold text-white font-monospace">${{ number_format($supply->costo_pedido, 2) }}</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="p-2 rounded-3" style="background: var(--surface-quiet); border: 1px solid var(--quiet-border);">
                <span class="stat-label">Costo Almacén/Año (H)</span>
                <div class="fs-5 fw-bold text-white font-monospace">${{ number_format($supply->costo_mantenimiento_anual, 2) }}</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="p-2 rounded-3" style="background: rgba(56,189,248,0.06); border: 1px solid rgba(56,189,248,0.2);">
                <span class="stat-label text-info">Lead Time (L)</span>
                <div class="fs-5 fw-bold text-info font-monospace">{{ $supply->tiempo_entrega_dias }} días</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="p-2 rounded-3" style="background: rgba(245,158,11,0.06); border: 1px solid rgba(245,158,11,0.2);">
                <span class="stat-label text-warning">Consumo Ref./Día</span>
                <div class="fs-5 fw-bold text-warning font-monospace">{{ $supply->consumo_diario_referencia }} <small style="font-size:0.7rem;">{{ $supply->unidad_medida }}</small></div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="p-2 rounded-3" style="background: rgba(16,185,129,0.08); border: 1px solid rgba(16,185,129,0.25);">
                <span class="stat-label text-success">Stock Global Red</span>
                <div class="fs-5 fw-bold text-success font-monospace">{{ number_format($supply->total_stock, 1) }}</div>
            </div>
        </div>
    </div>
</div>

<!-- MODELO EOQ (CANTIDAD ECONÓMICA DE PEDIDO) -->
<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="glass-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div class="section-heading mb-0 text-info" style="font-size:0.9rem;">
                    <i class="fa-solid fa-square-root-variable me-1"></i> Resultados del Modelo EOQ (Wilson)
                </div>
                <span class="badge" style="background: rgba(230,168,108,0.12); border: 1px solid rgba(230,168,108,0.3); color: var(--amber); font-size:0.72rem;">
                    Demanda Anual D = {{ number_format($demandaAnual, 0) }}
                </span>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-6">
                    <div class="p-3 rounded-3" style="background: rgba(230,168,108,0.08); border: 1px solid rgba(230,168,108,0.25);">
                        <span class="stat-label text-info">Lote Óptimo de Compra (Q*)</span>
                        <div class="fs-3 fw-bold text-white font-monospace">{{ $eoq['q_optimo_entero'] }} <small style="font-size:0.75rem;" class="text-muted">{{ $supply->unidad_medida }}</small></div>
                        <small class="text-muted" style="font-size:0.72rem;">Minimiza el costo total anual</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 rounded-3" style="background: rgba(16,185,129,0.08); border: 1px solid rgba(16,185,129,0.25);">
                        <span class="stat-label text-success">Punto de Reorden (ROP)</span>
                        <div class="fs-3 fw-bold text-white font-monospace">{{ $eoq['rop'] }} <small style="font-size:0.75rem;" class="text-muted">{{ $supply->unidad_medida }}</small></div>
                        <small class="text-muted" style="font-size:0.72rem;">Incluye {{ $supply->stock_seguridad_sugerido }} de stock seguridad</small>
                    </div>
                </div>
            </div>

            <table class="dark-table mb-2">
                <tbody>
                    <tr>
                        <td class="text-muted">Frecuencia Recomendada de Pedidos (N):</td>
                        <td class="text-end fw-bold text-white font-monospace">{{ $eoq['num_ordenes_ano'] }} órdenes al año</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Tiempo Óptimo entre Pedidos (T):</td>
                        <td class="text-end fw-bold text-white font-monospace">Cada {{ $eoq['tiempo_entre_pedidos_dias'] }} días</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Costo Anual de Ordenar (D/Q * S):</td>
                        <td class="text-end font-monospace text-warning">${{ number_format($eoq['costo_ordenar'], 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Costo Anual de Almacenamiento (Q/2 * H):</td>
                        <td class="text-end font-monospace text-success">${{ number_format($eoq['costo_mantener'], 2) }}</td>
                    </tr>
                    <tr style="border-top: 1px solid rgba(255,255,255,0.12);">
                        <td class="fw-bold text-white">Costo Total Anual de Inventario:</td>
                        <td class="text-end fw-bold text-info fs-5 font-monospace">${{ number_format($eoq['costo_total_inventario'], 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- GRÁFICA INTERACTIVA DE COSTOS EOQ -->
    <div class="col-lg-6">
        <div class="glass-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="section-heading mb-0 text-white" style="font-size:0.9rem;">
                    <i class="fa-solid fa-chart-line me-1 text-info"></i> Curva de Costo Total vs Tamaño de Lote (Q)
                </div>
                <small class="badge" style="background: var(--surface-quiet); color: var(--text-muted); font-size:0.7rem;">Punto Mínimo = Q*</small>
            </div>
            <div style="height: 280px; position: relative;">
                <canvas id="chartEoq"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- MODELO NEWSVENDOR (SI APLICA) -->
@if($newsvendor)
<div class="glass-card p-4 mb-4" style="background: rgba(245,158,11,0.04); border-left: 4px solid #f59e0b;">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <div class="section-heading mb-1 text-warning" style="font-size:0.9rem;">
                <i class="fa-solid fa-newspaper me-1"></i> Modelo Newsvendor / Período Único (Demanda Probabilística)
            </div>
            <p class="m-0 text-muted" style="font-size:0.8rem;">
                Alineado a alimentos perecederos y kits de emergencia: balancea el costo de subestimación/escasez ($C_u$) vs costo de desecho por vencimiento ($C_o$).
            </p>
        </div>
        <span class="badge" style="background: rgba(245,158,11,0.15); border: 1px solid rgba(245,158,11,0.35); color: var(--amber); font-size:0.75rem;">
            Razón Crítica: {{ $newsvendor['razon_critica'] }}
        </span>
    </div>

    <div class="row g-3 text-center">
        <div class="col-md-3">
            <span class="stat-label">Costo Subestimación / Escasez (Cu)</span>
            <div class="fs-5 fw-bold text-danger font-monospace">${{ number_format($supply->costo_escasez_newsvendor, 2) }}</div>
        </div>
        <div class="col-md-3">
            <span class="stat-label">Costo Sobreestimación / Desecho (Co)</span>
            <div class="fs-5 fw-bold text-warning font-monospace">${{ number_format($supply->costo_desecho_newsvendor, 2) }}</div>
        </div>
        <div class="col-md-3">
            <span class="stat-label">Nivel de Servicio Óptimo (CR)</span>
            <div class="fs-5 fw-bold text-info font-monospace">{{ $newsvendor['nivel_servicio_pct'] }}%</div>
        </div>
        <div class="col-md-3">
            <div class="p-2 rounded-3" style="background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3);">
                <span class="stat-label text-success">Lote Óptimo Newsvendor (Q*)</span>
                <div class="fs-4 fw-bold text-white font-monospace">{{ $newsvendor['q_optimo_entero'] }} <small style="font-size:0.75rem;" class="text-muted">{{ $supply->unidad_medida }}</small></div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- DISTRIBUCIÓN DEL STOCK EN LOS REFUGIOS -->
<div class="glass-card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div class="section-heading mb-0 text-info" style="font-size:0.9rem;">
            <i class="fa-solid fa-tents me-1"></i> Existencias y Autonomía por Refugio
        </div>
        <small class="text-muted">Cálculo individualizado según la demanda diaria de cada centro</small>
    </div>

    <div class="table-responsive">
        <table class="dark-table">
            <thead>
                <tr>
                    <th>Refugio</th>
                    <th>Población</th>
                    <th>Stock Actual</th>
                    <th>Consumo Diario Refugio</th>
                    <th>Días de Autonomía</th>
                    <th>Semáforo I.O.</th>
                    <th class="text-end">Acción</th>
                </tr>
            </thead>
            <tbody>
                @foreach($supply->inventories as $inv)
                <tr>
                    <td>
                        <strong class="text-white">{{ $inv->shelter->nombre }}</strong>
                        <small class="d-block text-muted">{{ $inv->shelter->codigo }} &bull; {{ $inv->shelter->municipio }}</small>
                    </td>
                    <td>{{ $inv->shelter->poblacion_total }} albergados</td>
                    <td>
                        <span class="fw-bold text-white fs-6 font-monospace">{{ number_format($inv->stock_actual, 1) }}</span> 
                        <small class="text-muted">{{ $supply->unidad_medida }}</small>
                    </td>
                    <td>{{ $inv->consumo_diario_estimado }} <small class="text-muted">{{ $supply->unidad_medida }}/día</small></td>
                    <td>
                        <strong style="color: {{ $inv->nivel_alerta['color'] }}; font-size: 1.05rem;">
                            {{ $inv->dias_autonomia > 100 ? '> 90' : $inv->dias_autonomia }} días
                        </strong>
                    </td>
                    <td>
                        <span class="bg-glass {{ $inv->nivel_alerta['badge'] }}" style="font-size:0.68rem;">{{ $inv->nivel_alerta['estado'] }}</span>
                    </td>
                    <td class="text-end">
                        <a href="{{ route('shelters.show', $inv->shelter) }}" class="btn-glass" style="padding: 4px 10px; font-size: 0.74rem;">
                            Ver Refugio <i class="fa-solid fa-arrow-right ms-1"></i>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Colores de gráficos leídos de los tokens del tema (funcionan en modo claro y oscuro)
    const cssVar = (name) => (getComputedStyle(document.documentElement).getPropertyValue(name) || '').trim();

    const curvaData = {!! json_encode($eoq['curva_costos']) !!};
    if (curvaData && curvaData.length > 0) {
        const labels = curvaData.map(p => p.q);
        const cOrdenar = curvaData.map(p => p.costo_ordenar);
        const cMantener = curvaData.map(p => p.costo_mantener);
        const cTotal = curvaData.map(p => p.costo_total);

        const ctx = document.getElementById('chartEoq').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Costo de Ordenar',
                        data: cOrdenar,
                        borderColor: cssVar('--warn-text'),
                        backgroundColor: cssVar('--warn-soft'),
                        borderWidth: 2,
                        pointRadius: 0,
                        tension: 0.3
                    },
                    {
                        label: 'Costo de Mantener',
                        data: cMantener,
                        borderColor: cssVar('--ok'),
                        backgroundColor: cssVar('--ok-soft'),
                        borderWidth: 2,
                        pointRadius: 0,
                        tension: 0.3
                    },
                    {
                        label: 'Costo Total Anual',
                        data: cTotal,
                        borderColor: cssVar('--amber'),
                        backgroundColor: cssVar('--accent-soft'),
                        borderWidth: 3,
                        pointRadius: 0,
                        tension: 0.3,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: { color: cssVar('--text-primary'), font: { family: 'Inter', size: 11 } }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(c) { return c.dataset.label + ': $' + c.raw.toFixed(2); }
                        }
                    }
                },
                scales: {
                    x: {
                        title: { display: true, text: 'Tamaño del Lote (Q)', color: cssVar('--text-muted') },
                        ticks: { color: cssVar('--text-muted') },
                        grid: { color: cssVar('--border') }
                    },
                    y: {
                        title: { display: true, text: 'Costo Anual ($)', color: cssVar('--text-muted') },
                        ticks: { color: cssVar('--text-muted') },
                        grid: { color: cssVar('--border') }
                    }
                }
            }
        });
    }
});
</script>
@endpush
