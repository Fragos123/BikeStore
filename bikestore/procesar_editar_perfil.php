<?php
session_start();
include 'conexion.php';

// 1. Verificar Sesión
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = $_SESSION['usuario_id'];
    
    // 2. Recibir y limpiar datos
    $nombre = trim($_POST['nombre']);
    $correo = trim($_POST['correo']);
    $edad = (int)$_POST['edad'];
    $nivel = trim($_POST['nivel_ciclismo']);
    
    // 3. Validaciones PHP (Backend) - Espejo de las de JS
    $errores = [];
    if (strlen($nombre) < 3) $errores[] = "Nombre muy corto.";
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) $errores[] = "Correo inválido.";
    if ($edad < 13 || $edad > 120) $errores[] = "Edad inválida.";
    if (empty($nivel)) $errores[] = "Nivel requerido.";

    if (empty($errores)) {
        // 4. Actualizar Base de Datos
        $sql = "UPDATE usuarios SET nombre = ?, correo = ?, edad = ?, nivel_ciclismo = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssisi", $nombre, $correo, $edad, $nivel, $usuario_id);
        
        if ($stmt->execute()) {
            // Actualizar nombre en la sesión actual para que se refleje inmediato en el navbar
            $_SESSION['usuario_nombre'] = $nombre;
            
            // Redirigir con éxito
            header("Location: perfil.php?success=" . urlencode("Perfil actualizado correctamente"));
        } else {
            // Error SQL (ej. correo duplicado)
            header("Location: editar_perfil.php?error=" . urlencode("Error al actualizar. Puede que el correo ya esté en uso."));
        }
        $stmt->close();
    } else {
        // Redirigir con errores de validación
        header("Location: editar_perfil.php?error=" . urlencode(implode(" ", $errores)));
    }
} else {
    header("Location: perfil.php");
}
$conn->close();
?>