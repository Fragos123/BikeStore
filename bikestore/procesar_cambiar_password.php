
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
    
    $usuario_id = $_SESSION['usuario_id'];
    $password_actual = $_POST['password_actual'];
    $password_nueva = $_POST['password_nueva'];
    $password_confirmar = $_POST['password_confirmar'];
    
    // Validaciones
    $errores = array();
    
    // Validar campos requeridos
    if (empty($password_actual)) $errores[] = "La contraseña actual es requerida";
    if (empty($password_nueva)) $errores[] = "La nueva contraseña es requerida";
    if (empty($password_confirmar)) $errores[] = "Debes confirmar la nueva contraseña";
    
    // Validar que las contraseñas nuevas coincidan
    if ($password_nueva !== $password_confirmar) {
        $errores[] = "Las contraseñas nuevas no coinciden";
    }
    
    // Validar que la nueva contraseña sea diferente a la actual
    if ($password_actual === $password_nueva) {
        $errores[] = "La nueva contraseña debe ser diferente a la actual";
    }
    
    // Validar requisitos de la nueva contraseña
    if (strlen($password_nueva) < 8) {
        $errores[] = "La nueva contraseña debe tener al menos 8 caracteres";
    }
    if (!preg_match('/[A-Z]/', $password_nueva)) {
        $errores[] = "La nueva contraseña debe tener al menos una letra mayúscula";
    }
    if (!preg_match('/[a-z]/', $password_nueva)) {
        $errores[] = "La nueva contraseña debe tener al menos una letra minúscula";
    }
    if (!preg_match('/[0-9]/', $password_nueva)) {
        $errores[] = "La nueva contraseña debe tener al menos un número";
    }
    if (!preg_match('/[!"#$%&\'()*+,\-.\/:\;<=>?@\[\]^_{|}~]/', $password_nueva)) {
        $errores[] = "La nueva contraseña debe tener al menos un símbolo especial";
    }
    
    // Verificar la contraseña actual
    if (empty($errores)) {
        $stmt = $conn->prepare("SELECT password FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $usuario = $resultado->fetch_assoc();
        $stmt->close();
        
        if (!password_verify($password_actual, $usuario['password'])) {
            $errores[] = "La contraseña actual es incorrecta";
        }
    }
    
    // Actualizar contraseña
    if (empty($errores)) {
        $password_hash = password_hash($password_nueva, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $password_hash, $usuario_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            
            header("Location: cambiar_password.php?success=" . urlencode("Contraseña actualizada exitosamente"));
            exit();
        } else {
            $errores[] = "Error al actualizar la contraseña";
            $stmt->close();
        }
    }
    
    // Si hay errores, regresar al formulario
    if (!empty($errores)) {
        $conn->close();
        $mensaje_error = implode(". ", $errores);
        header("Location: cambiar_password.php?error=" . urlencode($mensaje_error));
        exit();
    }
    
} else {
    header("Location: cambiar_password.php");
    exit();
}

$conn->close();
?>