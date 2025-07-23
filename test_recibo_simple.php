<?php
// Test simple de impresión de recibo - Sin validaciones de sesión
require_once 'config.php';

// Obtener datos del pago
$pago = null;
$pago_id = $_GET['pago_id'] ?? null;

if ($pago_id && is_numeric($pago_id)) {
    try {
        $stmt = $conn->prepare("
            SELECT p.id as pago_id, p.monto, p.metodo_pago, p.observaciones,
                   f.numero_factura, f.total as total_factura,
                   CONCAT(pac.nombre, ' ', pac.apellido) as paciente_nombre,
                   pac.dni as paciente_cedula,
                   u.nombre as medico_nombre,
                   DATE_FORMAT(p.fecha_pago, '%d/%m/%Y %H:%i') as fecha_pago_formato
            FROM pagos p
            LEFT JOIN facturas f ON p.factura_id = f.id
            LEFT JOIN pacientes pac ON f.paciente_id = pac.id
            LEFT JOIN usuarios u ON f.medico_id = u.id
            WHERE p.id = ?
        ");
        $stmt->execute([$pago_id]);
        $pago = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = "Error BD: " . $e->getMessage();
    }
}

// Si no hay pago específico, crear uno de prueba
if (!$pago) {
    $pago = [
        'pago_id' => 'TEST-999',
        'numero_factura' => 'F-TEST-' . date('YmdHis'),
        'paciente_nombre' => 'Paciente de Prueba',
        'paciente_cedula' => '12345678',
        'monto' => '150.00',
        'metodo_pago' => 'efectivo',
        'fecha_pago_formato' => date('d/m/Y H:i'),
        'medico_nombre' => 'Dr. Test',
        'total_factura' => '150.00'
    ];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo de Pago - Test</title>
    <style>
        /* Estilos base para pantalla */
        body { 
            font-family: 'Courier New', monospace; 
            font-size: 12px; 
            margin: 0; 
            padding: 20px; 
            background: white; 
        }
        .recibo { 
            max-width: 300px; 
            margin: 0 auto; 
            border: 2px solid #000; 
            padding: 15px; 
            background: white;
        }
        .header { 
            text-align: center; 
            border-bottom: 1px dashed #000; 
            margin-bottom: 15px; 
            padding-bottom: 10px; 
            font-weight: bold;
        }
        .linea { 
            margin: 8px 0; 
            display: flex;
            justify-content: space-between;
        }
        .label { font-weight: bold; }
        .valor { text-align: right; }
        .total { 
            font-weight: bold; 
            border-top: 2px solid #000; 
            margin-top: 15px; 
            padding-top: 10px; 
            text-align: center;
            font-size: 14px;
        }
        .botones {
            text-align: center;
            margin: 20px 0;
            background: #f0f0f0;
            padding: 15px;
            border-radius: 5px;
        }
        .btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            margin: 5px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn:hover { background: #0056b3; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #1e7e34; }
        .btn-secondary { background: #6c757d; }
        .btn-secondary:hover { background: #545b62; }
        
        /* Estilos para impresión */
        @media print {
            .no-print { display: none !important; }
            
            @page {
                size: 80mm auto;
                margin: 0;
            }
            
            body {
                font-size: 10px;
                line-height: 1.2;
                width: 80mm;
                margin: 0;
                padding: 2mm;
            }
            
            .recibo {
                width: 100%;
                max-width: none;
                border: none;
                padding: 0;
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <div class="botones no-print">
        <h3>🖨️ Test de Impresión de Recibo</h3>
        <p><strong>Pago ID:</strong> <?= htmlspecialchars($pago['pago_id']) ?></p>
        <button class="btn btn-success" onclick="window.print()">
            <i class="fas fa-print"></i> Imprimir Recibo
        </button>
        <button class="btn btn-secondary" onclick="window.close()">
            <i class="fas fa-times"></i> Cerrar
        </button>
        <button class="btn" onclick="location.reload()">
            <i class="fas fa-sync"></i> Recargar
        </button>
    </div>

    <div class="recibo">
        <div class="header">
            <div style="font-size: 16px; margin-bottom: 5px;">CONSULTORIO ODONTOLÓGICO</div>
            <div style="font-size: 12px;">RECIBO DE PAGO</div>
            <div style="font-size: 10px; margin-top: 5px;">
                <?= date('d/m/Y H:i:s') ?>
            </div>
        </div>

        <div class="linea">
            <span class="label">Recibo #:</span>
            <span class="valor"><?= htmlspecialchars($pago['pago_id']) ?></span>
        </div>

        <div class="linea">
            <span class="label">Factura:</span>
            <span class="valor"><?= htmlspecialchars($pago['numero_factura']) ?></span>
        </div>

        <div class="linea">
            <span class="label">Paciente:</span>
            <span class="valor"><?= htmlspecialchars($pago['paciente_nombre']) ?></span>
        </div>

        <?php if (!empty($pago['paciente_cedula'])): ?>
        <div class="linea">
            <span class="label">Cédula:</span>
            <span class="valor"><?= htmlspecialchars($pago['paciente_cedula']) ?></span>
        </div>
        <?php endif; ?>

        <div class="linea">
            <span class="label">Médico:</span>
            <span class="valor"><?= htmlspecialchars($pago['medico_nombre'] ?? 'N/A') ?></span>
        </div>

        <div class="linea">
            <span class="label">Fecha:</span>
            <span class="valor"><?= htmlspecialchars($pago['fecha_pago_formato']) ?></span>
        </div>

        <div class="linea">
            <span class="label">Método:</span>
            <span class="valor"><?= ucfirst(str_replace('_', ' ', $pago['metodo_pago'])) ?></span>
        </div>

        <div class="total">
            <div>MONTO PAGADO</div>
            <div style="font-size: 18px; margin-top: 5px;">
                $<?= number_format(floatval($pago['monto']), 2) ?>
            </div>
        </div>

        <div style="text-align: center; margin-top: 15px; font-size: 10px; border-top: 1px dashed #000; padding-top: 10px;">
            <div>¡Gracias por su pago!</div>
            <div style="margin-top: 5px;">Conserve este recibo</div>
        </div>
    </div>

    <div class="no-print" style="margin-top: 30px; text-align: center; background: #e9ecef; padding: 15px; border-radius: 5px;">
        <h4>📋 Información de Debug</h4>
        <p><strong>URL:</strong> <?= $_SERVER['REQUEST_URI'] ?></p>
        <p><strong>Navegador:</strong> <span style="font-size: 10px;"><?= $_SERVER['HTTP_USER_AGENT'] ?? 'N/A' ?></span></p>
        <p><strong>Datos del pago:</strong></p>
        <pre style="text-align: left; background: white; padding: 10px; border-radius: 3px; font-size: 10px;">
<?= print_r($pago, true) ?>
        </pre>
    </div>

    <script>
        console.log('🖨️ Recibo de pago cargado:', <?= json_encode($pago) ?>);
        
        // Auto-focus en la ventana para facilitar impresión
        window.focus();
        
        // Función mejorada de impresión
        function imprimirAhora() {
            console.log('🚀 Iniciando impresión...');
            
            try {
                // Intentar imprimir directamente
                window.print();
                console.log('✅ Comando de impresión ejecutado');
            } catch (error) {
                console.error('❌ Error al imprimir:', error);
                alert('Error al imprimir: ' + error.message);
            }
        }
        
        // Auto-imprimir si se especifica en la URL
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('auto_print') === '1') {
            console.log('🔄 Auto-impresión activada');
            setTimeout(imprimirAhora, 1000); // Delay para que cargue completamente
        }
        
        // Handlers para teclas rápidas
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                imprimirAhora();
            }
            if (e.key === 'Escape') {
                window.close();
            }
        });
        
        console.log('✅ Recibo listo para imprimir');
        console.log('💡 Presione Ctrl+P para imprimir o Escape para cerrar');
    </script>
</body>
</html>
