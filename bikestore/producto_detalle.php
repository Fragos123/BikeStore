<?php
session_start();
include 'conexion.php';

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

// NUEVO: Obtener tallas disponibles para este producto
$stmt = $conn->prepare("SELECT talla, stock FROM producto_tallas WHERE producto_id = ? AND activo = 1 AND stock > 0 ORDER BY FIELD(talla, 'XS', 'S', 'M', 'L', 'XL', 'XXL')");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$tallas_disponibles = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Si no hay tallas en la nueva tabla, usar la talla del producto (retrocompatibilidad)
if (empty($tallas_disponibles) && !empty($product['talla']) && $product['stock'] > 0) {
    $tallas_disponibles = [
        ['talla' => $product['talla'], 'stock' => $product['stock']]
    ];
}

// Obtener comentarios y calificación promedio
$stmt = $conn->prepare("
    SELECT c.*, u.nombre as usuario_nombre 
    FROM comentarios c 
    JOIN usuarios u ON c.usuario_id = u.id 
    WHERE c.producto_id = ? 
    ORDER BY c.fecha DESC
");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$comentarios = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Calcular calificación promedio
$stmt = $conn->prepare("SELECT AVG(calificacion) as promedio, COUNT(*) as total FROM comentarios WHERE producto_id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$calificacion_data = $stmt->get_result()->fetch_assoc();
$stmt->close();

$calificacion_promedio = $calificacion_data['promedio'] ? round($calificacion_data['promedio'], 1) : 0;
$total_comentarios = $calificacion_data['total'];

// Calcular fecha de envío estimada
$fecha_envio = date('d/m/Y', strtotime('+' . $product['dias_envio'] . ' days'));

// Usuario logueado
$usuario_logueado = isset($_SESSION['logueado']) && $_SESSION['logueado'] === true;

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['nombre']); ?> - BikeStore</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #F1F5F9 0%, #e2e8f0 100%);
            color: #2c3e50;
            line-height: 1.6;
        }

        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(173, 181, 189, 0.2);
            position: sticky;
            top: 0;
            z-index: 100;
            padding: 1rem 0;
        }

        .nav {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, #45556C, #364458);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
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
            color: #45556C;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 3rem 2rem;
        }

        .product-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            margin-bottom: 4rem;
        }

        /* Galería de imágenes */
        .image-gallery {
            position: sticky;
            top: 120px;
        }

        .main-image-container {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 20px 60px rgba(52, 73, 94, 0.08);
            border: 1px solid rgba(173, 181, 189, 0.1);
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .main-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 12px;
            transition: transform 0.3s ease;
        }

        .main-image-container:hover .main-image {
            transform: scale(1.05);
        }

        .thumbnail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 1rem;
        }

        .thumbnail {
            aspect-ratio: 1;
            background: white;
            border-radius: 12px;
            padding: 0.5rem;
            border: 2px solid transparent;
            cursor: pointer;
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .thumbnail:hover, .thumbnail.active {
            border-color: #45556C;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(69, 85, 108, 0.2);
        }

        .thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 6px;
        }

        .placeholder-thumb {
            display: flex;
            align-items: center;
            justify-content: center;
            color: #cbd5e0;
            font-size: 2rem;
        }

        /* Información del producto */
        .product-info {
            padding: 1rem 0;
        }

        .product-badge {
            display: inline-block;
            background: linear-gradient(135deg, #45556C, #364458);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .product-title {
            font-size: 2.5rem;
            font-weight: 800;
            color: #2c3e50;
            margin-bottom: 1rem;
            letter-spacing: -1px;
        }

        /* Calificación con estrellas */
        .rating-section {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 1.5rem 0;
            padding: 1rem;
            background: linear-gradient(135deg, #fff5e6 0%, #ffe6cc 100%);
            border-radius: 12px;
        }

        .stars {
            display: flex;
            gap: 0.25rem;
            font-size: 1.5rem;
        }

        .star {
            color: #ffd700;
            transition: transform 0.2s ease;
        }

        .star.empty {
            color: #e2e8f0;
        }

        .star:hover {
            transform: scale(1.2);
        }

        .rating-text {
            color: #6c757d;
            font-weight: 600;
        }

        .rating-number {
            font-size: 1.8rem;
            font-weight: 800;
            color: #ffa500;
        }

        /* Especificaciones del producto */
        .specs-section {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            margin: 2rem 0;
            box-shadow: 0 10px 30px rgba(52, 73, 94, 0.05);
        }

        .specs-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            margin-top: 1rem;
        }

        .spec-item {
            background: linear-gradient(135deg, #F1F5F9 0%, #e9ecef 100%);
            padding: 1.25rem;
            border-radius: 12px;
            border-left: 4px solid #45556C;
            transition: all 0.3s ease;
        }

        .spec-item:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(69, 85, 108, 0.15);
        }

        .spec-label {
            font-size: 0.85rem;
            color: #718096;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }

        .spec-value {
            font-size: 1.3rem;
            font-weight: 700;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .spec-icon {
            font-size: 1.5rem;
        }

        /* Badges especiales */
        .size-badge {
            display: inline-block;
            background: linear-gradient(135deg, #45556C, #364458);
            color: white;
            padding: 0.5rem 1.25rem;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 800;
            letter-spacing: 1px;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .level-badge {
            display: inline-block;
            padding: 0.5rem 1.25rem;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 700;
        }

        .level-principiante {
            background: linear-gradient(135deg, #48bb78, #38a169);
            color: white;
        }

        .level-intermedio {
            background: linear-gradient(135deg, #ed8936, #dd6b20);
            color: white;
        }

        .level-avanzado {
            background: linear-gradient(135deg, #e53e3e, #c53030);
            color: white;
        }

        /* Descripción */
        .description-section {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            margin: 2rem 0;
            box-shadow: 0 10px 30px rgba(52, 73, 94, 0.05);
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 1rem;
        }

        .description-text {
            color: #4a5568;
            line-height: 1.8;
        }

        /* Fecha de envío */
        .shipping-info {
            background: linear-gradient(135deg, #e6f7ff 0%, #cce7ff 100%);
            padding: 1.5rem;
            border-radius: 12px;
            margin: 2rem 0;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .shipping-icon {
            font-size: 2.5rem;
        }

        .shipping-text {
            flex: 1;
        }

        .shipping-title {
            font-weight: 700;
            color: #2c5282;
            margin-bottom: 0.25rem;
        }

        .shipping-date {
            color: #4299e1;
            font-size: 1.1rem;
            font-weight: 600;
        }

        /* Precio y selector de talla */
        .price-section {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            margin: 2rem 0;
            box-shadow: 0 10px 30px rgba(52, 73, 94, 0.05);
        }

        .current-price {
            font-size: 2.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, #45556C, #364458);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }

        .stock-info {
            color: #38a169;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .stock-info.out-of-stock {
            color: #e53e3e;
        }

        /* Selector de talla */
        .talla-selector {
            margin: 1.5rem 0;
        }

        .talla-label {
            display: block;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.75rem;
            font-size: 0.95rem;
        }

        .required-mark {
            color: #e53e3e;
        }

        .tallas-grid {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .talla-option {
            padding: 0.75rem 1.5rem;
            border: 2px solid #e2e8f0;
            background: white;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            color: #4a5568;
            font-size: 1rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.25rem;
        }

        .talla-option:hover {
            border-color: #45556C;
            background: #F1F5F9;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(69, 85, 108, 0.2);
        }

        .talla-option.selected {
            border-color: #45556C;
            background: linear-gradient(135deg, #45556C, #364458);
            color: white;
            box-shadow: 0 8px 20px rgba(69, 85, 108, 0.3);
        }

        .talla-stock-label {
            font-size: 0.8rem;
            opacity: 0.8;
        }

        .talla-error {
            color: #e53e3e;
            font-size: 0.9rem;
            margin-top: 0.5rem;
            display: none;
            font-weight: 500;
        }

        .no-tallas-warning {
            background: #fee2e2;
            border: 2px solid #ef4444;
            border-radius: 12px;
            padding: 1.5rem;
            color: #991b1b;
            font-weight: 600;
            text-align: center;
        }

        /* Selector de cantidad */
        .cantidad-selector {
            margin: 1.5rem 0;
        }

        .cantidad-label {
            display: block;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.75rem;
            font-size: 0.95rem;
        }

        .cantidad-controls {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .qty-btn {
            width: 40px;
            height: 40px;
            border: 2px solid #e2e8f0;
            background: white;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.2rem;
            font-weight: 600;
            transition: all 0.2s ease;
            color: #4a5568;
        }

        .qty-btn:hover {
            border-color: #45556C;
            color: #45556C;
            background: #F1F5F9;
        }

        .qty-input {
            width: 60px;
            text-align: center;
            padding: 0.5rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c3e50;
        }

        .stock-max {
            color: #718096;
            font-size: 0.9rem;
        }

        /* Botones de acción */
        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .btn {
            flex: 1;
            padding: 1.2rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            text-align: center;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #45556C, #364458);
            color: white;
            box-shadow: 0 10px 30px rgba(69, 85, 108, 0.3);
        }

        .btn-primary:hover:not(:disabled) {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(69, 85, 108, 0.4);
        }

        .btn-primary:disabled {
            background: #cbd5e0;
            cursor: not-allowed;
            box-shadow: none;
        }

        .btn-secondary {
            background: white;
            color: #45556C;
            border: 2px solid #45556C;
        }

        .btn-secondary:hover {
            background: #F1F5F9;
        }

        /* Mensaje para usuarios no logueados */
        .login-required {
            background: #fef2f2;
            padding: 1.5rem;
            border-radius: 12px;
            border-left: 4px solid #e53e3e;
            margin: 1.5rem 0;
        }

        .login-required-title {
            color: #991b1b;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .login-required-text {
            color: #b91c1c;
            font-size: 0.9rem;
        }

        /* Sección de comentarios */
        .comments-section {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 10px 30px rgba(52, 73, 94, 0.05);
            margin-top: 4rem;
        }

        .comments-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #F1F5F9;
        }

        .toggle-comments {
            background: linear-gradient(135deg, #45556C, #364458);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .toggle-comments:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(69, 85, 108, 0.3);
        }

        .comments-list {
            display: none;
        }

        .comments-list.show {
            display: block;
        }

        .comment-item {
            padding: 1.5rem;
            border-bottom: 1px solid #F1F5F9;
            animation: fadeInUp 0.4s ease;
        }

        .comment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
        }

        .comment-author {
            font-weight: 700;
            color: #2c3e50;
        }

        .comment-date {
            color: #a0aec0;
            font-size: 0.85rem;
        }

        .comment-rating {
            display: flex;
            gap: 0.25rem;
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }

        .comment-text {
            color: #4a5568;
            line-height: 1.6;
        }

        /* Formulario de comentarios */
        .add-comment-section {
            background: #F1F5F9;
            padding: 2rem;
            border-radius: 12px;
            margin-top: 2rem;
        }

        .add-comment-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 1rem;
        }

        .comment-form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .rating-input {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .rating-stars {
            display: flex;
            gap: 0.5rem;
        }

        .rating-star-input {
            font-size: 2rem;
            cursor: pointer;
            color: #e2e8f0;
            transition: all 0.2s ease;
        }

        .rating-star-input:hover,
        .rating-star-input.selected {
            color: #ffd700;
            transform: scale(1.1);
        }

        .comment-textarea {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-family: inherit;
            font-size: 1rem;
            resize: vertical;
            min-height: 120px;
        }

        .comment-textarea:focus {
            outline: none;
            border-color: #45556C;
            box-shadow: 0 0 0 3px rgba(69, 85, 108, 0.1);
        }

        .submit-comment {
            background: linear-gradient(135deg, #45556C, #364458);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            align-self: flex-start;
        }

        .submit-comment:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(69, 85, 108, 0.3);
        }

        .login-message {
            text-align: center;
            padding: 2rem;
            color: #718096;
        }

        .login-link {
            color: #45556C;
            font-weight: 600;
            text-decoration: none;
        }

        .login-link:hover {
            text-decoration: underline;
        }

        /* Animaciones */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .product-layout {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .image-gallery {
                position: relative;
                top: 0;
            }

            .specs-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .product-title {
                font-size: 2rem;
            }

            .current-price {
                font-size: 2rem;
            }

            .action-buttons {
                flex-direction: column;
            }

            .thumbnail-grid {
                grid-template-columns: repeat(3, 1fr);
            }

            .comments-section {
                padding: 1.5rem;
            }

            .tallas-grid {
                justify-content: center;
            }

            .cantidad-controls {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="nav">
            <a href="index.php" class="logo">BIKESTORE</a>
            <a href="productos.php" class="back-link">← Volver a productos</a>
        </nav>
    </header>

    <div class="container">
        <div class="product-layout">
            <!-- Galería de imágenes -->
            <div class="image-gallery">
                <div class="main-image-container">
                    <?php if (!empty($product['imagen_principal']) || !empty($product['imagen'])): ?>
                        <img src="<?php echo htmlspecialchars($product['imagen_principal'] ?: $product['imagen']); ?>" 
                             alt="<?php echo htmlspecialchars($product['nombre']); ?>" 
                             class="main-image" 
                             id="mainImage">
                    <?php else: ?>
                        <div style="font-size: 5rem; color: #cbd5e0;">🚴</div>
                    <?php endif; ?>
                </div>

                <div class="thumbnail-grid">
                    <?php
                    $imagenes = [
                        $product['imagen_principal'] ?: $product['imagen'],
                        $product['imagen_2'],
                        $product['imagen_3'],
                        $product['imagen_4'],
                        $product['imagen_5'],
                        $product['imagen_6']
                    ];
                    
                    $contador = 0;
                    foreach ($imagenes as $index => $imagen):
                        if (!empty($imagen) && $contador < 6):
                            $contador++;
                    ?>
                        <div class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>" 
                             onclick="changeImage('<?php echo htmlspecialchars($imagen); ?>', this)">
                            <img src="<?php echo htmlspecialchars($imagen); ?>" 
                                 alt="Vista <?php echo $contador; ?>">
                        </div>
                    <?php 
                        endif;
                    endforeach; 
                    
                    // Rellenar con placeholders si faltan imágenes
                    for ($i = $contador; $i < 6; $i++):
                    ?>
                        <div class="thumbnail">
                            <div class="placeholder-thumb">📷</div>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- Información del producto -->
            <div class="product-info">
                <div class="product-badge"><?php echo ucfirst($product['tipo']); ?></div>
                
                <h1 class="product-title"><?php echo htmlspecialchars($product['nombre']); ?></h1>

                <!-- Calificación con estrellas -->
                <div class="rating-section">
                    <div class="rating-number"><?php echo number_format($calificacion_promedio, 1); ?></div>
                    <div>
                        <div class="stars">
                            <?php 
                            for ($i = 1; $i <= 5; $i++):
                                if ($i <= floor($calificacion_promedio)):
                            ?>
                                <span class="star">★</span>
                            <?php else: ?>
                                <span class="star empty">★</span>
                            <?php 
                                endif;
                            endfor; 
                            ?>
                        </div>
                        <div class="rating-text"><?php echo $total_comentarios; ?> comentario<?php echo $total_comentarios != 1 ? 's' : ''; ?></div>
                    </div>
                </div>

                <!-- Especificaciones -->
                <div class="specs-section">
                    <h2 class="section-title">Especificaciones</h2>
                    <div class="specs-grid">
                        <!-- Tallas Disponibles -->
                        <?php if (!empty($tallas_disponibles)): ?>
                        <div class="spec-item">
                            <div class="spec-label">📏 Tallas Disponibles</div>
                            <div class="spec-value">
                                <?php foreach($tallas_disponibles as $t): ?>
                                    <span class="size-badge">
                                        <?php echo htmlspecialchars($t['talla']); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Nivel de Ciclismo -->
                        <?php if (!empty($product['nivel_ciclismo'])): ?>
                        <div class="spec-item">
                            <div class="spec-label">🎯 Nivel de Ciclismo</div>
                            <div class="spec-value">
                                <span class="level-badge level-<?php echo strtolower($product['nivel_ciclismo']); ?>">
                                    <?php echo ucfirst($product['nivel_ciclismo']); ?>
                                </span>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Peso -->
                        <?php if (!empty($product['peso'])): ?>
                        <div class="spec-item">
                            <div class="spec-label">⚖️ Peso</div>
                            <div class="spec-value">
                                <span class="spec-icon">⚖️</span>
                                <?php echo number_format($product['peso'], 1); ?> kg
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Velocidades -->
                        <?php if (!empty($product['velocidades'])): ?>
                        <div class="spec-item">
                            <div class="spec-label">⚙️ Velocidades</div>
                            <div class="spec-value">
                                <span class="spec-icon">⚙️</span>
                                <?php echo $product['velocidades']; ?> velocidades
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Descripción -->
                <div class="description-section">
                    <h2 class="section-title">Descripción</h2>
                    <p class="description-text"><?php echo nl2br(htmlspecialchars($product['descripcion'])); ?></p>
                </div>

                <!-- Fecha de envío estimada -->
                <div class="shipping-info">
                    <div class="shipping-icon">🚚</div>
                    <div class="shipping-text">
                        <div class="shipping-title">Envío estimado</div>
                        <div class="shipping-date">Llega el <?php echo $fecha_envio; ?></div>
                        <div style="color: #718096; font-size: 0.9rem; margin-top: 0.25rem;">
                            (<?php echo $product['dias_envio']; ?> días hábiles)
                        </div>
                    </div>
                </div>

                <!-- Precio y carrito -->
                <div class="price-section">
                    <div class="current-price">$<?php echo number_format($product['precio'], 2); ?></div>
                    <div class="stock-info <?php echo empty($tallas_disponibles) ? 'out-of-stock' : ''; ?>">
                        <?php if (!empty($tallas_disponibles)): ?>
                            ✓ Disponible en <?php echo count($tallas_disponibles); ?> talla<?php echo count($tallas_disponibles) != 1 ? 's' : ''; ?>
                        <?php else: ?>
                            ✗ Sin stock disponible
                        <?php endif; ?>
                    </div>

                    <?php if ($usuario_logueado): ?>
                        <?php if (!empty($tallas_disponibles)): ?>
                        <!-- Formulario para agregar al carrito -->
                        <form id="addToCartForm" action="agregar_carrito.php" method="POST" onsubmit="return validarCarrito()">
                            <input type="hidden" name="producto_id" value="<?php echo $product_id; ?>">
                            <input type="hidden" name="precio" value="<?php echo $product['precio']; ?>">
                            <input type="hidden" id="tallaSeleccionada" name="talla" value="">
                            
                            <!-- Selector de talla -->
                            <div class="talla-selector">
                                <label class="talla-label">
                                    Selecciona tu talla <span class="required-mark">*</span>
                                </label>
                                <div class="tallas-grid">
                                    <?php foreach ($tallas_disponibles as $talla_data): ?>
                                        <button type="button" 
                                                class="talla-option" 
                                                data-talla="<?php echo htmlspecialchars($talla_data['talla']); ?>"
                                                data-stock="<?php echo $talla_data['stock']; ?>"
                                                onclick="seleccionarTalla('<?php echo htmlspecialchars($talla_data['talla']); ?>', <?php echo $talla_data['stock']; ?>, this)">
                                            <span><?php echo htmlspecialchars($talla_data['talla']); ?></span>
                                            <span class="talla-stock-label">(<?php echo $talla_data['stock']; ?> disp.)</span>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                                <div id="tallaError" class="talla-error">
                                    ⚠️ Por favor selecciona una talla
                                </div>
                            </div>

                            <!-- Selector de cantidad -->
                            <div class="cantidad-selector">
                                <label class="cantidad-label">Cantidad</label>
                                <div class="cantidad-controls">
                                    <button type="button" onclick="cambiarCantidad(-1)" class="qty-btn">−</button>
                                    <input type="number" 
                                           id="cantidad" 
                                           name="cantidad" 
                                           value="1" 
                                           min="1" 
                                           max="1"
                                           class="qty-input"
                                           readonly>
                                    <button type="button" onclick="cambiarCantidad(1)" class="qty-btn">+</button>
                                    <span class="stock-max" id="stockMax">(Selecciona una talla)</span>
                                </div>
                            </div>

                            <div class="action-buttons">
                                <button type="submit" 
                                        class="btn btn-primary" 
                                        id="btnAgregar">
                                    🛒 Agregar al carrito
                                </button>
                                <button type="button" class="btn btn-secondary">
                                    ♥ Favoritos
                                </button>
                            </div>
                        </form>
                        <?php else: ?>
                        <!-- No hay tallas disponibles -->
                        <div class="no-tallas-warning">
                            ⚠️ Este producto no tiene tallas disponibles en este momento
                        </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <!-- Usuario no logueado -->
                        <div class="login-required">
                            <p class="login-required-title">🔒 Inicia sesión para comprar</p>
                            <p class="login-required-text">
                                Debes iniciar sesión para agregar productos al carrito
                            </p>
                        </div>
                        <div class="action-buttons">
                            <a href="login.php" class="btn btn-primary" style="text-align: center;">
                                Iniciar Sesión
                            </a>
                            <a href="registro.php" class="btn btn-secondary" style="text-align: center;">
                                Crear Cuenta
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sección de comentarios -->
        <div class="comments-section">
            <div class="comments-header">
                <h2 class="section-title">Comentarios y Reseñas (<?php echo $total_comentarios; ?>)</h2>
                <button class="toggle-comments" onclick="toggleComments()">
                    <span id="toggleText">Ver comentarios</span>
                </button>
            </div>

            <div class="comments-list" id="commentsList">
                <?php if (empty($comentarios)): ?>
                    <p style="text-align: center; color: #a0aec0; padding: 2rem;">
                        No hay comentarios aún. ¡Sé el primero en comentar!
                    </p>
                <?php else: ?>
                    <?php foreach ($comentarios as $comentario): ?>
                        <div class="comment-item">
                            <div class="comment-header">
                                <span class="comment-author"><?php echo htmlspecialchars($comentario['usuario_nombre']); ?></span>
                                <span class="comment-date"><?php echo date('d/m/Y', strtotime($comentario['fecha'])); ?></span>
                            </div>
                            <div class="comment-rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span class="star <?php echo $i > $comentario['calificacion'] ? 'empty' : ''; ?>">★</span>
                                <?php endfor; ?>
                            </div>
                            <p class="comment-text"><?php echo nl2br(htmlspecialchars($comentario['comentario'])); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <!-- Formulario para agregar comentario -->
                <div class="add-comment-section">
                    <h3 class="add-comment-title">Agregar tu comentario</h3>
                    
                    <?php if ($usuario_logueado): ?>
                        <form class="comment-form" action="procesar_comentario.php" method="POST" onsubmit="return validateComment()">
                            <input type="hidden" name="producto_id" value="<?php echo $product_id; ?>">
                            
                            <div class="rating-input">
                                <span style="font-weight: 600;">Tu calificación:</span>
                                <div class="rating-stars" id="ratingStars">
                                    <span class="rating-star-input" data-rating="1" onclick="setRating(1)">★</span>
                                    <span class="rating-star-input" data-rating="2" onclick="setRating(2)">★</span>
                                    <span class="rating-star-input" data-rating="3" onclick="setRating(3)">★</span>
                                    <span class="rating-star-input" data-rating="4" onclick="setRating(4)">★</span>
                                    <span class="rating-star-input" data-rating="5" onclick="setRating(5)">★</span>
                                </div>
                                <input type="hidden" name="calificacion" id="calificacionInput" value="0" required>
                            </div>
                            
                            <textarea class="comment-textarea" 
                                      name="comentario" 
                                      id="comentarioText"
                                      placeholder="Escribe tu opinión sobre este producto..." 
                                      required></textarea>
                            
                            <button type="submit" class="submit-comment">Publicar comentario</button>
                        </form>
                    <?php else: ?>
                        <div class="login-message">
                            <p>Debes <a href="login.php" class="login-link">iniciar sesión</a> para agregar un comentario</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // ========== FUNCIONES DEL CARRITO ==========
        
        let tallaSeleccionadaGlobal = '';
        let stockActual = 1;

        // Seleccionar talla con stock dinámico
        function seleccionarTalla(talla, stock, button) {
            // Remover selección previa
            document.querySelectorAll('.talla-option').forEach(btn => {
                btn.classList.remove('selected');
            });
            
            // Agregar selección actual
            button.classList.add('selected');
            tallaSeleccionadaGlobal = talla;
            stockActual = stock;
            document.getElementById('tallaSeleccionada').value = talla;
            
            // Actualizar el stock máximo del selector de cantidad
            const cantidadInput = document.getElementById('cantidad');
            if (cantidadInput) {
                cantidadInput.max = stock;
                cantidadInput.value = 1;
                
                // Actualizar texto de stock máximo
                const stockMaxText = document.getElementById('stockMax');
                if (stockMaxText) {
                    stockMaxText.textContent = `(Máx: ${stock})`;
                }
            }
            
            // Ocultar error
            document.getElementById('tallaError').style.display = 'none';
        }

        // Cambiar cantidad
        function cambiarCantidad(delta) {
            const input = document.getElementById('cantidad');
            const max = parseInt(input.max);
            let valor = parseInt(input.value) + delta;
            
            if (valor < 1) valor = 1;
            if (valor > max) {
                valor = max;
                mostrarNotificacion('Stock máximo de esta talla: ' + max + ' unidades', 'warning');
            }
            
            input.value = valor;
        }

        // Validar antes de enviar al carrito
        function validarCarrito() {
            const talla = document.getElementById('tallaSeleccionada').value;
            const tallaError = document.getElementById('tallaError');
            
            if (!talla || talla === '') {
                tallaError.style.display = 'block';
                
                // Scroll hacia el selector de talla
                document.querySelector('.talla-selector').scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'center' 
                });
                
                // Shake animation
                const tallaSelector = document.querySelector('.talla-selector');
                tallaSelector.style.animation = 'shake 0.5s';
                setTimeout(() => {
                    tallaSelector.style.animation = '';
                }, 500);
                
                return false;
            }
            
            // Mostrar loading en el botón
            const btnAgregar = document.getElementById('btnAgregar');
            const textoOriginal = btnAgregar.innerHTML;
            btnAgregar.innerHTML = '⏳ Agregando...';
            btnAgregar.disabled = true;
            
            // Si hay error, restaurar botón después de 3 segundos
            setTimeout(() => {
                if (window.location.href.includes('producto_detalle.php')) {
                    btnAgregar.innerHTML = textoOriginal;
                    btnAgregar.disabled = false;
                }
            }, 3000);
            
            return true;
        }

        // Mostrar notificación
        function mostrarNotificacion(mensaje, tipo = 'info') {
            const notif = document.createElement('div');
            notif.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 1rem 1.5rem;
                background: ${tipo === 'warning' ? '#fff3cd' : tipo === 'error' ? '#fed7d7' : '#d1ecf1'};
                color: ${tipo === 'warning' ? '#856404' : tipo === 'error' ? '#991b1b' : '#0c5460'};
                border: 2px solid ${tipo === 'warning' ? '#ffeaa7' : tipo === 'error' ? '#fc8181' : '#bee5eb'};
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                z-index: 10000;
                animation: slideInRight 0.3s ease;
                max-width: 300px;
                font-weight: 600;
            `;
            notif.textContent = mensaje;
            document.body.appendChild(notif);
            
            setTimeout(() => {
                notif.style.animation = 'slideOutRight 0.3s ease';
                setTimeout(() => notif.remove(), 300);
            }, 3000);
        }

        // ========== FUNCIONES DE GALERÍA ==========
        
        // Cambiar imagen principal
        function changeImage(src, thumbnail) {
            document.getElementById('mainImage').src = src;
            
            // Actualizar thumbnails activos
            document.querySelectorAll('.thumbnail').forEach(thumb => {
                thumb.classList.remove('active');
            });
            thumbnail.classList.add('active');
        }

        // ========== FUNCIONES DE COMENTARIOS ==========
        
        // Toggle comentarios
        function toggleComments() {
            const commentsList = document.getElementById('commentsList');
            const toggleText = document.getElementById('toggleText');
            
            if (commentsList.classList.contains('show')) {
                commentsList.classList.remove('show');
                toggleText.textContent = 'Ver comentarios';
            } else {
                commentsList.classList.add('show');
                toggleText.textContent = 'Ocultar comentarios';
            }
        }

        // Sistema de calificación por estrellas
        let selectedRating = 0;

        function setRating(rating) {
            selectedRating = rating;
            document.getElementById('calificacionInput').value = rating;
            
            // Actualizar estrellas visuales
            const stars = document.querySelectorAll('.rating-star-input');
            stars.forEach((star, index) => {
                if (index < rating) {
                    star.classList.add('selected');
                } else {
                    star.classList.remove('selected');
                }
            });
        }

        // Validar formulario de comentario
        function validateComment() {
            const rating = document.getElementById('calificacionInput').value;
            const comment = document.getElementById('comentarioText').value.trim();
            
            if (rating == 0) {
                alert('Por favor selecciona una calificación');
                return false;
            }
            
            if (comment.length < 10) {
                alert('El comentario debe tener al menos 10 caracteres');
                return false;
            }
            
            return true;
        }

        // ========== INICIALIZACIÓN ==========
        
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            
            // Mensajes de comentarios
            if (urlParams.get('comentario') === 'success') {
                mostrarNotificacion('¡Comentario agregado exitosamente!', 'success');
                document.getElementById('commentsList').classList.add('show');
                document.getElementById('toggleText').textContent = 'Ocultar comentarios';
            }
            
            if (urlParams.get('comentario') === 'actualizado') {
                mostrarNotificacion('Comentario actualizado exitosamente', 'success');
                document.getElementById('commentsList').classList.add('show');
                document.getElementById('toggleText').textContent = 'Ocultar comentarios';
            }
            
            if (urlParams.get('comentario') === 'error') {
                const mensaje = urlParams.get('mensaje') || 'Error al agregar el comentario';
                mostrarNotificacion(decodeURIComponent(mensaje), 'error');
            }

            // Mensajes del carrito
            if (urlParams.get('error')) {
                mostrarNotificacion(decodeURIComponent(urlParams.get('error')), 'error');
            }

            if (urlParams.get('success')) {
                mostrarNotificacion(decodeURIComponent(urlParams.get('success')), 'success');
            }
        });

        // Agregar estilos para animaciones
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideInRight {
                from {
                    transform: translateX(400px);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            
            @keyframes slideOutRight {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(400px);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>