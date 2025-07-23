<?php
// Versión corregida y simplificada de imprimir_recibo.php
require_once 'session_config.php';
session_start();
require_once 'config.php';

// Debug: Log para troubleshooting
error_log("=== IMPRIMIR RECIBO DEBUG ===");
error_log("Session ID: " . session_id());
error_log("GET params: " . print_r($_GET, true));
error_log("Session loggedin: " . (isset($_SESSION['loggedin']) ? $_SESSION['loggedin'] : 'NO SET'));
error_log("ultimo_pago existe: " . (isset($_SESSION['ultimo_pago']) ? 'SI' : 'NO'));

// HTML inicial
echo "<!DOCTYPE html>";
echo "<html lang='es'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Recibo de Pago</title>";
echo "<style>";
echo "/* Estilos base para pantalla */";
echo "body { font-family: 'Courier New', monospace; font-size: 12px; margin: 0; padding: 20px; background: white; }";
echo ".recibo { max-width: 300px; margin: 0 auto; border: 1px solid #000; padding: 10px; }";
echo ".header { text-align: center; border-bottom: 1px dashed #000; margin-bottom: 10px; padding-bottom: 10px; }";
echo ".linea { margin: 5px 0; }";
echo ".total { font-weight: bold; border-top: 1px dashed #000; margin-top: 10px; padding-top: 10px; }";
echo ".error { color: red; text-align: center; padding: 20px; }";
echo ".debug { background: #f0f0f0; padding: 10px; margin: 10px 0; font-size: 10px; }";
echo "";
echo "/* Estilos específicos para impresión térmica 80mm */";
echo "@media print {";
echo "    .no-print { display: none !important; }";
echo "    ";
echo "    /* Configuración de página para impresora térmica */";
echo "    @page {";
echo "        size: 80mm auto; /* Ancho fijo de 80mm, alto automático */";
echo "        margin: 0;       /* Sin márgenes */";
echo "    }";
echo "    ";
echo "    /* Reset completo para impresión */";
echo "    * {";
echo "        margin: 0;";
echo "        padding: 0;";
echo "        box-sizing: border-box;";
echo "    }";
echo "    ";
echo "    body {";
echo "        font-family: 'Courier New', monospace;";
echo "        font-size: 11px;";
echo "        line-height: 1.2;";
echo "        color: black;";
echo "        background: white;";
echo "        width: 80mm;";
echo "        margin: 0;";
echo "        padding: 2mm;";
echo "    }";
echo "    ";
echo "    .recibo {";
echo "        width: 100%;";
echo "        max-width: none;";
echo "        border: none;";
echo "        padding: 0;";
echo "        margin: 0;";
echo "    }";
echo "    ";
echo "    .header {";
echo "        text-align: center;";
echo "        border-bottom: 1px dashed black;";
echo "        margin-bottom: 3mm;";
echo "        padding-bottom: 2mm;";
echo "        font-size: 10px;";
echo "    }";
echo "    ";
echo "    .linea {";
echo "        margin: 1mm 0;";
echo "        font-size: 10px;";
echo "        word-wrap: break-word;";
echo "    }";
echo "    ";
echo "    .total {";
echo "        font-weight: bold;";
echo "        border-top: 1px dashed black;";
echo "        margin-top: 3mm;";
echo "        padding-top: 2mm;";
echo "        text-align: center;";
echo "        font-size: 12px;";
echo "    }";
echo "}";
echo "</style>";
echo "</head>";
echo "<body>";

// Verificar login
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    echo "<div class='error'>";
    echo "<h3>❌ Error de Sesión</h3>";
    echo "<p>Su sesión ha expirado o no está logueado.</p>";
    echo "<p>Session ID: " . session_id() . "</p>";
    echo "<button onclick='window.close()' class='no-print'>Cerrar</button>";
    echo "</div>";
    echo "</body></html>";
    exit();
}

// Obtener datos del pago
$pago = null;

// Método 1: Por parámetro GET (más confiable)
if (isset($_GET['pago_id']) && is_numeric($_GET['pago_id'])) {
    $pago_id = $_GET['pago_id'];
    
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
            error_log("Pago obtenido de BD correctamente: ID " . $pago_id);
        }
    } catch (PDOException $e) {
        error_log("Error al obtener pago de BD: " . $e->getMessage());
    }
}

// Método 2: Por sesión (fallback)
if (!$pago && isset($_SESSION['ultimo_pago'])) {
    $pago = $_SESSION['ultimo_pago'];
    error_log("Usando pago de sesión como fallback");
}

// Si no hay datos, mostrar error
if (!$pago) {
    echo "<div class='error'>";
    echo "<h3>❌ Sin Datos de Pago</h3>";
    echo "<p>No hay información de pago para imprimir.</p>";
    echo "<p>Por favor registre un pago primero.</p>";
    
    echo "<div class='debug no-print'>";
    echo "<strong>Debug Info:</strong><br>";
    echo "Session ID: " . session_id() . "<br>";
    echo "Usuario: " . htmlspecialchars($_SESSION['username'] ?? 'N/A') . "<br>";
    echo "GET pago_id: " . htmlspecialchars($_GET['pago_id'] ?? 'NO') . "<br>";
    echo "Session ultimo_pago: " . (isset($_SESSION['ultimo_pago']) ? 'EXISTS' : 'NO') . "<br>";
    if (isset($_SESSION['ultimo_pago'])) {
        echo "Contenido:<br><pre>" . print_r($_SESSION['ultimo_pago'], true) . "</pre>";
    }
    echo "</div>";
    
    echo "<button onclick='window.close()' class='no-print'>Cerrar</button>";
    echo "</div>";
    echo "</body></html>";
    exit();
}

// Procesar datos para el recibo
$numero_factura = $pago['numero_factura'] ?? 'N/A';
$monto = floatval($pago['monto'] ?? 0);
$metodo_pago = $pago['metodo_pago'] ?? 'efectivo';
$fecha = $pago['fecha_pago_formato'] ?? date('d/m/Y H:i');
$paciente_nombre = $pago['paciente_nombre'] ?? 'Paciente';
$paciente_cedula = $pago['paciente_cedula'] ?? '';
$medico_nombre = $pago['medico_nombre'] ?? 'Médico';

// Obtener configuración del consultorio
try {
    $stmt = $conn->query("SELECT nombre_consultorio, direccion, telefono, ruc FROM configuracion LIMIT 1");
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $config = null;
}

$nombre_consultorio = $config['nombre_consultorio'] ?? 'CONSULTORIO ODONTOLÓGICO';
$direccion = $config['direccion'] ?? 'Dirección del Consultorio';
$telefono = $config['telefono'] ?? '(555) 123-4567';
$ruc = $config['ruc'] ?? 'RUC: 12345678901';

// Generar el recibo
echo "<div class='recibo'>";

// Header
echo "<div class='header'>";
echo "<strong>" . htmlspecialchars($nombre_consultorio) . "</strong><br>";
echo htmlspecialchars($direccion) . "<br>";
echo "Tel: " . htmlspecialchars($telefono) . "<br>";
echo htmlspecialchars($ruc) . "<br>";
echo "<br>";
echo "<strong>RECIBO DE PAGO</strong><br>";
echo "Fecha: " . htmlspecialchars($fecha);
echo "</div>";

// Datos del pago
echo "<div class='linea'><strong>Factura N°:</strong> " . htmlspecialchars($numero_factura) . "</div>";
echo "<div class='linea'><strong>Paciente:</strong> " . htmlspecialchars($paciente_nombre) . "</div>";
if ($paciente_cedula) {
    echo "<div class='linea'><strong>Cédula:</strong> " . htmlspecialchars($paciente_cedula) . "</div>";
}
echo "<div class='linea'><strong>Atendido por:</strong> " . htmlspecialchars($medico_nombre) . "</div>";
echo "<div class='linea'><strong>Método de pago:</strong> " . ucfirst(str_replace('_', ' ', htmlspecialchars($metodo_pago))) . "</div>";

echo "<div class='total'>";
echo "<div class='linea'><strong>TOTAL PAGADO: $" . number_format($monto, 2) . "</strong></div>";
echo "</div>";

echo "<div style='text-align: center; margin-top: 15px; font-size: 10px;'>";
echo "¡Gracias por su confianza!<br>";
echo "Recibo generado el " . date('d/m/Y H:i');
echo "</div>";

echo "</div>";

// Botones de control (no se imprimen)
echo "<div class='no-print' style='text-align: center; margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px;'>";
echo "<h6 style='color: #28a745; margin-bottom: 15px;'>📄 Recibo Generado Correctamente</h6>";
echo "<div id='estadoImpresion' style='margin-bottom: 15px; padding: 10px; background: #e3f2fd; border-radius: 3px; font-size: 11px;'>";
echo "<span id='mensajeImpresion'>⏳ Enviando a impresora automáticamente...</span>";
echo "</div>";
echo "<button onclick='imprimirManual()' style='padding: 10px 20px; margin: 5px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;'>";
echo "<i style='margin-right: 5px;'>🖨️</i>Imprimir Manualmente";
echo "</button>";
echo "<button onclick='window.close()' style='padding: 10px 20px; margin: 5px; background: #6c757d; color: white; border: none; border-radius: 5px; cursor: pointer;'>";
echo "<i style='margin-right: 5px;'>❌</i>Cerrar";
echo "</button>";
echo "<div style='margin-top: 10px; font-size: 10px; color: #666;'>";
echo "<em>💡 Si no imprime automáticamente, use el botón \"Imprimir Manualmente\"<br>";
echo "🔧 Asegúrese de que su impresora esté conectada y configurada</em>";
echo "</div>";
echo "</div>";

echo "<script>";
echo "console.log('Recibo cargado exitosamente');";
echo "console.log('Datos del pago:', " . json_encode($pago) . ");";

// Auto-imprimir cuando se carga la página
echo "
// Variables globales para control de impresión
let impresionEjecutada = false;
let intentosImpresion = 0;

// Función para auto-imprimir
function autoImprimir() {
    console.log('Iniciando auto-impresión...');
    
    // Verificar que el documento esté completamente cargado
    if (document.readyState === 'complete') {
        // Actualizar estado
        const mensaje = document.getElementById('mensajeImpresion');
        if (mensaje) {
            mensaje.innerHTML = '🖨️ Ejecutando impresión automática...';
        }
        
        // Pequeño delay para asegurar que todo esté renderizado
        setTimeout(function() {
            console.log('Ejecutando window.print() automático...');
            intentosImpresion++;
            
            try {
                window.print();
                impresionEjecutada = true;
                
                // Actualizar estado después de intentar imprimir
                setTimeout(function() {
                    if (mensaje) {
                        mensaje.innerHTML = '✅ Comando de impresión enviado. Si no imprimió, use el botón manual.';
                        mensaje.style.background = '#d4edda';
                    }
                }, 1000);
                
            } catch (error) {
                console.error('Error en impresión automática:', error);
                if (mensaje) {
                    mensaje.innerHTML = '⚠️ Error en impresión automática. Use el botón manual.';
                    mensaje.style.background = '#fff3cd';
                }
            }
        }, 500);
    } else {
        // Si no está listo, esperar un poco más
        setTimeout(autoImprimir, 100);
    }
}

// Función para impresión manual
function imprimirManual() {
    console.log('Impresión manual solicitada...');
    
    const mensaje = document.getElementById('mensajeImpresion');
    if (mensaje) {
        mensaje.innerHTML = '🖨️ Ejecutando impresión manual...';
        mensaje.style.background = '#e3f2fd';
    }
    
    try {
        window.print();
        
        setTimeout(function() {
            if (mensaje) {
                mensaje.innerHTML = '✅ Comando de impresión manual enviado.';
                mensaje.style.background = '#d4edda';
            }
        }, 500);
        
    } catch (error) {
        console.error('Error en impresión manual:', error);
        alert('Error al intentar imprimir. Verifique que su impresora esté conectada.');
        
        if (mensaje) {
            mensaje.innerHTML = '❌ Error al imprimir. Verifique su impresora.';
            mensaje.style.background = '#f8d7da';
        }
    }
}

// Ejecutar auto-impresión cuando la página esté lista
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', autoImprimir);
} else {
    autoImprimir();
}

// También ejecutar cuando la ventana termine de cargar (por si acaso)
window.onload = function() {
    console.log('Ventana completamente cargada');
    
    if (!impresionEjecutada && intentosImpresion === 0) {
        console.log('Ejecutando impresión de respaldo...');
        setTimeout(function() {
            autoImprimir();
        }, 1000);
    }
};

// Detectar después del evento de impresión (si el navegador lo soporta)
window.addEventListener('afterprint', function() {
    console.log('Evento afterprint detectado');
    const mensaje = document.getElementById('mensajeImpresion');
    if (mensaje) {
        mensaje.innerHTML = '✅ Impresión completada o cancelada por el usuario.';
        mensaje.style.background = '#d4edda';
    }
});

window.addEventListener('beforeprint', function() {
    console.log('Evento beforeprint detectado - iniciando impresión');
});
";

// Limpiar datos de sesión después de mostrar el recibo (solo si no es de prueba)
if (isset($pago['pago_id']) && $pago['pago_id'] != 999) {
    echo "
    // Limpiar datos de sesión después de un breve delay
    setTimeout(function() {
        fetch('clear_ultimo_pago.php').then(function() {
            console.log('Datos de sesión limpiados');
        }).catch(function(error) {
            console.log('Error al limpiar sesión:', error);
        });
    }, 3000); // Aumentado a 3 segundos para dar tiempo a la impresión
    ";
}

echo "</script>";
echo "</body></html>";

error_log("Recibo generado exitosamente para pago: " . ($pago['pago_id'] ?? 'SESION'));
?>
