<?php
session_start();

// Verificar si el usuario está logueado y es admin
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true || $_SESSION['usuario_rol'] !== 'admin') {
    header("Location: login.php");
    exit;
}

include 'conexion.php';

// Verificar si el formulario fue enviado por POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $usuario_id = (int)$_POST['usuario_id'];
    $nombre = trim($_POST['nombre']);
    $correo = trim($_POST['correo']);
    $rol = trim($_POST['rol']);
    $nivel_ciclismo = trim($_POST['nivel_ciclismo']);
    $edad = !empty($_POST['edad']) ? (int)$_POST['edad'] : null;
    
    // Prevenir que el admin se edite a sí mismo desde aquí
    if ($usuario_id == $_SESSION['usuario_id']) {
        $conn->close();
        header("Location: principal_admin.php?error=" . urlencode("No puedes editarte a ti mismo desde aquí"));
        exit();
    }
    
    // Validaciones
    $errores = array();
    
    if (empty($nombre)) $errores[] = "El nombre es requerido";
    if (empty($correo)) $errores[] = "El correo es requerido";
    if (empty($rol)) $errores[] = "El rol es requerido";
    if (empty($nivel_ciclismo)) $errores[] = "El nivel de ciclismo es requerido";
    
    // Validar formato de correo
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El formato del correo no es válido";
    }
    
    // Validar rol
    $roles_permitidos = ['cliente', 'operador', 'admin'];
    if (!in_array($rol, $roles_permitidos)) {
        $errores[] = "El rol no es válido";
    }
    
    // Validar nivel de ciclismo
    $niveles_permitidos = ['principiante', 'intermedio', 'avanzado'];
    if (!in_array($nivel_ciclismo, $niveles_permitidos)) {
        $errores[] = "El nivel de ciclismo no es válido";
    }
    
    // Validar edad si se proporciona
    if ($edad !== null && ($edad < 13 || $edad > 120)) {
        $errores[] = "La edad debe estar entre 13 y 120 años";
    }
    
    // Verificar si el usuario existe
    if (empty($errores)) {
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows === 0) {
            $errores[] = "Usuario no encontrado";
        }
        $stmt->close();
    }
    
    // Verificar si el correo ya existe (excepto para este usuario)
    if (empty($errores)) {
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE correo = ? AND id != ?");
        $stmt->bind_param("si", $correo, $usuario_id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows > 0) {
            $errores[] = "Ya existe otro usuario con este correo electrónico";
        }
        $stmt->close();
    }
    
    // Actualizar usuario
    if (empty($errores)) {
        if ($edad !== null) {
            $stmt = $conn->prepare("UPDATE usuarios SET nombre = ?, correo = ?, rol = ?, nivel_ciclismo = ?, edad = ? WHERE id = ?");
            $stmt->bind_param("ssssii", $nombre, $correo, $rol, $nivel_ciclismo, $edad, $usuario_id);
        } else {
            $stmt = $conn->prepare("UPDATE usuarios SET nombre = ?, correo = ?, rol = ?, nivel_ciclismo = ?, edad = NULL WHERE id = ?");
            $stmt->bind_param("ssssi", $nombre, $correo, $rol, $nivel_ciclismo, $usuario_id);
        }
        
        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            
            header("Location: principal_admin.php?success=" . urlencode("Usuario '$nombre' actualizado exitosamente"));
            exit();
        } else {
            $errores[] = "Error al actualizar el usuario";
            $stmt->close();
        }
    }
    
    if (!empty($errores)) {
        $conn->close();
        $mensaje_error = implode(". ", $errores);
        header("Location: admin_editar_usuario.php?id=$usuario_id&error=" . urlencode($mensaje_error));
        exit();
    }
    
} else {
    header("Location: principal_admin.php");
    exit();
}

$conn->close();
?>