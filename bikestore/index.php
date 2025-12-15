<?php
session_start();
include 'conexion.php';

// Verificar login
$logueado = isset($_SESSION['logueado']) && $_SESSION['logueado'] === true;
$nombre_usuario = $logueado ? $_SESSION['usuario_nombre'] : '';
$usuario_rol = $logueado ? $_SESSION['usuario_rol'] : '';
$nivel_usuario = $logueado ? $_SESSION['usuario_nivel_ciclismo'] : '';

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

// Lógica de Sugerencias
$mostrar_sugerencias = false;
$sugerencias = [];
if ($logueado && isset($_GET['ver']) && $_GET['ver'] === 'sugerencias') {
    $mostrar_sugerencias = true;
    $stmt_sug = $conn->prepare("SELECT * FROM productos WHERE nivel_ciclismo = ? AND stock > 0 ORDER BY RAND() LIMIT 3");
    $stmt_sug->bind_param("s", $nivel_usuario);
    $stmt_sug->execute();
    $sugerencias = $stmt_sug->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt_sug->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BikeStore - Future Performance</title>
    <style>
        /* BASE OSCURA PREMIUM */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #0f0f0f;
            color: #fff;
            overflow-x: hidden;
        }

        /* NAVBAR */
        .navbar {
            position: fixed; top: 0; left: 0; width: 100%; padding: 1.2rem 3rem;
            display: flex; justify-content: space-between; align-items: center;
            z-index: 1000; transition: all 0.3s ease;
            background: linear-gradient(to bottom, rgba(0,0,0,0.9), transparent);
        }
        .navbar.scrolled {
            background: rgba(10, 10, 10, 0.95); padding: 0.8rem 3rem;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.5);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
        }
        .logo { font-size: 1.5rem; font-weight: 900; color: white; text-decoration: none; letter-spacing: -1px; text-transform: uppercase; }

        .nav-center { display: flex; gap: 2rem; }
        .nav-link {
            color: rgba(255, 255, 255, 0.7); text-decoration: none;
            font-weight: 600; font-size: 0.85rem; transition: color 0.3s;
            text-transform: uppercase; letter-spacing: 1px;
        }
        .nav-link:hover { color: #fff; }

        .nav-right { display: flex; align-items: center; gap: 1.5rem; }
        .icon-btn { background: none; border: none; cursor: pointer; font-size: 1.2rem; color: white; position: relative; text-decoration: none; }
        .cart-badge {
            position: absolute; top: -5px; right: -8px; background: #fff; color: #000;
            font-size: 0.6rem; font-weight: 800; padding: 2px 6px; border-radius: 10px;
        }

        /* DROPDOWN USUARIO */
        .user-dropdown { position: relative; padding-bottom: 10px; margin-bottom: -10px; }
        .user-menu {
            display: none; position: absolute; right: 0; top: 100%;
            background: #1a1a1a; min-width: 200px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.6); border-radius: 4px;
            padding: 0.5rem 0; margin-top: 5px;
            border: 1px solid rgba(255,255,255,0.1);
            animation: fadeInUp 0.2s ease;
        }
        .user-dropdown::after { content: ''; position: absolute; top: 100%; left: 0; width: 100%; height: 20px; background: transparent; }
        .user-dropdown:hover .user-menu { display: block; }
        
        .menu-item {
            display: block; padding: 12px 20px; color: #ccc;
            text-decoration: none; font-size: 0.85rem; font-weight: 500;
            transition: background 0.2s; text-align: left;
        }
        .menu-item:hover { background: #333; color: white; }
        .menu-divider { height: 1px; background: rgba(255,255,255,0.1); margin: 5px 0; }

        /* HERO SECTION */
        .hero {
            height: 100vh; width: 100%; position: relative;
            display: flex; align-items: center; justify-content: center; text-align: center;
        }
        .video-bg {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            object-fit: cover; z-index: -2; filter: brightness(0.5);
        }
        .hero-content { animation: fadeInUp 1s ease-out; padding: 0 1rem; z-index: 1; }
        .hero-subtitle {
            font-size: 1.1rem; font-weight: 700; letter-spacing: 4px;
            text-transform: uppercase; margin-bottom: 1.5rem; opacity: 0.9;
        }
        .hero-title {
            font-size: 6vw; font-weight: 900; line-height: 0.9; margin-bottom: 2rem;
            letter-spacing: -2px; text-transform: uppercase; color: #fff;
        }
        .hero-btn {
            display: inline-block; padding: 1.2rem 3.5rem; background: white;
            color: #000; text-decoration: none; font-weight: 800; border-radius: 0;
            text-transform: uppercase; letter-spacing: 2px; transition: all 0.3s ease;
        }
        .hero-btn:hover { background: transparent; color: white; border: 1px solid white; }
        .hero-btn-outline {
            background: transparent; border: 1px solid white; color: white; margin-left: 1rem; display: inline-block; padding: 1.2rem 3.5rem; text-decoration: none; font-weight: 800; text-transform: uppercase; letter-spacing: 2px;
        }
        .hero-btn-outline:hover { background: white; color: #000; }

        /* SECCIONES */
        .section { padding: 8rem 3rem; max-width: 1600px; margin: 0 auto; }
        .section-header { margin-bottom: 4rem; display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 1rem; }
        .section-title { font-size: 3rem; font-weight: 800; letter-spacing: -1px; margin: 0; color: white; }
        .section-link { color: #666; text-decoration: none; text-transform: uppercase; letter-spacing: 1px; font-size: 0.8rem; }
        .section-link:hover { color: white; }

        /* CATEGORÍAS */
        .cat-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem; }
        .cat-card {
            position: relative; height: 500px; overflow: hidden;
            display: block; color: white; text-decoration: none;
        }
        .cat-img { 
            width: 100%; height: 100%; object-fit: cover; 
            transition: transform 0.8s cubic-bezier(0.2, 1, 0.3, 1); 
            filter: grayscale(10%) brightness(0.8);
        }
        .cat-card:hover .cat-img { transform: scale(1.05); filter: grayscale(0%) brightness(1); }
        .cat-content {
            position: absolute; bottom: 0; left: 0; width: 100%; padding: 3rem;
            background: linear-gradient(to top, rgba(0,0,0,0.9), transparent);
        }
        .cat-name { font-size: 2.5rem; font-weight: 800; text-transform: uppercase; margin: 0; line-height: 1; }
        .cat-sub { font-size: 0.9rem; font-weight: 600; text-transform: uppercase; letter-spacing: 2px; opacity: 0.7; margin-bottom: 0.5rem; display: block; }

        /* JOURNAL (CORREGIDO) */
        .news-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 3rem; }
        .news-card { background: #1a1a1a; border-radius: 8px; overflow: hidden; transition: transform 0.3s; }
        .news-card:hover { transform: translateY(-8px); }
        .news-img { width: 100%; height: 220px; object-fit: cover; opacity: 0.8; transition: opacity 0.3s; }
        .news-card:hover .news-img { opacity: 1; }
        .news-body { padding: 2rem; }
        .news-tag { 
            font-size: 0.75rem; color: #fbbf24; text-transform: uppercase; 
            letter-spacing: 1px; margin-bottom: 0.5rem; font-weight: 700; display: block;
        }
        .news-title { font-size: 1.3rem; font-weight: 700; line-height: 1.3; margin-bottom: 1rem; color: white; }
        .news-excerpt { color: #888; font-size: 0.9rem; line-height: 1.6; margin-bottom: 1.5rem; }
        .news-link { 
            font-weight: 700; font-size: 0.85rem; color: white; 
            text-decoration: none; border-bottom: 1px solid #444; padding-bottom: 2px;
            transition: border 0.3s;
        }
        .news-link:hover { border-color: white; }

        /* SUGERENCIAS OVERLAY */
        .suggestions-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.95); z-index: 2000; display: flex; flex-direction: column; align-items: center; justify-content: center; animation: fadeIn 0.3s; }
        .sug-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem; max-width: 1200px; width: 100%; padding: 2rem; }
        .sug-card { background: #1a1a1a; padding: 1.5rem; text-decoration: none; color: white; text-align: center; border: 1px solid #333; }
        .sug-img { width: 100%; height: 200px; object-fit: cover; margin-bottom: 1rem; }

        /* FOOTER */
        .footer { background: #050505; color: #666; padding: 5rem 2rem; text-align: center; margin-top: 4rem; border-top: 1px solid #1a1a1a; }
        
        /* SEARCH */
        .search-modal { position: fixed; inset: 0; background: rgba(10,10,10,0.98); z-index: 2000; display: none; align-items: center; justify-content: center; }
        .search-modal.active { display: flex; }
        .search-input { font-size: 3rem; border: none; border-bottom: 1px solid #333; color: white; background: transparent; width: 80%; padding: 1rem; font-weight: 700; text-align: center; }
        .search-input:focus { outline: none; border-color: white; }
        .close-search { position: absolute; top: 40px; right: 50px; font-size: 2rem; cursor: pointer; color: white; }

        @keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        @media (max-width: 900px) {
            .hero-title { font-size: 12vw; } .nav-center { display: none; }
            .cat-grid, .news-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <nav class="navbar" id="navbar">
        <a href="index.php" class="logo">BIKESTORE</a>
        
        <div class="nav-center">
            <a href="productos.php" class="nav-link">BICICLETAS</a>
            <a href="#journal" class="nav-link">JOURNAL</a>
        </div>

        <div class="nav-right">
            <button class="icon-btn" onclick="toggleSearch()">🔍</button>
            <a href="carrito.php" class="icon-btn">
                🛒
                <?php if($cart_count > 0): ?><span class="cart-badge"><?php echo $cart_count; ?></span><?php endif; ?>
            </a>
            
            <?php if($logueado): ?>
                <div class="user-dropdown">
                    <button class="icon-btn" style="font-size:0.9rem; font-weight:700; letter-spacing:1px;">
                        <?php echo strtoupper(explode(' ', $nombre_usuario)[0]); ?> ▾
                    </button>
                    <div class="user-menu">
                        <a href="perfil.php" class="menu-item">👤 Mi Perfil</a>
                        <a href="pedidos.php" class="menu-item">📦 Mis Pedidos</a>
                        <?php if($usuario_rol == 'admin'): ?>
                            <a href="principal_admin.php" class="menu-item">⚙️ Panel Admin</a>
                        <?php elseif($usuario_rol == 'operador'): ?>
                            <a href="principal_operador.php" class="menu-item">⚙️ Panel Operador</a>
                        <?php endif; ?>
                        <div class="menu-divider"></div>
                        <a href="logout.php" class="menu-item" style="color:#ef4444;">Cerrar Sesión</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="login.php" class="icon-btn" style="font-size:0.9rem; font-weight:600;">INGRESAR</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="search-modal" id="searchModal">
        <span class="close-search" onclick="toggleSearch()">✕</span>
        <input type="text" class="search-input" placeholder="Escribe para buscar..." id="searchInput">
    </div>

    <header class="hero">
        <video autoplay muted loop playsinline class="video-bg">
            <source src="https://dma.canyon.com/video/upload/w_1920,c_fit/f_mp4/q_80,vc_h264/960963614" type="video/mp4">
        </video>
        <div class="overlay" style="background: rgba(0,0,0,0.3);"></div>
        <div class="hero-content">
            <p class="hero-subtitle">INGENIERÍA DE PRECISIÓN</p>
            <h1 class="hero-title">RENDIMIENTO<br>PURO</h1>
            <a href="productos.php" class="hero-btn">VER COLECCIÓN</a>
            <?php if($logueado && isset($_GET['ver']) != 'sugerencias'): ?>
                <a href="index.php?ver=sugerencias" class="hero-btn-outline">PARA MÍ</a>
            <?php endif; ?>
        </div>
    </header>

    <?php if ($mostrar_sugerencias): ?>
    <div class="suggestions-overlay">
        <h2 style="font-size:3rem; margin-bottom:1rem; font-weight:800; color:white;">SELECCIÓN <?php echo strtoupper($nivel_usuario); ?></h2>
        <div class="sug-grid">
            <?php foreach ($sugerencias as $s): ?>
            <a href="producto_detalle.php?id=<?php echo $s['id']; ?>" class="sug-card">
                <?php if($s['imagen_principal']): ?>
                    <img src="<?php echo htmlspecialchars($s['imagen_principal']); ?>" class="sug-img">
                <?php else: ?>
                    <div style="height:200px; background:#333; display:flex; align-items:center; justify-content:center;">🚲</div>
                <?php endif; ?>
                <h3><?php echo htmlspecialchars($s['nombre']); ?></h3>
                <p style="color:#888;">$<?php echo number_format($s['precio'], 2); ?></p>
            </a>
            <?php endforeach; ?>
        </div>
        <a href="index.php" style="color:#888; margin-top:3rem; text-decoration:underline;">CERRAR</a>
    </div>
    <?php endif; ?>

    <section class="section">
        <div class="section-header">
            <h2 class="section-title">CATEGORÍAS</h2>
            <a href="productos.php" class="section-link">VER TODO EL CATÁLOGO</a>
        </div>
        <div class="cat-grid">
            <a href="productos.php?tipo=montaña" class="cat-card">
                <img src="https://images.unsplash.com/photo-1544191696-102dbdaeeaa0?q=80&w=1200&auto=format&fit=crop" class="cat-img">
                <div class="cat-content">
                    <span class="cat-sub">EXPLORA</span>
                    <h3 class="cat-name">MONTAÑA</h3>
                </div>
            </a>
            <a href="productos.php?tipo=ruta" class="cat-card">
                <img src="https://dma.canyon.com/image/upload/w_322,h_242,c_fill/f_auto/q_auto/HP-canyon-world-road_glns0q" class="cat-img">
                <div class="cat-content">
                    <span class="cat-sub">VELOCIDAD</span>
                    <h3 class="cat-name">RUTA</h3>
                </div>
            </a>
            <a href="productos.php?tipo=urbana" class="cat-card">
                <img src="https://dma.canyon.com/image/upload/w_322,h_242,c_fill/f_auto/q_auto/HP-canyon-world-urban_s0omly" class="cat-img">
                <div class="cat-content">
                    <span class="cat-sub">CIUDAD</span>
                    <h3 class="cat-name">URBANA</h3>
                </div>
            </a>
            <a href="productos.php?tipo=eléctrica" class="cat-card">
                <img src="https://static.motor.es/fotos-noticias/2022/09/5-bicis-electricas-bianchi-evertic-mtb-touring-ciudad-202290096-1664382949_1.jpg" class="cat-img">
                <div class="cat-content">
                    <span class="cat-sub">E-POWER</span>
                    <h3 class="cat-name">ELÉCTRICA</h3>
                </div>
            </a>
        </div>
    </section>

    <section class="section" id="journal" style="background: #0f0f0f; border-top: 1px solid #222;">
        <div class="section-header">
            <h2 class="section-title">JOURNAL</h2>
            <p class="section-desc">Noticias, rankings y eventos.</p>
        </div>
        
        <div class="news-grid">
            <article class="news-card">
                <img src="https://tvazteca.brightspotcdn.com/dims4/default/cc77148/2147483647/strip/true/crop/1920x1080+0+0/resize/928x522!/format/webp/quality/90/?url=http%3A%2F%2Ftv-azteca-brightspot.s3.amazonaws.com%2Fd4%2F80%2Fb6ff13584ea2a7bb17bb70744b21%2Fisaac-del-toro-segundo-lugar-gran-giro-de-italia-2025.jpg" class="news-img">
                <div class="news-body">
                    <span class="news-tag">RANKING</span>
                    <h3 class="news-title">UCI World Ranking 2025</h3>
                    <p class="news-excerpt">Consulta la clasificación mundial actualizada.</p>
                    <a href="https://www.procyclingstats.com/rankings/me/uci-individual" target="_blank" class="news-link">Leer artículo</a>
                </div>
            </article>

            <article class="news-card">
                <img src="https://img.olympics.com/images/image/private/t_s_16_9_g_auto/t_s_w440/f_auto/v1730882055/primary/waa0mtmmrdpfrnwqqxvv" class="news-img">
                <div class="news-body">
                    <span class="news-tag">EVENTOS</span>
                    <h3 class="news-title">Calendario Ciclismo México</h3>
                    <p class="news-excerpt">Descubre las próximas carreras en el país.</p>
                    <a href="https://www.tritour.org/calendario-ciclismo-mexico" target="_blank" class="news-link">Ver fechas</a>
                </div>
            </article>

            <article class="news-card">
                <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQZMPDJwDReyEBNIfbNS0bZ_W64lOs_8YDtsA&s" class="news-img">
                <div class="news-body">
                    <span class="news-tag">TÉCNICA</span>
                    <h3 class="news-title">Guía de Ajuste (Bike Fit)</h3>
                    <p class="news-excerpt">Aprende a ajustar tu bici para evitar lesiones.</p>
                    <a href="https://www.rei.com/learn/expert-advice/bike-fit.html" target="_blank" class="news-link">Ver guía</a>
                </div>
            </article>
        </div>
    </section>

    <footer class="footer">
        <h2 class="logo" style="margin-bottom: 1.5rem;">BIKESTORE</h2>
        <p>&copy; <?php echo date('Y'); ?> BikeStore Inc. Designed for performance.</p>
    </footer>

    <script>
        window.addEventListener('scroll', () => {
            const nav = document.getElementById('navbar');
            if (window.scrollY > 50) nav.classList.add('scrolled');
            else nav.classList.remove('scrolled');
        });

        function toggleSearch() {
            const modal = document.getElementById('searchModal');
            const input = document.getElementById('searchInput');
            if (modal.classList.contains('active')) modal.classList.remove('active');
            else { modal.classList.add('active'); input.focus(); }
        }

        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') window.location.href = 'productos.php?q=' + encodeURIComponent(this.value);
        });
    </script>
</body>
</html>