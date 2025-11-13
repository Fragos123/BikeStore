<?php
session_start();

if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header("Location: login.php");
    exit;
}

include 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $usuario_id = $_SESSION['usuario_id'];
    $tipo = trim($_POST['tipo']);
    $nombre_titular = trim($_POST['nombre_titular']);
    $numero_tarjeta = str_replace(' ', '', trim($_POST['numero_tarjeta']));
    $mes_expiracion = (int)$_POST['mes_expiracion'];
    $ano_expiracion = (int)$_POST['ano_expiracion'];
    $cvv = trim($_POST['cvv']);
    $es_principal = isset($_POST['es_principal']) ? 1 : 0;
    
    // Validaciones
    $errores = [];
    
    $tipos_permitidos = ['tarjeta_credito', 'tarjeta_debito'];
    if (!in_array($tipo, $tipos_permitidos)) {
        $errores[] = "Tipo de tarjeta no válido";
    }
    
    if (empty($nombre_titular) || strlen($nombre_titular) < 3) {
        $errores[] = "El nombre del titular debe tener al menos 3 caracteres";
    }
    
    if (!preg_match('/^\d{16}$/', $numero_tarjeta)) {
        $errores[] = "El número de tarjeta debe tener 16 dígitos";
    }
    
    if ($mes_expiracion < 1 || $mes_expiracion > 12) {
        $errores[] = "Mes de expiración no válido";
    }
    
    $ano_actual = date('Y');
    if ($ano_expiracion < $ano_actual || $ano_expiracion > ($ano_actual + 20)) {
        $errores[] = "Año de expiración no válido";
    }
    
    // Validar que no esté vencida
    $fecha_actual = new DateTime();
    $mes_actual = (int)$fecha_actual->format('n');
    if ($ano_expiracion == $ano_actual && $mes_expiracion < $mes_actual) {
        $errores[] = "La tarjeta está vencida";
    }
    
    if (!preg_match('/^\d{3,4}$/', $cvv)) {
        $errores[] = "El CVV debe tener 3 o 4 dígitos";
    }
    
    if (!empty($errores)) {
        $conn->close();
        $mensaje_error = implode(". ", $errores);
        header("Location: agregar_metodo_pago.php?error=" . urlencode($mensaje_error));
        exit;
    }
    
    // Obtener últimos 4 dígitos y detectar marca
    $ultimos_digitos = substr($numero_tarjeta, -4);
    $primer_digito = substr($numero_tarjeta, 0, 1);
    
    // Detectar marca de tarjeta
    $marca = 'Desconocida';
    if ($primer_digito == '4') {
        $marca = 'Visa';
    } elseif ($primer_digito == '5') {
        $marca = 'Mastercard';
    } elseif ($primer_digito == '3') {
        $marca = 'American Express';
    }
    
    // Si se marca como principal, desmarcar las demás
    if ($es_principal == 1) {
        $stmt = $conn->prepare("UPDATE metodos_pago SET es_principal = 0 WHERE usuario_id = ?");
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $stmt->close();
    }
    
    // Insertar método de pago (NO guardamos el número completo ni el CVV)
    $stmt = $conn->prepare("INSERT INTO metodos_pago (usuario_id, tipo, nombre_titular, ultimos_digitos, marca, mes_expiracion, ano_expiracion, es_principal) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param("isssssii", 
        $usuario_id,
        $tipo,
        $nombre_titular,
        $ultimos_digitos,
        $marca,
        $mes_expiracion,
        $ano_expiracion,
        $es_principal
    );
    
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        header("Location: perfil.php?success=" . urlencode("Método de pago agregado exitosamente"));
        exit;
    } else {
        $stmt->close();
        $conn->close();
        header("Location: agregar_metodo_pago.php?error=" . urlencode("Error al agregar el método de pago"));
        exit;
    }
    
} else {
    header("Location: agregar_metodo_pago.php");
    exit;
}
?>