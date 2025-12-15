<?php
session_start();
include 'conexion.php';
// producto_detalle.php

// Obtener ID del producto
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id <= 0) {
    header('Location: productos.php');
    exit;
}

// Obtener datos del producto
$sql = "SELECT * FROM productos WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: productos.php');
    exit;
}

$product = $result->fetch_assoc();
$stmt->close();

// ==========================================================================
// CORRECCIÓN DE LÓGICA DE STOCK (Para evitar mostrar stock fantasma)
// ==========================================================================

$tallas_disponibles = [];

// 1. Primero verificamos si este producto usa el sistema de tallas (tiene registros en la tabla)
$stmt_check = $conn->prepare("SELECT COUNT(*) as total FROM producto_tallas WHERE producto_id = ? AND activo = 1");
$stmt_check->bind_param("i", $product_id);
$stmt_check->execute();
$usa_sistema_tallas = $stmt_check->get_result()->fetch_assoc()['total'] > 0;
$stmt_check->close();

if ($usa_sistema_tallas) {
    // Si usa sistema de tallas, SOLO traemos lo que hay en esa tabla.
    // Si no hay stock ahí, el array quedará vacío y mostrará "Agotado" correctamente.
    $stmt = $conn->prepare("SELECT talla, stock FROM producto_tallas WHERE producto_id = ? AND activo = 1 AND stock > 0 ORDER BY FIELD(talla, 'XS', 'S', 'M', 'L', 'XL', 'XXL')");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $tallas_disponibles = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    // Solo si NO tiene configuradas tallas especiales, usamos el stock general (Fallback)
    // Esto es para productos simples como accesorios.
    if (!empty($product['talla']) && $product['stock'] > 0) {
        $tallas_disponibles = [
            ['talla' => $product['talla'], 'stock' => $product['stock']]
        ];
    }
}
// ==========================================================================

// Obtener comentarios
$stmt = $conn->prepare("SELECT c.*, u.nombre as usuario_nombre FROM comentarios c JOIN usuarios u ON c.usuario_id = u.id WHERE c.producto_id = ? ORDER BY c.fecha DESC");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$comentarios = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Calcular calificación
$stmt = $conn->prepare("SELECT AVG(calificacion) as promedio, COUNT(*) as total FROM comentarios WHERE producto_id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$calificacion_data = $stmt->get_result()->fetch_assoc();
$stmt->close();

$calificacion_promedio = $calificacion_data['promedio'] ? round($calificacion_data['promedio'], 1) : 0;
$total_comentarios = $calificacion_data['total'];

$fecha_envio = date('d/m/Y', strtotime('+' . $product['dias_envio'] . ' days'));
$usuario_logueado = isset($_SESSION['logueado']) && $_SESSION['logueado'] === true;

// Sugerencias
$stmt_sug = $conn->prepare("SELECT * FROM productos WHERE tipo = ? AND id != ? AND stock > 0 ORDER BY RAND() LIMIT 3");
$stmt_sug->bind_param("si", $product['tipo'], $product_id);
$stmt_sug->execute();
$sugerencias = $stmt_sug->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_sug->close();

$conn->close();

// Corrección visual para el nivel
$nivel_mostrar = ($product['nivel_ciclismo'] === '0' || $product['nivel_ciclismo'] == 0) 
                 ? 'No especificado' 
                 : ucfirst($product['nivel_ciclismo']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['nombre']); ?> - BikeStore</title>
    <style>
        /* RESET & BASE DARK */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica Neue', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #0f0f0f; color: #fff; line-height: 1.5; }
        
        /* HEADER DARK */
        .header { background: rgba(10, 10, 10, 0.95); border-bottom: 1px solid rgba(255, 255, 255, 0.05); position: sticky; top: 0; z-index: 100; padding: 1.2rem 0; backdrop-filter: blur(10px); }
        .nav { max-width: 1400px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; padding: 0 2rem; }
        .logo { font-size: 1.4rem; font-weight: 800; color: #fff; text-decoration: none; letter-spacing: -0.5px; }
        .back-link { color: #888; text-decoration: none; font-size: 0.9rem; transition: color 0.2s; }
        .back-link:hover { color: #fff; }

        .container { max-width: 1300px; margin: 0 auto; padding: 2rem 2rem; }

        /* BREADCRUMBS DARK */
        .breadcrumbs { margin-bottom: 3rem; font-size: 0.85rem; color: #666; background: #1a1a1a; padding: 1rem 1.5rem; border-radius: 50px; display: inline-flex; align-items: center; border: 1px solid #222; }
        .breadcrumbs a { text-decoration: none; color: #888; transition: 0.2s; }
        .breadcrumbs a:hover { color: #fff; }
        .breadcrumbs span.separator { margin: 0 0.5rem; color: #444; }
        .breadcrumbs span.current { color: #fff; font-weight: 600; }

        /* PRODUCT LAYOUT */
        .product-layout { display: grid; grid-template-columns: 1.2fr 1fr; gap: 5rem; margin-bottom: 5rem; }
        
        /* GALLERY DARK */
        .image-gallery { position: sticky; top: 100px; display: flex; flex-direction: column; gap: 1rem; }
        .main-image-container { background: #1a1a1a; border-radius: 4px; aspect-ratio: 1; display: flex; align-items: center; justify-content: center; overflow: hidden; cursor: zoom-in; border: 1px solid #222; }
        .main-image { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s; opacity: 0.9; }
        .main-image-container:hover .main-image { transform: scale(1.03); opacity: 1; }
        
        .thumbnail-grid { display: flex; gap: 1rem; overflow-x: auto; padding-bottom: 5px; }
        .thumbnail { width: 80px; height: 80px; flex-shrink: 0; background: #1a1a1a; border-radius: 4px; cursor: pointer; border: 1px solid #222; transition: all 0.2s; opacity: 0.6; }
        .thumbnail.active { border-color: #fff; opacity: 1; }
        .thumbnail img { width: 100%; height: 100%; object-fit: cover; border-radius: 3px; }

        /* PRODUCT INFO */
        .product-info { padding-top: 1rem; }
        .product-subtitle { font-size: 0.9rem; color: #888; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.5rem; font-weight: 600; }
        .product-title { font-size: 2.8rem; font-weight: 700; color: #fff; margin-bottom: 1rem; line-height: 1.1; letter-spacing: -1px; }
        .current-price { font-size: 1.8rem; font-weight: 500; color: #eee; margin-bottom: 2rem; }

        /* RATING DARK */
        .rating-section { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 2rem; font-size: 0.9rem; }
        .star { color: #fbbf24; } /* Dorado */
        .star.empty { color: #444; } /* Gris oscuro para las vacías en fondo negro */
        .rating-text { color: #888; margin-left: 0.5rem; text-decoration: underline; cursor: pointer; }

        /* SELECTORES DARK */
        .selectors-container { margin: 2.5rem 0; border-top: 1px solid #222; padding-top: 2rem; }
        .selector-label { font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #fff; margin-bottom: 1rem; display: block; }
        
        .tallas-grid { display: flex; flex-wrap: wrap; gap: 10px; }
        .talla-option {
            min-width: 65px; height: 45px; border: 1px solid #333; background: #1a1a1a; color: #ccc;
            border-radius: 4px; display: flex; align-items: center; justify-content: center;
            cursor: pointer; transition: all 0.2s ease; font-size: 0.95rem; font-weight: 500;
        }
        .talla-option:hover { border-color: #fff; color: #fff; }
        .talla-option.selected { background: #fff; color: #000; border-color: #fff; box-shadow: 0 0 15px rgba(255,255,255,0.2); }
        
        .qty-wrapper { display: flex; align-items: center; border: 1px solid #333; border-radius: 4px; width: fit-content; margin-top: 0.5rem; background: #1a1a1a; }
        .qty-btn { width: 40px; height: 40px; background: transparent; border: none; cursor: pointer; font-size: 1.2rem; color: #fff; transition: 0.2s; }
        .qty-btn:hover { background: #333; }
        .qty-input { width: 50px; text-align: center; border: none; font-size: 1rem; font-weight: 600; color: #fff; background: transparent; }

        .talla-error { color: #ef4444; font-size: 0.85rem; margin-top: 0.5rem; display: none; }

        /* BOTONES ACCIÓN DARK */
        .action-buttons { display: flex; gap: 1rem; margin-top: 2.5rem; flex-wrap: wrap; }
        .btn { padding: 1.2rem 2.5rem; border-radius: 4px; font-weight: 700; font-size: 1rem; cursor: pointer; transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1); border: 1px solid transparent; letter-spacing: 0.5px; text-align: center; flex: 1; text-decoration: none; }
        .btn-primary { background: #fff; color: #000; border-color: #fff; }
        .btn-primary:hover { background: #ccc; border-color: #ccc; }
        .btn-secondary { background: transparent; color: #fff; border-color: #333; }
        .btn-secondary:hover { border-color: #fff; background: rgba(255,255,255,0.05); }
        .btn.active { background: rgba(239, 68, 68, 0.1); border-color: #ef4444; color: #ef4444; } 

        /* INFO TABS DARK */
        .details-list { margin-top: 3rem; border-top: 1px solid #222; }
        .detail-item { border-bottom: 1px solid #222; }
        .detail-summary { padding: 1.5rem 0; cursor: pointer; font-weight: 600; display: flex; justify-content: space-between; align-items: center; list-style: none; color: #fff; }
        .detail-summary::-webkit-details-marker { display: none; }
        .detail-content { padding-bottom: 1.5rem; color: #aaa; line-height: 1.8; font-size: 0.95rem; }
        .plus-icon { font-size: 1.2rem; font-weight: 300; transition: transform 0.3s; color: #666; }
        details[open] .plus-icon { transform: rotate(45deg); color: #fff; }

        /* SUGERENCIAS & COMENTARIOS DARK */
        .suggestions-section { margin-top: 5rem; padding-top: 3rem; border-top: 1px solid #222; }
        .suggestions-title { font-size: 1.5rem; margin-bottom: 2rem; font-weight: 700; color: #fff; }
        .suggestions-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 2rem; }
        .sug-card { text-decoration: none; color: inherit; display: block; background: #1a1a1a; border-radius: 4px; overflow: hidden; padding-bottom: 1rem; }
        .sug-img { width: 100%; height: 250px; object-fit: cover; opacity: 0.8; transition: opacity 0.3s; }
        .sug-card:hover .sug-img { opacity: 1; }
        .sug-info { padding: 1rem; }
        .sug-name { font-weight: 600; margin-bottom: 0.3rem; font-size: 1rem; color: #fff; }
        .sug-price { color: #888; font-size: 0.95rem; }
        
        /* COMENTARIOS DARK */
        .comment-item { padding: 2rem 0; border-bottom: 1px solid #222; }
        .comment-header { display: flex; justify-content: space-between; margin-bottom: 0.5rem; }
        .comment-author { font-weight: 700; font-size: 0.95rem; color: #fff; }
        .comment-date { font-size: 0.8rem; color: #555; }
        .comment-body { color: #aaa; font-size: 0.95rem; line-height: 1.6; }
        
        /* Formulario Comentario Dark */
        .comment-form-container { background: #1a1a1a; padding: 1.5rem; border-radius: 8px; margin-top: 2rem; border: 1px solid #333; }
        .dark-select { background: #0f0f0f; color: #fff; border: 1px solid #333; padding: 0.8rem; border-radius: 4px; width: 100%; }
        .dark-textarea { background: #0f0f0f; color: #fff; border: 1px solid #333; padding: 1rem; border-radius: 4px; width: 100%; min-height: 100px; margin-top: 1rem; }
        .dark-select:focus, .dark-textarea:focus { border-color: #666; outline: none; }

        /* TOAST */
        .toast { position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%) translateY(100px); background: #fff; color: #000; padding: 1rem 2rem; border-radius: 50px; z-index: 1000; opacity: 0; transition: all 0.4s; box-shadow: 0 10px 30px rgba(0,0,0,0.5); font-weight: 700; font-size: 0.9rem; display: flex; align-items: center; gap: 10px; }
        .toast.show { transform: translateX(-50%) translateY(0); opacity: 1; }
        .toast.error { background: #ef4444; color: #fff; }

        @media (max-width: 900px) {
            .product-layout { grid-template-columns: 1fr; gap: 2rem; }
            .image-gallery { position: static; }
            .action-buttons { flex-direction: column; }
        }
    </style>
</head>
<body>
    <header class="header">
        <nav class="nav">
            <a href="index.php" class="logo">BIKESTORE</a>
            <a href="productos.php" class="back-link">← Volver</a>
        </nav>
    </header>

    <div class="container">
        <div class="breadcrumbs">
            <a href="index.php">Inicio</a><span class="separator">/</span>
            <a href="productos.php">Catálogo</a><span class="separator">/</span>
            <span class="current"><?php echo htmlspecialchars($product['nombre']); ?></span>
        </div>

        <div class="product-layout">
            <div class="image-gallery">
                <div class="main-image-container">
                    <?php if (!empty($product['imagen_principal'])): ?>
                        <img src="<?php echo htmlspecialchars($product['imagen_principal']); ?>" alt="Producto" class="main-image" id="mainImage">
                    <?php else: ?>
                        <div style="font-size: 3rem; color: #333;">No IMG</div>
                    <?php endif; ?>
                </div>
                <div class="thumbnail-grid">
                    <?php
                    $imgs = array_filter([$product['imagen_principal'], $product['imagen_2'], $product['imagen_3'], $product['imagen_4'], $product['imagen_5'], $product['imagen_6']], fn($u) => !empty($u));
                    foreach ($imgs as $i => $img): ?>
                        <div class="thumbnail <?php echo $i===0?'active':''; ?>" onclick="changeImage('<?php echo htmlspecialchars($img); ?>', this)">
                            <img src="<?php echo htmlspecialchars($img); ?>" onerror="this.parentElement.style.display='none'">
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="product-info">
                <div class="product-subtitle"><?php echo ucfirst($product['tipo']); ?></div>
                <h1 class="product-title"><?php echo htmlspecialchars($product['nombre']); ?></h1>
                
                <div class="current-price">$<?php echo number_format($product['precio'], 2); ?></div>

                <div class="rating-section">
                    <span style="color:#fbbf24; font-size: 1.2rem;">★</span> 
                    <strong><?php echo number_format($calificacion_promedio, 1); ?></strong>
                    <span class="rating-text" onclick="document.getElementById('comments-section').scrollIntoView({behavior:'smooth'})">
                        Ver <?php echo $total_comentarios; ?> reseñas
                    </span>
                </div>

                <?php if ($usuario_logueado && !empty($tallas_disponibles)): ?>
                    <form action="agregar_carrito.php" method="POST" onsubmit="return validarCarrito()">
                        <input type="hidden" name="producto_id" value="<?php echo $product_id; ?>">
                        <input type="hidden" name="precio" value="<?php echo $product['precio']; ?>">
                        <input type="hidden" id="tallaSeleccionada" name="talla" value="">
                        
                        <div class="selectors-container">
                            <span class="selector-label">Selecciona Talla</span>
                            <div class="tallas-grid">
                                <?php foreach ($tallas_disponibles as $t): ?>
                                    <div class="talla-option" 
                                         onclick="seleccionarTalla('<?php echo $t['talla']; ?>', <?php echo $t['stock']; ?>, this)">
                                        <?php echo $t['talla']; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div id="tallaError" class="talla-error">Por favor selecciona una talla.</div>

                            <span class="selector-label" style="margin-top: 1.5rem;">Cantidad</span>
                            <div class="qty-wrapper">
                                <button type="button" class="qty-btn" onclick="cambiarCantidad(-1)">−</button>
                                <input type="number" id="cantidad" name="cantidad" value="1" min="1" class="qty-input" readonly>
                                <button type="button" class="qty-btn" onclick="cambiarCantidad(1)">+</button>
                            </div>
                            <div id="stockMsg" style="font-size:0.8rem; color:#666; margin-top:5px; height:1rem;"></div>
                        </div>

                        <div class="action-buttons">
                            <button type="submit" class="btn btn-primary" id="btnAgregar">Agregar al Carrito</button>
                            <button type="button" class="btn btn-secondary" id="btnFavorito" onclick="toggleFavorito()">Guardar Favorito</button>
                        </div>
                    </form>
                <?php elseif (!$usuario_logueado): ?>
                    <div style="background:#1a1a1a; padding:1.5rem; border-radius:4px; margin-top:2rem; border:1px solid #333;">
                        <p style="margin-bottom:1rem; color:#aaa;">Inicia sesión para comprar este producto.</p>
                        
                        <a href="login.php?redirect=<?php echo urlencode('producto_detalle.php?id=' . $product_id); ?>" 
                           class="btn btn-primary" 
                           style="display:inline-block; width:auto; padding:0.8rem 2rem;">
                           Ingresar
                        </a>
                        </div>
                <?php else: ?>
                    <div style="margin-top:2rem; padding:1rem; border:1px solid #333; color:#ef4444; background:rgba(239,68,68,0.1);">Producto agotado.</div>
                <?php endif; ?>

                <div class="details-list">
                    <details class="detail-item" open>
                        <summary class="detail-summary">Descripción <span class="plus-icon">+</span></summary>
                        <div class="detail-content">
                            <?php echo nl2br(htmlspecialchars($product['descripcion'])); ?>
                        </div>
                    </details>
                    <details class="detail-item">
                        <summary class="detail-summary">Especificaciones <span class="plus-icon">+</span></summary>
                        <div class="detail-content">
                            <p><strong>Nivel:</strong> <?php echo $nivel_mostrar; ?></p>
                            <p><strong>Peso:</strong> <?php echo $product['peso']; ?> kg</p>
                            <p><strong>Velocidades:</strong> <?php echo $product['velocidades']; ?></p>
                        </div>
                    </details>
                    <details class="detail-item">
                        <summary class="detail-summary">Envío y Entrega <span class="plus-icon">+</span></summary>
                        <div class="detail-content">
                            <p>Estimado: <?php echo $fecha_envio; ?> (<?php echo $product['dias_envio']; ?> días hábiles).</p>
                            <p>Envío gratuito en compras mayores a $1,000.</p>
                        </div>
                    </details>
                </div>
            </div>
        </div>

        <?php if ($sugerencias): ?>
        <div class="suggestions-section">
            <h2 class="suggestions-title">Podría interesarte</h2>
            <div class="suggestions-grid">
                <?php foreach($sugerencias as $s): ?>
                <a href="producto_detalle.php?id=<?php echo $s['id']; ?>" class="sug-card">
                    <img src="<?php echo htmlspecialchars($s['imagen_principal']); ?>" class="sug-img">
                    <div class="sug-info">
                        <div class="sug-name"><?php echo htmlspecialchars($s['nombre']); ?></div>
                        <div class="sug-price">$<?php echo number_format($s['precio'], 2); ?></div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div id="comments-section" style="margin-top: 5rem; border-top: 1px solid #222; padding-top: 3rem;">
            <h2 style="font-size: 1.5rem; margin-bottom: 2rem; color:#fff;">Reseñas (<?php echo $total_comentarios; ?>)</h2>
            <?php foreach ($comentarios as $c): ?>
                <div class="comment-item">
                    <div class="comment-header">
                        <span class="comment-author"><?php echo htmlspecialchars($c['usuario_nombre']); ?></span>
                        <span class="comment-date"><?php echo date('d M Y', strtotime($c['fecha'])); ?></span>
                    </div>
                    <div style="color:#fbbf24; font-size:0.8rem; margin-bottom:0.5rem;"><?php echo str_repeat('★', $c['calificacion']); ?></div>
                    <p class="comment-body"><?php echo nl2br(htmlspecialchars($c['comentario'])); ?></p>
                </div>
            <?php endforeach; ?>
            
            <?php if ($usuario_logueado): ?>
            <div class="comment-form-container">
                <button class="btn btn-secondary" onclick="document.getElementById('form-comment').style.display='block'; this.style.display='none'">Escribir reseña</button>
                <form id="form-comment" action="procesar_comentario.php" method="POST" style="display:none; margin-top:1rem; max-width:600px;">
                    <input type="hidden" name="producto_id" value="<?php echo $product_id; ?>">
                    
                    <div style="margin-bottom:1rem;">
                        <label style="display:block; margin-bottom:0.5rem; font-weight:600; color:#aaa;">Calificación:</label>
                        <select name="calificacion" required class="dark-select">
                            <option value="5">★★★★★ Excelente</option>
                            <option value="4">★★★★☆ Muy bueno</option>
                            <option value="3">★★★☆☆ Bueno</option>
                            <option value="2">★★☆☆☆ Regular</option>
                            <option value="1">★☆☆☆☆ Malo</option>
                        </select>
                    </div>

                    <textarea name="comentario" class="dark-textarea" placeholder="Cuéntanos tu experiencia..." required></textarea>
                    <button type="submit" class="btn btn-primary" style="margin-top:1rem; width:auto; padding:0.8rem 2rem;">Publicar</button>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div id="toast" class="toast">
        <span id="toast-icon">✅</span>
        <span id="toast-msg">Mensaje</span>
    </div>

    <script>
        function changeImage(src, el) {
            document.getElementById('mainImage').src = src;
            document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active'));
            el.classList.add('active');
        }

        let maxStock = 1;
        function seleccionarTalla(talla, stock, el) {
            document.getElementById('tallaSeleccionada').value = talla;
            maxStock = stock;
            document.getElementById('cantidad').value = 1;
            document.querySelectorAll('.talla-option').forEach(t => t.classList.remove('selected'));
            el.classList.add('selected');
            document.getElementById('tallaError').style.display = 'none';
            document.getElementById('stockMsg').innerText = stock + ' disponibles';
        }

        function cambiarCantidad(d) {
            let v = parseInt(document.getElementById('cantidad').value) + d;
            if(v >= 1 && v <= maxStock) document.getElementById('cantidad').value = v;
        }

        function validarCarrito() {
            if(!document.getElementById('tallaSeleccionada').value) {
                document.getElementById('tallaError').style.display = 'block';
                return false;
            }
            return true;
        }

        function toggleFavorito() {
            const btn = document.getElementById('btnFavorito');
            btn.classList.toggle('active');
            if(btn.classList.contains('active')) {
                btn.innerText = 'Guardado ❤️';
                showToast('Guardado en favoritos');
            } else {
                btn.innerText = 'Guardar Favorito';
            }
        }

        function showToast(msg, error=false) {
            const t = document.getElementById('toast');
            document.getElementById('toast-msg').innerText = msg;
            document.getElementById('toast-icon').innerText = error ? '❌' : '✅';
            if(error) t.classList.add('error'); else t.classList.remove('error');
            t.classList.add('show');
            setTimeout(() => t.classList.remove('show'), 3000);
        }

        const params = new URLSearchParams(window.location.search);
        if(params.get('comentario')=='success') showToast('Comentario publicado');
        if(params.get('error')) showToast(decodeURIComponent(params.get('error')), true);
        if(params.get('success')) showToast(decodeURIComponent(params.get('success')));
    </script>
</body>
</html>