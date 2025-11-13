<?php
session_start();

if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header("Location: login.php");
    exit;
}

include 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $usuario_id = $_SESSION['usuario_id'];
    $direccion_id = isset($_POST['direccion_id']) ? (int)$_POST['direccion_id'] : 0;
    $metodo_pago_id = isset($_POST['metodo_pago_id']) ? (int)$_POST['metodo_pago_id'] : 0;
    
    // Validaciones
    $errores = [];
    
    if ($direccion_id <= 0) {
        $errores[] = "Dirección inválida";
    }
    
    if ($metodo_pago_id <= 0) {
        $errores[] = "Método de pago inválido";
    }
    
    // Verificar que la dirección pertenece al usuario
    $stmt = $conn->prepare("SELECT id FROM direcciones WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $direccion_id, $usuario_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        $errores[] = "Dirección no válida";
    }
    $stmt->close();
    
    // Verificar que el método de pago pertenece al usuario
    $stmt = $conn->prepare("SELECT id FROM metodos_pago WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $metodo_pago_id, $usuario_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        $errores[] = "Método de pago no válido";
    }
    $stmt->close();
    
    // Obtener items del carrito
    $stmt = $conn->prepare("SELECT c.*, p.nombre, p.imagen_principal, p.stock FROM carrito c INNER JOIN productos p ON c.producto_id = p.id WHERE c.usuario_id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $items_carrito = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    if (empty($items_carrito)) {
        $errores[] = "El carrito está vacío";
    }
    
    // Validar stock de cada producto
    foreach ($items_carrito as $item) {
        // Verificar stock en producto_tallas
        $stmt = $conn->prepare("SELECT stock FROM producto_tallas WHERE producto_id = ? AND talla = ? AND activo = 1");
        $stmt->bind_param("is", $item['producto_id'], $item['talla']);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows > 0) {
            $stock_info = $resultado->fetch_assoc();
            $stock_disponible = $stock_info['stock'];
        } else {
            // Fallback a tabla productos
            $stock_disponible = $item['stock'];
        }
        $stmt->close();
        
        if ($stock_disponible < $item['cantidad']) {
            $errores[] = "Stock insuficiente para {$item['nombre']} talla {$item['talla']}";
        }
    }
    
    if (!empty($errores)) {
        $conn->close();
        $mensaje_error = implode(". ", $errores);
        header("Location: checkout.php?error=" . urlencode($mensaje_error));
        exit;
    }
    
    // Calcular totales
    $subtotal = 0;
    foreach ($items_carrito as $item) {
        $subtotal += $item['precio_unitario'] * $item['cantidad'];
    }
    $envio = $subtotal > 1000 ? 0 : 150;
    $total = $subtotal + $envio;
    
    // ==========================================
    // INICIAR TRANSACCIÓN
    // ==========================================
    
    $conn->begin_transaction();
    
    try {
        // 1. Crear el pedido
        $stmt = $conn->prepare("INSERT INTO pedidos (usuario_id, total, direccion_envio_id, metodo_pago_id, estado, subtotal, envio) VALUES (?, ?, ?, ?, 'pendiente', ?, ?)");
        $stmt->bind_param("idiiii", $usuario_id, $total, $direccion_id, $metodo_pago_id, $subtotal, $envio);
        
        if (!$stmt->execute()) {
            throw new Exception("Error al crear el pedido");
        }
        
        $pedido_id = $conn->insert_id;
        $stmt->close();
        
        // 2. Insertar items del pedido
        $stmt_item = $conn->prepare("INSERT INTO pedido_items (pedido_id, producto_id, nombre_producto, talla, cantidad, precio_unitario, subtotal, imagen_producto) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        
        foreach ($items_carrito as $item) {
            $subtotal_item = $item['precio_unitario'] * $item['cantidad'];
            
            $stmt_item->bind_param("iissidds",
                $pedido_id,
                $item['producto_id'],
                $item['nombre'],
                $item['talla'],
                $item['cantidad'],
                $item['precio_unitario'],
                $subtotal_item,
                $item['imagen_principal']
            );
            
            if (!$stmt_item->execute()) {
                throw new Exception("Error al agregar items al pedido");
            }
            
            // 3. Reducir stock (primero intentar en producto_tallas)
            $stmt_stock = $conn->prepare("UPDATE producto_tallas SET stock = stock - ? WHERE producto_id = ? AND talla = ?");
            $stmt_stock->bind_param("iis", $item['cantidad'], $item['producto_id'], $item['talla']);
            $stmt_stock->execute();
            
            if ($stmt_stock->affected_rows === 0) {
                // Si no afectó filas, actualizar en tabla productos
                $stmt_stock2 = $conn->prepare("UPDATE productos SET stock = stock - ? WHERE id = ?");
                $stmt_stock2->bind_param("ii", $item['cantidad'], $item['producto_id']);
                $stmt_stock2->execute();
                $stmt_stock2->close();
            }
            
            $stmt_stock->close();
        }
        
        $stmt_item->close();
        
        // 4. Vaciar el carrito
        $stmt = $conn->prepare("DELETE FROM carrito WHERE usuario_id = ?");
        $stmt->bind_param("i", $usuario_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Error al limpiar el carrito");
        }
        
        $stmt->close();
        
        // ==========================================
        // COMMIT DE LA TRANSACCIÓN
        // ==========================================
        
        $conn->commit();
        $conn->close();
        
        // Redirigir a confirmación
        header("Location: pedidos.php?success=" . urlencode("¡Pedido realizado exitosamente! Número de pedido: #" . str_pad($pedido_id, 6, '0', STR_PAD_LEFT)));
        exit;
        
    } catch (Exception $e) {
        // Rollback en caso de error
        $conn->rollback();
        $conn->close();
        
        header("Location: checkout.php?error=" . urlencode("Error al procesar el pedido: " . $e->getMessage()));
        exit;
    }
    
} else {
    header("Location: checkout.php");
    exit;
}
?>