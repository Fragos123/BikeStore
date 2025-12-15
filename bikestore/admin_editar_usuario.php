<?php
session_start();
if (!isset($_SESSION['logueado']) || $_SESSION['usuario_rol'] !== 'admin') { header("Location: login.php"); exit; }
include 'conexion.php';

if (!isset($_GET['id'])) { header("Location: principal_admin.php"); exit; }
$id = (int)$_GET['id'];

$stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Usuario</title>
    <style>
        body { font-family: sans-serif; background: #2c3e50; padding: 2rem; display: flex; justify-content: center; }
        .card { background: white; padding: 2rem; border-radius: 8px; width: 100%; max-width: 500px; }
        input, select { width: 100%; padding: 0.8rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px; }
        .btn { padding: 0.8rem; width: 100%; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .btn-save { background: #2c3e50; color: white; margin-bottom: 0.5rem; }
        .btn-cancel { background: #ccc; color: #333; text-decoration: none; display: block; text-align: center; }
    </style>
</head>
<body>
    <div class="card">
        <h2 style="text-align:center; color:#2c3e50;">Editar Usuario</h2>
        <form action="procesar_admin_editar_usuario.php" method="POST">
            <input type="hidden" name="usuario_id" value="<?php echo $usuario['id']; ?>">
            
            <label>Nombre:</label>
            <input type="text" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
            
            <label>Correo:</label>
            <input type="email" name="correo" value="<?php echo htmlspecialchars($usuario['correo']); ?>" required>
            
            <label>Rol (Permisos):</label>
            <select name="rol">
                <option value="cliente" <?php echo $usuario['rol']=='cliente'?'selected':''; ?>>Cliente</option>
                <option value="operador" <?php echo $usuario['rol']=='operador'?'selected':''; ?>>Operador</option>
                <option value="admin" <?php echo $usuario['rol']=='admin'?'selected':''; ?>>Administrador</option>
            </select>
            
            <label>Nivel Ciclismo:</label>
            <select name="nivel_ciclismo">
                <option value="principiante" <?php echo $usuario['nivel_ciclismo']=='principiante'?'selected':''; ?>>Principiante</option>
                <option value="intermedio" <?php echo $usuario['nivel_ciclismo']=='intermedio'?'selected':''; ?>>Intermedio</option>
                <option value="avanzado" <?php echo $usuario['nivel_ciclismo']=='avanzado'?'selected':''; ?>>Avanzado</option>
            </select>
            
            <label>Edad:</label>
            <input type="number" name="edad" value="<?php echo $usuario['edad']; ?>">
            
            <button type="submit" class="btn btn-save">Guardar Cambios</button>
            <a href="principal_admin.php" class="btn btn-cancel">Cancelar</a>
        </form>
    </div>
</body>
</html>