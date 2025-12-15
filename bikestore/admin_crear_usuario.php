<?php

session_start();

// Verificar si el usuario está logueado y es admin
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true || $_SESSION['usuario_rol'] !== 'admin') {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Usuario - BikeStore Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #2c3e50;
            min-height: 100vh;
            padding: 2rem;
        }

        .container {
            max-width: 700px;
            margin: 0 auto;
        }

        .back-button {
            background: white;
            color: #2c3e50;
            border: 1px solid #d1d5db;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 2rem;
        }

        .back-button:hover {
            background: #f9fafb;
            border-color: #2c3e50;
        }

        .form-card {
            background: white;
            border-radius: 12px;
            padding: 3rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            animation: fadeInUp 0.4s ease-out;
        }

        .form-header {
            text-align: center;
            margin-bottom: 2.5rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid #EFEFEF;
        }

        .form-icon {
            width: 64px;
            height: 64px;
            background: #2c3e50;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 1.5rem;
        }

        .form-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .form-subtitle {
            color: #6b7280;
            font-size: 0.95rem;
            font-weight: 400;
        }

        .user-form {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .form-label {
            font-weight: 500;
            color: #2c3e50;
            font-size: 0.9rem;
        }

        .required {
            color: #dc2626;
        }

        .form-input,
        .form-select {
            padding: 0.875rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.2s ease;
            font-family: inherit;
            background: white;
            color: #2c3e50;
        }

        .form-input:focus,
        .form-select:focus {
            outline: none;
            border-color: #2c3e50;
            box-shadow: 0 0 0 3px rgba(44, 62, 80, 0.1);
        }

        .form-help {
            font-size: 0.8rem;
            color: #6b7280;
        }

        .password-requirements {
            margin-top: 0.75rem;
            padding: 1rem;
            background: #f9fafb;
            border-radius: 8px;
            border-left: 3px solid #2c3e50;
        }

        .password-requirements h4 {
            color: #2c3e50;
            font-size: 0.85rem;
            margin-bottom: 0.75rem;
            font-weight: 600;
        }

        .requirement {
            display: flex;
            align-items: center;
            font-size: 0.85rem;
            color: #6b7280;
            margin-bottom: 0.4rem;
            transition: all 0.2s ease;
        }

        .requirement-icon {
            margin-right: 0.5rem;
            font-size: 0.75rem;
            color: #d1d5db;
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
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #EFEFEF;
        }

        .btn {
            flex: 1;
            padding: 0.875rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .btn-primary {
            background: #2c3e50;
            color: white;
        }

        .btn-primary:hover {
            background: #1a252f;
        }

        .btn-secondary {
            background: white;
            color: #6b7280;
            border: 1px solid #d1d5db;
        }

        .btn-secondary:hover {
            background: #f9fafb;
            border-color: #9ca3af;
        }

        .message {
            padding: 1rem 1.25rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-weight: 400;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.9rem;
        }

        .message.success {
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .message.error {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
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

            .form-card {
                padding: 2rem 1.5rem;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .button-group {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="principal_admin.php" class="back-button">← Volver al panel</a>

        <div class="form-card">
            <div class="form-header">
                <div class="form-icon">👤</div>
                <h1 class="form-title">Crear Nuevo Usuario</h1>
                <p class="form-subtitle">Agrega un nuevo usuario al sistema</p>
            </div>

            <!-- Mensajes -->
            <div id="mensaje"></div>

            <form class="user-form" action="procesar_admin_crear_usuario.php" method="POST" onsubmit="return validarFormulario()">
                
                <!-- Nombre completo -->
                <div class="form-group">
                    <label class="form-label" for="nombre">
                        Nombre Completo <span class="required">*</span>
                    </label>
                    <input type="text" id="nombre" name="nombre" class="form-input" 
                           placeholder="Nombre completo del usuario" required>
                </div>

                <!-- Email y Rol -->
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="correo">
                            Correo Electrónico <span class="required">*</span>
                        </label>
                        <input type="email" id="correo" name="correo" class="form-input" 
                               placeholder="usuario@email.com" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="rol">
                            Rol <span class="required">*</span>
                        </label>
                        <select id="rol" name="rol" class="form-select" required>
                            <option value="">Selecciona un rol</option>
                            <option value="cliente">Cliente</option>
                            <option value="operador">Operador</option>
                            <option value="admin">Administrador</option>
                        </select>
                    </div>
                </div>

                <!-- Contraseña -->
                <div class="form-group">
                    <label class="form-label" for="password">
                        Contraseña <span class="required">*</span>
                    </label>
                    <input type="password" id="password" name="password" class="form-input" 
                           placeholder="Contraseña segura" required>
                    
                    <!-- Requisitos de contraseña -->
                    <div class="password-requirements">
                        <h4>La contraseña debe contener:</h4>
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

                <!-- Nivel de ciclismo y Edad -->
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="nivel_ciclismo">
                            Nivel de Ciclismo <span class="required">*</span>
                        </label>
                        <select id="nivel_ciclismo" name="nivel_ciclismo" class="form-select" required>
                            <option value="">Selecciona un nivel</option>
                            <option value="principiante">Principiante</option>
                            <option value="intermedio">Intermedio</option>
                            <option value="avanzado">Avanzado</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="edad">Edad</label>
                        <input type="number" id="edad" name="edad" class="form-input" 
                               placeholder="Edad (opcional)" min="13" max="120">
                        <span class="form-help">Opcional: entre 13 y 120 años</span>
                    </div>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn btn-primary">
                        ✓ Crear Usuario
                    </button>
                    <a href="principal_admin.php" class="btn btn-secondary">
                        ✕ Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Actualizar requisitos de contraseña en tiempo real
        function updatePasswordRequirements() {
            const password = document.getElementById('password').value;
            
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
            const nombre = document.getElementById('nombre').value.trim();
            const correo = document.getElementById('correo').value.trim();
            const password = document.getElementById('password').value;
            const rol = document.getElementById('rol').value;
            const nivel_ciclismo = document.getElementById('nivel_ciclismo').value;
            const edad = document.getElementById('edad').value;
            
            const mensajeElemento = document.getElementById('mensaje');
            let mensaje = '';
            let esValido = true;

            // Validar nombre
            if (nombre === '') {
                mensaje += '✖ El nombre es requerido.<br>';
                esValido = false;
            }

            // Validar correo
            const regexCorreo = /^[^@]+@[^@]+\.[a-zA-Z]{2,}$/;
            if (!regexCorreo.test(correo)) {
                mensaje += '✖ El formato del correo no es válido.<br>';
                esValido = false;
            }

            // Validar rol
            if (rol === '') {
                mensaje += '✖ Debes seleccionar un rol.<br>';
                esValido = false;
            }

            // Validar nivel de ciclismo
            if (nivel_ciclismo === '') {
                mensaje += '✖ Debes seleccionar un nivel de ciclismo.<br>';
                esValido = false;
            }

            // Validar requisitos de contraseña
            const regexPassword = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!"#$%&'()*+,\-./:;<=>?@[\]^_{|}~]).{8,}$/;
            if (!regexPassword.test(password)) {
                mensaje += '✖ La contraseña no cumple con todos los requisitos de seguridad.<br>';
                esValido = false;
            }

            // Validar edad si se proporciona
            if (edad !== '' && (parseInt(edad) < 13 || parseInt(edad) > 120)) {
                mensaje += '✖ La edad debe estar entre 13 y 120 años.<br>';
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
        document.getElementById('password').addEventListener('input', updatePasswordRequirements);

        // Mostrar mensajes de la URL
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const mensajeElemento = document.getElementById('mensaje');

            if (urlParams.get('error')) {
                mensajeElemento.innerHTML = '✖ ' + decodeURIComponent(urlParams.get('error'));
                mensajeElemento.className = 'message error';
            }

            if (urlParams.get('success')) {
                mensajeElemento.innerHTML = '✓ ' + decodeURIComponent(urlParams.get('success'));
                mensajeElemento.className = 'message success';
            }
        });
    </script>
</body>
</html>