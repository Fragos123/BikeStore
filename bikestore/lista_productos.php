<?php
session_start();
// lista_productos.php

// 1. SEGURIDAD: Solo Admin y Operador
if (!isset($_SESSION['logueado']) || !in_array($_SESSION['usuario_rol'], ['admin', 'operador'])) {
    header("Location: login.php");
    exit;
}

include 'conexion.php';

// 2. CONFIGURACIÓN DE BÚSQUEDA Y FILTROS
$busqueda = isset($_GET['q']) ? trim($_GET['q']) : '';
$where_clause = "WHERE stock > -1"; // Mostrar activos (y agotados, pero no eliminados)
$params = [];
$types = "";

if (!empty($busqueda)) {
    // Busca por nombre, tipo o ID
    $where_clause .= " AND (nombre LIKE ? OR tipo LIKE ? OR id = ?)";
    $term = "%" . $busqueda . "%";
    
    // Parámetros para bind_param
    $params[] = $term; // Para nombre
    $params[] = $term; // Para tipo
    $params[] = $busqueda; // Para ID (exacto o parcial según prefieras, aquí parcial)
    $types .= "sss";
}

// 3. PAGINACIÓN
$registros_por_pagina = 10;
$pagina = isset($_GET['p']) ? (int)$_GET['p'] : 1;
if ($pagina < 1) $pagina = 1;

// Si hay búsqueda, calcular el total de resultados FILTRADOS
$sql_count = "SELECT COUNT(*) as total FROM productos $where_clause";
$stmt_count = $conn->prepare($sql_count);

if (!empty($params)) {
    $stmt_count->bind_param($types, ...$params);
}

$stmt_count->execute();
$total_registros = $stmt_count->get_result()->fetch_assoc()['total'];
$stmt_count->close();

// Calcular paginación real
$total_paginas = ceil($total_registros / $registros_por_pagina);
if ($pagina > $total_paginas && $total_paginas > 0) $pagina = 1; // Prevenir página vacía
$offset = ($pagina - 1) * $registros_por_pagina;

// 4. OBTENER PRODUCTOS
$sql = "SELECT * FROM productos $where_clause ORDER BY id DESC LIMIT ?, ?";
$stmt = $conn->prepare($sql);

// Añadir parámetros de límite
$params[] = $offset;
$params[] = $registros_por_pagina;
$types .= "ii";

$stmt->bind_param($types, ...$params);
$stmt->execute();
$productos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

// Link de regreso según rol
$link_volver = ($_SESSION['usuario_rol'] === 'admin') ? 'principal_admin.php' : 'principal_operador.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inventario Completo</title>
    <style>
        body { font-family: -apple-system, sans-serif; background: #f8f7f4; padding: 2rem; color: #333; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem; }
        .page-title { font-size: 1.5rem; font-weight: 800; color: #2c3e50; margin: 0; }
        .back-link { color: #666; text-decoration: none; display: block; margin-top: 5px; }
        
        .search-form { display: flex; gap: 0.5rem; }
        .search-input { padding: 0.7rem; border: 1px solid #ddd; border-radius: 8px; width: 300px; font-size: 1rem; }
        .btn { padding: 0.7rem 1.2rem; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; transition: 0.2s; font-size: 0.9rem; }
        .btn-dark { background: #2c3e50; color: white; }
        .btn-dark:hover { background: #1a252f; }
        .btn-outline { background: white; border: 1px solid #ccc; color: #333; }
        .btn-outline:hover { background: #f5f5f5; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { padding: 1rem; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f9fafb; color: #64748b; font-size: 0.85rem; text-transform: uppercase; font-weight: 700; }
        tr:hover { background: #f8f9fa; }
        
        .product-img { width: 50px; height: 50px; object-fit: cover; border-radius: 6px; background: #eee; }
        .badge { padding: 0.3rem 0.6rem; border-radius: 4px; font-size: 0.75rem; font-weight: 700; }
        .bg-cat { background: #e2e8f0; color: #475569; }
        .stock-ok { background: #dcfce7; color: #166534; }
        .stock-low { background: #fef9c3; color: #854d0e; }
        .stock-out { background: #fee2e2; color: #991b1b; }
        
        .actions { display: flex; gap: 0.5rem; }
        .btn-sm { padding: 0.4rem 0.8rem; font-size: 0.8rem; }
        .btn-edit { background: #e0f2fe; color: #0284c7; }
        .btn-delete { background: #fee2e2; color: #dc2626; }
        
        .pagination { display: flex; gap: 0.5rem; justify-content: center; margin-top: 2rem; }
        .page-link { padding: 0.5rem 1rem; border: 1px solid #ddd; border-radius: 6px; text-decoration: none; color: #333; }
        .page-link.active { background: #2c3e50; color: white; border-color: #2c3e50; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1 class="page-title">📦 Inventario Completo</h1>
                <a href="<?php echo $link_volver; ?>" class="back-link">← Volver al Panel</a>
            </div>
            
            <div style="display:flex; gap:10px;">
                <form action="" method="GET" class="search-form">
                    <input type="text" name="q" class="search-input" placeholder="Buscar por nombre, tipo o ID..." value="<?php echo htmlspecialchars($busqueda); ?>">
                    <button type="submit" class="btn btn-dark">🔍 Buscar</button>
                    <?php if($busqueda): ?>
                        <a href="lista_productos.php" class="btn btn-outline">✕</a>
                    <?php endif; ?>
                </form>
                <a href="operador_crear_producto.php" class="btn btn-dark">➕ Nuevo</a>
            </div>
        </div>

        <?php if (empty($productos)): ?>
            <div style="text-align:center; padding:3rem; color:#666;">
                <p style="font-size:1.2rem;">No se encontraron productos.</p>
                <?php if($busqueda): ?>
                    <p>Intenta con otra palabra o <a href="lista_productos.php">ver todos</a>.</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div style="overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th width="70">Img</th>
                            <th>ID</th>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Precio</th>
                            <th>Stock</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($productos as $p): ?>
                        <tr>
                            <td>
                                <?php if($p['imagen_principal']): ?>
                                    <img src="<?php echo htmlspecialchars($p['imagen_principal']); ?>" class="product-img">
                                <?php else: ?>
                                    <div class="product-img" style="display:flex;align-items:center;justify-content:center;">🚲</div>
                                <?php endif; ?>
                            </td>
                            <td>#<?php echo $p['id']; ?></td>
                            <td>
                                <strong style="color:#2c3e50;"><?php echo htmlspecialchars($p['nombre']); ?></strong>
                            </td>
                            <td><span class="badge bg-cat"><?php echo ucfirst($p['tipo']); ?></span></td>
                            <td>$<?php echo number_format($p['precio'], 2); ?></td>
                            <td>
                                <?php 
                                $s = $p['stock'];
                                if($s <= 0) echo '<span class="badge stock-out">Agotado</span>';
                                elseif($s <= 5) echo '<span class="badge stock-low">'.$s.' Bajo</span>';
                                else echo '<span class="badge stock-ok">'.$s.' Disp.</span>';
                                ?>
                            </td>
                            <td class="actions">
                                <a href="operador_editar_producto.php?id=<?php echo $p['id']; ?>" class="btn btn-sm btn-edit">✏️</a>
                                <a href="operador_eliminar_producto.php?id=<?php echo $p['id']; ?>" class="btn btn-sm btn-delete" onclick="return confirm('¿Eliminar este producto?')">🗑️</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if($total_paginas > 1): ?>
            <div class="pagination">
                <?php for($i=1; $i<=$total_paginas; $i++): ?>
                    <a href="?p=<?php echo $i; ?>&q=<?php echo urlencode($busqueda); ?>" class="page-link <?php echo $i==$pagina?'active':''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>