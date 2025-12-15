<?php
session_start();
//procesar_agregar_direccion.php
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header("Location: login.php");
    exit;
}

include 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $usuario_id = $_SESSION['usuario_id'];
    $origen = isset($_POST['origen']) ? $_POST['origen'] : '';
    
    // ... (Obtención de datos igual) ...
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
    if (empty($nombre_completo)) $errores[] = "El nombre es requerido";
    if (!preg_match('/^\d{10}$/', $telefono)) $errores[] = "Teléfono de 10 dígitos requerido";
    if (empty($calle) || empty($colonia) || empty($ciudad) || empty($estado)) $errores[] = "Faltan datos de dirección";
    
    if (!empty($errores)) {
        $conn->close();
        $url_error = "agregar_direccion.php?error=" . urlencode(implode(". ", $errores));
        if ($origen === 'checkout') $url_error .= "&origen=checkout";
        header("Location: " . $url_error);
        exit;
    }
    
    // Si es principal, quitar principal a las otras
    if ($es_principal == 1) {
        $stmt = $conn->prepare("UPDATE direcciones SET es_principal = 0 WHERE usuario_id = ?");
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $stmt->close();
    }
    
    // Insertar
    $stmt = $conn->prepare("INSERT INTO direcciones (usuario_id, nombre_completo, telefono, calle, numero_exterior, numero_interior, colonia, ciudad, estado, codigo_postal, referencias, es_principal) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssssssssi", $usuario_id, $nombre_completo, $telefono, $calle, $numero_exterior, $numero_interior, $colonia, $ciudad, $estado, $codigo_postal, $referencias, $es_principal);
    
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        
        // --- REDIRECCIÓN INTELIGENTE ---
        if ($origen === 'checkout') {
            header("Location: checkout.php");
        } else {
            header("Location: perfil.php?success=" . urlencode("Dirección agregada exitosamente"));
        }
        exit;
    } else {
        $stmt->close();
        $conn->close();
        $url_error = "agregar_direccion.php?error=" . urlencode("Error al guardar");
        if ($origen === 'checkout') $url_error .= "&origen=checkout";
        header("Location: " . $url_error);
        exit;
    }
} else {
    header("Location: agregar_direccion.php");
    exit;
}
?>