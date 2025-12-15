<?php 
session_start();
//principal_admin.php

// Verificar si el usuario está logueado y es admin
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true || $_SESSION['usuario_rol'] !== 'admin') {
    header("Location: login.php");
    exit;
}

include 'conexion.php';

// --- 1. ESTADÍSTICAS DE USUARIOS ---
$stmt = $conn->query("SELECT COUNT(*) as total FROM usuarios");
$total_usuarios = $stmt->fetch_assoc()['total'];

$stmt = $conn->query("SELECT rol, COUNT(*) as cantidad FROM usuarios GROUP BY rol");
$usuarios_por_rol = [];
while ($row = $stmt->fetch_assoc()) {
    $usuarios_por_rol[$row['rol']] = $row['cantidad'];
}

$filtro_rol = isset($_GET['filtro_rol']) ? $_GET['filtro_rol'] : '';

// Obtener últimos usuarios (Limitado a 5 para el dashboard)
if (!empty($filtro_rol)) {
    $stmt = $conn->prepare("SELECT id, nombre, correo, rol, fecha_registro FROM usuarios WHERE rol = ? ORDER BY fecha_registro DESC LIMIT 5");
    $stmt->bind_param("s", $filtro_rol);
    $stmt->execute();
    $result = $stmt->get_result();
    $ultimos_usuarios = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $stmt = $conn->query("SELECT id, nombre, correo, rol, fecha_registro FROM usuarios ORDER BY fecha_registro DESC LIMIT 5");
    $ultimos_usuarios = $stmt->fetch_all(MYSQLI_ASSOC);
}

// --- 2. REPORTE DE VENTAS (Financiero) ---
// Ingresos totales (excluyendo cancelados)
$stmt_stats = $conn->query("
    SELECT 
        COUNT(*) as total_pedidos, 
        SUM(CASE WHEN estado != 'cancelado' THEN total ELSE 0 END) as ingresos_totales, 
        AVG(CASE WHEN estado != 'cancelado' THEN total ELSE NULL END) as pedido_promedio 
    FROM pedidos
");
$estadisticas = $stmt_stats->fetch_assoc();

// Últimos pedidos (Limitado a 5 para el dashboard)
$stmt_recientes = $conn->query("SELECT * FROM pedidos ORDER BY fecha DESC LIMIT 5");
$ventas_recientes = $stmt_recientes->fetch_all(MYSQLI_ASSOC);

// --- 3. REPORTE DE INVENTARIO ---
$stmt_prod_count = $conn->query("SELECT COUNT(*) as total FROM productos WHERE stock > -1");
$total_productos = $stmt_prod_count->fetch_assoc()['total'];

// Últimos productos agregados (Limitado a 5)
$stmt_prod_recent = $conn->query("SELECT * FROM productos WHERE stock > -1 ORDER BY id DESC LIMIT 5");
$ultimos_productos = $stmt_prod_recent->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - BikeStore</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica Neue', -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif; background: #f8f7f4; min-height: 100vh; color: #2c2c2c; }

        /* Sidebar */
        .sidebar { position: fixed; left: 0; top: 0; bottom: 0; width: 260px; background: #1a1a1a; color: #e8e6e1; padding: 2rem 0; box-shadow: 2px 0 20px rgba(0, 0, 0, 0.08); z-index: 100; }
        .sidebar-header { padding: 0 1.5rem 2rem; border-bottom: 1px solid rgba(232, 230, 225, 0.1); }
        .logo { font-size: 1.5rem; font-weight: 300; letter-spacing: 3px; margin-bottom: 0.5rem; color: #dcdcdc; }
        .user-info { font-size: 0.85rem; color: #a8a8a8; margin-top: 0.5rem; font-weight: 300; }
        .user-role { display: inline-block; background: #f8f7f4; color: #1a1a1a; padding: 0.25rem 0.75rem; border-radius: 2px; font-size: 0.7rem; margin-top: 0.5rem; font-weight: 500; letter-spacing: 1px; text-transform: uppercase; }
        .sidebar-menu { margin-top: 2rem; }
        .menu-item { padding: 1rem 1.5rem; color: #a8a8a8; text-decoration: none; display: flex; align-items: center; gap: 1rem; transition: all 0.3s ease; font-weight: 300; font-size: 0.95rem; border-left: 3px solid transparent; }
        .menu-item:hover { background: rgba(212, 175, 55, 0.05); color: #f8f7f4; border-left-color: #f8f7f4; }
        .menu-item.active { background: rgba(212, 175, 55, 0.08); color: #f8f7f4; border-left-color: #f8f7f4; font-weight: 400; }
        .menu-icon { font-size: 1.2rem; }

        /* Main content */
        .main-content { margin-left: 260px; padding: 2.5rem; }
        .top-bar { background: white; padding: 1.8rem 2.5rem; border-radius: 2px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04); margin-bottom: 2.5rem; display: flex; justify-content: space-between; align-items: center; border: 1px solid #e8e6e1; }
        .page-title { font-size: 1.6rem; font-weight: 300; color: #1a1a1a; letter-spacing: 0.5px; }
        .top-buttons { display: flex; gap: 1rem; }
        
        .btn { padding: 0.75rem 1.8rem; border-radius: 2px; font-weight: 400; text-decoration: none; border: none; transition: all 0.3s ease; cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem; font-size: 0.9rem; letter-spacing: 0.5px; }
        .btn-primary { background: #2c2c2c; color: #f8f7f4; border: 1px solid #2c2c2c; }
        .btn-primary:hover { background: #1a1a1a; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); }
        .btn-secondary { background: transparent; color: #2c2c2c; border: 1px solid #d4d4d4; }
        .btn-secondary:hover { background: #f8f7f4; border-color: #2c2c2c; }

        /* Stats & Cards */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem; }
        .stat-card { background: white; padding: 2rem; border-radius: 2px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04); display: flex; justify-content: space-between; align-items: center; transition: all 0.3s ease; cursor: pointer; border: 1px solid #e8e6e1; }
        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); border-color: #2c3e50; }
        .stat-card.active { border-color: #2c3e50; box-shadow: 0 4px 12px rgba(212, 175, 55, 0.15); background: #fffef9; }
        .stat-label { font-size: 0.75rem; color: #7a7a7a; font-weight: 400; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 0.5rem; }
        .stat-value { font-size: 2.2rem; font-weight: 300; color: #1a1a1a; margin-top: 0.5rem; }
        .stat-icon { width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; background: #f8f7f4; border: 1px solid #e8e6e1; }

        /* Tables */
        .table-card { background: white; border-radius: 2px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04); overflow: hidden; border: 1px solid #e8e6e1; margin-bottom: 2.5rem; }
        .table-header { padding: 1.8rem 2.5rem; border-bottom: 1px solid #e8e6e1; display: flex; justify-content: space-between; align-items: center; background: #fafaf9; }
        .table-title { font-size: 1.2rem; font-weight: 400; color: #1a1a1a; letter-spacing: 0.5px; }
        .filter-info { font-size: 0.85rem; color: #7a7a7a; font-weight: 400; text-decoration: none; padding: 0.5rem 1rem; border-radius: 2px; transition: all 0.3s ease; border: 1px solid #e8e6e1; }
        .filter-info:hover { background: #f8f7f4; color: #2c2c2c; border-color: #d4d4d4; }
        
        table { width: 100%; border-collapse: collapse; }
        th { background: #fafaf9; padding: 1rem 2rem; text-align: left; font-weight: 400; color: #7a7a7a; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1.5px; border-bottom: 1px solid #e8e6e1; }
        td { padding: 1.2rem 2rem; border-bottom: 1px solid #f8f7f4; color: #2c2c2c; font-size: 0.9rem; }
        tr:hover { background: #fffef9; }

        /* Badges */
        .badge { display: inline-block; padding: 0.4rem 0.9rem; border-radius: 2px; font-size: 0.7rem; font-weight: 500; letter-spacing: 1px; text-transform: uppercase; }
        .badge-admin { background: #1a1a1a; color: #f8f7f4; }
        .badge-operador { background: #4a5568; color: white; }
        .badge-cliente { background: #8b7355; color: white; }
        
        .badge-pendiente { background: #fef3c7; color: #92400e; }
        .badge-procesando { background: #dbeafe; color: #1e40af; }
        .badge-enviado { background: #4a5568; color: white; }
        .badge-entregado { background: #d1fae5; color: #065f46; }
        .badge-cancelado { background: #fef2f2; color: #991b1b; }

        .stock-badge { display: inline-block; padding: 0.3rem 0.7rem; border-radius: 15px; font-size: 0.75rem; font-weight: 600; }
        .stock-ok { background: #c6f6d5; color: #22543d; }
        .stock-low { background: #feebc8; color: #7c2d12; }
        .stock-out { background: #fed7d7; color: #742a2a; }
        
        .product-img-thumb { width: 40px; height: 40px; border-radius: 4px; object-fit: cover; background: #f3f4f6; }

        /* Buttons */
        .action-buttons { display: flex; gap: 0.5rem; }
        .btn-icon { padding: 0.5rem 0.75rem; border: 1px solid #e8e6e1; border-radius: 2px; font-weight: 400; cursor: pointer; transition: all 0.3s ease; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.25rem; background: white; color: #2c2c2c; }
        .btn-view:hover { background: #1a1a1a; color: #fff; border-color: #1a1a1a; }
        .btn-edit:hover { background: #4a5568; color: white; border-color: #4a5568; }
        .btn-delete:hover { background: #8b4513; color: white; border-color: #8b4513; }
        .btn-icon:disabled { background: #f8f7f4; color: #d4d4d4; cursor: not-allowed; border-color: #e8e6e1; }

        /* Modal */
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(26, 26, 26, 0.6); backdrop-filter: blur(5px); z-index: 1000; align-items: center; justify-content: center; }
        .modal.active { display: flex; }
        .modal-content { background: white; padding: 3rem; border-radius: 2px; max-width: 500px; width: 90%; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3); animation: modalSlideIn 0.3s ease; border: 1px solid #e8e6e1; }
        @keyframes modalSlideIn { from { transform: scale(0.95) translateY(-20px); opacity: 0; } to { transform: scale(1) translateY(0); opacity: 1; } }
        .modal-title { font-size: 1.3rem; font-weight: 400; color: #1a1a1a; margin-bottom: 1rem; letter-spacing: 0.5px; }
        .modal-text { color: #5a5a5a; margin-bottom: 2rem; line-height: 1.7; font-size: 0.95rem; }
        .modal-buttons { display: flex; gap: 1rem; justify-content: flex-end; }
        .btn-cancel { background: white; color: #5a5a5a; border: 1px solid #d4d4d4; padding: 0.75rem 1.8rem; border-radius: 2px; font-weight: 400; cursor: pointer; transition: all 0.3s ease; font-size: 0.9rem; }
        .btn-cancel:hover { background: #f8f7f4; border-color: #2c2c2c; color: #2c2c2c; }
        .btn-confirm { background: #8b4513; color: white; border: 1px solid #8b4513; padding: 0.75rem 1.8rem; border-radius: 2px; font-weight: 400; cursor: pointer; transition: all 0.3s ease; font-size: 0.9rem; }
        .btn-confirm:hover { background: #6d3410; border-color: #6d3410; box-shadow: 0 4px 12px rgba(139, 69, 19, 0.3); }

        /* Notifications */
        .notification { position: fixed; top: 20px; right: 20px; padding: 1.2rem 1.8rem; border-radius: 2px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2); z-index: 2000; display: none; align-items: center; gap: 0.75rem; font-weight: 400; animation: slideIn 0.3s ease; backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.2); font-size: 0.9rem; }
        .notification.success { background: #2d5016; color: white; }
        .notification.error { background: #8b4513; color: white; }
        .notification.show { display: flex; }
        @keyframes slideIn { from { transform: translateX(400px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

        @media (max-width: 768px) {
            .sidebar { width: 80px; }
            .sidebar-header .user-info, .sidebar-header .user-role { display: none; }
            .menu-item span:not(.menu-icon) { display: none; }
            .main-content { margin-left: 80px; padding: 1rem; }
            .stats-grid { grid-template-columns: 1fr; }
            .action-buttons { flex-direction: column; }
        }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="logo">BIKESTORE</div>
            <div class="user-info">
                <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>
                <div class="user-role">Administrador</div>
            </div>
        </div>
        
        <nav class="sidebar-menu">
            <a href="principal_admin.php" class="menu-item active">
                <span class="menu-icon">📊</span>
                <span>Dashboard</span>
            </a>
            <a href="admin_reporte_ventas.php" class="menu-item">
                <span class="menu-icon">📈</span>
                <span>Reporte de Ventas</span>
            </a>
            <a href="lista_productos.php" class="menu-item">
                <span class="menu-icon">🚲</span>
                <span>Inventario Completo</span>
            </a>
            <a href="lista_usuarios.php" class="menu-item">
                <span class="menu-icon">👥</span>
                <span>Usuarios</span>
            </a>
            <a href="perfil.php" class="menu-item">
                <span class="menu-icon">⚙️</span>
                <span>Mi Perfil</span>
            </a>
            <a href="logout.php" class="menu-item">
                <span class="menu-icon">🚪</span>
                <span>Cerrar Sesión</span>
            </a>
        </nav>
    </aside>

    <main class="main-content">
        <div class="top-bar">
            <h1 class="page-title">Panel de Administración</h1>
            <div class="top-buttons">
                <a href="admin_crear_usuario.php" class="btn btn-primary">
                    ➕ Crear Usuario
                </a>
                <a href="index.php" class="btn btn-secondary">Ver Sitio</a>
            </div>
        </div>

        <div class="table-header" style="background:none; padding-left:0; padding-right:0;">
            <h2 class="table-title">Resumen de Usuarios</h2>
        </div>
        <div class="stats-grid">
            <div class="stat-card" onclick="filtrarUsuarios('')" id="card-total">
                <div>
                    <div class="stat-label">Total Usuarios</div>
                    <div class="stat-value"><?php echo $total_usuarios; ?></div>
                </div>
                <div class="stat-icon">👥</div>
            </div>

            <div class="stat-card" onclick="filtrarUsuarios('cliente')" id="card-cliente">
                <div>
                    <div class="stat-label">Clientes</div>
                    <div class="stat-value"><?php echo $usuarios_por_rol['cliente'] ?? 0; ?></div>
                </div>
                <div class="stat-icon">👤</div>
            </div>

            <div class="stat-card" onclick="filtrarUsuarios('operador')" id="card-operador">
                <div>
                    <div class="stat-label">Operadores</div>
                    <div class="stat-value"><?php echo $usuarios_por_rol['operador'] ?? 0; ?></div>
                </div>
                <div class="stat-icon">🔧</div>
            </div>

            <div class="stat-card" onclick="filtrarUsuarios('admin')" id="card-admin">
                <div>
                    <div class="stat-label">Administradores</div>
                    <div class="stat-value"><?php echo $usuarios_por_rol['admin'] ?? 0; ?></div>
                </div>
                <div class="stat-icon">⭐</div>
            </div>
        </div>

        <div class="table-header" style="background:none; padding-left:0; padding-right:0;">
            <h2 class="table-title">Resumen Financiero e Inventario</h2>
        </div>
        <div class="stats-grid">
            <div class="stat-card">
                <div>
                    <div class="stat-label">Ingresos Totales (Est.)</div>
                    <div class="stat-value">$<?php echo number_format($estadisticas['ingresos_totales'] ?? 0, 2); ?></div>
                </div>
                <div class="stat-icon">💰</div>
            </div>

            <div class="stat-card">
                <div>
                    <div class="stat-label">Ventas Totales</div>
                    <div class="stat-value"><?php echo $estadisticas['total_pedidos'] ?? 0; ?></div>
                </div>
                <div class="stat-icon">📦</div>
            </div>

            <div class="stat-card">
                <div>
                    <div class="stat-label">Total Productos</div>
                    <div class="stat-value"><?php echo $total_productos; ?></div>
                </div>
                <div class="stat-icon">🚴</div>
            </div>
        </div>

        <div class="table-card">
            <div class="table-header">
                <h2 class="table-title">
                    <?php 
                    if (!empty($filtro_rol)) {
                        echo "Usuarios: " . ucfirst($filtro_rol) . "s";
                    } else {
                        echo "Últimos Usuarios Registrados";
                    }
                    ?>
                </h2>
                <?php if (!empty($filtro_rol)): ?>
                    <a href="principal_admin.php" class="filter-info">✕ Limpiar filtro</a>
                <?php endif; ?>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Fecha Registro</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($ultimos_usuarios)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 2rem; color: #7a7a7a;">
                                No hay usuarios registrados
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($ultimos_usuarios as $usuario): ?>
                        <tr>
                            <td>#<?php echo str_pad($usuario['id'], 4, '0', STR_PAD_LEFT); ?></td>
                            <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['correo']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $usuario['rol']; ?>">
                                    <?php echo ucfirst($usuario['rol']); ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($usuario['fecha_registro'])); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="admin_ver_usuario.php?id=<?php echo $usuario['id']; ?>" class="btn-icon btn-view" title="Ver detalles">👁️</a>
                                    <?php if ($usuario['id'] == $_SESSION['usuario_id']): ?>
                                        <button class="btn-icon btn-edit" disabled title="No puedes editarte a ti mismo desde aquí">✏️</button>
                                        <button class="btn-icon btn-delete" disabled title="No puedes eliminarte a ti mismo">🗑️</button>
                                    <?php else: ?>
                                        <a href="admin_editar_usuario.php?id=<?php echo $usuario['id']; ?>" class="btn-icon btn-edit" title="Editar usuario">✏️</a>
                                        <button class="btn-icon btn-delete" onclick="confirmarEliminacion(<?php echo $usuario['id']; ?>, '<?php echo htmlspecialchars($usuario['nombre']); ?>')" title="Eliminar usuario">🗑️</button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <div style="padding: 1rem; text-align: center; border-top: 1px solid #eee;">
                <a href="lista_usuarios.php" class="btn btn-secondary">Ver Todos los Usuarios →</a>
            </div>
        </div>

        <div class="table-card">
            <div class="table-header">
                <h2 class="table-title">Últimos Productos Agregados al Inventario</h2>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>IMG</th>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th>Precio</th>
                        <th>Stock Total</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($ultimos_productos)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2rem; color: #7a7a7a;">
                                No hay productos registrados.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($ultimos_productos as $prod): ?>
                        <tr>
                            <td>
                                <?php if($prod['imagen_principal']): ?>
                                    <img src="<?php echo htmlspecialchars($prod['imagen_principal']); ?>" class="product-img-thumb" alt="img">
                                <?php else: ?>
                                    <div class="product-img-thumb" style="display:flex;align-items:center;justify-content:center;">🚲</div>
                                <?php endif; ?>
                            </td>
                            <td>#<?php echo str_pad($prod['id'], 4, '0', STR_PAD_LEFT); ?></td>
                            <td><?php echo htmlspecialchars($prod['nombre']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $prod['tipo']; ?>">
                                    <?php echo ucfirst($prod['tipo']); ?>
                                </span>
                            </td>
                            <td>$<?php echo number_format($prod['precio'], 2); ?></td>
                            <td>
                                <?php
                                $stock = $prod['stock'];
                                if ($stock <= 0) {
                                    echo '<span class="stock-badge stock-out">Agotado</span>';
                                } elseif ($stock <= 5) {
                                    echo '<span class="stock-badge stock-low">' . $stock . ' unid.</span>';
                                } else {
                                    echo '<span class="stock-badge stock-ok">' . $stock . ' unid.</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <a href="operador_editar_producto.php?id=<?php echo $prod['id']; ?>" class="btn-icon btn-edit" title="Editar producto">✏️</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <div style="padding: 1rem; text-align: center; border-top: 1px solid #eee;">
                <a href="lista_productos.php" class="btn btn-secondary">Ver Inventario Completo →</a>
            </div>
        </div>

        <div class="table-card">
            <div class="table-header">
                <h2 class="table-title">Actividad Reciente (Últimos Pedidos)</h2>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>ID Pedido</th>
                        <th>Fecha</th>
                        <th>Usuario (ID)</th>
                        <th>Total</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($ventas_recientes)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 2rem; color: #7a7a7a;">
                                No hay pedidos recientes.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($ventas_recientes as $venta): ?>
                        <tr>
                            <td>#<?php echo str_pad($venta['id'], 6, '0', STR_PAD_LEFT); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($venta['fecha'])); ?></td>
                            <td>ID: <?php echo $venta['usuario_id']; ?></td>
                            <td>$<?php echo number_format($venta['total'], 2); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $venta['estado']; ?>">
                                    <?php echo ucfirst($venta['estado']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <div style="padding: 1rem; text-align: center; border-top: 1px solid #eee;">
                <a href="lista_ventas.php" class="btn btn-secondary">Ver Todas las Ventas →</a>
            </div>
        </div>

    </main>

    <div class="modal" id="deleteModal">
        <div class="modal-content">
            <h3 class="modal-title">⚠️ Confirmar Eliminación</h3>
            <p class="modal-text" id="modalText">
                ¿Estás seguro de que deseas eliminar este usuario? Esta acción no se puede deshacer.
            </p>
            <div class="modal-buttons">
                <button class="btn-cancel" onclick="cerrarModal()">Cancelar</button>
                <button class="btn-confirm" onclick="eliminarUsuario()">Sí, eliminar</button>
            </div>
        </div>
    </div>

    <div class="notification" id="notification">
        <span id="notificationText"></span>
    </div>

    <script>
        let usuarioAEliminar = null;
        let filtroActual = '<?php echo $filtro_rol; ?>';

        window.addEventListener('DOMContentLoaded', function() {
            // Activar visualmente la tarjeta de filtro seleccionada
            if (filtroActual === '') {
                document.getElementById('card-total').classList.add('active');
            } else if (filtroActual === 'cliente') {
                document.getElementById('card-cliente').classList.add('active');
            } else if (filtroActual === 'operador') {
                document.getElementById('card-operador').classList.add('active');
            } else if (filtroActual === 'admin') {
                document.getElementById('card-admin').classList.add('active');
            }

            // Mensajes URL
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('success')) {
                mostrarNotificacion(decodeURIComponent(urlParams.get('success')), 'success');
                // Limpiar URL
                window.history.replaceState({}, document.title, 'principal_admin.php' + (filtroActual ? '?filtro_rol=' + filtroActual : ''));
            }
            if (urlParams.get('error')) {
                mostrarNotificacion(decodeURIComponent(urlParams.get('error')), 'error');
                window.history.replaceState({}, document.title, 'principal_admin.php' + (filtroActual ? '?filtro_rol=' + filtroActual : ''));
            }
        });

        function filtrarUsuarios(rol) {
            if (rol === '') {
                window.location.href = 'principal_admin.php';
            } else {
                window.location.href = 'principal_admin.php?filtro_rol=' + rol;
            }
        }

        function confirmarEliminacion(id, nombre) {
            usuarioAEliminar = id;
            document.getElementById('modalText').innerHTML = 
                `¿Estás seguro de que deseas eliminar al usuario <strong>${nombre}</strong>?<br><br>Esta acción no se puede deshacer (si tiene ventas, será anonimizado).`;
            document.getElementById('deleteModal').classList.add('active');
        }

        function cerrarModal() {
            document.getElementById('deleteModal').classList.remove('active');
            usuarioAEliminar = null;
        }

        function eliminarUsuario() {
            if (usuarioAEliminar) {
                window.location.href = 'admin_eliminar_usuario.php?id=' + usuarioAEliminar + 
                    (filtroActual ? '&filtro_rol=' + filtroActual : '');
            }
        }

        function mostrarNotificacion(mensaje, tipo) {
            const notification = document.getElementById('notification');
            const notificationText = document.getElementById('notificationText');
            
            notificationText.textContent = mensaje;
            notification.className = 'notification ' + tipo + ' show';
            
            setTimeout(() => {
                notification.classList.remove('show');
            }, 4000);
        }

        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) {
                cerrarModal();
            }
        });
    </script>
</body>
</html>