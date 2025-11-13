<?php

session_start();

if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header("Location: login.php");
    exit;
}

include 'conexion.php';

$usuario_id = $_SESSION['usuario_id'];
$pedido_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($pedido_id <= 0) {
    header("Location: pedidos.php");
    exit;
}

// Obtener datos del pedido
$stmt = $conn->prepare("
    SELECT p.*, 
           d.nombre_completo, d.telefono, d.calle, d.numero_exterior, d.numero_interior,
           d.colonia, d.ciudad, d.estado as estado_direccion, d.codigo_postal, d.referencias,
           m.tipo as metodo_tipo, m.ultimos_digitos, m.marca
    FROM pedidos p
    LEFT JOIN direcciones d ON p.direccion_envio_id = d.id
    LEFT JOIN metodos_pago m ON p.metodo_pago_id = m.id
    WHERE p.id = ? AND p.usuario_id = ?
");
$stmt->bind_param("ii", $pedido_id, $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    $stmt->close();
    $conn->close();
    header("Location: pedidos.php?error=" . urlencode("Pedido no encontrado"));
    exit;
}

$pedido = $resultado->fetch_assoc();
$stmt->close();

// Obtener items del pedido
$stmt = $conn->prepare("SELECT * FROM pedido_items WHERE pedido_id = ?");
$stmt->bind_param("i", $pedido_id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();

$fecha_formateada = date('d/m/Y H:i', strtotime($pedido['fecha']));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle del Pedido #<?php echo str_pad($pedido_id, 6, '0', STR_PAD_LEFT); ?> - BikeStore</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            padding: 2rem;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .back-button {
            background: white;
            color: #6c757d;
            border: 2px solid #e5e7eb;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 2rem;
        }

        .back-button:hover {
            background: #f8f9fa;
            border-color: #cbd5e0;
        }

        .pedido-header {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 1.5rem;
        }

        .header-info {
            flex: 1;
        }

        .pedido-numero {
            font-size: 2rem;
            font-weight: 900;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .pedido-fecha {
            color: #6b7280;
            font-size: 1rem;
            margin-bottom: 1rem;
        }

        .estado-badge {
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            font-size: 0.95rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
        }

        .estado-pendiente { background: #fff3cd; color: #856404; }
        .estado-procesando { background: #cfe2ff; color: #084298; }
        .estado-enviado { background: #d1ecf1; color: #0c5460; }
        .estado-entregado { background: #d1fae5; color: #065f46; }
        .estado-cancelado { background: #f8d7da; color: #842029; }

        .main-content {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 2rem;
        }

        .section {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Items del Pedido */
        .item-pedido {
            display: flex;
            gap: 1.5rem;
            padding: 1.5rem;
            border: 2px solid #f3f4f6;
            border-radius: 12px;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .item-pedido:hover {
            border-color: #e5e7eb;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .item-image {
            width: 100px;
            height: 100px;
            border-radius: 12px;
            object-fit: cover;
            background: #f3f4f6;
        }

        .item-placeholder {
            width: 100px;
            height: 100px;
            border-radius: 12px;
            background: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
        }

        .item-info {
            flex: 1;
        }

        .item-nombre {
            font-size: 1.1rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .item-detalles {
            display: flex;
            gap: 1rem;
            margin-bottom: 0.75rem;
        }

        .item-detalle-badge {
            padding: 0.25rem 0.75rem;
            background: #f3f4f6;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            color: #6b7280;
        }

        .item-precio {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 0.75rem;
            padding-top: 0.75rem;
            border-top: 1px solid #f3f4f6;
        }

        .precio-unitario {
            color: #6b7280;
            font-size: 0.9rem;
        }

        .precio-total {
            font-size: 1.2rem;
            font-weight: 700;
            color: #667eea;
        }

        /* Información de Envío */
        .info-item {
            margin-bottom: 1.5rem;
        }

        .info-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
            letter-spacing: 0.5px;
        }

        .info-value {
            color: #2c3e50;
            font-weight: 500;
            line-height: 1.6;
        }

        /* Resumen */
        .resumen-row {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            font-size: 1rem;
        }

        .resumen-row.subtotal {
            border-bottom: 1px solid #e5e7eb;
        }

        .resumen-row.total {
            font-size: 1.5rem;
            font-weight: 800;
            color: #2c3e50;
            padding-top: 1rem;
            margin-top: 0.5rem;
            border-top: 2px solid #cbd5e0;
        }

        .timeline {
            position: relative;
            padding-left: 2rem;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 0.5rem;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e5e7eb;
        }

        .timeline-item {
            position: relative;
            padding-bottom: 2rem;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -1.45rem;
            top: 0;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #cbd5e0;
            border: 3px solid white;
            box-shadow: 0 0 0 2px #e5e7eb;
        }

        .timeline-item.active::before {
            background: #667eea;
            box-shadow: 0 0 0 2px #667eea;
        }

        .timeline-title {
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 0.25rem;
        }

        .timeline-date {
            font-size: 0.85rem;
            color: #6b7280;
        }

        @media (max-width: 1024px) {
            .main-content {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .item-pedido {
                flex-direction: column;
            }

            .item-image,
            .item-placeholder {
                width: 100%;
                height: 200px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="pedidos.php" class="back-button">← Volver a mis pedidos</a>

        <!-- Header del Pedido -->
        <div class="pedido-header">
            <div class="header-content">
                <div class="header-info">
                    <h1 class="pedido-numero">
                        Pedido #<?php echo str_pad($pedido_id, 6, '0', STR_PAD_LEFT); ?>
                    </h1>
                    <div class="pedido-fecha">
                        📅 Realizado el <?php echo $fecha_formateada; ?>
                    </div>
                </div>
                <span class="estado-badge estado-<?php echo $pedido['estado']; ?>">
                    <?php 
                    $estados = [
                        'pendiente' => '⏳ Pendiente',
                        'procesando' => '⚙️ Procesando',
                        'enviado' => '🚚 Enviado',
                        'entregado' => '✅ Entregado',
                        'cancelado' => '❌ Cancelado'
                    ];
                    echo $estados[$pedido['estado']];
                    ?>
                </span>
            </div>
        </div>

        <div class="main-content">
            <div>
                <!-- Productos del Pedido -->
                <div class="section">
                    <h2 class="section-title">🛍️ Productos</h2>
                    <?php foreach ($items as $item): ?>
                        <div class="item-pedido">
                            <?php if (!empty($item['imagen_producto'])): ?>
                                <img src="<?php echo htmlspecialchars($item['imagen_producto']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['nombre_producto']); ?>"
                                     class="item-image">
                            <?php else: ?>
                                <div class="item-placeholder">🚴</div>
                            <?php endif; ?>

                            <div class="item-info">
                                <div class="item-nombre">
                                    <?php echo htmlspecialchars($item['nombre_producto']); ?>
                                </div>
                                
                                <div class="item-detalles">
                                    <span class="item-detalle-badge">
                                        📏 Talla: <?php echo htmlspecialchars($item['talla']); ?>
                                    </span>
                                    <span class="item-detalle-badge">
                                        📦 Cantidad: <?php echo $item['cantidad']; ?>
                                    </span>
                                </div>

                                <div class="item-precio">
                                    <span class="precio-unitario">
                                        Precio unitario: $<?php echo number_format($item['precio_unitario'], 2); ?>
                                    </span>
                                    <span class="precio-total">
                                        $<?php echo number_format($item['subtotal'], 2); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Dirección de Envío -->
                <?php if (!empty($pedido['nombre_completo'])): ?>
                <div class="section">
                    <h2 class="section-title">📍 Dirección de Envío</h2>
                    
                    <div class="info-item">
                        <div class="info-label">Destinatario</div>
                        <div class="info-value">
                            <?php echo htmlspecialchars($pedido['nombre_completo']); ?>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Teléfono</div>
                        <div class="info-value">
                            <?php echo htmlspecialchars($pedido['telefono']); ?>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Dirección</div>
                        <div class="info-value">
                            <?php echo htmlspecialchars($pedido['calle']); ?> 
                            <?php echo htmlspecialchars($pedido['numero_exterior']); ?>
                            <?php echo !empty($pedido['numero_interior']) ? ', Int. ' . htmlspecialchars($pedido['numero_interior']) : ''; ?>
                            <br>
                            <?php echo htmlspecialchars($pedido['colonia']); ?>
                            <br>
                            <?php echo htmlspecialchars($pedido['ciudad']); ?>, 
                            <?php echo htmlspecialchars($pedido['estado']); ?>
                            <br>
                            C.P. <?php echo htmlspecialchars($pedido['codigo_postal']); ?>
                        </div>
                    </div>

                    <?php if (!empty($pedido['referencias'])): ?>
                    <div class="info-item">
                        <div class="info-label">Referencias</div>
                        <div class="info-value">
                            <?php echo nl2br(htmlspecialchars($pedido['referencias'])); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Método de Pago -->
                <?php if (!empty($pedido['metodo_tipo'])): ?>
                <div class="section">
                    <h2 class="section-title">💳 Método de Pago</h2>
                    
                    <div class="info-item">
                        <div class="info-label">Tipo</div>
                        <div class="info-value">
                            <?php echo ucwords(str_replace('_', ' ', $pedido['metodo_tipo'])); ?>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Tarjeta</div>
                        <div class="info-value">
                            <?php echo htmlspecialchars($pedido['marca']); ?> 
                            •••• •••• •••• <?php echo htmlspecialchars($pedido['ultimos_digitos']); ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div>
                <!-- Resumen del Pedido -->
                <div class="section">
                    <h2 class="section-title">💰 Resumen</h2>
                    
                    <div class="resumen-row subtotal">
                        <span>Subtotal:</span>
                        <span>$<?php echo number_format($pedido['subtotal'], 2); ?></span>
                    </div>

                    <div class="resumen-row subtotal">
                        <span>Envío:</span>
                        <span>
                            <?php echo $pedido['envio'] == 0 ? 'GRATIS' : '$' . number_format($pedido['envio'], 2); ?>
                        </span>
                    </div>

                    <div class="resumen-row total">
                        <span>Total:</span>
                        <span>$<?php echo number_format($pedido['total'], 2); ?></span>
                    </div>
                </div>

                <!-- Timeline del Estado -->
                <div class="section">
                    <h2 class="section-title">📊 Estado del Pedido</h2>
                    
                    <div class="timeline">
                        <div class="timeline-item <?php echo in_array($pedido['estado'], ['pendiente', 'procesando', 'enviado', 'entregado']) ? 'active' : ''; ?>">
                            <div class="timeline-title">Pedido Recibido</div>
                            <div class="timeline-date"><?php echo $fecha_formateada; ?></div>
                        </div>

                        <div class="timeline-item <?php echo in_array($pedido['estado'], ['procesando', 'enviado', 'entregado']) ? 'active' : ''; ?>">
                            <div class="timeline-title">En Preparación</div>
                            <div class="timeline-date">
                                <?php echo $pedido['estado'] == 'procesando' || $pedido['estado'] == 'enviado' || $pedido['estado'] == 'entregado' ? 'Procesando tu pedido' : 'Pendiente'; ?>
                            </div>
                        </div>

                        <div class="timeline-item <?php echo in_array($pedido['estado'], ['enviado', 'entregado']) ? 'active' : ''; ?>">
                            <div class="timeline-title">Enviado</div>
                            <div class="timeline-date">
                                <?php echo $pedido['estado'] == 'enviado' || $pedido['estado'] == 'entregado' ? 'En camino a tu dirección' : 'Pendiente de envío'; ?>
                            </div>
                        </div>

                        <div class="timeline-item <?php echo $pedido['estado'] == 'entregado' ? 'active' : ''; ?>">
                            <div class="timeline-title">Entregado</div>
                            <div class="timeline-date">
                                <?php echo $pedido['estado'] == 'entregado' ? '¡Pedido entregado!' : 'Pendiente de entrega'; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>