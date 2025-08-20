<?php
// Script para restaurar los archivos del odontograma a su estado anterior a las modificaciones
// Creado: <?php echo date('Y-m-d H:i:s'); ?>

// Comprobar si hay archivos de respaldo disponibles
$archivos_backup = [
    'nueva_consulta.php.bak' => 'nueva_consulta.php',
    'forzar_odontograma_simple_nuevo.php.bak' => 'forzar_odontograma_simple_nuevo.php',
    'forzar_odontograma_corregido.php.bak' => 'forzar_odontograma_corregido.php',
    'odontograma_svg_mejorado.php.bak' => 'odontograma_svg_mejorado.php', // Versión original
    'odontograma_svg_mejorado.php.bak2' => 'odontograma_svg_mejorado.php', // Versión con orden de dientes corregido
    'odontograma_svg_mejorado.php.bak3' => 'odontograma_svg_mejorado.php', // Versión con numeración visual corregida
    'odontograma_svg_mejorado.php.bak4' => 'odontograma_svg_mejorado.php', // Versión antes de selección múltiple
    'odontograma_svg_mejorado.php.bak5' => 'odontograma_svg_mejorado.php', // Versión con la corrección de selección múltiple
    'odontograma_svg_mejorado.php.bak6' => 'odontograma_svg_mejorado.php'  // Versión con corrección completa para selección múltiple
];

$mensaje = '';

// Verificar si se está solicitando una restauración
if (isset($_GET['restaurar']) && $_GET['restaurar'] == '1') {
    // Intentar restaurar cada archivo
    foreach ($archivos_backup as $origen => $destino) {
        if (file_exists($origen)) {
            if (copy($origen, $destino)) {
                $mensaje .= "Archivo $destino restaurado correctamente.<br>";
            } else {
                $mensaje .= "Error al restaurar $destino.<br>";
            }
        } else {
            $mensaje .= "El archivo de respaldo $origen no existe.<br>";
        }
    }
    
    $mensaje .= "<p>Restauración completada. Ahora debe modificar 'nueva_consulta.php' para volver a incluir 
                'forzar_odontograma_simple_nuevo.php' en lugar de 'forzar_odontograma_corregido.php'.</p>";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurar Odontograma</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header bg-warning">
                        <h4>Punto de Restauración del Odontograma</h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($mensaje)): ?>
                        <div class="alert alert-info">
                            <?php echo $mensaje; ?>
                        </div>
                        <?php endif; ?>
                        
                        <h5>Información sobre los archivos de respaldo:</h5>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Archivo de respaldo</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($archivos_backup as $origen => $destino): ?>
                                <tr>
                                    <td><?php echo $origen; ?></td>
                                    <td>
                                        <?php if (file_exists($origen)): ?>
                                            <span class="badge badge-success">Disponible</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">No encontrado</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        
                        <?php if (!isset($_GET['restaurar'])): ?>
                        <div class="alert alert-warning">
                            <p><strong>¿Está seguro de que desea restaurar los archivos del odontograma a su estado anterior?</strong></p>
                            <p>Esta acción revertirá los cambios realizados para solucionar el problema del bucle de carga del odontograma.</p>
                            <p>Después de la restauración, deberá modificar manualmente el archivo 'nueva_consulta.php' para volver a incluir 'forzar_odontograma_simple_nuevo.php'.</p>
                        </div>
                        
                        <div class="text-center">
                            <a href="?restaurar=1" class="btn btn-danger">Sí, restaurar los archivos</a>
                            <a href="index.php" class="btn btn-secondary ml-2">Cancelar</a>
                        </div>
                        <?php else: ?>
                        <div class="text-center mt-3">
                            <a href="index.php" class="btn btn-primary">Volver a la página principal</a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="assets/js/jquery-3.5.1.slim.min.js"></script>
    <script src="assets/js/popper-2.5.4.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
</body>
</html>


