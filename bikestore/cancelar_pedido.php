<?php
session_start();
// cancelar_pedido.php

if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header("Location: login.php");
    exit;
}

include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pedido_id = isset($_POST['pedido_id']) ? (int)$_POST['pedido_id'] : 0;
    $usuario_id = $_SESSION['usuario_id'];

    if ($pedido_id <= 0) {
        header("Location: pedidos.php?error=" . urlencode("ID de pedido inválido"));
        exit;
    }

    // 1. Verificar que el pedido sea del usuario y esté 'pendiente'
    // Solo permitimos cancelar si aún no lo han procesado/enviado
    $stmt = $conn->prepare("SELECT id, estado FROM pedidos WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $pedido_id, $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        header("Location: pedidos.php?error=" . urlencode("Pedido no encontrado"));
        exit;
    }

    $pedido = $result->fetch_assoc();
    $stmt->close();

    if ($pedido['estado'] !== 'pendiente') {
        header("Location: pedidos.php?error=" . urlencode("Este pedido ya no se puede cancelar porque está en proceso o enviado."));
        exit;
    }

    // INICIAR TRANSACCIÓN DE DEVOLUCIÓN
    $conn->begin_transaction();

    try {
        // 2. Obtener los productos del pedido para devolver el stock
        $stmt_items = $conn->prepare("SELECT producto_id, talla, cantidad FROM pedido_items WHERE pedido_id = ?");
        $stmt_items->bind_param("i", $pedido_id);
        $stmt_items->execute();
        $items = $stmt_items->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt_items->close();

        // 3. Devolver stock (Inventario general y por talla)
        $stmt_stock_talla = $conn->prepare("UPDATE producto_tallas SET stock = stock + ? WHERE producto_id = ? AND talla = ?");
        $stmt_stock_prod = $conn->prepare("UPDATE productos SET stock = stock + ? WHERE id = ?");

        foreach ($items as $item) {
            // Devolver a producto_tallas
            $stmt_stock_talla->bind_param("iis", $item['cantidad'], $item['producto_id'], $item['talla']);
            $stmt_stock_talla->execute();

            // Devolver al total en productos
            $stmt_stock_prod->bind_param("ii", $item['cantidad'], $item['producto_id']);
            $stmt_stock_prod->execute();
        }
        
        $stmt_stock_talla->close();
        $stmt_stock_prod->close();

        // 4. Cambiar estado del pedido a 'cancelado'
        $stmt_update = $conn->prepare("UPDATE pedidos SET estado = 'cancelado' WHERE id = ?");
        $stmt_update->bind_param("i", $pedido_id);
        
        if (!$stmt_update->execute()) {
            throw new Exception("Error al actualizar el estado del pedido.");
        }
        $stmt_update->close();

        // Confirmar todo
        $conn->commit();
        $conn->close();

        header("Location: pedidos.php?success=" . urlencode("Pedido cancelado correctamente. Hemos reembolsado el stock."));
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        $conn->close();
        header("Location: pedidos.php?error=" . urlencode("Error al cancelar: " . $e->getMessage()));
        exit;
    }

} else {
    header("Location: pedidos.php");
    exit;
}
?>