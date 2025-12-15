<?php
session_start();
// Seguridad básica
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header("Location: login.php");
    exit;
}

$pedido_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$nombre_usuario = $_SESSION['usuario_nombre'];

// Opcional: Verificar que el pedido exista y sea del usuario (para evitar curiosos)
if($pedido_id > 0) {
    include 'conexion.php';
    $stmt = $conn->prepare("SELECT id FROM pedidos WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $pedido_id, $_SESSION['usuario_id']);
    $stmt->execute();
    if($stmt->get_result()->num_rows === 0) {
        // Si no es su pedido, redirigir
        header("Location: index.php");
        exit;
    }
    $stmt->close();
    $conn->close();
} else {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>¡Gracias por tu compra! - BikeStore</title>
    <style>
        /* BASE DARK */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #0f0f0f;
            color: #fff;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* NAVBAR (Simplificada para no distraer) */
        .navbar {
            padding: 1.5rem 3rem; display: flex; justify-content: center; align-items: center;
            background: rgba(10, 10, 10, 0.95); border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        .logo { font-size: 1.5rem; font-weight: 900; color: white; text-decoration: none; letter-spacing: -1px; text-transform: uppercase; }

        /* CONTENIDO CENTRADO */
        .main-content { 
            flex: 1; display: flex; align-items: center; justify-content: center; 
            padding: 4rem 2rem; 
        }
        
        .success-card {
            background: #1a1a1a; border: 1px solid #333; border-radius: 12px;
            padding: 4rem 3rem; width: 100%; max-width: 600px; text-align: center;
            box-shadow: 0 20px 60px rgba(0,0,0,0.6);
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        /* ANIMACIÓN CHECKMARK */
        .icon-container {
            width: 80px; height: 80px; background: rgba(52, 211, 153, 0.1);
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            margin: 0 auto 2rem; border: 2px solid #34d399;
            animation: popIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) 0.3s backwards;
        }
        .icon-svg { width: 40px; height: 40px; color: #34d399; }

        .title { font-size: 2.5rem; font-weight: 800; margin-bottom: 1rem; line-height: 1.1; }
        .subtitle { color: #aaa; font-size: 1.1rem; margin-bottom: 2rem; font-weight: 300; }
        
        .order-info {
            background: #222; padding: 1.5rem; border-radius: 8px; margin-bottom: 2.5rem;
            display: inline-block; width: 100%; border: 1px dashed #444;
        }
        .order-label { font-size: 0.8rem; text-transform: uppercase; color: #666; letter-spacing: 1px; font-weight: 700; margin-bottom: 0.5rem; display: block; }
        .order-number { font-size: 1.8rem; font-weight: 800; color: #fff; letter-spacing: 1px; font-family: monospace; }

        /* BOTONES */
        .actions { display: grid; gap: 1rem; }
        
        .btn {
            display: block; padding: 1.2rem; border-radius: 4px; text-align: center;
            text-decoration: none; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;
            transition: all 0.3s ease; cursor: pointer; border: 1px solid transparent;
        }
        
        .btn-primary { background: #fff; color: #000; }
        .btn-primary:hover { background: #ccc; transform: translateY(-2px); }
        
        .btn-secondary { background: transparent; border-color: #333; color: #fff; }
        .btn-secondary:hover { border-color: #fff; background: rgba(255,255,255,0.05); }
        
        .btn-link { background: transparent; color: #666; font-size: 0.9rem; text-transform: none; margin-top: 1rem; }
        .btn-link:hover { color: #fff; text-decoration: underline; }

        /* FOOTER SIMPLE */
        .footer { text-align: center; padding: 2rem; color: #444; font-size: 0.8rem; }

        @keyframes slideUp { from { transform: translateY(40px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        @keyframes popIn { from { transform: scale(0); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        
        @media print {
            .navbar, .actions, .footer { display: none; }
            body { background: white; color: black; }
            .success-card { box-shadow: none; border: 2px solid #000; }
            .icon-container { border-color: black; }
            .icon-svg { color: black; }
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="index.php" class="logo">BIKESTORE</a>
    </nav>

    <div class="main-content">
        <div class="success-card">
            <div class="icon-container">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
            </div>
            
            <h1 class="title">¡Pago Exitoso!</h1>
            <p class="subtitle">Gracias por tu compra, <?php echo explode(' ', $nombre_usuario)[0]; ?>.<br>Hemos enviado la confirmación a tu correo.</p>
            
            <div class="order-info">
                <span class="order-label">Tu número de pedido</span>
                <div class="order-number">#<?php echo str_pad($pedido_id, 6, '0', STR_PAD_LEFT); ?></div>
            </div>

            <div class="actions">
                <a href="detalle_pedido.php?id=<?php echo $pedido_id; ?>" class="btn btn-primary">Ver Detalles del Pedido</a>
                <a href="productos.php" class="btn btn-secondary">Seguir Comprando</a>
                <button onclick="window.print()" class="btn btn-link">🖨️ Imprimir Comprobante</button>
            </div>
        </div>
    </div>

    <footer class="footer">
        &copy; <?php echo date('Y'); ?> BikeStore Inc.
    </footer>

</body>
</html>