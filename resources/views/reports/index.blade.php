@extends('layouts.app')

@section('title', 'Centro de Reportes Oficiales')
@section('page-title', 'Centro de Emisión de Reportes Oficiales')
@section('page-subtitle', 'Informes en PDF y exportaciones para análisis logístico y evaluación de Investigación de Operaciones')

@section('content')

<!-- 1. COCKPIT HEADER CON TELEMETRÍA DE REPORTES -->
<div class="cockpit-page-header">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge" style="background: rgba(14, 165, 233, 0.2); color: #38bdf8; font-size: 0.72rem; border: 1px solid rgba(14, 165, 233, 0.4); border-radius: 9999px; padding: 3px 10px;">
                <i class="fa-solid fa-file-shield me-1"></i> Documentación Oficial Certificada
            </span>
            <span class="badge" style="background: rgba(16, 185, 129, 0.2); color: #34d399; font-size: 0.72rem; border: 1px solid rgba(16, 185, 129, 0.4); border-radius: 9999px; padding: 3px 10px;">
                Exportación PDF Directa
            </span>
        </div>
        <h3 class="cockpit-header-title">Generación de Informes y Auditoría</h3>
        <p class="cockpit-header-subtitle">Descarga de expedientes consolidados de inventario, libro mayor de movimientos y modelos matemáticos</p>
    </div>
</div>

<!-- 2. TARJETAS DE REPORTES OFICIALES -->
<div class="row g-4">
    <!-- Reporte 1: Estado de Inventario y Autonomía -->
    <div class="col-lg-4">
        <div class="glass-card p-4 h-100 d-flex flex-column justify-content-between" style="border-top: 3px solid #0ea5e9;">
            <div>
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div style="width: 48px; height: 48px; border-radius: 14px; background: rgba(14, 165, 233, 0.15); border: 1px solid rgba(14, 165, 233, 0.35); display: flex; align-items: center; justify-content: center; color: #38bdf8; font-size: 1.35rem;">
                        <i class="fa-solid fa-boxes-stacked"></i>
                    </div>
                    <div>
                        <span class="badge" style="background: rgba(14, 165, 233, 0.15); color: #38bdf8; font-size: 0.68rem;">PDF &bull; Existencias</span>
                        <h5 class="fw-bold text-white mb-0" style="font-size: 1.05rem;">Inventario &amp; Cobertura</h5>
                    </div>
                </div>

                <p class="text-muted" style="font-size: 0.80rem; line-height: 1.5;">
                    Consolidado de stock de alimentos y medicinas por refugio, cálculo de consumo diario per cápita y semáforo de riesgo de desabastecimiento.
                </p>
            </div>

            <form action="{{ route('reports.inventario-refugios') }}" method="GET" class="pt-3 border-top border-secondary border-opacity-25 mt-auto">
                <div class="mb-3">
                    <label class="form-label-glass">Filtrar por Refugio</label>
                    <select name="shelter_id" class="form-select-glass">
                        <option value="">🌐 -- Red Centralizada (Todos) --</option>
                        @foreach($shelters as $s)
                            <option value="{{ $s->id }}" {{ session('shelter_id') == $s->id ? 'selected' : '' }}>
                                [{{ $s->codigo }}] {{ $s->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn-accent w-100 justify-content-center">
                    <i class="fa-solid fa-file-arrow-down me-1"></i> Descargar Informe (PDF)
                </button>
            </form>
        </div>
    </div>

    <!-- Reporte 2: Kardex de Movimientos -->
    <div class="col-lg-4">
        <div class="glass-card p-4 h-100 d-flex flex-column justify-content-between" style="border-top: 3px solid #10b981;">
            <div>
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div style="width: 48px; height: 48px; border-radius: 14px; background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.35); display: flex; align-items: center; justify-content: center; color: #34d399; font-size: 1.35rem;">
                        <i class="fa-solid fa-clipboard-check"></i>
                    </div>
                    <div>
                        <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #34d399; font-size: 0.68rem;">PDF &bull; Trazabilidad</span>
                        <h5 class="fw-bold text-white mb-0" style="font-size: 1.05rem;">Kardex Oficial</h5>
                    </div>
                </div>

                <p class="text-muted" style="font-size: 0.80rem; line-height: 1.5;">
                    Auditoría formal de donaciones recibidas, compras institucionales, raciones consumidas y lotes vencidos con trazabilidad de operadores.
                </p>
            </div>

            <form action="{{ route('reports.kardex-movimientos') }}" method="GET" class="pt-3 border-top border-secondary border-opacity-25 mt-auto">
                <div class="mb-2">
                    <label class="form-label-glass">Refugio</label>
                    <select name="shelter_id" class="form-select-glass">
                        <option value="">🌐 -- Red Centralizada (Todos) --</option>
                        @foreach($shelters as $s)
                            <option value="{{ $s->id }}" {{ session('shelter_id') == $s->id ? 'selected' : '' }}>
                                [{{ $s->codigo }}] {{ $s->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label-glass">Desde</label>
                        <input type="date" name="fecha_desde" class="form-control-glass">
                    </div>
                    <div class="col-6">
                        <label class="form-label-glass">Hasta</label>
                        <input type="date" name="fecha_hasta" class="form-control-glass">
                    </div>
                </div>
                <button type="submit" class="btn-accent w-100 justify-content-center" style="background: linear-gradient(135deg, #059669, #10b981);">
                    <i class="fa-solid fa-file-arrow-down me-1"></i> Descargar Kardex (PDF)
                </button>
            </form>
        </div>
    </div>

    <!-- Reporte 3: Informe Técnico de Investigación de Operaciones -->
    <div class="col-lg-4">
        <div class="glass-card p-4 h-100 d-flex flex-column justify-content-between" style="border-top: 3px solid #f59e0b;">
            <div>
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div style="width: 48px; height: 48px; border-radius: 14px; background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.35); display: flex; align-items: center; justify-content: center; color: #fbbf24; font-size: 1.35rem;">
                        <i class="fa-solid fa-chart-line"></i>
                    </div>
                    <div>
                        <span class="badge" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24; font-size: 0.68rem;">PDF &bull; Técnico I.O.</span>
                        <h5 class="fw-bold text-white mb-0" style="font-size: 1.05rem;">Modelos I.O. &amp; ABC</h5>
                    </div>
                </div>

                <p class="text-muted" style="font-size: 0.80rem; line-height: 1.5;">
                    Documento técnico y académico con los modelos matemáticos resueltos (EOQ, Newsvendor), análisis de Pareto ABC y rebalanceo entre refugios.
                </p>
            </div>

            <div class="pt-3 border-top border-secondary border-opacity-25 mt-auto">
                <a href="{{ route('reports.analisis-operaciones') }}" class="btn-accent w-100 justify-content-center" style="background: linear-gradient(135deg, #d97706, #f59e0b);">
                    <i class="fa-solid fa-file-arrow-down me-1"></i> Descargar Informe I.O. (PDF)
                </a>
            </div>
        </div>
    </div>
</div>

@endsection
