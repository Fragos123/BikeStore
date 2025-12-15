<?php 
session_start(); 
include 'conexion.php';
//procesar_registro.php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $nombre = trim($_POST['nombre']);
    $apellidos = trim($_POST['apellidos']);
    $correo = trim($_POST['correo']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password']; // NUEVO
    $nivel_ciclismo = trim($_POST['nivel_ciclismo']);
    $edad = !empty($_POST['edad']) ? (int)$_POST['edad'] : null;
    
    $nombre_completo = $nombre . ' ' . $apellidos;
    $errores = array();
    
    // Validaciones básicas
    if (empty($nombre) || empty($apellidos) || empty($correo)) $errores[] = "Faltan datos obligatorios.";
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) $errores[] = "Correo inválido.";
    
    // Validar contraseña fuerte
    if (strlen($password) < 8 || 
        !preg_match('/[A-Z]/', $password) || 
        !preg_match('/[a-z]/', $password) || 
        !preg_match('/[0-9]/', $password)) {
        $errores[] = "La contraseña es muy débil (Mín 8 car., Mayús, minús, número).";
    }

    // NUEVO: Validar coincidencia
    if ($password !== $confirm_password) {
        $errores[] = "Las contraseñas no coinciden.";
    }
    
    // Verificar si correo existe
    if (empty($errores)) {
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE correo = ?");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errores[] = "Este correo ya está registrado.";
        }
        $stmt->close();
    }
    
    // Insertar
    if (empty($errores)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $rol_defecto = 'cliente';
        
        $sql = "INSERT INTO usuarios (nombre, correo, password, rol, nivel_ciclismo, edad) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", $nombre_completo, $correo, $password_hash, $rol_defecto, $nivel_ciclismo, $edad);
        
        if ($stmt->execute()) {
            // Enviar correo de bienvenida (Simulado/Real)
            $para = $correo;
            $titulo = '¡Bienvenido a BikeStore!';
            $mensaje = "Hola " . $nombre . ", bienvenido a nuestra comunidad de ciclistas.";
            $cabeceras = 'From: no-reply@bikestore.com';
            @mail($para, $titulo, $mensaje, $cabeceras);

            $stmt->close();
            $conn->close();
            
            header("Location: login.php?success=" . urlencode("¡Cuenta creada! Inicia sesión ahora."));
            exit();
        } else {
            $errores[] = "Error en la base de datos al crear usuario.";
        }
        $stmt->close();
    }
    
    // Si hubo errores
    if (!empty($errores)) {
        $conn->close();
        header("Location: registro.php?error=" . urlencode(implode(" ", $errores)));
        exit();
    }

} else {
    header("Location: registro.php");
    exit();
}
?>