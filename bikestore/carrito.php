<?php

session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header("Location: login.php?error=" . urlencode("Debes iniciar sesión para ver tu carrito"));
    exit;
}

include 'conexion.php';

$usuario_id = $_SESSION['usuario_id'];

// Obtener productos del carrito
$sql = "SELECT c.*, p.nombre, p.tipo, p.imagen, p.imagen_principal, p.stock, p.dias_envio 
        FROM carrito c 
        INNER JOIN productos p ON c.producto_id = p.id 
        WHERE c.usuario_id = ? 
        ORDER BY c.fecha_agregado DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$items_carrito = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ==========================================
// VALIDAR que las tallas en el carrito todavía existen y tienen stock
// ==========================================
$items_invalidos = [];

foreach ($items_carrito as $key => $item) {
    // Verificar stock de la talla específica
    $stmt = $conn->prepare("SELECT stock FROM producto_tallas WHERE producto_id = ? AND talla = ? AND activo = 1");
    $stmt->bind_param("is", $item['producto_id'], $item['talla']);
    $stmt->execute();
    $resultado_talla = $stmt->get_result();
    
    if ($resultado_talla->num_rows > 0) {
        $talla_info = $resultado_talla->fetch_assoc();
        $stock_real = $talla_info['stock'];
    } else {
        // Fallback: verificar en tabla productos
        $stmt2 = $conn->prepare("SELECT stock FROM productos WHERE id = ? AND talla = ?");
        $stmt2->bind_param("is", $item['producto_id'], $item['talla']);
        $stmt2->execute();
        $resultado_producto = $stmt2->get_result();
        
        if ($resultado_producto->num_rows > 0) {
            $producto_info = $resultado_producto->fetch_assoc();
            $stock_real = $producto_info['stock'];
        } else {
            $stock_real = 0;
        }
        $stmt2->close();
    }
    $stmt->close();
    
    // Actualizar el stock real en el array
    $items_carrito[$key]['stock_real'] = $stock_real;
    
    // Marcar items sin stock o con talla inexistente
    if ($stock_real <= 0) {
        $items_invalidos[] = [
            'id' => $item['id'],
            'nombre' => $item['nombre'],
            'talla' => $item['talla'],
            'razon' => 'Sin stock disponible'
        ];
    } else if ($item['cantidad'] > $stock_real) {
        $items_invalidos[] = [
            'id' => $item['id'],
            'nombre' => $item['nombre'],
            'talla' => $item['talla'],
            'razon' => "Stock insuficiente (solo {$stock_real} disponibles)"
        ];
    }
}

// Calcular totales
$subtotal = 0;
$total_items = 0;
foreach ($items_carrito as $item) {
    $subtotal += $item['precio_unitario'] * $item['cantidad'];
    $total_items += $item['cantidad'];
}

$envio = $subtotal > 1000 ? 0 : 150; // Envío gratis en compras mayores a $1000
$total = $subtotal + $envio;

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de Compras - BikeStore</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #F1F5F9 0%, #e2e8f0 100%);
            min-height: 100vh;
            color: #2c3e50;
        }

        .header {
            background: white;
            border-bottom: 1px solid #dee2e6;
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .nav {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, #45556C, #364458);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-decoration: none;
        }

        .back-link {
            color: #6c757d;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .back-link:hover {
            color: #45556C;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 3rem 2rem;
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, #45556C, #364458);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }

        .items-count {
            color: #6c757d;
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }

        /* Alerta de items inválidos */
        .invalid-items-alert {
            background: #fee2e2;
            border: 2px solid #ef4444;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .invalid-items-alert h3 {
            color: #991b1b;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .invalid-items-alert p {
            color: #b91c1c;
            margin-bottom: 1rem;
        }

        .invalid-items-alert ul {
            color: #991b1b;
            margin-left: 1.5rem;
        }

        .invalid-items-alert li {
            margin-bottom: 0.5rem;
        }

        .cart-layout {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 2rem;
        }

        /* Items del carrito */
        .cart-items {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }

        .cart-item {
            display: grid;
            grid-template-columns: 120px 1fr auto;
            gap: 1.5rem;
            padding: 1.5rem;
            border-bottom: 2px solid #F1F5F9;
            position: relative;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .cart-item.invalid-item {
            opacity: 0.6;
            background: #fff5f5;
            border-radius: 12px;
        }

        .item-image {
            width: 120px;
            height: 120px;
            border-radius: 12px;
            overflow: hidden;
            background: #F1F5F9;
        }

        .item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .placeholder-img {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: #dee2e6;
        }

        .item-details {
            flex: 1;
        }

        .item-name {
            font-size: 1.2rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .item-meta {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }

        .meta-badge {
            padding: 0.4rem 0.8rem;
            background: #F1F5F9;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            color: #495057;
        }

        .stock-badge {
            display: inline-block;
            padding: 0.3rem 0.6rem;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .stock-ok {
            background: #d1fae5;
            color: #065f46;
        }

        .stock-low {
            background: #fff3cd;
            color: #856404;
        }

        .stock-none {
            background: #fee2e2;
            color: #991b1b;
        }

        .item-warning {
            background: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 8px;
            padding: 0.75rem;
            margin-top: 0.5rem;
            color: #856404;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-top: 1rem;
        }

        .qty-btn {
            width: 32px;
            height: 32px;
            border: 2px solid #dee2e6;
            background: white;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .qty-btn:hover:not(:disabled) {
            border-color: #45556C;
            color: #45556C;
        }

        .qty-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .qty-input {
            width: 50px;
            text-align: center;
            padding: 0.4rem;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            font-weight: 600;
        }

        .item-actions {
            text-align: right;
        }

        .item-price {
            font-size: 1.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, #45556C, #364458);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
        }

        .remove-btn {
            background: #fff5f5;
            color: #e53e3e;
            border: 1px solid #feb2b2;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .remove-btn:hover {
            background: #fed7d7;
            border-color: #fc8181;
        }

        /* Resumen del pedido */
        .order-summary {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            height: fit-content;
            position: sticky;
            top: 100px;
        }

        .summary-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #F1F5F9;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            font-size: 1rem;
        }

        .summary-row.total {
            font-size: 1.3rem;
            font-weight: 800;
            color: #2c3e50;
            padding-top: 1rem;
            border-top: 2px solid #e9ecef;
            margin-top: 1rem;
        }

        .summary-label {
            color: #6c757d;
        }

        .summary-value {
            font-weight: 600;
            color: #2c3e50;
        }

        .free-shipping {
            background: #d1fae5;
            color: #065f46;
            padding: 0.75rem;
            border-radius: 8px;
            text-align: center;
            margin: 1rem 0;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .checkout-btn {
            width: 100%;
            padding: 1.2rem;
            background: linear-gradient(135deg, #45556C, #364458);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1.5rem;
        }

        .checkout-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(69, 85, 108, 0.3);
        }

        .checkout-btn:disabled {
            background: #cbd5e0;
            cursor: not-allowed;
        }

        .continue-shopping {
            width: 100%;
            padding: 1rem;
            background: white;
            color: #6c757d;
            border: 2px solid #dee2e6;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            display: block;
            margin-top: 1rem;
            transition: all 0.2s ease;
        }

        .continue-shopping:hover {
            border-color: #45556C;
            background: #F1F5F9;
            color: #45556C;
        }

        .empty-cart {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }

        .empty-cart-icon {
            font-size: 5rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .empty-cart h2 {
            font-size: 1.8rem;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .empty-cart p {
            color: #6c757d;
            margin-bottom: 2rem;
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            z-index: 2000;
            display: none;
            animation: slideIn 0.3s ease;
            max-width: 350px;
        }

        .notification.success {
            background: #d1fae5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }

        .notification.error {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }

        .notification.show {
            display: block;
        }

        @keyframes slideIn {
            from { transform: translateX(400px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @media (max-width: 1024px) {
            .cart-layout {
                grid-template-columns: 1fr;
            }

            .order-summary {
                position: relative;
                top: 0;
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 1.5rem 1rem;
            }

            .cart-item {
                grid-template-columns: 80px 1fr;
                gap: 1rem;
            }

            .item-image {
                width: 80px;
                height: 80px;
            }

            .item-actions {
                grid-column: 1 / -1;
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-top: 1rem;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <nav class="nav">
            <a href="index.php" class="logo">BIKESTORE</a>
            <a href="productos.php" class="back-link">← Seguir comprando</a>
        </nav>
    </header>

    <div class="container">
        <h1 class="page-title">Tu Carrito</h1>
        <p class="items-count"><?php echo $total_items; ?> artículo<?php echo $total_items != 1 ? 's' : ''; ?></p>

        <?php if (empty($items_carrito)): ?>
            <div class="empty-cart">
                <div class="empty-cart-icon">🛒</div>
                <h2>Tu carrito está vacío</h2>
                <p>Agrega algunos productos increíbles a tu carrito</p>
                <a href="productos.php" class="checkout-btn" style="max-width: 300px; margin: 0 auto;">
                    Ver Productos
                </a>
            </div>
        <?php else: ?>
            <!-- Alerta si hay items inválidos -->
            <?php if (!empty($items_invalidos)): ?>
            <div class="invalid-items-alert">
                <h3>⚠️ Atención: Algunos productos tienen problemas</h3>
                <p>Los siguientes productos no están disponibles o no tienen suficiente stock:</p>
                <ul>
                    <?php foreach ($items_invalidos as $inv): ?>
                        <li>
                            <strong><?php echo htmlspecialchars($inv['nombre']); ?></strong> 
                            (Talla <?php echo htmlspecialchars($inv['talla']); ?>): 
                            <?php echo htmlspecialchars($inv['razon']); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <p style="margin-top: 1rem; font-weight: 600;">
                    Por favor actualiza las cantidades o elimina estos productos antes de proceder al pago.
                </p>
            </div>
            <?php endif; ?>

            <div class="cart-layout">
                <!-- Items del carrito -->
                <div class="cart-items">
                    <?php foreach ($items_carrito as $item): 
                        $stock_real = isset($item['stock_real']) ? $item['stock_real'] : $item['stock'];
                        $tiene_stock = $stock_real > 0;
                        $stock_suficiente = $item['cantidad'] <= $stock_real;
                        $item_valido = $tiene_stock && $stock_suficiente;
                    ?>
                        <div class="cart-item <?php echo !$item_valido ? 'invalid-item' : ''; ?>" data-item-id="<?php echo $item['id']; ?>">
                            <div class="item-image">
                                <?php if (!empty($item['imagen_principal']) || !empty($item['imagen'])): ?>
                                    <img src="<?php echo htmlspecialchars($item['imagen_principal'] ?: $item['imagen']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['nombre']); ?>">
                                <?php else: ?>
                                    <div class="placeholder-img">🚴</div>
                                <?php endif; ?>
                            </div>

                            <div class="item-details">
                                <h3 class="item-name"><?php echo htmlspecialchars($item['nombre']); ?></h3>
                                <div class="item-meta">
                                    <span class="meta-badge">📏 Talla: <?php echo htmlspecialchars($item['talla']); ?></span>
                                    <span class="meta-badge">🚚 <?php echo $item['dias_envio']; ?> días</span>
                                    
                                    <!-- Badge de stock real -->
                                    <?php if (!$tiene_stock): ?>
                                        <span class="stock-badge stock-none">❌ Sin stock</span>
                                    <?php elseif (!$stock_suficiente): ?>
                                        <span class="stock-badge stock-low">⚠️ Stock limitado: <?php echo $stock_real; ?></span>
                                    <?php elseif ($stock_real <= 5): ?>
                                        <span class="stock-badge stock-low">⚠️ Últimas <?php echo $stock_real; ?> unidades</span>
                                    <?php else: ?>
                                        <span class="stock-badge stock-ok">✓ Disponible</span>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Advertencia si hay problemas -->
                                <?php if (!$item_valido): ?>
                                    <div class="item-warning">
                                        <?php if (!$tiene_stock): ?>
                                            ⚠️ Esta talla ya no está disponible. Por favor elimínala del carrito.
                                        <?php elseif (!$stock_suficiente): ?>
                                            ⚠️ Solo hay <?php echo $stock_real; ?> unidad(es) disponibles de esta talla.
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="quantity-controls">
                                    <button class="qty-btn" 
                                            onclick="updateQuantity(<?php echo $item['id']; ?>, -1, <?php echo $stock_real; ?>)"
                                            <?php echo !$tiene_stock ? 'disabled' : ''; ?>>−</button>
                                    <input type="number" 
                                           class="qty-input" 
                                           id="qty-<?php echo $item['id']; ?>"
                                           value="<?php echo min($item['cantidad'], $stock_real); ?>" 
                                           min="1" 
                                           max="<?php echo $stock_real; ?>"
                                           readonly>
                                    <button class="qty-btn" 
                                            onclick="updateQuantity(<?php echo $item['id']; ?>, 1, <?php echo $stock_real; ?>)"
                                            <?php echo !$tiene_stock ? 'disabled' : ''; ?>>+</button>
                                </div>
                            </div>

                            <div class="item-actions">
                                <div class="item-price">$<?php echo number_format($item['precio_unitario'] * $item['cantidad'], 2); ?></div>
                                <button class="remove-btn" onclick="removeItem(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars($item['nombre']); ?>')">
                                    🗑️ Eliminar
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Resumen del pedido -->
                <div class="order-summary">
                    <h2 class="summary-title">Resumen del Pedido</h2>
                    
                    <div class="summary-row">
                        <span class="summary-label">Subtotal (<?php echo $total_items; ?> artículos)</span>
                        <span class="summary-value">$<?php echo number_format($subtotal, 2); ?></span>
                    </div>

                    <div class="summary-row">
                        <span class="summary-label">Envío</span>
                        <span class="summary-value">
                            <?php echo $envio == 0 ? 'GRATIS' : '$' . number_format($envio, 2); ?>
                        </span>
                    </div>

                    <?php if ($subtotal >= 1000): ?>
                        <div class="free-shipping">
                            🎉 ¡Envío gratis en tu pedido!
                        </div>
                    <?php else: ?>
                        <div style="background: #fff3cd; color: #856404; padding: 0.75rem; border-radius: 8px; text-align: center; margin: 1rem 0; font-size: 0.9rem;">
                            💡 Agrega $<?php echo number_format(1000 - $subtotal, 2); ?> más para envío gratis
                        </div>
                    <?php endif; ?>

                    <div class="summary-row total">
                        <span>Total</span>
                        <span>$<?php echo number_format($total, 2); ?></span>
                    </div>

                    <button class="checkout-btn" 
                            onclick="proceedToCheckout()"
                            <?php echo !empty($items_invalidos) ? 'disabled' : ''; ?>>
                        🛍️ Proceder al Pago
                    </button>

                    <a href="productos.php" class="continue-shopping">
                        ← Continuar Comprando
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Notificación -->
    <div class="notification" id="notification"></div>

    <script>
        // Actualizar cantidad
        function updateQuantity(itemId, change, maxStock) {
            const input = document.getElementById(`qty-${itemId}`);
            let newValue = parseInt(input.value) + change;

            if (newValue < 1) newValue = 1;
            if (newValue > maxStock) {
                showNotification(`Stock máximo: ${maxStock} unidades`, 'error');
                return;
            }

            input.value = newValue;

            // Enviar actualización al servidor
            fetch('actualizar_carrito.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `item_id=${itemId}&cantidad=${newValue}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    showNotification(data.message || 'Error al actualizar', 'error');
                }
            })
            .catch(error => {
                showNotification('Error de conexión', 'error');
            });
        }

        // Eliminar item
        function removeItem(itemId, nombre) {
            if (!confirm(`¿Eliminar "${nombre}" del carrito?`)) {
                return;
            }

            fetch('eliminar_carrito.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `item_id=${itemId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    showNotification(data.message || 'Error al eliminar', 'error');
                }
            })
            .catch(error => {
                showNotification('Error de conexión', 'error');
            });
        }

        // Proceder al checkout CON VALIDACIÓN
        // Proceder al checkout
function proceedToCheckout() {
    <?php if (!empty($items_invalidos)): ?>
        let mensaje = "⚠️ No puedes proceder al pago. Los siguientes productos tienen problemas:\n\n";
        <?php foreach ($items_invalidos as $inv): ?>
        mensaje += "• <?php echo htmlspecialchars($inv['nombre']); ?> (Talla <?php echo $inv['talla']; ?>): <?php echo $inv['razon']; ?>\n";
        <?php endforeach; ?>
        mensaje += "\nPor favor actualiza las cantidades o elimina estos productos.";
        
        showNotification(mensaje, 'error');
        return false;
    <?php else: ?>
        // Redirigir al checkout
        window.location.href = 'checkout.php';
    <?php endif; ?>
}


        // Mostrar notificación
        function showNotification(message, type = 'success') {
            const notification = document.getElementById('notification');
            notification.textContent = message;
            notification.className = `notification ${type} show`;

            setTimeout(() => {
                notification.classList.remove('show');
            }, 4000);
        }

        // Mostrar mensajes de la URL
        window.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('success')) {
                showNotification(decodeURIComponent(urlParams.get('success')), 'success');
                window.history.replaceState({}, document.title, 'carrito.php');
            }
            if (urlParams.get('error')) {
                showNotification(decodeURIComponent(urlParams.get('error')), 'error');
                window.history.replaceState({}, document.title, 'carrito.php');
            }
        });
    </script>
</body>
</html>