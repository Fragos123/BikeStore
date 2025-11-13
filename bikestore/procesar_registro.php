<?php 
session_start(); 
include 'conexion.php';

// Verificar si el formulario fue enviado por POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // ===== OBTENER Y LIMPIAR DATOS =====
    $nombre = trim($_POST['nombre']);
    $apellidos = trim($_POST['apellidos']);
    $correo = trim($_POST['correo']);
    $password = $_POST['password'];
    $nivel_ciclismo = trim($_POST['nivel_ciclismo']);
    $edad = !empty($_POST['edad']) ? (int)$_POST['edad'] : null;
    
    // Combinar nombre y apellidos en un solo campo para la BD
    $nombre_completo = $nombre . ' ' . $apellidos;
    
    // ===== VALIDACIONES DEL SERVIDOR =====
    $errores = array();
    
    // Validar campos requeridos
    if (empty($nombre)) $errores[] = "El nombre es requerido";
    if (empty($apellidos)) $errores[] = "Los apellidos son requeridos";
    if (empty($correo)) $errores[] = "El correo es requerido";
    if (empty($password)) $errores[] = "La contraseña es requerida";
    if (empty($nivel_ciclismo)) $errores[] = "El nivel de ciclismo es requerido";
    
    // Validar formato de correo
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El formato del correo no es válido";
    }
    
    // Validar contraseña
    if (strlen($password) < 8) {
        $errores[] = "La contraseña debe tener al menos 8 caracteres";
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errores[] = "La contraseña debe tener al menos una letra mayúscula";
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errores[] = "La contraseña debe tener al menos una letra minúscula";
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errores[] = "La contraseña debe tener al menos un número";
    }
    if (!preg_match('/[!"#$%&\'()*+,\-.\/:\;<=>?@\[\]^_{|}~]/', $password)) {
        $errores[] = "La contraseña debe tener al menos un símbolo especial";
    }
    
    // Validar nivel de ciclismo
    $niveles_permitidos = ['principiante', 'intermedio', 'avanzado'];
    if (!in_array($nivel_ciclismo, $niveles_permitidos)) {
        $errores[] = "El nivel de ciclismo debe ser: principiante, intermedio o avanzado";
    }
    
    // Validar edad si se proporciona
    if ($edad !== null && ($edad < 13 || $edad > 120)) {
        $errores[] = "La edad debe estar entre 13 y 120 años";
    }
    
    // Verificar si el correo ya existe
    if (empty($errores)) {
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE correo = ?");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows > 0) {
            $errores[] = "Ya existe una cuenta con este correo electrónico";
        }
        $stmt->close();
    }
    
    // ===== PROCESAR REGISTRO =====
    if (empty($errores)) {
        // Encriptar contraseña
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        // Rol por defecto será 'cliente'
        $rol_defecto = 'cliente';
        
        // Preparar la consulta según si se proporciona edad o no
        if ($edad !== null) {
            $stmt = $conn->prepare("INSERT INTO usuarios (nombre, correo, password, rol, nivel_ciclismo, edad) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssi", $nombre_completo, $correo, $password_hash, $rol_defecto, $nivel_ciclismo, $edad);
        } else {
            $stmt = $conn->prepare("INSERT INTO usuarios (nombre, correo, password, rol, nivel_ciclismo) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $nombre_completo, $correo, $password_hash, $rol_defecto, $nivel_ciclismo);
        }
        
        if ($stmt->execute()) {
            // Registro exitoso - Redirigir al login
            $stmt->close();
            $conn->close();
            
            // Redirigir con mensaje de éxito
            header("Location: login.php?success=" . urlencode("Cuenta creada exitosamente. Inicia sesión con tus credenciales."));
            exit();
            
        } else {
            // Error al insertar
            $errores[] = "Error al crear la cuenta. Inténtalo nuevamente.";
            $stmt->close();
        }
    }
    
    // ===== SI HAY ERRORES, REGRESAR AL FORMULARIO =====
    if (!empty($errores)) {
        $mensaje_error = implode(". ", $errores);
        header("Location: registro.php?error=" . urlencode($mensaje_error));
        exit();
    }

} else {
    // Si no es POST, redirigir al formulario
    header("Location: registro.php");
    exit();
}

$conn->close();
?>