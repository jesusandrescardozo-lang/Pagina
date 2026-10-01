@extends('layouts.app')

@section('title', 'Nuevo Proyecto CPM')
@section('page-title', 'Crear Proyecto de Ruta Crítica (CPM)')
@section('page-subtitle', 'Investigación de Operaciones: Planificación y optimización de la cadena logística humanitaria')

@section('content')
<!-- COCKPIT HEADER -->
<div class="cockpit-page-header">
    <div>
        <div class="d-flex align-items-center gap-2 mb-2">
            <span class="badge" style="background: rgba(230,168,108,0.15); border: 1px solid rgba(230,168,108,0.35); color: #e6a86c; font-size:0.75rem;">
                <i class="fa-solid fa-diagram-project me-1"></i> MÉTODO DE LA RUTA CRÍTICA
            </span>
            <span class="badge" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); color: rgba(255,255,255,0.75); font-size:0.72rem;">
                Investigación de Operaciones
            </span>
        </div>
        <h2 class="cockpit-header-title text-white mb-1">Modelar Nuevo Flujo de Red CPM</h2>
        <p class="cockpit-header-subtitle">Defina un proceso logístico o cadena de emergencia para calcular holguras y cuellos de botella.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('cpm.index') }}" class="btn-glass" style="font-size:0.8rem;">
            <i class="fa-solid fa-arrow-left me-1"></i> Volver a Proyectos
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="glass-card p-4 p-md-5">
            <form method="POST" action="{{ route('cpm.store') }}">
                @csrf

                <div class="section-heading text-info mb-3">
                    <i class="fa-solid fa-network-wired me-1"></i> Configuración del Flujo Operativo
                </div>

                <div class="mb-3">
                    <label class="form-label-glass">Nombre del Proyecto o Proceso Logístico *</label>
                    <input type="text" name="nombre" class="form-control-glass w-100" value="{{ old('nombre') }}" required placeholder="Ej: Cadena de Reabastecimiento de Emergencia Zona Norte">
                </div>

                <div class="mb-3">
                    <label class="form-label-glass">Tipo de Proceso Logístico *</label>
                    <select name="tipo" class="form-select-glass w-100" required>
                        <option value="reabastecimiento_emergencia" {{ old('tipo') == 'reabastecimiento_emergencia' ? 'selected' : '' }}>🚨 Reabastecimiento de Emergencia a Refugios</option>
                        <option value="distribucion_alimentos" {{ old('tipo') == 'distribucion_alimentos' ? 'selected' : '' }}>🍲 Operativo de Distribución y Cocina de Alimentos</option>
                        <option value="cadena_suministro_medica" {{ old('tipo') == 'cadena_suministro_medica' ? 'selected' : '' }}>🏥 Cadena de Suministro y Dispensación Médica</option>
                        <option value="proceso_logistico" {{ old('tipo') == 'proceso_logistico' ? 'selected' : '' }}>🚛 Transporte y Despacho en Convoy</option>
                        <option value="personalizado" {{ old('tipo') == 'personalizado' ? 'selected' : '' }}>⚡ Flujo de Red Personalizado</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="form-label-glass">Descripción y Objetivos Operativos</label>
                    <textarea name="descripcion" class="form-control-glass w-100" rows="3" placeholder="Detalle los refugios involucrados, rutas críticas estimadas y metas de tiempo...">{{ old('descripcion') }}</textarea>
                </div>

                <div class="d-flex justify-content-end gap-2 pt-3 border-top border-secondary border-opacity-25">
                    <a href="{{ route('cpm.index') }}" class="btn-glass">Cancelar</a>
                    <button type="submit" class="btn-accent px-4">
                        <i class="fa-solid fa-diagram-project me-1"></i> Crear Proyecto CPM
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection