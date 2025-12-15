<?php
session_start();
include 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = trim($_POST['correo']);

    if (empty($correo) || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        header("Location: olvide_password.php?error=" . urlencode("Correo inválido"));
        exit;
    }

    // Verificar si el correo existe
    $stmt = $conn->prepare("SELECT id, nombre FROM usuarios WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $usuario = $result->fetch_assoc();
        
        // Generar token único y fecha de expiración (1 hora)
        $token = bin2hex(random_bytes(32));
        $expiracion = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Guardar token en la base de datos
        $update = $conn->prepare("UPDATE usuarios SET reset_token = ?, reset_token_exp = ? WHERE id = ?");
        $update->bind_param("ssi", $token, $expiracion, $usuario['id']);
        
        if ($update->execute()) {
            // --- SIMULACIÓN DE ENVÍO DE CORREO ---
            // En un servidor real usarías mail() o PHPMailer.
            // Aquí creamos el enlace para que puedas probarlo localmente.
            
            $enlace = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/restablecer_password.php?token=" . $token;
            
            // Para propósitos de prueba, mostramos el enlace en pantalla o lo enviamos por mail()
            // Intentamos enviar mail real si está configurado:
            $para = $correo;
            $titulo = 'Recuperación de Contraseña - BikeStore';
            $mensaje = "Hola " . $usuario['nombre'] . ",\n\n";
            $mensaje .= "Has solicitado restablecer tu contraseña. Haz clic en el siguiente enlace:\n";
            $mensaje .= $enlace . "\n\n";
            $mensaje .= "Este enlace expira en 1 hora.\n";
            $headers = 'From: no-reply@bikestore.com';
            
            @mail($para, $titulo, $mensaje, $headers);

            // IMPORTANTE: Como es localhost, redirigimos con éxito PERO
            // en un caso real no mostrarías el link en la URL. 
            // Aquí lo imprimimos en un mensaje de éxito para que puedas probarlo tú mismo.
            
            $msg = "Se ha enviado un correo. (MODO PRUEBA: <a href='$enlace' style='color:white;text-decoration:underline;'>Click aquí para restablecer</a>)";
            header("Location: olvide_password.php?success=" . urlencode($msg));
            exit;
        } else {
            header("Location: olvide_password.php?error=" . urlencode("Error al procesar la solicitud"));
            exit;
        }
        $update->close();
    } else {
        // Por seguridad, no decimos si el correo existe o no, pero mostramos éxito igual
        header("Location: olvide_password.php?success=" . urlencode("Si el correo existe, recibirás instrucciones."));
        exit;
    }
    $stmt->close();
} else {
    header("Location: olvide_password.php");
}
$conn->close();
?>