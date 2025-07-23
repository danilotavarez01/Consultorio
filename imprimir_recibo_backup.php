<?php
require_once 'session_config.php';
session_start();
require_once 'config.php';

// Debug: Registrar información de la sesión
error_log("=== DEBUG IMPRIMIR RECIBO ===");
error_log("Session ID: " . session_id());
error_log("GET params: " . print_r($_GET, true));
error_log("Session vars: " . print_r($_SESSION, true));
error_log("ultimo_pago existe: " . (isset($_SESSION['ultimo_pago']) ? 'SI' : 'NO'));

// Verificar que el usuario esté logueado
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    error_log("Usuario no logueado en imprimir_recibo.php");
    echo "<!DOCTYPE html><html><head><title>Error de Sesión</title></head><body>";
    echo "<script>
        alert('Su sesión ha expirado. Por favor inicie sesión nuevamente.');
        if (window.opener) {
            window.opener.location.href = 'index.php';
        }
        window.close();
    </script>";
    echo "</body></html>";
    exit();
}

$pago = null;

// Intentar obtener datos del pago por diferentes métodos
// Método 1: Por parámetro GET (pago_id)
if (isset($_GET['pago_id']) && is_numeric($_GET['pago_id'])) {
    $pago_id = $_GET['pago_id'];
    error_log("Intentando obtener pago por ID: " . $pago_id);
    
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
        $pago_bd = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($pago_bd) {
            $pago = $pago_bd;
            error_log("Pago obtenido de BD: " . print_r($pago, true));
        }
    } catch (PDOException $e) {
        error_log("Error al obtener pago de BD: " . $e->getMessage());
    }
}

// Método 2: Por sesión (fallback)
if (!$pago && isset($_SESSION['ultimo_pago'])) {
    $pago = $_SESSION['ultimo_pago'];
    error_log("Usando pago de sesión: " . print_r($pago, true));
    
    // Si es un pago de sesión sin ID, obtener más datos de la BD si es posible
    if (isset($pago['pago_id']) && is_numeric($pago['pago_id']) && $pago['pago_id'] != 999) {
        try {
            $stmt = $conn->prepare("
                SELECT p.*, f.observaciones as factura_observaciones,
                       DATE_FORMAT(p.fecha_pago, '%d/%m/%Y %H:%i') as fecha_pago_formato
                FROM pagos p
                LEFT JOIN facturas f ON p.factura_id = f.id
                WHERE p.id = ?
            ");
            $stmt->execute([$pago['pago_id']]);
            $pago_detalle = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($pago_detalle) {
                // Combinar datos de sesión con datos de BD
                $pago = array_merge($pago, $pago_detalle);
            }
        } catch (PDOException $e) {
            error_log("Error al enriquecer datos de pago: " . $e->getMessage());
        }
    }
}

// Si aún no hay datos de pago, mostrar error
if (!$pago) {
    error_log("No hay datos de pago disponibles");
    echo "<!DOCTYPE html><html><head><title>Sin Datos de Pago</title></head><body>";
    echo "<script>
        alert('No hay información de pago para imprimir.\\n\\nPor favor registre un pago primero.');
        window.close();
    </script>";
    echo "<div style='padding: 20px; text-align: center;'>";
    echo "<h3>No hay información de pago para imprimir</h3>";
    echo "<p>Session ID: " . session_id() . "</p>";
    echo "<p>Usuario: " . htmlspecialchars($_SESSION['username'] ?? 'N/A') . "</p>";
    echo "<p>Parámetros GET: " . print_r($_GET, true) . "</p>";
    echo "<p>Variables de sesión disponibles:</p>";
    echo "<pre>" . print_r(array_keys($_SESSION), true) . "</pre>";
    echo "<button onclick='window.close()'>Cerrar</button>";
    echo "</div>";
    echo "</body></html>";
    exit();
}

error_log("Datos finales del pago para recibo: " . print_r($pago, true));

// Procesar los datos del pago para el recibo
try {
    // Si ya tenemos datos completos del pago de la BD, usarlos directamente
    if (isset($pago['fecha_pago_formato']) && isset($pago['paciente_nombre'])) {
        $pago_detalle = [
            'numero_factura' => $pago['numero_factura'] ?? 'N/A',
            'monto' => $pago['monto'] ?? 0,
            'metodo_pago' => $pago['metodo_pago'] ?? 'efectivo',
            'fecha_pago_formato' => $pago['fecha_pago_formato'] ?? date('d/m/Y H:i'),
            'paciente_nombre' => $pago['paciente_nombre'] ?? 'Paciente',
            'paciente_cedula' => $pago['paciente_cedula'] ?? '',
            'medico_nombre' => $pago['medico_nombre'] ?? 'Médico',
            'factura_observaciones' => $pago['observaciones'] ?? 'Recibo de pago'
        ];
    }
    // Si es un pago de prueba (ID 999), usar datos de sesión
    else if (isset($pago['pago_id']) && $pago['pago_id'] == 999) {
        $pago_detalle = [
            'numero_factura' => $pago['numero_factura'] ?? 'FAC-TEST',
            'monto' => $pago['monto'] ?? 0,
            'metodo_pago' => $pago['metodo_pago'] ?? 'efectivo',
            'fecha_pago_formato' => date('d/m/Y H:i'),
            'paciente_nombre' => $pago['paciente_nombre'] ?? 'Paciente',
            'paciente_cedula' => $pago['paciente_cedula'] ?? 'N/A',
            'medico_nombre' => $pago['medico_nombre'] ?? 'Dr. Médico',
            'factura_observaciones' => 'Pago de prueba'
        ];
    } else if (isset($pago['pago_id'])) {
        // Buscar pago real en la base de datos
        $stmt = $conn->prepare("
            SELECT p.*, f.observaciones as factura_observaciones,
                   DATE_FORMAT(p.fecha_pago, '%d/%m/%Y %H:%i') as fecha_pago_formato
            FROM pagos p
            LEFT JOIN facturas f ON p.factura_id = f.id
            WHERE p.id = ?
        ");
        $stmt->execute([$pago['pago_id']]);
        $pago_detalle = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$pago_detalle) {
            throw new Exception("Pago no encontrado en la base de datos");
        }
    } else {
        // Usar datos directos de la sesión como fallback
        $pago_detalle = [
            'numero_factura' => $pago['numero_factura'] ?? 'FAC-0000',
            'monto' => $pago['monto'] ?? 0,
            'metodo_pago' => $pago['metodo_pago'] ?? 'efectivo',
            'fecha_pago_formato' => date('d/m/Y H:i'),
            'paciente_nombre' => $pago['paciente_nombre'] ?? 'Paciente',
            'paciente_cedula' => $pago['paciente_cedula'] ?? 'N/A',
            'medico_nombre' => $pago['medico_nombre'] ?? 'Dr. Médico',
            'factura_observaciones' => 'Recibo de pago'
        ];
    }
    
    // Obtener configuración del consultorio
    $stmt = $conn->query("
        SELECT nombre_consultorio, direccion, telefono, email, 
               ruc, mensaje_recibo
        FROM configuracion 
        LIMIT 1
    ");
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    // En caso de error, usar datos de sesión y configuración por defecto
    error_log("Error en imprimir_recibo.php: " . $e->getMessage());
    $pago_detalle = [
        'numero_factura' => $pago['numero_factura'] ?? 'FAC-0000',
        'monto' => $pago['monto'] ?? 0,
        'metodo_pago' => $pago['metodo_pago'] ?? 'efectivo',
        'fecha_pago_formato' => date('d/m/Y H:i'),
        'paciente_nombre' => $pago['paciente_nombre'] ?? 'Paciente',
        'paciente_cedula' => $pago['paciente_cedula'] ?? '',
        'medico_nombre' => $pago['medico_nombre'] ?? 'Dr. Médico',
        'factura_observaciones' => 'Recibo de pago'
    ];
}

// Datos por defecto si no hay configuración
if (!$config) {
    $config = [
        'nombre_consultorio' => 'CONSULTORIO ODONTOLÓGICO',
        'direccion' => 'Dirección del Consultorio',
        'telefono' => '(555) 123-4567',
        'email' => 'info@consultorio.com',
        'ruc' => '12345678901',
        'mensaje_recibo' => 'Gracias por su visita y confianza'
    ];
}

// Asegurar que todos los campos del pago existen
if (!$pago_detalle) {
    $pago_detalle = [
        'numero_factura' => $pago['numero_factura'] ?? 'FAC-0000',
        'monto' => $pago['monto'] ?? 0,
        'metodo_pago' => $pago['metodo_pago'] ?? 'efectivo',
        'fecha_pago_formato' => date('d/m/Y H:i'),
        'paciente_nombre' => $pago['paciente_nombre'] ?? 'Paciente',
        'paciente_cedula' => $pago['paciente_cedula'] ?? '',
        'medico_nombre' => $pago['medico_nombre'] ?? 'Dr. Médico',
        'factura_observaciones' => 'Recibo de pago',
        'numero_referencia' => '',
        'observaciones' => ''
    ];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo de Pago - <?= htmlspecialchars($pago['numero_factura']) ?></title>
    <style>
        @media print {
            @page {
                size: 80mm auto;
                margin: 2mm;
            }
            body {
                margin: 0;
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.2;
            width: 80mm;
            margin: 0 auto;
            padding: 2mm;
            background: white;
        }

        .header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 5px;
            margin-bottom: 8px;
        }

        .titulo {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 2px;
        }

        .info-consultorio {
            font-size: 10px;
            margin-bottom: 1px;
        }

        .recibo-titulo {
            text-align: center;
            font-weight: bold;
            font-size: 13px;
            margin: 8px 0;
            text-decoration: underline;
        }

        .contenido {
            margin: 5px 0;
        }

        .fila {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
            align-items: flex-start;
        }

        .etiqueta {
            font-weight: bold;
            width: 40%;
        }

        .valor {
            width: 60%;
            text-align: right;
            word-wrap: break-word;
        }

        .separador {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }

        .total {
            font-weight: bold;
            font-size: 14px;
            text-align: center;
            padding: 3px 0;
            border: 1px solid #000;
            margin: 5px 0;
        }

        .footer {
            text-align: center;
            font-size: 10px;
            margin-top: 8px;
            border-top: 1px dashed #000;
            padding-top: 5px;
        }

        .botones {
            text-align: center;
            margin: 10px 0;
            padding: 10px;
            background: #f5f5f5;
            border: 1px solid #ddd;
        }

        .btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 8px 15px;
            margin: 0 5px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 12px;
        }

        .btn:hover {
            background: #0056b3;
        }

        .btn-secondary {
            background: #6c757d;
        }

        .btn-secondary:hover {
            background: #545b62;
        }

        /* Estilos específicos para impresión térmica */
        .termica {
            font-family: 'Courier New', monospace;
            font-size: 11px;
            line-height: 1.1;
        }
    </style>
</head>
<body class="termica">
    <!-- Botones de acción (no se imprimen) -->
    <div class="botones no-print">
        <button class="btn" onclick="window.print()">
            <i class="fas fa-print"></i> Imprimir
        </button>
        <button class="btn btn-secondary" onclick="window.close()">
            <i class="fas fa-times"></i> Cerrar
        </button>
    </div>

    <!-- Recibo -->
    <div class="recibo">
        <!-- Encabezado -->
        <div class="header">
            <div class="titulo"><?= strtoupper(htmlspecialchars($config['nombre_consultorio'])) ?></div>
            <?php if (!empty($config['direccion'])): ?>
                <div class="info-consultorio"><?= htmlspecialchars($config['direccion']) ?></div>
            <?php endif; ?>
            <?php if (!empty($config['telefono'])): ?>
                <div class="info-consultorio">Tel: <?= htmlspecialchars($config['telefono']) ?></div>
            <?php endif; ?>
            <?php if (!empty($config['email'])): ?>
                <div class="info-consultorio"><?= htmlspecialchars($config['email']) ?></div>
            <?php endif; ?>
            <?php if (!empty($config['ruc'])): ?>
                <div class="info-consultorio">RUC: <?= htmlspecialchars($config['ruc']) ?></div>
            <?php endif; ?>
        </div>

        <!-- Título del recibo -->
        <div class="recibo-titulo">RECIBO DE PAGO</div>

        <!-- Información del recibo -->
        <div class="contenido">
            <div class="fila">
                <span class="etiqueta">Recibo N°:</span>
                <span class="valor"><?= htmlspecialchars($pago['numero_factura']) ?>-P<?= str_pad($pago['pago_id'], 3, '0', STR_PAD_LEFT) ?></span>
            </div>
            
            <div class="fila">
                <span class="etiqueta">Fecha:</span>
                <span class="valor"><?= $pago_detalle ? $pago_detalle['fecha_pago_formato'] : date('d/m/Y H:i') ?></span>
            </div>

            <div class="separador"></div>

            <div class="fila">
                <span class="etiqueta">Paciente:</span>
                <span class="valor"><?= htmlspecialchars($pago['paciente_nombre'] ?? 'Paciente') ?></span>
            </div>

            <?php if (!empty($pago['paciente_cedula'] ?? '')): ?>
            <div class="fila">
                <span class="etiqueta">DNI:</span>
                <span class="valor"><?= htmlspecialchars($pago['paciente_cedula']) ?></span>
            </div>
            <?php endif; ?>

            <div class="fila">
                <span class="etiqueta">Médico:</span>
                <span class="valor"><?= htmlspecialchars($pago['medico_nombre'] ?? 'Médico') ?></span>
            </div>

            <div class="separador"></div>

            <div class="fila">
                <span class="etiqueta">Factura N°:</span>
                <span class="valor"><?= htmlspecialchars($pago['numero_factura'] ?? 'N/A') ?></span>
            </div>

            <div class="fila">
                <span class="etiqueta">Total Factura:</span>
                <span class="valor">$<?= number_format($pago['total_factura'] ?? 0, 2) ?></span>
            </div>

            <div class="fila">
                <span class="etiqueta">Método de Pago:</span>
                <span class="valor"><?= ucfirst(str_replace('_', ' ', $pago['metodo_pago'] ?? 'efectivo')) ?></span>
            </div>

            <?php if (!empty($pago_detalle['numero_referencia'])): ?>
            <div class="fila">
                <span class="etiqueta">Referencia:</span>
                <span class="valor"><?= htmlspecialchars($pago_detalle['numero_referencia']) ?></span>
            </div>
            <?php endif; ?>

            <div class="separador"></div>

            <!-- Monto del pago (destacado) -->
            <div class="total">
                MONTO PAGADO: $<?= number_format($pago['monto'] ?? 0, 2) ?>
            </div>

            <?php if (!empty($pago_detalle['observaciones'])): ?>
            <div class="separador"></div>
            <div class="fila">
                <span class="etiqueta">Observaciones:</span>
            </div>
            <div style="text-align: center; font-size: 10px; margin: 3px 0;">
                <?= htmlspecialchars($pago_detalle['observaciones']) ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Pie del recibo -->
        <div class="footer">
            <div style="margin-bottom: 3px;">
                <?= !empty($config['mensaje_recibo']) ? htmlspecialchars($config['mensaje_recibo']) : 'Gracias por su visita' ?>
            </div>
            <div style="font-size: 9px;">
                Recibo generado el <?= date('d/m/Y H:i:s') ?>
            </div>
        </div>
    </div>

    <script>
        // Auto-imprimir cuando se carga la página (opcional)
        window.onload = function() {
            // Limpiar variables de sesión después de que el recibo se haya cargado
            setTimeout(function() {
                fetch('clear_ultimo_pago.php', { 
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'action=clear_modal'
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Variables de pago limpiadas después de cargar recibo:', data);
                })
                .catch(error => {
                    console.warn('Advertencia al limpiar variables:', error);
                });
            }, 500);
            
            // Mostrar vista previa por 2 segundos antes de imprimir
            setTimeout(function() {
                if (confirm('¿Proceder con la impresión del recibo?')) {
                    window.print();
                }
            }, 1000);
        };

        // Cerrar ventana después de imprimir
        window.onafterprint = function() {
            setTimeout(function() {
                window.close();
            }, 1000);
        };
    </script>
</body>
</html>

<?php
// Limpiar la información del último pago después de mostrar el recibo
unset($_SESSION['ultimo_pago']);
?>
