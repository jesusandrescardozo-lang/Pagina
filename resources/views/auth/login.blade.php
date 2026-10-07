<!DOCTYPE html>
<html lang="es" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NUTRILINK — Iniciar Sesión | Sistema Humanitario y Logístico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@500;700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">

    <script>
        (function() {
            const savedTheme = localStorage.getItem('app-theme') || 'dark';
            document.documentElement.setAttribute('data-bs-theme', savedTheme);
        })();
    </script>

    <style>
        :root {
            --bg-body: #121519;
            --bg-card: #171b21;
            --border-card: rgba(255, 255, 255, 0.08);
            --border-card-hover: rgba(255, 255, 255, 0.16);
            --text-title: #f4f6f8;
            --text-sub: #9aa4b2;
            --input-bg: rgba(255, 255, 255, 0.04);
            --input-border: rgba(255, 255, 255, 0.1);
            --input-focus-border: #e6a86c;
            --input-focus-glow: rgba(230, 168, 108, 0.18);
            --btn-gold: #e6a86c;
            --btn-gold-hover: #f0b67f;
            --btn-gold-shadow: rgba(216, 150, 86, 0.22);
            --amber-accent: #e6a86c;
            --split-bg: #0f1216;
            --transition: 0.2s ease;
        }

        [data-bs-theme="light"] {
            --bg-body: #f4f5f7;
            --bg-card: #ffffff;
            --border-card: rgba(15, 23, 42, 0.1);
            --border-card-hover: rgba(15, 23, 42, 0.2);
            --text-title: #1c2128;
            --text-sub: #69707d;
            --input-bg: #ffffff;
            --input-border: rgba(15, 23, 42, 0.14);
            --input-focus-border: #c88340;
            --input-focus-glow: rgba(200, 131, 64, 0.16);
            --btn-gold: #c88340;
            --btn-gold-hover: #b9773a;
            --btn-gold-shadow: rgba(200, 131, 64, 0.2);
            --amber-accent: #c88340;
            --split-bg: #eef0f3;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Outfit', 'Plus Jakarta Sans', -apple-system, sans-serif;
            background-color: var(--bg-body);
            color: var(--text-title);
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            align-items: stretch;
            transition: background-color 0.3s ease, color 0.3s ease;
            overflow-x: hidden;
        }

        /* Split Screen Container */
        .login-layout {
            display: flex;
            width: 100vw;
            min-height: 100vh;
        }

        /* LEFT PANEL: LUXURY ARCHITECTURAL SHOWCASE (Inspired by Image 1) */
        .showcase-panel {
            flex: 1.1;
            position: relative;
            background-color: var(--split-bg);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 48px 56px;
            overflow: hidden;
        }

        .showcase-bg-img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            /* La imagen es vertical (640x958) con el motivo en la parte alta:
               anclamos arriba para que la punta del dibujo no se recorte. */
            object-position: center top;
            opacity: 1;
            /* Es una foto casi negra: hay que aclararla un poco para que las
               aristas grises del patrón se distingan del fondo. */
            filter: brightness(1.18) contrast(1.06);
            transition: transform 12s ease;
        }

        .showcase-panel:hover .showcase-bg-img {
            transform: scale(1.03);
        }

        /* Cinematic Gradient Overlays (más suave: la imagen ya es oscura) */
        .showcase-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(
                to bottom,
                rgba(7, 9, 14, 0.58) 0%,
                rgba(7, 9, 14, 0.12) 34%,
                rgba(7, 9, 14, 0.32) 62%,
                rgba(7, 9, 14, 0.86) 100%
            );
            pointer-events: none;
            z-index: 1;
        }

        .showcase-right-edge-fade {
            position: absolute;
            top: 0;
            bottom: 0;
            right: 0;
            width: 120px;
            background: linear-gradient(to right, transparent, var(--bg-body));
            pointer-events: none;
            z-index: 2;
        }

        .showcase-header {
            position: relative;
            z-index: 3;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .showcase-logo-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 1.05rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.4);
        }

        .showcase-logo-text {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.25rem;
            font-weight: 700;
            letter-spacing: 2px;
            color: #ffffff;
            text-transform: uppercase;
        }

        .showcase-footer {
            position: relative;
            z-index: 3;
        }

        .welcome-quote-box {
            display: flex;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 30px;
        }

        .accent-vertical-bar {
            width: 3.5px;
            height: 64px;
            background: var(--amber-accent);
            border-radius: 4px;
            flex-shrink: 0;
            margin-top: 4px;
        }

        .welcome-headline {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 2.35rem;
            font-weight: 700;
            line-height: 1.15;
            color: #ffffff;
            letter-spacing: -0.5px;
            margin-bottom: 6px;
        }

        .welcome-subline {
            font-size: 0.95rem;
            color: rgba(226, 232, 240, 0.75);
            max-width: 440px;
            line-height: 1.5;
        }

        .showcase-copyright {
            font-size: 0.76rem;
            color: rgba(255, 255, 255, 0.45);
            letter-spacing: 0.3px;
        }

        /* RIGHT PANEL: FLOATING SLEEK LOGIN CARD (Image 1 Style) */
        .form-panel {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 32px;
            position: relative;
            background: var(--bg-body);
            transition: background-color 0.3s ease;
        }

        /* Header Bar on Form Side (Theme Toggle + Back) */
        .form-panel-topbar {
            position: absolute;
            top: 24px;
            right: 28px;
            display: flex;
            align-items: center;
            gap: 12px;
            z-index: 20;
        }

        /* Theme Toggle Button */
        .theme-toggle-btn {
            background: var(--input-bg);
            border: 1px solid var(--border-card);
            color: var(--text-title);
            padding: 7px 14px;
            border-radius: 9999px;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.25s ease;
            backdrop-filter: blur(12px);
        }

        .theme-toggle-btn:hover {
            border-color: var(--amber-accent);
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.15);
        }

        .theme-toggle-icon {
            color: var(--amber-accent);
            font-size: 0.9rem;
            transition: transform 0.3s ease;
        }

        /* The Login Card */
        .login-card {
            width: 100%;
            max-width: 440px;
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            border-radius: 16px;
            padding: 36px 32px 30px;
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.28);
            position: relative;
            transition: border-color var(--transition), box-shadow var(--transition);
        }

        [data-bs-theme="light"] .login-card {
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
        }

        /* Card Logo Badge */
        .card-logo-badge {
            width: 54px;
            height: 54px;
            margin: 0 auto 16px;
            border-radius: 14px;
            background: rgba(230, 168, 108, 0.1);
            border: 1px solid rgba(230, 168, 108, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--amber-accent);
            font-size: 1.35rem;
        }

        .login-card-title {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.7rem;
            font-weight: 700;
            text-align: center;
            color: var(--text-title);
            margin-bottom: 4px;
            letter-spacing: -0.3px;
        }

        .login-card-subtitle {
            font-size: 0.85rem;
            color: var(--text-sub);
            text-align: center;
            margin-bottom: 26px;
        }

        /* Form Inputs */
        .form-group-custom {
            margin-bottom: 18px;
        }

        .form-label-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 7px;
        }

        .form-label-custom {
            font-size: 0.80rem;
            font-weight: 600;
            color: var(--text-sub);
            letter-spacing: 0.2px;
        }

        .forgot-link {
            font-size: 0.78rem;
            color: var(--amber-accent);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }

        .forgot-link:hover {
            color: #f5c490;
            text-decoration: underline;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon-left {
            position: absolute;
            left: 16px;
            color: var(--text-sub);
            opacity: 0.65;
            font-size: 0.95rem;
            pointer-events: none;
            transition: color 0.2s;
        }

        .sleek-input {
            width: 100%;
            background: var(--input-bg);
            border: 1px solid var(--input-border);
            border-radius: 12px;
            padding: 12px 16px 12px 46px;
            color: var(--text-title);
            font-size: 0.90rem;
            font-family: inherit;
            outline: none;
            transition: all 0.2s ease;
        }

        .sleek-input::placeholder {
            color: var(--text-sub);
            opacity: 0.5;
        }

        .sleek-input:focus {
            border-color: var(--input-focus-border);
            box-shadow: 0 0 0 3px var(--input-focus-glow);
            background: var(--bg-card);
        }

        .input-wrapper:focus-within .input-icon-left {
            color: var(--amber-accent);
            opacity: 1;
        }

        /* Password Eye Toggle */
        .btn-toggle-eye {
            position: absolute;
            right: 14px;
            background: transparent;
            border: none;
            color: var(--text-sub);
            cursor: pointer;
            padding: 4px;
            font-size: 0.95rem;
            transition: color 0.2s;
        }

        .btn-toggle-eye:hover {
            color: var(--text-title);
        }

        /* Select option */
        .sleek-select {
            appearance: none;
            -webkit-appearance: none;
            cursor: pointer;
            padding-right: 40px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23e6a86c'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 16px center;
            background-size: 14px 14px;
        }

        .sleek-select option, .sleek-select optgroup {
            background: #0f131d;
            color: #f1f5f9;
        }

        [data-bs-theme="light"] .sleek-select option,
        [data-bs-theme="light"] .sleek-select optgroup {
            background: #ffffff;
            color: #0f172a;
        }

        /* Demo Quick Account Pills */
        .demo-accounts-bar {
            display: flex;
            align-items: center;
            /* Si los 4 perfiles no entran en una línea, pasan a la siguiente:
               así ningún botón queda recortado (antes se cortaba "Auditor"
               porque la barra hacía scroll horizontal con la barra oculta). */
            flex-wrap: wrap;
            gap: 6px;
            margin-bottom: 20px;
            padding-bottom: 4px;
            scrollbar-width: none;
        }

        .demo-accounts-bar::-webkit-scrollbar { display: none; }

        .demo-pill {
            background: var(--input-bg);
            border: 1px solid var(--border-card);
            color: var(--text-sub);
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 0.72rem;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.2s;
        }

        .demo-pill:hover {
            border-color: var(--amber-accent);
            color: var(--text-title);
            background: rgba(230, 168, 108, 0.1);
        }

        /* Amber Login Button */
        .btn-luxury-login {
            width: 100%;
            background: var(--btn-gold);
            color: #1a120b;
            border: none;
            outline: none;
            padding: 13px 20px;
            border-radius: 12px;
            font-size: 0.95rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            cursor: pointer;
            box-shadow: 0 6px 16px var(--btn-gold-shadow);
            transition: background-color var(--transition), transform var(--transition), box-shadow var(--transition);
            margin-top: 4px;
        }

        .btn-luxury-login:hover {
            background: var(--btn-gold-hover);
            transform: translateY(-1px);
            box-shadow: 0 8px 20px var(--btn-gold-shadow);
            color: #1a120b;
        }

        .btn-luxury-login:active {
            transform: translateY(0);
        }

        /* Divider */
        .or-divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 22px 0 16px;
            color: var(--text-sub);
            font-size: 0.74rem;
            text-transform: lowercase;
            letter-spacing: 0.3px;
        }

        .or-divider::before,
        .or-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border-card);
        }

        /* Guest Entry Button */
        .btn-guest-action {
            width: 100%;
            background: transparent;
            border: 1px solid var(--border-card);
            color: var(--text-title);
            padding: 11px 18px;
            border-radius: 12px;
            font-size: 0.86rem;
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s ease;
        }

        .btn-guest-action:hover {
            border-color: var(--amber-accent);
            color: var(--amber-accent);
            background: rgba(230, 168, 108, 0.06);
            transform: translateY(-1px);
        }

        /* Security Note at Bottom of Card */
        .card-security-footer {
            margin-top: 24px;
            text-align: center;
            font-size: 0.74rem;
            color: var(--text-sub);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .card-security-footer i {
            color: var(--amber-accent);
            font-size: 0.85rem;
        }

        /* Responsive Breakpoints */
        @media (max-width: 992px) {
            .showcase-panel {
                display: none;
            }
            .form-panel {
                flex: 1;
                padding: 40px 20px;
                min-height: 100vh;
                background: var(--bg-body);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
            .showcase-panel:hover .showcase-bg-img {
                transform: none;
            }
        }
    </style>
</head>
<body>

<div class="login-layout">

    <!-- LEFT PANEL: ARCHITECTURAL LUXURY (Nexora / NUTRILINK Aesthetic) -->
    <div class="showcase-panel">
        <img src="/images/login-bg.png" alt="Diseño arquitectónico abstracto" class="showcase-bg-img">
        <div class="showcase-overlay"></div>
        <div class="showcase-right-edge-fade"></div>

        <!-- Top Brand Header -->
        <div class="showcase-header">
            <div class="showcase-logo-icon">
                <i class="fa-solid fa-cubes-stacked"></i>
            </div>
            <div class="showcase-logo-text">NUTRILINK</div>
        </div>

        <!-- Bottom Welcome Tagline & Copyright -->
        <div class="showcase-footer">
            <div class="welcome-quote-box">
                <div class="accent-vertical-bar"></div>
                <div>
                    <h2 class="welcome-headline">Bienvenido<br>de nuevo</h2>
                    <p class="welcome-subline">
                        Plataforma Humanitaria de Gestión y Optimización de Suministros para la Red de Refugios.
                    </p>
                </div>
            </div>

            <div class="showcase-copyright">
                &copy; {{ date('Y') }} NUTRILINK. Todos los derechos reservados.
            </div>
        </div>
    </div>

    <!-- RIGHT PANEL: SLEEK LUXURY LOGIN CARD -->
    <div class="form-panel">

        <!-- Topbar: Dark / Light Mode Toggle Button -->
        <div class="form-panel-topbar">
            <button type="button" class="theme-toggle-btn" id="themeToggleBtn" onclick="toggleTheme()" title="Cambiar tema de color">
                <span class="theme-toggle-icon" id="themeIcon"><i class="fa-solid fa-moon"></i></span>
                <span id="themeText">Modo Oscuro</span>
            </button>
        </div>

        <!-- Central Floating Card -->
        <div class="login-card">

            <!-- Hexagon Badge Top Icon -->
            <div class="card-logo-badge">
                <i class="fa-solid fa-cube"></i>
            </div>

            <h1 class="login-card-title">Iniciar sesión</h1>
            <p class="login-card-subtitle">Inicia sesión en tu cuenta para continuar</p>

            @if ($errors->any())
                <div class="alert alert-danger p-2 px-3 rounded-3 mb-3 d-flex align-items-center gap-2" style="font-size: 0.82rem; background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.3); color: #fca5a5;">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            @if (session('info'))
                <div class="alert alert-info p-2 px-3 rounded-3 mb-3 d-flex align-items-center gap-2" style="font-size: 0.82rem; background: rgba(230, 168, 108, 0.12); border: 1px solid rgba(230, 168, 108, 0.3); color: #e6a86c;">
                    <i class="fa-solid fa-circle-info"></i>
                    <span>{{ session('info') }}</span>
                </div>
            @endif

            <!-- Quick Demo Selectors -->
            <div class="demo-accounts-bar" title="Acceso rápido con un clic para pruebas">
                <span style="font-size: 0.70rem; color: var(--text-sub); align-self: center; margin-right: 2px;">Rápido:</span>
                <button type="button" class="demo-pill" onclick="fillCredentials('admin@refugios.ve', 'Admin1234!')">
                    👑 Admin
                </button>
                <button type="button" class="demo-pill" onclick="fillCredentials('coordinador@refugios.ve', 'Coordinador1234!')">
                    📋 Coordinador
                </button>
                <button type="button" class="demo-pill" onclick="fillCredentials('almacen@refugios.ve', 'Almacen1234!')">
                    📦 Almacenista
                </button>
                <button type="button" class="demo-pill" onclick="fillCredentials('auditor@refugios.ve', 'Auditor1234!')">
                    🛡️ Auditor
                </button>
            </div>

            <form method="POST" action="{{ route('login') }}" autocomplete="on" id="loginForm">
                @csrf

                <!-- Email -->
                <div class="form-group-custom">
                    <div class="form-label-row">
                        <label class="form-label-custom" for="email">Correo electrónico</label>
                    </div>
                    <div class="input-wrapper">
                        <i class="fa-regular fa-envelope input-icon-left"></i>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            value="{{ old('email', 'admin@refugios.ve') }}" 
                            class="sleek-input" 
                            placeholder="nombre@refugios.ve" 
                            required 
                            autocomplete="email"
                            autofocus
                        >
                    </div>
                </div>

                <!-- Password -->
                <div class="form-group-custom">
                    <div class="form-label-row">
                        <label class="form-label-custom" for="password">Contraseña</label>
                        <span class="forgot-link" style="cursor: pointer;" onclick="fillCredentials('admin@refugios.ve', 'Admin1234!')">
                            ¿Olvidó contraseña?
                        </span>
                    </div>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-lock input-icon-left"></i>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="sleek-input" 
                            placeholder="••••••••••••" 
                            required 
                            autocomplete="current-password"
                        >
                        <button type="button" class="btn-toggle-eye" id="togglePasswordBtn" title="Mostrar/Ocultar" aria-label="Mostrar u ocultar contraseña">
                            <i class="fa-regular fa-eye" id="togglePasswordIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- Sede / Refugio Asignado -->
                <div class="form-group-custom mb-4">
                    <div class="form-label-row">
                        <label class="form-label-custom" for="shelter_id">Sede / Refugio Asignado</label>
                        <span style="font-size: 0.68rem; color: var(--amber-accent); opacity: 0.9;">Opcional</span>
                    </div>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-building-shield input-icon-left"></i>
                        <select name="shelter_id" id="shelter_id" class="sleek-input sleek-select">
                            <option value="" selected>🌐 Red Centralizada (Todos los Refugios)</option>
                            <optgroup label="── Centros Humanitarios ──">
                                @foreach($shelters as $s)
                                    <option value="{{ $s->id }}">
                                        [{{ $s->codigo }}] {{ $s->nombre }} — {{ $s->municipio }}
                                    </option>
                                @endforeach
                            </optgroup>
                        </select>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-luxury-login" id="submitBtn">
                    <span>Iniciar sesión</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </button>
            </form>

            <!-- Divider -->
            <div class="or-divider">o bien</div>

            <!-- Guest Mode -->
            <a href="{{ route('guest.enter') }}" class="btn-guest-action">
                <i class="fa-solid fa-compass me-1"></i>
                <span>Explorar como Invitado (Modo Lectura)</span>
            </a>

            <!-- Security footer (Image 1 style) -->
            <div class="card-security-footer">
                <i class="fa-solid fa-shield-halved"></i>
                <span>Tus datos están protegidos</span>
            </div>

        </div>

    </div>

</div>

<script>
    // Theme Switcher Logic
    function applyTheme(theme) {
        document.documentElement.setAttribute('data-bs-theme', theme);
        localStorage.setItem('app-theme', theme);

        const icon = document.getElementById('themeIcon');
        const text = document.getElementById('themeText');
        if (theme === 'light') {
            if (icon) icon.innerHTML = '<i class="fa-solid fa-sun"></i>';
            if (text) text.textContent = 'Modo Claro';
        } else {
            if (icon) icon.innerHTML = '<i class="fa-solid fa-moon"></i>';
            if (text) text.textContent = 'Modo Oscuro';
        }
    }

    function toggleTheme() {
        const currentTheme = document.documentElement.getAttribute('data-bs-theme') || 'dark';
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        applyTheme(newTheme);
    }

    // Initialize toggle button state on load
    document.addEventListener('DOMContentLoaded', () => {
        const savedTheme = localStorage.getItem('app-theme') || 'dark';
        applyTheme(savedTheme);
    });

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

    // Helper to auto-fill credentials for testing
    function fillCredentials(email, password) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = password;
    }
</script>

</body>
</html>