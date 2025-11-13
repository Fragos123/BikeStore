<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta - BikeStore</title>
    <style>
        /* ===== RESET Y BASE ===== */
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

        /* ===== FONDO CON VIDEO ===== */
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
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.1) 0%, rgba(0, 0, 0, 0.15) 50%, rgba(0, 0, 0, 0.1) 100%);
            z-index: -1;
        }

        /* ===== CONTENEDOR PRINCIPAL ===== */
        .register-container {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px) saturate(180%);
            border-radius: 24px;
            padding: 2.8rem;
            width: 100%;
            max-width: 600px;
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

        .register-container:hover {
            background: rgba(255, 255, 255, 0.08);
            border: 2px solid rgba(255, 255, 255, 0.4);
            box-shadow: 
                0 30px 60px rgba(0, 0, 0, 0.15), 
                0 0 0 2px rgba(255, 255, 255, 0.4);
        }

        .register-container::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            animation: shimmer 3s infinite;
            border-radius: 24px;
        }

        /* ===== HEADER ===== */
        .register-header {
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

        .register-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: rgba(255, 255, 255, 0.95);
            text-shadow: 0 1px 5px rgba(0, 0, 0, 0.3);
            margin-bottom: 0.5rem;
        }

        .subtitle {
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 0;
            line-height: 1.5;
            font-size: 0.9rem;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
        }

        /* ===== FORMULARIO ===== */
        .register-form {
            display: flex;
            flex-direction: column;
            gap: 1.2rem;
        }

        .form-group {
            position: relative;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
        }

        .form-input,
        .form-select {
            width: 100%;
            padding: 1rem;
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

        .form-input::placeholder {
            color: rgba(255, 255, 255, 0.6);
            font-weight: 400;
        }

        .form-select option {
            background: #2c3e50;
            color: #fff;
            padding: 0.5rem;
        }

        .form-input:focus,
        .form-select:focus {
            border-color: rgba(255, 255, 255, 0.6);
            background: rgba(255, 255, 255, 0.1);
            box-shadow: 
                0 0 0 3px rgba(255, 255, 255, 0.1), 
                0 8px 25px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }

        .form-input:hover,
        .form-select:hover {
            border-color: rgba(255, 255, 255, 0.5);
            background: rgba(255, 255, 255, 0.08);
        }

        /* Validaciones visuales */
        .form-input:valid:not(:placeholder-shown) {
            border-color: rgba(46, 204, 113, 0.6);
        }

        .form-input:invalid:not(:placeholder-shown) {
            border-color: rgba(231, 76, 60, 0.6);
        }

        /* ===== REQUISITOS DE CONTRASEÑA ===== */
        .password-requirements {
            margin-top: 0.8rem;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(8px);
        }

        .password-requirements h4 {
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        }

        .requirement {
            display: flex;
            align-items: center;
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 0.3rem;
            transition: all 0.3s ease;
        }

        .requirement-icon {
            margin-right: 0.5rem;
            font-size: 0.75rem;
        }

        .requirement.valid {
            color: #7bed9f;
        }

        .requirement.valid .requirement-icon {
            color: #7bed9f;
        }

        /* ===== BOTÓN DE ENVÍO ===== */
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
            margin-top: 1rem;
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

        .submit-btn:disabled {
            background: rgba(149, 165, 166, 0.3);
            border-color: rgba(149, 165, 166, 0.4);
            cursor: not-allowed;
            transform: none;
        }

        /* ===== MENSAJES ===== */
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

        /* ===== FOOTER ===== */
        .register-footer {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
        }

        .login-text {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
        }

        .login-link {
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

        .login-link:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.5);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.4);
        }

        /* ===== ANIMACIONES ===== */
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

        @keyframes shimmer {
            0% { left: -100%; }
            100% { left: 100%; }
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 600px) {
            body {
                padding: 1rem;
            }
            
            .register-container {
                padding: 2rem 1.5rem;
                margin: 1rem 0;
                background: rgba(255, 255, 255, 0.08);
            }
            
            .logo {
                font-size: 1.8rem;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
        }

        @media (max-height: 700px) {
            body {
                align-items: flex-start;
                padding-top: 2rem;
                padding-bottom: 2rem;
            }
        }
    </style>
</head>

<body>
    <!-- Video de fondo -->
    <video autoplay muted loop class="video-background">
        <source src="registro.mp4" type="video/mp4">
    </video>
    <div class="video-overlay"></div>

    <div class="register-container">
        <!-- Header -->
        <div class="register-header">
            <a href="http://localhost:8012/bikestore/#" style="text-decoration: none;">
                <h1 class="logo">BIKESTORE</h1>
            </a>
            <h2 class="register-title">Crea tu cuenta</h2>
            <p class="subtitle">
                Estás a pocos pasos de tener un lugar para guardar todos tus pedidos y la información de tu bicicleta.
            </p>
        </div>

        <!-- Formulario -->
        <form id="registroForm" class="register-form" action="procesar_registro.php" method="POST" onsubmit="return validarRegistro()">
            
            <!-- Email -->
            <div class="form-group">
                <label class="form-label" for="correo">Correo electrónico</label>
                <input type="email" id="correo" name="correo" class="form-input" 
                       placeholder="tu@email.com" required>
            </div>

            <!-- Nombre y Apellidos -->
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="nombre">Nombre</label>
                    <input type="text" id="nombre" name="nombre" class="form-input" 
                           placeholder="Tu nombre" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="apellidos">Apellidos</label>
                    <input type="text" id="apellidos" name="apellidos" class="form-input" 
                           placeholder="Tus apellidos" required>
                </div>
            </div>

            <!-- Contraseña -->
            <div class="form-group">
                <label class="form-label" for="password">Nueva contraseña</label>
                <input type="password" id="password" name="password" class="form-input" 
                       placeholder="Tu contraseña" required>
                
                <!-- Requisitos de contraseña -->
                <div class="password-requirements">
                    <h4>Tu contraseña debe contener al menos:</h4>
                    <div class="requirement" id="length-req">
                        <span class="requirement-icon">✓</span>
                        <span>8 caracteres</span>
                    </div>
                    <div class="requirement" id="number-req">
                        <span class="requirement-icon">✓</span>
                        <span>1 número (0-9)</span>
                    </div>
                    <div class="requirement" id="lowercase-req">
                        <span class="requirement-icon">✓</span>
                        <span>1 letra minúscula y 1 letra mayúscula (Aa-Zz)</span>
                    </div>
                    <div class="requirement" id="symbol-req">
                        <span class="requirement-icon">✓</span>
                        <span>1 símbolo (!"#$%&'()*+,-./:;<=>?@[\]^_{|}~)</span>
                    </div>
                </div>
            </div>

            <!-- Nivel de ciclismo y Edad -->
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="nivel_ciclismo">Nivel de ciclismo</label>
                    <select id="nivel_ciclismo" name="nivel_ciclismo" class="form-select" required>
                        <option value="">Selecciona tu nivel</option>
                        <option value="principiante">Principiante</option>
                        <option value="intermedio">Intermedio</option>
                        <option value="avanzado">Avanzado</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="edad">Edad (opcional)</label>
                    <input type="number" id="edad" name="edad" class="form-input" 
                           placeholder="Tu edad" min="13" max="120">
                </div>
            </div>

            <!-- Botón de envío -->
            <button type="submit" class="submit-btn">Crear cuenta</button>
        </form>

        <!-- Mensajes -->
        <div id="mensaje"></div>

        <!-- Footer -->
        <div class="register-footer">
            <p class="login-text">¿Ya tienes cuenta?</p>
            <a href="login.php" class="login-link">Inicia sesión aquí</a>
        </div>
    </div>

    <script>
        /* ===== FUNCIONES PRINCIPALES ===== */
        
        // Actualizar requisitos de contraseña en tiempo real
        function updatePasswordRequirements() {
            const password = document.getElementById('password').value;
            
            // 8 caracteres
            const lengthReq = document.getElementById('length-req');
            if (password.length >= 8) {
                lengthReq.classList.add('valid');
            } else {
                lengthReq.classList.remove('valid');
            }
            
            // Número
            const numberReq = document.getElementById('number-req');
            if (/\d/.test(password)) {
                numberReq.classList.add('valid');
            } else {
                numberReq.classList.remove('valid');
            }
            
            // Minúscula y mayúscula
            const lowercaseReq = document.getElementById('lowercase-req');
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) {
                lowercaseReq.classList.add('valid');
            } else {
                lowercaseReq.classList.remove('valid');
            }
            
            // Símbolo
            const symbolReq = document.getElementById('symbol-req');
            if (/[!"#$%&'()*+,\-./:;<=>?@[\]^_{|}~]/.test(password)) {
                symbolReq.classList.add('valid');
            } else {
                symbolReq.classList.remove('valid');
            }
        }

        // Validación completa del formulario
        function validarRegistro() {
            console.log("=== Iniciando validación de registro ===");
            
            // Capturar datos del formulario
            const nombre = document.getElementById("nombre").value;
            const apellidos = document.getElementById("apellidos").value;
            const correo = document.getElementById("correo").value;
            const password = document.getElementById("password").value;
            const nivel_ciclismo = document.getElementById("nivel_ciclismo").value;
            const edad = document.getElementById("edad").value;
            
            let mensaje = "";
            let esValido = true;
            
            console.log("Datos capturados:", { 
                nombre, apellidos, correo, password, nivel_ciclismo, edad
            });

            // Validar campos requeridos
            if (nombre.trim() === "") {
                mensaje += "❌ El nombre es requerido.<br>";
                esValido = false;
            }

            if (apellidos.trim() === "") {
                mensaje += "❌ Los apellidos son requeridos.<br>";
                esValido = false;
            }

            if (nivel_ciclismo === "") {
                mensaje += "❌ Debes seleccionar tu nivel de ciclismo.<br>";
                esValido = false;
            }

            // Validar correo
            const regexCorreo = /^[^@]+@[^@]+\.[a-zA-Z]{2,}$/;
            if (!regexCorreo.test(correo)) {
                mensaje += "❌ El correo no tiene un formato válido.<br>";
                console.log("Error: formato de correo inválido");
                esValido = false;
            } else {
                console.log("Formato de correo válido ✔");
            }

            // Validar contraseña completa
            const regexPassword = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!"#$%&'()*+,\-./:;<=>?@[\]^_{|}~]).{8,}$/;
            if (!regexPassword.test(password)) {
                mensaje += "❌ La contraseña no cumple con todos los requisitos.<br>";
                console.log("Error: contraseña inválida");
                esValido = false;
            } else {
                console.log("Formato de contraseña válido ✔");
            }

            // Validar edad si se proporciona
            if (edad !== "" && (parseInt(edad) < 13 || parseInt(edad) > 120)) {
                mensaje += "❌ La edad debe estar entre 13 y 120 años.<br>";
                esValido = false;
            }

            // Mostrar resultado
            const mensajeElemento = document.getElementById("mensaje");
            if (esValido) {
                mensajeElemento.innerHTML = "✅ Validaciones correctas, creando cuenta...";
                mensajeElemento.className = "message success";
                console.log("Formulario listo para enviar 🚀");
                return true;
            } else {
                mensajeElemento.innerHTML = mensaje;
                mensajeElemento.className = "message error";
                console.log("Formulario bloqueado ❌");
                return false;
            }
        }

        /* ===== EVENT LISTENERS ===== */
        
        // Escuchar cambios en la contraseña
        document.getElementById('password').addEventListener('input', updatePasswordRequirements);

        // Manejo de mensajes desde URL al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
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