<?php
session_start();
// 1. SEGURIDAD
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header("Location: login.php");
    exit;
}

include 'conexion.php';

$pedido_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$usuario_id = $_SESSION['usuario_id'];
$nombre_usuario = $_SESSION['usuario_nombre'];
$usuario_rol = $_SESSION['usuario_rol'];

// 2. OBTENER DATOS DEL PEDIDO (Con lógica de permisos inteligente)
$sql = "
    SELECT p.*, 
           d.nombre_completo, d.calle, d.numero_exterior, d.colonia, d.ciudad, d.estado as estado_dir, d.codigo_postal, d.telefono,
           m.tipo as tarjeta_tipo, m.ultimos_digitos
    FROM pedidos p
    LEFT JOIN direcciones d ON p.direccion_envio_id = d.id
    LEFT JOIN metodos_pago m ON p.metodo_pago_id = m.id
    WHERE p.id = ?
";

// REGLA CLAVE: Si NO es personal (admin/operador), filtramos para que solo vea SUS pedidos.
// Si ES personal, no agregamos este filtro, permitiéndole ver cualquier pedido.
if (!in_array($usuario_rol, ['admin', 'operador'])) {
    $sql .= " AND p.usuario_id = ?";
}

$stmt = $conn->prepare($sql);

if (in_array($usuario_rol, ['admin', 'operador'])) {
    // Admin/Operador: Solo vinculamos el ID del pedido
    $stmt->bind_param("i", $pedido_id);
} else {
    // Cliente: Vinculamos ID pedido y SU ID de usuario
    $stmt->bind_param("ii", $pedido_id, $usuario_id);
}

$stmt->execute();
$pedido = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$pedido) {
    // Si no se encuentra, redirigir inteligentemente
    if (in_array($usuario_rol, ['admin', 'operador'])) {
        header("Location: lista_ventas.php"); // Volver a la lista de gestión
    } else {
        header("Location: pedidos.php"); // Volver a "Mis Pedidos"
    }
    exit;
}

// 3. OBTENER ITEMS DEL PEDIDO
$sql_items = "
    SELECT pi.*, p.imagen_principal 
    FROM pedido_items pi
    LEFT JOIN productos p ON pi.producto_id = p.id
    WHERE pi.pedido_id = ?
";
$stmt = $conn->prepare($sql_items);
$stmt->bind_param("i", $pedido_id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Contador carrito para navbar
$stmt = $conn->prepare("SELECT SUM(cantidad) as total FROM carrito WHERE usuario_id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$cart_count = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
$stmt->close();

$conn->close();

// Definir link de regreso según el rol
if ($usuario_rol === 'admin' || $usuario_rol === 'operador') {
    $back_link = 'lista_ventas.php';
    $back_text = '← Volver a Gestión de Ventas';
} else {
    $back_link = 'pedidos.php';
    $back_text = '← Volver a Mis Pedidos';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido #<?php echo $pedido_id; ?> - BikeStore</title>
    <style>
        /* BASE DARK */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #0f0f0f;
            color: #fff;
            line-height: 1.5;
        }

        /* NAVBAR */
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
        .icon-btn { color: white; text-decoration: none; position: relative; font-size: 1.2rem; background:none; border:none; cursor:pointer; }
        .cart-badge {
            position: absolute; top: -5px; right: -8px; background: #fff; color: #000;
            font-size: 0.6rem; font-weight: 800; padding: 2px 6px; border-radius: 10px;
        }

        /* DROPDOWN USUARIO */
        .user-dropdown { position: relative; padding-bottom: 10px; margin-bottom: -10px; }
        .user-btn {
            background: transparent !important; color: #ffffff !important;
            border: 1px solid rgba(255, 255, 255, 0.3); padding: 6px 18px;
            border-radius: 50px; font-size: 0.9rem; font-weight: 700; cursor: pointer;
            letter-spacing: 1px; transition: all 0.3s ease;
        }
        .user-btn:hover { border-color: #ffffff; background: rgba(255, 255, 255, 0.1) !important; }

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
        .menu-item { display: block; padding: 10px 20px; color: #ddd; text-decoration: none; font-size: 0.9rem; transition: 0.2s; }
        .menu-item:hover { background: #333; color: white; }
        .menu-divider { height: 1px; background: rgba(255,255,255,0.1); margin: 5px 0; }

        /* LAYOUT DETALLE */
        .container { max-width: 1100px; margin: 0 auto; padding: 8rem 2rem 4rem; }

        .order-header { 
            display: flex; justify-content: space-between; align-items: center; 
            margin-bottom: 3rem; border-bottom: 1px solid #333; padding-bottom: 2rem; flex-wrap: wrap; gap: 1rem;
        }
        .order-title h1 { font-size: 2.5rem; font-weight: 900; margin: 0; letter-spacing: -1px; }
        .order-date { color: #888; font-size: 1rem; margin-top: 0.5rem; }

        .status-badge { 
            padding: 0.5rem 1.2rem; border-radius: 50px; font-size: 0.8rem; 
            font-weight: 800; text-transform: uppercase; letter-spacing: 1px; 
            border: 1px solid currentColor; display: inline-flex; align-items: center; gap: 8px;
        }
        .st-pendiente { color: #fbbf24; background: rgba(251, 191, 36, 0.1); }
        .st-procesando { color: #60a5fa; background: rgba(96, 165, 250, 0.1); }
        .st-enviado { color: #34d399; background: rgba(52, 211, 153, 0.1); }
        .st-entregado { color: #fff; background: #34d399; border-color: #34d399; }
        .st-cancelado { color: #ef4444; background: rgba(239, 68, 68, 0.1); }

        /* GRID CONTENT */
        .detail-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 3rem; }

        /* LISTA DE ITEMS */
        .items-card { background: #1a1a1a; border-radius: 8px; overflow: hidden; border: 1px solid #333; }
        .item-row { 
            display: flex; gap: 1.5rem; padding: 1.5rem; border-bottom: 1px solid #2a2a2a; 
            align-items: center; transition: 0.2s;
        }
        .item-row:last-child { border-bottom: none; }
        .item-row:hover { background: #222; }

        .item-img { 
            width: 80px; height: 80px; object-fit: cover; border-radius: 6px; 
            background: #333; flex-shrink: 0;
        }
        .item-info { flex: 1; }
        .item-name { font-weight: 700; font-size: 1.1rem; color: #fff; margin-bottom: 0.2rem; }
        .item-meta { font-size: 0.85rem; color: #888; }
        .item-price { font-weight: 700; font-size: 1.1rem; color: #fff; }

        /* INFO LATERAL */
        .info-card { background: #1a1a1a; border-radius: 8px; padding: 2rem; border: 1px solid #333; margin-bottom: 2rem; }
        .info-title { font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; color: #888; margin-bottom: 1rem; border-bottom: 1px solid #333; padding-bottom: 0.5rem; font-weight: 700; }
        .info-text { color: #ddd; font-size: 0.95rem; line-height: 1.6; margin-bottom: 0.5rem; }
        .info-strong { color: #fff; font-weight: 600; }

        .summary-row { display: flex; justify-content: space-between; margin-bottom: 0.8rem; font-size: 0.95rem; color: #bbb; }
        .summary-total { 
            display: flex; justify-content: space-between; margin-top: 1.5rem; 
            padding-top: 1rem; border-top: 1px solid #444; 
            font-size: 1.4rem; font-weight: 800; color: #fff; 
        }

        .btn-print {
            display: block; width: 100%; padding: 1rem; margin-top: 1rem;
            background: transparent; color: #fff; border: 1px solid #fff;
            text-align: center; text-decoration: none; font-weight: 700;
            text-transform: uppercase; letter-spacing: 1px; border-radius: 4px;
            cursor: pointer; transition: 0.3s;
        }
        .btn-print:hover { background: #fff; color: #000; }

        .back-btn { color: #888; text-decoration: none; margin-bottom: 2rem; display: inline-block; font-size: 0.9rem; }
        .back-btn:hover { color: #fff; }

        /* ESTILOS DE IMPRESIÓN (BLANCO) */
        @media print {
            body { background: white; color: black; }
            .navbar, .btn-print, .back-btn { display: none; }
            .container { padding: 0; max-width: 100%; }
            .order-header, .items-card, .info-card { border: 1px solid #ccc; box-shadow: none; background: none; margin-bottom: 20px; }
            .item-name, .item-price, .order-title h1, .summary-total { color: black; }
            .item-row { border-bottom: 1px solid #eee; }
            .detail-grid { display: block; }
            .info-card { break-inside: avoid; }
        }

        @media (max-width: 900px) {
            .detail-grid { grid-template-columns: 1fr; }
        }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="index.php" class="logo">BIKESTORE</a>
        
        <div class="nav-center">
            <a href="productos.php" class="nav-link">Catálogo</a>
            <a href="pedidos.php" class="nav-link">Mis Pedidos</a>
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
                    <a href="pedidos.php" class="menu-item">📦 Mis Pedidos</a>
                    <?php if($usuario_rol === 'admin'): ?>
                        <a href="principal_admin.php" class="menu-item">⚙️ Panel Admin</a>
                    <?php elseif($usuario_rol === 'operador'): ?>
                        <a href="principal_operador.php" class="menu-item">⚙️ Panel Operador</a>
                    <?php endif; ?>
                    <div class="menu-divider"></div>
                    <a href="logout.php" class="menu-item" style="color:#ef4444;">Cerrar Sesión</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container">
        <a href="<?php echo $back_link; ?>" class="back-btn"><?php echo $back_text; ?></a>

        <div class="order-header">
            <div class="order-title">
                <h1>PEDIDO #<?php echo str_pad($pedido['id'], 6, '0', STR_PAD_LEFT); ?></h1>
                <div class="order-date">Realizado el <?php echo date('d F Y \a \l\a\s H:i', strtotime($pedido['fecha'])); ?></div>
            </div>
            
            <div class="status-badge st-<?php echo $pedido['estado']; ?>">
                <span style="height:8px; width:8px; background:currentColor; border-radius:50%;"></span>
                <?php echo strtoupper($pedido['estado']); ?>
            </div>
        </div>

        <div class="detail-grid">
            
            <div class="items-column">
                <div class="items-card">
                    <?php foreach($items as $item): ?>
                    <div class="item-row">
                        <?php if($item['imagen_principal']): ?>
                            <img src="<?php echo htmlspecialchars($item['imagen_principal']); ?>" class="item-img" alt="Producto">
                        <?php else: ?>
                            <div class="item-img" style="display:flex; align-items:center; justify-content:center; color:#666;">🚲</div>
                        <?php endif; ?>
                        
                        <div class="item-info">
                            <div class="item-name"><?php echo htmlspecialchars($item['nombre_producto']); ?></div>
                            <div class="item-meta">
                                Talla: <?php echo htmlspecialchars($item['talla']); ?> | Cantidad: <?php echo $item['cantidad']; ?>
                            </div>
                        </div>
                        
                        <div class="item-price">
                            $<?php echo number_format($item['subtotal'], 2); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="info-column">
                
                <div class="info-card">
                    <div class="info-title">Dirección de Envío</div>
                    <div class="info-strong"><?php echo htmlspecialchars($pedido['nombre_completo']); ?></div>
                    <div class="info-text">
                        <?php echo htmlspecialchars($pedido['calle'] . ' #' . $pedido['numero_exterior']); ?><br>
                        <?php echo htmlspecialchars($pedido['colonia']); ?><br>
                        <?php echo htmlspecialchars($pedido['ciudad'] . ', ' . $pedido['estado_dir'] . ' ' . $pedido['codigo_postal']); ?>
                    </div>
                    <div class="info-text">📞 <?php echo htmlspecialchars($pedido['telefono']); ?></div>
                </div>

                <div class="info-card">
                    <div class="info-title">Método de Pago</div>
                    <div class="info-text">
                        <span style="font-size:1.2rem;">💳</span> 
                        <?php echo ucfirst($pedido['tarjeta_tipo'] ?? 'Tarjeta'); ?> •••• <?php echo htmlspecialchars($pedido['ultimos_digitos']); ?>
                    </div>
                </div>

                <div class="info-card">
                    <div class="info-title">Resumen Financiero</div>
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span>$<?php echo number_format($pedido['subtotal'], 2); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Envío</span>
                        <span><?php echo $pedido['envio'] > 0 ? '$'.number_format($pedido['envio'], 2) : 'GRATIS'; ?></span>
                    </div>
                    <div class="summary-total">
                        <span>TOTAL</span>
                        <span>$<?php echo number_format($pedido['total'], 2); ?></span>
                    </div>
                    
                    <button onclick="window.print()" class="btn-print">🖨️ Imprimir Recibo</button>
                </div>

            </div>
        </div>
    </div>

</body>
</html>