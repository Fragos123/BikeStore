<?php
session_start();
include 'conexion.php';

$token = isset($_GET['token']) ? trim($_GET['token']) : '';

if (empty($token)) {
    die("Error: Token no proporcionado.");
}

$stmt = $conn->prepare("SELECT id FROM usuarios WHERE reset_token = ? AND reset_token_exp > NOW()");
$stmt->bind_param("s", $token);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    die("Error: El enlace de recuperación es inválido o ha expirado.");
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Contraseña - BikeStore</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2rem 1rem; background: #2c3e50; }
        .video-background { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -2; object-fit: cover; }
        .video-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(135deg, rgba(0,0,0,0.1) 0%, rgba(0,0,0,0.15) 50%, rgba(0,0,0,0.1) 100%); z-index: -1; }
        
        .login-container { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(20px) saturate(180%); border-radius: 24px; padding: 2.8rem; width: 100%; max-width: 420px; box-shadow: 0 25px 50px rgba(0,0,0,0.1), 0 0 0 2px rgba(255,255,255,0.3); border: 2px solid rgba(255,255,255,0.3); text-align: center; }
        .logo { font-size: 2rem; font-weight: 800; color: #fff; margin-bottom: 0.5rem; display: block; text-decoration: none; text-shadow: 0 2px 10px rgba(0,0,0,0.3); }
        .login-title { font-size: 1.1rem; font-weight: 600; color: rgba(255,255,255,0.9); margin-bottom: 2rem; }
        
        .form-group { margin-bottom: 1.5rem; text-align: left; }
        .form-label { display: block; font-weight: 600; color: rgba(255,255,255,0.9); margin-bottom: 0.5rem; font-size: 0.9rem; }
        .form-input { width: 100%; padding: 1.1rem; border: 2px solid rgba(255,255,255,0.3); border-radius: 12px; font-size: 1rem; background: rgba(255,255,255,0.05); color: #fff; outline: none; transition: 0.3s; }
        .form-input:focus { border-color: rgba(255,255,255,0.6); background: rgba(255,255,255,0.1); }
        
        /* PASSWORD */
        .password-wrapper { position: relative; display: flex; align-items: center; }
        .password-wrapper input { padding-right: 45px; }
        .toggle-password { position: absolute; right: 15px; cursor: pointer; color: rgba(255, 255, 255, 0.6); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; transition: color 0.3s ease; }
        .toggle-password:hover { color: #fff; }
        .toggle-password svg { width: 20px; height: 20px; }

        .submit-btn { width: 100%; padding: 1.2rem; background: rgba(255,255,255,0.1); color: white; border: 2px solid rgba(255,255,255,0.4); border-radius: 12px; font-weight: 600; cursor: pointer; transition: 0.3s; font-size: 1rem; }
        .submit-btn:hover { background: rgba(255,255,255,0.2); transform: translateY(-2px); }
        
        .message { padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: 0.9rem; text-align: left; }
        .message.error { background: rgba(231, 76, 60, 0.2); color: #ff6b6b; border: 1px solid #ff4757; }

        /* FIX DOBLE OJO */
        input[type="password"]::-ms-reveal, input[type="password"]::-ms-clear { display: none; }
        input[type="password"]::-webkit-contacts-auto-fill-button, input[type="password"]::-webkit-credentials-auto-fill-button { visibility: hidden; display: none !important; pointer-events: none; position: absolute; right: 0; }
    </style>
</head>
<body>
    <video autoplay muted loop class="video-background">
        <source src="fondo.mp4" type="video/mp4">
    </video>
    <div class="video-overlay"></div>

    <div class="login-container">
        <a href="index.php" class="logo">BIKESTORE</a>
        <p class="login-title">Establecer Nueva Contraseña</p>

        <?php if (isset($_GET['error'])): ?>
            <div class="message error">❌ <?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <form action="procesar_restablecer_password.php" method="POST">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
            
            <div class="form-group">
                <label class="form-label" for="password">Nueva Contraseña</label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" class="form-input" placeholder="Mínimo 8 caracteres" required>
                    <span class="toggle-password" onclick="togglePassword('password', this)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </span>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="confirm_password">Confirmar Contraseña</label>
                <div class="password-wrapper">
                    <input type="password" id="confirm_password" name="confirm_password" class="form-input" placeholder="Repite la contraseña" required>
                    <span class="toggle-password" onclick="togglePassword('confirm_password', this)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </span>
                </div>
            </div>
            
            <button type="submit" class="submit-btn">Guardar Contraseña</button>
        </form>
    </div>

    <script>
        function togglePassword(inputId, iconElement) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
                // Icono Ojo Tachado (Oculto)
                iconElement.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>';
            } else {
                input.type = 'password';
                // Icono Ojo (Visible)
                iconElement.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
            }
        }
    </script>
</body>
</html>