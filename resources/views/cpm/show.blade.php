@extends('layouts.app')
@section('title', 'Red CPM - ' . $project->nombre)
@section('page-title', 'Método de la Ruta Crítica (CPM)')
@section('page-subtitle', 'Optimización de tiempos de atención y evaluación de ruta crítica')

@section('content')
<!-- COCKPIT HEADER -->
<div class="cockpit-page-header">
    <div>
        <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
            <span class="badge" style="background: rgba(244,63,94,0.15); border: 1px solid rgba(244,63,94,0.35); color: #fb7185; font-size:0.75rem;">
                <i class="fa-solid fa-diagram-project me-1"></i> PROYECTO CPM
            </span>
            <span class="badge" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); color: rgba(255,255,255,0.75); font-size:0.72rem;">
                Investigación de Operaciones
            </span>
        </div>
        <h2 class="cockpit-header-title text-white mb-1">{{ $project->nombre }}</h2>
        <p class="cockpit-header-subtitle">{{ $project->descripcion ?? 'Planificación de ruta crítica sin demoras admisibles.' }}</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <button type="button" class="btn-accent" data-bs-toggle="modal" data-bs-target="#modalActivity" style="font-size:0.8rem;">
            <i class="fa-solid fa-plus me-1"></i> Agregar Actividad
        </button>
        <a href="{{ route('cpm.index') }}" class="btn-glass" style="font-size:0.8rem;">
            <i class="fa-solid fa-arrow-left me-1"></i> Volver a Proyectos
        </a>
    </div>
</div>

<!-- RESUMEN DE MÉTRICAS CPM -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card c-red">
            <div class="stat-label"><i class="fa-solid fa-stopwatch me-1 text-danger"></i> DURACIÓN MÍNIMA TOTAL</div>
            <div class="stat-value font-monospace" style="color:#fb7185;">
                {{ $duracionProyecto }} <small style="font-size:0.85rem; color:var(--text-muted); font-weight:500;">unidades</small>
            </div>
            <small class="text-muted" style="font-size:0.75rem;">Tiempo del camino más largo en la red</small>
        </div>
    </div>
    <div class="col-md-8">
        <div class="stat-card c-violet">
            <div class="stat-label"><i class="fa-solid fa-fire me-1 text-danger"></i> ACTIVIDADES EN LA RUTA CRÍTICA (HOLGURA = 0)</div>
            <div class="d-flex flex-wrap gap-2 mt-2">
                @forelse($rutaCritica as $rc)
                    <span class="badge" style="background: rgba(244,63,94,0.2); border: 1px solid rgba(244,63,94,0.5); color: #fda4af; font-size:0.85rem; font-weight:700; padding:5px 14px; font-family:'JetBrains Mono',monospace;">
                        <i class="fa-solid fa-bolt me-1"></i> {{ $rc }}
                    </span>
                @empty
                    <span style="color:var(--text-muted); font-size:0.85rem;">No hay actividades registradas en la red aún.</span>
                @endforelse
            </div>
            <small class="text-muted d-block mt-2" style="font-size:0.72rem;">Cualquier retraso en estas tareas demorará la entrega humanitaria completa.</small>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- TABLA DE ACTIVIDADES Y TIEMPOS CPM -->
    <div class="col-lg-6">
        <div class="glass-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="section-heading mb-0 text-info" style="font-size:0.9rem;">
                    <i class="fa-solid fa-table-list me-2"></i> Matriz de Cálculos CPM
                </div>
                <small class="badge" style="background: rgba(255,255,255,0.06); color: rgba(255,255,255,0.7); font-size:0.7rem;">
                    {{ count($activities) }} Actividades
                </small>
            </div>
            <div class="table-responsive" style="max-height: 520px; overflow-y: auto;">
                <table class="dark-table">
                    <thead>
                        <tr>
                            <th>Cód</th>
                            <th>Actividad</th>
                            <th>Dur</th>
                            <th>Pred</th>
                            <th>ES</th>
                            <th>EF</th>
                            <th>LS</th>
                            <th>LF</th>
                            <th>Holg</th>
                            <th>Crítica</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activities as $act)
                            <tr style="{{ $act->es_critica ? 'background:rgba(244,63,94,0.09);' : '' }}">
                                <td style="color:#38bdf8; font-family:'JetBrains Mono',monospace; font-weight:700;">{{ $act->codigo }}</td>
                                <td><strong class="text-white">{{ $act->nombre }}</strong></td>
                                <td class="font-monospace">{{ $act->duracion }}</td>
                                <td style="color:var(--text-muted); font-family:'JetBrains Mono',monospace;">{{ is_array($act->predecesores) ? implode(',', $act->predecesores) : '-' }}</td>
                                <td class="font-monospace text-muted">{{ $act->es }}</td>
                                <td class="font-monospace text-white">{{ $act->ef }}</td>
                                <td class="font-monospace text-muted">{{ $act->ls }}</td>
                                <td class="font-monospace text-white">{{ $act->lf }}</td>
                                <td class="font-monospace fw-bold {{ $act->holgura == 0 ? 'text-danger' : 'text-success' }}">{{ $act->holgura }}</td>
                                <td>
                                    @if($act->es_critica)
                                        <span class="badge" style="background: rgba(244,63,94,0.2); color: #fb7185; border: 1px solid rgba(244,63,94,0.4); font-size:0.68rem;">SÍ</span>
                                    @else
                                        <span class="badge" style="background: rgba(16,185,129,0.15); color: #34d399; border: 1px solid rgba(16,185,129,0.3); font-size:0.68rem;">NO</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4 text-muted">No hay actividades definidas en este proyecto.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- GRAFO INTERACTIVO VIS.JS -->
    <div class="col-lg-6">
        <div class="glass-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div class="section-heading mb-0" style="color:#38bdf8; font-size:0.9rem;">
                    <i class="fa-solid fa-project-diagram me-2"></i> Diagrama Topológico de Red (Vis.js)
                </div>
                <div class="d-flex gap-2">
                    <span class="badge" style="background: rgba(244,63,94,0.2); border: 1px solid rgba(244,63,94,0.4); color: #fda4af; font-size:0.7rem;">
                        <i class="fa-solid fa-circle me-1" style="font-size:0.5rem;"></i> Ruta Crítica
                    </span>
                    <span class="badge" style="background: rgba(124,58,237,0.2); border: 1px solid rgba(124,58,237,0.4); color: #c4b5fd; font-size:0.7rem;">
                        <i class="fa-solid fa-circle me-1" style="font-size:0.5rem;"></i> Actividad Normal
                    </span>
                </div>
            </div>
            <div id="cpmNetwork" style="height: 480px; border: 1px solid rgba(255,255,255,0.1); border-radius: 16px; background: rgba(9, 13, 22, 0.75);"></div>
        </div>
    </div>
</div>

<!-- MODAL AGREGAR ACTIVIDAD -->
<div class="modal fade" id="modalActivity" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('cpm.activities.store', $project) }}" class="modal-content glass-card" style="background:#0c121e; border: 1px solid rgba(255,255,255,0.15); box-shadow: 0 25px 60px rgba(0,0,0,0.8);">
            @csrf
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-white"><i class="fa-solid fa-plus-circle me-2 text-info"></i> Agregar Actividad a la Red</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label-glass">Código Identificador (Ej: A, B, C, D...)</label>
                    <input type="text" name="codigo" class="form-control-glass w-100 font-monospace" required placeholder="Ej: F">
                </div>
                <div class="mb-3">
                    <label class="form-label-glass">Nombre / Descripción de la Tarea</label>
                    <input type="text" name="nombre" class="form-control-glass w-100" required placeholder="Ej: Triaje y Clasificación Secundaria">
                </div>
                <div class="mb-3">
                    <label class="form-label-glass">Duración Estimada (unidades)</label>
                    <input type="number" name="duracion" class="form-control-glass w-100 font-monospace" min="0" required value="10">
                </div>
                <div class="mb-3">
                    <label class="form-label-glass">Predecesores Inmediatos</label>
                    <div class="p-3 rounded-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); max-height: 150px; overflow-y: auto;">
                        <div class="d-flex flex-wrap gap-3">
                            @foreach($activities as $act)
                                <div class="form-check m-0">
                                    <input class="form-check-input" type="checkbox" name="predecesores[]" value="{{ $act->codigo }}" id="pred_{{ $act->id }}" style="accent-color: #38bdf8;">
                                    <label class="form-check-label small text-white" for="pred_{{ $act->id }}">
                                        <strong>{{ $act->codigo }}</strong> ({{ $act->nombre }})
                                    </label>
                                </div>
                            @endforeach
                            @if($activities->isEmpty())
                                <span class="text-muted small">Primera actividad (sin predecesores).</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn-glass" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn-accent">Guardar y Recalcular Ruta</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const container = document.getElementById('cpmNetwork');
        const rawGraph = @json($graphData);

        // Customize nodes style to fit dark futuristic theme
        const styledNodes = (rawGraph.nodes || []).map(n => {
            const isCrit = n.color === '#ef4444' || n.color === 'red';
            return {
                ...n,
                shape: 'box',
                margin: 10,
                color: {
                    background: isCrit ? '#f43f5e' : '#7c3aed',
                    border: isCrit ? '#fb7185' : '#a78bfa',
                    highlight: {
                        background: isCrit ? '#fb7185' : '#9f5cff',
                        border: '#ffffff'
                    }
                },
                font: { color: '#ffffff', face: 'Inter', size: 14, bold: true }
            };
        });

        const styledEdges = (rawGraph.edges || []).map(e => {
            const isCrit = e.color === '#ef4444' || e.color === 'red';
            return {
                ...e,
                color: isCrit ? '#f43f5e' : '#64748b',
                width: isCrit ? 3 : 1.5,
                arrows: { to: { enabled: true, scaleFactor: 0.8 } }
            };
        });

        const data = {
            nodes: new vis.DataSet(styledNodes),
            edges: new vis.DataSet(styledEdges)
        };

        const options = {
            layout: {
                hierarchical: {
                    direction: "LR",
                    sortMethod: "directed",
                    levelSeparation: 170,
                    nodeSpacing: 110
                }
            },
            physics: false,
            interaction: {
                dragNodes: true,
                zoomView: true,
                hover: true
            }
        };

        new vis.Network(container, data, options);
    });
</script>
@endpush
@endsection
