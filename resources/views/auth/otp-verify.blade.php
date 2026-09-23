<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Google Authenticator 2FA - NutriLnk</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --accent: #0284c7;
            --accent-light: #38bdf8;
            --accent-glow: rgba(2, 132, 199, 0.45);
            --accent2: #10b981;
            --bg-dark: #0a0a1a;
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            background: var(--bg-dark);
            background-image:
                radial-gradient(ellipse 80% 60% at 20% 0%, rgba(2, 132, 199, 0.22) 0%, transparent 60%),
                radial-gradient(ellipse 60% 50% at 80% 100%, rgba(16, 185, 129, 0.18) 0%, transparent 60%);
            padding: 24px 15px;
            color: #f1f0ff;
        }
        .otp-wrap {
            width: 100%; max-width: 500px;
            padding: 36px 32px;
            background: rgba(255, 255, 255, 0.04);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 26px;
            box-shadow: 0 25px 70px rgba(0, 0, 0, 0.65), 0 0 45px rgba(2, 132, 199, 0.18);
            position: relative;
        }

        /* Badge y Logo de Google Authenticator */
        .g-auth-logo-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(66, 133, 244, 0.12);
            border: 1px solid rgba(66, 133, 244, 0.35);
            color: #93c5fd;
            font-size: 0.76rem;
            font-weight: 700;
            padding: 5px 14px;
            border-radius: 999px;
            margin-bottom: 14px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .logo-ring {
            width: 62px; height: 62px; margin: 0 auto 14px;
            border-radius: 18px;
            background: linear-gradient(135deg, #1e3a8a, #0284c7);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.7rem; color: #fff;
            box-shadow: 0 0 35px var(--accent-glow);
            position: relative;
        }
        .logo-ring .mini-badge {
            position: absolute;
            bottom: -4px; right: -4px;
            width: 22px; height: 22px;
            background: #10b981;
            border-radius: 50%;
            border: 2px solid #0a0a1a;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.65rem; color: white;
        }
        h4 { font-weight: 800; font-size: 1.35rem; color: #fff; margin-bottom: 6px; }
        .subtitle { color: rgba(241, 240, 255, 0.65); font-size: 0.83rem; line-height: 1.45; }

        /* Tarjeta de usuario */
        .user-chip {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.09);
            border-radius: 14px;
            padding: 10px 14px;
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 18px 0;
        }
        .user-chip-avatar {
            width: 40px; height: 40px; border-radius: 12px;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 1rem; color: #fff; flex-shrink: 0;
        }

        /* Barra de Temporizador TOTP de 30 segundos */
        .totp-timer-wrap {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.74rem;
            color: rgba(241, 240, 255, 0.6);
            margin-bottom: 8px;
        }
        .totp-progress-bar {
            height: 4px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 22px;
        }
        .totp-progress-inner {
            height: 100%;
            width: 100%;
            background: linear-gradient(90deg, #10b981, #38bdf8);
            transition: width 1s linear;
        }

        /* Casillas de Dígitos OTP */
        .otp-inputs-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 10px;
            margin: 12px 0 20px;
        }
        .otp-box {
            width: 100%;
            height: 60px;
            text-align: center;
            font-size: 1.85rem;
            font-weight: 800;
            font-family: 'JetBrains Mono', monospace;
            background: rgba(255, 255, 255, 0.05);
            border: 1.5px solid rgba(255, 255, 255, 0.15);
            color: #fff;
            border-radius: 14px;
            outline: none;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .otp-box:focus {
            background: rgba(255, 255, 255, 0.09);
            border-color: var(--accent-light);
            box-shadow: 0 0 0 4px rgba(56, 189, 248, 0.25), 0 0 20px rgba(56, 189, 248, 0.25);
            transform: translateY(-2px);
        }
        .otp-box.filled {
            border-color: rgba(16, 185, 129, 0.6);
            background: rgba(16, 185, 129, 0.08);
            color: #34d399;
        }

        /* Botón de Enviar */
        .btn-submit {
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            border: none; color: #fff; font-weight: 700; font-size: 0.95rem;
            padding: 13px; border-radius: 12px; width: 100%;
            box-shadow: 0 4px 22px var(--accent-glow);
            cursor: pointer; transition: all 0.2s ease;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn-submit:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: 0 6px 28px rgba(2, 132, 199, 0.55);
        }
        .btn-submit:disabled {
            opacity: 0.6; cursor: not-allowed;
        }

        /* Alertas */
        .alert-otp {
            border-radius: 12px;
            padding: 10px 14px;
            font-size: 0.83rem;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .alert-otp-danger {
            background: rgba(244, 63, 94, 0.12);
            border: 1px solid rgba(244, 63, 94, 0.3);
            color: #fb7185;
        }
        .alert-otp-success {
            background: rgba(16, 185, 129, 0.12);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #34d399;
        }

        /* Animación Shake en error */
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-6px); }
            40%, 80% { transform: translateX(6px); }
        }
        .shake { animation: shake 0.4s ease-in-out; }

        .footer-actions {
            margin-top: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.82rem;
        }
        .btn-link-action {
            background: none; border: none; padding: 0;
            color: rgba(241, 240, 255, 0.65);
            text-decoration: none; cursor: pointer;
            transition: color 0.2s ease;
            display: inline-flex; align-items: center; gap: 6px;
        }
        .btn-link-action:hover { color: #f87171; }
    </style>
</head>
<body>

<div class="otp-wrap" id="otpCard">
    <div class="text-center">
        <div class="g-auth-logo-badge">
            <i class="fa-brands fa-google"></i> Google Authenticator 2FA
        </div>
        <div class="logo-ring">
            <i class="fa-solid fa-mobile-screen-button"></i>
            <div class="mini-badge"><i class="fa-solid fa-shield"></i></div>
        </div>
        <h4>Verificación de Seguridad 2FA</h4>
        <p class="subtitle mb-0">Abra la aplicación <strong>Google Authenticator</strong> en su teléfono móvil e introduzca el código de 6 dígitos.</p>
    </div>

    <!-- Tarjeta de Usuario -->
    <div class="user-chip">
        <div class="user-chip-avatar">
            {{ strtoupper(substr($user->name, 0, 1)) }}
        </div>
        <div style="flex: 1; min-width: 0;">
            <div class="fw-bold text-truncate" style="font-size: 0.88rem; color: #fff;">{{ $user->name }}</div>
            <div class="text-muted small text-truncate" style="font-size: 0.75rem;">
                <span class="text-info">{{ strtoupper(str_replace('_', ' ', $user->rol)) }}</span> &bull; 
                {{ preg_replace('/(?<=.).(?=.*@)/', '*', $user->email) }}
            </div>
        </div>
        <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); font-size: 0.72rem;">
            <i class="fa-solid fa-lock me-1"></i> Protegido
        </span>
    </div>

    <!-- Temporizador visual de 30 segundos -->
    <div class="totp-timer-wrap">
        <span><i class="fa-regular fa-clock me-1"></i> Ventana de validez del código:</span>
        <span class="fw-bold text-info" id="timerLabel">{{ $secondsRemaining }}s</span>
    </div>
    <div class="totp-progress-bar">
        <div class="totp-progress-inner" id="timerProgressBar" style="width: {{ ($secondsRemaining / 30) * 100 }}%;"></div>
    </div>

    <!-- Mensajes de estado -->
    <div id="statusAlert" style="display: none;" class="alert-otp"></div>

    @if ($errors->any())
        <div class="alert-otp alert-otp-danger shake">
            <i class="fa-solid fa-circle-exclamation"></i>
            <div>{{ $errors->first() }}</div>
        </div>
    @endif

    @if (session('success'))
        <div class="alert-otp alert-otp-success">
            <i class="fa-solid fa-circle-check"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    <!-- Formulario de Entrada OTP -->
    <form id="otpForm" method="POST" action="{{ route('otp.verify') }}">
        @csrf
        <input type="hidden" name="code" id="fullCodeInput">

        <label class="form-label text-uppercase fw-bold text-center d-block mb-1" style="font-size: 0.72rem; letter-spacing: 1px; color: rgba(241, 240, 255, 0.65);">
            Código de 6 dígitos
        </label>

        <div class="otp-inputs-grid" id="otpInputs">
            <input type="text" inputmode="numeric" pattern="[0-9]" maxlength="1" class="otp-box" data-index="0" autofocus autocomplete="one-time-code">
            <input type="text" inputmode="numeric" pattern="[0-9]" maxlength="1" class="otp-box" data-index="1">
            <input type="text" inputmode="numeric" pattern="[0-9]" maxlength="1" class="otp-box" data-index="2">
            <input type="text" inputmode="numeric" pattern="[0-9]" maxlength="1" class="otp-box" data-index="3">
            <input type="text" inputmode="numeric" pattern="[0-9]" maxlength="1" class="otp-box" data-index="4">
            <input type="text" inputmode="numeric" pattern="[0-9]" maxlength="1" class="otp-box" data-index="5">
        </div>

        <button type="submit" id="btnSubmit" class="btn-submit mt-2">
            <i class="fa-solid fa-shield-check"></i> Verificar con Google Authenticator
        </button>
    </form>

    <!-- Acciones Inferiores -->
    <div class="footer-actions">
        <form action="{{ route('logout') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn-link-action" style="color: #fb7185;">
                <i class="fa-solid fa-arrow-right-from-bracket"></i> Cancelar y Cerrar Sesión
            </button>
        </form>
    </div>
</div>

<script>
    const inputs = document.querySelectorAll('.otp-box');
    const fullCodeInput = document.getElementById('fullCodeInput');
    const otpForm = document.getElementById('otpForm');
    const btnSubmit = document.getElementById('btnSubmit');
    const statusAlert = document.getElementById('statusAlert');
    const otpCard = document.getElementById('otpCard');
    const timerLabel = document.getElementById('timerLabel');
    const timerProgressBar = document.getElementById('timerProgressBar');

    // Auto-focus primera casilla
    if (inputs.length > 0) inputs[0].focus();

    // Manejo de entrada interactiva y navegación entre cajas
    inputs.forEach((input, idx) => {
        input.addEventListener('input', (e) => {
            const val = e.target.value.replace(/[^0-9]/g, '');
            e.target.value = val ? val.slice(-1) : '';

            if (e.target.value) {
                e.target.classList.add('filled');
                if (idx < inputs.length - 1) {
                    inputs[idx + 1].focus();
                    inputs[idx + 1].select();
                }
            } else {
                e.target.classList.remove('filled');
            }
            updateFullCode();

            // Auto-submit al completar los 6 dígitos
            if (isAllFilled()) {
                submitOtp();
            }
        });

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && !e.target.value && idx > 0) {
                inputs[idx - 1].focus();
                inputs[idx - 1].select();
            } else if (e.key === 'ArrowLeft' && idx > 0) {
                inputs[idx - 1].focus();
            } else if (e.key === 'ArrowRight' && idx < inputs.length - 1) {
                inputs[idx + 1].focus();
            }
        });

        // Soporte para pegar código completo (Ctrl+V)
        input.addEventListener('paste', (e) => {
            e.preventDefault();
            const pasted = (e.clipboardData || window.clipboardData).getData('text');
            const clean = pasted.replace(/[^0-9]/g, '').slice(0, 6);
            if (clean) {
                clean.split('').forEach((char, i) => {
                    if (inputs[i]) {
                        inputs[i].value = char;
                        inputs[i].classList.add('filled');
                    }
                });
                updateFullCode();
                if (clean.length === 6) {
                    submitOtp();
                } else if (inputs[clean.length]) {
                    inputs[clean.length].focus();
                }
            }
        });
    });

    function updateFullCode() {
        let code = '';
        inputs.forEach(i => code += i.value);
        fullCodeInput.value = code;
    }

    function isAllFilled() {
        return Array.from(inputs).every(i => i.value.length === 1);
    }

    // Envío del formulario por AJAX
    otpForm.addEventListener('submit', (e) => {
        e.preventDefault();
        submitOtp();
    });

    function submitOtp() {
        updateFullCode();
        const code = fullCodeInput.value.trim();

        if (code.length !== 6) {
            showAlert('Por favor complete los 6 dígitos numéricos del código.', 'danger');
            shakeCard();
            return;
        }

        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Validando con Google Authenticator...';

        fetch("{{ route('otp.verify') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ code: code })
        })
        .then(res => res.json().then(data => ({ status: res.status, body: data })))
        .then(result => {
            if (result.status === 200 && result.body.success) {
                showAlert(result.body.message || 'Código verificado con éxito. Redirigiendo...', 'success');
                btnSubmit.innerHTML = '<i class="fa-solid fa-circle-check"></i> Acceso 2FA Autorizado';
                setTimeout(() => {
                    window.location.href = result.body.redirect || "{{ route('dashboard') }}";
                }, 600);
            } else {
                showAlert(result.body.message || 'Código incorrecto o expirado.', 'danger');
                shakeCard();
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = '<i class="fa-solid fa-shield-check"></i> Verificar con Google Authenticator';
                inputs.forEach(i => { i.value = ''; i.classList.remove('filled'); });
                inputs[0].focus();
                fullCodeInput.value = '';
            }
        })
        .catch(err => {
            showAlert('Error de conexión con el servidor. Intente nuevamente.', 'danger');
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<i class="fa-solid fa-shield-check"></i> Verificar con Google Authenticator';
        });
    }

    function showAlert(msg, type) {
        statusAlert.style.display = 'flex';
        statusAlert.className = `alert-otp alert-otp-${type}`;
        const icon = type === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation';
        statusAlert.innerHTML = `<i class="fa-solid ${icon}"></i> <div>${msg}</div>`;
    }

    function shakeCard() {
        otpCard.classList.remove('shake');
        void otpCard.offsetWidth; // trigger reflow
        otpCard.classList.add('shake');
    }

    // Sincronizador de Reloj TOTP (30 segundos en tiempo real)
    function updateTotpClock() {
        const now = Math.floor(Date.now() / 1000);
        const remaining = 30 - (now % 30);
        timerLabel.textContent = `${remaining}s restantes`;
        const percentage = (remaining / 30) * 100;
        timerProgressBar.style.width = `${percentage}%`;
    }

    setInterval(updateTotpClock, 1000);
    updateTotpClock();
</script>

</body>
</html>
