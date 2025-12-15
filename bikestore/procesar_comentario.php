<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header("Location: login.php");
    exit;
}

include 'conexion.php';

// Verificar si el formulario fue enviado por POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $producto_id = (int)$_POST['producto_id'];
    $usuario_id = $_SESSION['usuario_id'];
    $comentario = trim($_POST['comentario']);
    $calificacion = (int)$_POST['calificacion'];
    
    // Validaciones
    $errores = array();
    
    if ($producto_id <= 0) {
        $errores[] = "ID de producto inválido";
    }
    
    if (empty($comentario) || strlen($comentario) < 10) {
        $errores[] = "El comentario debe tener al menos 10 caracteres";
    }
    
    if ($calificacion < 1 || $calificacion > 5) {
        $errores[] = "La calificación debe estar entre 1 y 5";
    }
    
    // Verificar que el producto existe
    if (empty($errores)) {
        $stmt = $conn->prepare("SELECT id FROM productos WHERE id = ?");
        $stmt->bind_param("i", $producto_id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows === 0) {
            $errores[] = "El producto no existe";
        }
        $stmt->close();
    }
    
    // Verificar si el usuario ya comentó este producto
    if (empty($errores)) {
        $stmt = $conn->prepare("SELECT id FROM comentarios WHERE producto_id = ? AND usuario_id = ?");
        $stmt->bind_param("ii", $producto_id, $usuario_id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows > 0) {
            // Ya existe un comentario, actualizar
            $stmt->close();
            
            $stmt = $conn->prepare("UPDATE comentarios SET comentario = ?, calificacion = ?, fecha = CURRENT_TIMESTAMP WHERE producto_id = ? AND usuario_id = ?");
            $stmt->bind_param("siii", $comentario, $calificacion, $producto_id, $usuario_id);
            
            if ($stmt->execute()) {
                $stmt->close();
                $conn->close();
                header("Location: producto_detalle.php?id=$producto_id&comentario=actualizado");
                exit();
            } else {
                $errores[] = "Error al actualizar el comentario";
                $stmt->close();
            }
        } else {
            // No existe comentario, crear nuevo
            $stmt->close();
            
            $stmt = $conn->prepare("INSERT INTO comentarios (producto_id, usuario_id, comentario, calificacion) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iisi", $producto_id, $usuario_id, $comentario, $calificacion);
            
            if ($stmt->execute()) {
                $stmt->close();
                $conn->close();
                header("Location: producto_detalle.php?id=$producto_id&comentario=success");
                exit();
            } else {
                $errores[] = "Error al agregar el comentario";
                $stmt->close();
            }
        }
    }
    
    // Si hay errores, regresar al detalle del producto
    if (!empty($errores)) {
        $conn->close();
        $mensaje_error = implode(". ", $errores);
        header("Location: producto_detalle.php?id=$producto_id&comentario=error&mensaje=" . urlencode($mensaje_error));
        exit();
    }
    
} else {
    // Si no es POST, redirigir
    header("Location: productos.php");
    exit();
}

$conn->close();
?>