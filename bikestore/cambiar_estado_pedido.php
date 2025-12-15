<?php
session_start();
// cambiar_estado_pedido.php

// 1. SEGURIDAD: Solo Admin y Operador
if (!isset($_SESSION['logueado']) || !in_array($_SESSION['usuario_rol'], ['admin', 'operador'])) {
    header("Location: login.php");
    exit;
}

include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pedido_id = isset($_POST['pedido_id']) ? intval($_POST['pedido_id']) : 0;
    $nuevo_estado = isset($_POST['nuevo_estado']) ? $_POST['nuevo_estado'] : '';
    
    // Validar estados permitidos
    $estados_validos = ['pendiente', 'procesando', 'enviado', 'entregado', 'cancelado'];
    
    if ($pedido_id > 0 && in_array($nuevo_estado, $estados_validos)) {
        
        // Si el estado es 'cancelado', aquí podrías agregar la lógica de devolución de stock si lo deseas.
        // Por ahora, solo actualizamos el estatus administrativo.

        $stmt = $conn->prepare("UPDATE pedidos SET estado = ?, fecha_actualizacion = NOW() WHERE id = ?");
        $stmt->bind_param("si", $nuevo_estado, $pedido_id);
        
        if ($stmt->execute()) {
            // Redirigir a la página de donde vino (lista de ventas)
            $redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'lista_ventas.php';
            header("Location: $redirect?success=" . urlencode("Estado actualizado a " . ucfirst($nuevo_estado)));
            exit;
        } else {
            header("Location: lista_ventas.php?error=" . urlencode("Error al actualizar"));
            exit;
        }
        $stmt->close();
    } else {
        header("Location: lista_ventas.php?error=" . urlencode("Datos inválidos"));
        exit;
    }
} else {
    header("Location: lista_ventas.php");
    exit;
}
?>