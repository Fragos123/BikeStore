<?php

session_start();

// Verificar si el usuario está logueado y es admin
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true || $_SESSION['usuario_rol'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Obtener ID del usuario
if (!isset($_GET['id'])) {
    header("Location: principal_admin.php");
    exit;
}

include 'conexion.php';

$usuario_id = (int)$_GET['id'];

// Obtener datos del usuario
$stmt = $conn->prepare("SELECT id, nombre, correo, rol, nivel_ciclismo, edad, fecha_registro FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    $stmt->close();
    $conn->close();
    header("Location: principal_admin.php?error=" . urlencode("Usuario no encontrado"));
    exit;
}

$usuario = $resultado->fetch_assoc();
$stmt->close();
$conn->close();

// Calcular tiempo desde el registro
$fecha_registro = new DateTime($usuario['fecha_registro']);
$fecha_actual = new DateTime();
$tiempo_miembro = $fecha_actual->diff($fecha_registro);
$fecha_registro_formateada = $fecha_registro->format('d/m/Y H:i');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Usuario - BikeStore Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #2c3e50;
            min-height: 100vh;
            padding: 2rem;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .back-button {
            background: white;
            color: #2c3e50;
            border: 1px solid #d1d5db;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 2rem;
        }

        .back-button:hover {
            background: #f9fafb;
            border-color: #2c3e50;
        }

        .profile-header {
            text-align: center;
            margin-bottom: 2.5rem;
            background: white;
            border-radius: 12px;
            padding: 3rem 2rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            background: #2c3e50;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0 auto 1.5rem;
        }

        .profile-name {
            font-size: 2rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 0.75rem;
        }

        .profile-role {
            display: inline-block;
            padding: 0.5rem 1.25rem;
            background: #f9fafb;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            color: #2c3e50;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
        }

        .info-card {
            background: white;
            border-radius: 12px;
            padding: 2.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            animation: fadeInUp 0.4s ease-out;
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #EFEFEF;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .info-label {
            font-size: 0.8rem;
            font-weight: 500;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-value {
            font-size: 1rem;
            font-weight: 500;
            color: #2c3e50;
            padding: 0.75rem 1rem;
            background: #f9fafb;
            border-radius: 8px;
            border-left: 3px solid #2c3e50;
        }

        .badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .badge-admin { 
            background: #fef2f2; 
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .badge-operador { 
            background: #eff6ff; 
            color: #1e40af;
            border: 1px solid #bfdbfe;
        }
        .badge-cliente { 
            background: #ecfdf5; 
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
            flex-wrap: wrap;
        }

        .btn {
            padding: 0.875rem 1.75rem;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .btn-primary {
            background: #2c3e50;
            color: white;
        }

        .btn-primary:hover {
            background: #1a252f;
        }

        .btn-secondary {
            background: white;
            color: #6b7280;
            border: 1px solid #d1d5db;
        }

        .btn-secondary:hover {
            background: #f9fafb;
            border-color: #9ca3af;
        }

        .stats-card {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-box {
            background: white;
            padding: 1.75rem;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            animation: fadeInUp 0.4s ease-out;
        }

        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .stat-label {
            font-size: 0.8rem;
            color: #6b7280;
            font-weight: 500;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
            letter-spacing: 0.5px;
        }

        .stat-value {
            font-size: 1.25rem;
            font-weight: 700;
            color: #2c3e50;
        }

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

        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .profile-name {
                font-size: 1.5rem;
            }

            .info-card {
                padding: 1.5rem;
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
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="principal_admin.php" class="back-button">← Volver al panel</a>

        <div class="profile-header">
            <div class="profile-avatar">
                <?php echo strtoupper(substr($usuario['nombre'], 0, 1)); ?>
            </div>
            <h1 class="profile-name"><?php echo htmlspecialchars($usuario['nombre']); ?></h1>
            <span class="profile-role"><?php echo ucfirst($usuario['rol']); ?></span>
        </div>

        <div class="stats-card">
            <div class="stat-box">
                <div class="stat-icon">🚴</div>
                <div class="stat-label">Nivel de Ciclismo</div>
                <div class="stat-value"><?php echo ucfirst($usuario['nivel_ciclismo']); ?></div>
            </div>

            <div class="stat-box">
                <div class="stat-icon">📅</div>
                <div class="stat-label">Edad</div>
                <div class="stat-value">
                    <?php echo $usuario['edad'] ? $usuario['edad'] . ' años' : 'No especificada'; ?>
                </div>
            </div>

            <div class="stat-box">
                <div class="stat-icon">⭐</div>
                <div class="stat-label">Miembro desde</div>
                <div class="stat-value">
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
            </div>
        </div>

        <div class="info-card">
            <h2 class="card-title">Información Personal</h2>
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
                    <span class="info-label">Tipo de Cuenta</span>
                    <div class="info-value">
                        <span class="badge badge-<?php echo $usuario['rol']; ?>">
                            <?php echo ucfirst($usuario['rol']); ?>
                        </span>
                    </div>
                </div>

                <div class="info-item">
                    <span class="info-label">ID de Usuario</span>
                    <div class="info-value">#<?php echo str_pad($usuario['id'], 6, '0', STR_PAD_LEFT); ?></div>
                </div>

                <div class="info-item">
                    <span class="info-label">Nivel de Ciclismo</span>
                    <div class="info-value"><?php echo ucfirst($usuario['nivel_ciclismo']); ?></div>
                </div>

                <div class="info-item">
                    <span class="info-label">Fecha de Registro</span>
                    <div class="info-value"><?php echo $fecha_registro_formateada; ?></div>
                </div>
            </div>
        </div>

        <div class="action-buttons">
            <?php if ($usuario['id'] != $_SESSION['usuario_id']): ?>
                <a href="admin_editar_usuario.php?id=<?php echo $usuario['id']; ?>" class="btn btn-primary">
                    ✏️ Editar Usuario
                </a>
            <?php endif; ?>
            <a href="principal_admin.php" class="btn btn-secondary">
                ← Volver al Panel
            </a>
        </div>
    </div>
</body>
</html>