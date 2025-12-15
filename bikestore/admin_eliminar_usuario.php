<?php
session_start();
//admin_eliminar_usuario.php
// Verificar si el usuario está logueado y es admin
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true || $_SESSION['usuario_rol'] !== 'admin') {
    header("Location: login.php");
    exit;
}

include 'conexion.php';

if (isset($_GET['id'])) {
    $usuario_id = (int)$_GET['id'];
    $filtro_rol = isset($_GET['filtro_rol']) ? $_GET['filtro_rol'] : '';
    
    // Prevenir auto-eliminación
    if ($usuario_id == $_SESSION['usuario_id']) {
        $conn->close();
        header("Location: principal_admin.php?error=" . urlencode("No puedes eliminarte a ti mismo"));
        exit();
    }
    
    // Verificar usuario
    $stmt = $conn->prepare("SELECT id, nombre, rol FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows > 0) {
        $usuario = $resultado->fetch_assoc();
        $stmt->close();
        
        // VERIFICAR VENTAS
        $stmt_check = $conn->prepare("SELECT COUNT(*) as total FROM pedidos WHERE usuario_id = ?");
        $stmt_check->bind_param("i", $usuario_id);
        $stmt_check->execute();
        $tiene_ventas = $stmt_check->get_result()->fetch_assoc()['total'] > 0;
        $stmt_check->close();
        
        $conn->begin_transaction();

        try {
            if ($tiene_ventas) {
                // ANONIMIZAR
                $conn->query("DELETE FROM carrito WHERE usuario_id = $usuario_id");
                $conn->query("DELETE FROM sesiones WHERE usuario_id = $usuario_id");
                $conn->query("DELETE FROM metodos_pago WHERE usuario_id = $usuario_id");
                
                $correo_falso = "eliminado_" . time() . "_" . $usuario_id . "@bikestore.anonymized";
                $nombre_falso = "Usuario Eliminado (Histórico)";
                $pass_inutil = password_hash(bin2hex(random_bytes(20)), PASSWORD_DEFAULT); 

                $stmt = $conn->prepare("UPDATE usuarios SET nombre = ?, correo = ?, password = ?, rol = 'banned' WHERE id = ?");
                $stmt->bind_param("sssi", $nombre_falso, $correo_falso, $pass_inutil, $usuario_id);
                $stmt->execute();
                $stmt->close();

                $mensaje_exito = "Usuario anonimizado (tenía historial de ventas).";
            } else {
                // BORRAR TOTALMENTE
                $conn->query("DELETE FROM carrito WHERE usuario_id = $usuario_id");
                $conn->query("DELETE FROM comentarios WHERE usuario_id = $usuario_id");
                $conn->query("DELETE FROM direcciones WHERE usuario_id = $usuario_id");
                $conn->query("DELETE FROM metodos_pago WHERE usuario_id = $usuario_id");
                $conn->query("DELETE FROM sesiones WHERE usuario_id = $usuario_id");

                $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
                $stmt->bind_param("i", $usuario_id);
                $stmt->execute();
                $stmt->close();

                $mensaje_exito = "Usuario eliminado completamente.";
            }

            $conn->commit();
            $conn->close();
            
            $redirect = "principal_admin.php?success=" . urlencode($mensaje_exito);
            if (!empty($filtro_rol)) $redirect .= "&filtro_rol=" . urlencode($filtro_rol);
            header("Location: " . $redirect);
            exit();

        } catch (Exception $e) {
            $conn->rollback();
            $conn->close();
            header("Location: principal_admin.php?error=" . urlencode("Error: " . $e->getMessage()));
            exit();
        }
        
    } else {
        $stmt->close();
        $conn->close();
        header("Location: principal_admin.php?error=" . urlencode("Usuario no encontrado"));
        exit();
    }
} else {
    $conn->close();
    header("Location: principal_admin.php");
    exit();
}
?>