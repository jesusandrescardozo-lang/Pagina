@extends('layouts.app')

@section('title', 'Solicitar Transferencia Inter-Refugios')
@section('page-title', 'Emitir Solicitud de Transferencia de Insumos')
@section('page-subtitle', 'Movilización y balanceo de suministros humanitarios entre albergues')

@section('content')
<!-- COCKPIT HEADER -->
<div class="cockpit-page-header">
    <div>
        <div class="d-flex align-items-center gap-2 mb-2">
            <span class="badge" style="background: rgba(14,165,233,0.15); border: 1px solid rgba(14,165,233,0.35); color: #38bdf8; font-size:0.75rem;">
                <i class="fa-solid fa-route me-1"></i> LOGÍSTICA DE REBALANCEO
            </span>
            <span class="badge" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); color: rgba(255,255,255,0.75); font-size:0.72rem;">
                Inter-Albergues Red
            </span>
        </div>
        <h2 class="cockpit-header-title text-white mb-1">Emitir Orden de Transferencia y Despacho</h2>
        <p class="cockpit-header-subtitle">Asigne un refugio emisor con superávit de stock y dirija el convoy al refugio con menor cobertura.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('transfers.index') }}" class="btn-glass" style="font-size:0.8rem;">
            <i class="fa-solid fa-arrow-left me-1"></i> Volver a Transferencias
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="glass-card p-4 p-md-5">
            <!-- RUTA VISUAL INTER-ALBERGUES -->
            <div class="p-3 rounded-4 mb-4" style="background: rgba(56, 189, 248, 0.05); border: 1px dashed rgba(56, 189, 248, 0.3);">
                <div class="d-flex align-items-center justify-content-around text-center flex-wrap gap-3">
                    <div>
                        <span class="text-muted d-block" style="font-size:0.7rem; text-transform:uppercase;">Punto de Origen</span>
                        <strong class="text-white" style="font-size:0.9rem;"><i class="fa-solid fa-warehouse me-1 text-warning"></i> Albergue Emisor</strong>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div style="height: 2px; width: 60px; background: linear-gradient(90deg, #f59e0b, #0ea5e9);"></div>
                        <span class="badge bg-dark border border-info text-info p-2"><i class="fa-solid fa-truck-fast"></i></span>
                        <div style="height: 2px; width: 60px; background: linear-gradient(90deg, #0ea5e9, #10b981);"></div>
                    </div>
                    <div>
                        <span class="text-muted d-block" style="font-size:0.7rem; text-transform:uppercase;">Punto de Destino</span>
                        <strong class="text-white" style="font-size:0.9rem;"><i class="fa-solid fa-tents me-1 text-success"></i> Albergue Receptor</strong>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('transfers.store') }}">
                @csrf

                <!-- SECCIÓN 01: PUNTOS LOGÍSTICOS -->
                <div class="section-heading text-info mb-3">
                    <i class="fa-solid fa-arrows-split-up-and-left me-1"></i> 01. Nodos de Transferencia
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label-glass">Refugio Origen (Despachador) *</label>
                        <select name="refugio_origen_id" class="form-select-glass" required>
                            <option value="">-- Seleccionar Refugio Origen --</option>
                            @foreach($shelters as $sh)
                                <option value="{{ $sh->id }}" {{ old('refugio_origen_id', $origenId) == $sh->id ? 'selected' : '' }}>
                                    {{ $sh->nombre }} ({{ $sh->municipio }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label-glass">Refugio Destino (Receptor) *</label>
                        <select name="refugio_destino_id" class="form-select-glass" required>
                            <option value="">-- Seleccionar Refugio Destino --</option>
                            @foreach($shelters as $sh)
                                <option value="{{ $sh->id }}" {{ old('refugio_destino_id', $destinoId) == $sh->id ? 'selected' : '' }}>
                                    {{ $sh->nombre }} ({{ $sh->municipio }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- SECCIÓN 02: INSUMO Y CARGA -->
                <div class="section-heading text-warning mb-3">
                    <i class="fa-solid fa-boxes-packing me-1"></i> 02. Insumo, Cantidad y Prioridad de Envío
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label-glass">Insumo o Alimento a Transferir *</label>
                        <select name="supply_id" class="form-select-glass" required>
                            <option value="">-- Seleccionar Insumo --</option>
                            @foreach($supplies as $sp)
                                <option value="{{ $sp->id }}" {{ old('supply_id', $supplyId) == $sp->id ? 'selected' : '' }}>
                                    [{{ $sp->codigo }}] {{ $sp->nombre }} ({{ $sp->unidad_medida }} &bull; Clase {{ $sp->clasificacion_abc }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label-glass">Cantidad a Enviar *</label>
                        <input type="number" step="0.1" name="cantidad" value="{{ old('cantidad', $cantidad) }}" min="0.1" class="form-control-glass font-monospace" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label-glass">Nivel de Prioridad *</label>
                        <select name="prioridad" class="form-select-glass" required>
                            <option value="urgente" {{ old('prioridad', $prioridad) == 'urgente' ? 'selected' : '' }}>🚨 URGENTE (&lt; 24h)</option>
                            <option value="alta" {{ old('prioridad', $prioridad) == 'alta' ? 'selected' : '' }}>🟠 Alta (48h)</option>
                            <option value="media" {{ old('prioridad', $prioridad) == 'media' ? 'selected' : '' }}>🟡 Media</option>
                            <option value="baja" {{ old('prioridad', $prioridad) == 'baja' ? 'selected' : '' }}>🟢 Baja (Rutinaria)</option>
                        </select>
                    </div>
                </div>

                <!-- SECCIÓN 03: LOGÍSTICA DE TRANSPORTE -->
                <div class="section-heading text-primary mb-3">
                    <i class="fa-solid fa-truck-front me-1"></i> 03. Logística de Transporte y Justificación
                </div>

                <div class="mb-3">
                    <label class="form-label-glass">Vehículo de Transporte / Unidad Asignada</label>
                    <input type="text" name="vehiculo_transporte" value="{{ old('vehiculo_transporte') }}" class="form-control-glass" placeholder="Ej: Camión Toyota Dyna P-841MNC (Protección Civil), Pickup Cruz Roja...">
                </div>

                <div class="mb-4">
                    <label class="form-label-glass">Observaciones / Justificación de la Transferencia</label>
                    <textarea name="observaciones" rows="3" class="form-control-glass" placeholder="Motivo de la transferencia, informe de escasez en destino, detalles del convoy...">{{ old('observaciones') }}</textarea>
                </div>

                <div class="d-flex justify-content-end gap-2 pt-3 border-top border-secondary border-opacity-25">
                    <a href="{{ route('transfers.index') }}" class="btn-glass">Cancelar</a>
                    <button type="submit" class="btn-accent px-4">
                        <i class="fa-solid fa-truck-fast me-1"></i> Generar Orden de Transferencia
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
