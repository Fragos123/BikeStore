<?php
session_start();
//procesar_agregar_metodo_pago.php
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header("Location: login.php");
    exit;
}

include 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $usuario_id = $_SESSION['usuario_id'];
    $origen = isset($_POST['origen']) ? $_POST['origen'] : '';
    
    // ... (Obtención de datos igual) ...
    $tipo = trim($_POST['tipo']);
    $nombre_titular = trim($_POST['nombre_titular']);
    $numero_tarjeta = str_replace(' ', '', trim($_POST['numero_tarjeta']));
    $mes_expiracion = (int)$_POST['mes_expiracion'];
    $ano_expiracion = (int)$_POST['ano_expiracion'];
    $cvv = trim($_POST['cvv']);
    $es_principal = isset($_POST['es_principal']) ? 1 : 0;
    
    // Validaciones
    $errores = [];
    if (empty($nombre_titular)) $errores[] = "Falta nombre titular";
    if (!preg_match('/^\d{16}$/', $numero_tarjeta)) $errores[] = "Número tarjeta inválido";
    
    if (!empty($errores)) {
        $conn->close();
        $url_error = "agregar_metodo_pago.php?error=" . urlencode(implode(". ", $errores));
        if ($origen === 'checkout') $url_error .= "&origen=checkout";
        header("Location: " . $url_error);
        exit;
    }
    
    // Procesar tarjeta (solo últimos 4 dígitos)
    $ultimos_digitos = substr($numero_tarjeta, -4);
    $primer_digito = substr($numero_tarjeta, 0, 1);
    $marca = 'Desconocida';
    if ($primer_digito == '4') $marca = 'Visa';
    elseif ($primer_digito == '5') $marca = 'Mastercard';
    elseif ($primer_digito == '3') $marca = 'Amex';
    
    // Quitar principal a otras
    if ($es_principal == 1) {
        $stmt = $conn->prepare("UPDATE metodos_pago SET es_principal = 0 WHERE usuario_id = ?");
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $stmt->close();
    }
    
    // Insertar
    $stmt = $conn->prepare("INSERT INTO metodos_pago (usuario_id, tipo, nombre_titular, ultimos_digitos, marca, mes_expiracion, ano_expiracion, es_principal) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssssii", $usuario_id, $tipo, $nombre_titular, $ultimos_digitos, $marca, $mes_expiracion, $ano_expiracion, $es_principal);
    
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        
        // --- REDIRECCIÓN INTELIGENTE ---
        if ($origen === 'checkout') {
            header("Location: checkout.php");
        } else {
            header("Location: perfil.php?success=" . urlencode("Método de pago agregado"));
        }
        exit;
    } else {
        $stmt->close();
        $conn->close();
        $url_error = "agregar_metodo_pago.php?error=" . urlencode("Error al guardar");
        if ($origen === 'checkout') $url_error .= "&origen=checkout";
        header("Location: " . $url_error);
        exit;
    }
} else {
    header("Location: agregar_metodo_pago.php");
    exit;
}
?>