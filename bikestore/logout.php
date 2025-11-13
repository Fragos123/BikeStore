
<?php
// logout.php - Cierre de sesión seguro con eliminación de tokens
session_start();
include 'conexion.php';

// ===== ELIMINAR TOKEN DE BASE DE DATOS =====
if (isset($_COOKIE['session_token'])) {
    $token = $_COOKIE['session_token'];
    
    // Desactivar el token en la base de datos
    $stmt = $conn->prepare("UPDATE sesiones SET activa = 0 WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->close();
    
    // Eliminar la cookie del navegador
    setcookie('session_token', '', time() - 3600, '/');
}

// ===== ELIMINAR TODAS LAS SESIONES DEL USUARIO (Opcional) =====
// Si quieres cerrar sesión en todos los dispositivos del usuario
if (isset($_SESSION['usuario_id'])) {
    $usuario_id = $_SESSION['usuario_id'];
    $stmt_all = $conn->prepare("UPDATE sesiones SET activa = 0 WHERE usuario_id = ?");
    $stmt_all->bind_param("i", $usuario_id);
    $stmt_all->execute();
    $stmt_all->close();
}

$conn->close();

// ===== DESTRUIR SESIÓN PHP =====
$_SESSION = array();

// Destruir la cookie de sesión PHP si existe
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

session_destroy();

// ===== REDIRIGIR AL INDEX =====
header("Location: index.php?success=" . urlencode("Has cerrado sesión correctamente"));
exit();
?>