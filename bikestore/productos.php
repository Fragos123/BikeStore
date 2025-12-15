<?php
session_start();
include 'conexion.php';

// --- PAGINACIÓN Y FILTROS ---
$por_pagina = 12; 
$pagina = isset($_GET['p']) ? (int)$_GET['p'] : 1;
if ($pagina < 1) $pagina = 1;
$inicio = ($pagina - 1) * $por_pagina;

// Construcción de la consulta
$where = "WHERE stock > -1"; 
$params = [];
$types = "";

// Filtro por Búsqueda
if (isset($_GET['q']) && !empty($_GET['q'])) {
    $q = trim($_GET['q']);
    $where .= " AND (nombre LIKE ? OR descripcion LIKE ?)";
    $params[] = "%$q%";
    $params[] = "%$q%";
    $types .= "ss";
}

// Filtro por Categoría
if (isset($_GET['tipo']) && !empty($_GET['tipo'])) {
    $tipo = trim($_GET['tipo']);
    $where .= " AND tipo = ?";
    $params[] = $tipo;
    $types .= "s";
}

// Filtro por Precio
$orden = "id DESC"; 
if (isset($_GET['orden'])) {
    if ($_GET['orden'] == 'precio_asc') $orden = "precio ASC";
    if ($_GET['orden'] == 'precio_desc') $orden = "precio DESC";
    if ($_GET['orden'] == 'nombre') $orden = "nombre ASC";
}

// 1. Obtener Total
$sql_total = "SELECT COUNT(*) as total FROM productos $where";
$stmt = $conn->prepare($sql_total);
if (!empty($params)) $stmt->bind_param($types, ...$params);
$stmt->execute();
$total_registros = $stmt->get_result()->fetch_assoc()['total'];
$total_paginas = ceil($total_registros / $por_pagina);
$stmt->close();

// 2. Obtener Productos
$sql_prod = "SELECT * FROM productos $where ORDER BY $orden LIMIT ?, ?";
$stmt = $conn->prepare($sql_prod);

$params[] = $inicio;
$params[] = $por_pagina;
$types .= "ii";

$stmt->bind_param($types, ...$params);
$stmt->execute();
$productos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Datos usuario para navbar
$logueado = isset($_SESSION['logueado']) && $_SESSION['logueado'] === true;
$nombre_usuario = $logueado ? $_SESSION['usuario_nombre'] : '';
$usuario_rol = $logueado ? $_SESSION['usuario_rol'] : '';

// Contador carrito
$cart_count = 0;
if ($logueado) {
    $stmt = $conn->prepare("SELECT SUM(cantidad) as total FROM carrito WHERE usuario_id = ?");
    $stmt->bind_param("i", $_SESSION['usuario_id']);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $cart_count = $res['total'] ? (int)$res['total'] : 0;
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo - BikeStore</title>
    <style>
        /* BASE DARK */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #0f0f0f;
            color: #fff;
            line-height: 1.5;
        }
        
        /* NAVBAR */
        .navbar {
            position: fixed; top: 0; left: 0; width: 100%; padding: 1.2rem 3rem;
            display: flex; justify-content: space-between; align-items: center;
            z-index: 1000; background: rgba(10, 10, 10, 0.95);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
        }
        .logo { font-size: 1.5rem; font-weight: 900; color: white; text-decoration: none; letter-spacing: -1px; text-transform: uppercase; }
        .nav-links a { color: #ccc; text-decoration: none; font-weight: 600; margin-left: 20px; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; transition: 0.2s; }
        .nav-links a:hover { color: white; }
        .icon-btn { color: white; text-decoration: none; position: relative; margin-left: 1.5rem; font-size: 1.2rem; }
        
        .cart-badge {
            position: absolute; top: -5px; right: -8px; background: #fff; color: #000;
            font-size: 0.6rem; font-weight: 800; padding: 2px 6px; border-radius: 10px;
        }

        /* CONTAINER */
        .container { max-width: 1400px; margin: 0 auto; padding: 8rem 2rem 4rem; }
        
        /* HEADER PAGINA */
        .page-header { text-align: center; margin-bottom: 4rem; }
        .page-title { font-size: 3rem; font-weight: 900; letter-spacing: -2px; margin-bottom: 0.5rem; text-transform: uppercase; }
        .page-subtitle { color: #888; font-size: 1.1rem; font-weight: 300; }

        /* BARRA DE FILTROS DARK */
        .filter-bar { 
            display: flex; justify-content: space-between; align-items: center; 
            margin-bottom: 4rem; padding-bottom: 1.5rem; border-bottom: 1px solid #222; flex-wrap: wrap; gap: 1rem; 
        }
        
        .search-group { position: relative; width: 100%; max-width: 400px; }
        .search-input { 
            padding: 0.8rem 0 0.8rem 2rem; border: none; border-bottom: 1px solid #444;
            width: 100%; font-size: 1rem; transition: all 0.3s; background: transparent; color: white; border-radius: 0;
        }
        .search-input:focus { outline: none; border-color: #fff; }
        .search-icon { position: absolute; left: 0; top: 50%; transform: translateY(-50%); color: #666; font-size: 1rem; }
        
        .filter-group { display: flex; gap: 1rem; }
        .filter-select { 
            padding: 0.8rem 2rem 0.8rem 1rem; border: 1px solid #333; border-radius: 4px; 
            background: #1a1a1a; font-size: 0.85rem; color: #ddd; cursor: pointer; 
            transition: 0.2s; appearance: none; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px;
        }
        .filter-select:hover { border-color: #666; color: white; }
        .filter-select:focus { outline: none; border-color: #fff; }

        /* GRID DE PRODUCTOS */
        .products-grid { 
            display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); 
            gap: 2rem; row-gap: 4rem; 
        }
        
        /* TARJETA DARK */
        .product-card { 
            background: #1a1a1a; border-radius: 4px; overflow: hidden; 
            transition: all 0.4s ease; text-decoration: none; color: inherit; 
            position: relative; display: flex; flex-direction: column; border: 1px solid #222;
        }
        
        .product-card:hover { transform: translateY(-5px); border-color: #333; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        
        .img-wrapper { 
            position: relative; aspect-ratio: 1; overflow: hidden; background: #222;
        }
        
        .product-img { 
            width: 100%; height: 100%; object-fit: cover; 
            transition: transform 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94); 
            opacity: 0.9;
        }
        
        .product-card:hover .product-img { transform: scale(1.05); opacity: 1; }
        
        .card-body { padding: 1.5rem; }
        
        .product-cat { 
            font-size: 0.7rem; text-transform: uppercase; letter-spacing: 2px; 
            color: #666; font-weight: 700; margin-bottom: 0.5rem; 
        }
        
        .product-name { 
            font-size: 1.2rem; font-weight: 700; color: #fff; 
            margin-bottom: 0.5rem; line-height: 1.2; 
        }
        
        .product-price { font-size: 1rem; font-weight: 400; color: #ccc; }
        
        /* Badges */
        .badge { 
            position: absolute; top: 12px; left: 12px; padding: 4px 8px; 
            background: #fff; color: #000; font-size: 0.65rem; font-weight: 800; 
            text-transform: uppercase; letter-spacing: 1px; z-index: 2; border-radius: 2px;
        }
        
        /* Stock indicator */
        .stock-dot { height: 6px; width: 6px; border-radius: 50%; display: inline-block; margin-right: 6px; }
        .dot-green { background-color: #10b981; }
        .dot-red { background-color: #ef4444; }
        .stock-text { font-size: 0.75rem; color: #666; margin-top: 10px; display: flex; align-items: center; text-transform: uppercase; letter-spacing: 1px; font-weight: 600; }

        /* PAGINACIÓN DARK */
        .pagination { display: flex; justify-content: center; gap: 0.5rem; margin-top: 6rem; }
        .page-link { 
            width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;
            border: 1px solid #333; text-decoration: none; color: #888; font-weight: 600; transition: 0.2s; border-radius: 2px;
        }
        .page-link:hover { border-color: #fff; color: #fff; }
        .page-link.active { background: #fff; color: #000; border-color: #fff; }

        /* Scroll Top */
        #btnScrollTop {
            display: none; position: fixed; bottom: 30px; right: 30px; z-index: 999;
            width: 50px; height: 50px; border: 1px solid #333; background-color: #111; color: white;
            cursor: pointer; border-radius: 50%; transition: transform 0.3s; font-size: 1.2rem;
        }
        #btnScrollTop:hover { transform: translateY(-5px); border-color: white; }

        /* MENSAJE VACÍO */
        .empty-msg {
            grid-column: 1/-1; text-align: center; padding: 6rem 0; color: #666;
        }
        .empty-icon { font-size: 4rem; margin-bottom: 1rem; opacity: 0.3; }

        @media (max-width: 768px) {
            .filter-bar { flex-direction: column; align-items: stretch; gap: 1.5rem; }
            .search-group { max-width: 100%; }
            .products-grid { grid-template-columns: repeat(2, 1fr); gap: 1rem; }
        }
        @media (max-width: 480px) {
            .products-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="logo">BIKESTORE</a>
        <div class="nav-links">
            <a href="productos.php">Catálogo</a>
            <?php if($logueado): ?>
                <a href="perfil.php">Mi Cuenta</a>
            <?php else: ?>
                <a href="login.php">Ingresar</a>
            <?php endif; ?>
            <a href="carrito.php" class="icon-btn">
                🛒
                <?php if($cart_count > 0): ?><span class="cart-badge"><?php echo $cart_count; ?></span><?php endif; ?>
            </a>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Colección</h1>
            <p class="page-subtitle">Ingeniería, diseño y rendimiento en cada modelo.</p>
        </div>

        <form action="" method="GET" class="filter-bar">
            <div class="search-group">
                <span class="search-icon">🔍</span>
                <input type="text" name="q" class="search-input" placeholder="BUSCAR MODELO..." value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
            </div>
            
            <div class="filter-group">
                <select name="tipo" class="filter-select" onchange="this.form.submit()">
                    <option value="">Todas las categorías</option>
                    <option value="montaña" <?php echo (isset($_GET['tipo']) && $_GET['tipo'] == 'montaña') ? 'selected' : ''; ?>>Montaña</option>
                    <option value="ruta" <?php echo (isset($_GET['tipo']) && $_GET['tipo'] == 'ruta') ? 'selected' : ''; ?>>Ruta</option>
                    <option value="urbana" <?php echo (isset($_GET['tipo']) && $_GET['tipo'] == 'urbana') ? 'selected' : ''; ?>>Urbana</option>
                    <option value="eléctrica" <?php echo (isset($_GET['tipo']) && $_GET['tipo'] == 'eléctrica') ? 'selected' : ''; ?>>Eléctrica</option>
                </select>

                <select name="orden" class="filter-select" onchange="this.form.submit()">
                    <option value="id_desc" <?php echo (!isset($_GET['orden']) || $_GET['orden'] == 'id_desc') ? 'selected' : ''; ?>>Más recientes</option>
                    <option value="precio_asc" <?php echo (isset($_GET['orden']) && $_GET['orden'] == 'precio_asc') ? 'selected' : ''; ?>>Precio: Bajo a Alto</option>
                    <option value="precio_desc" <?php echo (isset($_GET['orden']) && $_GET['orden'] == 'precio_desc') ? 'selected' : ''; ?>>Precio: Alto a Bajo</option>
                    <option value="nombre" <?php echo (isset($_GET['orden']) && $_GET['orden'] == 'nombre') ? 'selected' : ''; ?>>Nombre (A-Z)</option>
                </select>
            </div>
        </form>

        <div class="products-grid">
            <?php if (empty($productos)): ?>
                <div class="empty-msg">
                    <div class="empty-icon">🚲</div>
                    <h3>No se encontraron resultados.</h3>
                    <a href="productos.php" style="color:white; margin-top:1rem; display:inline-block;">Ver todo el catálogo</a>
                </div>
            <?php else: ?>
                <?php foreach ($productos as $p): 
                    // =========================================================
                    // CÁLCULO DE STOCK REAL (Para evitar "disponibles fantasma")
                    // =========================================================
                    
                    // Verificamos si tiene tallas configuradas
                    $stock_real = 0;
                    $tiene_tallas = false;
                    
                    $sql_stock = "SELECT SUM(stock) as total FROM producto_tallas WHERE producto_id = " . $p['id'] . " AND activo = 1";
                    $res_stock = $conn->query($sql_stock);
                    $fila_stock = $res_stock->fetch_assoc();
                    
                    if ($fila_stock['total'] !== null) {
                        $stock_real = (int)$fila_stock['total'];
                        $tiene_tallas = true;
                    } else {
                        // Si no tiene tallas, usamos el stock general
                        $stock_real = (int)$p['stock'];
                    }
                    
                    $agotado = $stock_real <= 0;
                    // =========================================================
                ?>
                <a href="producto_detalle.php?id=<?php echo $p['id']; ?>" class="product-card">
                    <div class="img-wrapper">
                        <?php if(!$agotado && strtotime($p['fecha_creacion']) > strtotime('-30 days')): ?>
                            <span class="badge">NUEVO</span>
                        <?php endif; ?>
                        
                        <?php if (!empty($p['imagen_principal'])): ?>
                            <img src="<?php echo htmlspecialchars($p['imagen_principal']); ?>" alt="<?php echo htmlspecialchars($p['nombre']); ?>" class="product-img">
                        <?php else: ?>
                            <div style="display:flex; align-items:center; justify-content:center; height:100%; color:#333; font-size:3rem; background:#111;">🚲</div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="card-body">
                        <div class="product-cat"><?php echo ucfirst($p['tipo']); ?></div>
                        <div class="product-name"><?php echo htmlspecialchars($p['nombre']); ?></div>
                        <div class="product-price">$<?php echo number_format($p['precio'], 2); ?></div>
                        
                        <div class="stock-text">
                            <span class="stock-dot <?php echo $agotado ? 'dot-red' : 'dot-green'; ?>"></span>
                            <?php echo $agotado ? 'AGOTADO' : 'DISPONIBLE'; ?>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if ($total_paginas > 1): ?>
        <div class="pagination">
            <?php 
            $query_params = $_GET; 
            unset($query_params['p']);
            $query_str = http_build_query($query_params);
            $link_base = "?$query_str&p=";
            
            if ($pagina > 1): ?>
                <a href="<?php echo $link_base . ($pagina-1); ?>" class="page-link">←</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                <a href="<?php echo $link_base . $i; ?>" class="page-link <?php echo ($i === $pagina) ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>

            <?php if ($pagina < $total_paginas): ?>
                <a href="<?php echo $link_base . ($pagina+1); ?>" class="page-link">→</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <button id="btnScrollTop" onclick="window.scrollTo({top: 0, behavior: 'smooth'});">↑</button>

    <script>
        window.onscroll = function() {
            const btn = document.getElementById("btnScrollTop");
            if (document.body.scrollTop > 300 || document.documentElement.scrollTop > 300) {
                btn.style.display = "block";
            } else {
                btn.style.display = "none";
            }
        };
    </script>
</body>
</html>