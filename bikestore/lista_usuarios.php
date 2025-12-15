<?php
session_start();
if (!isset($_SESSION['logueado']) || $_SESSION['usuario_rol'] !== 'admin') {
    header("Location: login.php"); exit;
}
include 'conexion.php';

// Paginación
$por_pagina = 15;
$pagina = isset($_GET['p']) ? (int)$_GET['p'] : 1;
if($pagina < 1) $pagina = 1;
$inicio = ($pagina - 1) * $por_pagina;

// Búsqueda
$busqueda = isset($_GET['q']) ? trim($_GET['q']) : '';
$where = "WHERE 1=1"; 

if($busqueda) {
    $where .= " AND (nombre LIKE '%$busqueda%' OR correo LIKE '%$busqueda%' OR rol LIKE '%$busqueda%')";
}

// Total
$total = $conn->query("SELECT COUNT(*) as c FROM usuarios $where")->fetch_assoc()['c'];
$paginas = ceil($total / $por_pagina);

// Consulta
$sql = "SELECT * FROM usuarios $where ORDER BY id DESC LIMIT $inicio, $por_pagina";
$usuarios = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Usuarios</title>
    <style>
        body { font-family: sans-serif; background: #f8f7f4; padding: 2rem; color: #333; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .search-input { padding: 0.5rem; width: 300px; border: 1px solid #ccc; border-radius: 4px; }
        .btn { padding: 0.5rem 1rem; background: #2c2c2c; color: white; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; }
        .btn-edit { background: #e0f2fe; color: #0369a1; }
        .btn-delete { background: #fee2e2; color: #b91c1c; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 1rem; border-bottom: 1px solid #eee; text-align: left; }
        th { background: #fafaf9; }
        .pagination { display: flex; gap: 0.5rem; justify-content: center; margin-top: 2rem; }
        .page-link { padding: 0.5rem 1rem; border: 1px solid #ccc; text-decoration: none; color: #333; }
        .page-link.active { background: #2c2c2c; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1>Lista de Usuarios</h1>
                <a href="principal_admin.php" style="color:#666; text-decoration:none;">← Volver al Panel</a>
            </div>
            <div style="display:flex; gap:1rem;">
                <form action="" method="GET">
                    <input type="text" name="q" class="search-input" placeholder="Nombre, correo o rol..." value="<?php echo htmlspecialchars($busqueda); ?>">
                    <button type="submit" class="btn">Buscar</button>
                </form>
                <a href="admin_crear_usuario.php" class="btn">Nuevo Usuario</a>
            </div>
        </div>

        <table>
            <thead><tr><th>ID</th><th>Nombre</th><th>Correo</th><th>Rol</th><th>Registro</th><th>Acciones</th></tr></thead>
            <tbody>
                <?php foreach($usuarios as $u): ?>
                <tr>
                    <td>#<?php echo $u['id']; ?></td>
                    <td><?php echo htmlspecialchars($u['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($u['correo']); ?></td>
                    <td><span style="padding:0.2rem 0.5rem; background:#eee; border-radius:4px; font-size:0.8rem;"><?php echo ucfirst($u['rol']); ?></span></td>
                    <td><?php echo date('d/m/Y', strtotime($u['fecha_registro'])); ?></td>
                    <td>
                        <a href="admin_editar_usuario.php?id=<?php echo $u['id']; ?>" class="btn btn-edit" style="font-size:0.8rem; padding:0.3rem 0.6rem;">✏️</a>
                        <?php if($u['id'] != $_SESSION['usuario_id']): ?>
                            <a href="admin_eliminar_usuario.php?id=<?php echo $u['id']; ?>" onclick="return confirm('¿Seguro?')" class="btn btn-delete" style="font-size:0.8rem; padding:0.3rem 0.6rem;">🗑️</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if($paginas > 1): ?>
        <div class="pagination">
            <?php for($i=1; $i<=$paginas; $i++): ?>
                <a href="?p=<?php echo $i; ?>&q=<?php echo $busqueda; ?>" class="page-link <?php echo $i==$pagina?'active':''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>