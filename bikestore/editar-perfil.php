<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header("Location: login.php");
    exit;
}

// Obtener datos del usuario
include 'conexion.php';

$usuario_id = $_SESSION['usuario_id'];
$stmt = $conn->prepare("SELECT id, nombre, correo, nivel_ciclismo, edad FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil - BikeStore</title>
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
            max-width: 800px;
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

        .edit-card {
            background: #F1F5F9;
            border-radius: 24px;
            padding: 3rem;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.15);
            animation: fadeInUp 0.6s ease-out;
        }

        .edit-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .edit-icon {
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

        .edit-title {
            font-size: 2rem;
            font-weight: 900;
            color: #2E3848;
            margin-bottom: 0.5rem;
        }

        .edit-subtitle {
            color: #6b7280;
            font-size: 1rem;
        }

        .edit-form {
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

        .form-input,
        .form-select {
            padding: 1rem 1.25rem;
            border: 2px solid #d1d5db;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            font-family: inherit;
            background: white;
        }

        .form-input:focus,
        .form-select:focus {
            outline: none;
            border-color: #2E3848;
            background: white;
            box-shadow: 0 0 0 3px rgba(46, 56, 72, 0.1);
        }

        .form-input:disabled {
            background: #e5e7eb;
            cursor: not-allowed;
            color: #9ca3af;
        }

        .form-note {
            font-size: 0.85rem;
            color: #6b7280;
            font-style: italic;
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

            .edit-card {
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

        <div class="edit-card">
            <div class="edit-header">
                <div class="edit-icon">✏️</div>
                <h1 class="edit-title">Editar Perfil</h1>
                <p class="edit-subtitle">Actualiza tu información personal</p>
            </div>

            <!-- Mensajes -->
            <div id="mensaje"></div>

            <form class="edit-form" action="procesar_editar_perfil.php" method="POST" onsubmit="return validarFormulario()">
                
                <div class="form-group">
                    <label class="form-label" for="nombre">Nombre Completo</label>
                    <input type="text" id="nombre" name="nombre" class="form-input" 
                           value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="correo">Correo Electrónico</label>
                    <input type="email" id="correo" name="correo" class="form-input" 
                           value="<?php echo htmlspecialchars($usuario['correo']); ?>" required>
                    <span class="form-note">Asegúrate de usar un correo válido</span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="nivel_ciclismo">Nivel de Ciclismo</label>
                    <select id="nivel_ciclismo" name="nivel_ciclismo" class="form-select" required>
                        <option value="principiante" <?php echo $usuario['nivel_ciclismo'] === 'principiante' ? 'selected' : ''; ?>>Principiante</option>
                        <option value="intermedio" <?php echo $usuario['nivel_ciclismo'] === 'intermedio' ? 'selected' : ''; ?>>Intermedio</option>
                        <option value="avanzado" <?php echo $usuario['nivel_ciclismo'] === 'avanzado' ? 'selected' : ''; ?>>Avanzado</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="edad">Edad (opcional)</label>
                    <input type="number" id="edad" name="edad" class="form-input" 
                           value="<?php echo $usuario['edad'] ? $usuario['edad'] : ''; ?>" 
                           min="13" max="120" placeholder="Ingresa tu edad">
                    <span class="form-note">Deja en blanco si no deseas especificar</span>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn btn-primary">
                        💾 Guardar Cambios
                    </button>
                    <a href="perfil.php" class="btn btn-secondary">
                        ✕ Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function validarFormulario() {
            const nombre = document.getElementById('nombre').value.trim();
            const correo = document.getElementById('correo').value.trim();
            const nivel = document.getElementById('nivel_ciclismo').value;
            const edad = document.getElementById('edad').value;
            
            const mensajeElemento = document.getElementById('mensaje');
            let mensaje = '';
            let esValido = true;

            // Validar nombre
            if (nombre === '') {
                mensaje += '❌ El nombre es requerido.<br>';
                esValido = false;
            }

            // Validar correo
            const regexCorreo = /^[^@]+@[^@]+\.[a-zA-Z]{2,}$/;
            if (!regexCorreo.test(correo)) {
                mensaje += '❌ El formato del correo no es válido.<br>';
                esValido = false;
            }

            // Validar nivel
            if (nivel === '') {
                mensaje += '❌ Debes seleccionar un nivel de ciclismo.<br>';
                esValido = false;
            }

            // Validar edad si se proporciona
            if (edad !== '' && (parseInt(edad) < 13 || parseInt(edad) > 120)) {
                mensaje += '❌ La edad debe estar entre 13 y 120 años.<br>';
                esValido = false;
            }

            if (!esValido) {
                mensajeElemento.innerHTML = mensaje;
                mensajeElemento.className = 'message error';
                return false;
            }

            return true;
        }

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