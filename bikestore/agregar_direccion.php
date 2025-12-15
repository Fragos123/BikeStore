<?php
session_start();
// 1. SEGURIDAD
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header("Location: login.php");
    exit;
}

include 'conexion.php';
$usuario_id = $_SESSION['usuario_id'];
$mensaje = '';
$tipo_mensaje = '';

// Determinar a dónde volver
$return_url = isset($_GET['return']) && $_GET['return'] == 'checkout' ? 'checkout.php' : 'perfil.php';
$titulo_volver = isset($_GET['return']) && $_GET['return'] == 'checkout' ? 'Volver al Pago' : 'Volver al Perfil';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre_completo']);
    $calle = trim($_POST['calle']);
    $numero = trim($_POST['numero_exterior']);
    $colonia = trim($_POST['colonia']);
    $ciudad = trim($_POST['ciudad']);
    $estado = trim($_POST['estado']);
    $cp = trim($_POST['codigo_postal']);
    $telefono = trim($_POST['telefono']);

    // Validaciones PHP (Respaldo)
    if (empty($nombre) || empty($calle) || empty($numero) || empty($ciudad) || empty($cp) || empty($telefono)) {
        $mensaje = "Por favor completa los campos obligatorios.";
        $tipo_mensaje = "error";
    } else {
        $sql = "INSERT INTO direcciones (usuario_id, nombre_completo, calle, numero_exterior, colonia, ciudad, estado, codigo_postal, telefono) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issssssss", $usuario_id, $nombre, $calle, $numero, $colonia, $ciudad, $estado, $cp, $telefono);
        
        if ($stmt->execute()) {
            header("Location: " . $return_url);
            exit;
        } else {
            $mensaje = "Error al guardar. Intenta de nuevo.";
            $tipo_mensaje = "error";
        }
        $stmt->close();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Dirección - BikeStore</title>
    <style>
        /* ESTILOS BASE */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, sans-serif; background-color: #0f0f0f; color: #fff; min-height: 100vh; display: flex; flex-direction: column; }
        .navbar { padding: 1.5rem 3rem; display: flex; justify-content: space-between; align-items: center; background: rgba(10, 10, 10, 0.95); border-bottom: 1px solid rgba(255, 255, 255, 0.05); }
        .logo { font-size: 1.5rem; font-weight: 900; color: white; text-decoration: none; letter-spacing: -1px; text-transform: uppercase; }
        .nav-link { color: #ccc; text-decoration: none; font-size: 0.9rem; font-weight: 600; text-transform: uppercase; }
        .nav-link:hover { color: white; }
        .main-content { flex: 1; display: flex; align-items: center; justify-content: center; padding: 4rem 2rem; }
        .form-card { background: #1a1a1a; border: 1px solid #333; border-radius: 8px; padding: 3rem; width: 100%; max-width: 800px; box-shadow: 0 20px 50px rgba(0,0,0,0.5); }
        .card-header { text-align: center; margin-bottom: 3rem; }
        .card-title { font-size: 2rem; font-weight: 800; margin-bottom: 0.5rem; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
        .full-width { grid-column: 1 / -1; }
        .form-group { margin-bottom: 0.5rem; }
        .form-label { display: block; font-size: 0.75rem; font-weight: 700; color: #888; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.5rem; }
        .form-input { width: 100%; padding: 1rem; background: #0f0f0f; border: 1px solid #333; border-radius: 4px; color: white; font-size: 1rem; outline: none; transition: border-color 0.3s; }
        .form-input:focus { border-color: #fff; }
        .btn-group { display: flex; gap: 1rem; margin-top: 2rem; grid-column: 1 / -1; }
        .btn { flex: 1; padding: 1.2rem; border-radius: 4px; font-weight: 800; font-size: 0.9rem; text-transform: uppercase; cursor: pointer; text-align: center; text-decoration: none; border: 1px solid transparent; }
        .btn-primary { background: white; color: black; }
        .btn-secondary { background: transparent; border-color: #333; color: #ccc; }
        .alert { padding: 1rem; border-radius: 4px; margin-bottom: 2rem; font-size: 0.9rem; text-align: center; font-weight: 600; width: 100%; }
        .alert-error { background: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid #dc2626; }
        
        /* Estilo error JS */
        .input-error { border-color: #ef4444 !important; }
        .error-msg { color: #ef4444; font-size: 0.8rem; margin-top: 5px; display: none; }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="logo">BIKESTORE</a>
        <a href="<?php echo $return_url; ?>" class="nav-link">← <?php echo $titulo_volver; ?></a>
    </nav>

    <div class="main-content">
        <div class="form-card">
            <div class="card-header">
                <h1 class="card-title">Nueva Dirección</h1>
                <p style="color:#666;">Ingresa los datos de envío</p>
            </div>

            <?php if ($mensaje): ?>
                <div class="alert alert-<?php echo $tipo_mensaje; ?>"><?php echo $mensaje; ?></div>
            <?php endif; ?>

            <div id="js-error-banner" class="alert alert-error" style="display:none;"></div>

            <form action="" method="POST" class="form-grid" id="formDireccion" onsubmit="return validarDireccion()">
                
                <div class="form-group full-width">
                    <label class="form-label">Nombre de quien recibe</label>
                    <input type="text" id="nombre" name="nombre_completo" class="form-input" placeholder="Ej: Juan Pérez">
                </div>

                <div class="form-group full-width">
                    <label class="form-label">Calle</label>
                    <input type="text" id="calle" name="calle" class="form-input" placeholder="Ej: Av. Reforma">
                </div>

                <div class="form-group">
                    <label class="form-label">Número Exterior</label>
                    <input type="text" id="num_ext" name="numero_exterior" class="form-input" placeholder="Ej: 123">
                </div>

                <div class="form-group">
                    <label class="form-label">Colonia</label>
                    <input type="text" id="colonia" name="colonia" class="form-input" placeholder="Ej: Centro">
                </div>

                <div class="form-group">
                    <label class="form-label">Ciudad</label>
                    <input type="text" id="ciudad" name="ciudad" class="form-input" placeholder="Ej: CDMX">
                </div>

                <div class="form-group">
                    <label class="form-label">Estado</label>
                    <input type="text" id="estado" name="estado" class="form-input" placeholder="Ej: CDMX">
                </div>

                <div class="form-group">
                    <label class="form-label">Código Postal</label>
                    <input type="text" id="cp" name="codigo_postal" class="form-input" placeholder="Ej: 06000" maxlength="5">
                </div>

                <div class="form-group">
                    <label class="form-label">Teléfono (10 dígitos)</label>
                    <input type="tel" id="telefono" name="telefono" class="form-input" placeholder="Ej: 5512345678" maxlength="10">
                </div>

                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">Guardar Dirección</button>
                    <a href="<?php echo $return_url; ?>" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function validarDireccion() {
            let esValido = true;
            let errores = [];
            
            // Obtener campos
            const campos = {
                nombre: document.getElementById('nombre'),
                calle: document.getElementById('calle'),
                num_ext: document.getElementById('num_ext'),
                colonia: document.getElementById('colonia'),
                ciudad: document.getElementById('ciudad'),
                estado: document.getElementById('estado'),
                cp: document.getElementById('cp'),
                telefono: document.getElementById('telefono')
            };

            // Limpiar estilos previos
            Object.values(campos).forEach(input => input.classList.remove('input-error'));
            document.getElementById('js-error-banner').style.display = 'none';

            // 1. Validar vacíos
            for (const [key, input] of Object.entries(campos)) {
                if (!input.value.trim()) {
                    input.classList.add('input-error');
                    esValido = false;
                }
            }
            if (!esValido) errores.push("Todos los campos son obligatorios.");

            // 2. Validar CP (5 dígitos)
            const regexCP = /^\d{5}$/;
            if (campos.cp.value && !regexCP.test(campos.cp.value)) {
                campos.cp.classList.add('input-error');
                errores.push("El Código Postal debe tener 5 números.");
                esValido = false;
            }

            // 3. Validar Teléfono (10 dígitos)
            const regexTel = /^\d{10}$/;
            if (campos.telefono.value && !regexTel.test(campos.telefono.value)) {
                campos.telefono.classList.add('input-error');
                errores.push("El teléfono debe tener 10 dígitos numéricos.");
                esValido = false;
            }

            // Mostrar errores
            if (!esValido) {
                const banner = document.getElementById('js-error-banner');
                banner.innerHTML = errores.join("<br>");
                banner.style.display = 'block';
                window.scrollTo(0, 0);
            }

            return esValido;
        }

        // Permitir solo números en CP y Teléfono al escribir
        ['cp', 'telefono'].forEach(id => {
            document.getElementById(id).addEventListener('input', function(e) {
                this.value = this.value.replace(/\D/g, '');
            });
        });
    </script>
</body>
</html>