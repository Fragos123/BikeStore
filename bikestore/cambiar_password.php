<?php
session_start();
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header("Location: login.php");
    exit;
}

include 'conexion.php';
$usuario_id = $_SESSION['usuario_id'];
$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $actual = $_POST['password_actual'];
    $nueva = $_POST['password_nueva'];
    $confirmar = $_POST['password_confirmar'];

    // Verificar contraseña actual
    $stmt = $conn->prepare("SELECT password FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!password_verify($actual, $res['password'])) {
        $mensaje = "La contraseña actual es incorrecta.";
        $tipo_mensaje = "error";
    } elseif ($nueva !== $confirmar) {
        $mensaje = "Las contraseñas nuevas no coinciden.";
        $tipo_mensaje = "error";
    } elseif (strlen($nueva) < 8) {
        $mensaje = "La nueva contraseña debe tener al menos 8 caracteres.";
        $tipo_mensaje = "error";
    } else {
        // Actualizar
        $nueva_hash = password_hash($nueva, PASSWORD_DEFAULT);
        $stmt_upd = $conn->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
        $stmt_upd->bind_param("si", $nueva_hash, $usuario_id);
        
        if ($stmt_upd->execute()) {
            $mensaje = "Contraseña actualizada exitosamente.";
            $tipo_mensaje = "success";
        } else {
            $mensaje = "Error al actualizar.";
            $tipo_mensaje = "error";
        }
        $stmt_upd->close();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar Contraseña - BikeStore</title>
    <style>
        /* BASE OSCURA */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #0f0f0f; color: #fff; height: 100vh;
            display: flex; flex-direction: column;
        }

        /* NAVBAR */
        .navbar {
            padding: 1.5rem 3rem; display: flex; justify-content: space-between; align-items: center;
            background: rgba(10, 10, 10, 0.95); border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        .logo { font-size: 1.5rem; font-weight: 900; color: white; text-decoration: none; letter-spacing: -1px; text-transform: uppercase; }
        .nav-link { color: #ccc; text-decoration: none; font-size: 0.9rem; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; transition: color 0.3s; }
        .nav-link:hover { color: white; }

        /* CONTAINER */
        .main-content { flex: 1; display: flex; align-items: center; justify-content: center; padding: 2rem; }
        .form-card {
            background: #1a1a1a; border: 1px solid #333; border-radius: 8px;
            padding: 3rem; width: 100%; max-width: 500px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.5);
        }

        .card-title { font-size: 1.8rem; font-weight: 800; margin-bottom: 0.5rem; text-align: center; }
        .card-subtitle { color: #666; text-align: center; margin-bottom: 2rem; font-size: 0.9rem; }

        /* INPUTS & PASSWORD WRAPPER */
        .form-group { margin-bottom: 1.5rem; }
        .form-label { display: block; font-size: 0.8rem; font-weight: 700; color: #888; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.5rem; }
        
        .password-wrapper { position: relative; display: flex; align-items: center; }
        
        .form-input {
            width: 100%; padding: 1rem; background: #0f0f0f; border: 1px solid #333;
            border-radius: 4px; color: white; font-size: 1rem; outline: none;
            transition: border-color 0.3s; padding-right: 45px;
        }
        .form-input:focus { border-color: #fff; }

        .toggle-password {
            position: absolute; right: 15px; cursor: pointer; color: #666;
            display: flex; align-items: center; justify-content: center;
            transition: color 0.3s;
        }
        .toggle-password:hover { color: white; }
        .toggle-password svg { width: 20px; height: 20px; }

        /* BOTONES */
        .btn-group { display: flex; gap: 1rem; margin-top: 2rem; }
        .btn {
            flex: 1; padding: 1rem; border-radius: 4px; font-weight: 700; font-size: 0.9rem;
            text-transform: uppercase; letter-spacing: 1px; cursor: pointer; text-align: center;
            text-decoration: none; border: 1px solid transparent; transition: all 0.3s;
        }
        .btn-primary { background: white; color: black; }
        .btn-primary:hover { background: #ccc; }
        .btn-secondary { background: transparent; border-color: #333; color: #ccc; }
        .btn-secondary:hover { border-color: white; color: white; }

        /* MENSAJES */
        .alert { padding: 1rem; border-radius: 4px; margin-bottom: 1.5rem; font-size: 0.9rem; text-align: center; font-weight: 600; }
        .alert-success { background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid #059669; }
        .alert-error { background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid #dc2626; }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="index.php" class="logo">BIKESTORE</a>
        <a href="perfil.php" class="nav-link">← Volver al Perfil</a>
    </nav>

    <div class="main-content">
        <div class="form-card">
            <h1 class="card-title">Seguridad</h1>
            <p class="card-subtitle">Cambia tu contraseña de acceso</p>

            <?php if ($mensaje): ?>
                <div class="alert alert-<?php echo $tipo_mensaje; ?>">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="form-group">
                    <label class="form-label">Contraseña Actual</label>
                    <div class="password-wrapper">
                        <input type="password" name="password_actual" id="pass_actual" class="form-input" required>
                        <span class="toggle-password" onclick="togglePassword('pass_actual', this)">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Nueva Contraseña</label>
                    <div class="password-wrapper">
                        <input type="password" name="password_nueva" id="pass_nueva" class="form-input" required>
                        <span class="toggle-password" onclick="togglePassword('pass_nueva', this)">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Confirmar Nueva</label>
                    <div class="password-wrapper">
                        <input type="password" name="password_confirmar" id="pass_confirmar" class="form-input" required>
                        <span class="toggle-password" onclick="togglePassword('pass_confirmar', this)">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </span>
                    </div>
                </div>

                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">Actualizar</button>
                    <a href="perfil.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function togglePassword(inputId, iconElement) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
                // Icono Tachado
                iconElement.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>';
            } else {
                input.type = 'password';
                // Icono Abierto
                iconElement.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
            }
        }
    </script>
</body>
</html>