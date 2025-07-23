<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test jQuery - Modal</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    
    <!-- jQuery cargado en el head -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h2>🔧 Test de jQuery y Modal</h2>
        <hr>
        
        <div class="alert alert-info">
            <h4>Estado de jQuery:</h4>
            <p id="jquery-status">Verificando...</p>
        </div>
        
        <button type="button" class="btn btn-success" onclick="mostrarModalTest()">
            <i class="fas fa-play mr-2"></i>Probar Modal
        </button>
        
        <div class="mt-3">
            <a href="facturacion.php" class="btn btn-primary">
                <i class="fas fa-arrow-left mr-2"></i>Volver a Facturación
            </a>
        </div>
    </div>

    <!-- Modal de Prueba -->
    <div class="modal fade" id="modalTest" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h4 class="modal-title">
                        <i class="fas fa-check-circle mr-2"></i>Test Exitoso
                    </h4>
                </div>
                <div class="modal-body text-center">
                    <p><strong>✅ jQuery está funcionando correctamente!</strong></p>
                    <p>Si ves este modal, significa que no hay problemas con jQuery.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Test inmediato en el DOM
        console.log('=== TEST DE JQUERY ===');
        
        // Verificar jQuery
        if (typeof $ !== 'undefined') {
            console.log('✅ jQuery está disponible');
            $('#jquery-status').html('✅ <strong style="color: green;">jQuery cargado correctamente</strong><br>Versión: ' + $.fn.jquery);
        } else {
            console.error('❌ jQuery NO está disponible');
            document.getElementById('jquery-status').innerHTML = '❌ <strong style="color: red;">ERROR: jQuery no está cargado</strong>';
        }
        
        // Test del DOM ready
        $(document).ready(function() {
            console.log('✅ $(document).ready() funcionando');
            
            // Verificar Bootstrap modal
            if (typeof $.fn.modal !== 'undefined') {
                console.log('✅ Bootstrap Modal disponible');
            } else {
                console.error('❌ Bootstrap Modal NO disponible');
            }
        });
        
        function mostrarModalTest() {
            console.log('🧪 Probando modal...');
            
            if (typeof $ === 'undefined') {
                alert('❌ ERROR: jQuery no está disponible');
                return;
            }
            
            if ($('#modalTest').length === 0) {
                alert('❌ ERROR: Modal no encontrado en el DOM');
                return;
            }
            
            console.log('✅ Mostrando modal de prueba...');
            $('#modalTest').modal('show');
        }
    </script>
</body>
</html>
