<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Credencial Oficial - {{ $user->name }} · NutriLnk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --badge-bg: #0f172a;
            --accent: #0284c7;
            --accent-gold: #f59e0b;
            --venezuela-yellow: #facc15;
            --venezuela-blue: #2563eb;
            --venezuela-red: #dc2626;
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #090d16;
            color: #f8fafc;
            min-height: 100vh;
            padding: 30px 15px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        /* Barra de Control / Acciones */
        .action-bar {
            max-width: 720px;
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.1);
            padding: 14px 22px;
            border-radius: 16px;
            backdrop-filter: blur(16px);
        }

        /* Contenedor de Carnets (Frente y Reverso) */
        .cards-wrapper {
            display: flex;
            flex-wrap: wrap;
            gap: 28px;
            justify-content: center;
            perspective: 1000px;
        }

        /* Tarjeta Estilo Credencial PVC (Formato Vertical) */
        .badge-card {
            width: 320px;
            height: 505px;
            background: #0f172a;
            border-radius: 20px;
            overflow: hidden;
            position: relative;
            box-shadow: 0 25px 60px rgba(0,0,0,0.7), 0 0 35px rgba(2,132,199,0.25);
            border: 2px solid rgba(255,255,255,0.14);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* Ranura para cordón / lanyard */
        .lanyard-hole {
            position: absolute;
            top: 10px;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 8px;
            background: rgba(0,0,0,0.6);
            border: 1px solid rgba(255,255,255,0.25);
            border-radius: 10px;
            z-index: 10;
        }

        /* Encabezado del carnet con tricolor sutil */
        .badge-header {
            background: linear-gradient(135deg, #0b1e38, #1e293b);
            padding: 26px 16px 14px;
            text-align: center;
            position: relative;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .tricolor-stripe {
            height: 4px;
            width: 100%;
            position: absolute;
            top: 0;
            left: 0;
            display: flex;
        }
        .tricolor-stripe .yellow { flex: 1; background: var(--venezuela-yellow); }
        .tricolor-stripe .blue   { flex: 1; background: var(--venezuela-blue); }
        .tricolor-stripe .red    { flex: 1; background: var(--venezuela-red); }

        .badge-inst-title {
            font-size: 0.68rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #94a3b8;
            margin-top: 6px;
        }
        .badge-inst-sub {
            font-size: 0.85rem;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: 0.05em;
        }

        /* Cuerpo del Carnet Frente */
        .badge-body {
            padding: 14px 18px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
            justify-content: space-around;
        }
        .avatar-wrap {
            position: relative;
            margin-bottom: 8px;
        }
        .avatar-circle {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            background: linear-gradient(135deg, #0284c7, #10b981);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: 800;
            color: #ffffff;
            box-shadow: 0 10px 25px rgba(2,132,199,0.4);
            border: 3px solid rgba(255,255,255,0.2);
        }
        .avatar-check {
            position: absolute;
            bottom: -4px;
            right: -4px;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #10b981;
            border: 2px solid #0f172a;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            color: #fff;
        }

        .emp-name {
            font-size: 1rem;
            font-weight: 800;
            color: #ffffff;
            line-height: 1.2;
            margin-bottom: 3px;
        }
        .emp-cargo {
            font-size: 0.72rem;
            color: #94a3b8;
            font-weight: 500;
            margin-bottom: 6px;
        }
        .emp-role-tag {
            background: rgba(2,132,199,0.18);
            border: 1px solid rgba(56,189,248,0.4);
            color: #38bdf8;
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            padding: 3px 10px;
            border-radius: 20px;
            display: inline-block;
            margin-bottom: 10px;
        }

        /* Chip de Seguridad Digital y Barcode */
        .chip-security-box {
            background: linear-gradient(135deg, rgba(255,255,255,0.05), rgba(255,255,255,0.02));
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 14px;
            padding: 10px 14px;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 8px;
        }
        .smart-chip {
            width: 40px;
            height: 30px;
            border-radius: 6px;
            background: linear-gradient(135deg, #d97706, #f59e0b);
            border: 1px solid #b45309;
            position: relative;
            box-shadow: 0 0 10px rgba(245, 158, 11, 0.4);
            flex-shrink: 0;
        }
        .smart-chip::before {
            content: '';
            position: absolute;
            top: 50%; left: 0; right: 0;
            height: 1px; background: rgba(0,0,0,0.35);
        }
        .smart-chip::after {
            content: '';
            position: absolute;
            top: 0; bottom: 0; left: 50%;
            width: 1px; background: rgba(0,0,0,0.35);
        }
        .chip-info {
            text-align: left;
            flex: 1;
        }
        .chip-info-label {
            font-size: 0.58rem;
            text-transform: uppercase;
            font-weight: 700;
            color: #94a3b8;
            letter-spacing: 0.5px;
        }
        .chip-info-val {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.72rem;
            color: #38bdf8;
            font-weight: 700;
        }

        /* Simulación de Código de Barras de Seguridad */
        .barcode-strip {
            width: 100%;
            height: 28px;
            background: repeating-linear-gradient(
                90deg,
                #fff, #fff 2px,
                transparent 2px, transparent 4px,
                #fff 4px, #fff 7px,
                transparent 7px, transparent 9px,
                #fff 9px, #fff 10px,
                transparent 10px, transparent 13px,
                #fff 13px, #fff 16px,
                transparent 16px, transparent 18px
            );
            opacity: 0.75;
            border-radius: 4px;
            margin-top: 4px;
        }

        /* Pie del Carnet */
        .badge-footer {
            background: #090f1e;
            padding: 10px 14px;
            text-align: center;
            border-top: 1px solid rgba(255,255,255,0.08);
            font-size: 0.64rem;
            color: #64748b;
        }
        .token-text {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.68rem;
            font-weight: 700;
            color: #38bdf8;
            letter-spacing: 0.05em;
        }

        /* Reverso del Carnet */
        .badge-back {
            background: linear-gradient(180deg, #0f172a, #090d16);
            padding: 26px 20px 16px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .back-title {
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 6px;
        }
        .back-section p {
            font-size: 0.68rem;
            line-height: 1.4;
            color: #94a3b8;
        }

        /* Modo Impresión */
        @media print {
            body {
                background: white !important;
                color: black !important;
                padding: 0 !important;
            }
            .action-bar { display: none !important; }
            .badge-card {
                box-shadow: none !important;
                border: 1px solid #ccc !important;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

    <!-- Barra Superior de Acciones -->
    <div class="action-bar">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('carnets.index') }}" class="btn btn-sm btn-outline-light" style="border-radius: 10px;">
                <i class="fa-solid fa-arrow-left me-1"></i> Volver a Personal
            </a>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2" style="border-radius: 10px; font-size: 0.75rem;">
                <i class="fa-solid fa-shield-check me-1"></i> Credencial Oficial Autorizada
            </span>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-outline-info px-3 py-2 fw-semibold" style="border-radius: 10px;" data-bs-toggle="modal" data-bs-target="#modalQrGAuth">
                <i class="fa-brands fa-google me-1"></i> Vincular Google Authenticator
            </button>
            <button onclick="window.print()" class="btn btn-sm btn-info text-white fw-bold px-3 py-2" style="border-radius: 10px;">
                <i class="fa-solid fa-print me-1"></i> Imprimir Credencial PVC
            </button>
        </div>
    </div>

    <!-- Contenedor de las dos caras del Carnet -->
    <div class="cards-wrapper">

        <!-- ================= LADO FRENTE ================= -->
        <div class="badge-card">
            <div class="lanyard-hole"></div>

            <div class="badge-header">
                <div class="tricolor-stripe">
                    <div class="yellow"></div>
                    <div class="blue"></div>
                    <div class="red"></div>
                </div>
                <div class="badge-inst-title">República Bolivariana de Venezuela</div>
                <div class="badge-inst-sub">NUTRILNK &middot; LOGÍSTICA DC</div>
            </div>

            <div class="badge-body">
                <div class="avatar-wrap">
                    <div class="avatar-circle">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    </div>
                    <div class="avatar-check"><i class="fa-solid fa-check"></i></div>
                </div>

                <div class="emp-name">{{ $user->name }}</div>
                <div class="emp-cargo">{{ $user->cargo ?? 'Personal de Asistencia Humanitaria' }}</div>
                <div class="emp-role-tag">{{ strtoupper(str_replace('_', ' ', $user->rol)) }}</div>

                <!-- Chip de Identificación y Seguridad Digital -->
                <div class="chip-security-box">
                    <div class="smart-chip"></div>
                    <div class="chip-info">
                        <div class="chip-info-label">Acceso 2FA Protegido</div>
                        <div class="chip-info-val">GOOGLE AUTH &bull; ACTIVO</div>
                    </div>
                    <i class="fa-brands fa-google text-info fs-5"></i>
                </div>

                <!-- Simulación Barcode Oficial -->
                <div class="barcode-strip"></div>
            </div>

            <div class="badge-footer">
                <div class="text-white-50" style="font-size: 0.6rem;">IDENTIFICADOR INSTITUCIONAL</div>
                <div class="token-text">{{ $user->qr_token }}</div>
            </div>
        </div>

        <!-- ================= LADO REVERSO ================= -->
        <div class="badge-card badge-back">
            <div class="lanyard-hole"></div>

            <div class="back-section mt-3">
                <div class="back-title text-info">
                    <i class="fa-solid fa-shield-halved"></i> Acreditación Oficial
                </div>
                <p class="mb-2">
                    El portador de la presente credencial física se encuentra debidamente autorizado para operar y gestionar inventarios de emergencia, transferencias y racionamientos en los refugios del Distrito Capital.
                </p>

                <div class="back-title text-warning mt-3">
                    <i class="fa-solid fa-triangle-exclamation"></i> Normas de Uso
                </div>
                <ul class="ps-3 mb-2" style="font-size: 0.65rem;">
                    <li>Credencial personal, oficial e intransferible.</li>
                    <li>El acceso al sistema está protegido con <strong>Google Authenticator (2FA)</strong>.</li>
                    <li>En caso de extravío o baja, notifique inmediatamente para revocar el acceso.</li>
                </ul>
            </div>

            <div class="p-2 rounded-3 text-center my-2" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08);">
                <div class="text-info fw-bold" style="font-size: 0.62rem;"><i class="fa-brands fa-google me-1"></i> GOOGLE AUTHENTICATOR 2FA</div>
                <div class="text-white fw-bold font-monospace" style="font-size: 0.72rem; letter-spacing: 1.5px;">{{ $user->getGoogle2faSecret() }}</div>
                <div class="text-white-50" style="font-size: 0.58rem;">Código criptográfico personal intransferible</div>
            </div>

            <div class="border-top border-secondary pt-2 text-center" style="font-size: 0.6rem; color: #64748b;">
                <div class="text-white-50 mb-1">Firma del Director de Operaciones</div>
                <div style="font-style: italic; color: #94a3b8;">Protección Civil & Asistencia Humanitaria DC</div>
                <div class="mt-1" style="font-size: 0.55rem; color: #475569;">EMISIÓN OFICIAL 2026 · VIGENTE</div>
            </div>
        </div>

    </div>

    <!-- Tarjeta de Vinculación con Google Authenticator (Exclusivo de Credenciales) -->
    <div class="mt-4 text-center p-4 d-print-none" style="max-width: 670px; width: 100%; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); border-radius: 20px; backdrop-filter: blur(16px);">
        <div class="d-flex align-items-center justify-content-center gap-2 mb-3">
            <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(66, 133, 244, 0.2); display: flex; align-items: center; justify-content: center; color: #60a5fa; font-size: 1.2rem;">
                <i class="fa-brands fa-google"></i>
            </div>
            <h5 class="fw-bold mb-0 text-white">Vincular con Google Authenticator</h5>
        </div>
        <p class="text-white-50 small mb-3">
            Para autorizar el acceso al sistema, el funcionario debe escanear este código QR en la aplicación <strong>Google Authenticator</strong> de su teléfono móvil:
        </p>
        <div class="row align-items-center g-3">
            <div class="col-md-5 text-center">
                <div style="background: white; padding: 12px; border-radius: 16px; display: inline-block; box-shadow: 0 8px 24px rgba(0,0,0,0.3);">
                    {!! $user->getGoogle2faQrSvg(170) !!}
                </div>
            </div>
            <div class="col-md-7 text-start">
                <div class="text-muted small mb-1">Clave secreta manual:</div>
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span style="font-family: 'JetBrains Mono', monospace; background: rgba(0,0,0,0.5); padding: 7px 14px; border-radius: 8px; border: 1px solid rgba(56, 189, 248, 0.4); color: #38bdf8; font-weight: 700; letter-spacing: 2px; font-size: 0.95rem;">
                        {{ $user->getGoogle2faSecret() }}
                    </span>
                    <button type="button" class="btn btn-sm btn-outline-info" onclick="navigator.clipboard.writeText('{{ $user->getGoogle2faSecret() }}'); alert('¡Clave copiada al portapapeles!');" title="Copiar clave">
                        <i class="fa-regular fa-copy"></i>
                    </button>
                </div>
                <div class="small text-white-50">
                    <div class="text-info fw-bold mb-1"><i class="fa-solid fa-mobile-screen me-1"></i> Pasos rápidos:</div>
                    1. Abra <strong>Google Authenticator</strong> en su celular.<br>
                    2. Toque <strong>"+"</strong> &gt; <strong>"Escanear código QR"</strong>.<br>
                    3. Al iniciar sesión en el sistema, ingrese los 6 dígitos que genere la app.
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Vincular Google Authenticator en el Celular -->
    <div class="modal fade" id="modalQrGAuth" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="background: #0f172a; border: 1px solid rgba(255,255,255,0.14); border-radius: 20px; color: #f8fafc;">
                <div class="modal-header border-secondary">
                    <div class="d-flex align-items-center gap-2">
                        <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(66, 133, 244, 0.2); display: flex; align-items: center; justify-content: center; color: #60a5fa;">
                            <i class="fa-brands fa-google"></i>
                        </div>
                        <div>
                            <h6 class="modal-title fw-bold mb-0">Google Authenticator: {{ $user->name }}</h6>
                            <div class="text-muted small">{{ $user->email }}</div>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <p class="text-white-50 small mb-3">
                        Escanee este código QR usando la app <strong>Google Authenticator</strong> en su teléfono:
                    </p>
                    <div style="background: white; padding: 12px; border-radius: 16px; display: inline-block; box-shadow: 0 8px 24px rgba(0,0,0,0.3);">
                        <div style="background: white; padding: 12px; border-radius: 16px; display: inline-block;">
                            {!! $user->getGoogle2faQrSvg(190) !!}
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="text-muted small mb-1">Clave secreta manual:</div>
                        <span style="font-family: 'JetBrains Mono', monospace; background: rgba(0,0,0,0.5); padding: 6px 14px; border-radius: 8px; border: 1px solid rgba(56, 189, 248, 0.4); color: #38bdf8; font-weight: 700; letter-spacing: 2px;">
                            {{ $user->getGoogle2faSecret() }}
                        </span>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
