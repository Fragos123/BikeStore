<?php
session_start();
// 1. SEGURIDAD
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true || 
    !in_array($_SESSION['usuario_rol'], ['operador', 'admin'])) {
    header("Location: login.php");
    exit;
}

include 'conexion.php';

$rol_usuario = $_SESSION['usuario_rol'];
$link_volver = ($rol_usuario === 'admin') ? 'lista_productos.php' : 'principal_operador.php';
$texto_volver = ($rol_usuario === 'admin') ? 'Volver al Inventario' : 'Volver al Panel';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $conn->prepare("SELECT * FROM productos WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$producto = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$producto) { header("Location: $link_volver"); exit; }

$tallas_actuales = [];
$stmt_tallas = $conn->prepare("SELECT talla, stock FROM producto_tallas WHERE producto_id = ? AND activo = 1");
$stmt_tallas->bind_param("i", $id);
$stmt_tallas->execute();
$res_tallas = $stmt_tallas->get_result();
while ($row = $res_tallas->fetch_assoc()) {
    $tallas_actuales[$row['talla']] = $row['stock'];
}
$stmt_tallas->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Producto - BikeStore</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, sans-serif; background-color: #0f0f0f; color: #fff; min-height: 100vh; padding: 2rem; }
        .container { max-width: 900px; margin: 0 auto; }
        .back-button { background: transparent; color: #ccc; border: 1px solid #333; padding: 0.75rem 1.5rem; border-radius: 50px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 0.5rem; margin-bottom: 2rem; font-size: 0.9rem; text-transform: uppercase; transition: 0.3s; }
        .back-button:hover { background: #fff; color: #000; border-color: #fff; }
        .form-card { background: #1a1a1a; border: 1px solid #333; border-radius: 12px; padding: 3rem; box-shadow: 0 25px 60px rgba(0,0,0,0.5); }
        .product-form { display: flex; flex-direction: column; gap: 1.5rem; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
        .form-group { display: flex; flex-direction: column; gap: 0.5rem; }
        .form-label { font-weight: 700; color: #aaa; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; }
        .form-input, .form-select, .form-textarea { padding: 1rem; border: 1px solid #333; border-radius: 6px; font-size: 1rem; background: #0f0f0f; color: #fff; outline: none; transition: 0.3s; }
        .form-input:focus { border-color: #fff; }
        
        .tallas-section { background: #111; border: 1px solid #333; border-radius: 8px; padding: 1.5rem; }
        .tallas-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 1rem; margin-top: 1rem; }
        .talla-card { background: #1a1a1a; border: 1px solid #333; border-radius: 6px; padding: 1rem; }
        .talla-card.selected { border-color: #fff; background: #fff; }
        .talla-card.selected label { color: #000; }
        .talla-card.selected input { background: #eee; color: #000; border-color: #ccc; }
        .talla-header { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
        .talla-stock-input { width: 100%; padding: 0.5rem; background: #0f0f0f; border: 1px solid #333; color: #fff; text-align: center; border-radius: 4px; font-weight: 700; }
        
        .btn-primary { background: #fff; color: #000; padding: 1rem; border-radius: 6px; font-weight: 700; border: none; cursor: pointer; text-transform: uppercase; width: 100%; margin-top: 2rem; transition: 0.3s; }
        .btn-primary:hover { background: #ccc; }
        .message { padding: 1rem; margin-bottom: 1rem; border-radius: 6px; text-align: center; font-weight: 600; }
        .message.error { background: rgba(239,68,68,0.2); color: #ef4444; border: 1px solid #ef4444; }
    </style>
</head>
<body>
    <div class="container">
        <a href="<?php echo $link_volver; ?>" class="back-button">← <?php echo $texto_volver; ?></a>
        <div class="form-card">
            <h1 style="text-align:center; margin-bottom:2rem;">Editar Producto #<?php echo $id; ?></h1>
            <div id="mensaje"></div>

            <form class="product-form" action="procesar_operador_editar_producto.php" method="POST" onsubmit="return validarFormulario()">
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                
                <div class="form-group">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="nombre" class="form-input" value="<?php echo htmlspecialchars($producto['nombre']); ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Descripción</label>
                    <textarea name="descripcion" class="form-textarea" rows="4" required><?php echo htmlspecialchars($producto['descripcion']); ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Tipo</label>
                        <select name="tipo" class="form-select" required>
                            <option value="montaña" <?php echo $producto['tipo']=='montaña'?'selected':''; ?>>Montaña</option>
                            <option value="ruta" <?php echo $producto['tipo']=='ruta'?'selected':''; ?>>Ruta</option>
                            <option value="urbana" <?php echo $producto['tipo']=='urbana'?'selected':''; ?>>Urbana</option>
                            <option value="eléctrica" <?php echo $producto['tipo']=='eléctrica'?'selected':''; ?>>Eléctrica</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Precio</label>
                        <input type="text" name="precio" class="form-input" 
                               value="<?php echo $producto['precio']; ?>" required
                               oninput="limpiarInput(this, 1000000, true)">
                    </div>
                </div>

                <div class="tallas-section">
                    <div class="tallas-section-title" style="font-weight:700; color:#fff; margin-bottom:1rem;">📏 Tallas</div>
                    <div class="tallas-grid">
                        <?php foreach (['XS', 'S', 'M', 'L', 'XL', 'XXL'] as $talla): 
                            $activa = array_key_exists($talla, $tallas_actuales);
                            $stock = $activa ? $tallas_actuales[$talla] : 0;
                        ?>
                        <div class="talla-card <?php echo $activa?'selected':''; ?>" id="card-<?php echo $talla; ?>">
                            <div class="talla-header">
                                <input type="checkbox" id="talla-<?php echo $talla; ?>" name="tallas[]" value="<?php echo $talla; ?>" 
                                       <?php echo $activa?'checked':''; ?> onchange="toggleTalla('<?php echo $talla; ?>')">
                                <label for="talla-<?php echo $talla; ?>" style="font-weight:700; color:#fff;"><?php echo $talla; ?></label>
                            </div>
                            <input type="text" class="talla-stock-input" id="stock-<?php echo $talla; ?>" name="stock_<?php echo $talla; ?>" 
                                   value="<?php echo $stock; ?>" <?php echo $activa?'':'disabled'; ?>
                                   oninput="limpiarInput(this, 50, false)">
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Nivel</label>
                        <select name="nivel_ciclismo" class="form-select">
                            <option value="principiante" <?php echo $producto['nivel_ciclismo']=='principiante'?'selected':''; ?>>Principiante</option>
                            <option value="intermedio" <?php echo $producto['nivel_ciclismo']=='intermedio'?'selected':''; ?>>Intermedio</option>
                            <option value="avanzado" <?php echo $producto['nivel_ciclismo']=='avanzado'?'selected':''; ?>>Avanzado</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Días Envío</label>
                        <input type="text" name="dias_envio" class="form-input" value="<?php echo $producto['dias_envio']; ?>"
                               oninput="limpiarInput(this, 60, false)">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group"><label class="form-label">Peso</label><input type="text" name="peso" class="form-input" value="<?php echo $producto['peso']; ?>" oninput="limpiarInput(this, 100, true)"></div>
                    <div class="form-group"><label class="form-label">Velocidades</label><input type="text" name="velocidades" class="form-input" value="<?php echo $producto['velocidades']; ?>" oninput="limpiarInput(this, 30, false)"></div>
                </div>

                <div class="form-group">
                    <label class="form-label">Imagen Principal</label>
                    <input type="url" name="imagen_principal" class="form-input" value="<?php echo htmlspecialchars($producto['imagen_principal']); ?>" required>
                </div>
                
                <div class="form-row">
                    <input type="url" name="imagen_2" class="form-input" value="<?php echo htmlspecialchars($producto['imagen_2']); ?>">
                    <input type="url" name="imagen_3" class="form-input" value="<?php echo htmlspecialchars($producto['imagen_3']); ?>">
                </div>
                <div class="form-row">
                    <input type="url" name="imagen_4" class="form-input" value="<?php echo htmlspecialchars($producto['imagen_4']); ?>">
                    <input type="url" name="imagen_5" class="form-input" value="<?php echo htmlspecialchars($producto['imagen_5']); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Imagen 6</label>
                    <input type="url" name="imagen_6" class="form-input" value="<?php echo htmlspecialchars($producto['imagen_6']); ?>">
                </div>

                <button type="submit" class="btn-primary">Guardar Cambios</button>
            </form>
        </div>
    </div>

    <script>
        function limpiarInput(input, max, esDecimal) {
            let valor = input.value;
            if (esDecimal) {
                valor = valor.replace(/[^0-9.]/g, ''); 
                const partes = valor.split('.');
                if (partes.length > 2) valor = partes[0] + '.' + partes.slice(1).join('');
            } else {
                valor = valor.replace(/[^0-9]/g, '');
            }
            
            // Eliminar ceros izquierda
            if (valor.length > 1 && valor.startsWith('0') && !valor.startsWith('0.')) {
                valor = valor.replace(/^0+/, '');
            }

            // Validar Máximo
            if (valor !== '') {
                const num = parseFloat(valor);
                if (num > max) valor = max.toString();
            }

            if (input.value !== valor) input.value = valor;
        }

        function toggleTalla(talla) {
            const chk = document.getElementById('talla-' + talla);
            const inp = document.getElementById('stock-' + talla);
            const card = document.getElementById('card-' + talla);
            if(chk.checked) {
                inp.disabled = false;
                card.classList.add('selected');
            } else {
                inp.disabled = true;
                inp.value = '0';
                card.classList.remove('selected');
            }
        }
        
        function validarFormulario() {
            // Validaciones básicas de JS
            const precio = document.getElementById('precio').value;
            if(parseFloat(precio) <= 0) {
                alert('El precio debe ser mayor a 0');
                return false;
            }
            const tallas = document.querySelectorAll('input[name="tallas[]"]:checked');
            if(tallas.length === 0) {
                alert('Selecciona al menos una talla');
                return false;
            }
            return true;
        }

        const params = new URLSearchParams(window.location.search);
        if(params.get('error')) {
            const d = document.getElementById('mensaje');
            d.innerHTML = '❌ ' + decodeURIComponent(params.get('error'));
            d.className = 'message error';
        }
    </script>
</body>
</html>