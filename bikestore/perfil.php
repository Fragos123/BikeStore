<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header("Location: login.php");
    exit;
}

include 'conexion.php';

$usuario_id = $_SESSION['usuario_id'];

// Obtener datos del usuario
$stmt = $conn->prepare("SELECT id, nombre, correo, telefono, rol, nivel_ciclismo, edad, fecha_registro FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();
$stmt->close();

// Obtener direcciones del usuario
$stmt = $conn->prepare("SELECT * FROM direcciones WHERE usuario_id = ? ORDER BY es_principal DESC, fecha_creacion DESC");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$direcciones = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Obtener métodos de pago del usuario
$stmt = $conn->prepare("SELECT * FROM metodos_pago WHERE usuario_id = ? ORDER BY es_principal DESC, fecha_creacion DESC");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$metodos_pago = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Obtener total de pedidos
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM pedidos WHERE usuario_id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$total_pedidos = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

$conn->close();

// Calcular tiempo desde el registro
$fecha_registro = new DateTime($usuario['fecha_registro']);
$fecha_actual = new DateTime();
$tiempo_miembro = $fecha_actual->diff($fecha_registro);
$fecha_registro_formateada = $fecha_registro->format('d/m/Y');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - BikeStore</title>
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
            color: #2d3748;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Header */
        .profile-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .back-button {
            position: absolute;
            top: 2rem;
            left: 2rem;
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
        }

        .back-button:hover {
            background: rgba(241, 245, 249, 0.2);
            transform: translateX(-5px);
        }

        .profile-avatar-large {
            width: 120px;
            height: 120px;
            background: #F1F5F9;
            color: #2E3848;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            font-weight: 800;
            margin: 0 auto 1rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            border: 5px solid rgba(241, 245, 249, 0.2);
        }

        .profile-name {
            font-size: 2.5rem;
            font-weight: 900;
            color: #F1F5F9;
            margin-bottom: 0.5rem;
            text-shadow: 2px 2px 10px rgba(0, 0, 0, 0.2);
        }

        .profile-role {
            display: inline-block;
            padding: 0.5rem 1.5rem;
            background: rgba(241, 245, 249, 0.1);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(241, 245, 249, 0.2);
            border-radius: 50px;
            color: #F1F5F9;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 1px;
        }

        /* Cards Grid - TU DISEÑO ORIGINAL */
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .card {
            background: #F1F5F9;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #2E3848, #A6A09B);
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 70px rgba(0, 0, 0, 0.15);
        }

        .card-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #2E3848, #A6A09B);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .card-title {
            font-size: 0.85rem;
            font-weight: 600;
            color: #A6A09B;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 0.5rem;
        }

        .card-value {
            font-size: 1.8rem;
            font-weight: 800;
            color: #2E3848;
            margin-bottom: 0.5rem;
        }

        .card-description {
            color: #6b7280;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        /* Info Section - TU DISEÑO ORIGINAL */
        .info-section {
            background: #F1F5F9;
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .info-section-title {
            font-size: 1.5rem;
            font-weight: 800;
            color: #2E3848;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 3px solid #e5e7eb;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .info-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: #A6A09B;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-value {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2E3848;
            padding: 0.75rem 1rem;
            background: white;
            border-radius: 10px;
            border-left: 4px solid #2E3848;
        }

        /* Stats Bar - TU DISEÑO ORIGINAL */
        .stats-bar {
            background: #F1F5F9;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            text-align: center;
            margin-bottom: 2rem;
        }

        .stats-bar-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #2E3848;
            margin-bottom: 1rem;
        }

        .stats-bar-value {
            font-size: 3rem;
            font-weight: 900;
            background: linear-gradient(135deg, #2E3848, #A6A09B);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stats-bar-label {
            color: #6b7280;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }

        /* Badge - TU DISEÑO ORIGINAL */
        .badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-top: 0.5rem;
        }

        .badge-success {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-info {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-warning {
            background: #fef3c7;
            color: #92400e;
        }

        /* NUEVAS SECCIONES */
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        /* Direcciones */
        .direcciones-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }

        .direccion-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            border: 2px solid #e5e7eb;
            position: relative;
            transition: all 0.3s ease;
        }

        .direccion-card:hover {
            border-color: #2E3848;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .direccion-card.principal {
            border-color: #2E3848;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }

        .principal-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: #2E3848;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .direccion-nombre {
            font-size: 1.1rem;
            font-weight: 700;
            color: #2E3848;
            margin-bottom: 0.75rem;
        }

        .direccion-detalle {
            color: #6b7280;
            font-size: 0.9rem;
            line-height: 1.6;
            margin-bottom: 0.5rem;
        }

        .direccion-actions {
            display: flex;
            gap: 0.75rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e5e7eb;
        }

        /* Métodos de Pago */
        .metodos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }

        .metodo-card {
            background: linear-gradient(135deg, #2E3848 0%, #3a4556 100%);
            padding: 1.5rem;
            border-radius: 16px;
            color: white;
            position: relative;
            min-height: 180px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .metodo-card.principal {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .metodo-tipo {
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            opacity: 0.8;
            margin-bottom: 1rem;
        }

        .metodo-numero {
            font-size: 1.3rem;
            font-weight: 700;
            letter-spacing: 2px;
            margin-bottom: 1rem;
        }

        .metodo-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .metodo-titular {
            font-size: 0.9rem;
            font-weight: 600;
        }

        .metodo-expiracion {
            font-size: 0.85rem;
            opacity: 0.9;
        }

        .metodo-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            color: #6b7280;
        }

        .empty-state-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        /* Action Buttons - TU DISEÑO ORIGINAL */
        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
            flex-wrap: wrap;
        }

        .btn {
            padding: 1rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #2E3848, #A6A09B);
            color: #F1F5F9;
            box-shadow: 0 10px 30px rgba(46, 56, 72, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(46, 56, 72, 0.4);
        }

        .btn-secondary {
            background: #F1F5F9;
            color: #2E3848;
            border: 2px solid #2E3848;
        }

        .btn-secondary:hover {
            background: white;
            transform: translateY(-3px);
        }

        .btn-small {
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
            border-radius: 8px;
        }

        /* Notification */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            z-index: 2000;
            display: none;
        }

        .notification.success {
            background: #d1fae5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }

        .notification.error {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }

        .notification.show {
            display: block;
            animation: slideIn 0.3s ease;
        }

        /* Responsive */
        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .back-button {
                position: static;
                margin-bottom: 2rem;
            }

            .profile-name {
                font-size: 2rem;
            }

            .cards-grid {
                grid-template-columns: 1fr;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }

            .direcciones-grid, .metodos-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Animation - TU DISEÑO ORIGINAL */
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

        @keyframes slideIn {
            from { transform: translateX(400px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        .card, .info-section, .stats-bar {
            animation: fadeInUp 0.6s ease-out forwards;
        }

        .card:nth-child(1) { animation-delay: 0.1s; }
        .card:nth-child(2) { animation-delay: 0.2s; }
        .card:nth-child(3) { animation-delay: 0.3s; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Back Button -->
        <a href="index.php" class="back-button">
            ← Volver al inicio
        </a>

        <!-- Profile Header -->
        <div class="profile-header">
            <div class="profile-avatar-large">
                <?php echo strtoupper(substr($usuario['nombre'], 0, 1)); ?>
            </div>
            <h1 class="profile-name"><?php echo htmlspecialchars($usuario['nombre']); ?></h1>
            <span class="profile-role"><?php echo ucfirst($usuario['rol']); ?></span>
        </div>

        <!-- Stats Cards - TU DISEÑO ORIGINAL -->
        <div class="cards-grid">
            <!-- Nivel de Ciclismo -->
            <div class="card">
                <div class="card-icon">🚴</div>
                <div class="card-title">Nivel de Ciclismo</div>
                <div class="card-value"><?php echo ucfirst($usuario['nivel_ciclismo']); ?></div>
                <div class="card-description">
                    <?php 
                    $descripciones = [
                        'principiante' => 'Estás comenzando tu aventura en el ciclismo',
                        'intermedio' => 'Tienes experiencia y habilidades desarrolladas',
                        'avanzado' => 'Eres un ciclista experimentado y técnico'
                    ];
                    echo $descripciones[$usuario['nivel_ciclismo']];
                    ?>
                </div>
                <span class="badge badge-info"><?php echo ucfirst($usuario['nivel_ciclismo']); ?></span>
            </div>

            <!-- Edad -->
            <div class="card">
                <div class="card-icon">📅</div>
                <div class="card-title">Edad</div>
                <div class="card-value">
                    <?php echo $usuario['edad'] ? $usuario['edad'] . ' años' : 'No especificada'; ?>
                </div>
                <div class="card-description">
                    <?php 
                    if ($usuario['edad']) {
                        if ($usuario['edad'] < 18) {
                            echo 'Categoría: Junior';
                        } elseif ($usuario['edad'] < 30) {
                            echo 'Categoría: Adulto joven';
                        } elseif ($usuario['edad'] < 50) {
                            echo 'Categoría: Adulto';
                        } else {
                            echo 'Categoría: Master';
                        }
                    } else {
                        echo 'Puedes actualizar tu edad en cualquier momento';
                    }
                    ?>
                </div>
            </div>

            <!-- Tiempo como miembro -->
            <div class="card">
                <div class="card-icon">⭐</div>
                <div class="card-title">Miembro desde</div>
                <div class="card-value">
                    <?php 
                    if ($tiempo_miembro->y > 0) {
                        echo $tiempo_miembro->y . ' año' . ($tiempo_miembro->y > 1 ? 's' : '');
                    } elseif ($tiempo_miembro->m > 0) {
                        echo $tiempo_miembro->m . ' mes' . ($tiempo_miembro->m > 1 ? 'es' : '');
                    } elseif ($tiempo_miembro->d > 0) {
                        echo $tiempo_miembro->d . ' día' . ($tiempo_miembro->d > 1 ? 's' : '');
                    } else {
                        echo 'Hoy';
                    }
                    ?>
                </div>
                <div class="card-description">
                    Te uniste el <?php echo $fecha_registro_formateada; ?>
                </div>
                <span class="badge badge-success">Activo</span>
            </div>
        </div>

        <!-- Información Personal - TU DISEÑO ORIGINAL -->
        <div class="info-section">
            <h2 class="info-section-title">Información Personal</h2>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Nombre Completo</span>
                    <div class="info-value"><?php echo htmlspecialchars($usuario['nombre']); ?></div>
                </div>
                <div class="info-item">
                    <span class="info-label">Correo Electrónico</span>
                    <div class="info-value"><?php echo htmlspecialchars($usuario['correo']); ?></div>
                </div>
                <div class="info-item">
                    <span class="info-label">Teléfono</span>
                    <div class="info-value">
                        <?php echo !empty($usuario['telefono']) ? htmlspecialchars($usuario['telefono']) : 'No especificado'; ?>
                    </div>
                </div>
                <div class="info-item">
                    <span class="info-label">Tipo de Cuenta</span>
                    <div class="info-value"><?php echo ucfirst($usuario['rol']); ?></div>
                </div>
                <div class="info-item">
                    <span class="info-label">ID de Usuario</span>
                    <div class="info-value">#<?php echo str_pad($usuario['id'], 6, '0', STR_PAD_LEFT); ?></div>
                </div>
            </div>
        </div>

        <!-- NUEVA: Direcciones de Envío -->
        <div class="info-section">
            <div class="section-header">
                <h2 class="info-section-title" style="margin: 0; padding: 0; border: none;">
                    Direcciones de Envío (<?php echo count($direcciones); ?>)
                </h2>
                <a href="agregar_direccion.php" class="btn btn-primary">+ Agregar Dirección</a>
            </div>

            <?php if (empty($direcciones)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📍</div>
                    <p>No tienes direcciones registradas</p>
                    <p style="font-size: 0.9rem; margin-top: 0.5rem;">Agrega una dirección para realizar compras</p>
                </div>
            <?php else: ?>
                <div class="direcciones-grid">
                    <?php foreach ($direcciones as $direccion): ?>
                        <div class="direccion-card <?php echo $direccion['es_principal'] ? 'principal' : ''; ?>">
                            <?php if ($direccion['es_principal']): ?>
                                <span class="principal-badge">Principal</span>
                            <?php endif; ?>
                            
                            <div class="direccion-nombre"><?php echo htmlspecialchars($direccion['nombre_completo']); ?></div>
                            
                            <div class="direccion-detalle">
                                📞 <?php echo htmlspecialchars($direccion['telefono']); ?>
                            </div>
                            
                            <div class="direccion-detalle">
                                <?php echo htmlspecialchars($direccion['calle']); ?> 
                                <?php echo htmlspecialchars($direccion['numero_exterior']); ?>
                                <?php echo !empty($direccion['numero_interior']) ? ', Int. ' . htmlspecialchars($direccion['numero_interior']) : ''; ?>
                            </div>
                            
                            <div class="direccion-detalle">
                                <?php echo htmlspecialchars($direccion['colonia']); ?>, 
                                <?php echo htmlspecialchars($direccion['ciudad']); ?>, 
                                <?php echo htmlspecialchars($direccion['estado']); ?>
                            </div>
                            
                            <div class="direccion-detalle">
                                C.P. <?php echo htmlspecialchars($direccion['codigo_postal']); ?>
                            </div>

                            <div class="direccion-actions">
                                <a href="editar_direccion.php?id=<?php echo $direccion['id']; ?>" class="btn btn-secondary btn-small">Editar</a>
                                <?php if (!$direccion['es_principal']): ?>
                                    <button onclick="eliminarDireccion(<?php echo $direccion['id']; ?>)" class="btn btn-secondary btn-small">Eliminar</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- NUEVA: Métodos de Pago -->
        <div class="info-section">
            <div class="section-header">
                <h2 class="info-section-title" style="margin: 0; padding: 0; border: none;">
                    Métodos de Pago (<?php echo count($metodos_pago); ?>)
                </h2>
                <a href="agregar_metodo_pago.php" class="btn btn-primary">+ Agregar Método</a>
            </div>

            <?php if (empty($metodos_pago)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">💳</div>
                    <p>No tienes métodos de pago registrados</p>
                    <p style="font-size: 0.9rem; margin-top: 0.5rem;">Agrega una tarjeta para realizar compras</p>
                </div>
            <?php else: ?>
                <div class="metodos-grid">
                    <?php foreach ($metodos_pago as $metodo): ?>
                        <div class="metodo-card <?php echo $metodo['es_principal'] ? 'principal' : ''; ?>">
                            <?php if ($metodo['es_principal']): ?>
                                <span class="principal-badge">Principal</span>
                            <?php endif; ?>
                            
                            <div class="metodo-tipo">
                                <?php echo ucwords(str_replace('_', ' ', $metodo['tipo'])); ?>
                            </div>
                            
                            <div class="metodo-numero">
                                •••• •••• •••• <?php echo htmlspecialchars($metodo['ultimos_digitos']); ?>
                            </div>
                            
                            <div class="metodo-info">
                                <div class="metodo-titular"><?php echo htmlspecialchars($metodo['nombre_titular']); ?></div>
                                <div class="metodo-expiracion">
                                    <?php echo str_pad($metodo['mes_expiracion'], 2, '0', STR_PAD_LEFT); ?>/<?php echo substr($metodo['ano_expiracion'], -2); ?>
                                </div>
                            </div>

                            <div class="metodo-actions">
                                <?php if (!$metodo['es_principal']): ?>
                                    <button onclick="eliminarMetodoPago(<?php echo $metodo['id']; ?>)" class="btn btn-secondary btn-small">Eliminar</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Estadísticas - TU DISEÑO ORIGINAL -->
        <div class="stats-bar">
            <div class="stats-bar-label">
                <?php if ($total_pedidos > 0): ?>
                    Revisa tu historial completo de compras
                <?php else: ?>
                    Próximamente podrás ver tu historial completo
                <?php endif; ?>
            </div>
        </div>

        <!-- Action Buttons - TU DISEÑO ORIGINAL -->
        <div class="action-buttons">
            <a href="editar-perfil.php" class="btn btn-primary">
                ✏️ Editar Perfil
            </a>
            <a href="cambiar_password.php" class="btn btn-secondary">
                🔒 Cambiar Contraseña
            </a>
            <a href="pedidos.php" class="btn btn-secondary">
                📦 Ver Mis Pedidos
            </a>
        </div>
    </div>

    <!-- Notificación -->
    <div class="notification" id="notification"></div>

    <script>
        function eliminarDireccion(id) {
            if (!confirm('¿Estás seguro de eliminar esta dirección?')) return;

            fetch('eliminar_direccion.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `direccion_id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Dirección eliminada correctamente', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showNotification(data.message || 'Error al eliminar', 'error');
                }
            })
            .catch(() => showNotification('Error de conexión', 'error'));
        }

        function eliminarMetodoPago(id) {
            if (!confirm('¿Estás seguro de eliminar este método de pago?')) return;

            fetch('eliminar_metodo_pago.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `metodo_id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Método de pago eliminado correctamente', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showNotification(data.message || 'Error al eliminar', 'error');
                }
            })
            .catch(() => showNotification('Error de conexión', 'error'));
        }

        function showNotification(message, type = 'success') {
            const notification = document.getElementById('notification');
            notification.textContent = message;
            notification.className = `notification ${type} show`;

            setTimeout(() => {
                notification.classList.remove('show');
            }, 3000);
        }

        // Mostrar mensajes de la URL
        window.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('success')) {
                showNotification(decodeURIComponent(urlParams.get('success')), 'success');
                window.history.replaceState({}, document.title, 'perfil.php');
            }
            if (urlParams.get('error')) {
                showNotification(decodeURIComponent(urlParams.get('error')), 'error');
                window.history.replaceState({}, document.title, 'perfil.php');
            }
        });
    </script>
</body>
</html>-bar-title">Pedidos Realizados</div>
            <div class="stats-bar-value"><?php echo $total_pedidos; ?></div>
            <div class="stats