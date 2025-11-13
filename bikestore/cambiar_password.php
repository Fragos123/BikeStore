<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header("Location: login.php");
    exit;
}

$nombre_usuario = $_SESSION['usuario_nombre'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar Contraseña - BikeStore</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #2E3848 0%, #1a202c 100%);
            min-height: 100vh;
            padding: 2rem;
            color: #2d3748;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
        }

        .back-button {
            background: rgba(241, 245, 249, 0.1);
            backdrop-filter: blur(10px);
            color: #F1F5F9;
            border: 2px solid rgba(241, 245, 249, 0.2);
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 2rem;
        }

        .back-button:hover {
            background: rgba(241, 245, 249, 0.2);
            transform: translateX(-5px);
        }

        .password-card {
            background: #F1F5F9;
            border-radius: 24px;
            padding: 3rem;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.15);
            animation: fadeInUp 0.6s ease-out;
        }

        .password-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .password-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #2E3848, #A6A09B);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin: 0 auto 1.5rem;
        }

        .password-title {
            font-size: 2rem;
            font-weight: 900;
            color: #2E3848;
            margin-bottom: 0.5rem;
        }

        .password-subtitle {
            color: #6b7280;
            font-size: 1rem;
        }

        .password-form {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .form-label {
            font-weight: 600;
            color: #2E3848;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-input {
            padding: 1rem 1.25rem;
            border: 2px solid #d1d5db;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            font-family: inherit;
            background: white;
        }

        .form-input:focus {
            outline: none;
            border-color: #2E3848;
            background: white;
            box-shadow: 0 0 0 3px rgba(46, 56, 72, 0.1);
        }

        .password-requirements {
            margin-top: 0.8rem;
            padding: 1rem;
            background: white;
            border-radius: 12px;
            border-left: 4px solid #2E3848;
        }

        .password-requirements h4 {
            color: #2E3848;
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .requirement {
            display: flex;
            align-items: center;
            font-size: 0.85rem;
            color: #6b7280;
            margin-bottom: 0.3rem;
            transition: all 0.3s ease;
        }

        .requirement-icon {
            margin-right: 0.5rem;
            font-size: 0.75rem;
        }

        .requirement.valid {
            color: #059669;
        }

        .requirement.valid .requirement-icon {
            color: #059669;
        }

        .button-group {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .btn {
            flex: 1;
            padding: 1rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #2E3848, #A6A09B);
            color: #F1F5F9;
            box-shadow: 0 10px 30px rgba(46, 56, 72, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 40px rgba(46, 56, 72, 0.4);
        }

        .btn-secondary {
            background: white;
            color: #2E3848;
            border: 2px solid #d1d5db;
        }

        .btn-secondary:hover {
            background: #f9fafb;
            border-color: #9ca3af;
        }

        .message {
            padding: 1rem 1.25rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .message.success {
            background: #d1fae5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }

        .message.error {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }

        .security-note {
            background: #dbeafe;
            border-left: 4px solid #3b82f6;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1.5rem;
        }

        .security-note-title {
            font-weight: 600;
            color: #1e40af;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .security-note-text {
            color: #1e40af;
            font-size: 0.85rem;
            line-height: 1.5;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .password-card {
                padding: 2rem 1.5rem;
            }

            .button-group {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="perfil.php" class="back-button">← Volver al perfil</a>

        <div class="password-card">
            <div class="password-header">
                <div class="password-icon">🔒</div>
                <h1 class="password-title">Cambiar Contraseña</h1>
                <p class="password-subtitle">Actualiza tu contraseña de forma segura</p>
            </div>

            <!-- Mensajes -->
            <div id="mensaje"></div>

            <form class="password-form" action="procesar_cambiar_password.php" method="POST" onsubmit="return validarFormulario()">
                
                <div class="form-group">
                    <label class="form-label" for="password_actual">Contraseña Actual</label>
                    <input type="password" id="password_actual" name="password_actual" 
                           class="form-input" placeholder="Ingresa tu contraseña actual" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password_nueva">Nueva Contraseña</label>
                    <input type="password" id="password_nueva" name="password_nueva" 
                           class="form-input" placeholder="Ingresa tu nueva contraseña" required>
                    
                    <!-- Requisitos de contraseña -->
                    <div class="password-requirements">
                        <h4>Tu nueva contraseña debe contener:</h4>
                        <div class="requirement" id="length-req">
                            <span class="requirement-icon">✓</span>
                            <span>Al menos 8 caracteres</span>
                        </div>
                        <div class="requirement" id="number-req">
                            <span class="requirement-icon">✓</span>
                            <span>Al menos 1 número (0-9)</span>
                        </div>
                        <div class="requirement" id="lowercase-req">
                            <span class="requirement-icon">✓</span>
                            <span>1 letra minúscula y 1 mayúscula (Aa-Zz)</span>
                        </div>
                        <div class="requirement" id="symbol-req">
                            <span class="requirement-icon">✓</span>
                            <span>1 símbolo especial</span>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password_confirmar">Confirmar Nueva Contraseña</label>
                    <input type="password" id="password_confirmar" name="password_confirmar" 
                           class="form-input" placeholder="Confirma tu nueva contraseña" required>
                </div>

                <div class="security-note">
                    <div class="security-note-title">Consejo de seguridad</div>
                    <div class="security-note-text">
                        Usa una contraseña única que no hayas usado en otros sitios. 
                        Evita información personal como nombres o fechas de nacimiento.
                    </div>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn btn-primary">
                        🔐 Actualizar Contraseña
                    </button>
                    <a href="perfil.php" class="btn btn-secondary">
                        ✕ Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Actualizar requisitos de contraseña en tiempo real
        function updatePasswordRequirements() {
            const password = document.getElementById('password_nueva').value;
            
            const lengthReq = document.getElementById('length-req');
            if (password.length >= 8) {
                lengthReq.classList.add('valid');
            } else {
                lengthReq.classList.remove('valid');
            }
            
            const numberReq = document.getElementById('number-req');
            if (/\d/.test(password)) {
                numberReq.classList.add('valid');
            } else {
                numberReq.classList.remove('valid');
            }
            
            const lowercaseReq = document.getElementById('lowercase-req');
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) {
                lowercaseReq.classList.add('valid');
            } else {
                lowercaseReq.classList.remove('valid');
            }
            
            const symbolReq = document.getElementById('symbol-req');
            if (/[!"#$%&'()*+,\-./:;<=>?@[\]^_{|}~]/.test(password)) {
                symbolReq.classList.add('valid');
            } else {
                symbolReq.classList.remove('valid');
            }
        }

        function validarFormulario() {
            const passwordActual = document.getElementById('password_actual').value;
            const passwordNueva = document.getElementById('password_nueva').value;
            const passwordConfirmar = document.getElementById('password_confirmar').value;
            
            const mensajeElemento = document.getElementById('mensaje');
            let mensaje = '';
            let esValido = true;

            // Validar que todos los campos estén llenos
            if (passwordActual === '' || passwordNueva === '' || passwordConfirmar === '') {
                mensaje += '❌ Todos los campos son requeridos.<br>';
                esValido = false;
            }

            // Validar que las contraseñas nuevas coincidan
            if (passwordNueva !== passwordConfirmar) {
                mensaje += '❌ Las contraseñas nuevas no coinciden.<br>';
                esValido = false;
            }

            // Validar que la nueva contraseña sea diferente a la actual
            if (passwordActual === passwordNueva && passwordActual !== '') {
                mensaje += '❌ La nueva contraseña debe ser diferente a la actual.<br>';
                esValido = false;
            }

            // Validar requisitos de la nueva contraseña
            const regexPassword = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!"#$%&'()*+,\-./:;<=>?@[\]^_{|}~]).{8,}$/;
            if (!regexPassword.test(passwordNueva)) {
                mensaje += '❌ La nueva contraseña no cumple con todos los requisitos de seguridad.<br>';
                esValido = false;
            }

            if (!esValido) {
                mensajeElemento.innerHTML = mensaje;
                mensajeElemento.className = 'message error';
                return false;
            }

            return true;
        }

        // Event listener para actualizar requisitos en tiempo real
        document.getElementById('password_nueva').addEventListener('input', updatePasswordRequirements);

        // Mostrar mensajes de la URL
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const mensajeElemento = document.getElementById('mensaje');

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