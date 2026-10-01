@extends('layouts.app')

@section('title', 'Centro de Mando Humanitario')
@section('page-title', 'Centro de Mando y Telemetría Humanitaria')
@section('page-subtitle', 'Monitoreo dinámico de suministros, modelos de I.O. y distribución en tiempo real')

@section('content')

@php
    $isGuest = session('guest_mode', false) && !auth()->check();
    $userName = auth()->check() ? auth()->user()->name : 'Invitado';
    $firstName = explode(' ', trim($userName))[0];
@endphp

<style>
    /* Ambient Neon Glow Lines & 3D Accent Layers */
    .cockpit-hero {
        position: relative;
        background: linear-gradient(145deg, rgba(20, 24, 38, 0.85) 0%, rgba(8, 10, 16, 0.95) 100%);
        backdrop-filter: blur(28px) saturate(200%);
        -webkit-backdrop-filter: blur(28px) saturate(200%);
        border: 1px solid rgba(230, 168, 108, 0.16);
        border-radius: 26px;
        padding: 28px 32px;
        box-shadow: 
            0 20px 50px rgba(0, 0, 0, 0.7),
            inset 0 1px 1px rgba(255, 255, 255, 0.15),
            0 0 30px rgba(216, 150, 86, 0.14);
        margin-bottom: 26px;
        overflow: hidden;
    }

    .cockpit-hero::before {
        content: '';
        position: absolute;
        top: -60px;
        right: -60px;
        width: 260px;
        height: 260px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(216, 150, 86, 0.22) 0%, rgba(180, 100, 40, 0.08) 50%, transparent 70%);
        filter: blur(40px);
        pointer-events: none;
    }

    /* Big Futuristic Digital Clock (Inspired by Image 4) */
    .digital-clock-display {
        font-family: 'Space Grotesk', sans-serif;
        font-size: 2.7rem;
        font-weight: 800;
        letter-spacing: -1.5px;
        color: #ffffff;
        line-height: 1;
        text-shadow: 0 0 25px rgba(255, 255, 255, 0.4), 0 0 12px rgba(216, 150, 86, 0.45);
    }

    .digital-date-sub {
        font-size: 0.84rem;
        color: rgba(226, 232, 240, 0.65);
        font-weight: 500;
        margin-top: 5px;
    }

    /* Weather & Status Widget (Inspired by Image 4) */
    .weather-widget-card {
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid rgba(230, 168, 108, 0.16);
        border-radius: 18px;
        padding: 12px 18px;
        display: flex;
        align-items: center;
        gap: 14px;
        backdrop-filter: blur(14px);
    }

    .weather-icon-circle {
        width: 44px;
        height: 44px;
        border-radius: 14px;
        background: linear-gradient(135deg, rgba(216, 150, 86, 0.25), rgba(180, 100, 40, 0.18));
        border: 1px solid rgba(230, 168, 108, 0.35);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #e6a86c;
        font-size: 1.3rem;
        box-shadow: 0 0 15px rgba(216, 150, 86, 0.25);
    }

    /* Futuristic Stat Cards (Inspired by Image 2 & 3) */
    .neon-stat-card {
        background: linear-gradient(145deg, rgba(22, 30, 48, 0.7) 0%, rgba(11, 15, 26, 0.85) 100%);
        border: 1px solid rgba(255, 255, 255, 0.09);
        border-radius: 22px;
        padding: 22px 24px;
        position: relative;
        overflow: hidden;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 14px 35px rgba(0, 0, 0, 0.5), inset 0 1px 1px rgba(255, 255, 255, 0.12);
    }

    .neon-stat-card:hover {
        transform: translateY(-3px);
        border-color: rgba(230, 168, 108, 0.28);
        box-shadow: 0 20px 45px rgba(0, 0, 0, 0.65), 0 0 25px rgba(216, 150, 86, 0.15);
    }

    .neon-stat-card .top-badge-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
    }

    .neon-stat-card .stat-title {
        font-size: 0.74rem;
        font-weight: 700;
        letter-spacing: 0.8px;
        text-transform: uppercase;
        color: rgba(226, 232, 240, 0.65);
    }

    .neon-stat-card .stat-big-num {
        font-family: 'Space Grotesk', sans-serif;
        font-size: 2.25rem;
        font-weight: 800;
        color: #ffffff;
        line-height: 1.1;
        letter-spacing: -0.5px;
    }

    .stat-glow-tag {
        font-size: 0.68rem;
        font-weight: 700;
        padding: 3px 8px;
        border-radius: 9999px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .tag-emerald { background: rgba(16, 185, 129, 0.12); color: #6ee7b7; border: 1px solid rgba(16, 185, 129, 0.28); }
    .tag-cyan { background: rgba(230, 168, 108, 0.12); color: #e6a86c; border: 1px solid rgba(230, 168, 108, 0.35); }
    .tag-rose { background: rgba(244, 63, 94, 0.12); color: #fb7185; border: 1px solid rgba(244, 63, 94, 0.30); }
    .tag-amber { background: rgba(230, 168, 108, 0.12); color: #e6a86c; border: 1px solid rgba(230, 168, 108, 0.35); }

    /* Quick Access App Tiles (Inspired by Image 4) */
    .app-tile-btn {
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 18px;
        padding: 16px 14px;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        text-decoration: none;
        transition: all 0.25s ease;
        position: relative;
        overflow: hidden;
    }

    .app-tile-btn:hover {
        background: rgba(230, 168, 108, 0.08);
        border-color: rgba(230, 168, 108, 0.40);
        transform: translateY(-3px);
        box-shadow: 0 12px 25px rgba(0, 0, 0, 0.5), 0 0 20px rgba(216, 150, 86, 0.18);
    }

    .app-tile-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        margin-bottom: 10px;
        box-shadow: 0 8px 18px rgba(0,0,0,0.4);
        transition: transform 0.25s ease;
    }

    .app-tile-btn:hover .app-tile-icon {
        transform: scale(1.1);
    }

    .icon-blue   { background: linear-gradient(135deg, #d89656, #c07840); color: #1a0e04; }
    .icon-amber  { background: linear-gradient(135deg, #c07840, #a05828); color: #f5ede0; }
    .icon-emerald{ background: linear-gradient(135deg, #d89656, #b06030); color: #1a0e04; }
    .icon-purple { background: linear-gradient(135deg, #b87840, #d09050); color: #1a0e04; }
    .icon-rose   { background: linear-gradient(135deg, #c06830, #d88050); color: #f5ede0; }
    .icon-slate  { background: linear-gradient(135deg, #2a1e12, #3e2c1a); color: #e6a86c; border: 1px solid rgba(230,168,108,0.3); }

    /* Interactive To-Do Checklist Widget (Inspired by Image 4) */
    .todo-item {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.07);
        border-radius: 14px;
        padding: 12px 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 8px;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .todo-item:hover {
        background: rgba(255, 255, 255, 0.06);
        border-color: rgba(255, 255, 255, 0.14);
    }

    .todo-item.completed {
        opacity: 0.6;
        text-decoration: line-through;
    }

    .todo-checkbox {
        width: 18px;
        height: 18px;
        border-radius: 50%;
        border: 2px solid rgba(148, 163, 184, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
        flex-shrink: 0;
        transition: all 0.2s;
    }

    .todo-item.completed .todo-checkbox {
        background: var(--accent);
        border-color: var(--accent);
        color: #1a0e04;
    }

    /* Circular Health Gauge (Inspired by Image 2 & 3) */
    .circular-gauge-container {
        position: relative;
        width: 170px;
        height: 170px;
        margin: 0 auto;
    }

    .circular-gauge-center {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        pointer-events: none;
    }

    .gauge-percent {
        font-family: 'Space Grotesk', sans-serif;
        font-size: 2.1rem;
        font-weight: 800;
        color: #fff;
        line-height: 1;
        text-shadow: 0 0 15px rgba(216, 150, 86, 0.4);
    }

    .gauge-subtext {
        font-size: 0.65rem;
        color: rgba(226, 232, 240, 0.6);
        text-transform: uppercase;
        font-weight: 700;
        letter-spacing: 0.5px;
        margin-top: 4px;
    }

    /* Heuristic telemetry line (Inspired by Image 2) */
    .telemetry-route-card {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 16px;
        padding: 14px 18px;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }

    .telemetry-flow {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .flow-node {
        background: rgba(216, 150, 86, 0.12);
        border: 1px solid rgba(230, 168, 108, 0.35);
        padding: 6px 12px;
        border-radius: 10px;
        font-size: 0.82rem;
        font-weight: 600;
        color: #fff;
    }

    .flow-arrow {
        color: #e6a86c;
        font-size: 0.9rem;
        animation: flowPulse 2s infinite;
    }

    @keyframes flowPulse {
        0%, 100% { opacity: 0.4; transform: translateX(0); }
        50% { opacity: 1; transform: translateX(4px); }
    }

    /* LIGHT MODE STYLES FOR DASHBOARD (Inspired by Image 2) */
    [data-bs-theme="light"] .cockpit-hero {
        background: #ffffff !important;
        border: 1px solid rgba(0, 0, 0, 0.08) !important;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05) !important;
    }

    [data-bs-theme="light"] .digital-clock-display {
        color: #0f172a !important;
        text-shadow: none !important;
    }

    [data-bs-theme="light"] .digital-date-sub {
        color: #64748b !important;
    }

    [data-bs-theme="light"] .weather-widget-card {
        background: #f8fafc !important;
        border-color: rgba(0, 0, 0, 0.08) !important;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02) !important;
    }

    [data-bs-theme="light"] .neon-stat-card {
        background: #ffffff !important;
        border: 1px solid rgba(0, 0, 0, 0.08) !important;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04), 0 1px 3px rgba(0, 0, 0, 0.02) !important;
    }

    [data-bs-theme="light"] .neon-stat-card:hover {
        border-color: rgba(200, 131, 64, 0.35) !important;
        box-shadow: 0 14px 34px rgba(15, 23, 42, 0.08) !important;
    }

    [data-bs-theme="light"] .neon-stat-card .stat-big-num {
        color: #0f172a !important;
    }

    [data-bs-theme="light"] .neon-stat-card .stat-title {
        color: #64748b !important;
    }

    [data-bs-theme="light"] .app-tile-btn {
        background: #ffffff !important;
        border: 1px solid rgba(0, 0, 0, 0.08) !important;
        box-shadow: 0 3px 10px rgba(15, 23, 42, 0.03) !important;
    }

    [data-bs-theme="light"] .app-tile-btn:hover {
        border-color: var(--accent) !important;
        box-shadow: 0 8px 24px rgba(200, 131, 64, 0.14) !important;
    }

    [data-bs-theme="light"] .todo-item {
        background: #ffffff !important;
        border: 1px solid rgba(0, 0, 0, 0.07) !important;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02) !important;
    }

    [data-bs-theme="light"] .todo-item:hover {
        background: #f8fafc !important;
        border-color: rgba(0, 0, 0, 0.12) !important;
    }

    [data-bs-theme="light"] .telemetry-route-card {
        background: #ffffff !important;
        border: 1px solid rgba(0, 0, 0, 0.08) !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03) !important;
    }

    [data-bs-theme="light"] .flow-node {
        background: rgba(200, 131, 64, 0.08) !important;
        border-color: rgba(200, 131, 64, 0.25) !important;
        color: #c88340 !important;
    }

    [data-bs-theme="light"] .gauge-percent {
        color: #0f172a !important;
        text-shadow: none !important;
    }

    [data-bs-theme="light"] .gauge-subtext {
        color: #64748b !important;
    }
</style>

<!-- 1. HERO COCKPIT CENTERPIECE (Inspirado en Imagen 4 - Glassmorphic Widget Board) -->
<div class="cockpit-hero">
    <div class="row align-items-center g-4">
        <!-- Izquierda: Reloj Gigante y Fecha -->
        <div class="col-lg-5 col-md-6">
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="badge" style="background: rgba(216, 150, 86, 0.15); color: #e6a86c; font-size: 0.72rem; border: 1px solid rgba(216, 150, 86, 0.35); border-radius: 9999px; padding: 3px 10px;">
                    <i class="fa-solid fa-satellite-dish me-1"></i> Telemetría Activa en Tiempo Real
                </span>
                @if($activeShelter)
                    <span class="badge" style="background: rgba(216, 150, 86, 0.25); color: #f5ede0; font-size: 0.72rem; border: 1px solid rgba(216, 150, 86, 0.4); border-radius: 9999px; padding: 3px 10px;">
                        [{{ $activeShelter->codigo }}] {{ $activeShelter->nombre }}
                    </span>
                @else
                    <span class="badge" style="background: rgba(216, 150, 86, 0.15); color: #e6a86c; font-size: 0.72rem; border: 1px solid rgba(216, 150, 86, 0.35); border-radius: 9999px; padding: 3px 10px;">
                        🌐 Red Metropolitana
                    </span>
                @endif
            </div>
            <div class="digital-clock-display" id="heroBigClock">--:--:--</div>
            <div class="digital-date-sub" id="heroBigDate">Cargando fecha del sistema...</div>
        </div>

        <!-- Centro: Barra de Búsqueda Rápida Widget (Imagen 4) -->
        <div class="col-lg-4 col-md-6">
            <div class="position-relative">
                <div class="p-3 rounded-4" style="background: rgba(11, 15, 25, 0.8); border: 1px solid rgba(230, 168, 108, 0.18); box-shadow: 0 4px 20px rgba(0,0,0,0.4);">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="fa-solid fa-magnifying-glass fs-6" style="color: #e6a86c;"></i>
                        <span class="text-white fw-bold" style="font-size: 0.84rem;">Buscador Rápido</span>
                        <span class="ms-auto" style="font-size: 0.62rem; background: rgba(255,255,255,0.08); padding: 2px 6px; border-radius: 5px; color: rgba(255,255,255,0.5);">Ctrl + K</span>
                    </div>
                    <input 
                        type="text" 
                        id="cockpitQuickSearch" 
                        class="form-control" 
                        placeholder="Filtrar insumos, centros, órdenes..." 
                        style="background: transparent; border: none; color: #fff; padding: 4px 0; font-size: 0.82rem; outline: none; box-shadow: none;"
                    >
                </div>
            </div>
        </div>

        <!-- Derecha: Widget de Clima y Estado Operativo (Imagen 4) -->
        <div class="col-lg-3 col-md-12">
            <div class="weather-widget-card ms-lg-auto">
                <div class="weather-icon-circle">
                    <i class="fa-solid fa-cloud-moon"></i>
                </div>
                <div>
                    <div class="fw-bold text-white fs-5" style="line-height: 1.1;">24°C</div>
                    <div class="text-muted small" style="font-size: 0.72rem;">Clima Operativo Estable</div>
                    <span class="badge mt-1" style="background: rgba(216, 150, 86, 0.15); color: #e6a86c; font-size: 0.66rem; border: 1px solid rgba(216, 150, 86, 0.3);">
                        <i class="fa-solid fa-circle-check me-1"></i> Sin incidencias viales
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 2. TARJETAS DE MÉTRICAS KPI (Inspiradas en Imagen 2 y 3) -->
<div class="row g-3 mb-4">
    <!-- Card 1: Población -->
    <div class="col-xl-3 col-md-6">
        <div class="neon-stat-card h-100">
            <div class="top-badge-row">
                <span class="stat-title">Población Protegida</span>
                <span class="stat-glow-tag tag-cyan">
                    <i class="fa-solid fa-users"></i> {{ $ocupacionPct }}% Ocupado
                </span>
            </div>
            <div class="stat-big-num mb-1">{{ number_format($poblacionTotal) }}</div>
            <div class="text-muted small mb-2" style="font-size: 0.76rem;">
                de <strong class="text-white">{{ number_format($capacidadMaxima) }}</strong> cupos totales habilitados
            </div>
            <div class="progress-glass">
                <div class="bar bar-accent" style="width: {{ min($ocupacionPct, 100) }}%"></div>
            </div>
        </div>
    </div>

    <!-- Card 2: Población Prioritaria -->
    <div class="col-xl-3 col-md-6">
        <div class="neon-stat-card h-100">
            <div class="top-badge-row">
                <span class="stat-title">Grupos Prioritarios</span>
                <span class="stat-glow-tag" style="background: rgba(216, 150, 86, 0.15); color: #e6a86c; border: 1px solid rgba(216, 150, 86, 0.35);">
                    <i class="fa-solid fa-shield-heart"></i> Atención Especial
                </span>
            </div>
            <div class="stat-big-num mb-1">{{ number_format($poblacionNinos + $poblacionMayores + $poblacionMedica) }}</div>
            <div class="d-flex justify-content-between text-muted small mb-2" style="font-size: 0.74rem;">
                <span><i class="fa-solid fa-baby me-1" style="color: #e6a86c;"></i> {{ $poblacionNinos }} niños</span>
                <span><i class="fa-solid fa-hospital me-1" style="color: #d89656;"></i> {{ $poblacionMedica }} médicos</span>
            </div>
            <div class="progress-glass">
                <div class="bar" style="background: linear-gradient(90deg, #d89656, #e6a86c); width: 68%;"></div>
            </div>
        </div>
    </div>

    <!-- Card 3: Alimentos en Riesgo Crítico -->
    <div class="col-xl-3 col-md-6">
        <div class="neon-stat-card h-100">
            <div class="top-badge-row">
                <span class="stat-title">Alimentos Críticos (&lt; 2d)</span>
                <span class="stat-glow-tag {{ $criticosAlimentos > 0 ? 'tag-rose' : 'tag-emerald' }}">
                    <i class="fa-solid {{ $criticosAlimentos > 0 ? 'fa-triangle-exclamation' : 'fa-circle-check' }}"></i>
                    {{ $criticosAlimentos > 0 ? 'Déficit' : 'Óptimo' }}
                </span>
            </div>
            <div class="stat-big-num mb-1 {{ $criticosAlimentos > 0 ? 'text-danger' : 'text-success' }}">{{ $criticosAlimentos }}</div>
            <div class="text-muted small mb-2" style="font-size: 0.76rem;">
                {{ $criticosAlimentos > 0 ? 'Alimentos con menos de 48h de reserva' : 'Reserva alimentaria garantizada (> 7 días)' }}
            </div>
            <div class="progress-glass">
                <div class="bar {{ $criticosAlimentos > 0 ? 'bar-danger' : 'bar-success' }}" style="width: {{ $criticosAlimentos > 0 ? '90%' : '15%' }}%"></div>
            </div>
        </div>
    </div>

    <!-- Card 4: Insumos Médicos -->
    <div class="col-xl-3 col-md-6">
        <div class="neon-stat-card h-100">
            <div class="top-badge-row">
                <span class="stat-title">Fármacos Críticos (&lt; 2d)</span>
                <span class="stat-glow-tag {{ $criticosMedicos > 0 ? 'tag-amber' : 'tag-emerald' }}">
                    <i class="fa-solid {{ $criticosMedicos > 0 ? 'fa-pills' : 'fa-kit-medical' }}"></i>
                    {{ $criticosMedicos > 0 ? 'Atención' : 'Estable' }}
                </span>
            </div>
            <div class="stat-big-num mb-1 {{ $criticosMedicos > 0 ? 'text-warning' : 'text-success' }}">{{ $criticosMedicos }}</div>
            <div class="text-muted small mb-2" style="font-size: 0.76rem;">
                {{ $criticosMedicos > 0 ? 'Fármacos esenciales en alerta de reposición' : 'Cadena médica y botiquines abastecidos' }}
            </div>
            <div class="progress-glass">
                <div class="bar {{ $criticosMedicos > 0 ? 'bar-warning' : 'bar-success' }}" style="width: {{ $criticosMedicos > 0 ? '80%' : '15%' }}%"></div>
            </div>
        </div>
    </div>
</div>

<!-- 3. ACCESOS RÁPIDOS MODULARES (Inspirado en Imagen 4 "Quick Access") -->
<div class="glass-card p-4 mb-4">
    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
        <span class="section-heading mb-0 text-white">
            <i class="fa-solid fa-grid-2" style="color: #e6a86c;"></i> Acceso Rápido a Módulos (Quick Access)
        </span>
        <small class="text-muted" style="font-size: 0.74rem;">Acciones inmediatas con un solo clic</small>
    </div>

    <div class="row g-3">
        <div class="col-xl-2 col-md-4 col-6">
            <a href="{{ route('movements.create') }}" class="app-tile-btn">
                <div class="app-tile-icon icon-blue">
                    <i class="fa-solid fa-clipboard-check"></i>
                </div>
                <strong class="text-white" style="font-size: 0.82rem;">Kardex</strong>
                <small class="text-muted" style="font-size: 0.68rem;">Entradas/Salidas</small>
            </a>
        </div>

        <div class="col-xl-2 col-md-4 col-6">
            <a href="{{ route('transfers.create') }}" class="app-tile-btn">
                <div class="app-tile-icon icon-amber">
                    <i class="fa-solid fa-truck-ramp-box"></i>
                </div>
                <strong class="text-white" style="font-size: 0.82rem;">Rebalanceo</strong>
                <small class="text-muted" style="font-size: 0.68rem;">Transferencias</small>
            </a>
        </div>

        <div class="col-xl-2 col-md-4 col-6">
            <a href="{{ route('supplies.index') }}" class="app-tile-btn">
                <div class="app-tile-icon icon-emerald">
                    <i class="fa-solid fa-boxes-packing"></i>
                </div>
                <strong class="text-white" style="font-size: 0.82rem;">Insumos</strong>
                <small class="text-muted" style="font-size: 0.68rem;">Control de Stock</small>
            </a>
        </div>

        <div class="col-xl-2 col-md-4 col-6">
            <a href="{{ route('shelters.index') }}" class="app-tile-btn">
                <div class="app-tile-icon icon-purple">
                    <i class="fa-solid fa-tents"></i>
                </div>
                <strong class="text-white" style="font-size: 0.82rem;">Refugios</strong>
                <small class="text-muted" style="font-size: 0.68rem;">Censo y Centros</small>
            </a>
        </div>

        <div class="col-xl-2 col-md-4 col-6">
            <a href="{{ route('cpm.index') }}" class="app-tile-btn">
                <div class="app-tile-icon icon-rose">
                    <i class="fa-solid fa-diagram-project"></i>
                </div>
                <strong class="text-white" style="font-size: 0.82rem;">Ruta CPM</strong>
                <small class="text-muted" style="font-size: 0.68rem;">Ruta Crítica I.O.</small>
            </a>
        </div>

        <div class="col-xl-2 col-md-4 col-6">
            <a href="{{ route('reports.index') }}" class="app-tile-btn">
                <div class="app-tile-icon icon-slate">
                    <i class="fa-solid fa-file-pdf"></i>
                </div>
                <strong class="text-white" style="font-size: 0.82rem;">Reportes</strong>
                <small class="text-muted" style="font-size: 0.68rem;">Descargar PDF</small>
            </a>
        </div>
    </div>
</div>

<!-- 4. FILA DE TELEMETRÍA Y CONTROL (Medidor Radial Imagen 2/3 + Tareas Imagen 4) -->
<div class="row g-4 mb-4">
    <!-- Columna 1: Medidor Circular de Salud de la Red (Inspirado en Imagen 2 y 3) -->
    <div class="col-lg-5">
        <div class="glass-card p-4 h-100 d-flex flex-column justify-content-between">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="section-heading mb-0 text-white">
                    <i class="fa-solid fa-circle-notch" style="color: #e6a86c;"></i> Salud Global de Suministros
                </span>
                <span class="stat-glow-tag" style="background: rgba(216, 150, 86, 0.15); color: #e6a86c; border: 1px solid rgba(216, 150, 86, 0.35);">
                    <i class="fa-solid fa-bolt"></i> En Línea
                </span>
            </div>

            <!-- Gráfico Circular Radial SVG / Donut -->
            <div class="my-3">
                <div class="circular-gauge-container">
                    <canvas id="gaugeSupplyHealth" width="170" height="170"></canvas>
                    <div class="circular-gauge-center">
                        <div class="gauge-percent">88.5%</div>
                        <div class="gauge-subtext">Cobertura Total</div>
                    </div>
                </div>
            </div>

            <!-- Mini Barras de Telemetría -->
            <div class="mt-2">
                <div class="d-flex justify-content-between align-items-center mb-1" style="font-size: 0.75rem;">
                    <span class="text-muted"><i class="fa-solid fa-bowl-food me-1" style="color: #e6a86c;"></i> Cobertura Alimentaria</span>
                    <strong class="text-white">94%</strong>
                </div>
                <div class="progress-glass mb-2">
                    <div class="bar bar-accent" style="width: 94%;"></div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-1" style="font-size: 0.75rem;">
                    <span class="text-muted"><i class="fa-solid fa-kit-medical me-1" style="color: #c07840;"></i> Cobertura Farmacéutica</span>
                    <strong class="text-white">82%</strong>
                </div>
                <div class="progress-glass mb-2">
                    <div class="bar bar-accent" style="width: 82%;"></div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-1" style="font-size: 0.75rem;">
                    <span class="text-muted"><i class="fa-solid fa-bed me-1" style="color: #e6a86c;"></i> Capacidad de Hospedaje</span>
                    <strong class="text-white">{{ $ocupacionPct }}%</strong>
                </div>
                <div class="progress-glass">
                    <div class="bar" style="background: linear-gradient(90deg, #c07840, #d89656); width: {{ min($ocupacionPct, 100) }}%;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Columna 2: Lista de Acciones Críticas & Tareas Pendientes (Inspirado en Imagen 4 "To Do") -->
    <div class="col-lg-7">
        <div class="glass-card p-4 h-100 d-flex flex-column justify-content-between">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <span class="section-heading mb-0 text-white">
                    <i class="fa-solid fa-list-check" style="color: #e6a86c;"></i> Acciones Operativas Prioritarias
                </span>
                <span class="badge" style="background: rgba(216, 150, 86, 0.15); color: #e6a86c; border: 1px solid rgba(216, 150, 86, 0.3); font-size: 0.72rem; border-radius: 6px;">
                    <span id="completedTasksCount">2</span> de 4 Completadas
                </span>
            </div>

            <!-- Tareas Interactivas con Checkboxes Estilizados -->
            <div class="todo-list" id="todoWidgetList">
                <div class="todo-item" onclick="toggleTask(this)">
                    <div class="d-flex align-items-center">
                        <div class="todo-checkbox"><i class="fa-solid fa-check fs-6" style="display:none;"></i></div>
                        <div>
                            <strong class="text-white d-block" style="font-size: 0.82rem;">Reabastecer Refugio Naciones Unidas</strong>
                            <small class="text-muted" style="font-size: 0.72rem;">Insumo: Harina PAN &bull; Cobertura menor a 48h</small>
                        </div>
                    </div>
                    <span class="badge ms-2" style="background: rgba(244, 63, 94, 0.15); color: #fb7185; border: 1px solid rgba(244, 63, 94, 0.3); font-size: 0.65rem;">Urgente</span>
                </div>

                <div class="todo-item completed" onclick="toggleTask(this)">
                    <div class="d-flex align-items-center">
                        <div class="todo-checkbox"><i class="fa-solid fa-check fs-6" style="display:block;"></i></div>
                        <div>
                            <strong class="text-white d-block" style="font-size: 0.82rem;">Confirmar recepción de transferencia TRF-2026-002</strong>
                            <small class="text-muted" style="font-size: 0.72rem;">Despacho de medicamentos recibido conforme</small>
                        </div>
                    </div>
                    <span class="badge ms-2" style="background: rgba(216, 150, 86, 0.15); color: #e6a86c; border: 1px solid rgba(216, 150, 86, 0.3); font-size: 0.65rem;">Completado</span>
                </div>

                <div class="todo-item" onclick="toggleTask(this)">
                    <div class="d-flex align-items-center">
                        <div class="todo-checkbox"><i class="fa-solid fa-check fs-6" style="display:none;"></i></div>
                        <div>
                            <strong class="text-white d-block" style="font-size: 0.82rem;">Actualizar censo de infantes en Albergue Parque Central</strong>
                            <small class="text-muted" style="font-size: 0.72rem;">Ajuste de raciones alimentarias de leche maternal</small>
                        </div>
                    </div>
                    <span class="badge ms-2" style="background: rgba(216, 150, 86, 0.15); color: #e6a86c; border: 1px solid rgba(216, 150, 86, 0.3); font-size: 0.65rem;">Media</span>
                </div>

                <div class="todo-item completed" onclick="toggleTask(this)">
                    <div class="d-flex align-items-center">
                        <div class="todo-checkbox"><i class="fa-solid fa-check fs-6" style="display:block;"></i></div>
                        <div>
                            <strong class="text-white d-block" style="font-size: 0.82rem;">Sincronizar bitácora diaria de Kardex central</strong>
                            <small class="text-muted" style="font-size: 0.72rem;">Cierre de inventarios por turno de almacén</small>
                        </div>
                    </div>
                    <span class="badge ms-2" style="background: rgba(216, 150, 86, 0.15); color: #e6a86c; border: 1px solid rgba(216, 150, 86, 0.3); font-size: 0.65rem;">Completado</span>
                </div>
            </div>

            <!-- Barra de Progreso de Tareas -->
            <div class="mt-3">
                <div class="progress-glass">
                    <div class="bar bar-accent" id="todoProgressBar" style="width: 50%;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 5. REBALANCEO INTELIGENTE TELEMÉTRICO (Inspirado en Imagen 2 - Heuristic Routes) -->
@if(!empty($sugerenciasTransferencia))
<div class="glass-card p-4 mb-4">
    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
        <div>
            <span class="section-heading mb-1 text-white">
                <i class="fa-solid fa-route me-1" style="color: #e6a86c;"></i> Optimización Heurística de Transferencias Inter-Refugios
            </span>
            <p class="m-0 text-muted" style="font-size: 0.78rem;">
                Canaliza automáticamente excedentes desde refugios seguros hacia albergues saturados para evitar desabastecimiento.
            </p>
        </div>
        <span class="stat-glow-tag tag-cyan">
            <i class="fa-solid fa-brain"></i> {{ count($sugerenciasTransferencia) }} Rutas Calculadas
        </span>
    </div>

    @foreach(array_slice($sugerenciasTransferencia, 0, 3) as $sug)
    <div class="telemetry-route-card">
        <div class="telemetry-flow">
            <div class="flow-node">
                <i class="fa-solid fa-warehouse me-1" style="color: #e6a86c;"></i> {{ $sug['refugio_origen']->nombre }}
            </div>
            <div class="d-flex align-items-center gap-1">
                <i class="fa-solid fa-arrow-right flow-arrow"></i>
                <span class="badge" style="background: rgba(216, 150, 86, 0.18); color: #e6a86c; border: 1px solid rgba(216, 150, 86, 0.35); font-size: 0.74rem;">
                    {{ $sug['cantidad_sugerida'] }} {{ $sug['unidad_medida'] }} de {{ $sug['supply']->nombre }}
                </span>
                <i class="fa-solid fa-arrow-right flow-arrow"></i>
            </div>
            <div class="flow-node" style="border-color: rgba(244, 63, 94, 0.4); background: rgba(244, 63, 94, 0.1);">
                <i class="fa-solid fa-tents text-danger me-1"></i> {{ $sug['refugio_destino']->nombre }}
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <span class="text-muted small d-none d-md-inline" style="font-size: 0.72rem; max-width: 200px;">
                {{ $sug['motivo'] }}
            </span>
            <a href="{{ route('transfers.create', ['origen_id' => $sug['refugio_origen']->id, 'destino_id' => $sug['refugio_destino']->id, 'supply_id' => $sug['supply']->id, 'cantidad' => $sug['cantidad_sugerida'], 'prioridad' => $sug['prioridad']]) }}" 
               class="btn-accent py-1 px-3" style="font-size: 0.76rem;">
                <i class="fa-solid fa-bolt"></i> Ejecutar Envío
            </a>
        </div>
    </div>
    @endforeach
</div>
@endif

<!-- 6. GRÁFICAS VISUALES DE IMPACTO (Inspirado en Imagen 2 y 3) -->
<div class="row g-4 mb-4">
    <!-- Gráfica 1: Ocupación por Refugio -->
    <div class="col-lg-7">
        <div class="glass-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="section-heading mb-0 text-white">
                    <i class="fa-solid fa-chart-simple me-1" style="color: #e6a86c;"></i> Capacidad vs Personas Albergadas
                </span>
                <span class="stat-glow-tag tag-cyan">Tiempo Real</span>
            </div>
            <div style="height: 270px;">
                <canvas id="chartRefugios"></canvas>
            </div>
        </div>
    </div>

    <!-- Gráfica 2: Clasificación ABC -->
    <div class="col-lg-5">
        <div class="glass-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="section-heading mb-0 text-white">
                    <i class="fa-solid fa-chart-pie me-1" style="color: #e6a86c;"></i> Clasificación ABC de Insumos
                </span>
                <span class="stat-glow-tag tag-amber">Pareto I.O.</span>
            </div>
            <div style="height: 270px; position: relative;">
                <canvas id="chartAbc"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- 7. TABLA DE MONITOREO DE ALBERGUES -->
<div class="glass-card p-4 mb-4">
    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
        <div>
            <span class="section-heading mb-1 text-white">
                <i class="fa-solid fa-building-shield me-1" style="color: #e6a86c;"></i> Red de Refugios Humanitarios Monitoreados
            </span>
            <small class="text-muted">Censo poblacional y nivel de saturación por municipio</small>
        </div>
        @if(!$isGuest && auth()->check() && auth()->user()->isCoordinador())
        <a href="{{ route('shelters.create') }}" class="btn-accent py-2 px-3" style="font-size: 0.78rem;">
            <i class="fa-solid fa-plus"></i> Registrar Refugio
        </a>
        @endif
    </div>

    <div class="table-responsive">
        <table class="dark-table">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Refugio / Albergue</th>
                    <th>Municipio</th>
                    <th>Ocupación</th>
                    <th>Estado</th>
                    <th class="text-end">Operaciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($refugios as $refugio)
                @php $isActive = $activeShelter && $activeShelter->id == $refugio->id; @endphp
                <tr style="{{ $isActive ? 'background: rgba(216, 150, 86, 0.12); border-left: 3px solid #e6a86c;' : '' }}">
                    <td>
                        <code style="color: #e6a86c; font-weight: 700; background: rgba(216, 150, 86, 0.12); border: 1px solid rgba(216, 150, 86, 0.25); padding: 3px 8px; border-radius: 6px;">{{ $refugio->codigo }}</code>
                        @if($isActive)
                            <span class="badge ms-1" style="background: #e6a86c; color: #1a0e04; font-size: 0.60rem; font-weight: 800;">ACTIVO</span>
                        @endif
                    </td>
                    <td>
                        <strong class="text-white d-block">{{ $refugio->nombre }}</strong>
                        <small class="text-muted">{{ $refugio->direccion }}</small>
                    </td>
                    <td>{{ $refugio->municipio }}</td>
                    <td style="min-width: 150px;">
                        <div class="d-flex justify-content-between align-items-center mb-1" style="font-size: 0.72rem;">
                            <span class="fw-bold text-white">{{ $refugio->poblacion_total }} / {{ $refugio->capacidad_maxima }}</span>
                            <span class="text-muted">{{ $refugio->ocupacion_porcentaje }}%</span>
                        </div>
                        <div class="progress-glass">
                            <div class="bar {{ $refugio->ocupacion_porcentaje > 90 ? 'bar-danger' : ($refugio->ocupacion_porcentaje > 70 ? 'bar-warning' : 'bar-success') }}" 
                                 style="width: {{ min($refugio->ocupacion_porcentaje, 100) }}%"></div>
                        </div>
                    </td>
                    <td>
                        <span class="bg-glass {{ $refugio->estado_badge['class'] }}">
                            {{ $refugio->estado_badge['text'] }}
                        </span>
                    </td>
                    <td class="text-end">
                        <div class="d-inline-flex gap-2">
                            <a href="{{ route('dashboard', ['shelter_id' => $refugio->id]) }}" class="{{ $isActive ? 'btn-glass' : 'btn-accent' }}" style="padding: 5px 12px; font-size: 0.75rem; {{ $isActive ? 'color: #e6a86c; border-color: rgba(216,150,86,0.4);' : '' }}">
                                <i class="fa-solid fa-arrows-to-eye"></i> {{ $isActive ? 'Operando' : 'Operar' }}
                            </a>
                            <a href="{{ route('shelters.show', $refugio) }}" class="btn-glass" style="padding: 5px 12px; font-size: 0.75rem;">
                                <i class="fa-solid fa-boxes-stacked"></i> Stock
                            </a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- 8. MOVIMIENTOS RECIENTES Y TRANSFERENCIAS ACTIVAS -->
<div class="row g-4">
    @if(auth()->check() && auth()->user()->canAccessMovements())
    <div class="col-lg-7">
        <div class="glass-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="section-heading mb-0 text-white">
                    <i class="fa-solid fa-clock-rotate-left me-1" style="color: #e6a86c;"></i> Movimientos Recientes de Kardex
                </span>
                <a href="{{ route('movements.index') }}" class="btn-glass py-1 px-2" style="font-size: 0.74rem;">Ver todos</a>
            </div>
            <div class="table-responsive">
                <table class="dark-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Insumo</th>
                            <th>Refugio</th>
                            <th class="text-end">Cantidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($movimientosRecientes as $mov)
                        <tr>
                            <td style="font-size: 0.75rem; color: rgba(226, 232, 240, 0.7);">{{ $mov->fecha_movimiento->format('d/m H:i') }}</td>
                            <td>
                                <span class="bg-glass {{ $mov->tipo_badge['class'] }}" style="font-size: 0.68rem;">
                                    <i class="fa-solid {{ $mov->tipo_badge['icon'] }}"></i> {{ $mov->tipo_badge['text'] }}
                                </span>
                            </td>
                            <td>
                                <strong class="text-white">{{ $mov->supply->nombre }}</strong>
                            </td>
                            <td class="text-muted" style="font-size: 0.78rem;">{{ $mov->shelter->nombre }}</td>
                            <td class="text-end">
                                <span class="fw-bold {{ $mov->es_entrada ? 'text-success' : 'text-danger' }}">
                                    {{ $mov->es_entrada ? '+' : '-' }}{{ $mov->cantidad }} {{ $mov->supply->unidad_medida }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No hay movimientos recientes registrados.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
    @else
    <div class="col-12">
    @endif
        <div class="glass-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="section-heading mb-0 text-white">
                    <i class="fa-solid fa-truck-moving me-1" style="color: #e6a86c;"></i> Transferencias Activas
                </span>
                <a href="{{ route('transfers.index') }}" class="btn-glass py-1 px-2" style="font-size: 0.74rem;">Ver todas</a>
            </div>
            @forelse($transferenciasActivas as $trf)
            <div class="p-3 mb-2 rounded-3" style="background: rgba(255,255,255,0.03); border: 1px solid var(--border);">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <span class="fw-bold text-white" style="font-size: 0.85rem;">{{ $trf->codigo_transferencia }}</span>
                    <span class="bg-glass {{ $trf->estado_badge['class'] }}" style="font-size: 0.68rem;">{{ $trf->estado_badge['text'] }}</span>
                </div>
                <div style="font-size: 0.78rem;" class="text-muted mb-1">
                    {{ $trf->refugioOrigen->nombre }} &rarr; <span class="text-white fw-bold">{{ $trf->refugioDestino->nombre }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center" style="font-size: 0.76rem;">
                    <span class="fw-bold" style="color: #e6a86c;">{{ $trf->cantidad }} {{ $trf->supply->unidad_medida }} de {{ $trf->supply->nombre }}</span>
                    <span class="bg-glass {{ $trf->prioridad_badge['class'] }}">{{ $trf->prioridad }}</span>
                </div>
            </div>
            @empty
            <div class="text-center text-muted py-4">No hay transferencias activas en tránsito en este momento.</div>
            @endforelse
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Digital Clock & Date in Hero
    function updateHeroClock() {
        const now = new Date();
        const hours = String(now.getHours() % 12 || 12).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        const ampm = now.getHours() >= 12 ? 'PM' : 'AM';
        
        const clockEl = document.getElementById('heroBigClock');
        if (clockEl) {
            clockEl.textContent = `${hours}:${minutes}:${seconds} ${ampm}`;
        }

        const dateEl = document.getElementById('heroBigDate');
        if (dateEl) {
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const dateStr = now.toLocaleDateString('es-ES', options);
            dateEl.textContent = dateStr.charAt(0).toUpperCase() + dateStr.slice(1);
        }
    }
    setInterval(updateHeroClock, 1000);
    updateHeroClock();

    // 2. Circular Health Gauge (Inspired by Image 2 & 3)
    const gaugeCanvas = document.getElementById('gaugeSupplyHealth');
    if (gaugeCanvas) {
        new Chart(gaugeCanvas.getContext('2d'), {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [88.5, 11.5],
                    backgroundColor: [
                        '#d89656', // Dorado ámbar — paleta unificada
                        'rgba(255, 255, 255, 0.05)'
                    ],
                    borderWidth: 0,
                    circumference: 360,
                }]
            },
            options: {
                responsive: false,
                maintainAspectRatio: false,
                cutout: '80%',
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: false }
                }
            }
        });
    }

    // 3. Gráfico de Ocupación por Refugio (Chart.js)
    const ctxRef = document.getElementById('chartRefugios');
    if (ctxRef) {
        new Chart(ctxRef.getContext('2d'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($chartRefugiosLabels) !!},
                datasets: [
                    {
                        label: 'Población Albergada',
                        data: {!! json_encode($chartRefugiosPoblacion) !!},
                        backgroundColor: 'rgba(216, 150, 86, 0.80)',
                        borderColor: '#e6a86c',
                        borderWidth: 1,
                        borderRadius: 8
                    },
                    {
                        label: 'Capacidad Máxima',
                        data: {!! json_encode($chartRefugiosCapacidad) !!},
                        backgroundColor: 'rgba(255, 255, 255, 0.06)',
                        borderColor: 'rgba(255, 255, 255, 0.18)',
                        borderWidth: 1,
                        borderRadius: 8
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { labels: { color: '#f5ede0', font: { family: 'Plus Jakarta Sans', size: 11 } } }
                },
                scales: {
                    x: { ticks: { color: 'rgba(220, 210, 195, 0.65)', font: { size: 10 } }, grid: { display: false } },
                    y: { ticks: { color: 'rgba(220, 210, 195, 0.65)' }, grid: { color: 'rgba(230,168,108,0.06)' } }
                }
            }
        });
    }

    // 4. Gráfico ABC
    const ctxAbc = document.getElementById('chartAbc');
    if (ctxAbc) {
        new Chart(ctxAbc.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: [
                    'Clase A (Crítico / Alto Valor)',
                    'Clase B (Moderado / Estándar)',
                    'Clase C (Bajo Costo / Descartable)'
                ],
                datasets: [{
                    data: [
                        {{ $abcData['resumen']['A'] ?? 4 }},
                        {{ $abcData['resumen']['B'] ?? 5 }},
                        {{ $abcData['resumen']['C'] ?? 3 }}
                    ],
                    backgroundColor: [
                        '#d89656',  // ámbar principal
                        '#b06030',  // ámbar oscuro
                        '#2a1a0a'   // ámbar muy oscuro / casi marrón
                    ],
                    borderWidth: 0,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: '#f5ede0', font: { family: 'Plus Jakarta Sans', size: 11 }, boxWidth: 14 }
                    }
                },
                cutout: '72%'
            }
        });
    }

    // 5. Quick Search in Cockpit
    const cockpitSearch = document.getElementById('cockpitQuickSearch');
    if (cockpitSearch) {
        cockpitSearch.addEventListener('input', function(e) {
            const val = e.target.value.toLowerCase().trim();
            const rows = document.querySelectorAll('.dark-table tbody tr');
            rows.forEach(r => {
                const text = r.textContent.toLowerCase();
                r.style.display = text.includes(val) ? '' : 'none';
            });
        });
    }
});

// Interactive Task Checklist Click
function toggleTask(el) {
    el.classList.toggle('completed');
    const checkIcon = el.querySelector('.todo-checkbox i');
    if (checkIcon) {
        checkIcon.style.display = el.classList.contains('completed') ? 'block' : 'none';
    }

    // Recalcular contador y progreso
    const all = document.querySelectorAll('#todoWidgetList .todo-item');
    const completed = document.querySelectorAll('#todoWidgetList .todo-item.completed');
    const countEl = document.getElementById('completedTasksCount');
    const barEl = document.getElementById('todoProgressBar');

    if (countEl) countEl.textContent = completed.length;
    if (barEl && all.length > 0) {
        const pct = Math.round((completed.length / all.length) * 100);
        barEl.style.width = pct + '%';
    }
}
</script>
@endpush