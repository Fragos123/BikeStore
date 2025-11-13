<?php
include 'conexion.php';
//producto.php
// Obtener filtros de la URL
$tipo_filter = isset($_GET['tipo']) ? $_GET['tipo'] : '';
$talla_filter = isset($_GET['talla']) ? $_GET['talla'] : '';

// Construir consulta SQL con filtros
$sql = "SELECT * FROM productos WHERE 1=1";
$params = [];
$types = "";

if (!empty($tipo_filter)) {
    $sql .= " AND tipo LIKE ?";
    $params[] = "%$tipo_filter%";
    $types .= "s";
}

if (!empty($talla_filter)) {
    $sql .= " AND talla LIKE ?";
    $params[] = "%$talla_filter%";
    $types .= "s";
}

$sql .= " ORDER BY id DESC";

// Preparar y ejecutar consulta
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos - BikeStore</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #f8fafb;
            color: #2d3748;
        }

        /* Header simplificado */
        .header {
            background: linear-gradient(135deg, #f7f9fc 0%, #eceff4 100%);
            padding: 1rem 2rem;
            border-bottom: 1px solid #e1e7ed;
            position: sticky;
            top: 0;
            z-index: 100;
            backdrop-filter: blur(10px);
        }

        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 900;
            color: #495057;
            text-decoration: none;
            letter-spacing: -0.5px;
        }

        .back-link {
            color: #6c757d;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .back-link:hover {
            color: #495057;
        }

        /* Filters Header */
        .filters-header {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafb 100%);
            padding: 1.5rem 2rem;
            border-bottom: 1px solid #e9ecef;
        }

        .filters-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .results-count {
            font-size: 1.1rem;
            font-weight: 600;
            color: #495057;
        }

        .filter-buttons {
            display: flex;
            gap: 1rem;
        }

        .filter-btn {
            padding: 0.75rem 1.5rem;
            border: 2px solid #e9ecef;
            background: white;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #6c757d;
        }

        .filter-btn:hover {
            border-color: #adb5bd;
            color: #495057;
            background: #f8f9fa;
        }

        .filter-btn.active {
            background: linear-gradient(135deg, #495057 0%, #6c757d 100%);
            color: white;
            border-color: #495057;
        }

        /* Main Content */
        .main-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            display: flex;
            gap: 2rem;
        }

        /* Sidebar Filters */
        .sidebar {
            width: 300px;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border-radius: 20px;
            padding: 2rem;
            height: fit-content;
            box-shadow: 0 8px 32px rgba(73, 80, 87, 0.08);
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .sidebar.hidden {
            transform: translateX(-100%);
            opacity: 0;
            pointer-events: none;
        }

        .sidebar h3 {
            margin-bottom: 1.5rem;
            font-size: 1.2rem;
            color: #495057;
        }

        .filter-section {
            margin-bottom: 2rem;
        }

        .filter-section h4 {
            margin-bottom: 1rem;
            color: #6c757d;
            font-size: 1rem;
        }

        .checkbox-group {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .checkbox-item:hover {
            color: #495057;
        }

        .checkbox-item input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #6c757d;
            cursor: pointer;
        }

        .size-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.5rem;
        }

        .size-btn {
            padding: 0.5rem;
            border: 2px solid #e9ecef;
            background: white;
            text-align: center;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            color: #6c757d;
        }

        .size-btn:hover, .size-btn.active {
            border-color: #adb5bd;
            color: #495057;
            background: #f8f9fa;
        }

        /* Products Grid */
        .products-section {
            flex: 1;
            transition: all 0.3s ease;
        }

        .products-section.full-width {
            width: 100%;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 2rem;
            transition: all 0.3s ease;
        }

        .products-grid.full-width {
            grid-template-columns: repeat(3, 1fr);
        }

        /* Product Card */
        .product-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(73, 80, 87, 0.12);
            transition: all 0.3s ease;
            position: relative;
            border: 1px solid rgba(233, 236, 239, 0.6);
            cursor: pointer;
        }

        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 60px rgba(73, 80, 87, 0.15);
            border-color: rgba(173, 181, 189, 0.3);
        }

        .product-image {
            position: relative;
            height: 300px;
            background: linear-gradient(135deg, #f1f3f4 0%, #e8eaed 100%);
            overflow: hidden;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
            filter: contrast(1.05) saturate(0.95);
        }

        .product-card:hover .product-image img {
            transform: scale(1.03);
        }

        .discount-badge {
            position: absolute;
            top: 1rem;
            left: 1rem;
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
        }

        .new-badge {
            position: absolute;
            top: 1rem;
            left: 1rem;
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
        }

        .compare-btn {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: rgba(248, 249, 250, 0.95);
            border: 1px solid rgba(173, 181, 189, 0.3);
            padding: 0.5rem;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .compare-btn:hover {
            background: white;
            box-shadow: 0 4px 16px rgba(73, 80, 87, 0.15);
            border-color: rgba(108, 117, 125, 0.4);
        }

        .color-options {
            position: absolute;
            bottom: 1rem;
            left: 1rem;
            display: flex;
            gap: 0.5rem;
        }

        .color-dot {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, 0.9);
            box-shadow: 0 2px 8px rgba(73, 80, 87, 0.15);
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .color-dot:hover {
            transform: scale(1.1);
        }

        .product-info {
            padding: 1.5rem;
            background: rgba(255, 255, 255, 0.7);
        }

        .product-name {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: #495057;
        }

        .product-subtitle {
            color: #6c757d;
            margin-bottom: 1rem;
            font-size: 0.95rem;
        }

        .price-section {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .current-price {
            font-size: 1.3rem;
            font-weight: 700;
            color: #495057;
        }

        .original-price {
            font-size: 1rem;
            color: #adb5bd;
            text-decoration: line-through;
        }

        .savings {
            color: #dc3545;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .financing-info {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .size-selector {
            margin-bottom: 1.5rem;
        }

        .size-selector label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #495057;
        }

        .size-options {
            display: flex;
            gap: 0.5rem;
        }

        .size-option {
            padding: 0.5rem 1rem;
            border: 2px solid #e9ecef;
            background: white;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            color: #6c757d;
        }

        .size-option:hover, .size-option.selected {
            border-color: #adb5bd;
            color: #495057;
            background: #f8f9fa;
        }

        .add-to-cart-btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1rem;
        }

        .add-to-cart-btn:hover {
            background: linear-gradient(135deg, #5a6268 0%, #3d4349 100%);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(108, 117, 125, 0.3);
        }

        .add-to-cart-btn.added {
            background: linear-gradient(135deg, #38a169 0%, #2f855a 100%);
        }

        .no-products {
            text-align: center;
            padding: 4rem 2rem;
            color: #718096;
            grid-column: 1 / -1;
        }

        .no-products h2 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #4a5568;
        }

        /* Loading Animation */
        .loading {
            display: none;
            text-align: center;
            padding: 2rem;
            color: #6c757d;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #e9ecef;
            border-top: 4px solid #6c757d;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-content {
                flex-direction: column;
                padding: 1rem;
            }

            .sidebar {
                width: 100%;
                position: fixed;
                top: 0;
                left: 0;
                height: 100vh;
                z-index: 1000;
                transform: translateX(-100%);
                border-radius: 0;
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .products-grid, .products-grid.full-width {
                grid-template-columns: 1fr;
            }

            .filters-container {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }

            .filter-buttons {
                flex-wrap: wrap;
            }
        }

        .placeholder-image {
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-size: 3rem;
            font-weight: 100;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="nav">
            <a href="index.php" class="logo">BIKESTORE</a>
            <a href="index.php" class="back-link">← Volver al inicio</a>
        </nav>
    </header>

    <!-- Filters Header -->
    <div class="filters-header">
        <div class="filters-container">
            <div class="results-count" id="results-count">
                <span id="product-count"><?php echo $result->num_rows; ?></span> artículos
            </div>
            <div class="filter-buttons">
                <button class="filter-btn active" id="toggle-filters">Filtrar</button>
                <button class="filter-btn" id="clear-filters">Limpiar filtros</button>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Sidebar Filters -->
        <aside class="sidebar" id="sidebar">
            <h3>Seleccionar filtros</h3>
            
            <div class="filter-section">
                <h4>Estado</h4>
                <div class="checkbox-group">
                    <label class="checkbox-item">
                        <input type="checkbox" id="in-stock" checked>
                        <span>En stock (<span id="stock-count"><?php echo $result->num_rows; ?></span>)</span>
                    </label>
                </div>
            </div>

            <div class="filter-section">
                <h4>Tipo</h4>
                <div class="checkbox-group">
                    <label class="checkbox-item">
                        <input type="checkbox" value="montaña" data-filter="tipo">
                        <span>Montaña</span>
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" value="ruta" data-filter="tipo">
                        <span>Ruta</span>
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" value="urbana" data-filter="tipo">
                        <span>Urbana</span>
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" value="eléctrica" data-filter="tipo">
                        <span>Eléctrica</span>
                    </label>
                </div>
            </div>

            <div class="filter-section">
                <h4>Talla</h4>
                <div class="size-grid">
                    <button class="size-btn" data-size="XS">XS</button>
                    <button class="size-btn" data-size="S">S</button>
                    <button class="size-btn" data-size="M">M</button>
                    <button class="size-btn" data-size="L">L</button>
                    <button class="size-btn" data-size="XL">XL</button>
                    <button class="size-btn" data-size="XXL">XXL</button>
                </div>
            </div>
        </aside>

        <!-- Products Section -->
        <section class="products-section" id="products-section">
            <div class="loading" id="loading">
                <div class="spinner"></div>
                <p>Cargando productos...</p>
            </div>
            
            <div class="products-grid" id="products-grid">
                <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        // Calcular descuento si hay precio original
                        $hasDiscount = false;
                        $discountPercent = 0;
                        $originalPrice = $row['precio'] * 1.2; // Simular precio original
                        if ($originalPrice > $row['precio']) {
                            $hasDiscount = true;
                            $discountPercent = round((($originalPrice - $row['precio']) / $originalPrice) * 100);
                        }
                        
                        echo '<div class="product-card" data-tipo="' . htmlspecialchars($row['tipo']) . '" data-id="' . $row['id'] . '">';
                        echo '    <div class="product-image">';
                        
                        // Mostrar imagen o placeholder
                        if (!empty($row['imagen'])) {
                            echo '        <img src="' . htmlspecialchars($row['imagen']) . '" alt="' . htmlspecialchars($row['nombre']) . '">';
                        } else {
                            echo '        <div class="placeholder-image">🚲</div>';
                        }
                        
                        // Mostrar badges
                        if ($hasDiscount) {
                            echo '        <div class="discount-badge">-' . $discountPercent . '%</div>';
                        } else {
                            echo '        <div class="new-badge">NUEVO</div>';
                        }
                        
                        echo '        <button class="compare-btn" onclick="toggleCompare(' . $row['id'] . ')">⚖️</button>';
                        echo '        <div class="color-options">';
                        echo '            <div class="color-dot" style="background: #2d3748;"></div>';
                        echo '            <div class="color-dot" style="background: #667eea;"></div>';
                        echo '        </div>';
                        echo '    </div>';
                        
                        echo '    <div class="product-info">';
                        echo '        <h3 class="product-name">' . htmlspecialchars($row['nombre']) . '</h3>';
                        echo '        <p class="product-subtitle">' . htmlspecialchars($row['tipo']) . ' - ' . htmlspecialchars($row['peso']) . 'kg</p>';
                        
                        echo '        <div class="price-section">';
                        echo '            <span class="current-price">$' . number_format($row['precio'], 0, '.', ',') . '</span>';
                        if ($hasDiscount) {
                            echo '            <span class="original-price">$' . number_format($originalPrice, 0, '.', ',') . '</span>';
                            echo '            <span class="savings">Ahorras $' . number_format($originalPrice - $row['precio'], 0, '.', ',') . '</span>';
                        }
                        echo '        </div>';
                        
                        echo '        <div class="financing-info">Desde $' . number_format($row['precio']/12, 0, '.', ',') . ' al mes con crédito</div>';
                        
                        echo '        <div class="size-selector">';
                        echo '            <label>ELIGE TU TALLA</label>';
                        echo '            <div class="size-options">';
                        echo '                <button class="size-option" data-size="S">S</button>';
                        echo '                <button class="size-option" data-size="M">M</button>';
                        echo '                <button class="size-option" data-size="L">L</button>';
                        echo '                <button class="size-option" data-size="XL">XL</button>';
                        echo '            </div>';
                        echo '        </div>';
                        
                        echo '        <button class="add-to-cart-btn" onclick="addToCart(' . $row['id'] . ')">Agregar al Carrito</button>';
                        echo '    </div>';
                        echo '</div>';
                    }
                } else {
                    echo '<div class="no-products">';
                    echo '    <h2>No hay productos disponibles</h2>';
                    echo '    <p>Agrega algunos productos a tu base de datos para verlos aquí.</p>';
                    echo '</div>';
                }
                ?>
            </div>
        </section>
    </main>

    <script>
        // Variables globales
        let compareList = [];
        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        
        // Inicialización
        document.addEventListener('DOMContentLoaded', function() {
            initializeFilters();
            initializeProductEvents();
        });

        // Inicializar eventos de productos
        function initializeProductEvents() {
            // Click en producto para ir al detalle
            document.querySelectorAll('.product-card').forEach(card => {
                card.addEventListener('click', function(e) {
                    // Evitar navegación si se hace click en botones
                    if (e.target.closest('.compare-btn') || e.target.closest('.add-to-cart-btn') || e.target.closest('.size-option')) {
                        return;
                    }
                    const productId = this.dataset.id;
                    window.location.href = `producto_detalle.php?id=${productId}`;
                });
            });

            // Size selection
            document.querySelectorAll('.size-option').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    // Remove active class from siblings
                    this.parentNode.querySelectorAll('.size-option').forEach(sibling => {
                        sibling.classList.remove('selected');
                    });
                    // Add active class to clicked button
                    this.classList.add('selected');
                });
            });
        }

        // Inicializar filtros
        function initializeFilters() {
            // Toggle filters visibility
            document.getElementById('toggle-filters').addEventListener('click', function() {
                const sidebar = document.getElementById('sidebar');
                const productsSection = document.getElementById('products-section');
                const productsGrid = document.getElementById('products-grid');
                
                sidebar.classList.toggle('hidden');
                if (sidebar.classList.contains('hidden')) {
                    productsSection.classList.add('full-width');
                    productsGrid.classList.add('full-width');
                    this.textContent = 'Mostrar filtros';
                    this.classList.remove('active');
                } else {
                    productsSection.classList.remove('full-width');
                    productsGrid.classList.remove('full-width');
                    this.textContent = 'Filtrar';
                    this.classList.add('active');
                }
            });

            // Clear filters
            document.getElementById('clear-filters').addEventListener('click', function() {
                clearAllFilters();
            });

            // Filter by type
            document.querySelectorAll('input[data-filter="tipo"]').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    applyFilters();
                });
            });

            // Size filter buttons
            document.querySelectorAll('.size-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    this.classList.toggle('active');
                    applyFilters();
                });
            });

            // Mobile filters toggle
            if (window.innerWidth <= 768) {
                document.getElementById('toggle-filters').addEventListener('click', function() {
                    document.getElementById('sidebar').classList.toggle('active');
                });
            }
        }

        // Aplicar filtros
        function applyFilters() {
            const loading = document.getElementById('loading');
            const productsGrid = document.getElementById('products-grid');
            
            // Mostrar loading
            loading.style.display = 'block';
            productsGrid.style.opacity = '0.5';
            
            setTimeout(() => {
                const cards = document.querySelectorAll('.product-card');
                let visibleCount = 0;
                
                // Obtener filtros activos
                const activeTypes = Array.from(document.querySelectorAll('input[data-filter="tipo"]:checked')).map(cb => cb.value);
                const activeSizes = Array.from(document.querySelectorAll('.size-btn.active')).map(btn => btn.dataset.size);
                
                cards.forEach(card => {
                    const cardType = card.dataset.tipo.toLowerCase();
                    let show = true;
                    
                    // Filtrar por tipo
                    if (activeTypes.length > 0) {
                        show = show && activeTypes.some(type => cardType.includes(type.toLowerCase()));
                    }
                    
                    // Filtrar por talla (simulado)
                    if (activeSizes.length > 0) {
                        // En una implementación real, esto vendría de la base de datos
                        show = show && activeSizes.length > 0;
                    }
                    
                    if (show) {
                        card.style.display = 'block';
                        visibleCount++;
                    } else {
                        card.style.display = 'none';
                    }
                });
                
                // Actualizar contador
                document.getElementById('product-count').textContent = visibleCount;
                document.getElementById('stock-count').textContent = visibleCount;
                
                // Ocultar loading
                loading.style.display = 'none';
                productsGrid.style.opacity = '1';
                
                // Mostrar mensaje si no hay productos
                if (visibleCount === 0) {
                    showNoProductsMessage();
                } else {
                    hideNoProductsMessage();
                }
            }, 500);
        }

        // Limpiar todos los filtros
        function clearAllFilters() {
            // Desmarcar checkboxes
            document.querySelectorAll('input[data-filter]').forEach(cb => cb.checked = false);
            
            // Desactivar botones de talla
            document.querySelectorAll('.size-btn').forEach(btn => btn.classList.remove('active'));
            
            // Mostrar todos los productos
            document.querySelectorAll('.product-card').forEach(card => {
                card.style.display = 'block';
            });
            
            // Actualizar contador
            const totalProducts = document.querySelectorAll('.product-card').length;
            document.getElementById('product-count').textContent = totalProducts;
            document.getElementById('stock-count').textContent = totalProducts;
            
            hideNoProductsMessage();
        }

        // Mostrar mensaje de no productos
        function showNoProductsMessage() {
            let noProductsDiv = document.querySelector('.no-products');
            if (!noProductsDiv) {
                noProductsDiv = document.createElement('div');
                noProductsDiv.className = 'no-products';
                noProductsDiv.innerHTML = `
                    <h2>No se encontraron productos</h2>
                    <p>Intenta ajustar tus filtros para ver más resultados.</p>
                `;
                document.getElementById('products-grid').appendChild(noProductsDiv);
            }
            noProductsDiv.style.display = 'block';
        }

        // Ocultar mensaje de no productos
        function hideNoProductsMessage() {
            const noProductsDiv = document.querySelector('.no-products');
            if (noProductsDiv) {
                noProductsDiv.style.display = 'none';
            }
        }

        // Agregar al carrito
        function addToCart(productId) {
            const productCard = document.querySelector(`[data-id="${productId}"]`);
            const button = productCard.querySelector('.add-to-cart-btn');
            const selectedSize = productCard.querySelector('.size-option.selected');
            
            if (!selectedSize) {
                alert('Por favor selecciona una talla');
                return;
            }
            
            // Animation feedback
            const originalText = button.textContent;
            button.textContent = 'Agregado ✓';
            button.classList.add('added');
            
            // Agregar al carrito (simulado)
            const product = {
                id: productId,
                size: selectedSize.dataset.size,
                quantity: 1,
                timestamp: Date.now()
            };
            
            cart.push(product);
            localStorage.setItem('cart', JSON.stringify(cart));
            
            setTimeout(() => {
                button.textContent = originalText;
                button.classList.remove('added');
            }, 2000);
            
            // Mostrar notificación
            showNotification('Producto agregado al carrito');
        }

        // Toggle comparación
        function toggleCompare(productId) {
            const index = compareList.indexOf(productId);
            if (index > -1) {
                compareList.splice(index, 1);
            } else {
                if (compareList.length < 3) {
                    compareList.push(productId);
                } else {
                    alert('Máximo 3 productos para comparar');
                    return;
                }
            }
            
            updateCompareButtons();
            showNotification(`${compareList.length} productos en comparación`);
        }

        // Actualizar botones de comparación
        function updateCompareButtons() {
            document.querySelectorAll('.compare-btn').forEach(btn => {
                const card = btn.closest('.product-card');
                const productId = parseInt(card.dataset.id);
                
                if (compareList.includes(productId)) {
                    btn.style.background = 'rgba(108, 117, 125, 0.9)';
                    btn.style.color = 'white';
                } else {
                    btn.style.background = 'rgba(248, 249, 250, 0.95)';
                    btn.style.color = '#6c757d';
                }
            });
        }

        // Mostrar notificación
        function showNotification(message) {
            // Remover notificación existente
            const existingNotification = document.querySelector('.notification');
            if (existingNotification) {
                existingNotification.remove();
            }
            
            const notification = document.createElement('div');
            notification.className = 'notification';
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
                color: white;
                padding: 1rem 1.5rem;
                border-radius: 10px;
                box-shadow: 0 10px 25px rgba(73, 80, 87, 0.3);
                z-index: 1000;
                animation: slideIn 0.3s ease;
            `;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        // Responsive handling
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                document.getElementById('sidebar').classList.remove('active');
            }
        });

        // Animaciones CSS adicionales
        const additionalStyles = document.createElement('style');
        additionalStyles.textContent = `
            @keyframes slideIn {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            
            @keyframes slideOut {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(100%);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(additionalStyles);
    </script>
</body>
</html>