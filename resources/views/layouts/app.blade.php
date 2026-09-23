<!DOCTYPE html>
<html lang="es" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'NutriLnk') }} — @yield('title', 'Gestión Humanitaria')</title>
    
    <!-- CSS Dependencies -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@500;700&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    
    <!-- Scripts -->
    <script type="text/javascript" src="https://unpkg.com/vis-network/standalone/umd/vis-network.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --bg-primary: #07090e;
            --bg-secondary: #0c101c;
            --bg-card: rgba(18, 24, 38, 0.7);
            --bg-card-hover: rgba(26, 34, 52, 0.85);
            --sidebar-bg: rgba(10, 14, 24, 0.95);
            --accent: #0ea5e9;
            --accent-light: #38bdf8;
            --accent-glow: rgba(14, 165, 233, 0.4);
            --emerald: #10b981;
            --emerald-glow: rgba(16, 185, 129, 0.35);
            --amber: #f59e0b;
            --rose: #f43f5e;
            --purple: #8b5cf6;
            --text-primary: #f8fafc;
            --text-muted: rgba(226, 232, 240, 0.65);
            --border: rgba(255, 255, 255, 0.08);
            --border-hover: rgba(255, 255, 255, 0.16);
            --border-accent: rgba(56, 189, 248, 0.4);
            --sidebar-width: 268px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--bg-primary);
            background-image:
                radial-gradient(ellipse 70% 50% at 15% 0%, rgba(14, 165, 233, 0.14) 0%, transparent 60%),
                radial-gradient(ellipse 60% 45% at 85% 100%, rgba(16, 185, 129, 0.10) 0%, transparent 60%),
                radial-gradient(circle at 50% 50%, rgba(99, 102, 241, 0.04) 0%, transparent 80%);
            background-attachment: fixed;
            color: var(--text-primary);
            min-height: 100vh;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        /* SIDEBAR (Estilo Dock / Glassmorphism) */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            height: 100dvh;
            background: var(--sidebar-bg);
            backdrop-filter: blur(28px) saturate(180%);
            -webkit-backdrop-filter: blur(28px) saturate(180%);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 1000;
            box-shadow: 10px 0 35px rgba(0, 0, 0, 0.5);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar-logo {
            padding: 18px 18px 14px;
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
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 50%, #10b981 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            color: #fff;
            box-shadow: 0 4px 18px var(--accent-glow), inset 0 1px 1px rgba(255, 255, 255, 0.3);
            flex-shrink: 0;
        }

        .sidebar-logo-text h5 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.05rem;
            font-weight: 700;
            color: #fff;
            letter-spacing: -0.3px;
            margin: 0;
            line-height: 1.2;
        }

        .sidebar-logo-text small {
            font-size: 0.65rem;
            color: #38bdf8;
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
            color: rgba(148, 163, 184, 0.6);
            font-size: 0.78rem;
            pointer-events: none;
        }

        .sidebar-search-input {
            width: 100%;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 10px;
            padding: 7px 32px 7px 32px;
            color: #fff;
            font-size: 0.78rem;
            outline: none;
            transition: all 0.2s ease;
        }

        .sidebar-search-input:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(56, 189, 248, 0.5);
            box-shadow: 0 0 14px rgba(14, 165, 233, 0.25);
        }

        .sidebar-search-input::placeholder {
            color: rgba(148, 163, 184, 0.45);
        }

        .search-shortcut-badge {
            position: absolute;
            right: 8px;
            font-size: 0.62rem;
            padding: 2px 5px;
            border-radius: 5px;
            background: rgba(255, 255, 255, 0.07);
            color: rgba(255, 255, 255, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.1);
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
            scrollbar-color: rgba(56, 189, 248, 0.25) transparent;
        }

        .sidebar-nav::-webkit-scrollbar { width: 4px; }
        .sidebar-nav::-webkit-scrollbar-track { background: transparent; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(56, 189, 248, 0.25); border-radius: 4px; }

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
            color: rgba(148, 163, 184, 0.6);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .sidebar-section-toggle {
            font-size: 0.58rem;
            color: rgba(148, 163, 184, 0.45);
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
            border-radius: 11px;
            color: rgba(226, 232, 240, 0.72);
            font-size: 0.82rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
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
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.05);
            font-size: 0.82rem;
            flex-shrink: 0;
            color: rgba(226, 232, 240, 0.7);
            transition: all 0.2s ease;
        }

        .sidebar-nav .nav-link:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.06);
            border-color: rgba(255, 255, 255, 0.08);
            transform: translateX(2px);
        }

        .sidebar-nav .nav-link:hover .nav-icon {
            background: rgba(14, 165, 233, 0.2);
            color: #38bdf8;
        }

        .sidebar-nav .nav-link.active {
            color: #ffffff;
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.25) 0%, rgba(16, 185, 129, 0.12) 100%);
            border-color: rgba(56, 189, 248, 0.4);
            box-shadow: 0 4px 16px rgba(14, 165, 233, 0.18);
            font-weight: 600;
        }

        .sidebar-nav .nav-link.active .nav-icon {
            background: linear-gradient(135deg, #0284c7, #38bdf8);
            color: #fff;
            box-shadow: 0 0 12px var(--accent-glow);
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
            background: rgba(7, 10, 18, 0.98);
        }

        .sidebar-user {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--border);
            margin-bottom: 8px;
        }

        .user-avatar {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--accent), var(--emerald));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.88rem;
            font-weight: 700;
            color: #fff;
            flex-shrink: 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.4);
        }

        .user-name {
            font-size: 0.80rem;
            font-weight: 600;
            color: #fff;
            line-height: 1.2;
        }

        .user-role {
            font-size: 0.66rem;
            color: rgba(148, 163, 184, 0.7);
            text-transform: capitalize;
        }

        .btn-logout {
            width: 100%;
            padding: 7px 10px;
            border-radius: 10px;
            background: rgba(244, 63, 94, 0.1);
            border: 1px solid rgba(244, 63, 94, 0.28);
            color: #fb7185;
            font-size: 0.78rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all 0.2s ease;
        }

        .btn-logout:hover {
            background: rgba(244, 63, 94, 0.2);
            color: #fda4af;
        }

        /* MAIN CONTENT AREA */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Topbar Cockpit */
        .topbar {
            padding: 14px 28px;
            border-bottom: 1px solid var(--border);
            background: rgba(7, 9, 14, 0.78);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            position: sticky;
            top: 0;
            z-index: 900;
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
            color: #ffffff;
            line-height: 1.2;
        }

        .live-clock-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.09);
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 0.72rem;
            color: rgba(226, 232, 240, 0.7);
            font-family: 'JetBrains Mono', monospace;
        }

        .live-clock-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background-color: var(--emerald);
            box-shadow: 0 0 6px var(--emerald);
            animation: pulse-dot 1.5s infinite;
        }

        @keyframes pulse-dot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(0.85); }
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
            background: rgba(15, 21, 35, 0.85);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
            transition: border-color 0.2s;
        }

        .shelter-select-wrap:focus-within {
            border-color: rgba(56, 189, 248, 0.5);
            box-shadow: 0 0 12px rgba(14, 165, 233, 0.2);
        }

        .shelter-select-wrap select {
            background: transparent;
            color: #f8fafc;
            border: none;
            font-size: 0.78rem;
            font-weight: 600;
            outline: none;
            cursor: pointer;
            max-width: 280px;
        }

        .shelter-select-wrap select option,
        .shelter-select-wrap select optgroup {
            background: #0b0f19;
            color: #f1f5f9;
        }

        .system-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.72rem;
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #34d399;
            padding: 5px 12px;
            border-radius: 9999px;
            font-weight: 600;
            letter-spacing: 0.3px;
        }

        /* Guest banner */
        .guest-banner {
            background: linear-gradient(90deg, rgba(245, 158, 11, 0.15) 0%, rgba(217, 119, 6, 0.08) 100%);
            border-bottom: 1px solid rgba(245, 158, 11, 0.35);
            padding: 10px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .guest-banner-text {
            font-size: 0.82rem;
            color: #fde68a;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .badge-guest {
            background: #f59e0b;
            color: #000;
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
            background: #f59e0b;
            color: #000;
            font-weight: 700;
            font-size: 0.78rem;
            padding: 5px 14px;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-guest-login:hover {
            background: #fbbf24;
            color: #000;
            transform: translateY(-1px);
        }

        .btn-guest-exit {
            background: rgba(255, 255, 255, 0.08);
            color: #f1f5f9;
            font-size: 0.78rem;
            padding: 5px 12px;
            border-radius: 8px;
            text-decoration: none;
            border: 1px solid rgba(255, 255, 255, 0.15);
        }

        .main-content {
            padding: 26px 30px;
            flex: 1;
        }

        /* CARDS & GLASS UTILITIES */
        .glass-card {
            background: linear-gradient(145deg, rgba(20, 27, 44, 0.7) 0%, rgba(10, 14, 24, 0.85) 100%);
            backdrop-filter: blur(24px) saturate(190%);
            -webkit-backdrop-filter: blur(24px) saturate(190%);
            border: 1px solid rgba(255, 255, 255, 0.09);
            border-radius: 22px;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 16px 40px rgba(0, 0, 0, 0.55), inset 0 1px 1px rgba(255, 255, 255, 0.14);
            position: relative;
        }

        .glass-card:hover {
            border-color: rgba(255, 255, 255, 0.18);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.65), 0 0 25px rgba(14, 165, 233, 0.12);
        }

        /* Section Cockpit Header Component (Used in all submodules) */
        .cockpit-page-header {
            background: linear-gradient(145deg, rgba(20, 28, 48, 0.75) 0%, rgba(9, 13, 22, 0.9) 100%);
            backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 22px;
            padding: 22px 26px;
            margin-bottom: 24px;
            box-shadow: 0 14px 35px rgba(0, 0, 0, 0.5), inset 0 1px 1px rgba(255, 255, 255, 0.15);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
            position: relative;
            overflow: hidden;
        }

        .cockpit-page-header::after {
            content: '';
            position: absolute;
            top: -40px; right: -40px;
            width: 160px; height: 160px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(14, 165, 233, 0.2) 0%, transparent 70%);
            pointer-events: none;
        }

        .cockpit-header-title {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.35rem;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: -0.3px;
            margin-bottom: 4px;
        }

        .cockpit-header-subtitle {
            font-size: 0.80rem;
            color: rgba(226, 232, 240, 0.65);
            margin: 0;
        }

        .section-heading {
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            color: rgba(226, 232, 240, 0.65);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 7px;
        }

        /* TABLES */
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
            color: rgba(148, 163, 184, 0.85);
            padding: 13px 16px;
            background: rgba(11, 15, 26, 0.8);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            white-space: nowrap;
        }

        .dark-table tbody td {
            padding: 13px 16px;
            font-size: 0.84rem;
            color: var(--text-primary);
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
            vertical-align: middle;
            background: transparent;
            transition: background 0.15s ease;
        }

        .dark-table tbody tr:hover td {
            background: rgba(255, 255, 255, 0.04);
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

        .bg-ok { background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.35); }
        .bg-warn { background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.35); }
        .bg-err { background: rgba(244, 63, 94, 0.15); color: #fb7185; border: 1px solid rgba(244, 63, 94, 0.35); }
        .bg-info2 { background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.35); }
        .bg-purple { background: rgba(139, 92, 246, 0.15); color: #a78bfa; border: 1px solid rgba(139, 92, 246, 0.35); }

        /* BUTTONS */
        .btn-accent {
            background: linear-gradient(135deg, var(--accent), #0284c7);
            border: none;
            color: #fff;
            font-weight: 600;
            font-size: 0.84rem;
            padding: 9px 18px;
            border-radius: 12px;
            box-shadow: 0 4px 16px var(--accent-glow), inset 0 1px 1px rgba(255, 255, 255, 0.25);
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            cursor: pointer;
            text-decoration: none;
            white-space: nowrap;
        }

        .btn-accent:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(14, 165, 233, 0.5), inset 0 1px 2px rgba(255, 255, 255, 0.35);
            color: #fff;
        }

        .btn-glass {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-primary);
            font-weight: 500;
            font-size: 0.82rem;
            padding: 8px 16px;
            border-radius: 12px;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            cursor: pointer;
            text-decoration: none;
            white-space: nowrap;
            box-shadow: inset 0 1px 1px rgba(255, 255, 255, 0.08);
        }

        .btn-glass:hover {
            background: rgba(255, 255, 255, 0.09);
            border-color: rgba(255, 255, 255, 0.2);
            color: #fff;
            transform: translateY(-1px);
        }

        /* STAT CARDS */
        .stat-card {
            padding: 22px;
            border-radius: 20px;
            background: linear-gradient(145deg, rgba(22, 30, 48, 0.7) 0%, rgba(11, 15, 26, 0.85) 100%);
            border: 1px solid rgba(255, 255, 255, 0.09);
            position: relative;
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.45);
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 18px 40px rgba(0, 0, 0, 0.6), 0 0 20px rgba(14, 165, 233, 0.15);
            border-color: rgba(255, 255, 255, 0.18);
        }

        .stat-icon {
            width: 44px; height: 44px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem; margin-bottom: 12px;
        }

        .stat-card.c-blue   .stat-icon { background: rgba(14, 165, 233, 0.15); color: #38bdf8; }
        .stat-card.c-green  .stat-icon { background: rgba(16, 185, 129, 0.15); color: #34d399; }
        .stat-card.c-amber  .stat-icon { background: rgba(245, 158, 11, 0.15); color: #fbbf24; }
        .stat-card.c-red    .stat-icon { background: rgba(244, 63, 94, 0.15); color: #fb7185; }
        .stat-card.c-purple .stat-icon { background: rgba(139, 92, 246, 0.15); color: #a78bfa; }

        .stat-label {
            font-size: 0.70rem; font-weight: 700; letter-spacing: 0.8px;
            text-transform: uppercase; color: rgba(226, 232, 240, 0.6); margin-bottom: 4px;
        }

        .stat-value {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 2.1rem; font-weight: 800; line-height: 1.1; color: #fff;
            letter-spacing: -0.5px;
        }

        /* FORMS */
        .form-control-glass, .form-select-glass {
            background: rgba(11, 15, 25, 0.75) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            color: var(--text-primary) !important;
            border-radius: 12px !important;
            padding: 11px 15px !important;
            font-size: 0.88rem !important;
            transition: all 0.2s ease !important;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.3) !important;
        }

        .form-control-glass:focus, .form-select-glass:focus {
            background: rgba(15, 21, 37, 0.9) !important;
            border-color: rgba(56, 189, 248, 0.6) !important;
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.2), inset 0 2px 4px rgba(0, 0, 0, 0.3) !important;
            outline: none !important;
            color: #fff !important;
        }

        .form-control-glass::placeholder { color: rgba(148, 163, 184, 0.4) !important; }
        .form-label-glass {
            font-size: 0.72rem; font-weight: 700; letter-spacing: 0.6px; text-transform: uppercase;
            color: rgba(226, 232, 240, 0.7); margin-bottom: 6px; display: block;
        }

        .progress-glass {
            height: 7px;
            border-radius: 4px;
            background: rgba(255, 255, 255, 0.07);
            overflow: hidden;
        }

        .progress-glass .bar {
            height: 100%;
            border-radius: 4px;
            transition: width 0.6s ease;
        }

        .bar-accent { background: linear-gradient(90deg, #0284c7, #38bdf8); }
        .bar-success { background: linear-gradient(90deg, #059669, #10b981); }
        .bar-warning { background: linear-gradient(90deg, #d97706, #f59e0b); }
        .bar-danger { background: linear-gradient(90deg, #e11d48, #f43f5e); }

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
                <h5>NutriLnk</h5>
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
                    <i class="fa-solid fa-compass text-info"></i> Centro de Control
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
                    <i class="fa-solid fa-people-roof text-success"></i> Gestión Humanitaria
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
                <a href="{{ route('supplies.index') }}" class="nav-link {{ request()->routeIs('supplies.*') && !request()->routeIs('supplies.calculator') ? 'active' : '' }}">
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
                    <i class="fa-solid fa-truck-ramp-box text-warning"></i> Logística &amp; Envíos
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
        @if($isGuestMode || (auth()->check() && (auth()->user()->canAccessIoModels() || auth()->user()->canAccessCpm() || auth()->user()->canAccessReports() || auth()->user()->canAccessAudit() || auth()->user()->canAccessCarnets())))
        <div class="sidebar-section-group" data-section="herramientas">
            <div class="sidebar-section-header" onclick="toggleSidebarSection(this)">
                <span class="sidebar-section-label">
                    <i class="fa-solid fa-brain text-purple"></i> Herramientas I.O. &amp; Control
                </span>
                <i class="fa-solid fa-chevron-down sidebar-section-toggle"></i>
            </div>
            <div class="sidebar-section-items">
                @if($isGuestMode || (auth()->check() && auth()->user()->canAccessIoModels()))
                <a href="{{ route('supplies.calculator') }}" class="nav-link {{ request()->routeIs('supplies.calculator') ? 'active' : '' }}">
                    <div class="nav-link-content">
                        <span class="nav-icon"><i class="fa-solid fa-calculator"></i></span>
                        <span class="nav-text">Modelos EOQ &amp; Newsvendor</span>
                    </div>
                </a>
                @endif

                @if($isGuestMode || (auth()->check() && auth()->user()->canAccessCpm()))
                <a href="{{ route('cpm.index') }}" class="nav-link {{ request()->routeIs('cpm.*') ? 'active' : '' }}">
                    <div class="nav-link-content">
                        <span class="nav-icon"><i class="fa-solid fa-diagram-project"></i></span>
                        <span class="nav-text">Redes CPM (Ruta Crítica)</span>
                    </div>
                </a>
                @endif

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
                <div class="user-avatar" style="background: linear-gradient(135deg, #f59e0b, #d97706);"><i class="fa-solid fa-eye" style="font-size:0.85rem;"></i></div>
                <div style="flex:1; overflow:hidden;">
                    <div class="user-name text-truncate" style="color:#fbbf24;">Modo Invitado</div>
                    <div class="user-role" style="color:rgba(251,191,36,0.75);">Exploración / Solo Lectura</div>
                </div>
            </div>
            <a href="{{ route('login') }}" class="btn-logout" style="background:rgba(2,132,199,0.15); border-color:rgba(2,132,199,0.4); color:#38bdf8; text-decoration:none; margin-bottom:6px;">
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
                            <a href="{{ route('carnets.show', auth()->user()) }}" target="_blank" title="Ver mi credencial oficial" style="color:#38bdf8; font-size:0.85rem;" class="ms-1">
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
        <!-- Left: Saludo & Hora en vivo -->
        <div class="topbar-left">
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

        <!-- Right: Shelter Switcher & Status -->
        <div class="topbar-right">
            <form action="{{ route('shelter.switch') }}" method="POST" id="topbarShelterForm" class="d-flex align-items-center m-0">
                @csrf
                <div class="shelter-select-wrap">
                    <i class="fa-solid fa-building-shield me-2 text-info" style="font-size: 0.85rem;"></i>
                    <select name="shelter_id" onchange="document.getElementById('topbarShelterForm').submit()" title="Seleccionar contexto operativo">
                        <option value="" style="background: #0f172a; color: #38bdf8;">🌐 Red Centralizada (Todos los Refugios)</option>
                        <optgroup label="── Centros / Albergues Humanitarios ──" style="background: #090d16; color: #94a3b8;">
                            @foreach(\App\Models\Shelter::orderBy('codigo')->get() as $navShelter)
                                <option value="{{ $navShelter->id }}" {{ session('shelter_id') == $navShelter->id ? 'selected' : '' }} style="background: #0f172a; color: #fff;">
                                    [{{ $navShelter->codigo }}] {{ $navShelter->nombre }} — {{ $navShelter->municipio }}
                                </option>
                            @endforeach
                        </optgroup>
                    </select>
                </div>
            </form>

            @if(session('shelter_id'))
                <a href="{{ route('shelter.switch') }}" class="btn-glass py-1 px-2 text-info" style="font-size: 0.74rem; border-radius: 10px;" title="Restablecer a Red Centralizada">
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
        <div class="alert alert-success d-flex align-items-center justify-content-between p-3 rounded-4 mb-4" style="background: rgba(16, 185, 129, 0.12); border: 1px solid rgba(16, 185, 129, 0.3); color: #34d399;">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-circle-check fs-5"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close btn-close-white" onclick="this.parentElement.remove()" aria-label="Cerrar"></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger d-flex align-items-center justify-content-between p-3 rounded-4 mb-4" style="background: rgba(244, 63, 94, 0.12); border: 1px solid rgba(244, 63, 94, 0.3); color: #fb7185;">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation fs-5"></i>
                <span>{{ session('error') }}</span>
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
</script>

@stack('scripts')
</body>
</html>
