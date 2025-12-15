<?php
session_start();
// 1. SEGURIDAD
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true || 
    !in_array($_SESSION['usuario_rol'], ['operador', 'admin'])) {
    header("Location: login.php");
    exit;
}

include 'conexion.php';

// Definir redirección según rol
$redirect_success = ($_SESSION['usuario_rol'] === 'admin') ? 'lista_productos.php' : 'principal_operador.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Recolección de datos
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $tipo = trim($_POST['tipo']);
    $precio = (float)$_POST['precio'];
    $nivel_ciclismo = trim($_POST['nivel_ciclismo']);
    $dias_envio = (int)$_POST['dias_envio'];
    $peso = !empty($_POST['peso']) ? (float)$_POST['peso'] : 0;
    $velocidades = !empty($_POST['velocidades']) ? (int)$_POST['velocidades'] : 0;
    
    $imagen_principal = trim($_POST['imagen_principal']);
    $imagen_2 = !empty($_POST['imagen_2']) ? trim($_POST['imagen_2']) : '';
    $imagen_3 = !empty($_POST['imagen_3']) ? trim($_POST['imagen_3']) : '';
    $imagen_4 = !empty($_POST['imagen_4']) ? trim($_POST['imagen_4']) : '';
    $imagen_5 = !empty($_POST['imagen_5']) ? trim($_POST['imagen_5']) : '';
    $imagen_6 = !empty($_POST['imagen_6']) ? trim($_POST['imagen_6']) : '';
    
    $tallas_seleccionadas = isset($_POST['tallas']) ? $_POST['tallas'] : [];
    
    // --- VALIDACIONES DE SEGURIDAD ---
    $errores = array();
    
    if (empty($nombre) || strlen($nombre) < 3) $errores[] = "Nombre muy corto.";
    if ($precio <= 0) $errores[] = "El precio debe ser mayor a 0.";
    if ($precio > 1000000) $errores[] = "El precio excede el límite permitido.";
    if (empty($imagen_principal)) $errores[] = "Falta la imagen principal.";
    
    $tallas_validas = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
    $tallas_con_stock = [];
    $stock_total_general = 0;

    if (empty($tallas_seleccionadas)) {
        $errores[] = "Selecciona al menos una talla.";
    } else {
        foreach ($tallas_seleccionadas as $talla) {
            if (!in_array($talla, $tallas_validas)) continue;
            
            $stock = isset($_POST['stock_' . $talla]) ? (int)$_POST['stock_' . $talla] : 0;
            
            if ($stock > 50) {
                $errores[] = "El stock para la talla $talla no puede superar 50 unidades."; 
            } elseif ($stock >= 0) {
                $tallas_con_stock[$talla] = $stock;
                $stock_total_general += $stock;
            } else {
                $errores[] = "El stock no puede ser negativo.";
            }
        }
        
        if ($stock_total_general <= 0) {
            $errores[] = "El producto debe tener al menos 1 unidad de stock en total.";
        }
    }

    if (empty($errores)) {
        $conn->begin_transaction();
        try {
            $primera_talla = array_key_first($tallas_con_stock);
            
            $sql = "INSERT INTO productos (nombre, tipo, talla, nivel_ciclismo, peso, velocidades, precio, imagen, imagen_principal, imagen_2, imagen_3, imagen_4, imagen_5, imagen_6, stock, dias_envio, descripcion, fecha_creacion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $conn->prepare($sql);
            
            // CORRECCIÓN EXACTA DE TIPOS (17 caracteres)
            $stmt->bind_param("ssssdidsssssssiis", 
                $nombre,            
                $tipo,              
                $primera_talla,     
                $nivel_ciclismo,    
                $peso,              
                $velocidades,       
                $precio,            
                $imagen_principal,  
                $imagen_principal,  
                $imagen_2,          
                $imagen_3,          
                $imagen_4,          
                $imagen_5,          
                $imagen_6,          
                $stock_total_general, 
                $dias_envio,        
                $descripcion        
            );
            
            if (!$stmt->execute()) throw new Exception("Error al insertar: " . $stmt->error);
            
            $producto_id = $conn->insert_id;
            $stmt->close();
            
            $stmt_talla = $conn->prepare("INSERT INTO producto_tallas (producto_id, talla, stock, activo) VALUES (?, ?, ?, 1)");
            foreach ($tallas_con_stock as $talla => $stock) {
                $stmt_talla->bind_param("isi", $producto_id, $talla, $stock);
                if (!$stmt_talla->execute()) throw new Exception("Error al guardar talla $talla");
            }
            $stmt_talla->close();
            
            $conn->commit();
            header("Location: $redirect_success?success=" . urlencode("Producto creado correctamente."));
            exit;
            
        } catch (Exception $e) {
            $conn->rollback();
            $errores[] = $e->getMessage();
        }
    }
    
    if (!empty($errores)) {
        $conn->close();
        header("Location: operador_crear_producto.php?error=" . urlencode(implode(" ", $errores)));
        exit;
    }
    
} else {
    header("Location: operador_crear_producto.php");
    exit;
}
?>