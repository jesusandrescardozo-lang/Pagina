@extends('layouts.app')

@section('title', 'Editar Refugio ' . $shelter->nombre)
@section('page-title', 'Actualizar Datos del Refugio')
@section('page-subtitle', 'Modificar censo de albergados y parámetros operativos de ' . $shelter->codigo)

@section('content')
<!-- COCKPIT HEADER -->
<div class="cockpit-page-header">
    <div>
        <div class="d-flex align-items-center gap-2 mb-2">
            <span class="badge" style="background: rgba(230,168,108,0.15); border: 1px solid rgba(230,168,108,0.35); color: var(--amber); font-size:0.75rem; font-family:'JetBrains Mono',monospace;">
                {{ $shelter->codigo }}
            </span>
            <span class="badge {{ $shelter->estado_badge['class'] }}" style="font-size:0.75rem;">
                {{ $shelter->estado_badge['text'] }}
            </span>
        </div>
        <h2 class="cockpit-header-title text-white mb-1">Modificar Datos de {{ $shelter->nombre }}</h2>
        <p class="cockpit-header-subtitle">Actualice el censo demográfico en tiempo real o modifique la capacidad física asignada.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('shelters.show', $shelter) }}" class="btn-glass" style="font-size:0.8rem;">
            <i class="fa-solid fa-eye me-1"></i> Ver Refugio
        </a>
        <a href="{{ route('shelters.index') }}" class="btn-glass" style="font-size:0.8rem;">
            <i class="fa-solid fa-arrow-left me-1"></i> Volver a Lista
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="glass-card p-4 p-md-5">
            <form method="POST" action="{{ route('shelters.update', $shelter) }}">
                @csrf
                @method('PUT')

                <!-- SECCIÓN 01: IDENTIFICACIÓN -->
                <div class="section-heading text-info mb-3">
                    <i class="fa-solid fa-map-location-dot me-1"></i> 01. Identificación y Localización
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label-glass">Código Único *</label>
                        <input type="text" name="codigo" value="{{ old('codigo', $shelter->codigo) }}" class="form-control-glass font-monospace" required>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label-glass">Nombre del Refugio *</label>
                        <input type="text" name="nombre" value="{{ old('nombre', $shelter->nombre) }}" class="form-control-glass" required>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label-glass">Estado / Departamento *</label>
                        <input type="text" name="departamento" value="{{ old('departamento', $shelter->departamento) }}" class="form-control-glass" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-glass">Municipio / Parroquia *</label>
                        <input type="text" name="municipio" value="{{ old('municipio', $shelter->municipio) }}" class="form-control-glass" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label-glass">Dirección Física *</label>
                    <input type="text" name="direccion" value="{{ old('direccion', $shelter->direccion) }}" class="form-control-glass" required>
                </div>

                <!-- SECCIÓN 02: CAPACIDAD Y ESTATUS -->
                <div class="section-heading text-warning mb-3">
                    <i class="fa-solid fa-gauge-high me-1"></i> 02. Capacidad y Estatus Operativo
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label-glass">Capacidad Máxima (personas) *</label>
                        <input type="number" name="capacidad_maxima" value="{{ old('capacidad_maxima', $shelter->capacidad_maxima) }}" min="1" class="form-control-glass font-monospace" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-glass">Estado Operativo *</label>
                        <select name="estado" class="form-select-glass" required>
                            <option value="activo" {{ old('estado', $shelter->estado) == 'activo' ? 'selected' : '' }}>🟢 Activo Operativo</option>
                            <option value="alerta_capacidad" {{ old('estado', $shelter->estado) == 'alerta_capacidad' ? 'selected' : '' }}>🟡 Alerta de Capacidad</option>
                            <option value="evacuacion" {{ old('estado', $shelter->estado) == 'evacuacion' ? 'selected' : '' }}>🟠 En Evacuación</option>
                            <option value="cerrado" {{ old('estado', $shelter->estado) == 'cerrado' ? 'selected' : '' }}>⚪ Cerrado</option>
                        </select>
                    </div>
                </div>

                <!-- SECCIÓN 03: CENSO POBLACIONAL -->
                <div class="section-heading text-success mb-3">
                    <i class="fa-solid fa-users me-1"></i> 03. Actualización de Población Albergada (Censo)
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label-glass">Adultos</label>
                        <input type="number" name="poblacion_adultos" value="{{ old('poblacion_adultos', $shelter->poblacion_adultos) }}" min="0" class="form-control-glass font-monospace">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label-glass">Niños / Lactantes</label>
                        <input type="number" name="poblacion_ninos" value="{{ old('poblacion_ninos', $shelter->poblacion_ninos) }}" min="0" class="form-control-glass font-monospace text-info">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label-glass">Adultos Mayores</label>
                        <input type="number" name="poblacion_adultos_mayores" value="{{ old('poblacion_adultos_mayores', $shelter->poblacion_adultos_mayores) }}" min="0" class="form-control-glass font-monospace text-warning">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label-glass">Atención Médica</label>
                        <input type="number" name="poblacion_atencion_medica" value="{{ old('poblacion_atencion_medica', $shelter->poblacion_atencion_medica) }}" min="0" class="form-control-glass font-monospace text-danger">
                    </div>
                </div>

                <!-- SECCIÓN 04: CONTACTO -->
                <div class="section-heading text-primary mb-3">
                    <i class="fa-solid fa-id-badge me-1"></i> 04. Responsable de Albergue
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label-glass">Nombre del Responsable *</label>
                        <input type="text" name="responsable_nombre" value="{{ old('responsable_nombre', $shelter->responsable_nombre) }}" class="form-control-glass" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-glass">Teléfono / Radio *</label>
                        <input type="text" name="responsable_telefono" value="{{ old('responsable_telefono', $shelter->responsable_telefono) }}" class="form-control-glass" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-glass">Correo Electrónico</label>
                        <input type="email" name="responsable_email" value="{{ old('responsable_email', $shelter->responsable_email) }}" class="form-control-glass">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label-glass">Observaciones Generales</label>
                    <textarea name="observaciones" rows="3" class="form-control-glass">{{ old('observaciones', $shelter->observaciones) }}</textarea>
                </div>

                <div class="d-flex justify-content-end gap-2 pt-3 border-top border-secondary border-opacity-25">
                    <a href="{{ route('shelters.show', $shelter) }}" class="btn-glass">Cancelar</a>
                    <button type="submit" class="btn-accent px-4">
                        <i class="fa-solid fa-save me-1"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
