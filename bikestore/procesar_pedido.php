<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = $_SESSION['usuario_id'];
    $direccion_id = isset($_POST['direccion_id']) ? intval($_POST['direccion_id']) : 0;
    $metodo_pago_id = isset($_POST['metodo_pago_id']) ? intval($_POST['metodo_pago_id']) : 0;

    // 1. VALIDAR SELECCIÓN
    if ($direccion_id <= 0 || $metodo_pago_id <= 0) {
        header("Location: checkout.php?error=Selecciona una dirección y un método de pago.");
        exit;
    }

    // 2. VALIDAR TARJETA (SEGURIDAD BANCARIA)
    $stmt = $conn->prepare("SELECT fecha_expiracion, ultimos_digitos FROM metodos_pago WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $metodo_pago_id, $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        header("Location: checkout.php?error=El método de pago no existe.");
        exit;
    }

    $tarjeta = $result->fetch_assoc();
    $fecha_exp = $tarjeta['fecha_expiracion']; 

    // Reglas de validación de fecha
    if (!preg_match('/^\d{2}\/\d{2}$/', $fecha_exp)) {
         header("Location: checkout.php?error=Formato de fecha inválido.");
         exit;
    }
    list($mes, $anio) = explode('/', $fecha_exp);
    $mes = (int)$mes; $anio = (int)$anio + 2000;
    $actual_mes = (int)date('m'); $actual_anio = (int)date('Y');

    if ($anio < $actual_anio || ($anio == $actual_anio && $mes < $actual_mes)) {
        header("Location: checkout.php?error=La tarjeta terminada en " . $tarjeta['ultimos_digitos'] . " está vencida.");
        exit;
    }
    $stmt->close();

    // 3. OBTENER ITEMS Y VALIDAR STOCK REAL
    // FIX: Usamos LEFT JOIN con producto_tallas y COLLATE para evitar errores de base de datos
    $sql_items = "
        SELECT 
            c.*, 
            p.nombre, 
            p.precio, 
            p.stock as stock_general, 
            pt.stock as stock_talla
        FROM carrito c 
        JOIN productos p ON c.producto_id = p.id 
        LEFT JOIN producto_tallas pt ON c.producto_id = pt.producto_id 
            AND c.talla = pt.talla COLLATE utf8mb4_unicode_ci
        WHERE c.usuario_id = ?
    ";

    $stmt = $conn->prepare($sql_items);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (empty($items)) { header("Location: carrito.php"); exit; }

    $subtotal = 0;
    foreach ($items as $item) {
        // Validación de Stock Crítica
        $stock_real = ($item['stock_talla'] !== null) ? $item['stock_talla'] : $item['stock_general'];
        
        if ($item['cantidad'] > $stock_real) {
            header("Location: checkout.php?error=" . urlencode("Stock insuficiente para: " . $item['nombre'] . " (Talla: " . $item['talla'] . ")"));
            exit;
        }
        $subtotal += $item['precio'] * $item['cantidad'];
    }
    
    $envio = ($subtotal > 0 && $subtotal < 1000) ? 150 : 0;
    $total = $subtotal + $envio;

    // 4. PROCESAR TRANSACCIÓN
    $conn->begin_transaction();
    try {
        // Insertar Pedido
        $stmt = $conn->prepare("INSERT INTO pedidos (usuario_id, fecha, estado, total, subtotal, envio, direccion_envio_id, metodo_pago_id) VALUES (?, NOW(), 'pendiente', ?, ?, ?, ?, ?)");
        $stmt->bind_param("idddii", $usuario_id, $total, $subtotal, $envio, $direccion_id, $metodo_pago_id);
        $stmt->execute();
        $pedido_id = $conn->insert_id;
        $stmt->close();

        // Mover items del carrito a pedido_items
        $sql_insert_items = "INSERT INTO pedido_items (pedido_id, producto_id, cantidad, precio_unitario, subtotal, nombre_producto, talla) SELECT ?, c.producto_id, c.cantidad, p.precio, (p.precio * c.cantidad), p.nombre, c.talla FROM carrito c JOIN productos p ON c.producto_id = p.id WHERE c.usuario_id = ?";
        $stmt_item = $conn->prepare($sql_insert_items);
        $stmt_item->bind_param("ii", $pedido_id, $usuario_id);
        if (!$stmt_item->execute()) throw new Exception("Error al guardar los items del pedido.");
        $stmt_item->close();

        // Descontar Stock
        foreach ($items as $item) {
            // Descontar del general
            $conn->query("UPDATE productos SET stock = stock - {$item['cantidad']} WHERE id = {$item['producto_id']}");
            
            // Descontar de la talla específica (si aplica)
            if ($item['stock_talla'] !== null) {
                $talla_safe = $conn->real_escape_string($item['talla']);
                // FIX: Agregamos COLLATE aquí también por si acaso
                $conn->query("UPDATE producto_tallas SET stock = stock - {$item['cantidad']} WHERE producto_id = {$item['producto_id']} AND talla = '$talla_safe' COLLATE utf8mb4_unicode_ci");
            }
        }

        // Vaciar Carrito
        $stmt_del = $conn->prepare("DELETE FROM carrito WHERE usuario_id = ?");
        $stmt_del->bind_param("i", $usuario_id);
        $stmt_del->execute();
        $stmt_del->close();

        $conn->commit();
        header("Location: exito.php?id=" . $pedido_id);
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        header("Location: checkout.php?error=" . urlencode("Error al procesar: " . $e->getMessage()));
        exit;
    }
}
$conn->close();
?>