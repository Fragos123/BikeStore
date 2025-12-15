<?php

session_start();

// Verificar si el usuario está logueado y es operador
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true || $_SESSION['usuario_rol'] !== 'operador') {
    header("Location: login.php");
    exit;
}

include 'conexion.php';

// Verificar que se recibió un ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: principal_operador.php?error=" . urlencode("ID de producto no válido"));
    exit;
}

$producto_id = intval($_GET['id']);

// Primero obtener el nombre del producto para el mensaje
$stmt = $conn->prepare("SELECT nombre FROM productos WHERE id = ?");
$stmt->bind_param("i", $producto_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    $conn->close();
    header("Location: principal_operador.php?error=" . urlencode("Producto no encontrado"));
    exit;
}

$producto = $result->fetch_assoc();
$nombre_producto = $producto['nombre'];
$stmt->close();

// Eliminar comentarios asociados primero (integridad referencial)
$stmt = $conn->prepare("DELETE FROM comentarios WHERE producto_id = ?");
$stmt->bind_param("i", $producto_id);
$stmt->execute();
$stmt->close();

// Ahora eliminar el producto
$stmt = $conn->prepare("DELETE FROM productos WHERE id = ?");
$stmt->bind_param("i", $producto_id);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    header("Location: principal_operador.php?success=" . urlencode("Producto '$nombre_producto' eliminado exitosamente"));
    exit;
} else {
    $stmt->close();
    $conn->close();
    header("Location: principal_operador.php?error=" . urlencode("Error al eliminar el producto"));
    exit;
}
?>