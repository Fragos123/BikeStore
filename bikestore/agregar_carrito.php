<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header("Location: login.php?error=" . urlencode("Debes iniciar sesión para agregar productos al carrito"));
    exit;
}

include 'conexion.php';

// Verificar que sea POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: productos.php");
    exit;
}

// Obtener datos del formulario
$producto_id = isset($_POST['producto_id']) ? (int)$_POST['producto_id'] : 0;
$cantidad = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 1;
$talla = isset($_POST['talla']) ? trim($_POST['talla']) : '';
$usuario_id = $_SESSION['usuario_id'];

// Validaciones
$errores = [];

if ($producto_id <= 0) {
    $errores[] = "Producto inválido";
}

if ($cantidad <= 0) {
    $errores[] = "Cantidad inválida";
}

if (empty($talla)) {
    $errores[] = "Debes seleccionar una talla";
}

// ==========================================
// VALIDACIÓN CON TABLA producto_tallas
// ==========================================

if (empty($errores)) {
    // Verificar que el producto existe
    $stmt = $conn->prepare("SELECT id, nombre, precio FROM productos WHERE id = ?");
    $stmt->bind_param("i", $producto_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows === 0) {
        $errores[] = "El producto no existe";
    } else {
        $producto = $resultado->fetch_assoc();
        $precio = $producto['precio'];
        
        // Verificar que la talla existe para este producto y tiene stock
        $stmt2 = $conn->prepare("SELECT stock FROM producto_tallas WHERE producto_id = ? AND talla = ? AND activo = 1");
        $stmt2->bind_param("is", $producto_id, $talla);
        $stmt2->execute();
        $resultado_talla = $stmt2->get_result();
        
        if ($resultado_talla->num_rows === 0) {
            // Si no existe en producto_tallas, verificar en la tabla productos (retrocompatibilidad)
            $stmt3 = $conn->prepare("SELECT stock, talla FROM productos WHERE id = ? AND talla = ?");
            $stmt3->bind_param("is", $producto_id, $talla);
            $stmt3->execute();
            $resultado_producto = $stmt3->get_result();
            
            if ($resultado_producto->num_rows === 0) {
                $errores[] = "La talla seleccionada no está disponible para este producto";
                $stock_disponible = 0;
            } else {
                $producto_stock = $resultado_producto->fetch_assoc();
                $stock_disponible = $producto_stock['stock'];
            }
            $stmt3->close();
        } else {
            $talla_info = $resultado_talla->fetch_assoc();
            $stock_disponible = $talla_info['stock'];
        }
        $stmt2->close();
        
        // Verificar stock suficiente
        if (empty($errores)) {
            if ($stock_disponible < $cantidad) {
                $errores[] = "No hay suficiente stock para la talla {$talla}. Stock disponible: {$stock_disponible}";
            }
        }
    }
    $stmt->close();
}

// Si hay errores, redirigir
if (!empty($errores)) {
    $conn->close();
    $mensaje_error = implode(". ", $errores);
    header("Location: producto_detalle.php?id=$producto_id&error=" . urlencode($mensaje_error));
    exit;
}

// Verificar si el producto ya está en el carrito (mismo producto y talla)
$stmt = $conn->prepare("SELECT id, cantidad FROM carrito WHERE usuario_id = ? AND producto_id = ? AND talla = ?");
$stmt->bind_param("iis", $usuario_id, $producto_id, $talla);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {
    // El producto ya existe en el carrito, actualizar cantidad
    $item = $resultado->fetch_assoc();
    $nueva_cantidad = $item['cantidad'] + $cantidad;
    
    // Verificar que no exceda el stock
    if ($nueva_cantidad > $stock_disponible) {
        $stmt->close();
        $conn->close();
        header("Location: producto_detalle.php?id=$producto_id&error=" . urlencode("No puedes agregar más unidades. Stock máximo para talla {$talla}: {$stock_disponible}"));
        exit;
    }
    
    $stmt->close();
    
    // Actualizar cantidad
    $stmt = $conn->prepare("UPDATE carrito SET cantidad = ?, precio_unitario = ? WHERE id = ?");
    $stmt->bind_param("idi", $nueva_cantidad, $precio, $item['id']);
    
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        header("Location: carrito.php?success=" . urlencode("Cantidad actualizada en el carrito ✓"));
        exit;
    } else {
        $stmt->close();
        $conn->close();
        header("Location: producto_detalle.php?id=$producto_id&error=" . urlencode("Error al actualizar el carrito"));
        exit;
    }
    
} else {
    // El producto no está en el carrito, agregarlo
    $stmt->close();
    
    $stmt = $conn->prepare("INSERT INTO carrito (usuario_id, producto_id, cantidad, talla, precio_unitario) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iiisd", $usuario_id, $producto_id, $cantidad, $talla, $precio);
    
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        header("Location: carrito.php?success=" . urlencode("Producto agregado al carrito ✓"));
        exit;
    } else {
        $stmt->close();
        $conn->close();
        header("Location: producto_detalle.php?id=$producto_id&error=" . urlencode("Error al agregar al carrito"));
        exit;
    }
}

$conn->close();
?>