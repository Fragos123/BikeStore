<?php

session_start();

// Verificar si el usuario está logueado y es operador
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true || $_SESSION['usuario_rol'] !== 'operador') {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Producto - BikeStore</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #45556C 0%, #364458 100%);
            min-height: 100vh;
            padding: 2rem;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
        }

        .back-button {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 2rem;
        }

        .back-button:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateX(-5px);
        }

        .form-card {
            background: white;
            border-radius: 24px;
            padding: 3rem;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.15);
            animation: fadeInUp 0.6s ease-out;
        }

        .form-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .form-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #45556C, #364458);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin: 0 auto 1.5rem;
        }

        .form-title {
            font-size: 2rem;
            font-weight: 900;
            color: #2d3748;
            margin-bottom: 0.5rem;
        }

        .form-subtitle {
            color: #718096;
            font-size: 1rem;
        }

        .product-form {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .form-label {
            font-weight: 600;
            color: #2d3748;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .required {
            color: #e53e3e;
        }

        .form-input,
        .form-select,
        .form-textarea {
            padding: 1rem 1.25rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            font-family: inherit;
            background: #F1F5F9;
        }

        .form-textarea {
            min-height: 120px;
            resize: vertical;
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: #45556C;
            background: white;
            box-shadow: 0 0 0 3px rgba(69, 85, 108, 0.1);
        }

        .form-help {
            font-size: 0.85rem;
            color: #718096;
            font-style: italic;
        }

        .info-box {
            background: #eff6ff;
            border-left: 4px solid #45556C;
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
        }

        .info-box-title {
            font-weight: 600;
            color: #45556C;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .info-box-text {
            color: #364458;
            font-size: 0.85rem;
            line-height: 1.5;
        }

        /* NUEVO: Estilos para selector de tallas */
        .tallas-section {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border: 2px solid #45556C;
            border-radius: 16px;
            padding: 1.5rem;
            margin: 1.5rem 0;
        }

        .tallas-section-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #45556C;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .tallas-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .talla-card {
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 1rem;
            transition: all 0.3s ease;
        }

        .talla-card.selected {
            border-color: #45556C;
            background: #eff6ff;
            box-shadow: 0 4px 12px rgba(69, 85, 108, 0.2);
        }

        .talla-checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
        }

        .talla-checkbox {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }

        .talla-name {
            font-size: 1.1rem;
            font-weight: 700;
            color: #2d3748;
        }

        .talla-stock-input {
            width: 100%;
            padding: 0.5rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            text-align: center;
        }

        .talla-stock-input:disabled {
            background: #F1F5F9;
            color: #94a3b8;
            cursor: not-allowed;
        }

        .talla-stock-input:focus {
            outline: none;
            border-color: #45556C;
        }

        .tallas-warning {
            background: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 8px;
            padding: 0.75rem;
            margin-top: 1rem;
            color: #856404;
            font-size: 0.9rem;
            display: none;
        }

        .tallas-warning.show {
            display: block;
        }

        .button-group {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .btn {
            flex: 1;
            padding: 1rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #45556C, #364458);
            color: white;
            box-shadow: 0 10px 30px rgba(69, 85, 108, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 40px rgba(69, 85, 108, 0.4);
        }

        .btn-secondary {
            background: #F1F5F9;
            color: #4a5568;
            border: 2px solid #e2e8f0;
        }

        .btn-secondary:hover {
            background: #e2e8f0;
            border-color: #cbd5e0;
        }

        .message {
            padding: 1rem 1.25rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .message.success {
            background: #c6f6d5;
            color: #22543d;
            border-left: 4px solid #38a169;
        }

        .message.error {
            background: #fed7d7;
            color: #742a2a;
            border-left: 4px solid #e53e3e;
        }

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

        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .form-card {
                padding: 2rem 1.5rem;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .button-group {
                flex-direction: column;
            }

            .tallas-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="principal_operador.php" class="back-button">← Volver al panel</a>

        <div class="form-card">
            <div class="form-header">
                <div class="form-icon">🚴</div>
                <h1 class="form-title">Crear Nuevo Producto</h1>
                <p class="form-subtitle">Agrega una nueva bicicleta al catálogo</p>
            </div>

            <div class="info-box">
                <div class="info-box-title">💡 Nuevo: Sistema de Tallas Múltiples</div>
                <div class="info-box-text">
                    Ahora puedes agregar múltiples tallas para el mismo producto, cada una con su stock independiente. 
                    Selecciona las tallas disponibles y asigna el stock para cada una.
                </div>
            </div>

            <!-- Mensajes -->
            <div id="mensaje"></div>

            <form class="product-form" action="procesar_operador_crear_producto.php" method="POST" onsubmit="return validarFormulario()">
                
                <!-- Información básica -->
                <div class="form-group">
                    <label class="form-label" for="nombre">
                        Nombre del Producto <span class="required">*</span>
                    </label>
                    <input type="text" id="nombre" name="nombre" class="form-input" 
                           placeholder="Ej: Trek Marlin 7" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="descripcion">
                        Descripción <span class="required">*</span>
                    </label>
                    <textarea id="descripcion" name="descripcion" class="form-textarea" 
                              placeholder="Describe las características principales del producto..." required></textarea>
                    <span class="form-help">Mínimo 50 caracteres</span>
                </div>

                <!-- Tipo y especificaciones -->
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="tipo">
                            Tipo <span class="required">*</span>
                        </label>
                        <select id="tipo" name="tipo" class="form-select" required>
                            <option value="">Selecciona un tipo</option>
                            <option value="montaña">Montaña</option>
                            <option value="ruta">Ruta</option>
                            <option value="urbana">Urbana</option>
                            <option value="eléctrica">Eléctrica</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="precio">
                            Precio <span class="required">*</span>
                        </label>
                        <input type="number" id="precio" name="precio" class="form-input" 
                               placeholder="15999.00" step="0.01" min="0" required>
                    </div>
                </div>

                <!-- NUEVA SECCIÓN: Selector de Tallas con Stock -->
                <div class="tallas-section">
                    <div class="tallas-section-title">
                        📏 Tallas Disponibles <span class="required">*</span>
                    </div>
                    <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 1rem;">
                        Selecciona las tallas que estarán disponibles y asigna el stock para cada una
                    </p>

                    <div class="tallas-grid">
                        <?php
                        $tallas_disponibles = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
                        foreach ($tallas_disponibles as $talla):
                        ?>
                        <div class="talla-card" id="card-<?php echo $talla; ?>">
                            <div class="talla-checkbox-wrapper">
                                <input type="checkbox" 
                                       class="talla-checkbox" 
                                       id="talla-<?php echo $talla; ?>" 
                                       name="tallas[]" 
                                       value="<?php echo $talla; ?>"
                                       onchange="toggleTallaStock('<?php echo $talla; ?>')">
                                <label for="talla-<?php echo $talla; ?>" class="talla-name">
                                    <?php echo $talla; ?>
                                </label>
                            </div>
                            <input type="number" 
                                   class="talla-stock-input" 
                                   id="stock-<?php echo $talla; ?>" 
                                   name="stock_<?php echo $talla; ?>" 
                                   placeholder="Stock"
                                   min="0"
                                   value="0"
                                   disabled>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="tallas-warning" id="tallas-warning">
                        ⚠️ Debes seleccionar al menos una talla con stock mayor a 0
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="nivel_ciclismo">
                            Nivel de Ciclismo <span class="required">*</span>
                        </label>
                        <select id="nivel_ciclismo" name="nivel_ciclismo" class="form-select" required>
                            <option value="">Selecciona un nivel</option>
                            <option value="principiante">Principiante</option>
                            <option value="intermedio">Intermedio</option>
                            <option value="avanzado">Avanzado</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="dias_envio">
                            Días de envío <span class="required">*</span>
                        </label>
                        <input type="number" id="dias_envio" name="dias_envio" class="form-input" 
                               placeholder="7" min="1" max="60" value="7" required>
                        <span class="form-help">Días estimados de entrega</span>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="peso">Peso (kg)</label>
                        <input type="number" id="peso" name="peso" class="form-input" 
                               placeholder="13.5" step="0.1" min="0">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="velocidades">Velocidades</label>
                        <input type="number" id="velocidades" name="velocidades" class="form-input" 
                               placeholder="21" min="1" max="50">
                    </div>
                </div>

                <!-- Imágenes -->
                <div class="form-group">
                    <label class="form-label" for="imagen_principal">
                        Imagen Principal <span class="required">*</span>
                    </label>
                    <input type="url" id="imagen_principal" name="imagen_principal" class="form-input" 
                           placeholder="https://ejemplo.com/imagen.jpg" required>
                    <span class="form-help">URL de la imagen principal del producto</span>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="imagen_2">Imagen 2</label>
                        <input type="url" id="imagen_2" name="imagen_2" class="form-input" 
                               placeholder="https://ejemplo.com/imagen2.jpg">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="imagen_3">Imagen 3</label>
                        <input type="url" id="imagen_3" name="imagen_3" class="form-input" 
                               placeholder="https://ejemplo.com/imagen3.jpg">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="imagen_4">Imagen 4</label>
                        <input type="url" id="imagen_4" name="imagen_4" class="form-input" 
                               placeholder="https://ejemplo.com/imagen4.jpg">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="imagen_5">Imagen 5</label>
                        <input type="url" id="imagen_5" name="imagen_5" class="form-input" 
                               placeholder="https://ejemplo.com/imagen5.jpg">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="imagen_6">Imagen 6</label>
                    <input type="url" id="imagen_6" name="imagen_6" class="form-input" 
                           placeholder="https://ejemplo.com/imagen6.jpg">
                </div>

                <div class="button-group">
                    <button type="submit" class="btn btn-primary">
                        ✓ Crear Producto
                    </button>
                    <a href="principal_operador.php" class="btn btn-secondary">
                        ✕ Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Toggle stock input cuando se selecciona/deselecciona una talla
        function toggleTallaStock(talla) {
            const checkbox = document.getElementById('talla-' + talla);
            const stockInput = document.getElementById('stock-' + talla);
            const card = document.getElementById('card-' + talla);
            
            if (checkbox.checked) {
                stockInput.disabled = false;
                stockInput.required = true;
                stockInput.focus();
                card.classList.add('selected');
            } else {
                stockInput.disabled = true;
                stockInput.required = false;
                stockInput.value = '0';
                card.classList.remove('selected');
            }
            
            // Ocultar warning si hay al menos una talla seleccionada
            const tallasSeleccionadas = document.querySelectorAll('.talla-checkbox:checked').length;
            document.getElementById('tallas-warning').classList.toggle('show', tallasSeleccionadas === 0);
        }

        function validarFormulario() {
            const nombre = document.getElementById('nombre').value.trim();
            const descripcion = document.getElementById('descripcion').value.trim();
            const tipo = document.getElementById('tipo').value;
            const precio = document.getElementById('precio').value;
            const nivel_ciclismo = document.getElementById('nivel_ciclismo').value;
            const imagen_principal = document.getElementById('imagen_principal').value.trim();
            
            const mensajeElemento = document.getElementById('mensaje');
            let mensaje = '';
            let esValido = true;

            // Validar nombre
            if (nombre === '' || nombre.length < 3) {
                mensaje += '✖ El nombre debe tener al menos 3 caracteres.<br>';
                esValido = false;
            }

            // Validar descripción
            if (descripcion.length < 50) {
                mensaje += '✖ La descripción debe tener al menos 50 caracteres.<br>';
                esValido = false;
            }

            // Validar tipo
            if (tipo === '') {
                mensaje += '✖ Debes seleccionar un tipo de producto.<br>';
                esValido = false;
            }

            // Validar nivel de ciclismo
            if (nivel_ciclismo === '') {
                mensaje += '✖ Debes seleccionar un nivel de ciclismo.<br>';
                esValido = false;
            }

            // Validar precio
            if (precio <= 0) {
                mensaje += '✖ El precio debe ser mayor a 0.<br>';
                esValido = false;
            }

            // VALIDAR TALLAS: Al menos una talla seleccionada con stock > 0
            const tallasSeleccionadas = document.querySelectorAll('.talla-checkbox:checked');
            if (tallasSeleccionadas.length === 0) {
                mensaje += '✖ Debes seleccionar al menos una talla.<br>';
                document.getElementById('tallas-warning').classList.add('show');
                esValido = false;
            } else {
                let tieneStockValido = false;
                tallasSeleccionadas.forEach(checkbox => {
                    const talla = checkbox.value;
                    const stock = parseInt(document.getElementById('stock-' + talla).value) || 0;
                    if (stock > 0) {
                        tieneStockValido = true;
                    }
                });
                
                if (!tieneStockValido) {
                    mensaje += '✖ Al menos una talla debe tener stock mayor a 0.<br>';
                    esValido = false;
                }
            }

            // Validar imagen principal
            if (imagen_principal === '') {
                mensaje += '✖ Debes proporcionar una imagen principal.<br>';
                esValido = false;
            } else if (!isValidUrl(imagen_principal)) {
                mensaje += '✖ La URL de la imagen principal no es válida.<br>';
                esValido = false;
            }

            if (!esValido) {
                mensajeElemento.innerHTML = mensaje;
                mensajeElemento.className = 'message error';
                window.scrollTo({ top: 0, behavior: 'smooth' });
                return false;
            }

            return true;
        }

        function isValidUrl(string) {
            try {
                new URL(string);
                return true;
            } catch (_) {
                return false;
            }
        }

        // Mostrar mensajes de la URL
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const mensajeElemento = document.getElementById('mensaje');

            if (urlParams.get('error')) {
                mensajeElemento.innerHTML = '✖ ' + decodeURIComponent(urlParams.get('error'));
                mensajeElemento.className = 'message error';
            }

            if (urlParams.get('success')) {
                mensajeElemento.innerHTML = '✓ ' + decodeURIComponent(urlParams.get('success'));
                mensajeElemento.className = 'message success';
            }
        });
    </script>
</body>
</html>