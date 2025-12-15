<?php 
session_start(); 
include 'conexion.php';

// Verificar si el formulario fue enviado por POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // ===== OBTENER Y LIMPIAR DATOS =====
    $correo = trim($_POST['correo']);
    $password = $_POST['password'];
    
    // ===== VALIDACIONES DEL SERVIDOR =====
    $errores = array();
    
    // Validar campos requeridos
    if (empty($correo)) {
        $errores[] = "El correo es requerido";
    }
    
    if (empty($password)) {
        $errores[] = "La contraseña es requerida";
    }
    
    // Validar formato de correo
    if (!empty($correo) && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El formato del correo no es válido";
    }
    
    // ===== PROCESAR LOGIN =====
    if (empty($errores)) {
        // Buscar usuario por correo
        $stmt = $conn->prepare("SELECT id, nombre, correo, password, rol, nivel_ciclismo, edad FROM usuarios WHERE correo = ?");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows === 1) {
            // Usuario encontrado
            $usuario = $resultado->fetch_assoc();
            
            // Verificar contraseña
            if (password_verify($password, $usuario['password'])) {
                // ===== LOGIN EXITOSO =====
                
                // Regenerar ID de sesión para seguridad
                session_regenerate_id(true);
                
                // Guardar datos en la sesión
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'];
                $_SESSION['usuario_correo'] = $usuario['correo'];
                $_SESSION['usuario_rol'] = $usuario['rol'];
                $_SESSION['usuario_nivel_ciclismo'] = $usuario['nivel_ciclismo'];
                $_SESSION['usuario_edad'] = $usuario['edad'];
                $_SESSION['logueado'] = true;
                $_SESSION['tiempo_login'] = time();
                
                // ===== GENERAR TOKEN ÚNICO =====
                $token = bin2hex(random_bytes(32)); // Token de 64 caracteres
                
                // Obtener datos del cliente
                $ip_address = $_SERVER['REMOTE_ADDR'];
                $user_agent = $_SERVER['HTTP_USER_AGENT'];
                
                // Calcular fecha de expiración (24 horas desde ahora)
                $fecha_expiracion = date('Y-m-d H:i:s', strtotime('+24 hours'));
                
                // ===== GUARDAR TOKEN EN BASE DE DATOS =====
                $stmt_token = $conn->prepare("INSERT INTO sesiones (usuario_id, token, fecha_expiracion, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
                $stmt_token->bind_param("issss", $usuario['id'], $token, $fecha_expiracion, $ip_address, $user_agent);
                
                if ($stmt_token->execute()) {
                    // ===== CREAR COOKIE SEGURA =====
                    $cookie_options = array(
                        'expires' => time() + (24 * 60 * 60), // 24 horas
                        'path' => '/',
                        'domain' => '', // Dejar vacío para localhost
                        'secure' => false, // En producción cambiar a true (requiere HTTPS)
                        'httponly' => true, // No accesible desde JavaScript
                        'samesite' => 'Strict' // Protección CSRF
                    );
                    
                    setcookie('session_token', $token, $cookie_options);
                    
                    // Guardar token también en sesión PHP
                    $_SESSION['session_token'] = $token;
                    
                    $stmt_token->close();
                } else {
                    $errores[] = "Error al crear la sesión segura";
                }
                
                // Cerrar statement
                $stmt->close();
                $conn->close();
                
                // Si no hubo errores, redirigir
                if (empty($errores)) {
                    // Redirigir según el rol del usuario
                    if ($usuario['rol'] === 'admin') {
                        header("Location: principal_admin.php");
                        exit();
                    } elseif ($usuario['rol'] === 'operador') {
                        header("Location: principal_operador.php");
                        exit();
                    } else {
                        // Cliente o rol por defecto
                        header("Location: index.php");
                        exit();
                    }
                }
                
            } else {
                // Contraseña incorrecta
                $errores[] = "Correo o contraseña incorrectos";
            }
            
        } else {
            // Usuario no encontrado
            $errores[] = "Correo o contraseña incorrectos";
        }
        
        $stmt->close();
    }
    
    // ===== SI HAY ERRORES, REGRESAR AL LOGIN =====
    if (!empty($errores)) {
        $conn->close();
        $mensaje_error = implode(". ", $errores);
        header("Location: login.php?error=" . urlencode($mensaje_error));
        exit();
    }

} else {
    // Si no es POST, redirigir al formulario
    header("Location: login.php");
    exit();
}

$conn->close();
?>