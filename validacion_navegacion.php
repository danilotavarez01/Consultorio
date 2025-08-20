<?php
/**
 * Script de Validación de Navegación
 * Valida que todas las opciones del menú funcionen correctamente sin desloguear al usuario
 */
require_once 'session_config.php';
session_start();
require_once "permissions.php";

// Verificar que el usuario esté logueado
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    echo "<!DOCTYPE html><html><body>";
    echo "<h2>❌ Error: Usuario no autenticado</h2>";
    echo "<p>Debe <a href='login.php'>iniciar sesión</a> para usar este validador.</p>";
    echo "</body></html>";
    exit;
}

// Lista de páginas del menú para validar
$paginas_menu = [
    'index.php' => 'Dashboard Principal',
    'pacientes.php' => 'Gestión de Pacientes',
    'turnos.php' => 'Gestión de Turnos',
    'Citas.php' => 'Gestión de Citas',
    'recetas.php' => 'Gestión de Recetas',
    'enfermedades.php' => 'Catálogo de Enfermedades',
    'procedimientos.php' => 'Gestión de Procedimientos',
    'usuarios.php' => 'Gestión de Usuarios',
    'gestionar_doctores.php' => 'Gestión de Médicos',
    'receptionist_permissions.php' => 'Permisos de Recepcionista',
    'user_permissions.php' => 'Permisos de Usuario',
    'configuracion.php' => 'Configuración del Sistema',
    'facturacion.php' => 'Sistema de Facturación',
    'reportes_facturacion.php' => 'Reportes de Facturación'
];

// Validar cada página
$resultados = [];
foreach ($paginas_menu as $archivo => $nombre) {
    $resultado = [
        'nombre' => $nombre,
        'archivo' => $archivo,
        'existe' => file_exists($archivo),
        'session_config' => false,
        'permisos_include' => false,
        'estructura_correcta' => false
    ];
    
    if ($resultado['existe']) {
        $contenido = file_get_contents($archivo);
        
        // Verificar que incluya session_config.php
        $resultado['session_config'] = strpos($contenido, "require_once 'session_config.php';") !== false;
        
        // Verificar que incluya permissions.php
        $resultado['permisos_include'] = strpos($contenido, 'require_once "permissions.php";') !== false;
        
        // Verificar estructura correcta (session_config antes de session_start)
        $pos_session_config = strpos($contenido, "require_once 'session_config.php';");
        $pos_session_start = strpos($contenido, 'session_start();');
        $resultado['estructura_correcta'] = ($pos_session_config !== false && $pos_session_start !== false && $pos_session_config < $pos_session_start);
    }
    
    $resultados[] = $resultado;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Validación de Navegación - Consultorio</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/fontawesome.min.css">
    <link rel="stylesheet" href="css/dark-mode.css">
    <style>
        .status-ok { color: #28a745; }
        .status-error { color: #dc3545; }
        .status-warning { color: #ffc107; }
        .test-result { margin: 5px 0; padding: 10px; border-left: 4px solid #ddd; }
        .test-ok { border-left-color: #28a745; background-color: #d4edda; }
        .test-error { border-left-color: #dc3545; background-color: #f8d7da; }
        .test-warning { border-left-color: #ffc107; background-color: #fff3cd; }
    </style>
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include "sidebar.php"; ?>
        
        <div class="col-md-10 main-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-check-circle"></i> Validación de Navegación</h2>
                <div>
                    <button onclick="testNavigation()" class="btn btn-primary">
                        <i class="fas fa-play"></i> Probar Navegación
                    </button>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-home"></i> Volver al Dashboard
                    </a>
                </div>
            </div>

            <!-- Estado de la sesión -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-user-shield"></i> Estado de la Sesión</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Usuario:</strong> <?php echo htmlspecialchars($_SESSION['username'] ?? 'No definido'); ?></p>
                            <p><strong>Nombre:</strong> <?php echo htmlspecialchars($_SESSION['nombre'] ?? 'No definido'); ?></p>
                            <p><strong>Rol:</strong> <?php echo htmlspecialchars($_SESSION['rol'] ?? 'No definido'); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Session ID:</strong> <code><?php echo session_id(); ?></code></p>
                            <p><strong>Última actividad:</strong> <?php echo isset($_SESSION['last_activity']) ? date('Y-m-d H:i:s', $_SESSION['last_activity']) : 'No definida'; ?></p>
                            <p><strong>Estado:</strong> <span class="status-ok"><i class="fas fa-check-circle"></i> Activa</span></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resultados de validación -->
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-list-check"></i> Validación de Archivos del Menú</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($resultados as $resultado): ?>
                        <div class="test-result <?php echo ($resultado['existe'] && $resultado['session_config'] && $resultado['permisos_include'] && $resultado['estructura_correcta']) ? 'test-ok' : 'test-error'; ?>">
                            <h6>
                                <?php if ($resultado['existe'] && $resultado['session_config'] && $resultado['permisos_include'] && $resultado['estructura_correcta']): ?>
                                    <i class="fas fa-check-circle status-ok"></i>
                                <?php else: ?>
                                    <i class="fas fa-times-circle status-error"></i>
                                <?php endif; ?>
                                <?php echo $resultado['nombre']; ?>
                            </h6>
                            <div class="ml-4">
                                <p class="mb-1">
                                    <strong>Archivo:</strong> 
                                    <?php if ($resultado['existe']): ?>
                                        <span class="status-ok"><i class="fas fa-check"></i> <?php echo $resultado['archivo']; ?></span>
                                    <?php else: ?>
                                        <span class="status-error"><i class="fas fa-times"></i> <?php echo $resultado['archivo']; ?> (No existe)</span>
                                    <?php endif; ?>
                                </p>
                                
                                <?php if ($resultado['existe']): ?>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <?php if ($resultado['session_config']): ?>
                                                <span class="status-ok"><i class="fas fa-check"></i> Session Config</span>
                                            <?php else: ?>
                                                <span class="status-error"><i class="fas fa-times"></i> Session Config</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-4">
                                            <?php if ($resultado['permisos_include']): ?>
                                                <span class="status-ok"><i class="fas fa-check"></i> Permisos Include</span>
                                            <?php else: ?>
                                                <span class="status-error"><i class="fas fa-times"></i> Permisos Include</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-4">
                                            <?php if ($resultado['estructura_correcta']): ?>
                                                <span class="status-ok"><i class="fas fa-check"></i> Estructura Correcta</span>
                                            <?php else: ?>
                                                <span class="status-error"><i class="fas fa-times"></i> Estructura Incorrecta</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Test de navegación interactivo -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5><i class="fas fa-mouse-pointer"></i> Prueba de Navegación Interactiva</h5>
                </div>
                <div class="card-body">
                    <p>Utiliza los botones de abajo para probar la navegación entre diferentes módulos del sistema. El test abrirá cada página en una ventana nueva para verificar que no se produzcan deslogueos.</p>
                    
                    <div id="test-results" class="mt-3"></div>
                    
                    <div class="row mt-3">
                        <?php foreach ($paginas_menu as $archivo => $nombre): ?>
                            <?php if (file_exists($archivo)): ?>
                                <div class="col-md-4 mb-2">
                                    <button onclick="testPage('<?php echo $archivo; ?>', '<?php echo addslashes($nombre); ?>')" 
                                            class="btn btn-outline-primary btn-sm btn-block">
                                        <i class="fas fa-external-link-alt"></i> <?php echo $nombre; ?>
                                    </button>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function testPage(archivo, nombre) {
    const resultDiv = document.getElementById('test-results');
    const testId = 'test-' + archivo.replace(/[^a-zA-Z0-9]/g, '');
    
    // Crear o actualizar resultado
    let testResult = document.getElementById(testId);
    if (!testResult) {
        testResult = document.createElement('div');
        testResult.id = testId;
        testResult.className = 'alert alert-info';
        resultDiv.appendChild(testResult);
    }
    
    testResult.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Probando: ${nombre}...`;
    
    // Abrir página en ventana nueva
    const ventana = window.open(archivo, '_blank', 'width=800,height=600');
    
    // Esperar un momento y verificar si la ventana sigue abierta
    setTimeout(() => {
        try {
            if (ventana && !ventana.closed) {
                testResult.className = 'alert alert-success';
                testResult.innerHTML = `<i class="fas fa-check-circle"></i> ${nombre}: ✅ Navegación exitosa`;
                ventana.close();
            } else {
                testResult.className = 'alert alert-warning';
                testResult.innerHTML = `<i class="fas fa-exclamation-triangle"></i> ${nombre}: ⚠️ Ventana cerrada o bloqueada`;
            }
        } catch (e) {
            testResult.className = 'alert alert-danger';
            testResult.innerHTML = `<i class="fas fa-times-circle"></i> ${nombre}: ❌ Error en la navegación`;
        }
    }, 3000);
}

function testNavigation() {
    const pages = <?php echo json_encode(array_keys($paginas_menu)); ?>;
    const names = <?php echo json_encode(array_values($paginas_menu)); ?>;
    
    document.getElementById('test-results').innerHTML = '<h6>Iniciando prueba de navegación...</h6>';
    
    pages.forEach((page, index) => {
        setTimeout(() => {
            testPage(page, names[index]);
        }, index * 1000); // Espaciar las pruebas
    });
}
</script>

<script src="js/theme-manager.js"></script>
</body>
</html>

