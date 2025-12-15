<?php
session_start();
include 'conexion.php';

// 1. VERIFICAR SESIÓN
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    $back_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';
    header("Location: login.php?error=" . urlencode("Debes iniciar sesión para comprar") . "&redirect=" . urlencode($back_url));
    exit;
}

// 2. VERIFICAR MÉTODO POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: productos.php");
    exit;
}

// 3. OBTENER DATOS
$usuario_id = $_SESSION['usuario_id'];
$producto_id = isset($_POST['producto_id']) ? (int)$_POST['producto_id'] : 0;
$cantidad = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 1;
$talla_elegida = isset($_POST['talla']) ? trim($_POST['talla']) : '';

// 4. VALIDACIONES BÁSICAS
$errores = [];
if ($producto_id <= 0) $errores[] = "Producto inválido.";
if ($cantidad <= 0) $errores[] = "La cantidad debe ser mayor a 0.";
if (empty($talla_elegida)) $errores[] = "Debes seleccionar una talla.";

if (!empty($errores)) {
    redirigir_error($producto_id, implode(" ", $errores));
}

// 5. OBTENER STOCK REAL (Estrategia PHP-Side para evitar errores de Collation)
$stock_disponible = 0;
$nombre_producto = "";
$precio_real = 0;
$talla_encontrada_en_bd = false;

// A. Obtener datos generales del producto
$stmt = $conn->prepare("SELECT nombre, precio, stock FROM productos WHERE id = ?");
$stmt->bind_param("i", $producto_id);
$stmt->execute();
$res_prod = $stmt->get_result();

if ($prod = $res_prod->fetch_assoc()) {
    $nombre_producto = $prod['nombre'];
    $precio_real = $prod['precio'];
    $stock_general = $prod['stock']; // Stock fallback
} else {
    redirigir_error($producto_id, "El producto no existe.");
}
$stmt->close();

// B. Obtener TODAS las tallas del producto y buscar manualmente en PHP
// Esto evita que MySQL falle al comparar textos con diferente codificación
$stmt = $conn->prepare("SELECT talla, stock FROM producto_tallas WHERE producto_id = ?");
$stmt->bind_param("i", $producto_id);
$stmt->execute();
$res_tallas = $stmt->get_result();

$tiene_tallas_definidas = false;

while ($fila = $res_tallas->fetch_assoc()) {
    $tiene_tallas_definidas = true;
    // Comparamos en PHP (strcasecmp es insensible a mayúsculas/minúsculas)
    // Trim para quitar espacios invisibles que puedan causar el fallo
    if (strcasecmp(trim($fila['talla']), trim($talla_elegida)) === 0) {
        $stock_disponible = $fila['stock'];
        $talla_encontrada_en_bd = true;
        // IMPORTANTE: Normalizamos la talla al valor exacto de la BD para guardarla bien
        $talla_elegida = $fila['talla']; 
        break;
    }
}
$stmt->close();

// C. Determinar qué stock usar y validar si existe la talla
if (!$tiene_tallas_definidas) {
    // Si el producto no usa tabla de tallas (stock -1 en productos), usamos el general
    $stock_disponible = $stock_general;
} elseif (!$talla_encontrada_en_bd) {
    // Tiene tallas definidas, pero la que elegiste no coincide con ninguna
    redirigir_error($producto_id, "La talla seleccionada ($talla_elegida) no está disponible en el sistema.");
}

// 6. VALIDAR STOCK DISPONIBLE
if ($stock_disponible < $cantidad) {
    redirigir_error($producto_id, "Stock insuficiente. Solo quedan $stock_disponible unidades.");
}

// 7. ACTUALIZAR CARRITO
// Verificar si ya existe (Usamos consulta simple, PHP maneja la lógica)
// Buscamos manualmente en el carrito también para asegurar compatibilidad
$stmt = $conn->prepare("SELECT id, cantidad, talla FROM carrito WHERE usuario_id = ? AND producto_id = ?");
$stmt->bind_param("ii", $usuario_id, $producto_id);
$stmt->execute();
$res_carrito = $stmt->get_result();

$item_id = 0;
$cantidad_actual_carrito = 0;
$encontrado_en_carrito = false;

while ($row = $res_carrito->fetch_assoc()) {
    if (strcasecmp(trim($row['talla']), trim($talla_elegida)) === 0) {
        $item_id = $row['id'];
        $cantidad_actual_carrito = $row['cantidad'];
        $encontrado_en_carrito = true;
        break;
    }
}
$stmt->close();

if ($encontrado_en_carrito) {
    // === ACTUALIZAR ===
    $nueva_cantidad = $cantidad_actual_carrito + $cantidad;
    
    if ($nueva_cantidad > $stock_disponible) {
        redirigir_error($producto_id, "No puedes agregar más. Ya tienes $cantidad_actual_carrito en el carrito y el límite es $stock_disponible.");
    }

    $stmt_upd = $conn->prepare("UPDATE carrito SET cantidad = ? WHERE id = ?");
    $stmt_upd->bind_param("ii", $nueva_cantidad, $item_id);
    
    if ($stmt_upd->execute()) {
        header("Location: carrito.php?success=" . urlencode("Carrito actualizado correctamente"));
    } else {
        redirigir_error($producto_id, "Error al actualizar carrito.");
    }
    $stmt_upd->close();

} else {
    // === INSERTAR ===
    $stmt_ins = $conn->prepare("INSERT INTO carrito (usuario_id, producto_id, cantidad, talla, precio_unitario) VALUES (?, ?, ?, ?, ?)");
    $stmt_ins->bind_param("iiisd", $usuario_id, $producto_id, $cantidad, $talla_elegida, $precio_real);
    
    if ($stmt_ins->execute()) {
        header("Location: carrito.php?success=" . urlencode("Producto agregado al carrito"));
    } else {
        redirigir_error($producto_id, "Error al agregar al carrito: " . $stmt_ins->error);
    }
    $stmt_ins->close();
}

$conn->close();

// Función auxiliar
function redirigir_error($pid, $msg) {
    global $conn;
    $conn->close();
    header("Location: producto_detalle.php?id=$pid&error=" . urlencode($msg));
    exit;
}
?>