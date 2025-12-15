<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$nombre_usuario = $_SESSION['usuario_nombre'];

// --- OBTENER CARRITO ---
$stmt = $conn->prepare("SELECT c.*, p.nombre, p.precio, p.imagen_principal FROM carrito c JOIN productos p ON c.producto_id = p.id WHERE c.usuario_id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (empty($items)) { header("Location: carrito.php"); exit; }

$subtotal = 0;
foreach ($items as $item) $subtotal += $item['precio'] * $item['cantidad'];
$envio = ($subtotal > 0 && $subtotal < 1000) ? 150 : 0;
$total = $subtotal + $envio;

// --- OBTENER DATOS ---
$direcciones = $conn->query("SELECT * FROM direcciones WHERE usuario_id = $usuario_id")->fetch_all(MYSQLI_ASSOC);
$metodos_pago = $conn->query("SELECT * FROM metodos_pago WHERE usuario_id = $usuario_id")->fetch_all(MYSQLI_ASSOC);

// Helper para validar fecha
function estaVencida($fecha_exp) {
    if (!preg_match('/^\d{2}\/\d{2}$/', $fecha_exp)) return true; // Formato incorrecto
    list($mes, $anio) = explode('/', $fecha_exp);
    $mes = (int)$mes; 
    $anio = (int)$anio + 2000;
    $actual_mes = (int)date('m');
    $actual_anio = (int)date('Y');
    
    if ($anio < $actual_anio) return true;
    if ($anio == $actual_anio && $mes < $actual_mes) return true;
    return false;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalizar Compra - BikeStore</title>
    <style>
        /* ESTILOS DARK PREMIUM (Resumidos para enfoque en checkout) */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, sans-serif; background-color: #0f0f0f; color: #fff; line-height: 1.5; }
        
        .container { max-width: 1200px; margin: 0 auto; padding: 4rem 2rem; }
        .page-title { font-size: 2.5rem; font-weight: 900; letter-spacing: -1px; text-transform: uppercase; margin-bottom: 3rem; text-align: center; }
        
        .checkout-grid { display: grid; grid-template-columns: 1.5fr 1fr; gap: 4rem; align-items: start; }
        
        .section-title { font-size: 1.2rem; font-weight: 700; margin-bottom: 1.5rem; border-bottom: 1px solid #333; padding-bottom: 0.5rem; display: flex; justify-content: space-between; }
        .section-link { font-size: 0.8rem; color: #aaa; text-decoration: none; text-transform: uppercase; }
        .section-link:hover { color: white; }

        /* TARJETAS DE OPCIÓN */
        .options-grid { display: grid; gap: 1rem; margin-bottom: 3rem; }
        .option-label { display: block; cursor: pointer; position: relative; }
        
        .option-card {
            background: #1a1a1a; border: 1px solid #333; border-radius: 8px;
            padding: 1.5rem; transition: 0.2s; display: flex; align-items: center; gap: 1rem;
        }
        
        input[type="radio"] { display: none; }
        input[type="radio"]:checked + .option-card { border-color: #fff; background: #222; box-shadow: 0 0 15px rgba(255,255,255,0.1); }
        
        .radio-circle { width: 20px; height: 20px; border-radius: 50%; border: 2px solid #555; display: flex; align-items: center; justify-content: center; }
        .radio-dot { width: 10px; height: 10px; border-radius: 50%; background: #fff; display: none; }
        input[type="radio"]:checked + .option-card .radio-circle { border-color: #fff; }
        input[type="radio"]:checked + .option-card .radio-dot { display: block; }

        /* ESTILOS PARA TARJETA VENCIDA */
        .option-label.disabled { cursor: not-allowed; opacity: 0.6; }
        .option-label.disabled .option-card { border-color: #333; background: #151515; }
        .badge-expired { 
            background: #ef4444; color: white; font-size: 0.65rem; padding: 2px 6px; 
            border-radius: 4px; font-weight: 800; text-transform: uppercase; margin-left: 10px; 
        }

        /* RESUMEN */
        .summary-card { background: #1a1a1a; border: 1px solid #333; border-radius: 8px; padding: 2rem; position: sticky; top: 20px; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 1rem; color: #ccc; }
        .summary-total { display: flex; justify-content: space-between; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #333; font-size: 1.5rem; font-weight: 800; color: #fff; }
        
        .btn-confirm {
            display: block; width: 100%; padding: 1.2rem; margin-top: 2rem;
            background: #fff; color: #000; text-align: center; border: none;
            font-weight: 800; text-transform: uppercase; letter-spacing: 1px;
            cursor: pointer; border-radius: 4px; transition: 0.3s;
        }
        .btn-confirm:hover { background: #ccc; }
        .btn-confirm:disabled { background: #333; color: #666; cursor: not-allowed; }

        .back-link { display: inline-block; margin-bottom: 2rem; color: #888; text-decoration: none; }
        .back-link:hover { color: #fff; }
        .error-banner { background: rgba(239, 68, 68, 0.2); border: 1px solid #ef4444; color: #fca5a5; padding: 1rem; border-radius: 4px; margin-bottom: 2rem; text-align: center; }
    </style>
</head>
<body>

    <div class="container">
        <a href="carrito.php" class="back-link">← Volver al Carrito</a>
        <h1 class="page-title">Finalizar Compra</h1>

        <?php if (isset($_GET['error'])): ?>
            <div class="error-banner">⚠️ <?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <form action="procesar_pedido.php" method="POST" class="checkout-grid">
            
            <div class="checkout-forms">
                <div class="section-title">
                    <span>📍 Dirección de Envío</span>
                    <a href="agregar_direccion.php?return=checkout" class="section-link">+ Nueva</a>
                </div>
                
                <?php if (empty($direcciones)): ?>
                    <p style="color:#666; text-align:center; padding:2rem; border:1px dashed #333;">No hay direcciones. <a href="agregar_direccion.php?return=checkout" style="color:#fff;">Agrega una</a>.</p>
                <?php else: ?>
                    <div class="options-grid">
                        <?php foreach ($direcciones as $i => $dir): ?>
                        <label class="option-label">
                            <input type="radio" name="direccion_id" value="<?php echo $dir['id']; ?>" <?php echo $i===0?'checked':''; ?> required>
                            <div class="option-card">
                                <div class="radio-circle"><div class="radio-dot"></div></div>
                                <div>
                                    <strong style="display:block; margin-bottom:4px;"><?php echo htmlspecialchars($dir['nombre_completo']); ?></strong>
                                    <span style="font-size:0.9rem; color:#aaa;"><?php echo htmlspecialchars($dir['calle'].' #'.$dir['numero_exterior'].', '.$dir['colonia']); ?></span>
                                </div>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="section-title">
                    <span>💳 Método de Pago</span>
                    <a href="agregar_metodo_pago.php?return=checkout" class="section-link">+ Nueva</a>
                </div>

                <?php if (empty($metodos_pago)): ?>
                    <p style="color:#666; text-align:center; padding:2rem; border:1px dashed #333;">No hay métodos de pago. <a href="agregar_metodo_pago.php?return=checkout" style="color:#fff;">Agrega una tarjeta</a>.</p>
                <?php else: ?>
                    <div class="options-grid">
                        <?php 
                        $alguna_valida = false;
                        foreach ($metodos_pago as $pago): 
                            $vencida = estaVencida($pago['fecha_expiracion']);
                            if (!$vencida) $alguna_valida = true;
                        ?>
                        <label class="option-label <?php echo $vencida ? 'disabled' : ''; ?>">
                            <input type="radio" name="metodo_pago_id" value="<?php echo $pago['id']; ?>" <?php echo $vencida ? 'disabled' : ($alguna_valida && !$vencida ? '' : 'checked'); ?>>
                            <div class="option-card">
                                <?php if (!$vencida): ?>
                                    <div class="radio-circle"><div class="radio-dot"></div></div>
                                <?php else: ?>
                                    <div style="font-size:1.2rem;">🚫</div>
                                <?php endif; ?>
                                
                                <div style="flex:1; margin-left:10px;">
                                    <strong style="display:block; color:#fff;">
                                        <?php echo ucfirst($pago['tipo']); ?> •••• <?php echo htmlspecialchars($pago['ultimos_digitos']); ?>
                                        <?php if($vencida): ?><span class="badge-expired">CADUCADA</span><?php endif; ?>
                                    </strong>
                                    <span style="font-size:0.9rem; color:#aaa;">Expira: <?php echo htmlspecialchars($pago['fecha_expiracion']); ?></span>
                                </div>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="summary-col">
                <div class="summary-card">
                    <div style="font-weight:700; margin-bottom:1.5rem; border-bottom:1px solid #333; padding-bottom:1rem;">Resumen</div>
                    
                    <?php foreach($items as $item): ?>
                    <div style="display:flex; justify-content:space-between; margin-bottom:0.5rem; font-size:0.9rem; color:#aaa;">
                        <span><?php echo htmlspecialchars($item['nombre']); ?> (x<?php echo $item['cantidad']; ?>)</span>
                        <span>$<?php echo number_format($item['precio']*$item['cantidad'], 2); ?></span>
                    </div>
                    <?php endforeach; ?>

                    <div class="summary-total">
                        <span>Total</span>
                        <span>$<?php echo number_format($total, 2); ?></span>
                    </div>

                    <button type="submit" class="btn-confirm" <?php echo (empty($direcciones) || empty($metodos_pago) || !$alguna_valida) ? 'disabled' : ''; ?>>
                        Pagar Ahora
                    </button>
                    <p style="text-align:center; font-size:0.8rem; color:#666; margin-top:1rem;">🔒 Pagos procesados de forma segura.</p>
                </div>
            </div>

        </form>
    </div>
</body>
</html>