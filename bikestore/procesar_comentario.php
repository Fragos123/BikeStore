<?php
session_start();
include 'conexion.php';

// 1. Verificar si el usuario está logueado
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 2. Recibir y limpiar datos
    $producto_id = isset($_POST['producto_id']) ? intval($_POST['producto_id']) : 0;
    $usuario_id = $_SESSION['usuario_id'];
    $calificacion = isset($_POST['calificacion']) ? intval($_POST['calificacion']) : 0;
    $comentario = isset($_POST['comentario']) ? trim($_POST['comentario']) : '';

    // 3. Validaciones básicas
    if ($producto_id <= 0) {
        header("Location: productos.php");
        exit;
    }

    if ($calificacion < 1 || $calificacion > 5) {
        header("Location: producto_detalle.php?id=$producto_id&error=" . urlencode("La calificación debe ser entre 1 y 5 estrellas."));
        exit;
    }

    if (empty($comentario)) {
        header("Location: producto_detalle.php?id=$producto_id&error=" . urlencode("El comentario no puede estar vacío."));
        exit;
    }

    // 4. Insertar en la Base de Datos
    // Asegúrate de que tu tabla se llame 'comentarios' y tenga estas columnas
    $sql = "INSERT INTO comentarios (producto_id, usuario_id, calificacion, comentario, fecha) VALUES (?, ?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("iiis", $producto_id, $usuario_id, $calificacion, $comentario);
        
        if ($stmt->execute()) {
            // Éxito: Redirigir al producto con mensaje
            header("Location: producto_detalle.php?id=$producto_id&comentario=success");
        } else {
            // Error de SQL
            header("Location: producto_detalle.php?id=$producto_id&error=" . urlencode("Error al guardar: " . $conn->error));
        }
        $stmt->close();
    } else {
        header("Location: producto_detalle.php?id=$producto_id&error=" . urlencode("Error en la consulta: " . $conn->error));
    }
} else {
    // Si intentan entrar directo al archivo sin enviar formulario
    header("Location: productos.php");
}

$conn->close();
?>