<?php 

session_start();

// Verificar si el usuario está logueado y es operador
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true || $_SESSION['usuario_rol'] !== 'operador') {
    header("Location: login.php");
    exit;
}

// Obtener estadísticas de la base de datos
include 'conexion.php';

// Contar usuarios totales
$stmt = $conn->query("SELECT COUNT(*) as total FROM usuarios");
$total_usuarios = $stmt->fetch_assoc()['total'];

// Contar clientes
$stmt = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE rol = 'cliente'");
$total_clientes = $stmt->fetch_assoc()['total'];

// Contar productos
$stmt = $conn->query("SELECT COUNT(*) as total FROM productos");
$total_productos = $stmt->fetch_assoc()['total'];

// Obtener últimos usuarios registrados
$stmt = $conn->query("SELECT id, nombre, correo, rol, fecha_registro FROM usuarios WHERE rol = 'cliente' ORDER BY fecha_registro DESC LIMIT 5");
$ultimos_usuarios = $stmt->fetch_all(MYSQLI_ASSOC);

// Obtener últimos productos
$stmt = $conn->query("SELECT id, nombre, tipo, precio, stock FROM productos ORDER BY id DESC LIMIT 5");
$ultimos_productos = $stmt->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Operador - BikeStore</title>
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
        }

        /* Sidebar estilo moderno */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            width: 260px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            color: #2d3748;
            padding: 2rem 0;
            box-shadow: 4px 0 30px rgba(0, 0, 0, 0.1);
            z-index: 100;
        }

        .sidebar-header {
            padding: 0 1.5rem 2rem;
            border-bottom: 2px solid #F1F5F9;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 900;
            letter-spacing: -1px;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, #45556C, #364458);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .user-info {
            font-size: 0.9rem;
            color: #4a5568;
            margin-top: 0.5rem;
            font-weight: 500;
        }

        .user-role {
            display: inline-block;
            background: linear-gradient(135deg, #45556C, #364458);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            margin-top: 0.5rem;
            font-weight: 600;
        }

        .sidebar-menu {
            margin-top: 2rem;
        }

        .menu-item {
            padding: 1rem 1.5rem;
            color: #4a5568;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .menu-item:hover {
            background: linear-gradient(90deg, rgba(69, 85, 108, 0.1), transparent);
            color: #45556C;
            padding-left: 2rem;
        }

        .menu-item.active {
            background: linear-gradient(90deg, rgba(69, 85, 108, 0.15), transparent);
            color: #45556C;
            border-right: 4px solid #45556C;
            font-weight: 600;
        }

        .menu-icon {
            font-size: 1.3rem;
        }

        .main-content {
            margin-left: 260px;
            padding: 2rem;
        }

        .top-bar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            padding: 1.5rem 2rem;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .page-title {
            font-size: 1.8rem;
            font-weight: 800;
            background: linear-gradient(135deg, #45556C, #364458);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .top-buttons {
            display: flex;
            gap: 1rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            border: none;
            transition: all 0.3s ease;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .btn-primary {
            background: linear-gradient(135deg, #45556C, #364458);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(69, 85, 108, 0.3);
        }

        .btn-secondary {
            background: white;
            color: #45556C;
            border: 2px solid rgba(69, 85, 108, 0.3);
        }

        .btn-secondary:hover {
            background: rgba(69, 85, 108, 0.05);
            border-color: #45556C;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            padding: 1.5rem;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        }

        .stat-label {
            font-size: 0.85rem;
            color: #718096;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 900;
            background: linear-gradient(135deg, #45556C, #364458);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-top: 0.5rem;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            background: linear-gradient(135deg, #45556C, #364458);
            box-shadow: 0 8px 20px rgba(69, 85, 108, 0.3);
        }

        .table-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.3);
            margin-bottom: 2rem;
        }

        .table-header {
            padding: 1.5rem 2rem;
            border-bottom: 2px solid #F1F5F9;
        }

        .table-title {
            font-size: 1.3rem;
            font-weight: 800;
            background: linear-gradient(135deg, #45556C, #364458);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: linear-gradient(135deg, #F1F5F9, #e2e8f0);
            padding: 1rem 1.5rem;
            text-align: left;
            font-weight: 600;
            color: #4a5568;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #F1F5F9;
            color: #2d3748;
        }

        tr:hover {
            background: linear-gradient(90deg, rgba(69, 85, 108, 0.03), transparent);
        }

        .badge {
            display: inline-block;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-cliente {
            background: linear-gradient(135deg, #43e97b, #38f9d7);
            color: white;
        }

        .badge-montaña {
            background: linear-gradient(135deg, #fa709a, #fee140);
            color: white;
        }

        .badge-ruta {
            background: linear-gradient(135deg, #30cfd0, #330867);
            color: white;
        }

        .badge-urbana {
            background: linear-gradient(135deg, #a8edea, #fed6e3);
            color: #333;
        }

        .badge-eléctrica {
            background: linear-gradient(135deg, #fbc2eb, #a6c1ee);
            color: white;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn-icon {
            padding: 0.5rem 0.75rem;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            color: white;
            text-decoration: none;
        }

        .btn-edit {
            background: linear-gradient(135deg, #45556C, #364458);
        }

        .btn-edit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(69, 85, 108, 0.3);
        }

        .btn-delete {
            background: linear-gradient(135deg, #fa709a, #fee140);
        }

        .btn-delete:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(250, 112, 154, 0.3);
        }

        .stock-badge {
            display: inline-block;
            padding: 0.3rem 0.7rem;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .stock-ok {
            background: #c6f6d5;
            color: #22543d;
        }

        .stock-low {
            background: #feebc8;
            color: #7c2d12;
        }

        .stock-out {
            background: #fed7d7;
            color: #742a2a;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(69, 85, 108, 0.1);
            backdrop-filter: blur(10px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            padding: 2.5rem;
            border-radius: 20px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: modalSlideIn 0.3s ease;
        }

        @keyframes modalSlideIn {
            from {
                transform: scale(0.9) translateY(-20px);
                opacity: 0;
            }
            to {
                transform: scale(1) translateY(0);
                opacity: 1;
            }
        }

        .modal-title {
            font-size: 1.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, #45556C, #364458);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
        }

        .modal-text {
            color: #4a5568;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .modal-buttons {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
        }

        .btn-cancel {
            background: white;
            color: #4a5568;
            border: 2px solid #e2e8f0;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-cancel:hover {
            background: #F1F5F9;
            border-color: #cbd5e0;
        }

        .btn-confirm {
            background: linear-gradient(135deg, #fa709a, #fee140);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(250, 112, 154, 0.3);
        }

        .btn-confirm:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(250, 112, 154, 0.4);
        }

        /* Notificaciones */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 1.5rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            z-index: 2000;
            display: none;
            align-items: center;
            gap: 0.75rem;
            font-weight: 600;
            animation: slideIn 0.3s ease;
            backdrop-filter: blur(20px);
        }

        .notification.success {
            background: linear-gradient(135deg, rgba(67, 233, 123, 0.95), rgba(56, 249, 215, 0.95));
            color: white;
        }

        .notification.error {
            background: linear-gradient(135deg, rgba(252, 92, 125, 0.95), rgba(106, 130, 251, 0.95));
            color: white;
        }

        .notification.show {
            display: flex;
        }

        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @media (max-width: 768px) {
            .sidebar { width: 80px; }
            .sidebar-header .user-info,
            .sidebar-header .user-role {
                display: none;
            }
            .menu-item span:not(.menu-icon) {
                display: none;
            }
            .main-content { 
                margin-left: 80px; 
                padding: 1rem; 
            }
            .stats-grid { 
                grid-template-columns: 1fr; 
            }
        }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="logo">BIKESTORE</div>
            <div class="user-info">
                <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>
                <div class="user-role">Operador</div>
            </div>
        </div>
        
        <nav class="sidebar-menu">
            <a href="principal_operador.php" class="menu-item active">
                <span class="menu-icon">📊</span>
                <span>Dashboard</span>
            </a>
            <a href="perfil.php" class="menu-item">
                <span class="menu-icon">⚙️</span>
                <span>Mi Perfil</span>
            </a>
            <a href="logout.php" class="menu-item">
                <span class="menu-icon">🚪</span>
                <span>Cerrar Sesión</span>
            </a>
        </nav>
    </aside>

    <main class="main-content">
        <div class="top-bar">
            <h1 class="page-title">Panel de Operador</h1>
            <div class="top-buttons">
                <a href="operador_crear_producto.php" class="btn btn-primary">
                    ➕ Crear Producto
                </a>
                <a href="index.php" class="btn btn-secondary">Ver Sitio</a>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div>
                    <div class="stat-label">Total Usuarios</div>
                    <div class="stat-value"><?php echo $total_usuarios; ?></div>
                </div>
                <div class="stat-icon">👥</div>
            </div>

            <div class="stat-card">
                <div>
                    <div class="stat-label">Total Clientes</div>
                    <div class="stat-value"><?php echo $total_clientes; ?></div>
                </div>
                <div class="stat-icon">👤</div>
            </div>

            <div class="stat-card">
                <div>
                    <div class="stat-label">Total Productos</div>
                    <div class="stat-value"><?php echo $total_productos; ?></div>
                </div>
                <div class="stat-icon">🚴</div>
            </div>
        </div>

        <div class="table-card">
            <div class="table-header">
                <h2 class="table-title">Últimos Productos Agregados</h2>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($ultimos_productos)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 2rem; color: #718096;">
                                No hay productos registrados. ¡Crea el primer producto!
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($ultimos_productos as $producto): ?>
                        <tr>
                            <td>#<?php echo str_pad($producto['id'], 4, '0', STR_PAD_LEFT); ?></td>
                            <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $producto['tipo']; ?>">
                                    <?php echo ucfirst($producto['tipo']); ?>
                                </span>
                            </td>
                            <td>$<?php echo number_format($producto['precio'], 2); ?></td>
                            <td>
                                <?php
                                $stock = $producto['stock'];
                                if ($stock == 0) {
                                    echo '<span class="stock-badge stock-out">Agotado</span>';
                                } elseif ($stock <= 5) {
                                    echo '<span class="stock-badge stock-low">' . $stock . ' unid.</span>';
                                } else {
                                    echo '<span class="stock-badge stock-ok">' . $stock . ' unid.</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="operador_editar_producto.php?id=<?php echo $producto['id']; ?>" class="btn-icon btn-edit" title="Editar producto">
                                        ✏️ Editar
                                    </a>
                                    <button class="btn-icon btn-delete" onclick="confirmarEliminacionProducto(<?php echo $producto['id']; ?>, '<?php echo htmlspecialchars($producto['nombre']); ?>')" title="Eliminar producto">
                                        🗑️
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="table-card">
            <div class="table-header">
                <h2 class="table-title">Últimos Clientes Registrados</h2>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Fecha Registro</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($ultimos_usuarios)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 2rem; color: #718096;">
                                No hay clientes registrados
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($ultimos_usuarios as $usuario): ?>
                        <tr>
                            <td>#<?php echo str_pad($usuario['id'], 4, '0', STR_PAD_LEFT); ?></td>
                            <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['correo']); ?></td>
                            <td>
                                <span class="badge badge-cliente">
                                    <?php echo ucfirst($usuario['rol']); ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($usuario['fecha_registro'])); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-icon btn-delete" onclick="confirmarEliminacion(<?php echo $usuario['id']; ?>, '<?php echo htmlspecialchars($usuario['nombre']); ?>')" title="Eliminar usuario">
                                        🗑️
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Modal para eliminar usuario -->
    <div class="modal" id="deleteModalUsuario">
        <div class="modal-content">
            <h3 class="modal-title">⚠️ Confirmar Eliminación</h3>
            <p class="modal-text" id="modalTextUsuario">
                ¿Estás seguro de que deseas eliminar este usuario? Esta acción no se puede deshacer.
            </p>
            <div class="modal-buttons">
                <button class="btn-cancel" onclick="cerrarModalUsuario()">Cancelar</button>
                <button class="btn-confirm" onclick="eliminarUsuario()">Sí, eliminar</button>
            </div>
        </div>
    </div>

    <!-- Modal para eliminar producto -->
    <div class="modal" id="deleteModalProducto">
        <div class="modal-content">
            <h3 class="modal-title">⚠️ Confirmar Eliminación</h3>
            <p class="modal-text" id="modalTextProducto">
                ¿Estás seguro de que deseas eliminar este producto? Esta acción no se puede deshacer.
            </p>
            <div class="modal-buttons">
                <button class="btn-cancel" onclick="cerrarModalProducto()">Cancelar</button>
                <button class="btn-confirm" onclick="eliminarProducto()">Sí, eliminar</button>
            </div>
        </div>
    </div>

    <!-- Notificación -->
    <div class="notification" id="notification">
        <span id="notificationText"></span>
    </div>

    <script>
        let usuarioAEliminar = null;
        let productoAEliminar = null;

        window.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('success')) {
                mostrarNotificacion(decodeURIComponent(urlParams.get('success')), 'success');
                window.history.replaceState({}, document.title, 'principal_operador.php');
            }
            if (urlParams.get('error')) {
                mostrarNotificacion(decodeURIComponent(urlParams.get('error')), 'error');
                window.history.replaceState({}, document.title, 'principal_operador.php');
            }
        });

        // Funciones para eliminar usuarios
        function confirmarEliminacion(id, nombre) {
            usuarioAEliminar = id;
            document.getElementById('modalTextUsuario').innerHTML = 
                `¿Estás seguro de que deseas eliminar al usuario <strong>${nombre}</strong>?<br><br>Esta acción no se puede deshacer.`;
            document.getElementById('deleteModalUsuario').classList.add('active');
        }

        function cerrarModalUsuario() {
            document.getElementById('deleteModalUsuario').classList.remove('active');
            usuarioAEliminar = null;
        }

        function eliminarUsuario() {
            if (usuarioAEliminar) {
                window.location.href = 'operador_eliminar_usuario.php?id=' + usuarioAEliminar;
            }
        }

        // Funciones para eliminar productos
        function confirmarEliminacionProducto(id, nombre) {
            productoAEliminar = id;
            document.getElementById('modalTextProducto').innerHTML = 
                `¿Estás seguro de que deseas eliminar el producto <strong>${nombre}</strong>?<br><br>Esta acción no se puede deshacer.`;
            document.getElementById('deleteModalProducto').classList.add('active');
        }

        function cerrarModalProducto() {
            document.getElementById('deleteModalProducto').classList.remove('active');
            productoAEliminar = null;
        }

        function eliminarProducto() {
            if (productoAEliminar) {
                window.location.href = 'operador_eliminar_producto.php?id=' + productoAEliminar;
            }
        }

        function mostrarNotificacion(mensaje, tipo) {
            const notification = document.getElementById('notification');
            const notificationText = document.getElementById('notificationText');
            
            notificationText.textContent = mensaje;
            notification.className = 'notification ' + tipo + ' show';
            
            setTimeout(() => {
                notification.classList.remove('show');
            }, 4000);
        }

        document.getElementById('deleteModalUsuario').addEventListener('click', function(e) {
            if (e.target === this) {
                cerrarModalUsuario();
            }
        });

        document.getElementById('deleteModalProducto').addEventListener('click', function(e) {
            if (e.target === this) {
                cerrarModalProducto();
            }
        });
    </script>
</body>
</html>