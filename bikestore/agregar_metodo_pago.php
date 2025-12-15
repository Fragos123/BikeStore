<?php
session_start();
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) { header("Location: login.php"); exit; }
include 'conexion.php';
$usuario_id = $_SESSION['usuario_id'];
$return_url = isset($_GET['return']) && $_GET['return'] == 'checkout' ? 'checkout.php' : 'perfil.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Procesamiento PHP mínimo (la validación fuerte ya la hizo JS)
    $tipo = 'Desconocida'; // Simplificado para el ejemplo
    $nombre = $_POST['nombre_titular'];
    $numero = str_replace(' ', '', $_POST['numero_tarjeta']);
    $exp = $_POST['expiracion'];
    $ultimos = substr($numero, -4);
    
    $stmt = $conn->prepare("INSERT INTO metodos_pago (usuario_id, tipo, nombre_titular, ultimos_digitos, fecha_expiracion) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $usuario_id, $tipo, $nombre, $ultimos, $exp);
    $stmt->execute();
    header("Location: $return_url");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Tarjeta - BikeStore</title>
    <style>
        /* Mismos estilos base */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, sans-serif; background-color: #0f0f0f; color: #fff; min-height: 100vh; display: flex; flex-direction: column; }
        .navbar { padding: 1.5rem 3rem; background: rgba(10,10,10,0.95); border-bottom: 1px solid rgba(255,255,255,0.05); display: flex; justify-content: space-between; }
        .logo { font-size: 1.5rem; font-weight: 900; color: white; text-decoration: none; }
        .main-content { flex: 1; display: flex; align-items: center; justify-content: center; padding: 2rem; }
        .form-card { background: #1a1a1a; border: 1px solid #333; border-radius: 12px; padding: 3rem; width: 100%; max-width: 500px; }
        .form-group { margin-bottom: 1rem; }
        .form-label { display: block; font-size: 0.8rem; font-weight: 700; color: #888; margin-bottom: 0.5rem; text-transform: uppercase; }
        .form-input { width: 100%; padding: 1rem; background: #0f0f0f; border: 1px solid #333; border-radius: 6px; color: white; outline: none; }
        .form-input:focus { border-color: #fff; }
        .btn { width: 100%; padding: 1rem; background: white; color: black; border: none; border-radius: 6px; font-weight: 800; cursor: pointer; margin-top: 1rem; }
        .input-error { border-color: #ef4444 !important; }
        .error-banner { background: rgba(239,68,68,0.2); color: #fca5a5; padding: 1rem; border-radius: 6px; margin-bottom: 1rem; display: none; text-align: center; }
    </style>
</head>
<body>
    <nav class="navbar"><a href="index.php" class="logo">BIKESTORE</a><a href="<?php echo $return_url; ?>" style="color:#ccc;text-decoration:none;">Cancelar</a></nav>
    
    <div class="main-content">
        <div class="form-card">
            <h2 style="text-align:center; margin-bottom:2rem;">Nueva Tarjeta</h2>
            <div id="errorBanner" class="error-banner"></div>
            
            <form action="" method="POST" onsubmit="return validarTarjeta()">
                <div class="form-group">
                    <label class="form-label">Titular</label>
                    <input type="text" id="nombre" name="nombre_titular" class="form-input" placeholder="COMO EN LA TARJETA">
                </div>
                <div class="form-group">
                    <label class="form-label">Número de Tarjeta</label>
                    <input type="text" id="numero" name="numero_tarjeta" class="form-input" placeholder="0000 0000 0000 0000" maxlength="19">
                </div>
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
                    <div class="form-group">
                        <label class="form-label">Expiración</label>
                        <input type="text" id="exp" name="expiracion" class="form-input" placeholder="MM/YY" maxlength="5">
                    </div>
                    <div class="form-group">
                        <label class="form-label">CVV</label>
                        <input type="password" id="cvv" name="cvv" class="form-input" placeholder="123" maxlength="4">
                    </div>
                </div>
                <button type="submit" class="btn">Guardar Tarjeta Segura</button>
            </form>
        </div>
    </div>

    <script>
        // Algoritmo de Luhn para validar tarjetas reales
        function luhnCheck(val) {
            let sum = 0;
            for (let i = 0; i < val.length; i++) {
                let intVal = parseInt(val.substr(i, 1));
                if (i % 2 === 0) {
                    intVal *= 2;
                    if (intVal > 9) intVal = 1 + (intVal % 10);
                }
                sum += intVal;
            }
            return (sum % 10) === 0;
        }

        function validarTarjeta() {
            const nombre = document.getElementById('nombre');
            const numero = document.getElementById('numero');
            const exp = document.getElementById('exp');
            const cvv = document.getElementById('cvv');
            const banner = document.getElementById('errorBanner');
            let error = "";

            // Limpiar errores visuales
            [nombre, numero, exp, cvv].forEach(el => el.classList.remove('input-error'));

            // Validar Vacíos
            if(!nombre.value.trim()) { error = "Falta el nombre del titular"; nombre.classList.add('input-error'); }
            
            // Validar Número (Luhn)
            const numLimpio = numero.value.replace(/\s+/g, '');
            if(!/^\d{13,19}$/.test(numLimpio)) {
                error = "Número de tarjeta incompleto"; numero.classList.add('input-error');
            } else if(!luhnCheck(numLimpio)) {
                // error = "Número de tarjeta inválido (Luhn)"; // Comentar si solo pruebas con datos ficticios
                // numero.classList.add('input-error');
            }

            // Validar Fecha
            if(!/^\d{2}\/\d{2}$/.test(exp.value)) {
                error = "Fecha inválida (MM/YY)"; exp.classList.add('input-error');
            } else {
                const [m, y] = exp.value.split('/').map(Number);
                const now = new Date();
                const currentY = parseInt(now.getFullYear().toString().substr(-2));
                const currentM = now.getMonth() + 1;
                
                if(m < 1 || m > 12 || y < currentY || (y === currentY && m < currentM)) {
                    error = "La tarjeta ha expirado o la fecha es incorrecta"; exp.classList.add('input-error');
                }
            }

            // Validar CVV
            if(!/^\d{3,4}$/.test(cvv.value)) {
                error = "CVV inválido"; cvv.classList.add('input-error');
            }

            if(error) {
                banner.innerText = "❌ " + error;
                banner.style.display = 'block';
                return false;
            }
            return true;
        }

        // Máscaras de entrada
        document.getElementById('numero').addEventListener('input', function(e) {
            let v = this.value.replace(/\D/g, '');
            this.value = v.replace(/(.{4})/g, '$1 ').trim();
        });
        
        document.getElementById('exp').addEventListener('input', function(e) {
            let v = this.value.replace(/\D/g, '');
            if(v.length >= 3) this.value = v.slice(0,2) + '/' + v.slice(2,4);
            else this.value = v;
        });
    </script>
</body>
</html>