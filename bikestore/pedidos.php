<?php
session_start();
// pedidos.php

// 1. SEGURIDAD
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header("Location: login.php");
    exit;
}

include 'conexion.php';

$usuario_id = $_SESSION['usuario_id'];
$nombre_usuario = $_SESSION['usuario_nombre'];
$usuario_rol = $_SESSION['usuario_rol'];

// Obtener pedidos
$stmt = $conn->prepare("
    SELECT p.*, d.nombre_completo as direccion_nombre, d.ciudad, d.estado as estado_direccion
    FROM pedidos p
    LEFT JOIN direcciones d ON p.direccion_envio_id = d.id
    WHERE p.usuario_id = ?
    ORDER BY p.fecha DESC
");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$pedidos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Obtener contador carrito
$stmt = $conn->prepare("SELECT SUM(cantidad) as total FROM carrito WHERE usuario_id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$cart_count = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
$stmt->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Pedidos - BikeStore</title>
    <style>
        /* BASE DARK */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #0f0f0f;
            color: #fff;
            line-height: 1.5;
        }

        /* NAVBAR PREMIUM */
        .navbar {
            position: fixed; top: 0; left: 0; width: 100%; padding: 1.2rem 3rem;
            display: flex; justify-content: space-between; align-items: center;
            z-index: 1000; background: rgba(10, 10, 10, 0.95);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
        }
        .logo { font-size: 1.5rem; font-weight: 900; color: white; text-decoration: none; letter-spacing: -1px; text-transform: uppercase; }
        
        .nav-center a {
            color: rgba(255, 255, 255, 0.7); text-decoration: none; font-weight: 600;
            font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px;
            margin: 0 1.5rem; transition: color 0.3s;
        }
        .nav-center a:hover { color: #fff; }

        .nav-right { display: flex; align-items: center; gap: 1.5rem; }
        .icon-btn { color: white; text-decoration: none; position: relative; font-size: 1.2rem; background: none; border: none; cursor: pointer; }
        
        .cart-badge {
            position: absolute; top: -5px; right: -8px; background: #fff; color: #000;
            font-size: 0.6rem; font-weight: 800; padding: 2px 6px; border-radius: 10px;
        }

        /* DROPDOWN USUARIO CORREGIDO (Solución al texto invisible) */
        .user-dropdown { position: relative; padding-bottom: 10px; margin-bottom: -10px; }
        
        /* Botón del nombre de usuario: Fondo transparente y letras blancas */
        .user-btn {
            background: transparent !important; /* Forzar transparencia */
            color: #ffffff !important; /* Forzar blanco */
            border: 1px solid rgba(255, 255, 255, 0.3);
            padding: 6px 18px;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 700;
            cursor: pointer;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }
        .user-btn:hover {
            border-color: #ffffff;
            background: rgba(255, 255, 255, 0.1) !important;
        }

        .user-menu {
            display: none; position: absolute; right: 0; top: 100%;
            background: #1a1a1a; min-width: 200px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.5); border-radius: 8px;
            padding: 0.5rem 0; margin-top: 5px;
            border: 1px solid rgba(255,255,255,0.1);
            animation: fadeInUp 0.2s ease;
        }
        .user-dropdown::after { content: ''; position: absolute; top: 100%; left: 0; width: 100%; height: 20px; background: transparent; }
        .user-dropdown:hover .user-menu { display: block; }
        
        .menu-item {
            display: block; padding: 10px 20px; color: #ddd;
            text-decoration: none; font-size: 0.9rem; font-weight: 500;
            transition: background 0.2s; text-align: left;
        }
        .menu-item:hover { background: #333; color: white; }
        .menu-divider { height: 1px; background: rgba(255,255,255,0.1); margin: 5px 0; }

        /* CONTENIDO */
        .container { max-width: 900px; margin: 0 auto; padding: 8rem 1rem 4rem; }

        .page-header { 
            display: flex; justify-content: space-between; align-items: baseline; 
            margin-bottom: 3rem; border-bottom: 1px solid #222; padding-bottom: 1.5rem; 
        }
        .page-title { font-size: 2.5rem; font-weight: 800; letter-spacing: -1px; margin: 0; }
        
        /* LISTA DE PEDIDOS */
        .orders-list { display: flex; flex-direction: column; gap: 2rem; }
        
        .order-card { 
            background: #1a1a1a; border: 1px solid #333; border-radius: 8px; 
            overflow: hidden; transition: all 0.3s ease;
        }
        .order-card:hover { border-color: #555; transform: translateY(-2px); box-shadow: 0 10px 30px rgba(0,0,0,0.3); }

        .card-header { 
            background: #222; padding: 1.2rem 1.5rem; border-bottom: 1px solid #333; 
            display: flex; justify-content: space-between; align-items: center; 
        }
        
        .order-meta { display: flex; gap: 2rem; align-items: center; }
        .meta-group { display: flex; flex-direction: column; }
        .meta-label { font-size: 0.7rem; text-transform: uppercase; color: #666; letter-spacing: 1px; font-weight: 700; margin-bottom: 2px; }
        .meta-value { font-weight: 600; color: #fff; font-size: 1rem; font-family: monospace; }
        .meta-value.date { font-family: inherit; color: #ccc; }

        .status-badge { 
            padding: 0.3rem 0.8rem; border-radius: 4px; font-size: 0.75rem; 
            font-weight: 700; text-transform: uppercase; letter-spacing: 1px; 
            display: inline-flex; align-items: center; gap: 6px; border: 1px solid transparent;
        }
        
        .st-pendiente { color: #fbbf24; border-color: rgba(251, 191, 36, 0.3); background: rgba(251, 191, 36, 0.1); }
        .st-procesando { color: #60a5fa; border-color: rgba(96, 165, 250, 0.3); background: rgba(96, 165, 250, 0.1); }
        .st-enviado { color: #34d399; border-color: rgba(52, 211, 153, 0.3); background: rgba(52, 211, 153, 0.1); }
        .st-entregado { background: #34d399; color: #000; }
        .st-cancelado { color: #ef4444; border-color: rgba(239, 68, 68, 0.3); background: rgba(239, 68, 68, 0.1); }
        
        .status-dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; }

        .card-body { padding: 1.5rem; display: grid; grid-template-columns: 1fr auto; gap: 2rem; }
        
        .info-section { display: flex; flex-direction: column; gap: 0.5rem; font-size: 0.9rem; color: #aaa; }
        .info-row { display: flex; align-items: center; gap: 10px; }
        .icon-svg { width: 16px; height: 16px; color: #666; flex-shrink: 0; }

        .price-section { text-align: right; }
        .total-label { font-size: 0.8rem; color: #666; text-transform: uppercase; letter-spacing: 1px; }
        .total-amount { font-size: 1.8rem; font-weight: 700; color: #fff; margin-bottom: 1rem; }

        .actions-footer { display: flex; justify-content: flex-end; gap: 1rem; align-items: center; }
        
        .btn { 
            padding: 0.7rem 1.5rem; border-radius: 4px; font-size: 0.85rem; font-weight: 700; 
            text-decoration: none; cursor: pointer; transition: all 0.2s; border: 1px solid transparent; 
            text-transform: uppercase; letter-spacing: 1px;
        }
        
        .btn-outline { background: transparent; border-color: #fff; color: #fff; }
        .btn-outline:hover { background: #fff; color: #000; }
        
        .btn-danger-ghost { background: transparent; color: #ef4444; padding: 0.7rem 1rem; border-color: #333; }
        .btn-danger-ghost:hover { border-color: #ef4444; background: rgba(239, 68, 68, 0.1); }

        .empty-state { text-align: center; padding: 6rem 2rem; color: #666; }
        .empty-icon { width: 60px; height: 60px; color: #333; margin-bottom: 1rem; }
        .btn-primary { background: #fff; color: #000; padding: 1rem 2.5rem; border-radius: 4px; display: inline-block; margin-top: 2rem; text-decoration: none; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; transition: 0.3s; }
        .btn-primary:hover { background: #ccc; }

        .notification { position: fixed; top: 100px; right: 20px; padding: 1rem 1.5rem; border-radius: 4px; z-index: 2000; font-size: 0.9rem; font-weight: 600; display: none; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        .notif-success { background: #fff; color: #000; }
        .notif-error { background: #ef4444; color: #fff; }
        .show { display: block; animation: slideIn 0.3s; }
        
        @keyframes slideIn { from { transform: translateX(50px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        @media (max-width: 768px) {
            .navbar { padding: 1rem; }
            .nav-center { display: none; }
            .card-header { flex-direction: column; align-items: flex-start; gap: 1rem; }
            .card-body { grid-template-columns: 1fr; }
            .price-section { text-align: left; border-top: 1px solid #333; padding-top: 1.5rem; }
            .actions-footer { justify-content: flex-start; }
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="index.php" class="logo">BIKESTORE</a>
        
        <div class="nav-center">
            <a href="productos.php" class="nav-link">Catálogo</a>
            <a href="index.php#journal" class="nav-link">Journal</a>
        </div>

        <div class="nav-right">
            <a href="carrito.php" class="icon-btn">
                🛒
                <?php if($cart_count > 0): ?><span class="cart-badge"><?php echo $cart_count; ?></span><?php endif; ?>
            </a>
            
            <div class="user-dropdown">
                <button class="user-btn">
                    <?php echo strtoupper(explode(' ', $nombre_usuario)[0]); ?> ▾
                </button>
                <div class="user-menu">
                    <a href="perfil.php" class="menu-item">👤 Mi Perfil</a>
                    <a href="pedidos.php" class="menu-item" style="color: #fff; background: #333;">📦 Mis Pedidos</a>
                    <?php if($usuario_rol == 'admin'): ?>
                        <a href="principal_admin.php" class="menu-item">⚙️ Admin</a>
                    <?php elseif($usuario_rol == 'operador'): ?>
                        <a href="principal_operador.php" class="menu-item">⚙️ Operador</a>
                    <?php endif; ?>
                    <div class="menu-divider"></div>
                    <a href="logout.php" class="menu-item" style="color:#ef4444;">Cerrar Sesión</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container">
        <header class="page-header">
            <h1 class="page-title">Historial de Pedidos</h1>
        </header>

        <?php if (empty($pedidos)): ?>
            <div class="empty-state">
                <svg xmlns="http://www.w3.org/2000/svg" class="empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                <p style="font-size: 1.2rem;">Aún no has realizado ninguna compra.</p>
                <a href="productos.php" class="btn-primary">Explorar Catálogo</a>
            </div>
        <?php else: ?>
            <div class="orders-list">
                <?php foreach ($pedidos as $pedido): 
                    $fecha = date('d/m/Y', strtotime($pedido['fecha']));
                ?>
                <article class="order-card">
                    <div class="card-header">
                        <div class="order-meta">
                            <div class="meta-group">
                                <span class="meta-label">Pedido</span>
                                <span class="meta-value">#<?php echo str_pad($pedido['id'], 6, '0', STR_PAD_LEFT); ?></span>
                            </div>
                            <div class="meta-group">
                                <span class="meta-label">Fecha</span>
                                <span class="meta-value date"><?php echo $fecha; ?></span>
                            </div>
                        </div>
                        <div class="status-badge st-<?php echo $pedido['estado']; ?>">
                            <span class="status-dot"></span>
                            <?php echo ucfirst($pedido['estado']); ?>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="info-section">
                            <div class="info-row">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                <span style="font-weight: 600; color: #fff;"><?php echo htmlspecialchars($pedido['direccion_nombre'] ?? 'Dirección registrada'); ?></span>
                            </div>
                            <div class="info-row" style="color: #888; font-size: 0.85rem; margin-left: 26px;">
                                <?php echo htmlspecialchars($pedido['ciudad'] . ', ' . $pedido['estado_direccion']); ?>
                            </div>
                        </div>

                        <div class="price-section">
                            <div class="total-label">Total</div>
                            <div class="total-amount">$<?php echo number_format($pedido['total'], 2); ?></div>
                            
                            <div class="actions-footer">
                                <?php if ($pedido['estado'] === 'pendiente'): ?>
                                    <form action="cancelar_pedido.php" method="POST" onsubmit="return confirm('¿Seguro que deseas cancelar este pedido? Se reembolsará el stock.');" style="display:inline;">
                                        <input type="hidden" name="pedido_id" value="<?php echo $pedido['id']; ?>">
                                        <button type="submit" class="btn btn-danger-ghost">Cancelar</button>
                                    </form>
                                <?php endif; ?>

                                <a href="detalle_pedido.php?id=<?php echo $pedido['id']; ?>" class="btn btn-outline">Ver Detalle</a>
                            </div>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div id="notification" class="notification"></div>

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            const params = new URLSearchParams(window.location.search);
            if (params.get('success')) showNotif(decodeURIComponent(params.get('success')), 'notif-success');
            if (params.get('error')) showNotif(decodeURIComponent(params.get('error')), 'notif-error');
            
            if (params.get('success') || params.get('error')) {
                window.history.replaceState({}, document.title, 'pedidos.php');
            }
        });

        function showNotif(msg, className) {
            const n = document.getElementById('notification');
            n.textContent = msg;
            n.className = `notification ${className} show`;
            setTimeout(() => n.className = 'notification', 4000);
        }
    </script>
</body>
</html>