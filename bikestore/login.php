<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - BikeStore</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            margin: 0;
            padding: 2rem 1rem;
            overflow-x: hidden;
            overflow-y: auto;
        }

        .video-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -2;
            object-fit: cover;
        }

        .video-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, 
                rgba(0, 0, 0, 0.1) 0%, 
                rgba(0, 0, 0, 0.15) 50%,
                rgba(0, 0, 0, 0.1) 100%);
            z-index: -1;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px) saturate(180%);
            border-radius: 24px;
            padding: 2.8rem;
            width: 100%;
            max-width: 420px;
            box-shadow: 
                0 25px 50px rgba(0, 0, 0, 0.1),
                0 0 0 2px rgba(255, 255, 255, 0.3);
            border: 2px solid rgba(255, 255, 255, 0.3);
            position: relative;
            margin: auto;
            flex-shrink: 0;
            animation: fadeInUp 0.6s ease;
            transition: all 0.3s ease;
        }

        .login-container:hover {
            background: rgba(255, 255, 255, 0.08);
            border: 2px solid rgba(255, 255, 255, 0.4);
            box-shadow: 
                0 30px 60px rgba(0, 0, 0, 0.15),
                0 0 0 2px rgba(255, 255, 255, 0.4);
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo {
            font-size: 2rem;
            font-weight: 800;
            color: #fff;
            margin-bottom: 0.5rem;
            letter-spacing: -0.5px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .logo:hover {
            text-shadow: 0 4px 20px rgba(255, 255, 255, 0.3);
            transform: scale(1.02);
        }

        .login-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.9);
            text-shadow: 0 1px 5px rgba(0, 0, 0, 0.3);
        }

        .login-form {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .form-group {
            position: relative;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
        }

        .form-input {
            width: 100%;
            padding: 1.1rem;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 12px;
            font-size: 1rem;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
            outline: none;
            color: #fff;
            font-weight: 500;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .form-input:focus {
            border-color: rgba(255, 255, 255, 0.6);
            background: rgba(255, 255, 255, 0.1);
            box-shadow: 
                0 0 0 3px rgba(255, 255, 255, 0.1),
                0 8px 25px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }

        .form-input:hover {
            border-color: rgba(255, 255, 255, 0.5);
            background: rgba(255, 255, 255, 0.08);
        }

        .form-input::placeholder {
            color: rgba(255, 255, 255, 0.6);
            font-weight: 400;
        }

        /* ===== OJO DE CONTRASEÑA SVG ===== */
        .password-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .password-wrapper input {
            padding-right: 45px;
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            cursor: pointer;
            color: rgba(255, 255, 255, 0.6);
            transition: color 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            z-index: 10;
        }

        .toggle-password:hover {
            color: #fff;
        }

        .toggle-password svg {
            width: 20px;
            height: 20px;
        }

        .submit-btn {
            background: rgba(255, 255, 255, 0.05);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.4);
            padding: 1.2rem;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 0.5rem;
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
        }

        .submit-btn:hover {
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.6);
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.2);
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.4);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .message {
            padding: 0.875rem;
            border-radius: 8px;
            margin: 1rem 0;
            font-size: 0.9rem;
            font-weight: 500;
            line-height: 1.4;
            border-left: 3px solid transparent;
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .message.error {
            background: rgba(231, 76, 60, 0.1);
            color: #ff6b6b;
            border-left-color: #ff4757;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        }

        .message.success {
            background: rgba(46, 204, 113, 0.1);
            color: #7bed9f;
            border-left-color: #5f27cd;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        }

        .login-footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
        }

        .register-text {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
        }

        .register-link {
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-block;
            padding: 0.7rem 1.5rem;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(8px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
        }

        .register-link:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.5);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.4);
        }

        .forgot-password {
            text-align: center;
            margin-top: 1rem;
        }

        .forgot-password a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.3s ease;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        }
        
        .forgot-password a:hover { 
            color: rgba(255, 255, 255, 0.9);
            text-shadow: 0 2px 8px rgba(255, 255, 255, 0.2);
        }

        @media (max-width: 480px) {
            body { padding: 1rem; }
            .login-container {
                padding: 2rem 1.5rem;
                margin: 1rem 0;
                background: rgba(255, 255, 255, 0.08);
            }
            .logo { font-size: 1.8rem; }
        }

        @media (max-height: 700px) {
            body {
                align-items: flex-start;
                padding-top: 2rem;
                padding-bottom: 2rem;
            }
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .submit-btn:disabled {
            background: rgba(149, 165, 166, 0.3);
            border-color: rgba(149, 165, 166, 0.4);
            cursor: not-allowed;
            transform: none;
        }

        .btn-loading {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .spinner {
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

        .form-input:valid:not(:placeholder-shown) { 
            border-color: rgba(46, 204, 113, 0.6);
        }
        .form-input:invalid:not(:placeholder-shown) { 
            border-color: rgba(231, 76, 60, 0.6);
        }

        .login-container::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, 
                transparent, 
                rgba(255, 255, 255, 0.1), 
                transparent
            );
            animation: shimmer 3s infinite;
            border-radius: 24px;
        }

        @keyframes shimmer {
            0% { left: -100%; }
            100% { left: 100%; }
        }

        /* ===== FIX: ELIMINAR EL OJO POR DEFECTO DEL NAVEGADOR ===== */
        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear { display: none; }
        input[type="password"]::-webkit-contacts-auto-fill-button,
        input[type="password"]::-webkit-credentials-auto-fill-button {
            visibility: hidden; display: none !important; pointer-events: none; position: absolute; right: 0;
        }
    </style>
</head>
<body>
    <video autoplay muted loop class="video-background">
        <source src="fondo.mp4" type="video/mp4">
    </video>
    <div class="video-overlay"></div>

    <div class="login-container">
        <div class="login-header">
            <a href="index.php" style="text-decoration: none;">
                <h1 class="logo">BIKESTORE</h1>
            </a>
            <p class="login-title">Iniciar Sesión</p>
        </div>

        <form id="loginForm" class="login-form" action="procesar_login.php" method="POST" onsubmit="return validarFormulario()">
            
            <?php if (isset($_GET['redirect'])): ?>
                <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($_GET['redirect']); ?>">
            <?php endif; ?>
            <div class="form-group">
                <label class="form-label" for="correo">Correo</label>
                <input type="text" id="correo" name="correo" class="form-input" placeholder="tu@email.com" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Contraseña</label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" class="form-input" placeholder="Tu contraseña" required>
                    <span class="toggle-password" onclick="togglePassword()" title="Mostrar/Ocultar contraseña">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </span>
                </div>
            </div>

            <button type="submit" class="submit-btn" id="submitBtn">
                <span id="btn-text">Ingresar</span>
            </button>
        </form>

        <div id="mensaje"></div>

        <div class="forgot-password">
            <a href="olvide_password.php">¿Olvidaste tu contraseña?</a>
        </div>

        <div class="login-footer">
            <p class="register-text">¿No tienes cuenta?</p>
            <a href="registro.php" class="register-link">Crea una aquí</a>
        </div>
    </div>

    <script>
        let isFormSubmitting = false;

        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.querySelector('.toggle-password');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>';
            } else {
                passwordInput.type = 'password';
                toggleIcon.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
            }
        }

        function validarFormulario() {
            if (isFormSubmitting) return false;

            const correo = document.getElementById("correo").value.trim();
            const password = document.getElementById("password").value;
            const submitBtn = document.getElementById("submitBtn");
            const btnText = document.getElementById("btn-text");
            let mensaje = "";
            let esValido = true;

            const regexCorreo = /^[^\s@]+@[^\s@]+\.[a-zA-Z]{2,}$/;
            if (!regexCorreo.test(correo)) {
                mensaje += "❌ El correo no tiene un formato válido.<br>";
                esValido = false;
            }

            const regexPassword = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;
            if (!regexPassword.test(password)) {
                mensaje += "❌ La contraseña debe tener mínimo 8 caracteres, una mayúscula, una minúscula y un número.<br>";
                esValido = false;
            }

            const mensajeElemento = document.getElementById("mensaje");

            if (esValido) {
                isFormSubmitting = true;
                submitBtn.disabled = true;
                btnText.innerHTML = '<div class="btn-loading"><div class="spinner"></div>Ingresando...</div>';
                mensajeElemento.innerHTML = "✔ Validaciones correctas, autenticando...";
                mensajeElemento.className = "message success";
                return true;
            } else {
                mensajeElemento.innerHTML = mensaje;
                mensajeElemento.className = "message error";
                submitBtn.style.transform = 'scale(0.98)';
                setTimeout(() => { submitBtn.style.transform = ''; }, 150);
                return false;
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const correoInput = document.getElementById('correo');
            const passwordInput = document.getElementById('password');

            correoInput.addEventListener('blur', function() {
                const regexCorreo = /^[^\s@]+@[^\s@]+\.[a-zA-Z]{2,}$/;
                if (this.value && !regexCorreo.test(this.value)) {
                    this.style.borderColor = 'rgba(231, 76, 60, 0.6)';
                } else if (this.value) {
                    this.style.borderColor = 'rgba(46, 204, 113, 0.6)';
                }
            });

            [correoInput, passwordInput].forEach(input => {
                input.addEventListener('focus', function() {
                    this.style.borderColor = 'rgba(255, 255, 255, 0.6)';
                });
            });
            
            const urlParams = new URLSearchParams(window.location.search);
            const mensajeElemento = document.getElementById("mensaje");

            if (urlParams.get('error')) {
                mensajeElemento.innerHTML = '❌ ' + decodeURIComponent(urlParams.get('error'));
                mensajeElemento.className = 'message error';
            }
            if (urlParams.get('success')) {
                mensajeElemento.innerHTML = '✅ ' + decodeURIComponent(urlParams.get('success'));
                mensajeElemento.className = 'message success';
            }
        });
    </script>
</body>
</html>