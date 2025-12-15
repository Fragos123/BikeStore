<?php
session_start();
// 1. SEGURIDAD: Permitir 'operador' O 'admin'
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true || 
    !in_array($_SESSION['usuario_rol'], ['operador', 'admin'])) {
    header("Location: login.php");
    exit;
}

// Determinar a dónde regresar
$rol_usuario = $_SESSION['usuario_rol'];
$link_volver = ($rol_usuario === 'admin') ? 'principal_admin.php' : 'principal_operador.php';
$texto_volver = ($rol_usuario === 'admin') ? 'Volver al Admin' : 'Volver al Panel';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Producto - BikeStore</title>
    <style>
        /* BASE DARK PREMIUM */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background-color: #0f0f0f; color: #fff; min-height: 100vh; padding: 2rem; }
        .container { max-width: 900px; margin: 0 auto; }

        /* BOTÓN VOLVER */
        .back-button { background: transparent; color: #ccc; border: 1px solid #333; padding: 0.75rem 1.5rem; border-radius: 50px; text-decoration: none; font-weight: 600; transition: all 0.3s ease; display: inline-flex; align-items: center; gap: 0.5rem; margin-bottom: 2rem; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; }
        .back-button:hover { background: #fff; color: #000; border-color: #fff; transform: translateX(-5px); }

        /* FORMULARIO */
        .form-card { background: #1a1a1a; border: 1px solid #333; border-radius: 12px; padding: 3rem; box-shadow: 0 25px 60px rgba(0, 0, 0, 0.5); animation: fadeInUp 0.6s ease-out; }
        .form-header { text-align: center; margin-bottom: 2.5rem; }
        .form-icon { width: 80px; height: 80px; background: #222; border-radius: 20px; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; margin: 0 auto 1.5rem; border: 1px solid #333; }
        .form-title { font-size: 2rem; font-weight: 900; color: #fff; margin-bottom: 0.5rem; letter-spacing: -1px; }
        .form-subtitle { color: #888; font-size: 1rem; }

        .product-form { display: flex; flex-direction: column; gap: 1.5rem; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
        .form-group { display: flex; flex-direction: column; gap: 0.5rem; }
        .form-label { font-weight: 700; color: #aaa; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; }
        .required { color: #fbbf24; }

        /* INPUTS */
        .form-input, .form-select, .form-textarea { padding: 1rem 1.25rem; border: 1px solid #333; border-radius: 6px; font-size: 1rem; transition: all 0.3s ease; font-family: inherit; background: #0f0f0f; color: #fff; }
        .form-textarea { min-height: 120px; resize: vertical; }
        .form-input:focus, .form-select:focus, .form-textarea:focus { outline: none; border-color: #fff; }
        .form-input::placeholder, .form-textarea::placeholder { color: #444; }
        .form-help { font-size: 0.8rem; color: #666; font-style: italic; }

        /* TALLAS */
        .tallas-section { background: #111; border: 1px solid #333; border-radius: 8px; padding: 1.5rem; margin: 1.5rem 0; }
        .tallas-section-title { font-size: 1.1rem; font-weight: 700; color: #fff; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; }
        .tallas-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 1rem; margin-top: 1rem; }
        
        .talla-card { background: #1a1a1a; border: 1px solid #333; border-radius: 6px; padding: 1rem; transition: all 0.3s ease; }
        .talla-card.selected { border-color: #fff; background: #fff; }
        .talla-card.selected .talla-name { color: #000; }
        .talla-card.selected .talla-stock-input { background: #eee; color: #000; border-color: #ccc; }

        .talla-checkbox-wrapper { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem; }
        .talla-checkbox { width: 18px; height: 18px; cursor: pointer; accent-color: #000; }
        .talla-name { font-size: 1rem; font-weight: 700; color: #fff; }

        .talla-stock-input { width: 100%; padding: 0.5rem; border: 1px solid #333; border-radius: 4px; font-size: 0.9rem; font-weight: 600; text-align: center; background: #0f0f0f; color: #fff; }
        .talla-stock-input:disabled { background: #222; color: #555; cursor: not-allowed; border-color: #222; }
        .talla-stock-input:focus { outline: none; border-color: #fff; }

        .tallas-warning { background: rgba(251, 191, 36, 0.1); border: 1px solid #fbbf24; border-radius: 4px; padding: 0.75rem; margin-top: 1rem; color: #fbbf24; font-size: 0.9rem; display: none; text-align: center; }
        .tallas-warning.show { display: block; }

        /* INFO BOX */
        .info-box { background: rgba(255, 255, 255, 0.05); border-left: 4px solid #fff; padding: 1rem; border-radius: 4px; margin: 1rem 0 2rem 0; }
        .info-box-title { font-weight: 700; color: #fff; margin-bottom: 0.5rem; font-size: 0.9rem; }
        .info-box-text { color: #ccc; font-size: 0.85rem; line-height: 1.5; }

        /* BOTONES */
        .button-group { display: flex; gap: 1rem; margin-top: 2rem; }
        .btn { flex: 1; padding: 1rem 2rem; border-radius: 6px; font-weight: 700; font-size: 0.9rem; cursor: pointer; transition: all 0.3s ease; border: none; display: flex; align-items: center; justify-content: center; gap: 0.5rem; text-decoration: none; text-transform: uppercase; letter-spacing: 1px; }
        .btn-primary { background: #fff; color: #000; }
        .btn-primary:hover { background: #ccc; transform: translateY(-2px); }
        .btn-secondary { background: transparent; color: #fff; border: 1px solid #333; }
        .btn-secondary:hover { border-color: #fff; background: rgba(255,255,255,0.05); }

        /* MENSAJES */
        .message { padding: 1rem 1.25rem; border-radius: 4px; margin-bottom: 1.5rem; font-weight: 600; display: flex; align-items: center; gap: 0.75rem; font-size: 0.9rem; }
        .message.success { background: rgba(52, 211, 153, 0.1); color: #34d399; border: 1px solid #34d399; }
        .message.error { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid #ef4444; }

        @keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        @media (max-width: 768px) { body { padding: 1rem; } .form-card { padding: 2rem 1.5rem; } .form-row { grid-template-columns: 1fr; } .button-group { flex-direction: column; } .tallas-grid { grid-template-columns: repeat(2, 1fr); } }
    </style>
</head>
<body>
    <div class="container">
        <a href="<?php echo $link_volver; ?>" class="back-button">← <?php echo $texto_volver; ?></a>

        <div class="form-card">
            <div class="form-header">
                <div class="form-icon">🚴</div>
                <h1 class="form-title">Crear Nuevo Producto</h1>
                <p class="form-subtitle">Agrega una nueva bicicleta al catálogo</p>
            </div>

            <div class="info-box">
                <div class="info-box-title">💡 Restricciones Estrictas</div>
                <div class="info-box-text">
                    • <strong>Precio:</strong> Máximo $1,000,000.<br>
                    • <strong>Stock:</strong> Máximo 50 por talla. El sistema bloqueará automáticamente si intentas escribir más.
                </div>
            </div>

            <div id="mensaje"></div>

            <form class="product-form" action="procesar_operador_crear_producto.php" method="POST" onsubmit="return validarFormulario()">
                
                <div class="form-group">
                    <label class="form-label" for="nombre">Nombre del Producto <span class="required">*</span></label>
                    <input type="text" id="nombre" name="nombre" class="form-input" placeholder="Ej: Trek Marlin 7" required maxlength="100">
                </div>

                <div class="form-group">
                    <label class="form-label" for="descripcion">Descripción <span class="required">*</span></label>
                    <textarea id="descripcion" name="descripcion" class="form-textarea" placeholder="Describe las características principales..." required></textarea>
                    <span class="form-help">Mínimo 50 caracteres</span>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="tipo">Tipo <span class="required">*</span></label>
                        <select id="tipo" name="tipo" class="form-select" required>
                            <option value="">Selecciona un tipo</option>
                            <option value="montaña">Montaña</option>
                            <option value="ruta">Ruta</option>
                            <option value="urbana">Urbana</option>
                            <option value="eléctrica">Eléctrica</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="precio">Precio <span class="required">*</span></label>
                        <input type="text" id="precio" name="precio" class="form-input" 
                               placeholder="15999.00" step="0.01" min="1" max="1000000" required
                               oninput="limpiarInput(this, 1000000, true)">
                    </div>
                </div>

                <div class="tallas-section">
                    <div class="tallas-section-title">
                        📏 Tallas y Stock <span class="required">*</span>
                    </div>
                    <p style="color: #888; font-size: 0.9rem; margin-bottom: 1rem;">
                        Máximo 50 por talla.
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
                            <input type="text" 
                                   class="talla-stock-input" 
                                   id="stock-<?php echo $talla; ?>" 
                                   name="stock_<?php echo $talla; ?>" 
                                   placeholder="0"
                                   min="0"
                                   max="50"
                                   value="0"
                                   disabled
                                   oninput="limpiarInput(this, 50, false)">
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="tallas-warning" id="tallas-warning">
                        ⚠️ El stock total debe ser al menos 1.
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="nivel_ciclismo">Nivel de Ciclismo <span class="required">*</span></label>
                        <select id="nivel_ciclismo" name="nivel_ciclismo" class="form-select" required>
                            <option value="">Selecciona un nivel</option>
                            <option value="principiante">Principiante</option>
                            <option value="intermedio">Intermedio</option>
                            <option value="avanzado">Avanzado</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="dias_envio">Días de envío <span class="required">*</span></label>
                        <input type="text" id="dias_envio" name="dias_envio" class="form-input" placeholder="7" required
                               oninput="limpiarInput(this, 60, false)">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="peso">Peso (kg)</label>
                        <input type="text" id="peso" name="peso" class="form-input" placeholder="13.5"
                               oninput="limpiarInput(this, 100, true)">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="velocidades">Velocidades</label>
                        <input type="text" id="velocidades" name="velocidades" class="form-input" placeholder="21"
                               oninput="limpiarInput(this, 30, false)">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="imagen_principal">Imagen Principal <span class="required">*</span></label>
                    <input type="url" id="imagen_principal" name="imagen_principal" class="form-input" placeholder="https://ejemplo.com/imagen.jpg" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="imagen_2">Imagen 2</label>
                        <input type="url" id="imagen_2" name="imagen_2" class="form-input" placeholder="URL">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="imagen_3">Imagen 3</label>
                        <input type="url" id="imagen_3" name="imagen_3" class="form-input" placeholder="URL">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="imagen_4">Imagen 4</label>
                        <input type="url" id="imagen_4" name="imagen_4" class="form-input" placeholder="URL">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="imagen_5">Imagen 5</label>
                        <input type="url" id="imagen_5" name="imagen_5" class="form-input" placeholder="URL">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="imagen_6">Imagen 6</label>
                    <input type="url" id="imagen_6" name="imagen_6" class="form-input" placeholder="URL">
                </div>

                <div class="button-group">
                    <button type="submit" class="btn btn-primary">✓ Crear Producto</button>
                    <a href="<?php echo $link_volver; ?>" class="btn btn-secondary">✕ Cancelar</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Función estricta para validar inputs mientras escribes
        function limpiarInput(input, max, esDecimal) {
            let valor = input.value;
            
            // 1. Eliminar caracteres no numéricos
            if (esDecimal) {
                valor = valor.replace(/[^0-9.]/g, ''); 
                const partes = valor.split('.');
                if (partes.length > 2) valor = partes[0] + '.' + partes.slice(1).join('');
            } else {
                valor = valor.replace(/[^0-9]/g, '');
            }

            // 2. Eliminar ceros a la izquierda (ej: 09 -> 9)
            if (valor.length > 1 && valor.startsWith('0') && !valor.startsWith('0.')) {
                valor = valor.replace(/^0+/, '');
            }

            // 3. Validar Máximo
            if (valor !== '') {
                const num = parseFloat(valor);
                if (num > max) valor = max.toString();
            }

            if (input.value !== valor) input.value = valor;
        }

        function toggleTallaStock(talla) {
            const checkbox = document.getElementById('talla-' + talla);
            const stockInput = document.getElementById('stock-' + talla);
            const card = document.getElementById('card-' + talla);
            
            if (checkbox.checked) {
                stockInput.disabled = false;
                stockInput.required = true;
                stockInput.focus();
                card.classList.add('selected');
                if(stockInput.value === '0') stockInput.value = '';
            } else {
                stockInput.disabled = true;
                stockInput.required = false;
                stockInput.value = '0';
                card.classList.remove('selected');
            }
            const tallasSeleccionadas = document.querySelectorAll('.talla-checkbox:checked').length;
            document.getElementById('tallas-warning').classList.toggle('show', tallasSeleccionadas === 0);
        }

        function validarFormulario() {
            const precio = parseFloat(document.getElementById('precio').value);
            const mensajeElemento = document.getElementById('mensaje');
            let mensaje = '';
            let esValido = true;

            if (precio > 1000000) {
                mensaje += '✖ El precio no puede exceder $1,000,000.<br>';
                esValido = false;
            }

            const tallasSeleccionadas = document.querySelectorAll('.talla-checkbox:checked');
            if (tallasSeleccionadas.length === 0) {
                mensaje += '✖ Debes seleccionar al menos una talla.<br>';
                document.getElementById('tallas-warning').classList.add('show');
                esValido = false;
            } else {
                let stockTotal = 0;
                tallasSeleccionadas.forEach(checkbox => {
                    const talla = checkbox.value;
                    const stock = parseInt(document.getElementById('stock-' + talla).value) || 0;
                    stockTotal += stock;
                });
                
                if (stockTotal <= 0) {
                    mensaje += '✖ El stock total (suma de tallas) debe ser mayor a 0.<br>';
                    esValido = false;
                }
            }

            if (!esValido) {
                mensajeElemento.innerHTML = mensaje;
                mensajeElemento.className = 'message error';
                window.scrollTo({ top: 0, behavior: 'smooth' });
                return false;
            }
            return true;
        }

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