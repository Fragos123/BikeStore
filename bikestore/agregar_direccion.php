<?php
session_start();

if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Dirección - BikeStore</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #2E3848 0%, #1a202c 100%);
            min-height: 100vh;
            padding: 2rem;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        .back-button {
            background: rgba(241, 245, 249, 0.1);
            backdrop-filter: blur(10px);
            color: #F1F5F9;
            border: 2px solid rgba(241, 245, 249, 0.2);
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
            background: rgba(241, 245, 249, 0.2);
            transform: translateX(-5px);
        }

        .form-card {
            background: #F1F5F9;
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .form-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .form-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #2E3848, #A6A09B);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin: 0 auto 1rem;
        }

        .form-title {
            font-size: 2rem;
            font-weight: 900;
            color: #2E3848;
            margin-bottom: 0.5rem;
        }

        .form-subtitle {
            color: #6b7280;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: #2E3848;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .required {
            color: #e53e3e;
        }

        .form-input,
        .form-select,
        .form-textarea {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 1rem;
            font-family: inherit;
            transition: all 0.3s ease;
            background: white;
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: #2E3848;
            box-shadow: 0 0 0 3px rgba(46, 56, 72, 0.1);
        }

        .form-textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-help {
            font-size: 0.85rem;
            color: #6b7280;
            margin-top: 0.25rem;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-top: 1rem;
        }

        .checkbox-input {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }

        .checkbox-label {
            font-weight: 600;
            color: #2E3848;
            cursor: pointer;
        }

        .button-group {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
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
            text-align: center;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #2E3848, #A6A09B);
            color: white;
            box-shadow: 0 10px 30px rgba(46, 56, 72, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 40px rgba(46, 56, 72, 0.4);
        }

        .btn-secondary {
            background: white;
            color: #2E3848;
            border: 2px solid #2E3848;
        }

        .btn-secondary:hover {
            background: #f8f9fa;
        }

        .message {
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        .message.error {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }

            .button-group {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="perfil.php" class="back-button">← Volver al perfil</a>

        <div class="form-card">
            <div class="form-header">
                <div class="form-icon">📍</div>
                <h1 class="form-title">Agregar Dirección</h1>
                <p class="form-subtitle">Completa la información de tu dirección de envío</p>
            </div>

            <?php if (isset($_GET['error'])): ?>
                <div class="message error">
                    <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>

            <form action="procesar_agregar_direccion.php" method="POST" onsubmit="return validarFormulario()">
                
                <div class="form-group">
                    <label class="form-label" for="nombre_completo">
                        Nombre Completo <span class="required">*</span>
                    </label>
                    <input type="text" id="nombre_completo" name="nombre_completo" class="form-input" 
                           placeholder="Ej: Juan Pérez García" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="telefono">
                        Teléfono <span class="required">*</span>
                    </label>
                    <input type="tel" id="telefono" name="telefono" class="form-input" 
                           placeholder="Ej: 5512345678" required>
                    <div class="form-help">10 dígitos sin espacios ni guiones</div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="calle">
                            Calle <span class="required">*</span>
                        </label>
                        <input type="text" id="calle" name="calle" class="form-input" 
                               placeholder="Ej: Av. Insurgentes Sur" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="numero_exterior">
                            Número Exterior <span class="required">*</span>
                        </label>
                        <input type="text" id="numero_exterior" name="numero_exterior" class="form-input" 
                               placeholder="Ej: 1234" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="numero_interior">
                            Número Interior
                        </label>
                        <input type="text" id="numero_interior" name="numero_interior" class="form-input" 
                               placeholder="Ej: Depto 5">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="colonia">
                            Colonia <span class="required">*</span>
                        </label>
                        <input type="text" id="colonia" name="colonia" class="form-input" 
                               placeholder="Ej: Del Valle" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="ciudad">
                            Ciudad <span class="required">*</span>
                        </label>
                        <input type="text" id="ciudad" name="ciudad" class="form-input" 
                               placeholder="Ej: Ciudad de México" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="estado">
                            Estado <span class="required">*</span>
                        </label>
                        <select id="estado" name="estado" class="form-select" required>
                            <option value="">Selecciona un estado</option>
                            <option value="Aguascalientes">Aguascalientes</option>
                            <option value="Baja California">Baja California</option>
                            <option value="Baja California Sur">Baja California Sur</option>
                            <option value="Campeche">Campeche</option>
                            <option value="Chiapas">Chiapas</option>
                            <option value="Chihuahua">Chihuahua</option>
                            <option value="CDMX">Ciudad de México</option>
                            <option value="Coahuila">Coahuila</option>
                            <option value="Colima">Colima</option>
                            <option value="Durango">Durango</option>
                            <option value="Guanajuato">Guanajuato</option>
                            <option value="Guerrero">Guerrero</option>
                            <option value="Hidalgo">Hidalgo</option>
                            <option value="Jalisco">Jalisco</option>
                            <option value="México">Estado de México</option>
                            <option value="Michoacán">Michoacán</option>
                            <option value="Morelos">Morelos</option>
                            <option value="Nayarit">Nayarit</option>
                            <option value="Nuevo León">Nuevo León</option>
                            <option value="Oaxaca">Oaxaca</option>
                            <option value="Puebla">Puebla</option>
                            <option value="Querétaro">Querétaro</option>
                            <option value="Quintana Roo">Quintana Roo</option>
                            <option value="San Luis Potosí">San Luis Potosí</option>
                            <option value="Sinaloa">Sinaloa</option>
                            <option value="Sonora">Sonora</option>
                            <option value="Tabasco">Tabasco</option>
                            <option value="Tamaulipas">Tamaulipas</option>
                            <option value="Tlaxcala">Tlaxcala</option>
                            <option value="Veracruz">Veracruz</option>
                            <option value="Yucatán">Yucatán</option>
                            <option value="Zacatecas">Zacatecas</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="codigo_postal">
                        Código Postal <span class="required">*</span>
                    </label>
                    <input type="text" id="codigo_postal" name="codigo_postal" class="form-input" 
                           placeholder="Ej: 03100" maxlength="5" required>
                    <div class="form-help">5 dígitos</div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="referencias">
                        Referencias
                    </label>
                    <textarea id="referencias" name="referencias" class="form-textarea" 
                              placeholder="Ej: Entre calles X y Y, edificio color azul..."></textarea>
                </div>

                <div class="checkbox-group">
                    <input type="checkbox" id="es_principal" name="es_principal" value="1" class="checkbox-input">
                    <label for="es_principal" class="checkbox-label">
                        Establecer como dirección principal
                    </label>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn btn-primary">
                        ✓ Guardar Dirección
                    </button>
                    <a href="perfil.php" class="btn btn-secondary">
                        ✕ Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function validarFormulario() {
            const telefono = document.getElementById('telefono').value;
            const codigoPostal = document.getElementById('codigo_postal').value;

            // Validar teléfono (10 dígitos)
            if (!/^\d{10}$/.test(telefono)) {
                alert('El teléfono debe tener 10 dígitos');
                return false;
            }

            // Validar código postal (5 dígitos)
            if (!/^\d{5}$/.test(codigoPostal)) {
                alert('El código postal debe tener 5 dígitos');
                return false;
            }

            return true;
        }

        // Permitir solo números en teléfono y código postal
        document.getElementById('telefono').addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '').substring(0, 10);
        });

        document.getElementById('codigo_postal').addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '').substring(0, 5);
        });
    </script>
</body>
</html>