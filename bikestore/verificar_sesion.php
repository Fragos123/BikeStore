<?php
// verificar_sesion.php
// Middleware para validar tokens en cada página protegida

session_start();
include_once 'conexion.php';

/**
 * Función principal de verificación de sesión
 * Retorna true si la sesión es válida, false si no lo es
 */
function verificar_sesion_valida() {
    global $conn;
    
    // 1. Verificar que exista sesión PHP básica
    if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
        return false;
    }
    
    // 2. Verificar que exista el token en la cookie
    if (!isset($_COOKIE['session_token'])) {
        return false;
    }
    
    $token = $_COOKIE['session_token'];
    
    // 3. Validar token en la base de datos
    $stmt = $conn->prepare("
        SELECT s.id, s.usuario_id, s.fecha_expiracion, s.ip_address, s.activa,
               u.nombre, u.correo, u.rol, u.nivel_ciclismo, u.edad
        FROM sesiones s
        INNER JOIN usuarios u ON s.usuario_id = u.id
        WHERE s.token = ? AND s.activa = 1
    ");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows === 0) {
        // Token no existe o no está activo
        $stmt->close();
        return false;
    }
    
    $sesion = $resultado->fetch_assoc();
    $stmt->close();
    
    // 4. Verificar que no haya expirado
    $ahora = new DateTime();
    $expiracion = new DateTime($sesion['fecha_expiracion']);
    
    if ($ahora > $expiracion) {
        // Token expirado - desactivar en BD
        $stmt_desactivar = $conn->prepare("UPDATE sesiones SET activa = 0 WHERE token = ?");
        $stmt_desactivar->bind_param("s", $token);
        $stmt_desactivar->execute();
        $stmt_desactivar->close();
        return false;
    }
    
    // 5. Verificar que la IP coincida (opcional pero recomendado)
    $ip_actual = $_SERVER['REMOTE_ADDR'];
    if ($sesion['ip_address'] !== $ip_actual) {
        // IPs diferentes - posible robo de sesión
        // Puedes decidir si esto invalida la sesión o solo registras el evento
        error_log("Advertencia: IP diferente para token. Esperada: {$sesion['ip_address']}, Actual: {$ip_actual}");
        // return false; // Descomenta si quieres invalidar por cambio de IP
    }
    
    // 6. Verificar que el usuario_id de la sesión coincida con el de la BD
    if (isset($_SESSION['usuario_id']) && $_SESSION['usuario_id'] != $sesion['usuario_id']) {
        return false;
    }
    
    // 7. Actualizar datos de sesión con información fresca de la BD
    $_SESSION['usuario_id'] = $sesion['usuario_id'];
    $_SESSION['usuario_nombre'] = $sesion['nombre'];
    $_SESSION['usuario_correo'] = $sesion['correo'];
    $_SESSION['usuario_rol'] = $sesion['rol'];
    $_SESSION['usuario_nivel_ciclismo'] = $sesion['nivel_ciclismo'];
    $_SESSION['usuario_edad'] = $sesion['edad'];
    
    // ✅ Sesión válida
    return true;
}

// ===== EJECUTAR VERIFICACIÓN =====
if (!verificar_sesion_valida()) {
    // Sesión inválida - destruir todo y redirigir al login
    
    // Eliminar cookie
    if (isset($_COOKIE['session_token'])) {
        setcookie('session_token', '', time() - 3600, '/');
    }
    
    // Destruir sesión PHP
    $_SESSION = array();
    session_destroy();
    
    // Redirigir al login con mensaje
    header("Location: login.php?error=" . urlencode("Tu sesión ha expirado o no es válida. Por favor, inicia sesión nuevamente."));
    exit();
}

// Si llegamos aquí, la sesión es válida y el script continúa normalmente
?>