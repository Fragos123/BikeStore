<?php
session_start();
//procesar_admin_crear_usuario.php
// Verificar si el usuario está logueado y es admin
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true || $_SESSION['usuario_rol'] !== 'admin') {
    header("Location: login.php");
    exit;
}

include 'conexion.php';

// Verificar si el formulario fue enviado por POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $nombre = trim($_POST['nombre']);
    $correo = trim($_POST['correo']);
    $password = $_POST['password'];
    $rol = trim($_POST['rol']);
    $nivel_ciclismo = trim($_POST['nivel_ciclismo']);
    $edad = !empty($_POST['edad']) ? (int)$_POST['edad'] : null;
    
    // Validaciones
    $errores = array();
    
    if (empty($nombre)) $errores[] = "El nombre es requerido";
    if (empty($correo)) $errores[] = "El correo es requerido";
    if (empty($password)) $errores[] = "La contraseña es requerida";
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
            $errores[] = "Ya existe un usuario con este correo electrónico";
        }
        $stmt->close();
    }
    
    // Crear usuario
    if (empty($errores)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        if ($edad !== null) {
            $stmt = $conn->prepare("INSERT INTO usuarios (nombre, correo, password, rol, nivel_ciclismo, edad) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssi", $nombre, $correo, $password_hash, $rol, $nivel_ciclismo, $edad);
        } else {
            $stmt = $conn->prepare("INSERT INTO usuarios (nombre, correo, password, rol, nivel_ciclismo) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $nombre, $correo, $password_hash, $rol, $nivel_ciclismo);
        }
        
        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            
            header("Location: principal_admin.php?success=" . urlencode("Usuario '$nombre' creado exitosamente"));
            exit();
        } else {
            $errores[] = "Error al crear el usuario";
            $stmt->close();
        }
    }
    
    if (!empty($errores)) {
        $conn->close();
        $mensaje_error = implode(". ", $errores);
        header("Location: admin_crear_usuario.php?error=" . urlencode($mensaje_error));
        exit();
    }
    
} else {
    header("Location: admin_crear_usuario.php");
    exit();
}

$conn->close();
?>