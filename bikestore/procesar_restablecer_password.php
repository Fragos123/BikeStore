<?php
session_start();
include 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_POST['token'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validaciones
    if (empty($token) || empty($password) || empty($confirm_password)) {
        header("Location: restablecer_password.php?token=$token&error=" . urlencode("Todos los campos son obligatorios"));
        exit;
    }

    if ($password !== $confirm_password) {
        header("Location: restablecer_password.php?token=$token&error=" . urlencode("Las contraseñas no coinciden"));
        exit;
    }

    if (strlen($password) < 8) {
        header("Location: restablecer_password.php?token=$token&error=" . urlencode("La contraseña debe tener al menos 8 caracteres"));
        exit;
    }

    // Verificar token nuevamente
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE reset_token = ? AND reset_token_exp > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $usuario = $result->fetch_assoc();
        $usuario_id = $usuario['id'];
        
        // Encriptar nueva contraseña
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Actualizar contraseña y limpiar token
        $update = $conn->prepare("UPDATE usuarios SET password = ?, reset_token = NULL, reset_token_exp = NULL WHERE id = ?");
        $update->bind_param("si", $password_hash, $usuario_id);
        
        if ($update->execute()) {
            $update->close();
            $conn->close();
            // Redirigir al login con éxito
            header("Location: login.php?success=" . urlencode("Contraseña restablecida correctamente. Inicia sesión ahora."));
            exit;
        } else {
            header("Location: restablecer_password.php?token=$token&error=" . urlencode("Error al actualizar la contraseña"));
            exit;
        }
    } else {
        header("Location: restablecer_password.php?token=$token&error=" . urlencode("El enlace ha expirado o no es válido"));
        exit;
    }
    $stmt->close();
} else {
    header("Location: login.php");
}
$conn->close();
?>