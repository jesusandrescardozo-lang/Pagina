@extends('layouts.app')

@section('title', 'Método CPM - Ruta Crítica')
@section('page-title', 'Método de la Ruta Crítica (CPM) en Abastecimiento')
@section('page-subtitle', 'Optimización matemática de tiempos, actividades en red y cálculo de holguras para contingencias humanitarias')

@section('content')

<!-- 1. COCKPIT HEADER CON TELEMETRÍA CPM -->
<div class="cockpit-page-header">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge" style="background: rgba(139, 92, 246, 0.2); color: #a78bfa; font-size: 0.72rem; border: 1px solid rgba(139, 92, 246, 0.4); border-radius: 9999px; padding: 3px 10px;">
                <i class="fa-solid fa-diagram-project me-1"></i> Grafos y Redes de Optimización
            </span>
            <span class="badge" style="background: rgba(14, 165, 233, 0.2); color: #38bdf8; font-size: 0.72rem; border: 1px solid rgba(14, 165, 233, 0.4); border-radius: 9999px; padding: 3px 10px;">
                {{ $projects->count() }} Proyectos Registrados
            </span>
        </div>
        <h3 class="cockpit-header-title">Planificación y Ruta Crítica CPM</h3>
        <p class="cockpit-header-subtitle">Identificación de cuellos de botella en la cadena humanitaria y tiempos mínimos de entrega</p>
    </div>

    <div class="d-flex align-items-center gap-2 flex-wrap">
        <a href="{{ route('cpm.create') }}" class="btn-accent" style="background: linear-gradient(135deg, #7c3aed, #a78bfa);">
            <i class="fa-solid fa-plus"></i> Nuevo Proyecto CPM
        </a>
    </div>
</div>

<!-- 2. CUADRÍCULA DE PROYECTOS CPM -->
<div class="row g-4">
    @forelse($projects as $p)
    <div class="col-lg-6">
        <div class="glass-card p-4 h-100 d-flex flex-column justify-content-between" style="border-top: 3px solid #8b5cf6;">
            <div>
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <span class="bg-glass bg-purple" style="font-size: 0.72rem; font-weight: 700;">
                        {{ strtoupper(str_replace('_', ' ', $p->tipo)) }}
                    </span>
                    <span class="badge" style="background: rgba(255, 255, 255, 0.05); color: #cbd5e1; font-size: 0.74rem;">
                        <i class="fa-solid fa-list-check me-1 text-info"></i> {{ $p->activities_count }} Actividades
                    </span>
                </div>

                <h4 class="fw-bold text-white mb-2" style="font-size: 1.15rem;">{{ $p->nombre }}</h4>
                <p class="text-muted mb-3" style="font-size: 0.82rem; line-height: 1.5;">
                    {{ $p->descripcion ?: 'Plan operativo de distribución y tiempos de respuesta de emergencia.' }}
                </p>

                <!-- Visual Grafo Preview Badge -->
                <div class="p-3 rounded-3 mb-3 d-flex align-items-center gap-3" style="background: rgba(139, 92, 246, 0.08); border: 1px solid rgba(139, 92, 246, 0.2);">
                    <div style="width: 42px; height: 42px; border-radius: 12px; background: rgba(139, 92, 246, 0.2); display: flex; align-items: center; justify-content: center; color: #a78bfa; font-size: 1.25rem;">
                        <i class="fa-solid fa-network-wired"></i>
                    </div>
                    <div>
                        <strong class="text-white d-block" style="font-size: 0.84rem;">Grafo Interactivo Vis.js</strong>
                        <small class="text-muted" style="font-size: 0.72rem;">Cálculo automático de inicio temprano (ES), tardío (LF) y holguras totales</small>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center pt-3 border-top border-secondary border-opacity-25 mt-auto">
                <span class="text-muted small" style="font-size: 0.74rem;">
                    <i class="fa-regular fa-calendar me-1"></i> Registrado: {{ $p->created_at->format('d/m/Y') }}
                </span>
                <a href="{{ route('cpm.show', $p) }}" class="btn-accent" style="background: linear-gradient(135deg, #7c3aed, #a78bfa); padding: 7px 18px; font-size: 0.80rem;">
                    <i class="fa-solid fa-diagram-project me-1"></i> Abrir Grafo CPM
                </a>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="glass-card p-5 text-center text-muted">
            <i class="fa-solid fa-diagram-project fs-1 mb-3 text-purple d-block"></i>
            <h5>No hay proyectos CPM registrados aún</h5>
            <p>Comienza creando el primer plan de ruta crítica para optimizar los tiempos de respuesta.</p>
            <a href="{{ route('cpm.create') }}" class="btn-accent mt-2">
                <i class="fa-solid fa-plus me-1"></i> Crear Primer Proyecto
            </a>
        </div>
    </div>
    @endforelse
</div>

@endsection
