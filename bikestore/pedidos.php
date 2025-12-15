<?php

session_start();

if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header("Location: login.php");
    exit;
}

include 'conexion.php';

$usuario_id = $_SESSION['usuario_id'];

// Obtener pedidos del usuario
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

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Pedidos - BikeStore</title>
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

        .header {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 800;
            color: #2c3e50;
        }

        .back-link {
            color: #6c757d;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .back-link:hover {
            color: #2c3e50;
        }

        .pedido-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }

        .pedido-card:hover {
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
        }

        .pedido-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f3f4f6;
        }

        .pedido-info {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .pedido-numero {
            font-size: 1.3rem;
            font-weight: 800;
            color: #2c3e50;
        }

        .pedido-fecha {
            color: #6b7280;
            font-size: 0.9rem;
        }

        .estado-badge {
            padding: 0.5rem 1.25rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .estado-pendiente {
            background: #fff3cd;
            color: #856404;
        }

        .estado-procesando {
            background: #cfe2ff;
            color: #084298;
        }

        .estado-enviado {
            background: #d1ecf1;
            color: #0c5460;
        }

        .estado-entregado {
            background: #d1fae5;
            color: #065f46;
        }

        .estado-cancelado {
            background: #f8d7da;
            color: #842029;
        }

        .pedido-body {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }

        .pedido-detalles {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .detalle-item {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .detalle-icon {
            font-size: 1.2rem;
            min-width: 30px;
        }

        .detalle-content {
            flex: 1;
        }

        .detalle-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            margin-bottom: 0.25rem;
        }

        .detalle-value {
            color: #2c3e50;
            font-weight: 500;
        }

        .pedido-totales {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 12px;
            padding: 1.5rem;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.75rem;
            font-size: 0.95rem;
        }

        .total-row.final {
            font-size: 1.3rem;
            font-weight: 800;
            color: #2c3e50;
            padding-top: 1rem;
            border-top: 2px solid #cbd5e0;
            margin-top: 0.5rem;
        }

        .btn-ver-detalle {
            width: 100%;
            padding: 0.875rem 1.5rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            margin-top: 1rem;
        }

        .btn-ver-detalle:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }

        .empty-state {
            background: white;
            border-radius: 16px;
            padding: 4rem 2rem;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }

        .empty-icon {
            font-size: 5rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .empty-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .empty-text {
            color: #6b7280;
            margin-bottom: 2rem;
        }

        .btn-primary {
            padding: 1rem 2rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            z-index: 2000;
            display: none;
        }

        .notification.success {
            background: #d1fae5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }

        .notification.show {
            display: block;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from { transform: translateX(400px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @media (max-width: 768px) {
            .pedido-body {
                grid-template-columns: 1fr;
            }

            .header-content {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-content">
                <h1 class="page-title">📦 Mis Pedidos</h1>
                <a href="perfil.php" class="back-link">← Volver al perfil</a>
            </div>
        </div>

        <?php if (empty($pedidos)): ?>
            <div class="empty-state">
                <div class="empty-icon">📦</div>
                <h2 class="empty-title">No tienes pedidos aún</h2>
                <p class="empty-text">Explora nuestro catálogo y realiza tu primera compra</p>
                <a href="productos.php" class="btn-primary">Ver Productos</a>
            </div>
        <?php else: ?>
            <?php foreach ($pedidos as $pedido): 
                $fecha_formateada = date('d/m/Y H:i', strtotime($pedido['fecha']));
                $estado_class = 'estado-' . $pedido['estado'];
            ?>
                <div class="pedido-card">
                    <div class="pedido-header">
                        <div class="pedido-info">
                            <div class="pedido-numero">
                                Pedido #<?php echo str_pad($pedido['id'], 6, '0', STR_PAD_LEFT); ?>
                            </div>
                            <div class="pedido-fecha">
                                📅 <?php echo $fecha_formateada; ?>
                            </div>
                        </div>
                        <span class="estado-badge <?php echo $estado_class; ?>">
                            <?php 
                            $estados = [
                                'pendiente' => '⏳ Pendiente',
                                'procesando' => '⚙️ Procesando',
                                'enviado' => '🚚 Enviado',
                                'entregado' => '✅ Entregado',
                                'cancelado' => '❌ Cancelado'
                            ];
                            echo isset($estados[$pedido['estado']]) ? $estados[$pedido['estado']] : $pedido['estado'];
                            ?>
                        </span>
                    </div>

                    <div class="pedido-body">
                        <div class="pedido-detalles">
                            <?php if (!empty($pedido['direccion_nombre'])): ?>
                                <div class="detalle-item">
                                    <div class="detalle-icon">📍</div>
                                    <div class="detalle-content">
                                        <div class="detalle-label">Dirección de Envío</div>
                                        <div class="detalle-value">
                                            <?php echo htmlspecialchars($pedido['direccion_nombre']); ?><br>
                                            <?php if (!empty($pedido['ciudad'])): ?>
                                                <?php echo htmlspecialchars($pedido['ciudad']); ?>
                                                <?php if (!empty($pedido['estado_direccion'])): ?>
                                                    , <?php echo htmlspecialchars($pedido['estado_direccion']); ?>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="detalle-item">
                                <div class="detalle-icon">📊</div>
                                <div class="detalle-content">
                                    <div class="detalle-label">Estado del Pedido</div>
                                    <div class="detalle-value">
                                        <?php
                                        $mensajes_estado = [
                                            'pendiente' => 'Tu pedido está pendiente de confirmación',
                                            'procesando' => 'Estamos preparando tu pedido',
                                            'enviado' => 'Tu pedido está en camino',
                                            'entregado' => 'Tu pedido ha sido entregado',
                                            'cancelado' => 'Este pedido fue cancelado'
                                        ];
                                        echo isset($mensajes_estado[$pedido['estado']]) ? $mensajes_estado[$pedido['estado']] : 'Estado: ' . htmlspecialchars($pedido['estado']);
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="pedido-totales">
                            <div class="total-row">
                                <span>Subtotal:</span>
                                <span>$<?php echo number_format($pedido['subtotal'], 2); ?></span>
                            </div>
                            <div class="total-row">
                                <span>Envío:</span>
                                <span>
                                    <?php echo $pedido['envio'] == 0 ? 'GRATIS' : '$' . number_format($pedido['envio'], 2); ?>
                                </span>
                            </div>
                            <div class="total-row final">
                                <span>Total:</span>
                                <span>$<?php echo number_format($pedido['total'], 2); ?></span>
                            </div>

                            <a href="detalle_pedido.php?id=<?php echo $pedido['id']; ?>" class="btn-ver-detalle">
                                Ver Detalle del Pedido
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Notificación -->
    <div class="notification" id="notification"></div>

    <script>
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
                window.history.replaceState({}, document.title, 'pedidos.php');
            }
        });
    </script>
</body>
</html>