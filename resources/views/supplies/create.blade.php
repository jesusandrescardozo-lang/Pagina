@extends('layouts.app')

@section('title', 'Nuevo Insumo - Parámetros I.O.')
@section('page-title', 'Registrar Nuevo Insumo / Alimento')
@section('page-subtitle', 'Definición de parámetros logísticos y cuantitativos de Investigación de Operaciones')

@section('content')
<!-- COCKPIT HEADER -->
<div class="cockpit-page-header">
    <div>
        <div class="d-flex align-items-center gap-2 mb-2">
            <span class="badge" style="background: rgba(230,168,108,0.15); border: 1px solid rgba(230,168,108,0.35); color: var(--amber); font-size:0.75rem;">
                <i class="fa-solid fa-boxes-stacked me-1"></i> CATÁLOGO MAESTRO
            </span>
            <span class="badge" style="background: var(--surface-quiet); border: 1px solid var(--quiet-border); color: var(--text-muted); font-size:0.72rem;">
                Modelos Cuantitativos I.O.
            </span>
        </div>
        <h2 class="cockpit-header-title text-white mb-1">Registrar Nuevo Insumo Logístico</h2>
        <p class="cockpit-header-subtitle">Configure los costos de orden (S), mantenimiento (H), lead time (L) y parámetros probabilísticos Newsvendor.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('supplies.index') }}" class="btn-glass" style="font-size:0.8rem;">
            <i class="fa-solid fa-arrow-left me-1"></i> Volver al Catálogo
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="glass-card p-4 p-md-5">
            <form method="POST" action="{{ route('supplies.store') }}">
                @csrf

                <!-- SECCIÓN 01: DATOS BÁSICOS -->
                <div class="section-heading text-info mb-3">
                    <i class="fa-solid fa-box-open me-1"></i> 01. Ficha Técnica y Clasificación de Impacto
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="form-label-glass">Código / SKU *</label>
                        <input type="text" name="codigo" value="{{ old('codigo', 'INS-' . strtoupper(substr(uniqid(), -4))) }}" class="form-control-glass font-monospace" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-glass">Nombre del Insumo *</label>
                        <input type="text" name="nombre" value="{{ old('nombre') }}" class="form-control-glass" placeholder="Ej: Harina de Maíz Precocida, Suero Oral..." required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label-glass">Categoría *</label>
                        <select name="categoria" class="form-select-glass" required>
                            <option value="alimento" {{ old('categoria') == 'alimento' ? 'selected' : '' }}>🥣 Alimento / Nutrición</option>
                            <option value="medico" {{ old('categoria') == 'medico' ? 'selected' : '' }}>💊 Insumo Médico / Fármaco</option>
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label-glass">Subcategoría *</label>
                        <input type="text" name="subcategoria" value="{{ old('subcategoria') }}" class="form-control-glass" placeholder="Ej: Granos Básicos, Antibióticos..." required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-glass">Unidad de Medida *</label>
                        <input type="text" name="unidad_medida" value="{{ old('unidad_medida', 'unidades') }}" class="form-control-glass" placeholder="kg, litros, raciones, cajas, frascos..." required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-glass">Clasificación ABC *</label>
                        <select name="clasificacion_abc" class="form-select-glass" required>
                            <option value="A" {{ old('clasificacion_abc') == 'A' ? 'selected' : '' }}>Clase A (Crítico / Alto Impacto Vital)</option>
                            <option value="B" {{ old('clasificacion_abc', 'B') == 'B' ? 'selected' : '' }}>Clase B (Moderado / Estándar)</option>
                            <option value="C" {{ old('clasificacion_abc') == 'C' ? 'selected' : '' }}>Clase C (Bajo Costo / Alto Volumen)</option>
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-4 align-items-center">
                    <div class="col-md-7">
                        <div class="p-3 rounded-3" style="background: var(--surface-quiet); border: 1px solid var(--quiet-border);">
                            <div class="form-check m-0">
                                <input class="form-check-input" type="checkbox" name="es_perecedero" id="es_perecedero" value="1" {{ old('es_perecedero') ? 'checked' : '' }} style="accent-color: var(--accent);">
                                <label class="form-check-label text-white fw-bold" for="es_perecedero" style="font-size:0.85rem;">
                                    Insumo Perecedero con control estricto de Caducidad (Regla FEFO)
                                </label>
                                <small class="text-muted d-block" style="font-size:0.75rem;">Requiere número de lote y fecha de vencimiento obligatoria al ingresar donaciones o compras.</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label-glass">Vida Útil Estimada (Días)</label>
                        <input type="number" name="dias_vida_util_promedio" value="{{ old('dias_vida_util_promedio') }}" min="1" class="form-control-glass font-monospace" placeholder="Ej: 180">
                    </div>
                </div>

                <!-- SECCIÓN 02: PARÁMETROS EOQ Y ROP -->
                <div class="section-heading text-info mb-3">
                    <i class="fa-solid fa-square-root-variable me-1"></i> 02. Parámetros Cuantitativos EOQ (Wilson) y ROP
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label-glass">Costo Unitario de Compra (C) *</label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark text-muted border-secondary">$</span>
                            <input type="number" step="0.01" name="costo_unitario" value="{{ old('costo_unitario', 5.00) }}" min="0.01" class="form-control-glass font-monospace" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-glass">Costo de Pedido / Emisión (S) *</label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark text-muted border-secondary">$</span>
                            <input type="number" step="0.01" name="costo_pedido" value="{{ old('costo_pedido', 20.00) }}" min="0.01" class="form-control-glass font-monospace" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-glass">Costo Almacén / Año (H) *</label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark text-muted border-secondary">$</span>
                            <input type="number" step="0.01" name="costo_mantenimiento_anual" value="{{ old('costo_mantenimiento_anual', 1.50) }}" min="0.01" class="form-control-glass font-monospace" required>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label-glass">Lead Time / Entrega (L en días) *</label>
                        <input type="number" step="0.1" name="tiempo_entrega_dias" value="{{ old('tiempo_entrega_dias', 3.0) }}" min="0.1" class="form-control-glass font-monospace" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-glass">Consumo Referencia Persona / Día *</label>
                        <input type="number" step="0.001" name="consumo_diario_referencia" value="{{ old('consumo_diario_referencia', 0.25) }}" min="0.001" class="form-control-glass font-monospace" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-glass">Stock Seguridad Sugerido (SS) *</label>
                        <input type="number" step="0.1" name="stock_seguridad_sugerido" value="{{ old('stock_seguridad_sugerido', 50.0) }}" min="0" class="form-control-glass font-monospace" required>
                    </div>
                </div>

                <!-- SECCIÓN 03: NEWSVENDOR -->
                <div class="section-heading text-warning mb-3">
                    <i class="fa-solid fa-newspaper me-1"></i> 03. Parámetros Newsvendor (Período Único con Riesgo de Vencimiento)
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label-glass">Costo de Escasez / Subestimación (Cu)</label>
                        <input type="number" step="0.01" name="costo_escasez_newsvendor" value="{{ old('costo_escasez_newsvendor') }}" min="0.01" class="form-control-glass font-monospace" placeholder="Costo humanitario por desabastecimiento ($)">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-glass">Costo de Desecho / Sobreestimación (Co)</label>
                        <input type="number" step="0.01" name="costo_desecho_newsvendor" value="{{ old('costo_desecho_newsvendor') }}" min="0.01" class="form-control-glass font-monospace" placeholder="Pérdida por descomposición/caducidad ($)">
                    </div>
                </div>

                <!-- SECCIÓN 04: OBSERVACIONES -->
                <div class="mb-4">
                    <label class="form-label-glass">Especificaciones Bromatológicas o Farmacológicas</label>
                    <textarea name="descripcion" rows="3" class="form-control-glass" placeholder="Instrucciones de almacenamiento, temperatura requerida, contraindicaciones...">{{ old('descripcion') }}</textarea>
                </div>

                <div class="d-flex justify-content-end gap-2 pt-3 border-top border-secondary border-opacity-25">
                    <a href="{{ route('supplies.index') }}" class="btn-glass">Cancelar</a>
                    <button type="submit" class="btn-accent px-4">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Registrar Insumo en Catálogo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
