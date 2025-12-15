
<?php
session_start();
//operador_agregar_producto.php
// Verificar si el usuario está logueado y es operador
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true || $_SESSION['usuario_rol'] !== 'operador') {
    header("Location: login.php");
    exit;
}

include 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $nombre = trim($_POST['nombre']);
    $tipo = trim($_POST['tipo']);
    $peso = !empty($_POST['peso']) ? (float)$_POST['peso'] : null;
    $velocidades = !empty($_POST['velocidades']) ? (int)$_POST['velocidades'] : null;
    $precio = (float)$_POST['precio'];
    $imagen = trim($_POST['imagen']);
    $descripcion = trim($_POST['descripcion']);
    
    // Validaciones
    $errores = array();
    
    if (empty($nombre)) $errores[] = "El nombre es requerido";
    if (empty($tipo)) $errores[] = "El tipo es requerido";
    if ($precio <= 0) $errores[] = "El precio debe ser mayor a 0";
    
    // Validar tipo
    $tipos_permitidos = ['montaña', 'ruta', 'urbana', 'eléctrica'];
    if (!in_array($tipo, $tipos_permitidos)) {
        $errores[] = "El tipo de bicicleta no es válido";
    }
    
    if (empty($errores)) {
        // Preparar consulta según los campos proporcionados
        if ($peso !== null && $velocidades !== null && !empty($imagen) && !empty($descripcion)) {
            $stmt = $conn->prepare("INSERT INTO bicicletas (nombre, tipo, peso, velocidades, precio, imagen, descripcion) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssdidss", $nombre, $tipo, $peso, $velocidades, $precio, $imagen, $descripcion);
        } elseif ($peso !== null && $velocidades !== null && !empty($imagen)) {
            $stmt = $conn->prepare("INSERT INTO bicicletas (nombre, tipo, peso, velocidades, precio, imagen) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssdids", $nombre, $tipo, $peso, $velocidades, $precio, $imagen);
        } elseif ($peso !== null && $velocidades !== null) {
            $stmt = $conn->prepare("INSERT INTO bicicletas (nombre, tipo, peso, velocidades, precio) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssdid", $nombre, $tipo, $peso, $velocidades, $precio);
        } else {
            $stmt = $conn->prepare("INSERT INTO bicicletas (nombre, tipo, precio) VALUES (?, ?, ?)");
            $stmt->bind_param("ssd", $nombre, $tipo, $precio);
        }
        
        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            header("Location: principal_operador.php?success=Producto agregado exitosamente");
            exit();
        } else {
            $errores[] = "Error al agregar el producto";
            $stmt->close();
        }
    }
    
    if (!empty($errores)) {
        $conn->close();
        $mensaje_error = implode(". ", $errores);
        header("Location: principal_operador.php?error=" . urlencode($mensaje_error));
        exit();
    }
    
} else {
    header("Location: principal_operador.php");
    exit();
}

$conn->close();
?>