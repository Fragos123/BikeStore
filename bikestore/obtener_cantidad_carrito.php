<?php
session_start();
header('Content-Type: application/json');

// Verificar si está logueado
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    echo json_encode(['cantidad' => 0]);
    exit;
}

include 'conexion.php';

$usuario_id = $_SESSION['usuario_id'];
$stmt = $conn->prepare("SELECT SUM(cantidad) as total FROM carrito WHERE usuario_id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

$cantidad = 0;
if ($row = $result->fetch_assoc()) {
    $cantidad = $row['total'] ? (int)$row['total'] : 0;
}

$stmt->close();
$conn->close();

echo json_encode(['cantidad' => $cantidad]);
?>