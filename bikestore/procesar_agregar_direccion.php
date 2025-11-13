<?php
session_start();

if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header("Location: login.php");
    exit;
}

include 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $usuario_id = $_SESSION['usuario_id'];
    $nombre_completo = trim($_POST['nombre_completo']);
    $telefono = trim($_POST['telefono']);
    $calle = trim($_POST['calle']);
    $numero_exterior = trim($_POST['numero_exterior']);
    $numero_interior = isset($_POST['numero_interior']) ? trim($_POST['numero_interior']) : null;
    $colonia = trim($_POST['colonia']);
    $ciudad = trim($_POST['ciudad']);
    $estado = trim($_POST['estado']);
    $codigo_postal = trim($_POST['codigo_postal']);
    $referencias = isset($_POST['referencias']) ? trim($_POST['referencias']) : null;
    $es_principal = isset($_POST['es_principal']) ? 1 : 0;
    
    // Validaciones
    $errores = [];
    
    if (empty($nombre_completo) || strlen($nombre_completo) < 3) {
        $errores[] = "El nombre debe tener al menos 3 caracteres";
    }
    
    if (!preg_match('/^\d{10}$/', $telefono)) {
        $errores[] = "El teléfono debe tener 10 dígitos";
    }
    
    if (empty($calle)) {
        $errores[] = "La calle es requerida";
    }
    
    if (empty($numero_exterior)) {
        $errores[] = "El número exterior es requerido";
    }
    
    if (empty($colonia)) {
        $errores[] = "La colonia es requerida";
    }
    
    if (empty($ciudad)) {
        $errores[] = "La ciudad es requerida";
    }
    
    if (empty($estado)) {
        $errores[] = "El estado es requerido";
    }
    
    if (!preg_match('/^\d{5}$/', $codigo_postal)) {
        $errores[] = "El código postal debe tener 5 dígitos";
    }
    
    if (!empty($errores)) {
        $conn->close();
        $mensaje_error = implode(". ", $errores);
        header("Location: agregar_direccion.php?error=" . urlencode($mensaje_error));
        exit;
    }
    
    // Si se marca como principal, desmarcar las demás
    if ($es_principal == 1) {
        $stmt = $conn->prepare("UPDATE direcciones SET es_principal = 0 WHERE usuario_id = ?");
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $stmt->close();
    }
    
    // Insertar la dirección
    $stmt = $conn->prepare("INSERT INTO direcciones (usuario_id, nombre_completo, telefono, calle, numero_exterior, numero_interior, colonia, ciudad, estado, codigo_postal, referencias, es_principal) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param("issssssssssi", 
        $usuario_id,
        $nombre_completo,
        $telefono,
        $calle,
        $numero_exterior,
        $numero_interior,
        $colonia,
        $ciudad,
        $estado,
        $codigo_postal,
        $referencias,
        $es_principal
    );
    
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        header("Location: perfil.php?success=" . urlencode("Dirección agregada exitosamente"));
        exit;
    } else {
        $stmt->close();
        $conn->close();
        header("Location: agregar_direccion.php?error=" . urlencode("Error al agregar la dirección"));
        exit;
    }
    
} else {
    header("Location: agregar_direccion.php");
    exit;
}
?>