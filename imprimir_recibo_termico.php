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

// Obtener datos del último pago
$ultimo_pago = $_SESSION['ultimo_pago'] ?? null;

if (!$ultimo_pago) {
    echo "<script>alert('No hay datos de pago para imprimir.'); window.close();</script>";
    exit();
}

// Obtener datos adicionales si es necesario
try {
    // Obtener datos del consultorio desde configuración
    $stmt = $conn->prepare("SELECT nombre_consultorio, direccion, telefono, ruc FROM configuracion LIMIT 1");
    $stmt->execute();
    $consultorio = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Si no hay configuración, usar valores por defecto
    if (!$consultorio) {
        $consultorio = [
            'nombre_consultorio' => 'CONSULTORIO ODONTOLÓGICO',
            'direccion' => 'Dirección del Consultorio',
            'telefono' => 'Teléfono: (000) 000-0000',
            'ruc' => 'RUC: 00000000000'
        ];
    }
    
    // Obtener detalles del pago
    if (isset($ultimo_pago['factura_id'])) {
        $stmt = $conn->prepare("
            SELECT f.*, 
                   CONCAT(p.nombre, ' ', p.apellido) as paciente_nombre,
                   p.telefono as paciente_telefono,
                   p.dni as paciente_cedula,
                   u.nombre as medico_nombre
            FROM facturas f
            LEFT JOIN pacientes p ON f.paciente_id = p.id
            LEFT JOIN usuarios u ON f.medico_id = u.id  
            WHERE f.id = ?
        ");
        $stmt->execute([$ultimo_pago['factura_id']]);
        $factura_info = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
} catch (PDOException $e) {
    // En caso de error, usar datos básicos
    $consultorio = [
        'nombre_consultorio' => 'CONSULTORIO ODONTOLÓGICO',
        'direccion' => 'Dirección del Consultorio',
        'telefono' => 'Teléfono: (000) 000-0000',
        'ruc' => 'RUC: 00000000000'
    ];
    $factura_info = null;
}

// Función para centrar texto en 32 caracteres (ancho típico 80mm)
function centrarTexto($texto, $ancho = 32) {
    $longitud = strlen($texto);
    if ($longitud >= $ancho) {
        return substr($texto, 0, $ancho);
    }
    $espacios = floor(($ancho - $longitud) / 2);
    return str_repeat(' ', $espacios) . $texto;
}

// Función para justificar texto (izquierda-derecha)
function justificarTexto($izquierda, $derecha, $ancho = 32) {
    $espacios_necesarios = $ancho - strlen($izquierda) - strlen($derecha);
    if ($espacios_necesarios < 1) {
        return substr($izquierda, 0, $ancho - strlen($derecha)) . $derecha;
    }
    return $izquierda . str_repeat(' ', $espacios_necesarios) . $derecha;
}

// Función para truncar texto si es muy largo
function truncarTexto($texto, $max = 32) {
    return strlen($texto) > $max ? substr($texto, 0, $max-3) . '...' : $texto;
}

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo de Pago - Impresión Térmica</title>
    <style>
        /* Estilos para impresión térmica 80mm */
        @media print {
            @page {
                size: 80mm auto;
                margin: 0;
            }
            body {
                margin: 0;
                padding: 2mm;
                font-family: 'Courier New', monospace;
                font-size: 9pt;
                line-height: 1.1;
                color: black;
                background: white;
            }
            .no-print {
                display: none !important;
            }
        }
        
        /* Estilos para vista previa */
        body {
            font-family: 'Courier New', monospace;
            font-size: 10pt;
            line-height: 1.2;
            max-width: 300px;
            margin: 0 auto;
            padding: 10px;
            background: white;
            color: black;
        }
        
        .recibo-container {
            width: 100%;
            background: white;
            border: 1px dashed #ccc;
            padding: 5px;
        }
        
        .separador {
            border-top: 1px dashed #000;
            margin: 3px 0;
        }
        
        .centrado {
            text-align: center;
        }
        
        .negrita {
            font-weight: bold;
        }
        
        .grande {
            font-size: 12pt;
            font-weight: bold;
        }
        
        .botones-control {
            text-align: center;
            margin: 20px 0;
            background: #f0f0f0;
            padding: 10px;
            border-radius: 5px;
        }
        
        .btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 8px 15px;
            margin: 5px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 12px;
        }
        
        .btn:hover {
            background: #0056b3;
        }
        
        .btn-success {
            background: #28a745;
        }
        
        .btn-success:hover {
            background: #1e7e34;
        }
        
        .btn-secondary {
            background: #6c757d;
        }
        
        .btn-secondary:hover {
            background: #545b62;
        }
    </style>
</head>
<body>
    <!-- Botones de control (no se imprimen) -->
    <div class="botones-control no-print">
        <h4>Vista Previa - Recibo Térmico 80mm</h4>
        <button class="btn btn-success" onclick="imprimirRecibo()">
            🖨️ Imprimir Ahora
        </button>
        <button class="btn btn-secondary" onclick="window.close()">
            ❌ Cerrar
        </button>
        <br><small>El recibo se optimizará automáticamente para impresoras térmicas de 80mm</small>
    </div>

    <!-- Contenido del recibo -->
    <div class="recibo-container">
        <!-- Encabezado del consultorio -->
        <div class="centrado grande">
            <?= strtoupper(truncarTexto($consultorio['nombre_consultorio'], 32)) ?>
        </div>
        <div class="centrado">
            <?= truncarTexto($consultorio['direccion'], 32) ?>
        </div>
        <div class="centrado">
            <?= truncarTexto($consultorio['telefono'], 32) ?>
        </div>
        <?php if (!empty($consultorio['ruc'])): ?>
        <div class="centrado">
            <?= truncarTexto($consultorio['ruc'], 32) ?>
        </div>
        <?php endif; ?>
        
        <div class="separador"></div>
        
        <!-- Título del recibo -->
        <div class="centrado negrita">
            RECIBO DE PAGO
        </div>
        
        <div class="separador"></div>
        
        <!-- Información del recibo -->
        <div>
            <?= justificarTexto('Fecha:', date('d/m/Y H:i'), 32) ?>
        </div>
        <div>
            <?= justificarTexto('Recibo:', 'REC-' . sprintf('%06d', $ultimo_pago['factura_id'] ?? 0), 32) ?>
        </div>
        <?php if (!empty($ultimo_pago['numero_factura'])): ?>
        <div>
            <?= justificarTexto('Factura:', $ultimo_pago['numero_factura'], 32) ?>
        </div>
        <?php endif; ?>
        
        <div class="separador"></div>
        
        <!-- Información del paciente -->
        <div class="negrita">
            PACIENTE:
        </div>
        <div>
            <?= truncarTexto(strtoupper($ultimo_pago['paciente_nombre'] ?? 'N/A'), 32) ?>
        </div>
        <?php if (!empty($factura_info['paciente_cedula'])): ?>
        <div>
            <?= justificarTexto('Cedula:', $factura_info['paciente_cedula'], 32) ?>
        </div>
        <?php endif; ?>
        <?php if (!empty($factura_info['paciente_telefono'])): ?>
        <div>
            <?= justificarTexto('Tel:', $factura_info['paciente_telefono'], 32) ?>
        </div>
        <?php endif; ?>
        
        <div class="separador"></div>
        
        <!-- Detalles del pago -->
        <div class="negrita">
            DETALLE DEL PAGO:
        </div>
        <div>
            <?= justificarTexto('Monto:', '$' . number_format($ultimo_pago['monto'] ?? 0, 2), 32) ?>
        </div>
        <div>
            <?= justificarTexto('Metodo:', ucfirst(str_replace('_', ' ', $ultimo_pago['metodo_pago'] ?? 'efectivo')), 32) ?>
        </div>
        <?php if (!empty($ultimo_pago['numero_referencia'])): ?>
        <div>
            <?= justificarTexto('Ref.:', $ultimo_pago['numero_referencia'], 32) ?>
        </div>
        <?php endif; ?>
        
        <div class="separador"></div>
        
        <!-- Total destacado -->
        <div class="centrado negrita grande">
            TOTAL PAGADO: $<?= number_format($ultimo_pago['monto'] ?? 0, 2) ?>
        </div>
        
        <div class="separador"></div>
        
        <!-- Información adicional -->
        <?php if (!empty($factura_info['medico_nombre'])): ?>
        <div>
            <?= justificarTexto('Atendido por:', truncarTexto($factura_info['medico_nombre'], 18), 32) ?>
        </div>
        <?php endif; ?>
        
        <div class="separador"></div>
        
        <!-- Pie del recibo -->
        <div class="centrado">
            ¡GRACIAS POR SU CONFIANZA!
        </div>
        <div class="centrado">
            <?= date('d/m/Y H:i:s') ?>
        </div>
        
        <!-- Espacio para corte -->
        <br><br>
        <div class="centrado">
            - - - - - - - - - - - - - - - -
        </div>
        <br>
    </div>

    <script>
        function imprimirRecibo() {
            // Configurar la página para impresión térmica
            const css = `
                <style>
                    @page {
                        size: 80mm auto;
                        margin: 2mm;
                    }
                    body {
                        font-family: 'Courier New', monospace !important;
                        font-size: 9pt !important;
                        line-height: 1.1 !important;
                        margin: 0 !important;
                        padding: 0 !important;
                        color: black !important;
                        background: white !important;
                    }
                    .no-print {
                        display: none !important;
                    }
                    .recibo-container {
                        border: none !important;
                        padding: 2mm !important;
                    }
                </style>
            `;
            
            // Agregar el CSS al documento
            const head = document.getElementsByTagName('head')[0];
            const style = document.createElement('style');
            style.innerHTML = css;
            head.appendChild(style);
            
            // Imprimir
            window.print();
            
            // Opcional: cerrar la ventana después de imprimir
            setTimeout(function() {
                if (confirm('¿Desea cerrar esta ventana?')) {
                    window.close();
                }
            }, 1000);
        }
        
        // Auto-imprimir si se pasa el parámetro
        <?php if (isset($_GET['auto_print']) && $_GET['auto_print'] == '1'): ?>
        window.onload = function() {
            setTimeout(imprimirRecibo, 500);
        };
        <?php endif; ?>
    </script>
</body>
</html>
