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
    <title>Agregar Método de Pago - BikeStore</title>
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
            max-width: 600px;
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
            background: linear-gradient(135deg, #667eea, #764ba2);
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

        .info-box {
            background: #dbeafe;
            border-left: 4px solid #3b82f6;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        .info-box-text {
            color: #1e40af;
            font-size: 0.9rem;
            line-height: 1.5;
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
        .form-select {
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
        .form-select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
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

        .card-preview {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 2rem;
            border-radius: 16px;
            color: white;
            margin-bottom: 2rem;
            min-height: 200px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .card-chip {
            width: 50px;
            height: 40px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .card-number-preview {
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: 3px;
            margin-bottom: 1rem;
        }

        .card-info-preview {
            display: flex;
            justify-content: space-between;
        }

        .card-name-preview {
            font-size: 0.95rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .card-expiry-preview {
            font-size: 0.9rem;
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
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.4);
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
                <div class="form-icon">💳</div>
                <h1 class="form-title">Agregar Método de Pago</h1>
                <p class="form-subtitle">Completa los datos de tu tarjeta</p>
            </div>

            <div class="info-box">
                <div class="info-box-text">
                    🔒 <strong>Tus datos están seguros.</strong> Solo almacenamos los últimos 4 dígitos de tu tarjeta. No guardamos el número completo ni el CVV.
                </div>
            </div>

            <?php if (isset($_GET['error'])): ?>
                <div class="message error">
                    <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>

            <!-- Vista Previa de Tarjeta -->
            <div class="card-preview">
                <div class="card-chip"></div>
                <div class="card-number-preview" id="cardNumberPreview">
                    •••• •••• •••• ••••
                </div>
                <div class="card-info-preview">
                    <div class="card-name-preview" id="cardNamePreview">NOMBRE DEL TITULAR</div>
                    <div class="card-expiry-preview" id="cardExpiryPreview">MM/AA</div>
                </div>
            </div>

            <form action="procesar_agregar_metodo_pago.php" method="POST" onsubmit="return validarFormulario()">
                
                <div class="form-group">
                    <label class="form-label" for="tipo">
                        Tipo de Tarjeta <span class="required">*</span>
                    </label>
                    <select id="tipo" name="tipo" class="form-select" required>
                        <option value="">Selecciona un tipo</option>
                        <option value="tarjeta_credito">Tarjeta de Crédito</option>
                        <option value="tarjeta_debito">Tarjeta de Débito</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="nombre_titular">
                        Nombre del Titular <span class="required">*</span>
                    </label>
                    <input type="text" id="nombre_titular" name="nombre_titular" class="form-input" 
                           placeholder="Ej: JUAN PEREZ" 
                           style="text-transform: uppercase;"
                           oninput="updateCardPreview()"
                           required>
                    <div class="form-help">Como aparece en la tarjeta</div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="numero_tarjeta">
                        Número de Tarjeta <span class="required">*</span>
                    </label>
                    <input type="text" id="numero_tarjeta" name="numero_tarjeta" class="form-input" 
                           placeholder="1234 5678 9012 3456"
                           maxlength="19"
                           oninput="updateCardPreview()"
                           required>
                    <div class="form-help">16 dígitos</div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="mes_expiracion">
                            Mes de Expiración <span class="required">*</span>
                        </label>
                        <select id="mes_expiracion" name="mes_expiracion" class="form-select" 
                                onchange="updateCardPreview()"
                                required>
                            <option value="">Mes</option>
                            <?php for ($i = 1; $i <= 12; $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="ano_expiracion">
                            Año de Expiración <span class="required">*</span>
                        </label>
                        <select id="ano_expiracion" name="ano_expiracion" class="form-select"
                                onchange="updateCardPreview()"
                                required>
                            <option value="">Año</option>
                            <?php 
                            $ano_actual = date('Y');
                            for ($i = 0; $i <= 10; $i++): 
                                $ano = $ano_actual + $i;
                            ?>
                                <option value="<?php echo $ano; ?>"><?php echo $ano; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="cvv">
                        CVV <span class="required">*</span>
                    </label>
                    <input type="password" id="cvv" name="cvv" class="form-input" 
                           placeholder="123"
                           maxlength="4"
                           required>
                    <div class="form-help">3 o 4 dígitos en el reverso de la tarjeta</div>
                </div>

                <div class="checkbox-group">
                    <input type="checkbox" id="es_principal" name="es_principal" value="1" class="checkbox-input">
                    <label for="es_principal" class="checkbox-label">
                        Establecer como método de pago principal
                    </label>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn btn-primary">
                        ✓ Guardar Tarjeta
                    </button>
                    <a href="perfil.php" class="btn btn-secondary">
                        ✕ Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function updateCardPreview() {
            // Actualizar nombre
            const nombre = document.getElementById('nombre_titular').value || 'NOMBRE DEL TITULAR';
            document.getElementById('cardNamePreview').textContent = nombre.toUpperCase();

            // Actualizar número (últimos 4 dígitos)
            const numero = document.getElementById('numero_tarjeta').value.replace(/\s/g, '');
            if (numero.length >= 4) {
                const ultimosDigitos = numero.slice(-4);
                document.getElementById('cardNumberPreview').textContent = `•••• •••• •••• ${ultimosDigitos}`;
            } else {
                document.getElementById('cardNumberPreview').textContent = '•••• •••• •••• ••••';
            }

            // Actualizar fecha de expiración
            const mes = document.getElementById('mes_expiracion').value;
            const ano = document.getElementById('ano_expiracion').value;
            if (mes && ano) {
                const mesFormatted = mes.toString().padStart(2, '0');
                const anoFormatted = ano.toString().slice(-2);
                document.getElementById('cardExpiryPreview').textContent = `${mesFormatted}/${anoFormatted}`;
            } else {
                document.getElementById('cardExpiryPreview').textContent = 'MM/AA';
            }
        }

        function validarFormulario() {
            const numero = document.getElementById('numero_tarjeta').value.replace(/\s/g, '');
            const cvv = document.getElementById('cvv').value;
            const mes = parseInt(document.getElementById('mes_expiracion').value);
            const ano = parseInt(document.getElementById('ano_expiracion').value);

            // Validar número de tarjeta (16 dígitos)
            if (!/^\d{16}$/.test(numero)) {
                alert('El número de tarjeta debe tener 16 dígitos');
                return false;
            }

            // Validar CVV (3 o 4 dígitos)
            if (!/^\d{3,4}$/.test(cvv)) {
                alert('El CVV debe tener 3 o 4 dígitos');
                return false;
            }

            // Validar que la tarjeta no esté vencida
            const fechaActual = new Date();
            const anoActual = fechaActual.getFullYear();
            const mesActual = fechaActual.getMonth() + 1;

            if (ano < anoActual || (ano === anoActual && mes < mesActual)) {
                alert('La tarjeta está vencida');
                return false;
            }

            return true;
        }

        // Formatear número de tarjeta mientras se escribe
        document.getElementById('numero_tarjeta').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s/g, '').replace(/\D/g, '');
            let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
            e.target.value = formattedValue.substring(0, 19);
        });

        // Permitir solo números en CVV
        document.getElementById('cvv').addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '').substring(0, 4);
        });

        // Convertir nombre a mayúsculas
        document.getElementById('nombre_titular').addEventListener('input', function(e) {
            this.value = this.value.toUpperCase();
        });
    </script>
</body>
</html>