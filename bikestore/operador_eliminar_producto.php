<?php
session_start();

// 1. SEGURIDAD: Permitir 'operador' O 'admin'
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true || 
    !in_array($_SESSION['usuario_rol'], ['operador', 'admin'])) {
    header("Location: login.php");
    exit;
}

include 'conexion.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $redirect = ($_SESSION['usuario_rol'] === 'admin') ? 'lista_productos.php' : 'principal_operador.php';
    header("Location: $redirect?error=ID_invalido");
    exit;
}

$producto_id = intval($_GET['id']);
$redirect_page = ($_SESSION['usuario_rol'] === 'admin') ? 'lista_productos.php' : 'principal_operador.php';

// 2. ELIMINACIÓN LÓGICA (SOFT DELETE)
// En lugar de DELETE FROM, usamos UPDATE. 
// Ponemos el stock en -1 para indicar al sistema que este producto está "BORRADO" del catálogo
// pero mantenemos sus datos para que los REPORTES DE VENTAS sigan funcionando.

$stmt = $conn->prepare("UPDATE productos SET stock = -1 WHERE id = ?");
$stmt->bind_param("i", $producto_id);

if ($stmt->execute()) {
    // También desactivamos sus tallas para que no ocupen espacio en memoria
    $conn->query("UPDATE producto_tallas SET activo = 0 WHERE producto_id = $producto_id");
    
    $stmt->close();
    $conn->close();
    header("Location: $redirect_page?success=" . urlencode("Producto removido del catálogo exitosamente. Los registros históricos se mantienen."));
    exit;
} else {
    $conn->close();
    header("Location: $redirect_page?error=" . urlencode("Error al intentar remover el producto."));
    exit;
}