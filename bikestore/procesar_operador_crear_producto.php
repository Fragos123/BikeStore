<?php
session_start();

// Verificar si el usuario está logueado y es operador
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true || $_SESSION['usuario_rol'] !== 'operador') {
    header("Location: login.php");
    exit;
}

include 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $nombre = trim($_POST['nombre']);
    $tipo = trim($_POST['tipo']);
    $nivel_ciclismo = trim($_POST['nivel_ciclismo']);
    $peso = !empty($_POST['peso']) ? (float)$_POST['peso'] : null;
    $velocidades = !empty($_POST['velocidades']) ? (int)$_POST['velocidades'] : null;
    $precio = (float)$_POST['precio'];
    $imagen = trim($_POST['imagen_principal']);
    $imagen_principal = trim($_POST['imagen_principal']);
    $imagen_2 = !empty($_POST['imagen_2']) ? trim($_POST['imagen_2']) : null;
    $imagen_3 = !empty($_POST['imagen_3']) ? trim($_POST['imagen_3']) : null;
    $imagen_4 = !empty($_POST['imagen_4']) ? trim($_POST['imagen_4']) : null;
    $imagen_5 = !empty($_POST['imagen_5']) ? trim($_POST['imagen_5']) : null;
    $imagen_6 = !empty($_POST['imagen_6']) ? trim($_POST['imagen_6']) : null;
    $dias_envio = (int)$_POST['dias_envio'];
    $descripcion = trim($_POST['descripcion']);
    
    // NUEVO: Obtener tallas seleccionadas
    $tallas_seleccionadas = isset($_POST['tallas']) ? $_POST['tallas'] : [];
    
    // Validaciones
    $errores = array();
    
    if (empty($nombre) || strlen($nombre) < 3) $errores[] = "El nombre debe tener al menos 3 caracteres";
    if (empty($descripcion) || strlen($descripcion) < 10) $errores[] = "La descripción debe tener al menos 10 caracteres";
    if (empty($tipo)) $errores[] = "El tipo es requerido";
    if (empty($nivel_ciclismo)) $errores[] = "El nivel de ciclismo es requerido";
    if ($precio <= 0) $errores[] = "El precio debe ser mayor a 0";
    if ($dias_envio < 1 || $dias_envio > 60) $errores[] = "Los días de envío deben estar entre 1 y 60";
    if (empty($imagen)) $errores[] = "La imagen principal es requerida";
    
    // NUEVO: Validar tallas
    if (empty($tallas_seleccionadas)) {
        $errores[] = "Debes seleccionar al menos una talla";
    }
    
    // Validar tipo
    $tipos_permitidos = ['montaña', 'ruta', 'urbana', 'eléctrica'];
    if (!in_array($tipo, $tipos_permitidos)) {
        $errores[] = "El tipo de bicicleta no es válido";
    }
    
    // Validar tallas y stocks
    $tallas_permitidas = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
    $tallas_con_stock = array();
    $stock_total = 0;
    
    foreach ($tallas_seleccionadas as $talla) {
        if (!in_array($talla, $tallas_permitidas)) {
            $errores[] = "La talla '$talla' no es válida";
            continue;
        }
        
        $stock_key = 'stock_' . $talla;
        $stock = isset($_POST[$stock_key]) ? (int)$_POST[$stock_key] : 0;
        
        if ($stock < 0) {
            $errores[] = "El stock de la talla $talla no puede ser negativo";
        }
        
        if ($stock > 0) {
            $tallas_con_stock[$talla] = $stock;
            $stock_total += $stock;
        }
    }
    
    if (empty($tallas_con_stock)) {
        $errores[] = "Al menos una talla debe tener stock mayor a 0";
    }
    
    // Validar nivel de ciclismo
    $niveles_permitidos = ['principiante', 'intermedio', 'avanzado'];
    if (!in_array($nivel_ciclismo, $niveles_permitidos)) {
        $errores[] = "El nivel de ciclismo no es válido";
    }
    
    if (empty($errores)) {
        // Iniciar transacción
        $conn->begin_transaction();
        
        try {
            // Insertar producto con la primera talla (para mantener compatibilidad)
            $primera_talla = array_key_first($tallas_con_stock);
            
            $sql = "INSERT INTO productos (nombre, tipo, talla, nivel_ciclismo, peso, velocidades, precio, imagen, imagen_principal, imagen_2, imagen_3, imagen_4, imagen_5, imagen_6, stock, dias_envio, descripcion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            
            if ($stmt === false) {
                throw new Exception("Error en prepare: " . $conn->error);
            }
            
            $stmt->bind_param("sssssissssssssiis", 
                $nombre,           
                $tipo,             
                $primera_talla,    // Usar primera talla seleccionada
                $nivel_ciclismo,   
                $peso,             
                $velocidades,      
                $precio,           
                $imagen,           
                $imagen_principal, 
                $imagen_2,         
                $imagen_3,         
                $imagen_4,         
                $imagen_5,         
                $imagen_6,         
                $stock_total,      // Stock total de todas las tallas
                $dias_envio,       
                $descripcion       
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Error al agregar el producto: " . $stmt->error);
            }
            
            $producto_id = $conn->insert_id;
            $stmt->close();
            
            // Insertar tallas en la tabla producto_tallas
            $stmt_talla = $conn->prepare("INSERT INTO producto_tallas (producto_id, talla, stock, activo) VALUES (?, ?, ?, 1)");
            
            foreach ($tallas_con_stock as $talla => $stock) {
                $stmt_talla->bind_param("isi", $producto_id, $talla, $stock);
                
                if (!$stmt_talla->execute()) {
                    throw new Exception("Error al agregar talla $talla: " . $stmt_talla->error);
                }
            }
            
            $stmt_talla->close();
            
            // Commit de la transacción
            $conn->commit();
            $conn->close();
            
            $mensaje_tallas = count($tallas_con_stock) . " talla(s): " . implode(", ", array_keys($tallas_con_stock));
            header("Location: principal_operador.php?success=" . urlencode("Producto '$nombre' creado exitosamente con $mensaje_tallas"));
            exit();
            
        } catch (Exception $e) {
            // Rollback en caso de error
            $conn->rollback();
            $errores[] = $e->getMessage();
        }
    }
    
    if (!empty($errores)) {
        $conn->close();
        $mensaje_error = implode(". ", $errores);
        header("Location: operador_crear_producto.php?error=" . urlencode($mensaje_error));
        exit();
    }
    
} else {
    header("Location: operador_crear_producto.php");
    exit;
}

$conn->close();
?>