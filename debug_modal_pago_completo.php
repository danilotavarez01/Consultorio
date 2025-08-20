<?php
require_once 'session_config.php';
session_start();
require_once 'config.php';
require_once 'permissions.php';

// Verificar que el usuario esté logueado
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin'] || !isset($_SESSION['id'])) {
    header('Location: index.php');
    exit();
}

// NUEVA FUNCIONALIDAD: Simular pago real si se pasa el parámetro
if (isset($_GET['simular_pago']) && $_GET['simular_pago'] == '1') {
    // Simular datos de un pago exitoso
    $_SESSION['ultimo_pago'] = [
        'factura_id' => 999,
        'numero_factura' => 'FAC-SIMULATION-' . date('YmdHis'),
        'paciente_nombre' => 'Paciente Simulado',
        'monto' => 250.00,
        'metodo_pago' => 'efectivo',
        'fecha' => date('Y-m-d H:i:s'),
        'usuario_id' => $_SESSION['id']
    ];
    
    $_SESSION['show_print_modal'] = true;
    
    // Redirigir de vuelta a facturación para mostrar el modal
    echo "<script>
        alert('✅ Pago simulado exitosamente!\\n\\nSerás redirigido a la página de facturación donde deberías ver el modal automáticamente.');
        window.location.href = 'facturacion.php';
    </script>";
    exit();
}

echo "<html><head><title>Diagnóstico Modal de Pago</title>";
echo "<link rel='stylesheet' href='assets/css/bootstrap.min.css'>";
echo "<link rel='stylesheet' href='assets/css/fontawesome.min.css'>";
echo "</head><body class='p-4'>";

echo "<div class='container'>";
echo "<h2><i class='fas fa-bug mr-2'></i>Diagnóstico del Modal de Pago Exitoso</h2>";

// Verificar variables de sesión
echo "<div class='card mt-4'>";
echo "<div class='card-header bg-primary text-white'>";
echo "<h5 class='mb-0'><i class='fas fa-search mr-2'></i>Estado de Variables de Sesión</h5>";
echo "</div>";
echo "<div class='card-body'>";

echo "<h6>📋 Variables de Sesión Actuales:</h6>";
echo "<pre class='bg-light p-3 border rounded'>";

if (isset($_SESSION['ultimo_pago'])) {
    echo "✅ \$_SESSION['ultimo_pago'] EXISTE:\n";
    print_r($_SESSION['ultimo_pago']);
} else {
    echo "❌ \$_SESSION['ultimo_pago'] NO EXISTE\n";
}

echo "\n";

if (isset($_SESSION['show_print_modal'])) {
    echo "✅ \$_SESSION['show_print_modal'] = " . ($_SESSION['show_print_modal'] ? 'TRUE' : 'FALSE') . "\n";
} else {
    echo "❌ \$_SESSION['show_print_modal'] NO EXISTE\n";
}

echo "\n📊 Todas las variables de sesión:\n";
foreach ($_SESSION as $key => $value) {
    if (is_array($value)) {
        echo "$key => [ARRAY]\n";
    } else {
        echo "$key => " . (is_bool($value) ? ($value ? 'TRUE' : 'FALSE') : $value) . "\n";
    }
}

echo "</pre>";
echo "</div>";
echo "</div>";

// Botón para simular un pago exitoso
echo "<div class='card mt-4'>";
echo "<div class='card-header bg-success text-white'>";
echo "<h5 class='mb-0'><i class='fas fa-flask mr-2'></i>Simulador de Pago Exitoso</h5>";
echo "</div>";
echo "<div class='card-body'>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simular_pago'])) {
    // Simular un pago exitoso
    $_SESSION['ultimo_pago'] = [
        'pago_id' => 999,
        'factura_id' => 1,
        'monto' => 150.00,
        'metodo_pago' => 'efectivo',
        'numero_factura' => 'FAC-TEST',
        'paciente_nombre' => 'Paciente de Prueba'
    ];
    $_SESSION['show_print_modal'] = true;
    
    echo "<div class='alert alert-success'>";
    echo "<i class='fas fa-check mr-2'></i>¡Pago simulado correctamente!";
    echo "</div>";
    
    echo "<script>";
    echo "setTimeout(function() { window.location.href = 'facturacion.php'; }, 2000);";
    echo "</script>";
    
    echo "<p>Redirigiendo a facturación en 2 segundos para ver el modal...</p>";
} else {
    echo "<p>Haz clic en el botón de abajo para simular un pago exitoso y ver si aparece el modal:</p>";
    echo "<form method='POST'>";
    echo "<button type='submit' name='simular_pago' class='btn btn-success btn-lg'>";
    echo "<i class='fas fa-play mr-2'></i>Simular Pago Exitoso";
    echo "</button>";
    echo "</form>";
}

echo "</div>";
echo "</div>";

// Verificar condiciones del modal
echo "<div class='card mt-4'>";
echo "<div class='card-header bg-warning text-dark'>";
echo "<h5 class='mb-0'><i class='fas fa-checklist mr-2'></i>Condiciones para Mostrar Modal</h5>";
echo "</div>";
echo "<div class='card-body'>";

echo "<h6>🎯 Condiciones necesarias para que aparezca el modal:</h6>";
echo "<ul class='list-group list-group-flush'>";

$condicion1 = isset($_SESSION['show_print_modal']) && $_SESSION['show_print_modal'] === true;
$condicion2 = isset($_SESSION['ultimo_pago']);

echo "<li class='list-group-item d-flex justify-content-between align-items-center'>";
echo "Variable \$_SESSION['show_print_modal'] = true";
echo "<span class='badge badge-" . ($condicion1 ? "success" : "danger") . "'>";
echo $condicion1 ? "✅ OK" : "❌ FALLA";
echo "</span>";
echo "</li>";

echo "<li class='list-group-item d-flex justify-content-between align-items-center'>";
echo "Variable \$_SESSION['ultimo_pago'] existe";
echo "<span class='badge badge-" . ($condicion2 ? "success" : "danger") . "'>";
echo $condicion2 ? "✅ OK" : "❌ FALLA";
echo "</span>";
echo "</li>";

$todasOK = $condicion1 && $condicion2;

echo "<li class='list-group-item d-flex justify-content-between align-items-center bg-light'>";
echo "<strong>RESULTADO FINAL</strong>";
echo "<span class='badge badge-" . ($todasOK ? "success" : "danger") . " badge-lg'>";
echo $todasOK ? "✅ MODAL DEBERÍA APARECER" : "❌ MODAL NO APARECERÁ";
echo "</span>";
echo "</li>";

echo "</ul>";
echo "</div>";
echo "</div>";

// Botones de acción
echo "<div class='card mt-4'>";
echo "<div class='card-header bg-info text-white'>";
echo "<h5 class='mb-0'><i class='fas fa-tools mr-2'></i>Acciones</h5>";
echo "</div>";
echo "<div class='card-body'>";

echo "<div class='btn-group mr-2'>";
echo "<a href='facturacion.php' class='btn btn-primary'>";
echo "<i class='fas fa-arrow-left mr-2'></i>Volver a Facturación";
echo "</a>";
echo "</div>";

echo "<div class='btn-group mr-2'>";
echo "<a href='clear_ultimo_pago.php' class='btn btn-warning'>";
echo "<i class='fas fa-trash mr-2'></i>Limpiar Variables";
echo "</a>";
echo "</div>";

echo "<div class='btn-group'>";
echo "<button onclick='location.reload()' class='btn btn-secondary'>";
echo "<i class='fas fa-sync mr-2'></i>Actualizar Diagnóstico";
echo "</button>";
echo "</div>";

echo "</div>";
echo "</div>";

echo "</div>";

echo "</body></html>";
?>

