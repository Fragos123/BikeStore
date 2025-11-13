<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

include 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $usuario_id = $_SESSION['usuario_id'];
    $direccion_id = isset($_POST['direccion_id']) ? (int)$_POST['direccion_id'] : 0;
    
    if ($direccion_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID inválido']);
        exit;
    }
    
    // Verificar que la dirección pertenece al usuario y no es principal
    $stmt = $conn->prepare("SELECT es_principal FROM direcciones WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $direccion_id, $usuario_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows === 0) {
        $stmt->close();
        $conn->close();
        echo json_encode(['success' => false, 'message' => 'Dirección no encontrada']);
        exit;
    }
    
    $direccion = $resultado->fetch_assoc();
    $stmt->close();
    
    if ($direccion['es_principal'] == 1) {
        $conn->close();
        echo json_encode(['success' => false, 'message' => 'No puedes eliminar la dirección principal']);
        exit;
    }
    
    // Eliminar la dirección
    $stmt = $conn->prepare("DELETE FROM direcciones WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $direccion_id, $usuario_id);
    
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        echo json_encode(['success' => true, 'message' => 'Dirección eliminada']);
        exit;
    } else {
        $stmt->close();
        $conn->close();
        echo json_encode(['success' => false, 'message' => 'Error al eliminar']);
        exit;
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}
?>