@extends('layouts.app')

@section('title', 'Registrar Movimiento Kardex')
@section('page-title', 'Registrar Operación en Kardex')
@section('page-subtitle', 'Ingreso de donaciones, compras, consumos o bajas por merma/caducidad')

@section('content')
<!-- COCKPIT HEADER -->
<div class="cockpit-page-header">
    <div>
        <div class="d-flex align-items-center gap-2 mb-2">
            <span class="badge" style="background: rgba(14,165,233,0.15); border: 1px solid rgba(14,165,233,0.35); color: #38bdf8; font-size:0.75rem;">
                <i class="fa-solid fa-receipt me-1"></i> ASIENTO EN KARDEX
            </span>
            <span class="badge" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); color: rgba(255,255,255,0.75); font-size:0.72rem;">
                Trazabilidad FEFO Inmediata
            </span>
        </div>
        <h2 class="cockpit-header-title text-white mb-1">Registrar Operación de Inventario</h2>
        <p class="cockpit-header-subtitle">Asiente entradas por donación/compra, salidas por ración/consumo o transferencias entre refugios.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('movements.index') }}" class="btn-glass" style="font-size:0.8rem;">
            <i class="fa-solid fa-arrow-left me-1"></i> Volver a Kardex
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="glass-card p-4 p-md-5">
            <form method="POST" action="{{ route('movements.store') }}">
                @csrf

                <!-- SECCIÓN 01: DESTINO Y PRODUCTO -->
                <div class="section-heading text-info mb-3">
                    <i class="fa-solid fa-map-pin me-1"></i> 01. Refugio y Suministro Afectado
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label-glass">Refugio / Albergue Destino *</label>
                        <select name="shelter_id" class="form-select-glass" required>
                            <option value="">-- Seleccionar Refugio --</option>
                            @foreach($shelters as $sh)
                                <option value="{{ $sh->id }}" {{ (old('shelter_id', $selectedShelterId) == $sh->id) ? 'selected' : '' }}>
                                    {{ $sh->nombre }} ({{ $sh->poblacion_total }} albergados)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label-glass">Insumo o Alimento *</label>
                        <select name="supply_id" class="form-select-glass" required>
                            <option value="">-- Seleccionar Insumo --</option>
                            @foreach($supplies as $sp)
                                <option value="{{ $sp->id }}" {{ (old('supply_id', $selectedSupplyId) == $sp->id) ? 'selected' : '' }}>
                                    [{{ $sp->codigo }}] {{ $sp->nombre }} ({{ $sp->unidad_medida }} &bull; Clase {{ $sp->clasificacion_abc }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- SECCIÓN 02: TIPO Y MAGNITUD -->
                <div class="section-heading text-warning mb-3">
                    <i class="fa-solid fa-arrow-right-arrow-left me-1"></i> 02. Naturaleza y Cantidad de la Operación
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label-glass">Tipo de Movimiento *</label>
                        <select name="tipo" id="tipo_movimiento" class="form-select-glass" required onchange="toggleLoteFields()">
                            <optgroup label="Entradas (+)">
                                <option value="entrada_donacion" {{ old('tipo', $defaultTipo) == 'entrada_donacion' ? 'selected' : '' }}>🟢 Entrada: Donación Humanitaria</option>
                                <option value="entrada_compra" {{ old('tipo', $defaultTipo) == 'entrada_compra' ? 'selected' : '' }}>🟢 Entrada: Compra Institucional</option>
                                <option value="entrada_transferencia" {{ old('tipo', $defaultTipo) == 'entrada_transferencia' ? 'selected' : '' }}>🟢 Entrada: Recepción de Transferencia</option>
                            </optgroup>
                            <optgroup label="Salidas (-)">
                                <option value="salida_consumo" {{ old('tipo', $defaultTipo) == 'salida_consumo' ? 'selected' : '' }}>🔴 Salida: Consumo Diario de Población</option>
                                <option value="salida_transferencia" {{ old('tipo', $defaultTipo) == 'salida_transferencia' ? 'selected' : '' }}>🔴 Salida: Despacho a otro Refugio</option>
                                <option value="salida_merma_caducidad" {{ old('tipo', $defaultTipo) == 'salida_merma_caducidad' ? 'selected' : '' }}>🔴 Salida: Merma / Vencimiento</option>
                            </optgroup>
                            <optgroup label="Ajustes de Auditoría">
                                <option value="ajuste_inventario" {{ old('tipo', $defaultTipo) == 'ajuste_inventario' ? 'selected' : '' }}>🔵 Ajuste de Conteo Físico</option>
                            </optgroup>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label-glass">Cantidad *</label>
                        <input type="number" step="0.01" name="cantidad" value="{{ old('cantidad') }}" min="0.01" class="form-control-glass font-monospace" placeholder="Ej: 50.0" required>
                    </div>
                </div>

                <!-- SECCIÓN DE LOTES Y CADUCIDAD PARA ENTRADAS (FEFO) -->
                <div id="seccion_lote" class="p-3 rounded-3 mb-3" style="background: rgba(56,189,248,0.06); border: 1px solid rgba(56,189,248,0.25);">
                    <div class="section-heading text-info mb-2">
                        <i class="fa-solid fa-boxes-stacked me-1"></i> Control de Lote y Vencimiento (Regla FEFO)
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label-glass">Número de Lote</label>
                            <input type="text" name="numero_lote" value="{{ old('numero_lote') }}" class="form-control-glass font-monospace" placeholder="Ej: LOT-2026-A10 (Dejar vacío para autogenerar)">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-glass">Fecha de Caducidad / Vencimiento</label>
                            <input type="date" name="fecha_vencimiento" value="{{ old('fecha_vencimiento') }}" class="form-control-glass">
                        </div>
                    </div>
                    <small class="text-muted d-block mt-2" style="font-size:0.75rem;">
                        <i class="fa-solid fa-circle-info me-1 text-info"></i> El sistema ordenará las salidas automáticas priorizando el lote con fecha más próxima (First Expired, First Out).
                    </small>
                </div>

                <!-- SECCIÓN 03: SOPORTE Y JUSTIFICACIÓN -->
                <div class="section-heading text-primary mb-3">
                    <i class="fa-solid fa-file-invoice me-1"></i> 03. Soporte Documental y Justificación
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label-glass">Documento de Referencia / Boleta</label>
                        <input type="text" name="documento_referencia" value="{{ old('documento_referencia') }}" class="form-control-glass font-monospace" placeholder="Ej: REMISION-402, ACTA-CONRED, GUIA-902">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-glass">Motivo o Justificación Operativa</label>
                        <input type="text" name="motivo" value="{{ old('motivo') }}" class="form-control-glass" placeholder="Ej: Ración matutina, Donación recibida, Despacho urgente">
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 pt-3 border-top border-secondary border-opacity-25">
                    <a href="{{ route('movements.index') }}" class="btn-glass">Cancelar</a>
                    <button type="submit" class="btn-accent px-4">
                        <i class="fa-solid fa-check-circle me-1"></i> Asentar Movimiento en Kardex
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleLoteFields() {
    const tipo = document.getElementById('tipo_movimiento').value;
    const seccion = document.getElementById('seccion_lote');
    if (tipo.startsWith('entrada')) {
        seccion.style.display = 'block';
    } else {
        seccion.style.display = 'none';
    }
}
document.addEventListener('DOMContentLoaded', toggleLoteFields);
</script>
@endpush
