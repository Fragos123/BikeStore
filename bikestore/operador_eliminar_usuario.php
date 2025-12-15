<?php

session_start();

// Verificar si el usuario está logueado y es operador
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true || $_SESSION['usuario_rol'] !== 'operador') {
    header("Location: login.php");
    exit;
}

include 'conexion.php';

if (isset($_GET['id'])) {
    $usuario_id = (int)$_GET['id'];
    
    // Verificar que el usuario existe y es cliente
    $stmt = $conn->prepare("SELECT id, nombre, rol FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows > 0) {
        $usuario = $resultado->fetch_assoc();
        
        // Solo permitir eliminar clientes
        if ($usuario['rol'] !== 'cliente') {
            $stmt->close();
            $conn->close();
            header("Location: principal_operador.php?error=" . urlencode("Solo puedes eliminar usuarios con rol de cliente"));
            exit();
        }
        
        $stmt->close();
        
        // Eliminar el usuario
        $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $usuario_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            header("Location: principal_operador.php?success=" . urlencode("Usuario '" . $usuario['nombre'] . "' eliminado exitosamente"));
            exit();
        } else {
            $stmt->close();
            $conn->close();
            header("Location: principal_operador.php?error=" . urlencode("Error al eliminar el usuario"));
            exit();
        }
    } else {
        $stmt->close();
        $conn->close();
        header("Location: principal_operador.php?error=" . urlencode("Usuario no encontrado"));
        exit();
    }
} else {
    header("Location: principal_operador.php");
    exit();
}

$conn->close();
?>