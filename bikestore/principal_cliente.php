
<?php

session_start();
if ($_SESSION['rol'] != 'cliente') {
    header("Location: login.php");
    exit;
}
echo "<h1>Bienvenido Cliente, ".$_SESSION['usuario']."</h1>";
echo "<a href='logout.php'>Cerrar sesión</a>";
?>
