<?php
session_start();
header('Content-Type: application/json');
include 'conexion.php';

// Verificar sesión
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$item_id = isset($_POST['item_id']) ? (int)$_POST['item_id'] : 0;
$cantidad = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 0;
$usuario_id = $_SESSION['usuario_id'];

if ($item_id <= 0 || $cantidad <= 0) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit;
}

// 1. OBTENER INFO DEL ITEM EN EL CARRITO
// Necesitamos saber qué producto es y QUÉ TALLA tiene para validar el stock correcto
$stmt = $conn->prepare("SELECT producto_id, talla FROM carrito WHERE id = ? AND usuario_id = ?");
$stmt->bind_param("ii", $item_id, $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Item no encontrado']);
    exit;
}

$carrito_item = $resultado->fetch_assoc();
$producto_id = $carrito_item['producto_id'];
$talla = $carrito_item['talla'];
$stmt->close();

// 2. VERIFICAR STOCK REAL (SEGÚN SI TIENE TALLA O NO)
$stock_disponible = 0;

if (!empty($talla)) {
    // Si tiene talla, buscamos en la tabla de tallas
    $stmt_stock = $conn->prepare("SELECT stock FROM producto_tallas WHERE producto_id = ? AND talla = ?");
    $stmt_stock->bind_param("is", $producto_id, $talla);
    $stmt_stock->execute();
    $res_stock = $stmt_stock->get_result();
    
    if ($fila = $res_stock->fetch_assoc()) {
        $stock_disponible = $fila['stock'];
    }
    $stmt_stock->close();
} else {
    // Si NO tiene talla, buscamos en la tabla de productos general
    $stmt_stock = $conn->prepare("SELECT stock FROM productos WHERE id = ?");
    $stmt_stock->bind_param("i", $producto_id);
    $stmt_stock->execute();
    $res_stock = $stmt_stock->get_result();
    
    if ($fila = $res_stock->fetch_assoc()) {
        $stock_disponible = $fila['stock'];
    }
    $stmt_stock->close();
}

// 3. VALIDAR LÍMITE
if ($cantidad > $stock_disponible) {
    echo json_encode([
        'success' => false, 
        'message' => "Stock insuficiente. Solo hay $stock_disponible disponibles en talla $talla."
    ]);
    exit;
}

// 4. ACTUALIZAR CANTIDAD
$stmt_update = $conn->prepare("UPDATE carrito SET cantidad = ? WHERE id = ?");
$stmt_update->bind_param("ii", $cantidad, $item_id);

if ($stmt_update->execute()) {
    echo json_encode(['success' => true, 'message' => 'Cantidad actualizada']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar']);
}

$stmt_update->close();
$conn->close();
?>