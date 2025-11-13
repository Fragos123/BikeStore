
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
    $nombre = trim($_POST['nombre']);
    $correo = trim($_POST['correo']);
    $nivel_ciclismo = trim($_POST['nivel_ciclismo']);
    $edad = !empty($_POST['edad']) ? (int)$_POST['edad'] : null;
    
    // Validaciones
    $errores = array();
    
    if (empty($nombre)) $errores[] = "El nombre es requerido";
    if (empty($correo)) $errores[] = "El correo es requerido";
    if (empty($nivel_ciclismo)) $errores[] = "El nivel de ciclismo es requerido";
    
    // Validar formato de correo
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El formato del correo no es válido";
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
    
    // Verificar si el correo ya existe (excepto el del usuario actual)
    if (empty($errores)) {
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE correo = ? AND id != ?");
        $stmt->bind_param("si", $correo, $usuario_id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows > 0) {
            $errores[] = "El correo ya está siendo utilizado por otro usuario";
        }
        $stmt->close();
    }
    
    // Actualizar datos
    if (empty($errores)) {
        if ($edad !== null) {
            $stmt = $conn->prepare("UPDATE usuarios SET nombre = ?, correo = ?, nivel_ciclismo = ?, edad = ? WHERE id = ?");
            $stmt->bind_param("sssii", $nombre, $correo, $nivel_ciclismo, $edad, $usuario_id);
        } else {
            $stmt = $conn->prepare("UPDATE usuarios SET nombre = ?, correo = ?, nivel_ciclismo = ?, edad = NULL WHERE id = ?");
            $stmt->bind_param("sssi", $nombre, $correo, $nivel_ciclismo, $usuario_id);
        }
        
        if ($stmt->execute()) {
            // Actualizar sesión
            $_SESSION['usuario_nombre'] = $nombre;
            $_SESSION['usuario_correo'] = $correo;
            $_SESSION['usuario_nivel_ciclismo'] = $nivel_ciclismo;
            $_SESSION['usuario_edad'] = $edad;
            
            $stmt->close();
            $conn->close();
            
            header("Location: editar-perfil.php?success=" . urlencode("Perfil actualizado exitosamente"));
            exit();
        } else {
            $errores[] = "Error al actualizar el perfil";
            $stmt->close();
        }
    }
    
    if (!empty($errores)) {
        $conn->close();
        $mensaje_error = implode(". ", $errores);
        header("Location: editar-perfil.php?error=" . urlencode($mensaje_error));
        exit();
    }
    
} else {
    header("Location: editar-perfil.php");
    exit();
}

$conn->close();
?>