<?php
session_start();
// 1. SEGURIDAD: Permitir 'operador' O 'admin'
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true || 
    !in_array($_SESSION['usuario_rol'], ['operador', 'admin'])) {
    header("Location: login.php");
    exit;
}

include 'conexion.php';

// Definir a dónde redirigir
$redirect_success = ($_SESSION['usuario_rol'] === 'admin') ? 'lista_productos.php' : 'principal_operador.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Recolección
    $producto_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
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
    
    // Validaciones
    $errores = [];
    if ($producto_id <= 0) $errores[] = "ID de producto inválido.";
    if (empty($nombre)) $errores[] = "Nombre requerido.";
    if ($precio <= 0) $errores[] = "Precio inválido.";
    if ($precio > 1000000) $errores[] = "Precio excede el límite.";
    
    // Validar Tallas
    $tallas_seleccionadas = isset($_POST['tallas']) ? $_POST['tallas'] : [];
    $stock_total_nuevo = 0;
    
    foreach($tallas_seleccionadas as $talla) {
        $st = isset($_POST['stock_'.$talla]) ? (int)$_POST['stock_'.$talla] : 0;
        if($st > 50) {
            $errores[] = "Stock para $talla excede 50 unidades.";
            break;
        }
        if($st >= 0) $stock_total_nuevo += $st;
    }
    
    if (empty($tallas_seleccionadas) && empty($errores)) $errores[] = "Selecciona al menos una talla.";
    if ($stock_total_nuevo <= 0 && empty($errores)) $errores[] = "El stock total debe ser mayor a 0.";

    if (empty($errores)) {
        $conn->begin_transaction();
        try {
            // A. Actualizar tabla principal
            $sql = "UPDATE productos SET 
                    nombre = ?, descripcion = ?, tipo = ?, precio = ?, 
                    nivel_ciclismo = ?, dias_envio = ?, peso = ?, velocidades = ?, 
                    imagen_principal = ?, imagen_2 = ?, imagen_3 = ?, 
                    imagen_4 = ?, imagen_5 = ?, imagen_6 = ?
                    WHERE id = ?";
            
            $stmt = $conn->prepare($sql);
            // Tipos: sssdsidissssssi (17 caracteres)
            $stmt->bind_param("sssdsidissssssi", 
                $nombre, $descripcion, $tipo, $precio, 
                $nivel_ciclismo, $dias_envio, $peso, $velocidades, 
                $imagen_principal, $imagen_2, $imagen_3, $imagen_4, $imagen_5, $imagen_6, 
                $producto_id
            );
            
            if (!$stmt->execute()) throw new Exception("Error al actualizar datos base.");
            $stmt->close();
            
            // B. Actualizar Tallas (Lógica inteligente)
            $conn->query("UPDATE producto_tallas SET activo = 0 WHERE producto_id = $producto_id");
            
            $stmt_check = $conn->prepare("SELECT id FROM producto_tallas WHERE producto_id = ? AND talla = ?");
            $stmt_update_talla = $conn->prepare("UPDATE producto_tallas SET stock = ?, activo = 1 WHERE id = ?");
            $stmt_insert_talla = $conn->prepare("INSERT INTO producto_tallas (producto_id, talla, stock, activo) VALUES (?, ?, ?, 1)");
            
            foreach ($tallas_seleccionadas as $talla) {
                $stock = (int)$_POST['stock_' . $talla];
                if ($stock <= 0) continue; 
                
                $stmt_check->bind_param("is", $producto_id, $talla);
                $stmt_check->execute();
                $res = $stmt_check->get_result();
                
                if ($res->num_rows > 0) {
                    $row = $res->fetch_assoc();
                    $stmt_update_talla->bind_param("ii", $stock, $row['id']);
                    $stmt_update_talla->execute();
                } else {
                    $stmt_insert_talla->bind_param("isi", $producto_id, $talla, $stock);
                    $stmt_insert_talla->execute();
                }
            }
            
            // C. Actualizar Stock Total
            $conn->query("UPDATE productos SET stock = $stock_total_nuevo WHERE id = $producto_id");
            
            $conn->commit();
            header("Location: $redirect_success?success=" . urlencode("Producto actualizado correctamente."));
            exit;

        } catch (Exception $e) {
            $conn->rollback();
            $errores[] = $e->getMessage();
        }
    }
    
    // Si falla
    $conn->close();
    header("Location: operador_editar_producto.php?id=$producto_id&error=" . urlencode(implode(" ", $errores)));
    exit;
    
} else {
    header("Location: " . $redirect_success);
    exit;
}
?>