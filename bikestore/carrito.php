<?php
session_start();
include 'conexion.php';

// 1. SEGURIDAD
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$nombre_usuario = $_SESSION['usuario_nombre'];

// 2. ELIMINAR PRODUCTO
if (isset($_GET['eliminar'])) {
    $id_carrito = intval($_GET['eliminar']);
    $stmt = $conn->prepare("DELETE FROM carrito WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $id_carrito, $usuario_id);
    $stmt->execute();
    $stmt->close();
    header("Location: carrito.php");
    exit;
}

// 3. OBTENER PRODUCTOS DEL CARRITO CON EL STOCK REAL
// FIX: Agregamos 'COLLATE utf8mb4_unicode_ci' para evitar error de mezcla de intercalaciones
$sql = "
    SELECT 
        c.id as carrito_id, 
        c.cantidad, 
        c.talla, 
        p.id as producto_id, 
        p.nombre, 
        p.precio, 
        p.imagen_principal,
        CASE 
            WHEN c.talla IS NOT NULL AND c.talla != '' THEN pt.stock 
            ELSE p.stock 
        END as stock_real
    FROM carrito c 
    JOIN productos p ON c.producto_id = p.id 
    LEFT JOIN producto_tallas pt ON c.producto_id = pt.producto_id 
        AND c.talla = pt.talla COLLATE utf8mb4_unicode_ci 
    WHERE c.usuario_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Calcular totales
$subtotal = 0;
foreach ($items as $item) {
    $subtotal += $item['precio'] * $item['cantidad'];
}
$envio = ($subtotal > 0 && $subtotal < 1000) ? 150 : 0;
$total = $subtotal + $envio;

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tu Carrito - BikeStore</title>
    <style>
        /* BASE */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #000;
            color: #fff;
            overflow-x: hidden;
        }

        /* VIDEO DE FONDO */
        .video-bg {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            object-fit: cover; z-index: -2;
        }
        .overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: -1; backdrop-filter: blur(5px);
        }

        /* NAVBAR */
        .navbar {
            position: fixed; top: 0; left: 0; width: 100%; padding: 1.2rem 3rem;
            display: flex; justify-content: space-between; align-items: center;
            z-index: 1000; background: rgba(0, 0, 0, 0.6);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }
        .logo { font-size: 1.5rem; font-weight: 900; color: white; text-decoration: none; letter-spacing: -1px; text-transform: uppercase; }
        
        .nav-center a {
            color: rgba(255, 255, 255, 0.8); text-decoration: none; font-weight: 600;
            font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px;
            margin: 0 1.5rem; transition: color 0.3s;
        }
        .nav-center a:hover { color: #fff; }

        .nav-right { display: flex; align-items: center; gap: 1.5rem; }
        .icon-btn { color: white; text-decoration: none; position: relative; font-size: 1.2rem; }
        
        /* DROPDOWN USUARIO */
        .user-dropdown { position: relative; padding-bottom: 10px; margin-bottom: -10px; }
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
        .menu-item { display: block; padding: 10px 20px; color: #ddd; text-decoration: none; font-size: 0.85rem; transition: 0.2s; }
        .menu-item:hover { background: #333; color: white; }

        /* LAYOUT CARRITO */
        .container { max-width: 1200px; margin: 0 auto; padding: 8rem 2rem 4rem; }
        .page-title { font-size: 2.5rem; font-weight: 800; letter-spacing: -1px; margin-bottom: 2rem; text-align: center; }

        .cart-grid { 
            display: grid; grid-template-columns: 2fr 1fr; gap: 3rem; 
            align-items: start;
        }

        /* LISTA DE ITEMS */
        .cart-items { display: flex; flex-direction: column; gap: 1.5rem; }
        
        .cart-card {
            background: rgba(30, 30, 30, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px; padding: 1.5rem;
            display: flex; gap: 1.5rem; align-items: center;
            transition: 0.3s;
        }
        .cart-card:hover { background: rgba(40, 40, 40, 0.8); border-color: rgba(255,255,255,0.2); }

        .item-img { 
            width: 100px; height: 100px; object-fit: cover; border-radius: 6px; 
            background: #333; display: block;
        }
        
        .item-details { flex: 1; }
        
        /* Enlace del nombre del producto */
        .item-link { text-decoration: none; color: inherit; }
        .item-name { font-size: 1.2rem; font-weight: 700; margin-bottom: 0.3rem; transition: color 0.2s; }
        .item-link:hover .item-name { color: #ccc; text-decoration: underline; }

        .item-meta { color: #aaa; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .item-price { font-size: 1.1rem; font-weight: 600; color: #fff; }

        .item-actions { display: flex; align-items: center; gap: 1rem; margin-top: 0.5rem; }
        
        /* CONTROLES DE CANTIDAD */
        .qty-control-wrapper {
            display: flex; align-items: center; background: #222; 
            border-radius: 4px; border: 1px solid #444;
        }
        .qty-btn-mini {
            background: transparent; color: #fff; border: none; 
            width: 28px; height: 28px; cursor: pointer; font-weight: bold; font-size: 1rem;
            display: flex; align-items: center; justify-content: center;
        }
        .qty-btn-mini:hover { background: #444; }
        .qty-input-mini {
            width: 35px; background: transparent; border: none; 
            color: #fff; text-align: center; font-size: 0.9rem; font-weight: 600;
        }
        
        .btn-remove { 
            color: #ef4444; background: none; border: none; cursor: pointer; 
            font-size: 0.9rem; font-weight: 600; text-transform: uppercase; 
            letter-spacing: 1px; transition: 0.2s; 
        }
        .btn-remove:hover { color: #ffadad; text-decoration: underline; }

        /* RESUMEN DE COMPRA */
        .summary-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px; padding: 2rem;
            position: sticky; top: 120px;
        }

        .summary-title { font-size: 1.5rem; font-weight: 800; margin-bottom: 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 1rem; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 1rem; color: #ccc; font-size: 1rem; }
        .summary-row.total { 
            color: #fff; font-size: 1.5rem; font-weight: 800; 
            margin-top: 1.5rem; border-top: 1px solid rgba(255,255,255,0.1); 
            padding-top: 1rem; 
        }

        .btn-checkout {
            display: block; width: 100%; padding: 1.2rem;
            background: #fff; color: #000; text-align: center;
            font-weight: 800; text-transform: uppercase; letter-spacing: 1px;
            text-decoration: none; border-radius: 4px; margin-top: 2rem;
            transition: all 0.3s;
        }
        .btn-checkout:hover { background: #e2e2e2; transform: translateY(-2px); }

        /* EMPTY STATE */
        .empty-cart { text-align: center; padding: 4rem; color: #ccc; grid-column: 1/-1; }
        .empty-icon { font-size: 4rem; margin-bottom: 1rem; opacity: 0.5; }
        .btn-shop { 
            display: inline-block; padding: 1rem 2rem; border: 1px solid #fff; 
            color: #fff; text-decoration: none; border-radius: 50px; 
            font-weight: 600; margin-top: 1rem; transition: 0.3s; 
        }
        .btn-shop:hover { background: #fff; color: #000; }
        
        .stock-warning { color: #eab308; font-size: 0.8rem; margin-top: 5px; display: none; }

        @keyframes fadeInUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        
        @media (max-width: 900px) {
            .cart-grid { grid-template-columns: 1fr; }
            .summary-card { position: static; margin-top: 2rem; }
        }
    </style>
</head>
<body>

    <video autoplay muted loop playsinline class="video-bg">
        <source src="fondo.mp4" type="video/mp4">
    </video>
    <div class="overlay"></div>

    <nav class="navbar">
        <a href="index.php" class="logo">BIKESTORE</a>
        
        <div class="nav-center">
            <a href="productos.php">Catálogo</a>
            <a href="index.php#journal">Journal</a>
        </div>

        <div class="nav-right">
            <div class="user-dropdown">
                <a href="#" class="icon-btn" style="font-weight:700; font-size:0.9rem;">
                    <?php echo strtoupper(explode(' ', $nombre_usuario)[0]); ?> ▾
                </a>
                <div class="user-menu">
                    <a href="perfil.php" class="menu-item">👤 Mi Perfil</a>
                    <a href="pedidos.php" class="menu-item">📦 Mis Pedidos</a>
                    <a href="logout.php" class="menu-item" style="color:#ef4444;">Cerrar Sesión</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container">
        <h1 class="page-title">TU CARRITO</h1>

        <?php if (empty($items)): ?>
            <div class="empty-cart">
                <div class="empty-icon">🛒</div>
                <h2>Tu carrito está vacío</h2>
                <p>Parece que aún no has agregado bicicletas o accesorios.</p>
                <a href="productos.php" class="btn-shop">Ir a la Tienda</a>
            </div>
        <?php else: ?>
            <div class="cart-grid">
                
                <div class="cart-items">
                    <?php foreach ($items as $item): 
                        $max_stock = $item['stock_real'] !== null ? $item['stock_real'] : 99;
                    ?>
                    <div class="cart-card">
                        <a href="producto_detalle.php?id=<?php echo $item['producto_id']; ?>">
                            <?php if ($item['imagen_principal']): ?>
                                <img src="<?php echo htmlspecialchars($item['imagen_principal']); ?>" class="item-img" alt="Producto">
                            <?php else: ?>
                                <div class="item-img" style="display:flex; align-items:center; justify-content:center; font-size:2rem; color:#fff;">🚲</div>
                            <?php endif; ?>
                        </a>
                        
                        <div class="item-details">
                            <a href="producto_detalle.php?id=<?php echo $item['producto_id']; ?>" class="item-link">
                                <div class="item-name"><?php echo htmlspecialchars($item['nombre']); ?></div>
                            </a>

                            <div class="item-meta">
                                Talla: <strong><?php echo $item['talla']; ?></strong> | 
                                Stock disponible: <?php echo $max_stock; ?>
                            </div>
                            
                            <div class="item-actions">
                                <div class="qty-control-wrapper">
                                    <button class="qty-btn-mini" onclick="actualizarCantidad(<?php echo $item['carrito_id']; ?>, -1, <?php echo $max_stock; ?>)">−</button>
                                    <input type="text" id="qty-<?php echo $item['carrito_id']; ?>" value="<?php echo $item['cantidad']; ?>" class="qty-input-mini" readonly>
                                    <button class="qty-btn-mini" onclick="actualizarCantidad(<?php echo $item['carrito_id']; ?>, 1, <?php echo $max_stock; ?>)">+</button>
                                </div>
                                <a href="carrito.php?eliminar=<?php echo $item['carrito_id']; ?>" class="btn-remove" onclick="return confirm('¿Quitar del carrito?');">Eliminar</a>
                            </div>
                            <div id="warn-<?php echo $item['carrito_id']; ?>" class="stock-warning">¡Máximo disponible alcanzado!</div>
                        </div>
                        
                        <div class="item-price">
                            $<?php echo number_format($item['precio'] * $item['cantidad'], 2); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="summary-card">
                    <div class="summary-title">Resumen</div>
                    
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span>$<?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    
                    <div class="summary-row">
                        <span>Envío</span>
                        <span><?php echo $envio == 0 ? 'GRATIS' : '$' . number_format($envio, 2); ?></span>
                    </div>
                    
                    <div class="summary-row total">
                        <span>Total</span>
                        <span>$<?php echo number_format($total, 2); ?></span>
                    </div>

                    <a href="checkout.php" class="btn-checkout">Proceder al Pago</a>
                    
                    <div style="text-align:center; margin-top:1rem; font-size:0.8rem; color:#aaa;">
                        🔒 Pago seguro SSL 256-bit
                    </div>
                </div>

            </div>
        <?php endif; ?>
    </div>

    <script>
    function actualizarCantidad(itemId, cambio, maxStock) {
        const input = document.getElementById('qty-' + itemId);
        const warning = document.getElementById('warn-' + itemId);
        const cantidadActual = parseInt(input.value);
        const nuevaCantidad = cantidadActual + cambio;

        if (nuevaCantidad < 1) return;

        if (nuevaCantidad > maxStock) {
            warning.style.display = 'block';
            setTimeout(() => warning.style.display = 'none', 2000);
            return;
        }

        input.style.opacity = '0.5';
        warning.style.display = 'none';

        const formData = new FormData();
        formData.append('item_id', itemId);
        formData.append('cantidad', nuevaCantidad);

        fetch('actualizar_carrito.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Error al actualizar');
                input.style.opacity = '1';
                if(data.message.includes("Stock insuficiente")) {
                    location.reload(); 
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            input.style.opacity = '1';
        });
    }
    </script>
</body>
</html>