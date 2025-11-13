<?php

session_start();

if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header("Location: login.php?error=" . urlencode("Debes iniciar sesión"));
    exit;
}

include 'conexion.php';

$usuario_id = $_SESSION['usuario_id'];

// Obtener items del carrito
$stmt = $conn->prepare("SELECT c.*, p.nombre, p.imagen_principal, p.dias_envio FROM carrito c INNER JOIN productos p ON c.producto_id = p.id WHERE c.usuario_id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$items_carrito = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (empty($items_carrito)) {
    header("Location: carrito.php?error=" . urlencode("Tu carrito está vacío"));
    exit;
}

// Obtener direcciones
$stmt = $conn->prepare("SELECT * FROM direcciones WHERE usuario_id = ? ORDER BY es_principal DESC, fecha_creacion DESC");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$direcciones = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Obtener métodos de pago
$stmt = $conn->prepare("SELECT * FROM metodos_pago WHERE usuario_id = ? ORDER BY es_principal DESC, fecha_creacion DESC");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$metodos_pago = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();

// Calcular totales
$subtotal = 0;
foreach ($items_carrito as $item) {
    $subtotal += $item['precio_unitario'] * $item['cantidad'];
}
$envio = $subtotal > 1000 ? 0 : 150;
$total = $subtotal + $envio;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - BikeStore</title>
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

        .checkout-layout {
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
            font-size: 1.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .step-number {
            width: 35px;
            height: 35px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.1rem;
        }

        /* Direcciones */
        .direccion-option {
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .direccion-option:hover {
            border-color: #667eea;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
        }

        .direccion-option.selected {
            border-color: #667eea;
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
        }

        .direccion-radio {
            width: 20px;
            height: 20px;
            margin-right: 1rem;
        }

        .direccion-content {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }

        .direccion-info {
            flex: 1;
        }

        .direccion-nombre {
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .direccion-detalle {
            color: #6b7280;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        .principal-badge {
            background: #667eea;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-left: 0.5rem;
        }

        /* Métodos de Pago */
        .metodo-option {
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .metodo-option:hover {
            border-color: #667eea;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
        }

        .metodo-option.selected {
            border-color: #667eea;
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
        }

        .metodo-radio {
            width: 20px;
            height: 20px;
        }

        .metodo-info {
            flex: 1;
        }

        .metodo-tipo {
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 0.25rem;
        }

        .metodo-numero {
            color: #6b7280;
            font-size: 0.9rem;
        }

        /* Botones de acción */
        .add-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .add-button:hover {
            background: #f7fafc;
        }

        .empty-state {
            text-align: center;
            padding: 2rem;
            color: #6b7280;
        }

        /* Resumen */
        .order-summary {
            position: sticky;
            top: 2rem;
        }

        .summary-items {
            max-height: 300px;
            overflow-y: auto;
            margin-bottom: 1.5rem;
        }

        .summary-item {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #f3f4f6;
        }

        .summary-item-image {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            object-fit: cover;
        }

        .summary-item-info {
            flex: 1;
        }

        .summary-item-name {
            font-weight: 600;
            color: #2c3e50;
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }

        .summary-item-details {
            font-size: 0.85rem;
            color: #6b7280;
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

        .checkout-btn {
            width: 100%;
            padding: 1.2rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
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
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }

        .checkout-btn:disabled {
            background: #cbd5e0;
            cursor: not-allowed;
        }

        .warning-box {
            background: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            color: #856404;
            font-weight: 600;
        }

        @media (max-width: 1024px) {
            .checkout-layout {
                grid-template-columns: 1fr;
            }

            .order-summary {
                position: relative;
                top: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-content">
                <h1 class="page-title">🛒 Finalizar Compra</h1>
                <a href="carrito.php" class="back-link">← Volver al carrito</a>
            </div>
        </div>

        <div class="checkout-layout">
            <div>
                <form id="checkoutForm" action="procesar_pedido.php" method="POST" onsubmit="return validarCheckout()">
                    
                    <!-- PASO 1: Dirección de Envío -->
                    <div class="section">
                        <h2 class="section-title">
                            <span class="step-number">1</span>
                            Dirección de Envío
                        </h2>

                        <?php if (empty($direcciones)): ?>
                            <div class="empty-state">
                                <p>No tienes direcciones registradas</p>
                                <a href="agregar_direccion.php" class="add-button" style="margin-top: 1rem;">
                                    + Agregar Dirección
                                </a>
                            </div>
                        <?php else: ?>
                            <?php foreach ($direcciones as $index => $direccion): ?>
                                <label class="direccion-option <?php echo $direccion['es_principal'] ? 'selected' : ''; ?>">
                                    <div class="direccion-content">
                                        <input type="radio" 
                                               name="direccion_id" 
                                               value="<?php echo $direccion['id']; ?>" 
                                               class="direccion-radio"
                                               <?php echo $direccion['es_principal'] ? 'checked' : ''; ?>
                                               required>
                                        <div class="direccion-info">
                                            <div class="direccion-nombre">
                                                <?php echo htmlspecialchars($direccion['nombre_completo']); ?>
                                                <?php if ($direccion['es_principal']): ?>
                                                    <span class="principal-badge">Principal</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="direccion-detalle">
                                                📞 <?php echo htmlspecialchars($direccion['telefono']); ?>
                                            </div>
                                            <div class="direccion-detalle">
                                                <?php echo htmlspecialchars($direccion['calle']); ?> 
                                                <?php echo htmlspecialchars($direccion['numero_exterior']); ?>,
                                                <?php echo htmlspecialchars($direccion['colonia']); ?>,
                                                <?php echo htmlspecialchars($direccion['ciudad']); ?>,
                                                <?php echo htmlspecialchars($direccion['estado']); ?>
                                                C.P. <?php echo htmlspecialchars($direccion['codigo_postal']); ?>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                            
                            <a href="agregar_direccion.php" class="add-button">
                                + Agregar Nueva Dirección
                            </a>
                        <?php endif; ?>
                    </div>

                    <!-- PASO 2: Método de Pago -->
                    <div class="section">
                        <h2 class="section-title">
                            <span class="step-number">2</span>
                            Método de Pago
                        </h2>

                        <?php if (empty($metodos_pago)): ?>
                            <div class="empty-state">
                                <p>No tienes métodos de pago registrados</p>
                                <a href="agregar_metodo_pago.php" class="add-button" style="margin-top: 1rem;">
                                    + Agregar Método de Pago
                                </a>
                            </div>
                        <?php else: ?>
                            <?php foreach ($metodos_pago as $metodo): ?>
                                <label class="metodo-option <?php echo $metodo['es_principal'] ? 'selected' : ''; ?>">
                                    <input type="radio" 
                                           name="metodo_pago_id" 
                                           value="<?php echo $metodo['id']; ?>" 
                                           class="metodo-radio"
                                           <?php echo $metodo['es_principal'] ? 'checked' : ''; ?>
                                           required>
                                    <div class="metodo-info">
                                        <div class="metodo-tipo">
                                            <?php echo ucwords(str_replace('_', ' ', $metodo['tipo'])); ?>
                                            <?php if ($metodo['es_principal']): ?>
                                                <span class="principal-badge">Principal</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="metodo-numero">
                                            <?php echo htmlspecialchars($metodo['marca']); ?> •••• <?php echo htmlspecialchars($metodo['ultimos_digitos']); ?>
                                            - Exp: <?php echo str_pad($metodo['mes_expiracion'], 2, '0', STR_PAD_LEFT); ?>/<?php echo substr($metodo['ano_expiracion'], -2); ?>
                                        </div>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                            
                            <a href="agregar_metodo_pago.php" class="add-button">
                                + Agregar Nuevo Método
                            </a>
                        <?php endif; ?>
                    </div>

                    <!-- Validación -->
                    <?php if (empty($direcciones) || empty($metodos_pago)): ?>
                        <div class="warning-box">
                            ⚠️ Necesitas al menos una dirección y un método de pago para continuar
                        </div>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Resumen del Pedido -->
            <div class="section order-summary">
                <h2 class="section-title">Resumen del Pedido</h2>

                <div class="summary-items">
                    <?php foreach ($items_carrito as $item): ?>
                        <div class="summary-item">
                            <?php if (!empty($item['imagen_principal'])): ?>
                                <img src="<?php echo htmlspecialchars($item['imagen_principal']); ?>" 
                                     class="summary-item-image" 
                                     alt="<?php echo htmlspecialchars($item['nombre']); ?>">
                            <?php else: ?>
                                <div class="summary-item-image" style="background: #f3f4f6; display: flex; align-items: center; justify-content: center;">
                                    🚴
                                </div>
                            <?php endif; ?>
                            <div class="summary-item-info">
                                <div class="summary-item-name"><?php echo htmlspecialchars($item['nombre']); ?></div>
                                <div class="summary-item-details">
                                    Talla: <?php echo htmlspecialchars($item['talla']); ?> | 
                                    Cant: <?php echo $item['cantidad']; ?>
                                </div>
                                <div class="summary-item-details" style="font-weight: 600; color: #2c3e50; margin-top: 0.25rem;">
                                    $<?php echo number_format($item['precio_unitario'] * $item['cantidad'], 2); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

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

                <button type="submit" 
                        form="checkoutForm"
                        class="checkout-btn"
                        <?php echo (empty($direcciones) || empty($metodos_pago)) ? 'disabled' : ''; ?>>
                    💳 Realizar Pedido
                </button>
            </div>
        </div>
    </div>

    <script>
        function validarCheckout() {
            const direccion = document.querySelector('input[name="direccion_id"]:checked');
            const metodoPago = document.querySelector('input[name="metodo_pago_id"]:checked');

            if (!direccion) {
                alert('Por favor selecciona una dirección de envío');
                return false;
            }

            if (!metodoPago) {
                alert('Por favor selecciona un método de pago');
                return false;
            }

            // Confirmar compra
            if (!confirm('¿Confirmas tu pedido por $<?php echo number_format($total, 2); ?>?')) {
                return false;
            }

            return true;
        }

        // Actualizar visualización al seleccionar opciones
        document.querySelectorAll('.direccion-radio').forEach(radio => {
            radio.addEventListener('change', function() {
                document.querySelectorAll('.direccion-option').forEach(opt => {
                    opt.classList.remove('selected');
                });
                this.closest('.direccion-option').classList.add('selected');
            });
        });

        document.querySelectorAll('.metodo-radio').forEach(radio => {
            radio.addEventListener('change', function() {
                document.querySelectorAll('.metodo-option').forEach(opt => {
                    opt.classList.remove('selected');
                });
                this.closest('.metodo-option').classList.add('selected');
            });
        });
    </script>
</body>
</html>