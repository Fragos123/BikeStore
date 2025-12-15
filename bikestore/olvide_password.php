<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - BikeStore</title>
    <style>
        /* Mismos estilos base que tu login.php */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }
        .video-background { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -2; object-fit: cover; }
        .video-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(135deg, rgba(0,0,0,0.1) 0%, rgba(0,0,0,0.15) 50%, rgba(0,0,0,0.1) 100%); z-index: -1; }
        
        .login-container {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px) saturate(180%);
            border-radius: 24px;
            padding: 2.8rem;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.1), 0 0 0 2px rgba(255,255,255,0.3);
            border: 2px solid rgba(255,255,255,0.3);
            text-align: center;
        }

        .logo { font-size: 2rem; font-weight: 800; color: #fff; margin-bottom: 0.5rem; display: block; text-decoration: none; text-shadow: 0 2px 10px rgba(0,0,0,0.3); }
        .login-title { font-size: 1.1rem; font-weight: 600; color: rgba(255,255,255,0.9); margin-bottom: 2rem; }
        
        .form-group { margin-bottom: 1.5rem; text-align: left; }
        .form-label { display: block; font-weight: 600; color: rgba(255,255,255,0.9); margin-bottom: 0.5rem; font-size: 0.9rem; }
        .form-input {
            width: 100%; padding: 1.1rem; border: 2px solid rgba(255,255,255,0.3);
            border-radius: 12px; font-size: 1rem; background: rgba(255,255,255,0.05);
            color: #fff; outline: none; transition: 0.3s;
        }
        .form-input:focus { border-color: rgba(255,255,255,0.6); background: rgba(255,255,255,0.1); }
        
        .submit-btn {
            width: 100%; padding: 1.2rem; background: rgba(255,255,255,0.1); color: white;
            border: 2px solid rgba(255,255,255,0.4); border-radius: 12px; font-weight: 600;
            cursor: pointer; transition: 0.3s; font-size: 1rem;
        }
        .submit-btn:hover { background: rgba(255,255,255,0.2); transform: translateY(-2px); }
        
        .back-link { display: inline-block; margin-top: 1.5rem; color: rgba(255,255,255,0.8); text-decoration: none; font-size: 0.9rem; }
        .back-link:hover { color: white; text-decoration: underline; }

        .message { padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: 0.9rem; text-align: left; }
        .message.error { background: rgba(231, 76, 60, 0.2); color: #ff6b6b; border: 1px solid #ff4757; }
        .message.success { background: rgba(46, 204, 113, 0.2); color: #7bed9f; border: 1px solid #2ecc71; }
    </style>
</head>
<body>
    <video autoplay muted loop class="video-background">
        <source src="fondo.mp4" type="video/mp4">
    </video>
    <div class="video-overlay"></div>

    <div class="login-container">
        <a href="index.php" class="logo">BIKESTORE</a>
        <p class="login-title">Recuperar Contraseña</p>

        <?php if (isset($_GET['error'])): ?>
            <div class="message error">❌ <?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['success'])): ?>
            <div class="message success">✅ <?php echo $_GET['success']; ?></div>
        <?php endif; ?>

        <form action="procesar_olvide_password.php" method="POST">
            <div class="form-group">
                <label class="form-label" for="correo">Ingresa tu correo electrónico</label>
                <input type="email" id="correo" name="correo" class="form-input" placeholder="tu@email.com" required>
            </div>
            
            <button type="submit" class="submit-btn">Enviar enlace de recuperación</button>
        </form>

        <a href="login.php" class="back-link">← Volver al inicio de sesión</a>
    </div>
</body>
</html>