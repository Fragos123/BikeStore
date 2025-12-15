<?php 
session_start();
// admin_reporte_ventas.php

if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true || $_SESSION['usuario_rol'] !== 'admin') {
    header("Location: login.php");
    exit;
}

include 'conexion.php';

// --- CONFIGURACIÓN DE FILTROS ---
$mes_actual = date('m');
$anio_actual = date('Y');

// Si no se envían filtros, asumimos "GLOBAL" (todos los tiempos)
$es_global = !isset($_GET['filtrar']); 

$filtro_mes = isset($_GET['mes']) ? (int)$_GET['mes'] : $mes_actual;
$filtro_anio = isset($_GET['anio']) ? (int)$_GET['anio'] : $anio_actual;

// Construir condición WHERE dinámica
$where_clause = "WHERE estado != 'cancelado'";
$where_clause_all = "WHERE 1=1"; // Para conteos totales incluyendo cancelados

$params = [];
$types = "";

if (!$es_global) {
    $where_clause .= " AND MONTH(fecha) = ? AND YEAR(fecha) = ?";
    $where_clause_all .= " AND MONTH(fecha) = ? AND YEAR(fecha) = ?";
    $params[] = $filtro_mes;
    $params[] = $filtro_anio;
    $types .= "ii";
}

// 1. ESTADÍSTICAS GENERALES (KPIs)
$sql_kpi = "
    SELECT 
        COUNT(*) as total_pedidos, 
        COALESCE(SUM(CASE WHEN estado != 'cancelado' THEN total ELSE 0 END), 0) as ingresos_totales, 
        AVG(CASE WHEN estado != 'cancelado' THEN total ELSE NULL END) as pedido_promedio,
        SUM(CASE WHEN estado = 'cancelado' THEN 1 ELSE 0 END) as total_cancelados
    FROM pedidos 
    $where_clause_all
";
$stmt = $conn->prepare($sql_kpi);
if(!$es_global) $stmt->bind_param($types, ...$params);
$stmt->execute();
$kpis = $stmt->get_result()->fetch_assoc();
$stmt->close();

// 2. DISTRIBUCIÓN DE ESTATUS (Para la Gráfica de Pastel)
$sql_status = "SELECT estado, COUNT(*) as cantidad FROM pedidos $where_clause_all GROUP BY estado";
$stmt = $conn->prepare($sql_status);
if(!$es_global) $stmt->bind_param($types, ...$params);
$stmt->execute();
$res_status = $stmt->get_result();

$status_labels = [];
$status_data = [];
$colores_status = [
    'pendiente' => '#f59e0b', 
    'procesando' => '#3b82f6', 
    'enviado' => '#6366f1', 
    'entregado' => '#10b981', 
    'cancelado' => '#ef4444'
];
$status_colors = [];

while($row = $res_status->fetch_assoc()) {
    $status_labels[] = ucfirst($row['estado']);
    $status_data[] = $row['cantidad'];
    $status_colors[] = $colores_status[$row['estado']] ?? '#ccc';
}
$stmt->close();

// 3. VENTAS EN EL TIEMPO (Línea)
// Si es Global -> Agrupar por MES
// Si es Mensual -> Agrupar por DÍA
if ($es_global) {
    $sql_time = "
        SELECT DATE_FORMAT(fecha, '%Y-%m') as periodo, SUM(total) as venta 
        FROM pedidos $where_clause 
        GROUP BY periodo ORDER BY periodo ASC LIMIT 12
    ";
} else {
    $sql_time = "
        SELECT DAY(fecha) as periodo, SUM(total) as venta 
        FROM pedidos $where_clause 
        GROUP BY periodo ORDER BY periodo ASC
    ";
}

$stmt = $conn->prepare($sql_time);
if(!$es_global) $stmt->bind_param($types, ...$params);
$stmt->execute();
$res_time = $stmt->get_result();

$time_labels = [];
$time_data = [];
while($row = $res_time->fetch_assoc()) {
    $time_labels[] = $es_global ? $row['periodo'] : "Día " . $row['periodo'];
    $time_data[] = $row['venta'];
}
$stmt->close();

// 4. TOP PRODUCTOS
$sql_top = "
    SELECT 
        pi.nombre_producto, 
        SUM(pi.cantidad) as total_vendido, 
        SUM(pi.subtotal) as ingresos_producto 
    FROM pedido_items pi
    JOIN pedidos p ON pi.pedido_id = p.id
    " . str_replace("WHERE", "WHERE", $where_clause) . "
    GROUP BY pi.producto_id, pi.nombre_producto 
    ORDER BY total_vendido DESC 
    LIMIT 5
";
// Reusamos where_clause pero necesitamos asegurarnos que aplique a la tabla 'p' si hay ambigüedad, 
// pero como usamos prepared statements con los mismos parámetros, ajustamos la query.
// Truco: str_replace para prefijar tabla si fuera necesario, pero aquí 'fecha' y 'estado' son únicos de pedidos.
// Simplemente ejecutamos con los mismos params.

$stmt = $conn->prepare($sql_top);
if(!$es_global) $stmt->bind_param($types, ...$params);
$stmt->execute();
$top_productos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$nombres_prod = [];
$cantidades_prod = [];
foreach($top_productos as $p) {
    $nombres_prod[] = $p['nombre_producto'];
    $cantidades_prod[] = $p['total_vendido'];
}

$conn->close();

$meses_txt = [1=>'Ene',2=>'Feb',3=>'Mar',4=>'Abr',5=>'May',6=>'Jun',7=>'Jul',8=>'Ago',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dic'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Ventas - BikeStore</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f8f7f4; padding: 2rem; color: #2c2c2c; }
        .container { max-width: 1200px; margin: 0 auto; }
        
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem; }
        .title-group h1 { margin: 0; font-size: 1.8rem; color: #2c3e50; }
        .title-group p { margin: 0.5rem 0 0; color: #64748b; }
        
        .filter-box { background: white; padding: 0.8rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); display: flex; gap: 0.5rem; align-items: center; }
        .form-select { padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; }
        .btn { padding: 0.5rem 1rem; border: none; border-radius: 4px; cursor: pointer; font-weight: 600; text-decoration: none; }
        .btn-dark { background: #2c3e50; color: white; }
        .btn-outline { background: white; border: 1px solid #ccc; color: #333; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-card { background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); text-align: center; border-top: 4px solid #2c3e50; }
        .stat-val { font-size: 2rem; font-weight: 800; color: #2c3e50; margin: 0.5rem 0; }
        .stat-lbl { color: #64748b; font-size: 0.85rem; text-transform: uppercase; font-weight: 700; }
        
        .charts-row { display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem; }
        .chart-container { background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .chart-title { text-align: center; font-weight: 700; color: #2c3e50; margin-bottom: 1.5rem; }
        
        .table-container { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 1rem; text-align: left; border-bottom: 1px solid #f1f5f9; }
        th { background: #f8fafc; color: #475569; }

        @media (max-width: 900px) { .charts-row { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="title-group">
                <h1>Reporte de Ventas</h1>
                <p>
                    <?php echo $es_global ? "Vista: <strong>Histórico Global</strong>" : "Vista: <strong>" . $meses_txt[$filtro_mes] . " " . $filtro_anio . "</strong>"; ?>
                </p>
            </div>
            
            <form action="" method="GET" class="filter-box">
                <select name="mes" class="form-select">
                    <?php foreach($meses_txt as $n => $t): ?>
                        <option value="<?php echo $n; ?>" <?php echo $n==$filtro_mes?'selected':''; ?>><?php echo $t; ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="anio" class="form-select">
                    <?php for($i=2024; $i<=date('Y'); $i++): ?>
                        <option value="<?php echo $i; ?>" <?php echo $i==$filtro_anio?'selected':''; ?>><?php echo $i; ?></option>
                    <?php endfor; ?>
                </select>
                <button type="submit" name="filtrar" value="1" class="btn btn-dark">Filtrar</button>
                <?php if(!$es_global): ?>
                    <a href="admin_reporte_ventas.php" class="btn btn-outline">Ver Todo</a>
                <?php endif; ?>
            </form>
            <a href="principal_admin.php" class="btn btn-outline">← Volver</a>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-lbl">Ingresos Netos</div>
                <div class="stat-val">$<?php echo number_format($kpis['ingresos_totales'], 2); ?></div>
            </div>
            <div class="stat-card" style="border-color: #3b82f6;">
                <div class="stat-lbl">Pedidos Totales</div>
                <div class="stat-val"><?php echo $kpis['total_pedidos']; ?></div>
            </div>
            <div class="stat-card" style="border-color: #10b981;">
                <div class="stat-lbl">Ticket Promedio</div>
                <div class="stat-val">$<?php echo number_format($kpis['pedido_promedio'] ?? 0, 0); ?></div>
            </div>
            <div class="stat-card" style="border-color: #ef4444;">
                <div class="stat-lbl">Cancelados</div>
                <div class="stat-val" style="color:#ef4444;"><?php echo $kpis['total_cancelados']; ?></div>
            </div>
        </div>

        <div class="charts-row">
            <div class="chart-container">
                <div class="chart-title">Distribución de Estatus</div>
                <canvas id="chartPie"></canvas>
            </div>
            
            <div class="chart-container">
                <div class="chart-title">Comportamiento de Ventas</div>
                <canvas id="chartLine"></canvas>
            </div>
        </div>

        <div class="chart-container" style="margin-bottom: 2rem;">
            <div class="chart-title">Top 5 Productos Más Vendidos</div>
            <canvas id="chartBar" height="100"></canvas>
        </div>

        <div class="table-container">
            <div style="padding:1rem; border-bottom:1px solid #eee;">
                <h3 style="margin:0; font-size:1.1rem; color:#2c3e50;">Detalle Top Productos</h3>
            </div>
            <table>
                <thead><tr><th>Producto</th><th>Unidades</th><th>Ingresos</th></tr></thead>
                <tbody>
                    <?php if(empty($top_productos)): ?>
                        <tr><td colspan="3" style="text-align:center; padding:2rem;">Sin datos.</td></tr>
                    <?php else: ?>
                        <?php foreach($top_productos as $p): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($p['nombre_producto']); ?></td>
                            <td><strong><?php echo $p['total_vendido']; ?></strong></td>
                            <td>$<?php echo number_format($p['ingresos_producto'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        Chart.register(ChartDataLabels);
        Chart.defaults.font.family = "'Segoe UI', sans-serif";
        Chart.defaults.color = '#64748b';

        // 1. CHART PIE (ESTATUS)
        new Chart(document.getElementById('chartPie'), {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($status_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($status_data); ?>,
                    backgroundColor: <?php echo json_encode($status_colors); ?>,
                    borderWidth: 0
                }]
            },
            options: {
                plugins: {
                    legend: { position: 'right' },
                    datalabels: {
                        color: '#fff', font: { weight: 'bold' },
                        formatter: (val, ctx) => {
                            let sum = 0;
                            let dataArr = ctx.chart.data.datasets[0].data;
                            dataArr.map(data => { sum += data; });
                            let perc = (val*100 / sum).toFixed(0) + "%";
                            return val > 0 ? perc : '';
                        }
                    }
                }
            }
        });

        // 2. CHART LINE (TIEMPO)
        new Chart(document.getElementById('chartLine'), {
            type: 'line',
            data: {
                labels: <?php echo json_encode($time_labels); ?>,
                datasets: [{
                    label: 'Ventas ($)',
                    data: <?php echo json_encode($time_data); ?>,
                    borderColor: '#2c3e50',
                    backgroundColor: 'rgba(44, 62, 80, 0.1)',
                    fill: true, tension: 0.3
                }]
            },
            options: {
                plugins: { legend: { display: false }, datalabels: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });

        // 3. CHART BAR (TOP PRODUCTOS)
        new Chart(document.getElementById('chartBar'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($nombres_prod); ?>,
                datasets: [{
                    label: 'Unidades',
                    data: <?php echo json_encode($cantidades_prod); ?>,
                    backgroundColor: '#10b981',
                    borderRadius: 4
                }]
            },
            options: {
                indexAxis: 'y',
                plugins: { 
                    legend: { display: false },
                    datalabels: { anchor: 'end', align: 'end', color:'#10b981', font:{weight:'bold'} }
                }
            }
        });
    </script>
</body>
</html>