@extends('layouts.app')

@section('title', 'Registrar Refugio')
@section('page-title', 'Registrar Nuevo Refugio / Albergue')
@section('page-subtitle', 'Alta de albergue temporal o centro de recepción humanitario')

@section('content')
<!-- COCKPIT HEADER -->
<div class="cockpit-page-header">
    <div>
        <div class="d-flex align-items-center gap-2 mb-2">
            <span class="badge" style="background: rgba(14,165,233,0.15); border: 1px solid rgba(14,165,233,0.35); color: #38bdf8; font-size:0.75rem;">
                <i class="fa-solid fa-plus-circle me-1"></i> ALTA OPERATIVA
            </span>
            <span class="badge" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); color: rgba(255,255,255,0.75); font-size:0.72rem;">
                Red Nacional de Albergues
            </span>
        </div>
        <h2 class="cockpit-header-title text-white mb-1">Registrar Nuevo Albergue Temporal</h2>
        <p class="cockpit-header-subtitle">Ingrese los parámetros de capacidad física, demografía censada y logística territorial.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('shelters.index') }}" class="btn-glass" style="font-size:0.8rem;">
            <i class="fa-solid fa-arrow-left me-1"></i> Volver a Refugios
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="glass-card p-4 p-md-5">
            <form method="POST" action="{{ route('shelters.store') }}">
                @csrf

                <!-- SECCIÓN 01: IDENTIFICACIÓN -->
                <div class="section-heading text-info mb-3">
                    <i class="fa-solid fa-map-location-dot me-1"></i> 01. Identificación y Localización Geográfica
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label-glass">Código Único de Identificación *</label>
                        <input type="text" name="codigo" value="{{ old('codigo', 'REF-' . strtoupper(substr(uniqid(), -4))) }}" class="form-control-glass font-monospace" required>
                        <small class="text-muted" style="font-size:0.72rem;">Identificador para Kardex y rutas</small>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label-glass">Nombre del Refugio / Albergue *</label>
                        <input type="text" name="nombre" value="{{ old('nombre') }}" class="form-control-glass" placeholder="Ej: Refugio Polideportivo Norte" required>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label-glass">Estado / Departamento *</label>
                        <input type="text" name="departamento" value="{{ old('departamento', 'Distrito Capital') }}" class="form-control-glass" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-glass">Municipio / Parroquia *</label>
                        <input type="text" name="municipio" value="{{ old('municipio', 'Libertador') }}" class="form-control-glass" placeholder="Ej: Libertador (El Paraíso)" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label-glass">Dirección Física Completa *</label>
                    <input type="text" name="direccion" value="{{ old('direccion') }}" class="form-control-glass" placeholder="Calle, avenida, sector o puntos de referencia geográfica..." required>
                </div>

                <!-- SECCIÓN 02: CAPACIDAD Y ESTATUS -->
                <div class="section-heading text-warning mb-3">
                    <i class="fa-solid fa-gauge-high me-1"></i> 02. Capacidad y Estatus Operativo
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label-glass">Capacidad Máxima de Plazas (personas) *</label>
                        <input type="number" name="capacidad_maxima" value="{{ old('capacidad_maxima', 100) }}" min="1" class="form-control-glass font-monospace" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-glass">Estado Operativo Inicial *</label>
                        <select name="estado" class="form-select-glass" required>
                            <option value="activo" {{ old('estado') == 'activo' ? 'selected' : '' }}>🟢 Activo y Recibiendo</option>
                            <option value="alerta_capacidad" {{ old('estado') == 'alerta_capacidad' ? 'selected' : '' }}>🟡 Alerta de Capacidad Próxima</option>
                            <option value="evacuacion" {{ old('estado') == 'evacuacion' ? 'selected' : '' }}>🟠 En Evacuación / Contingencia</option>
                            <option value="cerrado" {{ old('estado') == 'cerrado' ? 'selected' : '' }}>⚪ Cerrado Temporalmente</option>
                        </select>
                    </div>
                </div>

                <!-- SECCIÓN 03: CENSO DEMOGRÁFICO -->
                <div class="section-heading text-success mb-3">
                    <i class="fa-solid fa-users-between-lines me-1"></i> 03. Censo Poblacional Inicial
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label-glass">Adultos</label>
                        <input type="number" name="poblacion_adultos" value="{{ old('poblacion_adultos', 0) }}" min="0" class="form-control-glass font-monospace">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label-glass">Niños / Lactantes</label>
                        <input type="number" name="poblacion_ninos" value="{{ old('poblacion_ninos', 0) }}" min="0" class="form-control-glass font-monospace text-info">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label-glass">Adultos Mayores</label>
                        <input type="number" name="poblacion_adultos_mayores" value="{{ old('poblacion_adultos_mayores', 0) }}" min="0" class="form-control-glass font-monospace text-warning">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label-glass">Atención Médica</label>
                        <input type="number" name="poblacion_atencion_medica" value="{{ old('poblacion_atencion_medica', 0) }}" min="0" class="form-control-glass font-monospace text-danger" title="Personas con requerimiento médico prioritario">
                    </div>
                </div>

                <!-- SECCIÓN 04: CONTACTO Y RESPONSABLE -->
                <div class="section-heading text-primary mb-3">
                    <i class="fa-solid fa-id-badge me-1"></i> 04. Coordinador / Responsable en Terreno
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label-glass">Nombre del Responsable *</label>
                        <input type="text" name="responsable_nombre" value="{{ old('responsable_nombre') }}" class="form-control-glass" placeholder="Ej: Dr. Carlos Méndez" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-glass">Teléfono de Contacto / Radio *</label>
                        <input type="text" name="responsable_telefono" value="{{ old('responsable_telefono') }}" class="form-control-glass" placeholder="+58 412 000-0000" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-glass">Correo Electrónico Institucional</label>
                        <input type="email" name="responsable_email" value="{{ old('responsable_email') }}" class="form-control-glass" placeholder="coordinador@albergue.org">
                    </div>
                </div>

                <!-- SECCIÓN 05: OBSERVACIONES -->
                <div class="mb-4">
                    <label class="form-label-glass">Observaciones Operativas y Equipamiento</label>
                    <textarea name="observaciones" rows="3" class="form-control-glass" placeholder="Detalles de cocina comunitaria, punto de primeros auxilios, acceso vehicular...">{{ old('observaciones') }}</textarea>
                </div>

                <div class="d-flex justify-content-end gap-2 pt-3 border-top border-secondary border-opacity-25">
                    <a href="{{ route('shelters.index') }}" class="btn-glass">Cancelar</a>
                    <button type="submit" class="btn-accent px-4">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Guardar y Habilitar Refugio
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
