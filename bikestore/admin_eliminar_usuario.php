<?php

session_start();

// Verificar si el usuario está logueado y es admin
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true || $_SESSION['usuario_rol'] !== 'admin') {
    header("Location: login.php");
    exit;
}

include 'conexion.php';

if (isset($_GET['id'])) {
    $usuario_id = (int)$_GET['id'];
    $filtro_rol = isset($_GET['filtro_rol']) ? $_GET['filtro_rol'] : '';
    
    // Prevenir que el admin se elimine a sí mismo
    if ($usuario_id == $_SESSION['usuario_id']) {
        $conn->close();
        $redirect = "principal_admin.php?error=" . urlencode("No puedes eliminarte a ti mismo");
        if (!empty($filtro_rol)) {
            $redirect .= "&filtro_rol=" . urlencode($filtro_rol);
        }
        header("Location: " . $redirect);
        exit();
    }
    
    // Verificar que el usuario existe
    $stmt = $conn->prepare("SELECT id, nombre, rol FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows > 0) {
        $usuario = $resultado->fetch_assoc();
        $stmt->close();
        
        // Eliminar el usuario
        $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $usuario_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            
            $redirect = "principal_admin.php?success=" . urlencode("Usuario '" . $usuario['nombre'] . "' eliminado exitosamente");
            if (!empty($filtro_rol)) {
                $redirect .= "&filtro_rol=" . urlencode($filtro_rol);
            }
            header("Location: " . $redirect);
            exit();
        } else {
            $stmt->close();
            $conn->close();
            
            $redirect = "principal_admin.php?error=" . urlencode("Error al eliminar el usuario");
            if (!empty($filtro_rol)) {
                $redirect .= "&filtro_rol=" . urlencode($filtro_rol);
            }
            header("Location: " . $redirect);
            exit();
        }
    } else {
        $stmt->close();
        $conn->close();
        
        $redirect = "principal_admin.php?error=" . urlencode("Usuario no encontrado");
        if (!empty($filtro_rol)) {
            $redirect .= "&filtro_rol=" . urlencode($filtro_rol);
        }
        header("Location: " . $redirect);
        exit();
    }
} else {
    $conn->close();
    header("Location: principal_admin.php");
    exit();
}
?>