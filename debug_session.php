<?php
require_once 'session_config.php';
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Debug de Sesión</title>
</head>
<body>
<h2>Debug de Variables de Sesión</h2>

<h3>Estado de la sesión:</h3>
<pre>
Session ID: <?= session_id() ?>
Session Status: <?= session_status() ?>
Logged in: <?= isset($_SESSION['loggedin']) ? ($_SESSION['loggedin'] ? 'Sí' : 'No') : 'No establecido' ?>
</pre>

<h3>Variable 'ultimo_pago':</h3>
<pre>
<?php
if (isset($_SESSION['ultimo_pago'])) {
    echo "Existe:\n";
    print_r($_SESSION['ultimo_pago']);
} else {
    echo "No existe la variable 'ultimo_pago'";
}
?>
</pre>

<h3>Variable 'show_print_modal':</h3>
<pre>
<?php
if (isset($_SESSION['show_print_modal'])) {
    echo "Existe: " . ($_SESSION['show_print_modal'] ? 'true' : 'false');
} else {
    echo "No existe la variable 'show_print_modal'";
}
?>
</pre>

<h3>Todas las variables de sesión:</h3>
<pre>
<?php print_r($_SESSION); ?>
</pre>

<h3>Acciones:</h3>
<p>
    <a href="facturacion.php">Volver a Facturación</a> |
    <a href="javascript:window.close()">Cerrar Ventana</a>
</p>

</body>
</html>
