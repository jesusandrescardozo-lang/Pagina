<!DOCTYPE html>
<html lang="es" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'NUTRILINK') }} — @yield('title', 'Gestión Humanitaria')</title>
    
    <!-- Instant Theme Loader (Zero Flash) -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('app-theme') || 'dark';
            document.documentElement.setAttribute('data-bs-theme', savedTheme);
        })();
    </script>

    <!-- CSS Dependencies -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@500;700&family=Outfit:wght@400;500;600;700&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    
    <!-- Scripts -->
    <script type="text/javascript" src="https://unpkg.com/vis-network/standalone/umd/vis-network.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        /* ============================================================
           PALETA OFICIAL — Neutros sobrios + ámbar suave
           Base carbón cálida (sin brillos neón), un único acento ámbar,
           bordes definidos y radios contenidos para un aspecto serio.
           ============================================================ */
        :root {
            color-scheme: dark;

            /* Base neutra */
            --bg-primary: #121519;
            --bg-secondary: #171b20;
            --bg-card: rgba(26, 30, 37, 0.92);
            --bg-card-hover: rgba(31, 36, 44, 0.98);
            --sidebar-bg: #15191f;

            /* Acento único: ámbar suave (identidad de marca) */
            --accent: #d89656;
            --accent-light: #e6a86c;
            --accent-soft: rgba(230, 168, 108, 0.14);

            /* Semáforo de estados — coherente con los colores usados en las vistas */
            --ok: #10b981;
            --ok-soft: rgba(16, 185, 129, 0.13);
            --warn: #e6a86c;
            --warn-soft: rgba(230, 168, 108, 0.13);
            --danger: #f43f5e;
            --danger-soft: rgba(244, 63, 94, 0.13);
            --info: #e6a86c;
            --amber: #e6a86c;
            --rose: #f43f5e;
            --purple: #d89656;

            /* Texto y líneas */
            --text-primary: #e7e9ee;
            --text-muted: rgba(206, 212, 220, 0.62);
            --border: rgba(148, 163, 184, 0.13);
            --border-hover: rgba(148, 163, 184, 0.24);
            --border-accent: rgba(230, 168, 108, 0.40);

            /* Tokens semánticos de texto — cada tema define su propio contraste */
            --ok-text: #6ee7b7;
            --warn-text: #facc15;
            --danger-text: #fb7185;

            /* Superficies discretas (chips, celdas, avatares) */
            --surface-quiet: rgba(255, 255, 255, 0.06);
            --surface-quiet-hover: rgba(255, 255, 255, 0.10);
            --quiet-border: rgba(255, 255, 255, 0.12);

            /* Colores nativos de Bootstrap alineados a la paleta carbón */
            --bs-body-bg: #121519;
            --bs-body-color: #e7e9ee;
            --bs-emphasis-color: #e7e9ee;
            --bs-heading-color: #e7e9ee;
            --bs-muted-color: rgba(206, 212, 220, 0.62);
            --bs-border-color: rgba(148, 163, 184, 0.13);
            --bs-secondary-bg: #171b20;
            --bs-tertiary-bg: #15191f;

            /* Métricas compartidas */
            --sidebar-width: 264px;
            --radius-sm: 8px;
            --radius: 12px;
            --radius-lg: 16px;
            --shadow-card: 0 1px 2px rgba(0, 0, 0, 0.35), 0 8px 24px rgba(0, 0, 0, 0.28);
            --shadow-raised: 0 2px 6px rgba(0, 0, 0, 0.4), 0 14px 34px rgba(0, 0, 0, 0.32);
            --transition: 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* ============================================================
           MODO CLARO — paleta gris sobria (sin blancos puros)
           Mantiene la misma estructura de contraste del modo oscuro:
           fondos grises por niveles, ámbar apagado para texto/acentos
           y semáforo de estados oscurecido para legibilidad (AA).
           ============================================================ */
        [data-bs-theme="light"] {
            color-scheme: light;

            /* Base gris — niveles de elevación sin brillo */
            --bg-primary: #d9dce0;   /* página */
            --bg-secondary: #cfd2d7; /* paneles, cabeceras de tabla, inputs */
            --bg-card: #e6e8eb;      /* tarjetas */
            --bg-card-hover: #eef0f2;
            --sidebar-bg: #d2d5da;   /* barra lateral: un paso más oscura que la página */

            /* Acento: ámbar apagado */
            --accent: #b87b3f;       /* rellenos sólidos (texto oscuro encima) */
            --accent-light: #8f5c1f; /* texto e íconos con contraste sobre gris */
            --accent-soft: rgba(143, 92, 31, 0.12);

            /* Semáforo de estados — mismo árbol de color, tonos apagados */
            --ok: #0f7a5d;
            --ok-soft: rgba(15, 122, 93, 0.12);
            --warn: #8f5c1f;
            --warn-soft: rgba(143, 92, 31, 0.12);
            --danger: #c02446;
            --danger-soft: rgba(192, 36, 70, 0.11);
            --info: #8f5c1f;
            --amber: #8f5c1f;
            --rose: #c02446;
            --purple: #8f5c1f;

            /* Texto — contraste fuerte sobre gris (respalda al modo oscuro) */
            --text-primary: #1d2126;
            --text-muted: #5b636d;
            --border: rgba(29, 33, 38, 0.16);
            --border-hover: rgba(29, 33, 38, 0.30);
            --border-accent: rgba(143, 92, 31, 0.45);

            /* Tokens semánticos de texto */
            --ok-text: #0b6b4f;
            --warn-text: #8a5a1f;
            --danger-text: #b31740;

            /* Superficies discretas */
            --surface-quiet: rgba(29, 33, 38, 0.06);
            --surface-quiet-hover: rgba(29, 33, 38, 0.10);
            --quiet-border: rgba(29, 33, 38, 0.16);

            /* Sombras más suaves sobre gris */
            --shadow-card: 0 1px 2px rgba(15, 23, 42, 0.08), 0 6px 18px rgba(15, 23, 42, 0.07);
            --shadow-raised: 0 2px 6px rgba(15, 23, 42, 0.10), 0 14px 30px rgba(15, 23, 42, 0.11);

            /* Alinea los colores nativos de Bootstrap (menús, modales, tooltips)
               con la escala gris en lugar del blanco puro */
            --bs-body-bg: #e6e8eb;
            --bs-body-color: #1d2126;
            --bs-emphasis-color: #1d2126;
            --bs-heading-color: #1d2126;
            --bs-muted-color: #5b636d;
            --bs-border-color: rgba(29, 33, 38, 0.16);
            --bs-secondary-bg: #d9dce0;
            --bs-tertiary-bg: #cfd2d7;
        }

        /* Unificación global de clases auxiliares con la paleta ámbar */
        .text-muted {
            color: var(--text-muted) !important;
        }

        .text-info {
            color: var(--accent-light) !important;
        }
        .border-info {
            border-color: var(--border-accent) !important;
        }
        .bg-info {
            background-color: var(--accent) !important;
            color: #fff !important;
        }
        .badge.bg-info {
            background: rgba(230, 168, 108, 0.15) !important;
            color: var(--accent-light) !important;
            border: 1px solid rgba(230, 168, 108, 0.35) !important;
        }
        .btn-info {
            background: var(--accent) !important;
            border-color: var(--accent) !important;
            color: #1a0e04 !important;
        }
        .alert-info {
            background: rgba(230, 168, 108, 0.12) !important;
            border-color: rgba(230, 168, 108, 0.30) !important;
            color: var(--accent-light) !important;
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background-color: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            line-height: 1.55;
            transition: background-color var(--transition), color var(--transition);
        }

        /* Theme Toggle Button in Topbar */
        .theme-switch-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--bg-card);
            border: 1px solid var(--border);
            padding: 5px 12px 5px 6px;
            border-radius: 9999px;
            font-size: 0.74rem;
            font-weight: 600;
            color: var(--text-primary);
            cursor: pointer;
            transition: border-color var(--transition), background-color var(--transition);
            user-select: none;
        }

        [data-bs-theme="light"] .theme-switch-pill {
            background: var(--bg-card);
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.08);
        }

        .theme-switch-pill:hover {
            border-color: var(--border-accent);
        }

        .theme-switch-indicator {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: var(--accent);
            color: #1a0e04;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.72rem;
            transition: transform var(--transition);
        }

        [data-bs-theme="light"] .theme-switch-indicator {
            background: var(--accent-light);
            color: #ffffff;
        }

        /* SIDEBAR (fijo, sobrio, sin glassmorphism extremo) */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            height: 100dvh;
            background: var(--sidebar-bg);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 1000;
            box-shadow: 1px 0 0 rgba(0, 0, 0, 0.15);
            transition: transform var(--transition), background-color var(--transition);
        }

        .sidebar-logo {
            padding: 16px 18px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-shrink: 0;
        }

        .sidebar-logo-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .logo-icon {
            width: 38px;
            height: 38px;
            border-radius: var(--radius);
            background: var(--accent);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.05rem;
            color: #1a0e04;
            flex-shrink: 0;
        }

        .sidebar-logo-text h5 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.02rem;
            font-weight: 700;
            color: var(--text-primary);
            letter-spacing: -0.2px;
            margin: 0;
            line-height: 1.2;
        }

        .sidebar-logo-text small {
            font-size: 0.64rem;
            color: var(--accent-light);
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            display: block;
        }

        /* Buscador en sidebar */
        .sidebar-search-wrap {
            padding: 10px 14px 6px;
            flex-shrink: 0;
        }

        .sidebar-search-box {
            position: relative;
            display: flex;
            align-items: center;
        }

        .sidebar-search-box i {
            position: absolute;
            left: 11px;
            color: var(--text-muted);
            font-size: 0.78rem;
            pointer-events: none;
        }

        .sidebar-search-input {
            width: 100%;
            background: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 7px 32px 7px 32px;
            color: var(--text-primary);
            font-size: 0.78rem;
            outline: none;
            transition: border-color var(--transition), background-color var(--transition);
        }

        .sidebar-search-input:focus {
            background: var(--bg-card-hover);
            border-color: var(--border-accent);
        }

        .sidebar-search-input::placeholder {
            color: var(--text-muted);
        }

        .search-shortcut-badge {
            position: absolute;
            right: 8px;
            font-size: 0.62rem;
            padding: 2px 5px;
            border-radius: 5px;
            background: var(--bg-card);
            color: var(--text-muted);
            border: 1px solid var(--border);
            font-family: monospace;
            pointer-events: none;
        }

        /* Lista de Navegación */
        .sidebar-nav {
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 8px 12px 14px;
            scrollbar-width: thin;
            scrollbar-color: rgba(230, 168, 108, 0.25) transparent;
        }

        .sidebar-nav::-webkit-scrollbar { width: 4px; }
        .sidebar-nav::-webkit-scrollbar-track { background: transparent; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(230, 168, 108, 0.25); border-radius: 4px; }

        .sidebar-section-group {
            margin-bottom: 12px;
        }

        .sidebar-section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 10px 4px;
            cursor: pointer;
            user-select: none;
        }

        .sidebar-section-label {
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: var(--text-muted);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .sidebar-section-toggle {
            font-size: 0.58rem;
            color: var(--text-muted);
            opacity: 0.7;
            transition: transform 0.2s ease;
        }

        .sidebar-section-group.is-collapsed .sidebar-section-toggle {
            transform: rotate(-90deg);
        }

        .sidebar-section-group.is-collapsed .sidebar-section-items {
            display: none;
        }

        .sidebar-nav .nav-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 11px;
            border-radius: var(--radius);
            color: var(--text-muted);
            font-size: 0.82rem;
            font-weight: 500;
            text-decoration: none;
            transition: background-color var(--transition), color var(--transition), border-color var(--transition);
            margin-bottom: 3px;
            border: 1px solid transparent;
        }

        .nav-link-content {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-icon {
            width: 28px;
            height: 28px;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--surface-quiet);
            font-size: 0.82rem;
            flex-shrink: 0;
            color: var(--text-muted);
            transition: background-color var(--transition), color var(--transition);
        }

        .sidebar-nav .nav-link:hover {
            color: var(--text-primary);
            background: var(--surface-quiet-hover);
            border-color: var(--border);
        }

        .sidebar-nav .nav-link:hover .nav-icon {
            background: var(--accent-soft);
            color: var(--accent-light);
        }

        .sidebar-nav .nav-link.active {
            color: var(--text-primary);
            background: var(--accent-soft);
            border-color: var(--border-accent);
            font-weight: 600;
        }

        .sidebar-nav .nav-link.active .nav-icon {
            background: var(--accent);
            color: #1a0e04;
        }

        .nav-pill-badge {
            font-size: 0.65rem;
            padding: 2px 7px;
            border-radius: 6px;
            font-weight: 700;
        }

        /* Sidebar Footer */
        .sidebar-footer {
            flex-shrink: 0;
            padding: 12px 14px;
            border-top: 1px solid var(--border);
            background: var(--sidebar-bg);
        }

        .sidebar-user {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            border-radius: 12px;
            background: var(--surface-quiet);
            border: 1px solid var(--border);
            margin-bottom: 8px;
        }

        .user-avatar {
            width: 34px;
            height: 34px;
            border-radius: var(--radius);
            background: var(--accent);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.88rem;
            font-weight: 700;
            color: #1a0e04;
            flex-shrink: 0;
        }

        .user-name {
            font-size: 0.80rem;
            font-weight: 600;
            color: var(--text-primary);
            line-height: 1.2;
        }

        .user-role {
            font-size: 0.66rem;
            color: var(--text-muted);
            text-transform: capitalize;
        }

        .btn-logout {
            width: 100%;
            padding: 7px 10px;
            border-radius: var(--radius-sm);
            background: var(--danger-soft);
            border: 1px solid rgba(244, 63, 94, 0.26);
            color: var(--danger-text);
            font-size: 0.78rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: background-color var(--transition), border-color var(--transition);
        }

        .btn-logout:hover {
            background: rgba(244, 63, 94, 0.2);
            border-color: rgba(244, 63, 94, 0.42);
            color: var(--danger-text);
        }

        /* MAIN CONTENT AREA */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Topbar */
        .topbar {
            padding: 14px 28px;
            border-bottom: 1px solid var(--border);
            background: rgba(18, 21, 25, 0.92);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            position: sticky;
            top: 0;
            z-index: 900;
        }

        [data-bs-theme="light"] .topbar {
            background: rgba(210, 213, 218, 0.95);
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .greeting-badge {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .greeting-text {
            font-size: 0.96rem;
            font-weight: 700;
            color: var(--text-primary);
            line-height: 1.2;
        }

        .live-clock-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--bg-card);
            border: 1px solid var(--border);
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 0.72rem;
            color: var(--text-muted);
            font-family: 'JetBrains Mono', monospace;
        }

        .live-clock-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background-color: var(--accent);
            animation: pulse-dot 2s ease-in-out infinite;
        }

        @keyframes pulse-dot {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.45; }
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        /* Shelter Selector */
        .shelter-select-wrap {
            display: flex;
            align-items: center;
            padding: 4px 10px;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            transition: border-color var(--transition);
        }

        .shelter-select-wrap:focus-within {
            border-color: var(--border-accent);
        }

        .shelter-select-wrap select {
            background: transparent;
            color: var(--text-primary);
            border: none;
            font-size: 0.78rem;
            font-weight: 600;
            outline: none;
            cursor: pointer;
            max-width: 280px;
        }

        .shelter-select-wrap select option,
        .shelter-select-wrap select optgroup {
            background: var(--bg-secondary);
            color: var(--text-primary);
        }

        .system-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.72rem;
            background: var(--accent-soft);
            border: 1px solid var(--border-accent);
            color: var(--accent-light);
            padding: 5px 12px;
            border-radius: 9999px;
            font-weight: 600;
            letter-spacing: 0.3px;
        }

        /* Guest banner */
        .guest-banner {
            background: var(--warn-soft);
            border-bottom: 1px solid var(--border-accent);
            padding: 10px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .guest-banner-text {
            font-size: 0.82rem;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .badge-guest {
            background: var(--accent);
            color: #1a0e04;
            font-weight: 800;
            font-size: 0.68rem;
            padding: 2px 7px;
            border-radius: 6px;
            text-transform: uppercase;
        }

        .guest-banner-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-guest-login {
            background: var(--accent);
            color: #1a0e04;
            font-weight: 700;
            font-size: 0.78rem;
            padding: 5px 14px;
            border-radius: var(--radius-sm);
            text-decoration: none;
            transition: background-color var(--transition);
        }

        .btn-guest-login:hover {
            background: var(--accent-light);
            color: #1a0e04;
        }

        .btn-guest-exit {
            background: var(--bg-card);
            color: var(--text-primary);
            font-size: 0.78rem;
            padding: 5px 12px;
            border-radius: var(--radius-sm);
            text-decoration: none;
            border: 1px solid var(--border);
        }

        .main-content {
            padding: 26px 30px;
            flex: 1;
        }

        /* CARDS & UTILIDADES */
        .glass-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            transition: border-color var(--transition), box-shadow var(--transition), background-color var(--transition);
            box-shadow: var(--shadow-card);
            position: relative;
        }

        .glass-card:hover {
            border-color: var(--border-hover);
            box-shadow: var(--shadow-raised);
        }

        /* Encabezado de sección (usado en todos los submódulos) */
        .cockpit-page-header {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 22px 26px;
            margin-bottom: 24px;
            box-shadow: var(--shadow-card);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
            position: relative;
            overflow: hidden;
        }

        .cockpit-header-title {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.32rem;
            font-weight: 700;
            color: var(--text-primary);
            letter-spacing: -0.3px;
            margin-bottom: 4px;
        }

        .cockpit-header-subtitle {
            font-size: 0.80rem;
            color: var(--text-muted);
            margin: 0;
        }

        .section-heading {
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 7px;
        }

        /* TABLAS */
        .dark-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .dark-table thead th {
            font-size: 0.70rem;
            font-weight: 700;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            color: var(--text-muted);
            padding: 13px 16px;
            background: var(--bg-secondary);
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }

        .dark-table tbody td {
            padding: 13px 16px;
            font-size: 0.84rem;
            color: var(--text-primary);
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
            background: transparent;
            transition: background var(--transition);
        }

        .dark-table tbody tr:hover td {
            background: var(--accent-soft);
        }

        .dark-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* BADGES */
        .bg-glass {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 11px;
            border-radius: 9999px;
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.3px;
        }

        .bg-ok    { background: var(--ok-soft); color: var(--ok-text); border: 1px solid rgba(16, 185, 129, 0.30); }
        .bg-warn  { background: var(--warn-soft); color: var(--amber); border: 1px solid rgba(230, 168, 108, 0.34); }
        .bg-err   { background: var(--danger-soft); color: var(--danger-text); border: 1px solid rgba(244, 63, 94, 0.32); }
        .bg-info2 { background: var(--warn-soft); color: var(--amber); border: 1px solid rgba(230, 168, 108, 0.34); }
        .bg-amber { background: var(--warn-soft); color: var(--amber); border: 1px solid rgba(230, 168, 108, 0.34); }
        .bg-purple{ background: var(--accent-soft); color: var(--amber); border: 1px solid rgba(216, 150, 86, 0.30); }

        /* Bordes de los badges semánticos: se adaptan al tema */
        [data-bs-theme="light"] .bg-ok    { border-color: rgba(15, 122, 93, 0.35); }
        [data-bs-theme="light"] .bg-warn,
        [data-bs-theme="light"] .bg-info2,
        [data-bs-theme="light"] .bg-amber { border-color: rgba(143, 92, 31, 0.38); }
        [data-bs-theme="light"] .bg-err   { border-color: rgba(192, 36, 70, 0.35); }
        [data-bs-theme="light"] .bg-purple{ border-color: rgba(143, 92, 31, 0.35); }

        /* BUTTONS */
        .btn-accent {
            background: var(--accent);
            border: 1px solid transparent;
            color: #1a0e04;
            font-weight: 700;
            font-size: 0.84rem;
            padding: 9px 18px;
            border-radius: var(--radius);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.28);
            transition: background-color var(--transition), box-shadow var(--transition), transform var(--transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            cursor: pointer;
            text-decoration: none;
            white-space: nowrap;
        }

        .btn-accent:hover {
            background: var(--accent-light);
            color: #1a0e04;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.32);
        }

        .btn-accent:active {
            transform: translateY(1px);
            box-shadow: none;
        }

        .btn-glass {
            background: var(--bg-card);
            border: 1px solid var(--border);
            color: var(--text-primary);
            font-weight: 500;
            font-size: 0.82rem;
            padding: 8px 16px;
            border-radius: var(--radius);
            transition: background-color var(--transition), border-color var(--transition), color var(--transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            cursor: pointer;
            text-decoration: none;
            white-space: nowrap;
        }

        .btn-glass:hover {
            background: var(--bg-card-hover);
            border-color: var(--border-hover);
            color: var(--text-primary);
        }

        /* STAT CARDS */
        .stat-card {
            padding: 22px;
            border-radius: var(--radius-lg);
            background: var(--bg-card);
            border: 1px solid var(--border);
            position: relative;
            overflow: hidden;
            transition: transform var(--transition), box-shadow var(--transition), border-color var(--transition);
            box-shadow: var(--shadow-card);
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-raised);
            border-color: var(--border-hover);
        }

        .stat-icon {
            width: 44px; height: 44px;
            border-radius: var(--radius);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem; margin-bottom: 12px;
        }

        .stat-card.c-blue   .stat-icon { background: var(--accent-soft); color: var(--amber); }
        .stat-card.c-green  .stat-icon { background: var(--ok-soft); color: var(--ok-text); }
        .stat-card.c-amber  .stat-icon { background: var(--accent-soft); color: var(--amber); }
        .stat-card.c-red    .stat-icon { background: var(--danger-soft); color: var(--danger-text); }
        .stat-card.c-purple .stat-icon { background: var(--accent-soft); color: var(--amber); }

        .stat-label {
            font-size: 0.70rem; font-weight: 700; letter-spacing: 0.8px;
            text-transform: uppercase; color: var(--text-muted); margin-bottom: 4px;
        }

        .stat-value {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 2.1rem; font-weight: 800; line-height: 1.1; color: var(--text-primary);
            letter-spacing: -0.5px;
        }

        /* FORMS */
        .form-control-glass, .form-select-glass {
            background: var(--bg-secondary) !important;
            border: 1px solid var(--border) !important;
            color: var(--text-primary) !important;
            border-radius: var(--radius) !important;
            padding: 11px 15px !important;
            font-size: 0.88rem !important;
            transition: border-color var(--transition), background-color var(--transition) !important;
        }

        .form-control-glass:focus, .form-select-glass:focus {
            background: var(--bg-card-hover) !important;
            border-color: var(--border-accent) !important;
            box-shadow: 0 0 0 3px var(--accent-soft) !important;
            outline: none !important;
            color: var(--text-primary) !important;
        }

        .form-control-glass::placeholder { color: var(--text-muted) !important; }
        .form-label-glass {
            font-size: 0.72rem; font-weight: 700; letter-spacing: 0.6px; text-transform: uppercase;
            color: var(--text-muted); margin-bottom: 6px; display: block;
        }

        .progress-glass {
            height: 7px;
            border-radius: 4px;
            background: var(--surface-quiet-hover);
            overflow: hidden;
        }

        .progress-glass .bar {
            height: 100%;
            border-radius: 4px;
            transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .bar-accent { background: var(--accent); }
        .bar-success { background: var(--ok); }
        .bar-warning { background: var(--amber); }
        .bar-danger  { background: var(--danger); }

        /* ============================================================
           MODO CLARO — ajustes por componente sobre la escala gris
           ============================================================ */
        [data-bs-theme="light"] .sidebar {
            box-shadow: 1px 0 0 rgba(15, 23, 42, 0.10);
            border-right-color: rgba(29, 33, 38, 0.20);
        }

        /* Superficies: gris claro en lugar de blanco puro */
        [data-bs-theme="light"] .shelter-select-wrap,
        [data-bs-theme="light"] .live-clock-pill,
        [data-bs-theme="light"] .theme-switch-pill,
        [data-bs-theme="light"] .btn-glass,
        [data-bs-theme="light"] .glass-card,
        [data-bs-theme="light"] .cockpit-page-header,
        [data-bs-theme="light"] .stat-card,
        [data-bs-theme="light"] .sidebar-user {
            background: var(--bg-card);
        }

        [data-bs-theme="light"] .btn-glass:hover,
        [data-bs-theme="light"] .glass-card:hover,
        [data-bs-theme="light"] .stat-card:hover {
            background: var(--bg-card-hover);
        }

        [data-bs-theme="light"] .theme-switch-pill {
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.08);
        }

        [data-bs-theme="light"] .dark-table thead th {
            background: var(--bg-secondary);
            color: var(--text-muted);
            border-bottom-color: var(--border-hover);
        }

        [data-bs-theme="light"] .dark-table tbody tr:hover td {
            background: var(--accent-soft);
        }

        [data-bs-theme="light"] .form-control-glass,
        [data-bs-theme="light"] .form-select-glass,
        [data-bs-theme="light"] .form-control-glass:focus,
        [data-bs-theme="light"] .form-select-glass:focus {
            background: var(--bg-secondary) !important;
        }

        [data-bs-theme="light"] .form-control-glass:focus,
        [data-bs-theme="light"] .form-select-glass:focus {
            background: var(--bg-card) !important;
        }

        /* Etiquetas "texto blanco": en modo claro pasan a tinta oscura
           (las que viven sobre fondos de color se excluyen) */
        [data-bs-theme="light"] .text-white:not(.badge):not(.bg-dark):not(.bg-danger):not(.bg-success):not(.bg-warning):not(.bg-primary) {
            color: var(--text-primary) !important;
        }

        [data-bs-theme="light"] .btn-close-white {
            filter: brightness(0);
            opacity: 0.6;
        }

        /* Botones de relleno: texto oscuro sobre ámbar */
        [data-bs-theme="light"] .btn-info,
        [data-bs-theme="light"] .bg-info {
            color: #1a0e04 !important;
        }

        [data-bs-theme="light"] .badge.bg-info,
        [data-bs-theme="light"] .alert-info {
            color: var(--accent-light) !important;
        }

        /* Íconos de navegación y etiquetas de sección sobre gris */
        [data-bs-theme="light"] .nav-icon {
            background: rgba(29, 33, 38, 0.07);
        }

        [data-bs-theme="light"] .sidebar-nav .nav-link:hover .nav-icon {
            background: var(--accent-soft);
        }

        [data-bs-theme="light"] .guest-banner {
            background: var(--warn-soft);
            border-bottom-color: var(--border-accent);
        }

        [data-bs-theme="light"] .badge-guest,
        [data-bs-theme="light"] .btn-guest-login {
            color: #1a0e04;
        }

        [data-bs-theme="light"] .btn-logout {
            color: var(--danger-text);
            background: var(--danger-soft);
            border-color: rgba(192, 36, 70, 0.30);
        }

        [data-bs-theme="light"] .btn-logout:hover {
            background: rgba(192, 36, 70, 0.16);
            border-color: rgba(192, 36, 70, 0.45);
            color: var(--danger-text);
        }

        [data-bs-theme="light"] .sidebar-footer {
            background: var(--sidebar-bg);
            border-top-color: var(--border-hover);
        }

        /* Barra de desplazamiento del menú, acorde al tema */
        [data-bs-theme="light"] .sidebar-nav {
            scrollbar-color: rgba(143, 92, 31, 0.35) transparent;
        }

        [data-bs-theme="light"] .sidebar-nav::-webkit-scrollbar-thumb {
            background: rgba(143, 92, 31, 0.35);
        }

        /* Accesibilidad: respeta la preferencia de movimiento reducido */
        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
                scroll-behavior: auto !important;
            }
        }

        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.mobile-open {
                transform: translateX(0);
            }
            .main-wrapper {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>

@php
    $isGuestMode = session('guest_mode', false) && !auth()->check();
    $userName = auth()->check() ? auth()->user()->name : 'Invitado';
    $firstName = explode(' ', trim($userName))[0];
@endphp

<!-- Sidebar Navigation -->
<nav class="sidebar" id="appSidebar">
    <!-- Logo Header -->
    <div class="sidebar-logo">
        <a href="{{ route('dashboard') }}" class="sidebar-logo-brand">
            <div class="logo-icon">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>
            <div class="sidebar-logo-text">
                <h5>NUTRILINK</h5>
                <small>Gestión Humanitaria</small>
            </div>
        </a>
    </div>

    <!-- Quick Search Input -->
    <div class="sidebar-search-wrap">
        <div class="sidebar-search-box">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="sidebarSearchInput" class="sidebar-search-input" placeholder="Buscar módulo o insumo..." autocomplete="off">
            <span class="search-shortcut-badge">Ctrl K</span>
        </div>
    </div>

    <!-- Grouped Sections -->
    <div class="sidebar-nav" id="sidebarNav">

        <!-- 1. CENTRO DE CONTROL -->
        <div class="sidebar-section-group" data-section="control">
            <div class="sidebar-section-header" onclick="toggleSidebarSection(this)">
                <span class="sidebar-section-label">
                    <i class="fa-solid fa-compass" style="color: var(--amber);"></i> Centro de Control
                </span>
                <i class="fa-solid fa-chevron-down sidebar-section-toggle"></i>
            </div>
            <div class="sidebar-section-items">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <div class="nav-link-content">
                        <span class="nav-icon"><i class="fa-solid fa-chart-pie"></i></span>
                        <span class="nav-text">Dashboard I.O.</span>
                    </div>
                    <span class="nav-pill-badge bg-info2">En vivo</span>
                </a>
            </div>
        </div>

        <!-- 2. GESTIÓN HUMANITARIA -->
        @if($isGuestMode || (auth()->check() && (auth()->user()->canAccessShelters() || auth()->user()->canAccessSupplies())))
        <div class="sidebar-section-group" data-section="humanitaria">
            <div class="sidebar-section-header" onclick="toggleSidebarSection(this)">
                <span class="sidebar-section-label">
                    <i class="fa-solid fa-people-roof" style="color: var(--amber);"></i> Gestión Humanitaria
                </span>
                <i class="fa-solid fa-chevron-down sidebar-section-toggle"></i>
            </div>
            <div class="sidebar-section-items">
                @if($isGuestMode || auth()->user()->canAccessShelters())
                <a href="{{ route('shelters.index') }}" class="nav-link {{ request()->routeIs('shelters.*') ? 'active' : '' }}">
                    <div class="nav-link-content">
                        <span class="nav-icon"><i class="fa-solid fa-tents"></i></span>
                        <span class="nav-text">Refugios y Albergues</span>
                    </div>
                </a>
                @endif

                @if($isGuestMode || auth()->user()->canAccessSupplies())
                <a href="{{ route('supplies.index') }}" class="nav-link {{ request()->routeIs('supplies.*') ? 'active' : '' }}">
                    <div class="nav-link-content">
                        <span class="nav-icon"><i class="fa-solid fa-boxes-packing"></i></span>
                        <span class="nav-text">Insumos y Alimentos</span>
                    </div>
                </a>
                @endif
            </div>
        </div>
        @endif

        <!-- 3. LOGÍSTICA & ENVÍOS -->
        @if($isGuestMode || (auth()->check() && (auth()->user()->canAccessMovements() || auth()->user()->canAccessTransfers())))
        <div class="sidebar-section-group" data-section="logistica">
            <div class="sidebar-section-header" onclick="toggleSidebarSection(this)">
                <span class="sidebar-section-label">
                    <i class="fa-solid fa-truck-ramp-box" style="color: var(--amber);"></i> Logística &amp; Envíos
                </span>
                <i class="fa-solid fa-chevron-down sidebar-section-toggle"></i>
            </div>
            <div class="sidebar-section-items">
                @if($isGuestMode || auth()->user()->canAccessMovements())
                <a href="{{ route('movements.index') }}" class="nav-link {{ request()->routeIs('movements.*') ? 'active' : '' }}">
                    <div class="nav-link-content">
                        <span class="nav-icon"><i class="fa-solid fa-clipboard-check"></i></span>
                        <span class="nav-text">Kardex / Movimientos</span>
                    </div>
                </a>
                @endif

                @if($isGuestMode || auth()->user()->canAccessTransfers())
                <a href="{{ route('transfers.index') }}" class="nav-link {{ request()->routeIs('transfers.*') ? 'active' : '' }}">
                    <div class="nav-link-content">
                        <span class="nav-icon"><i class="fa-solid fa-arrows-split-up-and-left"></i></span>
                        <span class="nav-text">Rebalanceo Inter-Refugios</span>
                    </div>
                </a>
                @endif
            </div>
        </div>
        @endif

        <!-- 4. HERRAMIENTAS ESPECIALIZADAS -->
        @if($isGuestMode || (auth()->check() && (auth()->user()->canAccessReports() || auth()->user()->canAccessAudit() || auth()->user()->canAccessCarnets())))
        <div class="sidebar-section-group" data-section="herramientas">
            <div class="sidebar-section-header" onclick="toggleSidebarSection(this)">
                <span class="sidebar-section-label">
                    <i class="fa-solid fa-brain" style="color: var(--amber);"></i> Herramientas I.O. &amp; Control
                </span>
                <i class="fa-solid fa-chevron-down sidebar-section-toggle"></i>
            </div>
            <div class="sidebar-section-items">
                @if(!$isGuestMode && auth()->check() && auth()->user()->canAccessCarnets())
                <a href="{{ route('carnets.index') }}" class="nav-link {{ request()->routeIs('carnets.*') ? 'active' : '' }}">
                    <div class="nav-link-content">
                        <span class="nav-icon"><i class="fa-solid fa-id-card-clip"></i></span>
                        <span class="nav-text">Personal &amp; Credenciales</span>
                    </div>
                </a>
                @endif

                @if($isGuestMode || (auth()->check() && auth()->user()->canAccessReports()))
                <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                    <div class="nav-link-content">
                        <span class="nav-icon"><i class="fa-solid fa-file-pdf"></i></span>
                        <span class="nav-text">Reportes Oficiales</span>
                    </div>
                </a>
                @endif

                @if(!$isGuestMode && auth()->check() && auth()->user()->canAccessAudit())
                <a href="{{ route('audit.index') }}" class="nav-link {{ request()->routeIs('audit.*') ? 'active' : '' }}">
                    <div class="nav-link-content">
                        <span class="nav-icon"><i class="fa-solid fa-shield-halved"></i></span>
                        <span class="nav-text">Auditoría / Bitácora</span>
                    </div>
                </a>
                @endif
            </div>
        </div>
        @endif

    </div>

    <!-- User Profile Footer -->
    <div class="sidebar-footer">
        @if($isGuestMode)
            <div class="sidebar-user" style="border-color: rgba(245, 158, 11, 0.35); background: rgba(245, 158, 11, 0.08);">
                <div class="user-avatar" style="background: var(--accent);"><i class="fa-solid fa-eye" style="font-size:0.85rem;"></i></div>
                <div style="flex:1; overflow:hidden;">
                    <div class="user-name text-truncate" style="color: var(--amber);">Modo Invitado</div>
                    <div class="user-role" style="color:rgba(251,191,36,0.75);">Exploración / Solo Lectura</div>
                </div>
            </div>
            <a href="{{ route('login') }}" class="btn-logout" style="background:rgba(216,150,86,0.15); border-color:rgba(216,150,86,0.35); color: var(--amber); text-decoration:none; margin-bottom:6px;">
                <i class="fa-solid fa-right-to-bracket"></i> Iniciar Sesión
            </a>
            <a href="{{ route('guest.exit') }}" class="btn-logout" style="font-size:0.72rem; opacity:0.7; text-decoration:none;">
                <i class="fa-solid fa-xmark"></i> Salir del modo invitado
            </a>
        @else
            <div class="sidebar-user">
                <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</div>
                <div style="flex:1; overflow:hidden;">
                    <div class="user-name text-truncate d-flex align-items-center justify-content-between">
                        <span class="text-truncate">{{ auth()->user()->name ?? 'Usuario' }}</span>
                        @if(auth()->check())
                            <a href="{{ route('carnets.show', auth()->user()) }}" target="_blank" title="Ver mi credencial oficial" style="color: var(--amber); font-size:0.85rem;" class="ms-1">
                                <i class="fa-solid fa-id-badge"></i>
                            </a>
                        @endif
                    </div>
                    <div class="user-role">{{ str_replace('_', ' ', auth()->user()->rol ?? 'operador') }}</div>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn-logout">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i> Cerrar Sesión
                </button>
            </form>
        @endif
    </div>
</nav>

<!-- Main Wrapper -->
<div class="main-wrapper">
    <!-- Cockpit Topbar -->
    <header class="topbar">
        <!-- Left: Hamburger Móvil & Saludo & Hora en vivo -->
        <div class="topbar-left">
            <button type="button" class="btn btn-sm btn-glass d-lg-none me-2" onclick="document.getElementById('appSidebar').classList.toggle('mobile-open')" aria-label="Abrir Menú">
                <i class="fa-solid fa-bars"></i>
            </button>
            <div class="greeting-badge">
                <span class="fs-4">👋</span>
                <div>
                    <div class="greeting-text" id="greetingLabel">Hola, {{ $firstName }}</div>
                    <div class="live-clock-pill">
                        <span class="live-clock-dot"></span>
                        <span id="liveTimeClock">--:--:--</span>
                        <span>&bull;</span>
                        <span id="liveDateClock">--</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Theme Switcher & Shelter Switcher & Status -->
        <div class="topbar-right">
            <!-- Theme Toggle Button (Oscuro / Claro) -->
            <button type="button" class="theme-switch-pill" id="themeSwitchBtn" onclick="toggleTheme()" title="Alternar Modo Oscuro / Modo Claro" aria-label="Cambiar tema de color">
                <div class="theme-switch-indicator">
                    <i class="fa-solid fa-moon" id="themeSwitchIcon"></i>
                </div>
                <span class="theme-switch-text" id="themeSwitchText">Modo Oscuro</span>
            </button>

            <form action="{{ route('shelter.switch') }}" method="POST" id="topbarShelterForm" class="d-flex align-items-center m-0">
                @csrf
                <div class="shelter-select-wrap">
                    <i class="fa-solid fa-building-shield me-2" style="font-size: 0.85rem; color: var(--amber);"></i>
                    <select name="shelter_id" onchange="document.getElementById('topbarShelterForm').submit()" title="Seleccionar contexto operativo">
                        <option value="">🌐 Red Centralizada (Todos los Refugios)</option>
                        <optgroup label="── Centros / Albergues Humanitarios ──">
                            @foreach(\App\Models\Shelter::orderBy('codigo')->get() as $navShelter)
                                <option value="{{ $navShelter->id }}" {{ session('shelter_id') == $navShelter->id ? 'selected' : '' }}>
                                    [{{ $navShelter->codigo }}] {{ $navShelter->nombre }} — {{ $navShelter->municipio }}
                                </option>
                            @endforeach
                        </optgroup>
                    </select>
                </div>
            </form>

            @if(session('shelter_id'))
                <a href="{{ route('shelter.switch') }}" class="btn-glass py-1 px-2" style="color: var(--amber); font-size: 0.74rem; border-radius: 10px;" title="Restablecer a Red Centralizada">
                    <i class="fa-solid fa-arrows-rotate me-1"></i> Ver Centralizado
                </a>
            @endif

            <div class="system-status-pill">
                <i class="fa-solid fa-signal"></i>
                <span>Red Operativa I.O.</span>
            </div>
        </div>
    </header>

    <!-- Guest Mode Warning Banner -->
    @if($isGuestMode)
    <div class="guest-banner">
        <div class="guest-banner-text">
            <i class="fa-solid fa-eye fs-5"></i>
            <span class="badge-guest"><i class="fa-solid fa-lock-open"></i> Modo Demostración</span>
            <span>Estás explorando el sistema en <strong>Modo Invitado (Solo Lectura)</strong> con datos y métricas en vivo.</span>
        </div>
        <div class="guest-banner-actions">
            <a href="{{ route('login') }}" class="btn-guest-login">
                <i class="fa-solid fa-right-to-bracket me-1"></i> Iniciar Sesión Real
            </a>
            <a href="{{ route('guest.exit') }}" class="btn-guest-exit">
                <i class="fa-solid fa-xmark me-1"></i> Salir
            </a>
        </div>
    </div>
    @endif

    <!-- Content Area -->
    <main class="main-content">
        @if(session('success'))
        <div class="alert alert-success d-flex align-items-center justify-content-between p-3 rounded-4 mb-4" style="background: rgba(16, 185, 129, 0.12); border: 1px solid rgba(16, 185, 129, 0.3); color: var(--ok-text);">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-circle-check fs-5"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close btn-close-white" onclick="this.parentElement.remove()" aria-label="Cerrar"></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger d-flex align-items-center justify-content-between p-3 rounded-4 mb-4" style="background: rgba(244, 63, 94, 0.12); border: 1px solid rgba(244, 63, 94, 0.3); color: var(--danger-text);">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation fs-5"></i>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" class="btn-close btn-close-white" onclick="this.parentElement.remove()" aria-label="Cerrar"></button>
        </div>
        @endif

        @if ($errors->any())
        <div class="alert alert-danger d-flex align-items-start justify-content-between p-3 rounded-4 mb-4" style="background: rgba(244, 63, 94, 0.12); border: 1px solid rgba(244, 63, 94, 0.3); color: var(--danger-text);">
            <div class="d-flex align-items-start gap-2 w-100">
                <i class="fa-solid fa-circle-exclamation fs-5 mt-1"></i>
                <div class="w-100">
                    <strong class="d-block mb-1">Corrija los siguientes errores:</strong>
                    <ul class="mb-0 ps-3" style="font-size: 0.84rem;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button type="button" class="btn-close btn-close-white" onclick="this.parentElement.remove()" aria-label="Cerrar"></button>
        </div>
        @endif

        @yield('content')
    </main>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Live Clock & Time Greeting
    function updateClockAndGreeting() {
        const now = new Date();
        const hours = now.getHours();
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        const ampm = hours >= 12 ? 'PM' : 'AM';
        const formattedHours = hours % 12 || 12;

        const timeString = `${formattedHours}:${minutes}:${seconds} ${ampm}`;
        const clockEl = document.getElementById('liveTimeClock');
        if (clockEl) clockEl.textContent = timeString;

        // Date String
        const options = { weekday: 'short', day: 'numeric', month: 'short' };
        const dateString = now.toLocaleDateString('es-ES', options);
        const dateEl = document.getElementById('liveDateClock');
        if (dateEl) dateEl.textContent = dateString.toUpperCase();

        // Greeting
        let greetingPrefix = 'Hola';
        if (hours >= 5 && hours < 12) {
            greetingPrefix = 'Buenos días';
        } else if (hours >= 12 && hours < 19) {
            greetingPrefix = 'Buenas tardes';
        } else {
            greetingPrefix = 'Buenas noches';
        }

        const greetingLabel = document.getElementById('greetingLabel');
        if (greetingLabel) {
            greetingLabel.textContent = `${greetingPrefix}, {{ $firstName }}`;
        }
    }

    setInterval(updateClockAndGreeting, 1000);
    updateClockAndGreeting();

    // Toggle Section
    function toggleSidebarSection(headerEl) {
        const group = headerEl.closest('.sidebar-section-group');
        if (!group) return;
        group.classList.toggle('is-collapsed');
    }

    // Quick Search Filter
    const searchInput = document.getElementById('sidebarSearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            const q = e.target.value.toLowerCase().trim();
            const navLinks = document.querySelectorAll('.sidebar-nav .nav-link');
            const groups = document.querySelectorAll('.sidebar-section-group');

            if (!q) {
                navLinks.forEach(l => l.style.display = '');
                groups.forEach(g => { g.style.display = ''; g.classList.remove('is-collapsed'); });
                return;
            }

            groups.forEach(g => {
                let hasMatch = false;
                g.querySelectorAll('.nav-link').forEach(link => {
                    const txt = link.textContent.toLowerCase();
                    if (txt.includes(q)) {
                        link.style.display = '';
                        hasMatch = true;
                    } else {
                        link.style.display = 'none';
                    }
                });
                if (hasMatch) {
                    g.style.display = '';
                    g.classList.remove('is-collapsed');
                } else {
                    g.style.display = 'none';
                }
            });
        });

        // Ctrl + K shortcut
        window.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                searchInput.focus();
                searchInput.select();
            }
        });
    }

    // Theme Switcher Engine (Dark / Light Mode)
    function applyTheme(theme) {
        document.documentElement.setAttribute('data-bs-theme', theme);
        localStorage.setItem('app-theme', theme);

        const icon = document.getElementById('themeSwitchIcon');
        const text = document.getElementById('themeSwitchText');
        if (theme === 'light') {
            if (icon) icon.className = 'fa-solid fa-sun';
            if (text) text.textContent = 'Modo Claro';
        } else {
            if (icon) icon.className = 'fa-solid fa-moon';
            if (text) text.textContent = 'Modo Oscuro';
        }
    }

    function toggleTheme() {
        const currentTheme = document.documentElement.getAttribute('data-bs-theme') || 'dark';
        const nextTheme = currentTheme === 'dark' ? 'light' : 'dark';
        applyTheme(nextTheme);
    }

    // Initialize toggle state on document ready
    document.addEventListener('DOMContentLoaded', () => {
        const savedTheme = localStorage.getItem('app-theme') || 'dark';
        applyTheme(savedTheme);
    });
</script>

@stack('scripts')
</body>
</html>
