<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta - BikeStore</title>
    <style>
        /* ===== RESET Y BASE ===== */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; position: relative; margin: 0; padding: 2rem 1rem; overflow-x: hidden; }

        /* ===== FONDO CON VIDEO ===== */
        .video-background { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -2; object-fit: cover; }
        .video-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(135deg, rgba(0, 0, 0, 0.1) 0%, rgba(0, 0, 0, 0.15) 50%, rgba(0, 0, 0, 0.1) 100%); z-index: -1; }

        /* ===== CONTENEDOR PRINCIPAL ===== */
        .register-container { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(20px) saturate(180%); border-radius: 24px; padding: 2.8rem; width: 100%; max-width: 600px; box-shadow: 0 25px 50px rgba(0, 0, 0, 0.1), 0 0 0 2px rgba(255, 255, 255, 0.3); border: 2px solid rgba(255, 255, 255, 0.3); position: relative; margin: auto; animation: fadeInUp 0.6s ease; }
        .register-container:hover { background: rgba(255, 255, 255, 0.08); border: 2px solid rgba(255, 255, 255, 0.4); }

        /* ===== HEADER ===== */
        .register-header { text-align: center; margin-bottom: 2rem; }
        .logo { font-size: 2rem; font-weight: 800; color: #fff; margin-bottom: 0.5rem; letter-spacing: -0.5px; text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3); text-decoration: none; display: block; }
        .register-title { font-size: 1.4rem; font-weight: 700; color: rgba(255, 255, 255, 0.95); margin-bottom: 0.5rem; }
        .subtitle { color: rgba(255, 255, 255, 0.8); font-size: 0.9rem; line-height: 1.5; }

        /* ===== FORMULARIO ===== */
        .register-form { display: flex; flex-direction: column; gap: 1.2rem; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .form-label { display: block; font-weight: 600; color: rgba(255, 255, 255, 0.9); margin-bottom: 0.5rem; font-size: 0.9rem; }
        
        .form-input, .form-select { width: 100%; padding: 1rem; border: 2px solid rgba(255, 255, 255, 0.3); border-radius: 12px; font-size: 1rem; background: rgba(255, 255, 255, 0.05); color: #fff; font-weight: 500; transition: all 0.3s ease; outline: none; }
        .form-input::placeholder { color: rgba(255, 255, 255, 0.6); }
        .form-select option { background: #2c3e50; color: #fff; }
        .form-input:focus, .form-select:focus { border-color: rgba(255, 255, 255, 0.6); background: rgba(255, 255, 255, 0.1); }

        /* ===== PASSWORD ===== */
        .password-wrapper { position: relative; display: flex; align-items: center; }
        .password-wrapper input { padding-right: 45px; }
        .toggle-password { position: absolute; right: 15px; cursor: pointer; color: rgba(255, 255, 255, 0.6); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; transition: color 0.3s ease; }
        .toggle-password:hover { color: #fff; }
        /* Ajuste para que el SVG se vea bien */
        .toggle-password svg { width: 20px; height: 20px; }

        /* Requisitos Password */
        .password-requirements { margin-top: 0.5rem; padding: 1rem; background: rgba(255, 255, 255, 0.08); border-radius: 12px; border: 1px solid rgba(255, 255, 255, 0.2); }
        .password-requirements h4 { color: rgba(255, 255, 255, 0.9); font-size: 0.85rem; margin-bottom: 0.5rem; }
        .requirement { display: flex; align-items: center; font-size: 0.8rem; color: rgba(255, 255, 255, 0.7); margin-bottom: 0.3rem; }
        .requirement.valid { color: #7bed9f; }

        /* ===== BOTONES ===== */
        .submit-btn { background: rgba(255, 255, 255, 0.1); color: white; border: 2px solid rgba(255, 255, 255, 0.4); padding: 1.2rem; border-radius: 12px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease; margin-top: 1rem; }
        .submit-btn:hover { background: rgba(255, 255, 255, 0.2); transform: translateY(-2px); }

        /* ===== MENSAJES ===== */
        .message { padding: 0.875rem; border-radius: 8px; margin: 1rem 0; font-size: 0.9rem; font-weight: 500; }
        .message.error { background: rgba(231, 76, 60, 0.2); color: #ff6b6b; border: 1px solid #ff4757; }
        .message.success { background: rgba(46, 204, 113, 0.2); color: #7bed9f; border: 1px solid #2ecc71; }

        .register-footer { text-align: center; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid rgba(255, 255, 255, 0.2); }
        .login-link { color: rgba(255, 255, 255, 0.9); text-decoration: none; font-weight: 600; padding: 0.5rem 1rem; border: 1px solid rgba(255, 255, 255, 0.3); border-radius: 8px; transition: 0.3s; display: inline-block; margin-top: 0.5rem; }
        .login-link:hover { background: rgba(255, 255, 255, 0.1); color: #fff; }

        @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        @media (max-width: 600px) { .register-container { padding: 2rem 1.5rem; } .form-row { grid-template-columns: 1fr; } }

        /* ===== FIX: OCULTAR OJO DEL NAVEGADOR ===== */
        input[type="password"]::-ms-reveal, input[type="password"]::-ms-clear { display: none; }
        input[type="password"]::-webkit-contacts-auto-fill-button, input[type="password"]::-webkit-credentials-auto-fill-button { visibility: hidden; display: none !important; pointer-events: none; position: absolute; right: 0; }
    </style>
</head>
<body>
    <video autoplay muted loop class="video-background">
        <source src="registro.mp4" type="video/mp4">
    </video>
    <div class="video-overlay"></div>

    <div class="register-container">
        <div class="register-header">
            <a href="index.php" class="logo">BIKESTORE</a>
            <h2 class="register-title">Crea tu cuenta</h2>
            <p class="subtitle">Únete para guardar tus pedidos y agilizar tus compras.</p>
        </div>

        <form id="registroForm" class="register-form" action="procesar_registro.php" method="POST" onsubmit="return validarRegistro()">
            
            <div class="form-group">
                <label class="form-label" for="correo">Correo electrónico</label>
                <input type="email" id="correo" name="correo" class="form-input" placeholder="tu@email.com" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="nombre">Nombre</label>
                    <input type="text" id="nombre" name="nombre" class="form-input" placeholder="Ej: Juan" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="apellidos">Apellidos</label>
                    <input type="text" id="apellidos" name="apellidos" class="form-input" placeholder="Ej: Pérez" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Contraseña</label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" class="form-input" placeholder="Mínimo 8 caracteres" required>
                    <span class="toggle-password" onclick="togglePassword('password', this)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </span>
                </div>
                
                <div class="password-requirements">
                    <h4>Seguridad:</h4>
                    <div class="requirement" id="length-req"><span>✓ 8 caracteres</span></div>
                    <div class="requirement" id="number-req"><span>✓ 1 número</span></div>
                    <div class="requirement" id="lowercase-req"><span>✓ Mayúscula y minúscula</span></div>
                    <div class="requirement" id="symbol-req"><span>✓ Símbolo especial</span></div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Confirmar Contraseña</label>
                <div class="password-wrapper">
                    <input type="password" id="confirm_password" name="confirm_password" class="form-input" placeholder="Repite tu contraseña" required>
                    <span class="toggle-password" onclick="togglePassword('confirm_password', this)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </span>
                </div>
                <div id="password-match-error" style="color: #ff6b6b; font-size: 0.85rem; margin-top: 5px; display: none; font-weight: 500;">
                    ❌ Las contraseñas no coinciden
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Nivel de ciclismo</label>
                    <select id="nivel_ciclismo" name="nivel_ciclismo" class="form-select" required>
                        <option value="">Selecciona tu nivel</option>
                        <option value="principiante">Principiante</option>
                        <option value="intermedio">Intermedio</option>
                        <option value="avanzado">Avanzado</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Edad (opcional)</label>
                    <input type="number" id="edad" name="edad" class="form-input" placeholder="Ej: 25" min="13" max="120">
                </div>
            </div>

            <button type="submit" class="submit-btn" id="btnRegistro">Crear cuenta</button>
        </form>

        <div id="mensaje"></div>

        <div class="register-footer">
            <p style="color: rgba(255,255,255,0.8); font-size: 0.9rem;">¿Ya tienes cuenta?</p>
            <a href="login.php" class="login-link">Inicia sesión aquí</a>
        </div>
    </div>

    <script>
        function togglePassword(inputId, iconElement) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
                // SVG Ojo Tachado (Ocultar)
                iconElement.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>';
            } else {
                input.type = 'password';
                // SVG Ojo Normal (Ver)
                iconElement.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
            }
        }

        // Validación en tiempo real de requisitos
        document.getElementById('password').addEventListener('input', function() {
            const val = this.value;
            document.getElementById('length-req').className = val.length >= 8 ? 'requirement valid' : 'requirement';
            document.getElementById('number-req').className = /\d/.test(val) ? 'requirement valid' : 'requirement';
            document.getElementById('lowercase-req').className = (/[a-z]/.test(val) && /[A-Z]/.test(val)) ? 'requirement valid' : 'requirement';
            document.getElementById('symbol-req').className = /[!"#$%&'()*+,\-./:;<=>?@[\]^_{|}~]/.test(val) ? 'requirement valid' : 'requirement';
        });

        // Validación de coincidencia en tiempo real
        document.getElementById('confirm_password').addEventListener('input', function() {
            const pass1 = document.getElementById('password').value;
            const pass2 = this.value;
            const errorDiv = document.getElementById('password-match-error');
            
            if(pass2 && pass1 !== pass2) {
                errorDiv.style.display = 'block';
                this.style.borderColor = '#ff6b6b';
            } else {
                errorDiv.style.display = 'none';
                this.style.borderColor = 'rgba(255, 255, 255, 0.3)';
            }
        });

        function validarRegistro() {
            const correo = document.getElementById("correo").value.trim();
            const password = document.getElementById("password").value;
            const confirmPassword = document.getElementById("confirm_password").value;
            const btn = document.getElementById("btnRegistro");
            
            let error = "";

            if (!/^[^\s@]+@[^\s@]+\.[a-zA-Z]{2,}$/.test(correo)) error = "Correo inválido.";
            
            const regexPass = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;
            if (!regexPass.test(password)) error = "La contraseña no cumple los requisitos.";

            if (password !== confirmPassword) error = "Las contraseñas no coinciden.";
            
            if (error) {
                const msg = document.getElementById("mensaje");
                msg.innerHTML = "❌ " + error;
                msg.className = "message error";
                return false;
            }

            btn.innerHTML = "Creando cuenta...";
            btn.disabled = true;
            return true;
        }

        // Mostrar errores de PHP
        window.addEventListener('DOMContentLoaded', () => {
            const params = new URLSearchParams(window.location.search);
            if(params.get('error')) {
                const msg = document.getElementById("mensaje");
                msg.innerHTML = "❌ " + decodeURIComponent(params.get('error'));
                msg.className = "message error";
            }
        });
    </script>
</body>
</html>