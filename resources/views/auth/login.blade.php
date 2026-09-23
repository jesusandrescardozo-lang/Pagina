<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NutriLnk — Iniciar Sesión | Sistema Humanitario y Logístico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --accent: #0ea5e9;
            --accent-glow: rgba(14, 165, 233, 0.45);
            --accent-purple: #6366f1;
            --emerald: #10b981;
            --amber: #f59e0b;
            --bg-dark: #07090e;
            --card-glass: rgba(18, 22, 34, 0.65);
            --border-glass: rgba(255, 255, 255, 0.12);
            --border-glass-hover: rgba(255, 255, 255, 0.25);
            --input-bg: rgba(11, 15, 25, 0.7);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            min-height: 100vh;
            background-color: var(--bg-dark);
            color: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
            padding: 40px 20px;
        }

        /* Ambient glowing background */
        .ambient-glow {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
            overflow: hidden;
        }

        .glow-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(90px);
            opacity: 0.35;
            animation: pulse-glow 12s ease-in-out infinite alternate;
        }

        .glow-1 {
            width: 520px;
            height: 520px;
            background: radial-gradient(circle, rgba(14, 165, 233, 0.4) 0%, rgba(99, 102, 241, 0.2) 60%, transparent 80%);
            top: -120px;
            left: -100px;
        }

        .glow-2 {
            width: 480px;
            height: 480px;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.25) 0%, rgba(14, 165, 233, 0.15) 60%, transparent 80%);
            bottom: -100px;
            right: -100px;
            animation-duration: 15s;
        }

        .glow-3 {
            width: 320px;
            height: 320px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.3) 0%, transparent 70%);
            top: 40%;
            left: 60%;
            animation-duration: 10s;
        }

        /* 3D Dark Glossy Elements floating in background */
        .scene-decorations {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 1;
        }

        .shape-3d {
            position: absolute;
            border-radius: 50%;
        }

        /* Glossy Dark Torus (Ring) */
        .torus-wrap {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 620px;
            height: 620px;
            transform: translate(-50%, -50%);
            pointer-events: none;
            z-index: 1;
        }

        .glossy-ring {
            position: absolute;
            width: 420px;
            height: 420px;
            border-radius: 50%;
            border: 45px solid #0d121d;
            box-shadow: 
                0 30px 60px rgba(0, 0, 0, 0.9),
                inset 0 4px 12px rgba(255, 255, 255, 0.18),
                inset 0 -8px 20px rgba(0, 0, 0, 0.8),
                0 0 40px rgba(14, 165, 233, 0.15);
            top: 60px;
            left: -40px;
            transform: rotate(-25deg) rotateX(35deg);
            animation: float-ring 14s ease-in-out infinite alternate;
        }

        .glossy-sphere-1 {
            width: 140px;
            height: 140px;
            right: 40px;
            top: 80px;
            background: radial-gradient(circle at 35% 30%, #2a3449 0%, #111624 50%, #05070d 90%);
            box-shadow: 
                0 25px 45px rgba(0, 0, 0, 0.8),
                inset 0 3px 8px rgba(255, 255, 255, 0.25),
                inset 0 -10px 18px rgba(0, 0, 0, 0.9),
                0 0 25px rgba(14, 165, 233, 0.12);
            animation: float-sphere 9s ease-in-out infinite alternate;
        }

        .glossy-sphere-2 {
            width: 75px;
            height: 75px;
            left: 30px;
            bottom: 120px;
            background: radial-gradient(circle at 35% 30%, #333d52 0%, #0f1422 60%, #030408 95%);
            box-shadow: 
                0 15px 30px rgba(0, 0, 0, 0.85),
                inset 0 2px 5px rgba(255, 255, 255, 0.28),
                inset 0 -6px 12px rgba(0, 0, 0, 0.9);
            animation: float-sphere 11s ease-in-out infinite alternate-reverse;
        }

        .mini-sphere {
            width: 32px;
            height: 32px;
            left: 110px;
            top: 150px;
            background: radial-gradient(circle at 30% 28%, #475569 0%, #0f172a 70%, #020617 100%);
            box-shadow: 0 10px 20px rgba(0,0,0,0.7), inset 0 1px 3px rgba(255,255,255,0.3);
            animation: float-sphere 7s ease-in-out infinite alternate;
        }

        @keyframes float-ring {
            0% { transform: rotate(-25deg) rotateX(35deg) translateY(0); }
            100% { transform: rotate(-22deg) rotateX(30deg) translateY(-18px); }
        }

        @keyframes float-sphere {
            0% { transform: translateY(0) scale(1); }
            100% { transform: translateY(-22px) scale(1.03); }
        }

        @keyframes pulse-glow {
            0% { opacity: 0.28; transform: scale(1); }
            100% { opacity: 0.42; transform: scale(1.08); }
        }

        /* Glassmorphism Mockup Card Container */
        .card-container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 480px;
        }

        .glass-card {
            position: relative;
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.07) 0%, rgba(255, 255, 255, 0.02) 60%, rgba(14, 165, 233, 0.03) 100%), var(--card-glass);
            backdrop-filter: blur(36px) saturate(190%);
            -webkit-backdrop-filter: blur(36px) saturate(190%);
            border: 1px solid var(--border-glass);
            border-radius: 28px;
            padding: 42px 38px;
            box-shadow: 
                0 30px 80px rgba(0, 0, 0, 0.75),
                0 10px 30px rgba(0, 0, 0, 0.5),
                inset 0 1px 1px rgba(255, 255, 255, 0.22),
                inset 0 -1px 1px rgba(0, 0, 0, 0.4);
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .glass-card:hover {
            border-color: var(--border-glass-hover);
            box-shadow: 
                0 35px 90px rgba(0, 0, 0, 0.8),
                0 0 50px rgba(14, 165, 233, 0.12),
                inset 0 1px 2px rgba(255, 255, 255, 0.28);
        }

        /* Top Bar Indicator (similar to modern mockups) */
        .card-top-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: rgba(14, 165, 233, 0.1);
            border: 1px solid rgba(14, 165, 233, 0.25);
            padding: 5px 12px;
            border-radius: 9999px;
            font-size: 0.72rem;
            font-weight: 600;
            color: #38bdf8;
            letter-spacing: 0.4px;
            text-transform: uppercase;
        }

        .status-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background-color: #38bdf8;
            box-shadow: 0 0 8px #38bdf8;
            animation: blink 2s infinite ease-in-out;
        }

        @keyframes blink {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(0.85); }
        }

        .mockup-controls {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .mockup-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            transition: all 0.2s ease;
        }

        .mockup-dot.active {
            background: rgba(255, 255, 255, 0.7);
            box-shadow: 0 0 6px rgba(255, 255, 255, 0.4);
        }

        /* Brand & Header */
        .brand-section {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo-box {
            position: relative;
            width: 64px;
            height: 64px;
            margin: 0 auto 16px;
            border-radius: 20px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.12), rgba(255, 255, 255, 0.02));
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 
                0 12px 30px rgba(0, 0, 0, 0.5),
                inset 0 1px 1px rgba(255, 255, 255, 0.35),
                0 0 25px rgba(14, 165, 233, 0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.65rem;
            color: #fff;
            transition: transform 0.3s ease;
        }

        .logo-box i {
            background: linear-gradient(135deg, #38bdf8 0%, #818cf8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            filter: drop-shadow(0 2px 6px rgba(56, 189, 248, 0.4));
        }

        .brand-title {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.65rem;
            font-weight: 700;
            letter-spacing: -0.5px;
            color: #ffffff;
            margin-bottom: 4px;
        }

        .brand-desc {
            font-size: 0.82rem;
            color: rgba(226, 232, 240, 0.6);
            font-weight: 400;
            line-height: 1.4;
        }

        /* Form Labels & Controls */
        .form-label-custom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.74rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            color: rgba(226, 232, 240, 0.7);
            margin-bottom: 8px;
        }

        .field-wrap {
            position: relative;
            margin-bottom: 18px;
        }

        .field-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(148, 163, 184, 0.5);
            font-size: 0.95rem;
            pointer-events: none;
            transition: color 0.2s ease;
            z-index: 2;
        }

        .glass-input {
            width: 100%;
            background: var(--input-bg);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 14px;
            padding: 13px 44px 13px 46px;
            color: #f8fafc;
            font-size: 0.9rem;
            font-family: inherit;
            outline: none;
            transition: all 0.25s ease;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.4);
        }

        .glass-input::placeholder {
            color: rgba(148, 163, 184, 0.35);
        }

        .glass-input:focus {
            background: rgba(15, 21, 37, 0.85);
            border-color: rgba(56, 189, 248, 0.6);
            box-shadow: 
                0 0 0 4px rgba(14, 165, 233, 0.15),
                inset 0 2px 4px rgba(0, 0, 0, 0.4),
                0 0 20px rgba(14, 165, 233, 0.2);
            color: #ffffff;
        }

        .field-wrap:focus-within .field-icon {
            color: #38bdf8;
        }

        /* Password toggle button */
        .toggle-password {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            color: rgba(148, 163, 184, 0.5);
            font-size: 0.95rem;
            cursor: pointer;
            padding: 4px 6px;
            z-index: 2;
            transition: color 0.2s ease;
        }

        .toggle-password:hover {
            color: #38bdf8;
        }

        /* Select styling */
        .glass-select {
            appearance: none;
            -webkit-appearance: none;
            cursor: pointer;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2338bdf8'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 16px center;
            background-size: 15px 15px;
            padding-right: 42px;
        }

        .glass-select option,
        .glass-select optgroup {
            background: #0b0f19;
            color: #f1f5f9;
        }

        .glass-select optgroup {
            color: #38bdf8;
            font-weight: 700;
        }

        /* Security info pill */
        .security-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: rgba(14, 165, 233, 0.08);
            border: 1px solid rgba(14, 165, 233, 0.18);
            border-radius: 12px;
            padding: 9px 14px;
            margin-bottom: 22px;
            font-size: 0.75rem;
            color: #7dd3fc;
            font-weight: 500;
        }

        /* Primary Submit Button */
        .btn-primary-glass {
            width: 100%;
            border: none;
            outline: none;
            padding: 14px 20px;
            border-radius: 14px;
            font-size: 0.93rem;
            font-weight: 700;
            color: #ffffff;
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 50%, #0f766e 100%);
            box-shadow: 
                0 10px 25px rgba(2, 132, 199, 0.4),
                inset 0 1px 1px rgba(255, 255, 255, 0.3);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.25s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-primary-glass:hover {
            transform: translateY(-2px);
            background: linear-gradient(135deg, #0369a1 0%, #0284c7 50%, #10b981 100%);
            box-shadow: 
                0 14px 35px rgba(2, 132, 199, 0.55),
                inset 0 1px 2px rgba(255, 255, 255, 0.4);
            color: #ffffff;
        }

        .btn-primary-glass:active {
            transform: translateY(0);
        }

        /* Divider */
        .glass-divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 22px 0 16px;
            color: rgba(148, 163, 184, 0.4);
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.6px;
            text-transform: uppercase;
        }

        .glass-divider::before,
        .glass-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: rgba(255, 255, 255, 0.08);
        }

        /* Guest Button */
        .btn-guest-glass {
            width: 100%;
            padding: 12px 18px;
            border-radius: 14px;
            background: rgba(245, 158, 11, 0.06);
            border: 1px solid rgba(245, 158, 11, 0.25);
            color: #fbbf24;
            font-size: 0.88rem;
            font-weight: 700;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            transition: all 0.25s ease;
            backdrop-filter: blur(10px);
        }

        .btn-guest-glass:hover {
            background: rgba(245, 158, 11, 0.14);
            border-color: rgba(245, 158, 11, 0.5);
            color: #fde68a;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(245, 158, 11, 0.2);
        }

        .guest-tag {
            font-size: 0.7rem;
            font-weight: 500;
            opacity: 0.75;
            background: rgba(245, 158, 11, 0.15);
            padding: 2px 7px;
            border-radius: 6px;
        }

        /* Alerts */
        .glass-alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
            border-radius: 12px;
            padding: 11px 14px;
            font-size: 0.82rem;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .glass-alert-info {
            background: rgba(14, 165, 233, 0.1);
            border: 1px solid rgba(14, 165, 233, 0.25);
            color: #38bdf8;
            border-radius: 12px;
            padding: 11px 14px;
            font-size: 0.82rem;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Footer hint */
        .footer-hint {
            text-align: center;
            font-size: 0.72rem;
            color: rgba(148, 163, 184, 0.45);
            margin-top: 18px;
            margin-bottom: 0;
            line-height: 1.4;
        }

        @media (max-width: 576px) {
            .glass-card {
                padding: 30px 22px;
                border-radius: 22px;
            }
            .torus-wrap {
                display: none;
            }
        }
    </style>
</head>
<body>

<!-- Ambient Light Blobs -->
<div class="ambient-glow">
    <div class="glow-orb glow-1"></div>
    <div class="glow-orb glow-2"></div>
    <div class="glow-orb glow-3"></div>
</div>

<!-- 3D Scene Glossy Background Objects (Glassmorphism Mockup Elements) -->
<div class="scene-decorations">
    <div class="torus-wrap">
        <div class="glossy-ring"></div>
    </div>
    <div class="shape-3d glossy-sphere-1"></div>
    <div class="shape-3d glossy-sphere-2"></div>
    <div class="shape-3d mini-sphere"></div>
</div>

<!-- Central Login Card -->
<div class="card-container">
    <div class="glass-card">
        <!-- Top bar with status and mockup dots -->
        <div class="card-top-bar">
            <span class="status-pill">
                <span class="status-dot"></span>
                Portal Seguro
            </span>
            <div class="mockup-controls">
                <span class="mockup-dot active"></span>
                <span class="mockup-dot"></span>
                <span class="mockup-dot"></span>
            </div>
        </div>

        <!-- Brand Identity -->
        <div class="brand-section">
            <div class="logo-box">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>
            <h1 class="brand-title">NutriLnk</h1>
            <p class="brand-desc">Plataforma Humanitaria de Gestión y Optimización de Insumos</p>
        </div>

        @if ($errors->any())
            <div class="glass-alert-error">
                <i class="fa-solid fa-circle-exclamation fs-6"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        @if (session('info'))
            <div class="glass-alert-info">
                <i class="fa-solid fa-circle-info fs-6"></i>
                <span>{{ session('info') }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" autocomplete="on">
            @csrf

            <!-- Email -->
            <div class="field-wrap">
                <label class="form-label-custom" for="email">
                    <span>Correo Institucional</span>
                </label>
                <div class="position-relative">
                    <i class="fa-regular fa-envelope field-icon"></i>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="{{ old('email') }}" 
                        class="glass-input" 
                        placeholder="usuario@refugios.ve" 
                        required 
                        autocomplete="email"
                        autofocus
                    >
                </div>
            </div>

            <!-- Password -->
            <div class="field-wrap">
                <label class="form-label-custom" for="password">
                    <span>Contraseña</span>
                </label>
                <div class="position-relative">
                    <i class="fa-solid fa-lock field-icon"></i>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="glass-input" 
                        placeholder="••••••••••••" 
                        required 
                        autocomplete="current-password"
                    >
                    <button type="button" class="toggle-password" id="togglePasswordBtn" title="Mostrar/ocultar contraseña" aria-label="Mostrar u ocultar contraseña">
                        <i class="fa-regular fa-eye" id="togglePasswordIcon"></i>
                    </button>
                </div>
            </div>

            <!-- Shelter Context Selection -->
            <div class="field-wrap mb-4">
                <label class="form-label-custom" for="shelter_id">
                    <span><i class="fa-solid fa-tents text-info me-1"></i> Refugio Asignado</span>
                    <span style="color: #38bdf8; font-size: 0.65rem; background: rgba(56,189,248,0.1); padding: 1px 6px; border-radius: 4px;">Opcional</span>
                </label>
                <div class="position-relative">
                    <i class="fa-solid fa-building-shield field-icon text-info"></i>
                    <select name="shelter_id" id="shelter_id" class="glass-input glass-select">
                        <option value="" selected>🌐 Red Centralizada (Todos los Refugios)</option>
                        <optgroup label="── Centros y Albergues Humanitarios ──">
                            @foreach($shelters as $s)
                                <option value="{{ $s->id }}">
                                    🏢 [{{ $s->codigo }}] {{ $s->nombre }} — {{ $s->municipio }}
                                </option>
                            @endforeach
                        </optgroup>
                    </select>
                </div>
            </div>

            <!-- 2FA indicator badge -->
            <div class="security-badge">
                <i class="fa-solid fa-shield-halved text-info"></i>
                <span>Acceso protegido con Doble Factor (2FA OTP)</span>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn-primary-glass">
                <span>Ingresar al Sistema</span>
                <i class="fa-solid fa-arrow-right"></i>
            </button>
        </form>

        <!-- Divider -->
        <div class="glass-divider">o ingresa sin credenciales</div>

        <!-- Guest Entry Button -->
        <a href="{{ route('guest.enter') }}" class="btn-guest-glass">
            <i class="fa-solid fa-compass"></i>
            <span>Explorar como Invitado</span>
            <span class="guest-tag">Solo lectura</span>
        </a>

        <p class="footer-hint">
            <i class="fa-solid fa-circle-info me-1 text-info"></i>
            El modo invitado permite interactuar con los módulos y reportes en tiempo real sin modificar datos.
        </p>
    </div>
</div>

<script>
    // Toggle Password Visibility
    const toggleBtn = document.getElementById('togglePasswordBtn');
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('togglePasswordIcon');

    if (toggleBtn && passwordInput && toggleIcon) {
        toggleBtn.addEventListener('click', function () {
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
            toggleIcon.className = isPassword ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye';
        });
    }
</script>

</body>
</html>