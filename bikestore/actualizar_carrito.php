<?php
session_start();
header('Content-Type: application/json');

// Verificar sesión
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

include 'conexion.php';

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

// Verificar que el item pertenece al usuario
$stmt = $conn->prepare("SELECT c.id, c.producto_id, p.stock FROM carrito c INNER JOIN productos p ON c.producto_id = p.id WHERE c.id = ? AND c.usuario_id = ?");
$stmt->bind_param("ii", $item_id, $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    $stmt->close();
    $conn->close();
    echo json_encode(['success' => false, 'message' => 'Item no encontrado']);
    exit;
}

$item = $resultado->fetch_assoc();
$stmt->close();

// Verificar stock
if ($cantidad > $item['stock']) {
    $conn->close();
    echo json_encode(['success' => false, 'message' => "Stock insuficiente. Máximo: {$item['stock']}"]);
    exit;
}

// Actualizar cantidad
$stmt = $conn->prepare("UPDATE carrito SET cantidad = ? WHERE id = ?");
$stmt->bind_param("ii", $cantidad, $item_id);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    echo json_encode(['success' => true, 'message' => 'Cantidad actualizada']);
} else {
    $stmt->close();
    $conn->close();
    echo json_encode(['success' => false, 'message' => 'Error al actualizar']);
}
?>