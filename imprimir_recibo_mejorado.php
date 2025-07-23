<?php
// Versión mejorada de imprimir_recibo.php con debug avanzado
require_once 'session_config.php';
session_start();
require_once 'config.php';

// Debug: Log para troubleshooting
error_log("=== IMPRIMIR RECIBO MEJORADO DEBUG ===");
error_log("Session ID: " . session_id());
error_log("GET params: " . print_r($_GET, true));
error_log("Session loggedin: " . (isset($_SESSION['loggedin']) ? $_SESSION['loggedin'] : 'NO SET'));
error_log("ultimo_pago existe: " . (isset($_SESSION['ultimo_pago']) ? 'SI' : 'NO'));

// HTML inicial
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo de Pago - Consultorio</title>
    <style id="estilosBase">
        /* ========== ESTILOS PARA PANTALLA ========== */
        body { 
            font-family: 'Courier New', monospace; 
            font-size: 12px; 
            margin: 0; 
            padding: 20px; 
            background: #f5f5f5; 
        }
        .recibo { 
            max-width: 320px; 
            margin: 0 auto; 
            border: 2px solid #000; 
            padding: 15px; 
            background: white;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .header { 
            text-align: center; 
            border-bottom: 2px dashed #000; 
            margin-bottom: 15px; 
            padding-bottom: 10px; 
        }
        .logo {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .linea { 
            margin: 8px 0;
            display: flex;
            justify-content: space-between;
        }
        .linea-simple {
            margin: 5px 0;
        }
        .total { 
            font-weight: bold; 
            border-top: 2px dashed #000; 
            margin-top: 15px; 
            padding-top: 10px; 
            font-size: 14px;
            text-align: center;
        }
        .separador {
            text-align: center;
            margin: 10px 0;
            border-bottom: 1px dashed #ccc;
            padding-bottom: 5px;
        }
        .centrado { 
            text-align: center; 
        }
        .derecha { 
            text-align: right; 
        }
        .error { 
            color: red; 
            text-align: center; 
            padding: 20px; 
        }
        .botones-control {
            text-align: center;
            margin-top: 20px;
            padding: 10px;
            background: #e9ecef;
            border-radius: 5px;
        }
        .btn {
            padding: 8px 16px;
            margin: 5px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 12px;
        }
        .btn-success { background: #28a745; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        
        /* ========== ESTILOS PARA IMPRESIÓN TÉRMICA 80MM ========== */
        @media print {
            .no-print, .botones-control { display: none !important; }
            
            /* Configuración específica para impresora térmica 80mm */
            @page {
                size: 80mm auto; /* Ancho exacto de 80mm, alto automático */
                margin: 0;       /* Sin márgenes para maximizar espacio */
            }
            
            /* Reset completo para impresión */
            * {
                margin: 0 !important;
                padding: 0 !important;
                box-sizing: border-box !important;
            }
            
            body {
                font-family: 'Courier New', monospace !important;
                font-size: 10px !important;
                line-height: 1.1 !important;
                color: black !important;
                background: white !important;
                width: 80mm !important;
                max-width: 80mm !important;
                margin: 0 !important;
                padding: 1mm !important;
            }
            
            .recibo {
                width: 100% !important;
                max-width: 78mm !important;
                border: none !important;
                padding: 0 !important;
                margin: 0 !important;
                background: white !important;
                box-shadow: none !important;
            }
            
            .header {
                text-align: center !important;
                border-bottom: 1px dashed black !important;
                margin-bottom: 2mm !important;
                padding-bottom: 1mm !important;
                font-size: 9px !important;
            }
            
            .logo {
                font-size: 11px !important;
                font-weight: bold !important;
                margin-bottom: 1mm !important;
            }
            
            .linea {
                margin: 0.5mm 0 !important;
                font-size: 9px !important;
                word-wrap: break-word !important;
                display: block !important;
                width: 100% !important;
            }
            
            .linea-simple {
                margin: 0.5mm 0 !important;
                font-size: 9px !important;
            }
            
            .linea .label {
                display: inline-block !important;
                width: 40% !important;
                font-weight: normal !important;
            }
            
            .linea .valor {
                display: inline-block !important;
                width: 58% !important;
                text-align: right !important;
                font-weight: bold !important;
            }
            
            .total {
                font-weight: bold !important;
                border-top: 1px dashed black !important;
                margin-top: 2mm !important;
                padding-top: 1mm !important;
                text-align: center !important;
                font-size: 11px !important;
            }
            
            .separador {
                text-align: center !important;
                margin: 1mm 0 !important;
                border-bottom: 1px dashed #999 !important;
                padding-bottom: 0.5mm !important;
                font-size: 8px !important;
            }
            
            .centrado { 
                text-align: center !important; 
                font-size: 9px !important;
            }
            
            .derecha { 
                text-align: right !important; 
                font-size: 9px !important;
            }
            
            /* Texto más pequeño para información adicional */
            .info-adicional {
                font-size: 8px !important;
                margin: 0.5mm 0 !important;
            }
            
            /* Asegurar que el texto no se corte */
            .no-break {
                page-break-inside: avoid !important;
            }
        }
    </style>
    
    <!-- Script para cargar configuración personalizada -->
    <script>
        // Cargar configuración personalizada si existe
        document.addEventListener('DOMContentLoaded', function() {
            const cssPersonalizado = localStorage.getItem('reciboCSS');
            const config = localStorage.getItem('reciboConfig');
            
            if (cssPersonalizado && config) {
                try {
                    const configObj = JSON.parse(config);
                    console.log('🔧 Cargando configuración personalizada:', configObj);
                    
                    // Crear estilo personalizado
                    const style = document.createElement('style');
                    style.id = 'estilosPersonalizados';
                    style.textContent = cssPersonalizado;
                    document.head.appendChild(style);
                    
                    // Mostrar indicador de configuración personalizada
                    const indicador = document.createElement('div');
                    indicador.className = 'no-print';
                    indicador.style.cssText = `
                        position: fixed; 
                        top: 10px; 
                        right: 10px; 
                        background: #28a745; 
                        color: white; 
                        padding: 5px 10px; 
                        border-radius: 3px; 
                        font-size: 11px; 
                        z-index: 1000;
                        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
                    `;
                    indicador.innerHTML = `⚙️ Config: ${configObj.ancho}mm`;
                    document.body.appendChild(indicador);
                    
                    // Remover indicador después de 3 segundos
                    setTimeout(() => {
                        if (indicador.parentNode) {
                            indicador.parentNode.removeChild(indicador);
                        }
                    }, 3000);
                    
                } catch (e) {
                    console.error('Error al cargar configuración personalizada:', e);
                }
            } else {
                console.log('📐 Usando configuración por defecto (80mm)');
            }
        });
    </script>
    
</head>
<body>

<?php
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
    echo "<button onclick='window.close()' class='no-print'>Cerrar</button>";
    echo "</div>";
    echo "</body></html>";
    exit();
}

// Generar el recibo optimizado para impresora térmica 80mm
echo "<div class='recibo'>";

// Header del consultorio - Formato optimizado para 80mm
echo "<div class='header'>";
echo "<div class='logo'>CONSULTORIO ODONTOLOGICO</div>";
echo "<div class='centrado'>RECIBO DE PAGO</div>";
echo "<div class='centrado'>=============================</div>";
echo "</div>";

// Información del recibo - Formato de dos columnas para 80mm
echo "<div class='linea'>";
echo "<span class='label'>RECIBO #:</span>";
echo "<span class='valor'>" . str_pad($pago['pago_id'] ?? '0', 6, '0', STR_PAD_LEFT) . "</span>";
echo "</div>";

echo "<div class='linea'>";
echo "<span class='label'>FECHA:</span>";
echo "<span class='valor'>" . ($pago['fecha_pago_formato'] ?? date('d/m/Y H:i')) . "</span>";
echo "</div>";

if (!empty($pago['numero_factura'])) {
    echo "<div class='linea'>";
    echo "<span class='label'>FACTURA:</span>";
    echo "<span class='valor'>" . htmlspecialchars($pago['numero_factura']) . "</span>";
    echo "</div>";
}

echo "<div class='separador'>-----------------------------</div>";

// Información del paciente
echo "<div class='linea-simple centrado'><strong>DATOS DEL PACIENTE</strong></div>";
echo "<div class='linea-simple'>NOMBRE: " . htmlspecialchars($pago['paciente_nombre'] ?? 'No especificado') . "</div>";

if (!empty($pago['paciente_cedula'])) {
    echo "<div class='linea-simple'>CEDULA: " . htmlspecialchars($pago['paciente_cedula']) . "</div>";
}

if (!empty($pago['medico_nombre'])) {
    echo "<div class='linea-simple'>MEDICO: " . htmlspecialchars($pago['medico_nombre']) . "</div>";
}

echo "<div class='separador'>-----------------------------</div>";

// Método de pago
echo "<div class='linea'>";
echo "<span class='label'>METODO PAGO:</span>";
echo "<span class='valor'>" . strtoupper(str_replace('_', ' ', $pago['metodo_pago'] ?? 'EFECTIVO')) . "</span>";
echo "</div>";

// Observaciones (si existen)
if (!empty($pago['observaciones'])) {
    echo "<div class='separador'>-----------------------------</div>";
    echo "<div class='linea-simple centrado'><strong>OBSERVACIONES</strong></div>";
    // Dividir texto largo en líneas para 80mm
    $observaciones = wordwrap(htmlspecialchars($pago['observaciones']), 25, "\n", true);
    $lineas_obs = explode("\n", $observaciones);
    foreach ($lineas_obs as $linea) {
        echo "<div class='linea-simple'>" . trim($linea) . "</div>";
    }
}

echo "<div class='separador'>=============================</div>";

// Total - Destacado y centrado
echo "<div class='total no-break'>";
echo "<div style='font-size: 12px; margin-bottom: 2mm;'><strong>MONTO TOTAL PAGADO</strong></div>";
echo "<div style='font-size: 16px; font-weight: bold;'>$" . number_format(floatval($pago['monto'] ?? 0), 2) . "</div>";
echo "</div>";

echo "<div class='separador'>=============================</div>";

// Footer optimizado para 80mm
echo "<div class='centrado info-adicional'>¡GRACIAS POR SU PAGO!</div>";
echo "<div class='centrado info-adicional'>Conserve este recibo</div>";
echo "<div class='centrado info-adicional'>Generado: " . date('d/m/Y H:i') . "</div>";

// Espacio adicional para corte del papel
echo "<div style='height: 10mm;'></div>";

echo "</div>";

// Botones de control (no se imprimen)
echo "<div class='botones-control no-print'>";
echo "<h6 style='color: #28a745; margin-bottom: 15px;'>📄 Recibo Optimizado para Impresora Térmica 80mm</h6>";
echo "<div id='estadoImpresion' style='margin-bottom: 15px; padding: 10px; background: #e3f2fd; border-radius: 3px; font-size: 11px;'>";
echo "<span id='mensajeImpresion'>⏳ Preparando impresión automática...</span>";
echo "</div>";
echo "<button onclick='imprimirManual()' class='btn btn-success'>";
echo "🖨️ Imprimir en Térmica 80mm";
echo "</button>";
echo "<button onclick='cerrarVentana()' class='btn btn-secondary'>";
echo "❌ Cerrar";
echo "</button>";
echo "<button onclick='abrirTestImpresion()' class='btn btn-secondary'>";
echo "🔧 Test Impresión";
echo "</button>";
echo "<button onclick='abrirConfiguracion()' class='btn btn-secondary'>";
echo "📏 Configurar Tamaño";
echo "</button>";
echo "<div style='margin-top: 10px; font-size: 10px; color: #666;'>";
echo "<em>💡 Optimizado para impresoras térmicas de 80mm<br>";
echo "🔧 Configure su impresora en modo 'Recibo' o '80mm'<br>";
echo "📏 Ancho del papel: 80mm (3.15 pulgadas)<br>";
echo "⚙️ Use 'Configurar Tamaño' para personalizar el formato</em>";
echo "</div>";
echo "</div>";
echo "</div>";
?>

<script>
console.log('=== RECIBO MEJORADO CARGADO ===');
console.log('Datos del pago:', <?= json_encode($pago) ?>);

// Variables globales para control de impresión
let impresionEjecutada = false;
let intentosImpresion = 0;
const maxIntentos = 5;
let timerImpresion = null;
let debugMode = true; // Modo debug habilitado

function logDebug(mensaje) {
    if (debugMode) {
        console.log('🔍 [DEBUG]: ' + mensaje);
    }
}

function updateMensaje(texto, color = '#fff3cd') {
    const mensaje = document.getElementById('mensajeImpresion');
    if (mensaje) {
        mensaje.innerHTML = texto;
        mensaje.style.background = color;
    }
    logDebug('MENSAJE ACTUALIZADO: ' + texto.replace(/<[^>]*>/g, ''));
}

function verificarCompatibilidad() {
    logDebug('=== VERIFICACIÓN DE COMPATIBILIDAD ===');
    
    const resultados = {
        windowPrint: typeof window.print === 'function',
        userAgent: navigator.userAgent,
        isPopup: window.opener !== null,
        docState: document.readyState,
        hasRecibo: document.querySelector('.recibo') !== null
    };
    
    logDebug('window.print disponible: ' + resultados.windowPrint);
    logDebug('User Agent: ' + resultados.userAgent);
    logDebug('Es ventana emergente: ' + resultados.isPopup);
    logDebug('Estado del documento: ' + resultados.docState);
    logDebug('Contenido del recibo encontrado: ' + resultados.hasRecibo);
    
    return resultados;
}

function autoImprimir() {
    if (impresionEjecutada) {
        logDebug('Impresión ya ejecutada, saliendo...');
        return;
    }
    
    intentosImpresion++;
    logDebug('=== INTENTO DE IMPRESIÓN #' + intentosImpresion + '/' + maxIntentos + ' ===');
    
    updateMensaje('🖨️ Preparando impresión automática... (Intento ' + intentosImpresion + '/' + maxIntentos + ')');
    
    // Verificar compatibilidad
    const compat = verificarCompatibilidad();
    
    if (compat.docState !== 'complete') {
        logDebug('Documento no completamente cargado, esperando...');
        if (intentosImpresion <= maxIntentos) {
            timerImpresion = setTimeout(autoImprimir, 1000);
        }
        return;
    }
    
    if (!compat.hasRecibo) {
        logDebug('Contenido del recibo no encontrado');
        updateMensaje('❌ Error: Contenido del recibo no cargado', '#f8d7da');
        return;
    }
    
    if (!compat.windowPrint) {
        logDebug('window.print no está disponible');
        updateMensaje('❌ Error: Función de impresión no disponible en este navegador', '#f8d7da');
        return;
    }
    
    logDebug('Todas las verificaciones pasaron, procediendo con impresión...');
    
    try {
        updateMensaje('🖨️ Enviando comando de impresión...', '#e3f2fd');
        
        // Pequeño delay para asegurar que el mensaje se actualice
        setTimeout(function() {
            logDebug('Ejecutando window.print()...');
            
            // Intentar la impresión
            window.print();
            
            impresionEjecutada = true;
            logDebug('window.print() ejecutado exitosamente');
            
            // Feedback después de la impresión
            setTimeout(function() {
                updateMensaje('✅ Comando de impresión enviado correctamente.<br>Si no se imprimió, use el botón "Imprimir Manualmente".', '#d4edda');
            }, 1500);
            
        }, 300);
        
    } catch (error) {
        logDebug('Error en window.print(): ' + error.message);
        console.error('❌ Error en window.print():', error);
        
        updateMensaje('❌ Error de impresión: ' + error.message, '#f8d7da');
        
        // Reintentar si no hemos alcanzado el máximo
        if (intentosImpresion < maxIntentos) {
            logDebug('Reintentando en 2 segundos...');
            updateMensaje('⏳ Error temporal. Reintentando en 2 segundos... (' + intentosImpresion + '/' + maxIntentos + ')', '#fff3cd');
            timerImpresion = setTimeout(autoImprimir, 2000);
        } else {
            logDebug('Máximo de intentos alcanzado');
            updateMensaje('❌ No se pudo imprimir automáticamente después de ' + maxIntentos + ' intentos.<br>Use el botón "Imprimir Manualmente".', '#f8d7da');
        }
    }
}

function imprimirManual() {
    logDebug('=== IMPRESIÓN MANUAL SOLICITADA ===');
    
    // Cancelar auto-impresión si está en curso
    if (timerImpresion) {
        clearTimeout(timerImpresion);
        timerImpresion = null;
    }
    
    updateMensaje('🖨️ Ejecutando impresión manual...', '#e3f2fd');
    
    try {
        if (typeof window.print !== 'function') {
            throw new Error('La función de impresión no está disponible');
        }
        
        window.print();
        
        setTimeout(function() {
            updateMensaje('✅ Comando de impresión manual enviado correctamente.', '#d4edda');
        }, 1000);
        
    } catch (error) {
        logDebug('Error en impresión manual: ' + error.message);
        console.error('❌ Error en impresión manual:', error);
        alert('Error al intentar imprimir: ' + error.message + '\n\nVerifique que su impresora esté conectada y configurada correctamente.');
        
        updateMensaje('❌ Error al imprimir: ' + error.message, '#f8d7da');
    }
}

function cerrarVentana() {
    logDebug('Cerrando ventana de recibo...');
    
    // Cancelar timers activos
    if (timerImpresion) {
        clearTimeout(timerImpresion);
    }
    
    try {
        window.close();
    } catch (error) {
        logDebug('No se pudo cerrar la ventana automáticamente');
    }
}

function abrirTestImpresion() {
    logDebug('Abriendo herramienta de test de impresión...');
    try {
        const testWindow = window.open('test_impresion_automatica.php', 'testImpresion', 'width=800,height=600,scrollbars=yes');
        if (!testWindow) {
            alert('No se pudo abrir la ventana de test. Verifique que no esté bloqueada por el navegador.');
        }
    } catch (error) {
        alert('Error al abrir test de impresión: ' + error.message);
    }
}

function abrirConfiguracion() {
    logDebug('Abriendo configuración de tamaño de recibo...');
    
    const mensaje = '⚙️ Configuración de Tamaño de Recibo\n\n' +
                   'Seleccione una opción:\n\n' +
                   '1. Configurador Interactivo (Recomendado)\n' +
                   '2. Test de Impresión Térmica 80mm\n' +
                   '3. Documentación de Configuración\n\n' +
                   'Ingrese el número (1-3):';
    
    const opcion = prompt(mensaje);
    
    try {
        if (opcion === '1') {
            const configWindow = window.open('configuracion_impresora_80mm.php', 'configuracion', 
                'width=1000,height=800,scrollbars=yes,resizable=yes');
            if (!configWindow) {
                alert('No se pudo abrir la ventana de configuración. Verifique que no esté bloqueada por el navegador.');
            } else {
                logDebug('Ventana de configuración abierta exitosamente');
            }
        } else if (opcion === '2') {
            const testWindow = window.open('test_impresion_termica_80mm.php', 'testTermica', 
                'width=900,height=700,scrollbars=yes');
            if (!testWindow) {
                alert('No se pudo abrir el test térmico. Verifique que no esté bloqueada por el navegador.');
            }
        } else if (opcion === '3') {
            window.open('configuracion_impresora_80mm.php#troubleshooting', '_blank');
        } else if (opcion) {
            alert('❌ Opción inválida. Seleccione 1, 2 o 3.');
        }
    } catch (error) {
        alert('Error al abrir configuración: ' + error.message);
        logDebug('Error en abrirConfiguracion: ' + error.message);
    }
}

// === INICIALIZACIÓN ===
logDebug('Estado inicial del documento: ' + document.readyState);

// Múltiples puntos de entrada para auto-impresión
if (document.readyState === 'complete') {
    logDebug('Documento ya completo, ejecutando impresión inmediata');
    setTimeout(autoImprimir, 500);
} else {
    // Escuchar DOMContentLoaded
    document.addEventListener('DOMContentLoaded', function() {
        logDebug('DOMContentLoaded disparado');
        setTimeout(autoImprimir, 300);
    });
    
    // Escuchar cuando la ventana termine de cargar
    window.addEventListener('load', function() {
        logDebug('Window load disparado');
        if (!impresionEjecutada) {
            setTimeout(autoImprimir, 500);
        }
    });
}

// Fallback con timeout
setTimeout(function() {
    if (!impresionEjecutada && intentosImpresion === 0) {
        logDebug('Fallback timeout - ejecutando impresión');
        autoImprimir();
    }
}, 2000);

// Detectar eventos de impresión del navegador
window.addEventListener('beforeprint', function() {
    logDebug('beforeprint detectado - preparando impresión');
    updateMensaje('🖨️ Preparando impresión...', '#e3f2fd');
});

window.addEventListener('afterprint', function() {
    logDebug('afterprint detectado - impresión completada o cancelada');
    updateMensaje('✅ Proceso de impresión finalizado.', '#d4edda');
});

// Información de debug inicial
verificarCompatibilidad();

<?php
// Limpiar datos de sesión después de mostrar el recibo (solo si no es de prueba)
if (isset($pago['pago_id']) && $pago['pago_id'] != 999) {
    echo "
    // Limpiar datos de sesión después de un breve delay
    setTimeout(function() {
        fetch('clear_ultimo_pago.php').then(function() {
            logDebug('Datos de sesión limpiados');
        }).catch(function(error) {
            logDebug('Error al limpiar sesión: ' + error);
        });
    }, 5000); // Aumentado a 5 segundos para dar tiempo a la impresión
    ";
}
?>
</script>

</body>
</html>

<?php
error_log("Recibo mejorado generado exitosamente para pago: " . ($pago['pago_id'] ?? 'SESION'));
?>
