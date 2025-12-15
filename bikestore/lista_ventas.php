<?php
session_start();
// 1. SEGURIDAD: Permitir Admin Y Operador
if (!isset($_SESSION['logueado']) || !in_array($_SESSION['usuario_rol'], ['admin', 'operador'])) {
    header("Location: login.php");
    exit;
}
include 'conexion.php';

// Paginación
$por_pagina = 15;
$pagina = isset($_GET['p']) ? (int)$_GET['p'] : 1;
if($pagina < 1) $pagina = 1;
$inicio = ($pagina - 1) * $por_pagina;

// Búsqueda y Filtros
$busqueda = isset($_GET['q']) ? trim($_GET['q']) : '';
$filtro_fecha = isset($_GET['fecha']) ? $_GET['fecha'] : ''; // Nuevo filtro de fecha

$where = "WHERE 1=1"; 

if($busqueda) {
    $where .= " AND (p.id LIKE '%$busqueda%' OR u.nombre LIKE '%$busqueda%')";
}

// Filtro rápido de fecha (Requerimiento: Consultar ventas del día)
if ($filtro_fecha === 'hoy') {
    $where .= " AND DATE(p.fecha) = CURDATE()";
} elseif (!empty($filtro_fecha)) {
    $fecha_safe = $conn->real_escape_string($filtro_fecha);
    $where .= " AND DATE(p.fecha) = '$fecha_safe'";
}

// Total registros
$sql_count = "SELECT COUNT(*) as c FROM pedidos p LEFT JOIN usuarios u ON p.usuario_id = u.id $where";
$total = $conn->query($sql_count)->fetch_assoc()['c'];
$paginas = ceil($total / $por_pagina);

// Consulta Principal
$sql = "
    SELECT p.*, u.nombre as cliente 
    FROM pedidos p 
    LEFT JOIN usuarios u ON p.usuario_id = u.id 
    $where 
    ORDER BY p.fecha DESC 
    LIMIT $inicio, $por_pagina
";
$ventas = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
$conn->close();

// Definir botón volver según rol
$link_volver = ($_SESSION['usuario_rol'] === 'admin') ? 'principal_admin.php' : 'principal_operador.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Ventas</title>
    <style>
        body { font-family: -apple-system, sans-serif; background: #f8f7f4; padding: 2rem; color: #333; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem; }
        .search-input { padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px; }
        .btn { padding: 0.5rem 1rem; background: #2c2c2c; color: white; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; font-size: 0.9rem; }
        .btn-filter { background: #e2e8f0; color: #333; }
        .btn-filter.active { background: #3b82f6; color: white; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { padding: 1rem; border-bottom: 1px solid #eee; text-align: left; }
        th { background: #fafaf9; }
        
        .badge { padding: 0.3rem 0.6rem; border-radius: 4px; font-size: 0.8rem; font-weight: bold; display: inline-block; }
        .estado-pendiente { background: #fef3c7; color: #92400e; }
        .estado-procesando { background: #dbeafe; color: #1e40af; }
        .estado-enviado { background: #e0f2fe; color: #0369a1; }
        .estado-entregado { background: #dcfce7; color: #166534; }
        .estado-cancelado { background: #fee2e2; color: #991b1b; }
        
        /* Select de estado embebido */
        .status-select { padding: 5px; border-radius: 4px; border: 1px solid #ddd; background: white; font-size: 0.85rem; cursor: pointer; }
        .pagination { display: flex; gap: 0.5rem; justify-content: center; margin-top: 2rem; }
        .page-link { padding: 0.5rem 1rem; border: 1px solid #ccc; text-decoration: none; color: #333; }
        .page-link.active { background: #2c2c2c; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1>Gestión de Pedidos</h1>
                <a href="<?php echo $link_volver; ?>" style="color:#666; text-decoration:none;">← Volver al Panel</a>
            </div>
            
            <div style="display:flex; gap:0.5rem; align-items:center; flex-wrap:wrap;">
                <a href="lista_ventas.php" class="btn btn-filter <?php echo $filtro_fecha==''?'active':''; ?>">Todos</a>
                <a href="lista_ventas.php?fecha=hoy" class="btn btn-filter <?php echo $filtro_fecha=='hoy'?'active':''; ?>">📅 Hoy</a>
                
                <form action="" method="GET" style="display:flex; gap:0.5rem;">
                    <?php if($filtro_fecha): ?><input type="hidden" name="fecha" value="<?php echo $filtro_fecha; ?>"><?php endif; ?>
                    <input type="text" name="q" class="search-input" placeholder="Buscar ID o Cliente..." value="<?php echo htmlspecialchars($busqueda); ?>">
                    <button type="submit" class="btn">🔍</button>
                </form>
            </div>
        </div>

        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Total</th>
                        <th>Estado Actual</th>
                        <th>Acciones / Cambiar Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($ventas)): ?>
                        <tr><td colspan="6" style="text-align:center; padding:2rem;">No hay ventas registradas con estos filtros.</td></tr>
                    <?php else: ?>
                        <?php foreach($ventas as $v): ?>
                        <tr>
                            <td>#<?php echo str_pad($v['id'], 6, '0', STR_PAD_LEFT); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($v['fecha'])); ?></td>
                            <td><?php echo htmlspecialchars($v['cliente'] ?: 'Usuario Eliminado'); ?></td>
                            <td>$<?php echo number_format($v['total'], 2); ?></td>
                            <td>
                                <span class="badge estado-<?php echo $v['estado']; ?>">
                                    <?php echo ucfirst($v['estado']); ?>
                                </span>
                            </td>
                            <td>
                                <div style="display:flex; gap: 10px; align-items: center;">
                                    <a href="detalle_pedido.php?id=<?php echo $v['id']; ?>" class="btn" style="padding:0.3rem 0.8rem; background:#64748b;">Ver</a>
                                    
                                    <form action="cambiar_estado_pedido.php" method="POST" style="margin:0;">
                                        <input type="hidden" name="pedido_id" value="<?php echo $v['id']; ?>">
                                        <select name="nuevo_estado" class="status-select" onchange="this.form.submit()">
                                            <option value="" disabled selected>Cambiar...</option>
                                            <option value="pendiente">Pendiente</option>
                                            <option value="procesando">Procesando</option>
                                            <option value="enviado">Enviado</option>
                                            <option value="entregado">Entregado</option>
                                            <option value="cancelado">Cancelado</option>
                                        </select>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if($paginas > 1): ?>
        <div class="pagination">
            <?php 
            $url_base = "?";
            if($busqueda) $url_base .= "q=$busqueda&";
            if($filtro_fecha) $url_base .= "fecha=$filtro_fecha&";
            ?>
            <?php for($i=1; $i<=$paginas; $i++): ?>
                <a href="<?php echo $url_base; ?>p=<?php echo $i; ?>" class="page-link <?php echo $i==$pagina?'active':''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>