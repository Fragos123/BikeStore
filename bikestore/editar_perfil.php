<?php
session_start();
// Verificar sesión
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header("Location: login.php");
    exit;
}

include 'conexion.php';
$usuario_id = $_SESSION['usuario_id'];

// Obtener datos actuales del usuario para rellenar el formulario
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
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
        /* ESTILOS BASE DARK */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0f0f0f;
            color: #fff;
            display: flex; justify-content: center; align-items: center;
            min-height: 100vh;
        }

        .form-card { 
            background: #1a1a1a; 
            border: 1px solid #333; 
            padding: 2.5rem; 
            border-radius: 8px; 
            width: 100%; 
            max-width: 450px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }

        h2 { text-align: center; margin-bottom: 2rem; font-weight: 800; letter-spacing: -1px; }

        label { 
            display: block; margin-bottom: 0.5rem; 
            font-size: 0.8rem; font-weight: 700; color: #888; text-transform: uppercase; letter-spacing: 1px;
        }

        .form-input, .form-select { 
            width: 100%; padding: 1rem; margin-bottom: 1.5rem; 
            background: #0f0f0f; border: 1px solid #333; 
            color: white; border-radius: 4px; outline: none; font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-input:focus, .form-select:focus { border-color: #fff; }

        /* Botones */
        .btn { 
            width: 100%; padding: 1rem; background: white; color: black; 
            border: none; font-weight: 800; cursor: pointer; border-radius: 4px; 
            text-transform: uppercase; letter-spacing: 1px; transition: 0.3s;
        }
        .btn:hover { background: #ccc; }

        .btn-cancel { 
            background: transparent; color: #ccc; border: 1px solid #333; margin-top: 1rem; 
        }
        .btn-cancel:hover { border-color: #fff; color: #fff; background: transparent; }

        /* Estilos de Error JS */
        .error-msg { 
            background: rgba(239, 68, 68, 0.1); border: 1px solid #ef4444; color: #fca5a5; 
            padding: 0.8rem; margin-bottom: 1.5rem; border-radius: 4px; 
            display: none; text-align: center; font-size: 0.9rem; font-weight: 600;
        }
        .input-error { border-color: #ef4444 !important; }
    </style>
</head>
<body>

    <div class="form-card">
        <h2>Editar Datos</h2>
        
        <div id="errorBox" class="error-msg"></div>

        <form action="procesar_editar_perfil.php" method="POST" onsubmit="return validarPerfil()">
            
            <label>Nombre Completo</label>
            <input type="text" id="nombre" name="nombre" class="form-input" 
                   value="<?php echo htmlspecialchars($usuario['nombre']); ?>" placeholder="Tu nombre">

            <label>Correo Electrónico</label>
            <input type="email" id="correo" name="correo" class="form-input" 
                   value="<?php echo htmlspecialchars($usuario['correo']); ?>" placeholder="correo@ejemplo.com">

            <label>Edad</label>
            <input type="number" id="edad" name="edad" class="form-input" 
                   value="<?php echo htmlspecialchars($usuario['edad']); ?>" placeholder="Ej: 25">

            <label>Nivel de Ciclismo</label>
            <select id="nivel" name="nivel_ciclismo" class="form-select">
                <option value="">Selecciona una opción...</option>
                <option value="principiante" <?php echo ($usuario['nivel_ciclismo']=='principiante') ? 'selected' : ''; ?>>Principiante</option>
                <option value="intermedio" <?php echo ($usuario['nivel_ciclismo']=='intermedio') ? 'selected' : ''; ?>>Intermedio</option>
                <option value="avanzado" <?php echo ($usuario['nivel_ciclismo']=='avanzado') ? 'selected' : ''; ?>>Avanzado</option>
            </select>

            <button type="submit" class="btn">Guardar Cambios</button>
            <a href="perfil.php"><button type="button" class="btn btn-cancel">Cancelar</button></a>
        </form>
    </div>

    <script>
        function validarPerfil() {
            const nombre = document.getElementById('nombre');
            const correo = document.getElementById('correo');
            const edad = document.getElementById('edad');
            const nivel = document.getElementById('nivel');
            const errorBox = document.getElementById('errorBox');
            
            let errores = [];

            // 1. Limpiar estilos previos
            [nombre, correo, edad, nivel].forEach(el => el.classList.remove('input-error'));
            errorBox.style.display = 'none';

            // 2. Validaciones Lógicas
            
            // Nombre: Al menos 3 caracteres
            if (nombre.value.trim().length < 3) {
                nombre.classList.add('input-error');
                errores.push("El nombre debe tener al menos 3 letras.");
            }

            // Correo: Regex simple de email
            const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!regexEmail.test(correo.value)) {
                correo.classList.add('input-error');
                errores.push("El formato del correo no es válido.");
            }

            // Edad: Rango realista (13 a 120 años)
            if (!edad.value || edad.value < 13 || edad.value > 120) {
                edad.classList.add('input-error');
                errores.push("La edad debe estar entre 13 y 120 años.");
            }

            // Nivel: Obligatorio
            if (nivel.value === "") {
                nivel.classList.add('input-error');
                errores.push("Debes seleccionar un nivel de ciclismo.");
            }

            // 3. Mostrar errores si existen
            if (errores.length > 0) {
                errorBox.innerHTML = errores.join("<br>");
                errorBox.style.display = 'block';
                return false; // Detener envío
            }

            return true; // Permitir envío
        }
    </script>
</body>
</html>