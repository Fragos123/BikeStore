<?php
/**
 * Funciones auxiliares para el carrito de compras
 * Incluir este archivo donde necesites obtener información del carrito
 */

/**
 * Obtener cantidad total de items en el carrito del usuario actual
 * @return int
 */
function obtenerCantidadCarrito() {
    if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
        return 0;
    }
    
    include 'conexion.php';
    
    $usuario_id = $_SESSION['usuario_id'];
    $stmt = $conn->prepare("SELECT SUM(cantidad) as total FROM carrito WHERE usuario_id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $count = 0;
    if ($row = $result->fetch_assoc()) {
        $count = $row['total'] ? (int)$row['total'] : 0;
    }
    
    $stmt->close();
    $conn->close();
    
    return $count;
}

/**
 * Obtener total del carrito
 * @return array ['subtotal' => float, 'envio' => float, 'total' => float, 'items' => int]
 */
function obtenerTotalCarrito() {
    if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
        return ['subtotal' => 0, 'envio' => 0, 'total' => 0, 'items' => 0];
    }
    
    include 'conexion.php';
    
    $usuario_id = $_SESSION['usuario_id'];
    $stmt = $conn->prepare("SELECT SUM(precio_unitario * cantidad) as subtotal, SUM(cantidad) as items FROM carrito WHERE usuario_id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $subtotal = 0;
    $items = 0;
    
    if ($row = $result->fetch_assoc()) {
        $subtotal = $row['subtotal'] ? (float)$row['subtotal'] : 0;
        $items = $row['items'] ? (int)$row['items'] : 0;
    }
    
    $stmt->close();
    $conn->close();
    
    // Calcular envío (gratis si es mayor a $1000)
    $envio = $subtotal > 1000 ? 0 : 150;
    $total = $subtotal + $envio;
    
    return [
        'subtotal' => $subtotal,
        'envio' => $envio,
        'total' => $total,
        'items' => $items
    ];
}

/**
 * Verificar si un producto está en el carrito del usuario
 * @param int $producto_id
 * @param string|null $talla
 * @return bool
 */
function productoEnCarrito($producto_id, $talla = null) {
    if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
        return false;
    }
    
    include 'conexion.php';
    
    $usuario_id = $_SESSION['usuario_id'];
    
    if ($talla) {
        $stmt = $conn->prepare("SELECT id FROM carrito WHERE usuario_id = ? AND producto_id = ? AND talla = ?");
        $stmt->bind_param("iis", $usuario_id, $producto_id, $talla);
    } else {
        $stmt = $conn->prepare("SELECT id FROM carrito WHERE usuario_id = ? AND producto_id = ?");
        $stmt->bind_param("ii", $usuario_id, $producto_id);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $existe = $result->num_rows > 0;
    
    $stmt->close();
    $conn->close();
    
    return $existe;
}
?>