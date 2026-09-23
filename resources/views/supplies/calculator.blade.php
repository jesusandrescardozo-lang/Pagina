@extends('layouts.app')

@section('title', 'Simulador y Calculadora I.O. de Inventarios')
@section('page-title', 'Simulador I.O. de Inventarios')
@section('page-subtitle', 'Optimización matemática mediante Modelos EOQ, ROP y Newsvendor para suministros humanitarios')

@section('content')

<!-- COCKPIT HEADER CON MODELOS I.O. -->
<div class="cockpit-page-header mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
            <span class="badge" style="background: rgba(14,165,233,0.2); color: #38bdf8; font-size:0.72rem; border: 1px solid rgba(14,165,233,0.4); border-radius: 9999px; padding:3px 12px;">
                <i class="fa-solid fa-brain me-1"></i> Modelos Cuantitativos I.O.
            </span>
            <span class="badge" style="background: rgba(16,185,129,0.2); color: #34d399; font-size:0.72rem; border: 1px solid rgba(16,185,129,0.4); border-radius: 9999px; padding:3px 12px;">
                <i class="fa-solid fa-calculator me-1"></i> EOQ Wilson &amp; Newsvendor
            </span>
            <span class="badge" style="background: rgba(245,158,11,0.15); color: #fbbf24; font-size:0.72rem; border: 1px solid rgba(245,158,11,0.35); border-radius: 9999px; padding:3px 12px;">
                <i class="fa-solid fa-chart-line me-1"></i> Simulación Dinámica
            </span>
        </div>
        <h2 class="cockpit-header-title text-white mb-1">Simulador Matemático de Inventarios Humanitarios</h2>
        <p class="cockpit-header-subtitle">Calcule el lote óptimo (Q*), puntos de reorden (ROP) y el fractil crítico estocástico Newsvendor.</p>
    </div>
    @if(isset($selectedSupply))
    <div class="text-end">
        <div class="badge mb-1" style="background: rgba(56,189,248,0.12); border: 1px solid rgba(56,189,248,0.3); color: #38bdf8; font-size:0.75rem; font-family:'JetBrains Mono',monospace;">
            {{ $selectedSupply->codigo }}
        </div>
        <div class="fw-bold text-white" style="font-size:0.9rem;">{{ $selectedSupply->nombre }}</div>
        <small class="text-muted">Clase {{ $selectedSupply->clasificacion_abc }} · {{ $selectedSupply->unidad_medida }}</small>
    </div>
    @endif
</div>

<!-- SELECTOR DE INSUMO PARA PRECARGA -->
<div class="glass-card p-3 mb-4">
    <form method="GET" action="{{ route('supplies.calculator') }}" class="d-flex flex-wrap align-items-center gap-3">
        <div class="d-flex align-items-center gap-2 flex-shrink-0">
            <i class="fa-solid fa-boxes-packing text-info"></i>
            <span class="stat-label mb-0">Precargar desde insumo del catálogo:</span>
        </div>
        <select name="supply_id" class="form-select-glass" style="max-width: 380px; flex: 1;" onchange="this.form.submit()">
            <option value="">— Ingreso Manual de Parámetros —</option>
            @foreach($supplies as $s)
                <option value="{{ $s->id }}" {{ (isset($selectedSupply) && $selectedSupply->id == $s->id) ? 'selected' : '' }}>
                    [{{ $s->codigo }}] {{ $s->nombre }} ({{ $s->unidad_medida }} · Clase {{ $s->clasificacion_abc }})
                </option>
            @endforeach
        </select>
        @if(isset($selectedSupply))
            <a href="{{ route('supplies.show', $selectedSupply) }}" class="btn-glass" style="font-size:0.8rem; white-space:nowrap;">
                <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Ver Análisis Completo
            </a>
        @endif
    </form>
</div>

<div class="row g-4">
    <!-- ============== COLUMNA 1: MODELO EOQ Y ROP ============== -->
    <div class="col-lg-6">
        <div class="glass-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
                <div>
                    <h5 class="fw-bold text-white mb-1">
                        <i class="fa-solid fa-calculator text-info me-2"></i> Modelo EOQ / Wilson
                    </h5>
                    <small class="text-muted">Minimización de costo total anual con demanda determinística</small>
                </div>
                <span class="badge" style="background: rgba(56,189,248,0.15); border: 1px solid rgba(56,189,248,0.35); color: #38bdf8; font-size:0.7rem;">EOQ &amp; ROP</span>
            </div>

            <!-- Fórmulas matemáticas -->
            <div class="p-2 px-3 rounded-3 mb-3" style="background: rgba(56,189,248,0.04); border: 1px solid rgba(56,189,248,0.15); font-size: 0.82rem; font-family:'JetBrains Mono',monospace;">
                <span class="text-info">Q* = √( (2 × D × S) / H )</span>
                &nbsp;&nbsp;|&nbsp;&nbsp;
                <span class="text-success">ROP = (d × L) + SS</span>
            </div>

            <form method="GET" action="{{ route('supplies.calculator') }}">
                @if(isset($selectedSupply))
                    <input type="hidden" name="supply_id" value="{{ $selectedSupply->id }}">
                @endif
                <input type="hidden" name="nv_media"    value="{{ $nvMedia }}">
                <input type="hidden" name="nv_desv"     value="{{ $nvDesv }}">
                <input type="hidden" name="nv_escasez"  value="{{ $nvEscasez }}">
                <input type="hidden" name="nv_desecho"  value="{{ $nvDesecho }}">

                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <label class="form-label-glass">Demanda Anual (D)</label>
                        <input type="number" step="1" name="demanda" value="{{ $demanda }}" min="1" class="form-control-glass font-monospace" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label-glass">Costo por Pedido (S)</label>
                        <input type="number" step="0.1" name="costo_pedido" value="{{ $costoPedido }}" min="0.1" class="form-control-glass font-monospace" required>
                    </div>
                </div>

                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <label class="form-label-glass">Costo Almacén / Año (H)</label>
                        <input type="number" step="0.01" name="costo_mantenimiento" value="{{ $costoMant }}" min="0.01" class="form-control-glass font-monospace" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label-glass">Costo Unitario (C)</label>
                        <input type="number" step="0.01" name="costo_unitario" value="{{ $costoUnit }}" min="0.01" class="form-control-glass font-monospace" required>
                    </div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label-glass">Lead Time en Días (L)</label>
                        <input type="number" step="0.1" name="lead_time" value="{{ $leadTime }}" min="0.1" class="form-control-glass font-monospace" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label-glass">Stock de Seguridad (SS)</label>
                        <input type="number" step="1" name="stock_seguridad" value="{{ $stockSeguridad }}" min="0" class="form-control-glass font-monospace" required>
                    </div>
                </div>

                <button type="submit" class="btn-accent w-100 justify-content-center mb-4">
                    <i class="fa-solid fa-rotate me-1"></i> Recalcular Modelo EOQ
                </button>
            </form>

            <!-- RESULTADOS EOQ -->
            <div class="row g-2 mb-3">
                <div class="col-6">
                    <div class="p-3 rounded-3 text-center" style="background: rgba(56,189,248,0.08); border: 1px solid rgba(56,189,248,0.25);">
                        <span class="stat-label text-info">Lote Óptimo (Q*)</span>
                        <div class="fs-2 fw-bold text-white font-monospace mt-1">{{ $eoq['q_optimo_entero'] }}</div>
                        <small class="text-muted" style="font-size:0.72rem;">Exacto: {{ $eoq['q_optimo'] }}</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 rounded-3 text-center" style="background: rgba(16,185,129,0.08); border: 1px solid rgba(16,185,129,0.25);">
                        <span class="stat-label text-success">Punto de Reorden (ROP)</span>
                        <div class="fs-2 fw-bold text-white font-monospace mt-1">{{ $eoq['rop'] }}</div>
                        <small class="text-muted" style="font-size:0.72rem;">d × L + SS</small>
                    </div>
                </div>
            </div>

            <table class="dark-table" style="font-size:0.82rem;">
                <tbody>
                    <tr>
                        <td class="text-muted">Núm. de Pedidos al Año (N = D/Q*):</td>
                        <td class="text-end fw-bold text-white font-monospace">{{ $eoq['num_ordenes_ano'] }} órdenes</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Tiempo entre Pedidos (T):</td>
                        <td class="text-end fw-bold text-white font-monospace">c/{{ $eoq['tiempo_entre_pedidos_dias'] }} días</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Costo Anual de Ordenar (D/Q × S):</td>
                        <td class="text-end font-monospace text-warning">${{ number_format($eoq['costo_ordenar'], 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Costo Anual de Mantener (Q/2 × H):</td>
                        <td class="text-end font-monospace text-success">${{ number_format($eoq['costo_mantener'], 2) }}</td>
                    </tr>
                    <tr style="border-top: 1px solid rgba(255,255,255,0.12);">
                        <td class="fw-bold text-white">Costo Total Anual de Inventario:</td>
                        <td class="text-end fw-bold text-info fs-5 font-monospace">${{ number_format($eoq['costo_total_inventario'], 2) }}</td>
                    </tr>
                </tbody>
            </table>

            <!-- GRÁFICA DE COSTOS EOQ -->
            <div class="mt-4" style="height: 200px; position: relative;">
                <canvas id="chartEoqSim"></canvas>
            </div>
        </div>
    </div>

    <!-- ============== COLUMNA 2: MODELO NEWSVENDOR ============== -->
    <div class="col-lg-6">
        <div class="glass-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
                <div>
                    <h5 class="fw-bold text-white mb-1">
                        <i class="fa-solid fa-newspaper text-warning me-2"></i> Modelo Newsvendor (Período Único)
                    </h5>
                    <small class="text-muted">Optimización estocástica para insumos perecederos y kits de emergencia</small>
                </div>
                <span class="badge" style="background: rgba(245,158,11,0.15); border: 1px solid rgba(245,158,11,0.35); color: #fbbf24; font-size:0.7rem;">Demanda Normal</span>
            </div>

            <!-- Fórmulas matemáticas Newsvendor -->
            <div class="p-2 px-3 rounded-3 mb-3" style="background: rgba(245,158,11,0.05); border: 1px solid rgba(245,158,11,0.2); font-size: 0.82rem; font-family:'JetBrains Mono',monospace;">
                <span class="text-warning">CR = Cu / (Cu + Co)</span>
                &nbsp;&nbsp;|&nbsp;&nbsp;
                <span class="text-white">Q* = μ + Z × σ</span>
            </div>

            <form method="GET" action="{{ route('supplies.calculator') }}">
                @if(isset($selectedSupply))
                    <input type="hidden" name="supply_id" value="{{ $selectedSupply->id }}">
                @endif
                <input type="hidden" name="demanda"           value="{{ $demanda }}">
                <input type="hidden" name="costo_pedido"      value="{{ $costoPedido }}">
                <input type="hidden" name="costo_mantenimiento" value="{{ $costoMant }}">
                <input type="hidden" name="costo_unitario"    value="{{ $costoUnit }}">
                <input type="hidden" name="lead_time"         value="{{ $leadTime }}">
                <input type="hidden" name="stock_seguridad"   value="{{ $stockSeguridad }}">

                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <label class="form-label-glass">Demanda Promedio (μ)</label>
                        <input type="number" step="1" name="nv_media" value="{{ $nvMedia }}" min="1" class="form-control-glass font-monospace" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label-glass">Desviación Estándar (σ)</label>
                        <input type="number" step="1" name="nv_desv" value="{{ $nvDesv }}" min="1" class="form-control-glass font-monospace" required>
                    </div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label-glass">Costo Escasez / Subest. (Cu)</label>
                        <input type="number" step="0.1" name="nv_escasez" value="{{ $nvEscasez }}" min="0.1" class="form-control-glass font-monospace" title="Costo humanitario por desabastecimiento" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label-glass">Costo Desecho / Sobreest. (Co)</label>
                        <input type="number" step="0.1" name="nv_desecho" value="{{ $nvDesecho }}" min="0.1" class="form-control-glass font-monospace" title="Pérdida por vencimiento o excedente" required>
                    </div>
                </div>

                <button type="submit" class="w-100 justify-content-center mb-4 btn-glass text-warning fw-bold" style="border-color: rgba(245,158,11,0.4); padding: 12px; border-radius: 12px; font-size:0.9rem;">
                    <i class="fa-solid fa-rotate me-1"></i> Recalcular Modelo Newsvendor
                </button>
            </form>

            <!-- RESULTADOS NEWSVENDOR -->
            <div class="row g-2 mb-3">
                <div class="col-6">
                    <div class="p-3 rounded-3 text-center" style="background: rgba(245,158,11,0.08); border: 1px solid rgba(245,158,11,0.25);">
                        <span class="stat-label text-warning">Razón Crítica (CR)</span>
                        <div class="fs-2 fw-bold text-white font-monospace mt-1">{{ $newsvendor['razon_critica'] }}</div>
                        <small class="text-muted" style="font-size:0.72rem;">Ns: {{ $newsvendor['nivel_servicio_pct'] }}%</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 rounded-3 text-center" style="background: rgba(16,185,129,0.08); border: 1px solid rgba(16,185,129,0.25);">
                        <span class="stat-label text-success">Lote Óptimo NV (Q*)</span>
                        <div class="fs-2 fw-bold text-white font-monospace mt-1">{{ $newsvendor['q_optimo_entero'] }}</div>
                        <small class="text-muted" style="font-size:0.72rem;">Z={{ $newsvendor['z_score'] }}</small>
                    </div>
                </div>
            </div>

            <!-- PANEL DE INTERPRETACIÓN -->
            <div class="p-3 rounded-3 mb-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08);">
                <div class="section-heading text-info mb-2" style="font-size:0.8rem;">
                    <i class="fa-solid fa-lightbulb me-1"></i> Interpretación para la Toma de Decisiones en Refugios
                </div>
                <ul class="text-muted ps-3 mb-0" style="font-size: 0.79rem; line-height: 1.7;">
                    <li>Con Cu = <strong class="text-danger">${{ number_format($nvEscasez, 2) }}</strong> &gt; Co = <strong class="text-warning">${{ number_format($nvDesecho, 2) }}</strong>, el sistema fija un nivel de servicio de <strong class="text-white">{{ $newsvendor['nivel_servicio_pct'] }}%</strong>.</li>
                    <li>Q* = <strong class="text-white">{{ $newsvendor['q_optimo_entero'] }}</strong> supera la media (μ={{ $nvMedia }}) con un stock de protección de <strong class="text-success">{{ $newsvendor['stock_seguridad_newsvendor'] }} unidades</strong>.</li>
                    <li>Probabilidad de desabastecimiento reducida a <strong class="text-info">{{ $newsvendor['probabilidad_escasez_pct'] }}%</strong>.</li>
                </ul>
            </div>

            <!-- PANEL COMPARATIVO EOQ vs NEWSVENDOR -->
            <div class="p-3 rounded-3" style="background: linear-gradient(135deg, rgba(56,189,248,0.05), rgba(245,158,11,0.05)); border: 1px solid rgba(255,255,255,0.1);">
                <div class="section-heading mb-2" style="font-size:0.8rem; color: rgba(255,255,255,0.7);">
                    <i class="fa-solid fa-scale-balanced me-1 text-info"></i> Comparativa de Modelos
                </div>
                <div class="row g-2 text-center">
                    <div class="col-4">
                        <small class="text-muted d-block" style="font-size:0.68rem; text-transform:uppercase;">EOQ (Q*)</small>
                        <span class="fw-bold text-info font-monospace">{{ $eoq['q_optimo_entero'] }}</span>
                    </div>
                    <div class="col-4">
                        <small class="text-muted d-block" style="font-size:0.68rem; text-transform:uppercase;">Newsv. (Q*)</small>
                        <span class="fw-bold text-warning font-monospace">{{ $newsvendor['q_optimo_entero'] }}</span>
                    </div>
                    <div class="col-4">
                        <small class="text-muted d-block" style="font-size:0.68rem; text-transform:uppercase;">ROP</small>
                        <span class="fw-bold text-success font-monospace">{{ $eoq['rop'] }}</span>
                    </div>
                </div>
                <div class="mt-2">
                    @php
                        $diff = $newsvendor['q_optimo_entero'] - $eoq['q_optimo_entero'];
                        $diffPercent = $eoq['q_optimo_entero'] > 0 ? round(($diff / $eoq['q_optimo_entero']) * 100) : 0;
                    @endphp
                    @if($diff > 0)
                        <small class="text-warning d-block text-center mt-1" style="font-size:0.72rem;">
                            <i class="fa-solid fa-arrow-up me-1"></i> El modelo Newsvendor recomienda <strong>{{ abs($diff) }} unidades más</strong> (+{{ $diffPercent }}%) para cubrir la variabilidad de demanda.
                        </small>
                    @elseif($diff < 0)
                        <small class="text-success d-block text-center mt-1" style="font-size:0.72rem;">
                            <i class="fa-solid fa-arrow-down me-1"></i> El modelo Newsvendor recomienda <strong>{{ abs($diff) }} unidades menos</strong> ({{ $diffPercent }}%) por el costo de desecho.
                        </small>
                    @else
                        <small class="text-muted d-block text-center mt-1" style="font-size:0.72rem;">Ambos modelos convergen al mismo lote óptimo.</small>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const curvaData = {!! json_encode($eoq['curva_costos']) !!};
    if (curvaData && curvaData.length > 0) {
        const labels = curvaData.map(p => p.q);
        const cOrdenar = curvaData.map(p => p.costo_ordenar);
        const cMantener = curvaData.map(p => p.costo_mantener);
        const cTotal = curvaData.map(p => p.costo_total);

        const ctx = document.getElementById('chartEoqSim').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Costo Ordenar',
                        data: cOrdenar,
                        borderColor: '#f59e0b',
                        backgroundColor: 'rgba(245,158,11,0.05)',
                        borderWidth: 1.5,
                        pointRadius: 0,
                        tension: 0.3
                    },
                    {
                        label: 'Costo Mantener',
                        data: cMantener,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16,185,129,0.05)',
                        borderWidth: 1.5,
                        pointRadius: 0,
                        tension: 0.3
                    },
                    {
                        label: 'Costo Total',
                        data: cTotal,
                        borderColor: '#38bdf8',
                        backgroundColor: 'rgba(56,189,248,0.08)',
                        borderWidth: 2.5,
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
                        labels: { color: '#f1f0ff', font: { size: 10 } }
                    },
                    tooltip: {
                        callbacks: {
                            label: c => c.dataset.label + ': $' + c.raw.toFixed(2)
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: { color: 'rgba(241,240,255,0.55)', font: { size: 9 } },
                        grid: { color: 'rgba(255,255,255,0.04)' },
                        title: { display: true, text: 'Tamaño del Lote (Q)', color: 'rgba(241,240,255,0.5)', font: { size: 9 } }
                    },
                    y: {
                        ticks: { color: 'rgba(241,240,255,0.55)', font: { size: 9 } },
                        grid: { color: 'rgba(255,255,255,0.04)' },
                        title: { display: true, text: 'Costo ($)', color: 'rgba(241,240,255,0.5)', font: { size: 9 } }
                    }
                }
            }
        });
    }
});
</script>
@endpush
