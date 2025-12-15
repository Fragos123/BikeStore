<?php
session_start();
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) { header("Location: login.php"); exit; }

include 'conexion.php';
$usuario_id = $_SESSION['usuario_id'];

// LÓGICA DE ELIMINACIÓN
if (isset($_GET['del_dir'])) {
    $id = intval($_GET['del_dir']);
    $conn->query("DELETE FROM direcciones WHERE id = $id AND usuario_id = $usuario_id");
    header("Location: perfil.php"); exit;
}
if (isset($_GET['del_card'])) {
    $id = intval($_GET['del_card']);
    $conn->query("DELETE FROM metodos_pago WHERE id = $id AND usuario_id = $usuario_id");
    header("Location: perfil.php"); exit;
}

// Obtener datos
$usuario = $conn->query("SELECT * FROM usuarios WHERE id = $usuario_id")->fetch_assoc();
$direcciones = $conn->query("SELECT * FROM direcciones WHERE usuario_id = $usuario_id")->fetch_all(MYSQLI_ASSOC);
$tarjetas = $conn->query("SELECT * FROM metodos_pago WHERE usuario_id = $usuario_id")->fetch_all(MYSQLI_ASSOC);
$total_pedidos = $conn->query("SELECT COUNT(*) as total FROM pedidos WHERE usuario_id = $usuario_id")->fetch_assoc()['total'];

// CALCULAR ANTIGÜEDAD EXACTA
$fecha_registro = new DateTime($usuario['fecha_registro']);
$hoy = new DateTime();
$antiguedad = $fecha_registro->diff($hoy);

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Perfil - BikeStore</title>
    <style>
        /* BASE DARK */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background-color: #0f0f0f; color: #fff; }
        
        .navbar { padding: 1.5rem 3rem; display: flex; justify-content: space-between; align-items: center; background: rgba(10,10,10,0.95); border-bottom: 1px solid rgba(255,255,255,0.05); }
        .logo { font-size: 1.5rem; font-weight: 900; color: white; text-decoration: none; text-transform: uppercase; }
        .nav-link { color: #ccc; text-decoration: none; font-weight: 600; font-size: 0.9rem; margin-left: 2rem; }
        
        .container { max-width: 1200px; margin: 0 auto; padding: 4rem 2rem; }
        .header-profile { margin-bottom: 4rem; border-bottom: 1px solid #333; padding-bottom: 2rem; display: flex; justify-content: space-between; align-items: flex-end; }
        .title { font-size: 3rem; font-weight: 900; line-height: 1; margin: 0; }
        .subtitle { color: #666; font-size: 1rem; margin-top: 0.5rem; }
        
        .grid { display: grid; grid-template-columns: 1fr 2fr; gap: 3rem; }
        
        .card { background: #1a1a1a; border: 1px solid #333; border-radius: 8px; padding: 2rem; margin-bottom: 2rem; }
        .card-title { font-size: 1.1rem; font-weight: 700; text-transform: uppercase; margin-bottom: 1.5rem; border-bottom: 1px solid #333; padding-bottom: 0.5rem; display: flex; justify-content: space-between; }
        
        .data-row { margin-bottom: 1rem; }
        .label { display: block; font-size: 0.7rem; color: #666; text-transform: uppercase; font-weight: 700; letter-spacing: 1px; }
        .value { font-size: 1.1rem; font-weight: 500; }
        
        .manage-item { display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: #222; margin-bottom: 0.5rem; border-radius: 4px; border: 1px solid #333; }
        .manage-info { font-size: 0.9rem; }
        .manage-sub { font-size: 0.8rem; color: #888; display: block; }
        .btn-del { color: #ef4444; text-decoration: none; font-size: 1.2rem; padding: 0.5rem; transition: 0.2s; }
        .btn-del:hover { background: rgba(239,68,68,0.1); border-radius: 4px; }
        .btn-add { font-size: 0.8rem; color: #fff; text-decoration: none; border: 1px solid #fff; padding: 0.3rem 0.8rem; border-radius: 50px; }
        
        .stats-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 2rem; }
        .stat-box { background: #222; padding: 1.5rem; text-align: center; border-radius: 4px; border: 1px solid #333; }
        .stat-num { font-size: 2rem; font-weight: 800; }
        
        @media (max-width: 768px) { .grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="logo">BIKESTORE</a>
        <div>
            <a href="productos.php" class="nav-link">TIENDA</a>
            <a href="pedidos.php" class="nav-link">PEDIDOS</a>
        </div>
    </nav>

    <div class="container">
        <div class="header-profile">
            <div>
                <h1 class="title">HOLA, <?php echo strtoupper(explode(' ', $usuario['nombre'])[0]); ?></h1>
                <p class="subtitle">Miembro <?php echo ucfirst($usuario['nivel_ciclismo']); ?></p>
            </div>
            <a href="logout.php" style="color:#ef4444; text-decoration:none; font-weight:700; border:1px solid #ef4444; padding:0.8rem 2rem;">CERRAR SESIÓN</a>
        </div>

        <div class="grid">
            <div>
                <div class="card">
                    <div class="card-title">Mis Datos <a href="editar_perfil.php" style="font-size:0.9rem; color:#fff;">✎</a></div>
                    <div class="data-row"><span class="label">Nombre</span><div class="value"><?php echo htmlspecialchars($usuario['nombre']); ?></div></div>
                    <div class="data-row"><span class="label">Correo</span><div class="value"><?php echo htmlspecialchars($usuario['correo']); ?></div></div>
                    <div class="data-row"><span class="label">Nivel</span><div class="value"><?php echo ucfirst($usuario['nivel_ciclismo']); ?></div></div>
                    <div style="margin-top:2rem;">
                        <a href="cambiar_password.php" style="color:#aaa; text-decoration:underline; font-size:0.9rem;">Cambiar Contraseña</a>
                    </div>
                </div>

                <div class="stats-row">
                    <div class="stat-box">
                        <div class="stat-num"><?php echo $total_pedidos; ?></div>
                        <span class="label">Pedidos</span>
                    </div>
                    
                    <div class="stat-box">
                        <div class="stat-num">
                            <?php 
                            if ($antiguedad->y > 0) echo $antiguedad->y;
                            elseif ($antiguedad->m > 0) echo $antiguedad->m;
                            else echo $antiguedad->d;
                            ?>
                        </div>
                        <span class="label">
                            <?php 
                            if ($antiguedad->y > 0) echo 'Años';
                            elseif ($antiguedad->m > 0) echo 'Meses';
                            else echo 'Días';
                            ?>
                        </span>
                    </div>
                </div>
            </div>

            <div>
                <div class="card">
                    <div class="card-title">
                        Direcciones 
                        <a href="agregar_direccion.php?return=perfil" class="btn-add">+</a>
                    </div>
                    <?php if(empty($direcciones)): ?>
                        <p style="color:#666; font-size:0.9rem;">No tienes direcciones guardadas.</p>
                    <?php else: ?>
                        <?php foreach($direcciones as $dir): ?>
                        <div class="manage-item">
                            <div class="manage-info">
                                <strong><?php echo htmlspecialchars($dir['nombre_completo']); ?></strong>
                                <span class="manage-sub"><?php echo htmlspecialchars($dir['calle'] .' '. $dir['numero_exterior']); ?>, <?php echo htmlspecialchars($dir['ciudad']); ?></span>
                            </div>
                            <a href="?del_dir=<?php echo $dir['id']; ?>" class="btn-del" onclick="return confirm('¿Borrar dirección?')">🗑️</a>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="card">
                    <div class="card-title">
                        Métodos de Pago
                        <a href="agregar_metodo_pago.php?return=perfil" class="btn-add">+</a>
                    </div>
                    <?php if(empty($tarjetas)): ?>
                        <p style="color:#666; font-size:0.9rem;">No tienes tarjetas guardadas.</p>
                    <?php else: ?>
                        <?php foreach($tarjetas as $card): 
                             $vencida = false;
                             if(preg_match('/^\d{2}\/\d{2}$/', $card['fecha_expiracion'])) {
                                list($m, $a) = explode('/', $card['fecha_expiracion']);
                                if(($a+2000) < date('Y') || (($a+2000)==date('Y') && $m < date('m'))) $vencida = true;
                             }
                        ?>
                        <div class="manage-item" style="<?php echo $vencida ? 'border-color:#ef4444; opacity:0.7;' : ''; ?>">
                            <div class="manage-info">
                                <strong><?php echo ucfirst($card['tipo']); ?> •••• <?php echo $card['ultimos_digitos']; ?></strong>
                                <span class="manage-sub" style="<?php echo $vencida ? 'color:#ef4444;' : ''; ?>">
                                    Exp: <?php echo $card['fecha_expiracion']; ?> <?php echo $vencida ? '(VENCIDA)' : ''; ?>
                                </span>
                            </div>
                            <a href="?del_card=<?php echo $card['id']; ?>" class="btn-del" onclick="return confirm('¿Borrar tarjeta?')">🗑️</a>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>