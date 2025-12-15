<?php
session_start();

// Verificar si el usuario está logueado
$logueado = isset($_SESSION['logueado']) && $_SESSION['logueado'] === true;
$nombre_usuario = $logueado ? $_SESSION['usuario_nombre'] : '';
$usuario_rol = $logueado ? $_SESSION['usuario_rol'] : '';

// Obtener cantidad de items en el carrito
$cart_count = 0;
if ($logueado) {
    include 'conexion.php';
    $usuario_id = $_SESSION['usuario_id'];
    $stmt = $conn->prepare("SELECT SUM(cantidad) as total FROM carrito WHERE usuario_id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $cart_count = $row['total'] ? (int)$row['total'] : 0;
    }
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BikeStore - Las mejores bicicletas al mejor precio</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        /* Header Navigation */
        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 900;
            color: #2d3748;
            text-decoration: none;
            letter-spacing: -1px;
        }

        /* Search Toggle */
        .search-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.8);
            display: none;
            z-index: 2000;
            justify-content: center;
            align-items: flex-start;
            padding-top: 10vh;
        }

        .search-modal {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        }

        .search-modal-input {
            width: 100%;
            padding: 1rem;
            font-size: 1.2rem;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            margin-bottom: 1rem;
        }

        .search-modal-input:focus {
            outline: none;
            border-color: #2E3848;
        }

        .close-search {
            position: absolute;
            top: 20px;
            right: 30px;
            background: none;
            border: none;
            font-size: 2rem;
            color: white;
            cursor: pointer;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .nav-link {
            color: #4a5568;
            text-decoration: none;
            font-weight: 500;
            padding: 0.5rem 0;
            position: relative;
            transition: color 0.3s ease;
        }

        .nav-link:hover {
            color: #2E3848;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: #2E3848;
            transition: width 0.3s ease;
        }

        .nav-link:hover::after {
            width: 100%;
        }

        .user-actions {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .icon-button {
            background: none;
            border: none;
            font-size: 1.3rem;
            color: #4a5568;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 50%;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            position: relative;
        }

        .icon-button:hover {
            background: #f7fafc;
            color: #2E3848;
            transform: translateY(-1px);
        }

        .search-icon-btn {
            font-size: 1.2rem;
        }

        /* Estilos del carrito con badge */
        .cart-button {
            position: relative;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 50%;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
        }

        .cart-button:hover {
            background: #f7fafc;
            transform: translateY(-1px);
        }

        .cart-icon {
            font-size: 1.3rem;
            color: #4a5568;
        }

        .cart-button:hover .cart-icon {
            color: #2E3848;
        }

        .cart-badge {
            position: absolute;
            top: -2px;
            right: -2px;
            background: linear-gradient(135deg, #e53e3e, #c53030);
            color: white;
            font-size: 0.65rem;
            font-weight: 700;
            padding: 0.15rem 0.4rem;
            border-radius: 50%;
            min-width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(229, 62, 62, 0.4);
            animation: pulse 2s infinite;
            border: 2px solid white;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.15);
            }
        }

        .cart-badge.empty {
            display: none;
        }

        .user-icon {
            font-size: 1.2rem;
        }

        /* User Menu Dropdown */
        .user-menu {
            position: relative;
        }

        .user-button {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: #f7fafc;
            border: 1px solid #e2e8f0;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            color: #2d3748;
        }

        .user-button:hover {
            background: #edf2f7;
            border-color: #cbd5e0;
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            background: #2E3848;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .user-name {
            max-width: 150px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            margin-top: 0.5rem;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            min-width: 200px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
        }

        .user-menu:hover .dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-item {
            padding: 0.75rem 1rem;
            color: #4a5568;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.2s ease;
            font-weight: 500;
        }

        .dropdown-item:hover {
            background: #f7fafc;
            color: #2E3848;
        }

        .dropdown-item:first-child {
            border-radius: 10px 10px 0 0;
        }

        .dropdown-item:last-child {
            border-radius: 0 0 10px 10px;
            color: #e53e3e;
        }

        .dropdown-item:last-child:hover {
            background: #fff5f5;
            color: #c53030;
        }

        .dropdown-divider {
            height: 1px;
            background: #e2e8f0;
            margin: 0.5rem 0;
        }

        .cart-badge-dropdown {
            background: #e53e3e;
            color: white;
            padding: 0.2rem 0.5rem;
            border-radius: 10px;
            font-size: 0.7rem;
            font-weight: 700;
        }

        /* Menu Categories */
        .categories-nav {
            background: #2E3848;
            color: white;
            padding: 1rem 0;
            margin-top: 80px;
        }

        .categories-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .categories-menu {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .menu-toggle {
            background: none;
            border: none;
            color: white;
            font-size: 1.2rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .category-item {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .category-item:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        /* Hero Section */
        .hero {
            position: relative;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            overflow: hidden;
        }

        .video-background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: -2;
        }

        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.2));
            z-index: -1;
        }

        .hero-content {
            color: white;
            max-width: 800px;
            padding: 2rem;
            z-index: 1;
        }

        .hero-subtitle {
            font-size: 1.1rem;
            color: #f7fafc;
            margin-bottom: 1rem;
            font-weight: 500;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .hero-title {
            font-size: 4rem;
            font-weight: 900;
            margin-bottom: 1.5rem;
            line-height: 1.1;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .hero-description {
            font-size: 1.3rem;
            margin-bottom: 2rem;
            color: #e2e8f0;
            font-weight: 300;
        }

        .hero-cta {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-primary {
            padding: 1rem 2rem;
            background: #2E3848;
            color: white;
            border: none;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary:hover {
            background: #1a202c;
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(46, 56, 72, 0.4);
        }

        .btn-secondary {
            padding: 1rem 2rem;
            background: transparent;
            color: white;
            border: 2px solid white;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-secondary:hover {
            background: white;
            color: #2d3748;
            transform: translateY(-3px);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .nav {
                padding: 1rem;
                flex-wrap: wrap;
            }

            .search-container {
                order: 3;
                flex: 1 1 100%;
                margin: 1rem 0 0 0;
            }

            .nav-links {
                display: none;
            }

            .user-name {
                display: none;
            }

            .hero-title {
                font-size: 2.5rem;
            }

            .hero-description {
                font-size: 1.1rem;
            }

            .categories-menu {
                flex-wrap: wrap;
                gap: 1rem;
            }

            .hero-cta {
                flex-direction: column;
                align-items: center;
            }
        }

        /* Scroll animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .hero-content > * {
            animation: fadeInUp 0.8s ease-out forwards;
            opacity: 0;
        }

        .hero-subtitle { animation-delay: 0.2s; }
        .hero-title { animation-delay: 0.4s; }
        .hero-description { animation-delay: 0.6s; }
        .hero-cta { animation-delay: 0.8s; }
    </style>
</head>
<body>
    <!-- Header Navigation -->
    <header class="header">
        <nav class="nav">
            <a href="index.php" class="logo">BIKESTORE</a>
            
            <div class="nav-links">
                <span>Las mejores bicis. Al mejor precio.</span>
            </div>
            
            <div class="user-actions">
                <button class="icon-button search-icon-btn" onclick="toggleSearch()">🔍</button>
                
                <!-- BOTÓN DEL CARRITO CON CONTADOR -->
                <a href="carrito.php" class="cart-button" title="Ver carrito (<?php echo $cart_count; ?> artículos)">
                    <span class="cart-icon">🛒</span>
                    <span class="cart-badge <?php echo $cart_count == 0 ? 'empty' : ''; ?>" id="cartBadge">
                        <?php echo $cart_count; ?>
                    </span>
                </a>
                
                <?php if ($logueado): ?>
                    <!-- Usuario logueado -->
                    <div class="user-menu">
                        <button class="user-button">
                            <div class="user-avatar"><?php echo strtoupper(substr($nombre_usuario, 0, 1)); ?></div>
                            <span class="user-name"><?php echo htmlspecialchars($nombre_usuario); ?></span>
                        </button>
                        <div class="dropdown-menu">
                            <a href="perfil.php" class="dropdown-item">
                                👤 Mi Perfil
                            </a>
                            <a href="carrito.php" class="dropdown-item">
                                🛒 Mi Carrito
                                <?php if ($cart_count > 0): ?>
                                    <span class="cart-badge-dropdown"><?php echo $cart_count; ?></span>
                                <?php endif; ?>
                            </a>
                            <a href="pedidos.php" class="dropdown-item">📦 Mis Pedidos</a>
                            <?php if ($usuario_rol === 'admin'): ?>
                                <div class="dropdown-divider"></div>
                                <a href="principal_admin.php" class="dropdown-item">⚙️ Panel Admin</a>
                            <?php endif; ?>
                            <?php if ($usuario_rol === 'operador'): ?>
                                <div class="dropdown-divider"></div>
                                <a href="principal_operador.php" class="dropdown-item">⚙️ Panel Operador</a>
                            <?php endif; ?>
                            <div class="dropdown-divider"></div>
                            <a href="logout.php" class="dropdown-item">🚪 Cerrar Sesión</a>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Usuario no logueado -->
                    <a href="login.php" class="icon-button user-icon" title="Iniciar sesión">👤</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <!-- Search Overlay Modal -->
    <div class="search-overlay" id="searchOverlay">
        <button class="close-search" onclick="toggleSearch()">×</button>
        <div class="search-modal">
            <input type="text" class="search-modal-input" placeholder="Buscar bicicletas..." id="searchModalInput">
            <div class="search-suggestions">
                <p>Búsquedas populares: Montaña, Carretera, Urbana, Eléctrica</p>
            </div>
        </div>
    </div>

    <!-- Categories Menu -->
    <div class="categories-nav">
        <div class="categories-container">
            <div class="categories-menu">
                <a href="productos.php?categoria=carretera" class="category-item">Carretera</a>
                <a href="productos.php?categoria=gravel" class="category-item">Gravel</a>
                <a href="productos.php?categoria=montaña" class="category-item">Montaña</a>
                <a href="productos.php?categoria=urbanas" class="category-item">Urbanas / Cicloturismo</a>
                <a href="productos.php?categoria=accesorios" class="category-item">Piezas y Accesorios</a>
                <a href="productos.php?categoria=outlet" class="category-item">Outlet</a>
                <a href="productos.php?categoria=ofertas" class="category-item">Ofertas</a>
            </div>
        </div>
    </div>

    <!-- Hero Section -->
    <section class="hero">
        <!-- Video Background -->
        <video autoplay muted loop class="video-background" playsinline data-object-fit="cover">
            <source src="https://dma.canyon.com/video/upload/w_1920,c_fit/f_mp4/q_80,vc_h264/960963614" type="video/mp4">
            <source src="bike-video.mp4" type="video/mp4">
            <source src="bike-video.webm" type="video/webm">
        </video>
        
        <!-- Overlay -->
        <div class="hero-overlay"></div>
        
        <!-- Hero Content -->
        <div class="hero-content">
            <p class="hero-subtitle">Disfruta del Ciclismo</p>
            <h1 class="hero-title">Más lejos, más rápido</h1>
            <p class="hero-description">
                Una bicicleta moderna para el ciclista moderno. Hacia arriba para cualquier cosa, hacia abajo para todo.
            </p>
            <div class="hero-cta">
                <a href="productos.php" class="btn-primary">Ver Productos</a>
                <?php if (!$logueado): ?>
                    <a href="registro.php" class="btn-secondary">Crear Cuenta</a>
                <?php else: ?>
                    <a href="carrito.php" class="btn-secondary">
                        🛒 Ver Carrito <?php if($cart_count > 0) echo "($cart_count)"; ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <script>
        // Toggle search modal
        function toggleSearch() {
            const searchOverlay = document.getElementById('searchOverlay');
            const searchInput = document.getElementById('searchModalInput');
            
            if (searchOverlay.style.display === 'flex') {
                searchOverlay.style.display = 'none';
            } else {
                searchOverlay.style.display = 'flex';
                searchInput.focus();
            }
        }

        // Close search on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.getElementById('searchOverlay').style.display = 'none';
            }
        });

        // Search functionality
        document.getElementById('searchModalInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const searchTerm = this.value;
                if (searchTerm) {
                    window.location.href = `buscar.php?q=${encodeURIComponent(searchTerm)}`;
                }
            }
        });

        // Header scroll effect
        window.addEventListener('scroll', function() {
            const header = document.querySelector('.header');
            if (window.scrollY > 100) {
                header.style.background = 'rgba(255, 255, 255, 0.98)';
                header.style.boxShadow = '0 2px 20px rgba(0, 0, 0, 0.1)';
            } else {
                header.style.background = 'rgba(255, 255, 255, 0.95)';
                header.style.boxShadow = 'none';
            }
        });

        // Actualizar badge del carrito dinámicamente (opcional)
        function actualizarBadgeCarrito() {
            fetch('obtener_cantidad_carrito.php')
                .then(response => response.json())
                .then(data => {
                    const badge = document.getElementById('cartBadge');
                    if (data.cantidad > 0) {
                        badge.textContent = data.cantidad;
                        badge.classList.remove('empty');
                    } else {
                        badge.classList.add('empty');
                    }
                })
                .catch(error => console.error('Error al actualizar carrito:', error));
        }

        // Actualizar badge cada 30 segundos (opcional)
        // setInterval(actualizarBadgeCarrito, 30000);
    </script>
</body>
</html>