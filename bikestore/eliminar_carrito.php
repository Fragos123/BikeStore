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
$usuario_id = $_SESSION['usuario_id'];

if ($item_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

// Eliminar item (solo si pertenece al usuario)
$stmt = $conn->prepare("DELETE FROM carrito WHERE id = ? AND usuario_id = ?");
$stmt->bind_param("ii", $item_id, $usuario_id);

if ($stmt->execute()) {
    $affected_rows = $stmt->affected_rows;
    $stmt->close();
    $conn->close();
    
    if ($affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Producto eliminado del carrito']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Item no encontrado']);
    }
} else {
    $stmt->close();
    $conn->close();
    echo json_encode(['success' => false, 'message' => 'Error al eliminar']);
}
?>