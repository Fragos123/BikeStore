<?php
session_start();
// 1. SEGURIDAD
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true || $_SESSION['usuario_rol'] !== 'operador') {
    header("Location: login.php");
    exit;
}

include 'conexion.php';
$nombre_operador = $_SESSION['usuario_nombre'];

// --- LÓGICA DE PRODUCTOS ---
$busqueda_prod = isset($_GET['q']) ? trim($_GET['q']) : '';
$where_prod = "WHERE stock > -1"; 
if ($busqueda_prod) {
    $where_prod .= " AND (nombre LIKE '%$busqueda_prod%' OR tipo LIKE '%$busqueda_prod%')";
}
$productos = $conn->query("SELECT * FROM productos $where_prod ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);

// --- LÓGICA DE CLIENTES ---
$busqueda_cli = isset($_GET['qc']) ? trim($_GET['qc']) : '';
$where_cli = "WHERE rol = 'cliente'";
if ($busqueda_cli) {
    $where_cli .= " AND (nombre LIKE '%$busqueda_cli%' OR correo LIKE '%$busqueda_cli%')";
}
$clientes = $conn->query("SELECT * FROM usuarios $where_cli ORDER BY fecha_registro DESC")->fetch_all(MYSQLI_ASSOC);

// --- ESTADÍSTICAS RÁPIDAS ---
$total_prod = $conn->query("SELECT COUNT(*) as c FROM productos WHERE stock > -1")->fetch_assoc()['c'];

// Calculamos stock bajo REAL (sumando tallas)
$stock_bajo = 0;
$res_todos = $conn->query("SELECT id, stock FROM productos WHERE stock > -1");
while($p = $res_todos->fetch_assoc()) {
    // Verificar tallas
    $res_tallas = $conn->query("SELECT SUM(stock) as total FROM producto_tallas WHERE producto_id = {$p['id']} AND activo = 1");
    $data_tallas = $res_tallas->fetch_assoc();
    
    $stock_real = ($data_tallas['total'] !== null) ? $data_tallas['total'] : $p['stock'];
    
    if($stock_real < 5) {
        $stock_bajo++;
    }
}

$total_clientes = $conn->query("SELECT COUNT(*) as c FROM usuarios WHERE rol = 'cliente'")->fetch_assoc()['c'];

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Operador - BikeStore</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #0f0f0f; color: #fff; min-height: 100vh; display: flex; }

        /* SIDEBAR */
        .sidebar { width: 260px; background: #1a1a1a; padding: 2rem 0; border-right: 1px solid #333; position: fixed; height: 100%; top: 0; left: 0; display: flex; flex-direction: column; z-index: 100; }
        .logo-area { padding: 0 2rem 2rem; border-bottom: 1px solid #333; margin-bottom: 2rem; }
        .logo { font-size: 1.5rem; font-weight: 900; color: #fff; text-decoration: none; letter-spacing: -1px; }
        .user-tag { font-size: 0.8rem; color: #888; margin-top: 0.5rem; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; }
        
        .nav-menu { list-style: none; flex: 1; }
        .nav-item { margin-bottom: 0.5rem; }
        .nav-link { display: flex; align-items: center; gap: 1rem; padding: 1rem 2rem; color: #888; text-decoration: none; font-weight: 500; transition: 0.3s; border-left: 3px solid transparent; }
        .nav-link:hover, .nav-link.active { color: #fff; background: rgba(255,255,255,0.05); border-left-color: #fff; }
        .nav-icon { width: 20px; text-align: center; }

        /* MAIN CONTENT */
        .main { margin-left: 260px; flex: 1; padding: 3rem; width: calc(100% - 260px); }
        
        .top-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 3rem; }
        .page-title { font-size: 2.5rem; font-weight: 800; margin: 0; letter-spacing: -1px; }
        .page-subtitle { color: #666; margin-top: 0.5rem; }

        /* STATS CARDS */
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; margin-bottom: 4rem; }
        .stat-card { background: #1a1a1a; border: 1px solid #333; border-radius: 12px; padding: 1.5rem; transition: transform 0.3s; }
        .stat-card:hover { transform: translateY(-5px); border-color: #555; }
        .stat-val { font-size: 2.5rem; font-weight: 800; margin-bottom: 0.5rem; line-height: 1; }
        .stat-label { color: #888; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 600; }

        /* TABLAS */
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; margin-top: 4rem; }
        .section-title { font-size: 1.5rem; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 10px; }
        
        .table-card { background: #1a1a1a; border: 1px solid #333; border-radius: 12px; overflow: hidden; }
        
        .toolbar { padding: 1.5rem; border-bottom: 1px solid #333; display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap; }
        .search-form { display: flex; gap: 0.5rem; flex: 1; max-width: 400px; }
        .search-input { background: #0f0f0f; border: 1px solid #333; color: #fff; padding: 0.8rem 1rem; border-radius: 6px; width: 100%; outline: none; }
        .search-input:focus { border-color: #666; }
        
        .btn { padding: 0.8rem 1.5rem; border-radius: 6px; font-weight: 700; text-decoration: none; font-size: 0.9rem; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem; transition: 0.3s; }
        .btn-primary { background: #fff; color: #000; }
        .btn-primary:hover { background: #ccc; }
        .btn-danger { background: rgba(239,68,68,0.1); color: #ef4444; border: 1px solid #ef4444; }
        .btn-danger:hover { background: #ef4444; color: #fff; }
        .btn-edit { background: #222; color: #fff; border: 1px solid #444; padding: 0.5rem 1rem; font-size: 0.8rem; }
        .btn-edit:hover { border-color: #fff; }

        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 1rem 1.5rem; color: #666; font-size: 0.8rem; text-transform: uppercase; font-weight: 700; border-bottom: 1px solid #333; background: #222; }
        td { padding: 1rem 1.5rem; border-bottom: 1px solid #222; color: #ddd; vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #222; }

        .img-thumb { width: 40px; height: 40px; border-radius: 4px; object-fit: cover; background: #333; }
        .badge { padding: 3px 8px; border-radius: 4px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; }
        .badge-ok { background: rgba(52, 211, 153, 0.1); color: #34d399; }
        .badge-low { background: rgba(251, 191, 36, 0.1); color: #fbbf24; }
        .badge-out { background: rgba(239, 68, 68, 0.1); color: #ef4444; }

        /* MODAL */
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); backdrop-filter: blur(5px); z-index: 2000; align-items: center; justify-content: center; }
        .modal.active { display: flex; }
        .modal-card { background: #1a1a1a; border: 1px solid #333; border-radius: 12px; padding: 2rem; max-width: 400px; width: 90%; text-align: center; }
        .modal-title { font-size: 1.5rem; font-weight: 700; margin-bottom: 1rem; }
        .modal-actions { display: flex; gap: 1rem; justify-content: center; margin-top: 2rem; }

        @media (max-width: 1024px) {
            .sidebar { width: 80px; }
            .logo-area, .user-tag, .nav-link span { display: none; }
            .nav-link { justify-content: center; padding: 1.5rem 0; }
            .main { margin-left: 80px; width: calc(100% - 80px); }
            .stats-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="logo-area">
            <a href="index.php" class="logo">BIKESTORE</a>
            <div class="user-tag">Operador: <?php echo strtoupper(explode(' ', $nombre_operador)[0]); ?></div>
        </div>
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="#productos" class="nav-link active" onclick="showSection('productos')">
                    <span class="nav-icon">🚲</span> <span>Inventario</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#clientes" class="nav-link" onclick="showSection('clientes')">
                    <span class="nav-icon">👥</span> <span>Clientes</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="lista_ventas.php" class="nav-link">
                    <span class="nav-icon">📈</span> <span>Gestionar Ventas</span>
                </a>
            </li>
            <li class="nav-item" style="margin-top: auto;">
                <a href="logout.php" class="nav-link" style="color: #ef4444;">
                    <span class="nav-icon">🚪</span> <span>Cerrar Sesión</span>
                </a>
            </li>
        </ul>
    </aside>

    <main class="main">
        <div class="top-header">
            <div>
                <h1 class="page-title">Panel de Control</h1>
                <p class="page-subtitle">Gestión de inventario y usuarios</p>
            </div>
            <a href="index.php" class="btn btn-edit" style="background:transparent;">Ir a la Tienda →</a>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-val"><?php echo $total_prod; ?></div>
                <div class="stat-label">Productos Totales</div>
            </div>
            <div class="stat-card">
                <div class="stat-val" style="color: #fbbf24;"><?php echo $stock_bajo; ?></div>
                <div class="stat-label">Stock Bajo / Agotado</div>
            </div>
            <div class="stat-card">
                <div class="stat-val"><?php echo $total_clientes; ?></div>
                <div class="stat-label">Clientes Registrados</div>
            </div>
        </div>

        <div id="sec-productos">
            <div class="section-header">
                <div class="section-title">🚲 Gestión de Inventario</div>
                <a href="operador_crear_producto.php" class="btn btn-primary">＋ Nuevo Producto</a>
            </div>

            <div class="table-card">
                <div class="toolbar">
                    <form action="" method="GET" class="search-form">
                        <input type="text" name="q" class="search-input" placeholder="Buscar producto por nombre o tipo..." value="<?php echo htmlspecialchars($busqueda_prod); ?>">
                        <button type="submit" class="btn btn-edit">🔍</button>
                        <?php if($busqueda_prod): ?><a href="principal_operador.php" class="btn btn-edit">✕</a><?php endif; ?>
                    </form>
                </div>
                <div style="overflow-x:auto;">
                    <table>
                        <thead>
                            <tr>
                                <th width="60">IMG</th>
                                <th>Nombre</th>
                                <th>Tipo</th>
                                <th>Precio</th>
                                <th>Stock Real</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($productos)): ?>
                                <tr><td colspan="6" style="text-align:center; padding:3rem; color:#666;">No se encontraron productos.</td></tr>
                            <?php else: ?>
                                <?php foreach($productos as $p): 
                                    // =============================================================
                                    // CORRECCIÓN STOCK REAL (Para mostrar suma de tallas o stock general)
                                    // =============================================================
                                    $stmt_stock = $conn->prepare("SELECT SUM(stock) as total FROM producto_tallas WHERE producto_id = ? AND activo = 1");
                                    $stmt_stock->bind_param("i", $p['id']);
                                    $stmt_stock->execute();
                                    $res_stock = $stmt_stock->get_result()->fetch_assoc();
                                    
                                    // Si hay tallas, usamos la suma. Si no, usamos el stock general.
                                    $stock_real = ($res_stock['total'] !== null) ? (int)$res_stock['total'] : (int)$p['stock'];
                                    $stmt_stock->close();
                                    // =============================================================
                                ?>
                                <tr>
                                    <td>
                                        <?php if($p['imagen_principal']): ?>
                                            <img src="<?php echo htmlspecialchars($p['imagen_principal']); ?>" class="img-thumb">
                                        <?php else: ?>
                                            <div class="img-thumb" style="display:flex;align-items:center;justify-content:center;">🚲</div>
                                        <?php endif; ?>
                                    </td>
                                    <td style="font-weight:700;"><?php echo htmlspecialchars($p['nombre']); ?></td>
                                    <td style="text-transform:uppercase; font-size:0.8rem; color:#aaa;"><?php echo $p['tipo']; ?></td>
                                    <td>$<?php echo number_format($p['precio'], 2); ?></td>
                                    <td>
                                        <?php 
                                        if($stock_real <= 0) echo '<span class="badge badge-out">Agotado (0)</span>';
                                        elseif($stock_real < 5) echo '<span class="badge badge-low">Bajo ('.$stock_real.')</span>';
                                        else echo '<span class="badge badge-ok">'.$stock_real.' unid.</span>';
                                        ?>
                                    </td>
                                    <td>
                                        <div style="display:flex; gap:0.5rem;">
                                            <a href="operador_editar_producto.php?id=<?php echo $p['id']; ?>" class="btn-edit" title="Editar">✏️</a>
                                            <a href="operador_eliminar_producto.php?id=<?php echo $p['id']; ?>" class="btn-edit" style="color:#ef4444; border-color:#ef4444;" onclick="return confirm('¿Eliminar este producto?')" title="Eliminar">🗑️</a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="sec-clientes" style="display:none;">
            <div class="section-header">
                <div class="section-title">👥 Gestión de Clientes</div>
            </div>

            <div class="table-card">
                <div class="toolbar">
                    <form action="" method="GET" class="search-form">
                        <input type="text" name="qc" class="search-input" placeholder="Buscar cliente por nombre o correo..." value="<?php echo htmlspecialchars($busqueda_cli); ?>">
                        <input type="hidden" name="tab" value="clientes">
                        <button type="submit" class="btn btn-edit">🔍</button>
                        <?php if($busqueda_cli): ?><a href="principal_operador.php?tab=clientes" class="btn btn-edit">✕</a><?php endif; ?>
                    </form>
                </div>
                <div style="overflow-x:auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Correo</th>
                                <th>Nivel</th>
                                <th>Registro</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($clientes)): ?>
                                <tr><td colspan="6" style="text-align:center; padding:3rem; color:#666;">No se encontraron clientes.</td></tr>
                            <?php else: ?>
                                <?php foreach($clientes as $c): ?>
                                <tr>
                                    <td>#<?php echo str_pad($c['id'], 4, '0', STR_PAD_LEFT); ?></td>
                                    <td style="font-weight:700; color:#fff;"><?php echo htmlspecialchars($c['nombre']); ?></td>
                                    <td style="color:#aaa;"><?php echo htmlspecialchars($c['correo']); ?></td>
                                    <td><span style="background:#222; padding:2px 8px; border-radius:4px; font-size:0.8rem;"><?php echo ucfirst($c['nivel_ciclismo']); ?></span></td>
                                    <td><?php echo date('d/m/Y', strtotime($c['fecha_registro'])); ?></td>
                                    <td>
                                        <button onclick="confirmarEliminarCliente(<?php echo $c['id']; ?>, '<?php echo htmlspecialchars($c['nombre']); ?>')" class="btn-edit" style="color:#ef4444; border-color:#ef4444;">Eliminar 🗑️</button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </main>

    <div class="modal" id="modalEliminar">
        <div class="modal-card">
            <div style="font-size:3rem; margin-bottom:1rem;">⚠️</div>
            <div class="modal-title">¿Eliminar Cliente?</div>
            <p id="modalMsg" style="color:#aaa; margin-bottom:1rem;"></p>
            <p style="font-size:0.8rem; color:#666;">Esta acción borrará sus pedidos y datos. No se puede deshacer.</p>
            
            <div class="modal-actions">
                <button onclick="cerrarModal()" class="btn btn-edit">Cancelar</button>
                <a href="#" id="btnConfirmarEliminar" class="btn btn-danger">Sí, Eliminar</a>
            </div>
        </div>
    </div>

    <script>
        function showSection(sec) {
            document.getElementById('sec-productos').style.display = sec === 'productos' ? 'block' : 'none';
            document.getElementById('sec-clientes').style.display = sec === 'clientes' ? 'block' : 'none';
            
            // Actualizar sidebar
            document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
            if(sec === 'productos') document.querySelector('a[href="#productos"]').classList.add('active');
            if(sec === 'clientes') document.querySelector('a[href="#clientes"]').classList.add('active');
        }

        // Mantener pestaña activa tras recargar (búsqueda)
        const params = new URLSearchParams(window.location.search);
        if(params.get('tab') === 'clientes' || params.get('qc')) {
            showSection('clientes');
        } else {
            showSection('productos');
        }

        // Modal Logic
        function confirmarEliminarCliente(id, nombre) {
            document.getElementById('modalMsg').innerText = `Vas a eliminar al cliente: ${nombre}`;
            document.getElementById('btnConfirmarEliminar').href = `operador_eliminar_cliente.php?id=${id}`;
            document.getElementById('modalEliminar').classList.add('active');
        }

        function cerrarModal() {
            document.getElementById('modalEliminar').classList.remove('active');
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>