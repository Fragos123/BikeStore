<?php
session_start();
// operador_eliminar_cliente.php

// 1. SEGURIDAD: Solo Operador o Admin
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true || 
    !in_array($_SESSION['usuario_rol'], ['operador', 'admin'])) {
    header("Location: login.php");
    exit;
}

include 'conexion.php';

if (isset($_GET['id'])) {
    $cliente_id = intval($_GET['id']);
    
    // 2. VERIFICAR QUE SEA UN CLIENTE (No borrar admins ni operadores)
    $stmt = $conn->prepare("SELECT id, nombre, rol FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $cliente_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user) {
        header("Location: principal_operador.php?error=" . urlencode("Usuario no encontrado"));
        exit;
    }

    if ($user['rol'] !== 'cliente') {
        header("Location: principal_operador.php?error=" . urlencode("Solo puedes eliminar usuarios con rol de Cliente."));
        exit;
    }

    // 3. ELIMINAR CLIENTE Y SUS DATOS RELACIONADOS
    $conn->begin_transaction();
    try {
        // Borrar datos dependientes
        $conn->query("DELETE FROM carrito WHERE usuario_id = $cliente_id");
        $conn->query("DELETE FROM direcciones WHERE usuario_id = $cliente_id");
        $conn->query("DELETE FROM metodos_pago WHERE usuario_id = $cliente_id");
        $conn->query("DELETE FROM comentarios WHERE usuario_id = $cliente_id");
        
        // Anonimizar pedidos históricos (Opcional: Si quieres mantener el registro financiero pero sin el usuario)
        // O borrar todo si prefieres limpieza total. Aquí borraremos todo para simplificar la petición.
        // Nota: Si borras pedidos, se borra el historial de ventas. 
        // Mejor práctica: Poner usuario_id en NULL en pedidos si la DB lo permite, o borrar.
        // Asumiremos borrado total a petición del usuario.
        
        $conn->query("DELETE FROM pedido_items WHERE pedido_id IN (SELECT id FROM pedidos WHERE usuario_id = $cliente_id)");
        $conn->query("DELETE FROM pedidos WHERE usuario_id = $cliente_id");
        
        // Finalmente borrar el usuario
        $stmt_del = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt_del->bind_param("i", $cliente_id);
        $stmt_del->execute();
        $stmt_del->close();

        $conn->commit();
        header("Location: principal_operador.php?tab=clientes&success=" . urlencode("Cliente eliminado correctamente."));
        
    } catch (Exception $e) {
        $conn->rollback();
        header("Location: principal_operador.php?tab=clientes&error=" . urlencode("Error al eliminar: " . $e->getMessage()));
    }

} else {
    header("Location: principal_operador.php");
}
$conn->close();
?>