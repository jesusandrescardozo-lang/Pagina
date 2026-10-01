@extends('layouts.app')

@section('title', 'Control de Personal y Credenciales Oficiales')
@section('page-title', 'Control de Personal y Credenciales de Seguridad')
@section('page-subtitle', 'Gestión de funcionarios, credenciales institucionales y control de acceso')

@section('content')

<!-- 1. COCKPIT HEADER CON TELEMETRÍA DE PERSONAL -->
<div class="cockpit-page-header">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge" style="background: rgba(230, 168, 108, 0.15); color: #e6a86c; font-size: 0.72rem; border: 1px solid rgba(230, 168, 108, 0.35); border-radius: 9999px; padding: 3px 10px;">
                <i class="fa-solid fa-id-card-clip me-1"></i> Credenciales Criptográficas
            </span>
            <span class="badge" style="background: rgba(16, 185, 129, 0.2); color: #34d399; font-size: 0.72rem; border: 1px solid rgba(16, 185, 129, 0.4); border-radius: 9999px; padding: 3px 10px;">
                {{ $users->count() }} Funcionarios Autorizados
            </span>
        </div>
        <h3 class="cockpit-header-title">Personal Humanitario &amp; Credenciales</h3>
        <p class="cockpit-header-subtitle">Tokens criptográficos intransferibles, roles operativos y control de acceso</p>
    </div>
</div>

<!-- 2. TABLA DE FUNCIONARIOS Y CREDENCIALES -->
<div class="glass-card p-4">
    <div class="table-responsive">
        <table class="dark-table">
            <thead>
                <tr>
                    <th>Funcionario</th>
                    <th>Cargo Institucional</th>
                    <th>Rol Operativo</th>
                    <th>Sede / Refugio Asignado</th>
                    <th class="text-center">Estado de Acceso</th>
                    <th class="text-end">Credencial Oficial</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $u)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <div style="width: 40px; height: 40px; border-radius: 12px; background: linear-gradient(135deg, #d89656, #c07840); display: flex; align-items: center; justify-content: center; font-weight: 700; color: white; flex-shrink: 0; box-shadow: 0 4px 14px rgba(0,0,0,0.3);">
                                {{ strtoupper(substr($u->name, 0, 2)) }}
                            </div>
                            <div>
                                <strong class="text-white d-block" style="font-size: 0.88rem;">{{ $u->name }}</strong>
                                <small class="text-muted" style="font-size: 0.74rem;">{{ $u->email }}</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="text-white fw-semibold" style="font-size: 0.82rem;">{{ $u->cargo ?? 'Operador de Campo' }}</span>
                    </td>
                    <td>
                        <span class="bg-glass bg-info2" style="font-weight: 700;">
                            {{ strtoupper(str_replace('_', ' ', $u->rol)) }}
                        </span>
                    </td>
                    <td>
                        @if($u->shelter)
                            <span class="badge" style="background: rgba(230, 168, 108, 0.15); color: #e6a86c; border: 1px solid rgba(230, 168, 108, 0.3); font-size: 0.74rem; border-radius: 8px;">
                                <i class="fa-solid fa-tents me-1"></i> [{{ $u->shelter->codigo }}] {{ $u->shelter->nombre }}
                            </span>
                        @else
                            <span class="badge" style="background: rgba(148, 163, 184, 0.15); color: #94a3b8; border: 1px solid rgba(148, 163, 184, 0.3); font-size: 0.74rem; border-radius: 8px;">
                                🌐 Centralizado
                            </span>
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 9999px; padding: 4px 10px; font-size: 0.72rem;">
                            <i class="fa-solid fa-circle-check me-1"></i> Autorizado
                        </span>
                    </td>
                    <td class="text-end">
                        <a href="{{ route('carnets.show', $u) }}" target="_blank" class="btn-accent py-1 px-3" style="font-size: 0.78rem;">
                            <i class="fa-solid fa-id-badge me-1"></i> Ver Carnet Oficial
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection
