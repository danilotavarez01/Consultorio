<?php
require_once 'session_config.php';
session_start();

// Verificación simple y compatible
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php?error=' . urlencode('Debe iniciar sesión para acceder a esta página'));
    exit();
}

error_log("CONFIGURACION 80MM: Usuario autenticado - ID: " . ($_SESSION['id'] ?? 'NO_ID'));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración Impresora Térmica 80mm</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .config-section { background: #f8f9fa; padding: 20px; margin: 20px 0; border-radius: 5px; border-left: 4px solid #007bff; }
        .warning { background: #fff3cd; border-left: 4px solid #ffc107; }
        .success { background: #d4edda; border-left: 4px solid #28a745; }
        .code { background: #f1f1f1; padding: 10px; border-radius: 3px; font-family: monospace; }
        .steps { counter-reset: step-counter; }
        .step { counter-increment: step-counter; margin: 15px 0; }
        .step::before { content: counter(step-counter) ". "; font-weight: bold; color: #007bff; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .btn { padding: 10px 20px; margin: 5px; border: none; border-radius: 3px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-primary { background: #007bff; color: white; }
        .btn-success { background: #28a745; color: white; }
    </style>
</head>
<body>
    <!-- Header con modo oscuro -->
    <?php include 'includes/header.php'; ?>
    <h1>🖨️ Configuración de Impresora Térmica 80mm</h1>
    
    <div class="config-section success">
        <h3>👤 Estado de Sesión</h3>
        <p><strong>Usuario logueado:</strong> <?= isset($_SESSION['nombre']) ? $_SESSION['nombre'] : 'Usuario ID: ' . $_SESSION['id'] ?></p>
        <p><strong>Acceso autorizado:</strong> ✅ Configuración disponible</p>
        <p style="font-size: 12px; color: #666;"><em>Sesión ID: <?= session_id() ?></em></p>
    </div>
    
    <div class="config-section warning">
        <h3>⚠️ Importante</h3>
        <p>Esta página le ayudará a configurar correctamente su impresora térmica de 80mm para recibos del consultorio.</p>
    </div>

    <div class="config-section">
        <h3>📋 Especificaciones del Recibo</h3>
        <table>
            <tr><th>Especificación</th><th>Valor</th></tr>
            <tr><td>Ancho del papel</td><td>80mm (3.15 pulgadas)</td></tr>
            <tr><td>Tipo de impresora</td><td>Térmica de recibos</td></tr>
            <tr><td>Tamaño de fuente</td><td>9-11px (Courier New)</td></tr>
            <tr><td>Ancho de línea</td><td>~32 caracteres</td></tr>
            <tr><td>Configuración de página</td><td>80mm x auto</td></tr>
            <tr><td>Márgenes</td><td>0mm (sin márgenes)</td></tr>
        </table>
    </div>

    <div class="config-section">
        <h3>🔧 Pasos de Configuración</h3>
        <div class="steps">
            <div class="step"><strong>Conectar la impresora:</strong> Asegúrese de que su impresora térmica esté conectada por USB o red.</div>
            <div class="step"><strong>Instalar drivers:</strong> Instale los drivers específicos de su modelo de impresora.</div>
            <div class="step"><strong>Configurar en Windows:</strong></div>
            <div style="margin-left: 20px;">
                <p>• Panel de Control → Dispositivos e impresoras</p>
                <p>• Botón derecho en su impresora → Propiedades</p>
                <p>• Pestaña "Avanzadas" → Configurar tamaño de papel personalizado</p>
                <p>• Ancho: 80mm, Alto: Continuo o 200mm</p>
            </div>
            <div class="step"><strong>Configurar navegador:</strong></div>
            <div style="margin-left: 20px;">
                <p>• En Chrome: Configuración → Avanzada → Impresión</p>
                <p>• Seleccionar impresora térmica como predeterminada</p>
                <p>• Configurar tamaño: "Más configuraciones" → Tamaño de papel: "80mm Roll"</p>
            </div>
            <div class="step"><strong>Configurar márgenes:</strong> Establecer todos los márgenes en 0mm.</div>
        </div>
    </div>

    <div class="config-section">
        <h3>🖨️ Configuraciones Específicas por Marca</h3>
        
        <h4>EPSON TM-T20/T88 Series:</h4>
        <div class="code">
Tamaño de papel: 80mm Roll Paper<br>
Orientación: Retrato<br>
Escala: 100%<br>
Márgenes: 0mm todos<br>
Calidad: Normal
        </div>

        <h4>BIXOLON SRP-350/330:</h4>
        <div class="code">
Tamaño: Receipt 80mm<br>
Modo: Text Mode<br>
Font: Courier New, 9pt<br>
Márgenes: Mínimos
        </div>

        <h4>STAR TSP100/650:</h4>
        <div class="code">
Paper Type: Receipt<br>
Paper Width: 80mm<br>
Print Speed: Normal<br>
Character Set: PC437
        </div>
    </div>

    <div class="config-section">
        <h3>📏 Configuración del Tamaño del Recibo</h3>
        <p>Configure el tamaño del recibo según sus necesidades y tipo de impresora:</p>
        
        <h4>🎛️ Configurador Interactivo:</h4>
        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;">
            <form id="reciboConfig" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div>
                    <label for="anchoRecibo"><strong>Ancho del Papel:</strong></label>
                    <select id="anchoRecibo" onchange="actualizarConfig()" style="width: 100%; padding: 5px;">
                        <option value="80">80mm (Estándar Térmico)</option>
                        <option value="58">58mm (Compacto)</option>
                        <option value="112">112mm (Carta Pequeña)</option>
                        <option value="210">210mm (A4)</option>
                    </select>
                </div>
                
                <div>
                    <label for="altoRecibo"><strong>Alto del Papel:</strong></label>
                    <select id="altoRecibo" onchange="actualizarConfig()" style="width: 100%; padding: 5px;">
                        <option value="auto">Automático (Continuo)</option>
                        <option value="150">150mm (Pequeño)</option>
                        <option value="200">200mm (Mediano)</option>
                        <option value="297">297mm (A4)</option>
                    </select>
                </div>
                
                <div>
                    <label for="tamanoFuente"><strong>Tamaño de Fuente:</strong></label>
                    <select id="tamanoFuente" onchange="actualizarConfig()" style="width: 100%; padding: 5px;">
                        <option value="8">8px (Muy Pequeña)</option>
                        <option value="9">9px (Pequeña)</option>
                        <option value="10" selected>10px (Estándar)</option>
                        <option value="11">11px (Mediana)</option>
                        <option value="12">12px (Grande)</option>
                    </select>
                </div>
                
                <div>
                    <label for="margenes"><strong>Márgenes:</strong></label>
                    <select id="margenes" onchange="actualizarConfig()" style="width: 100%; padding: 5px;">
                        <option value="0" selected>0mm (Sin Márgenes)</option>
                        <option value="1">1mm (Mínimo)</option>
                        <option value="2">2mm (Pequeño)</option>
                        <option value="5">5mm (Estándar)</option>
                    </select>
                </div>
                
                <div style="grid-column: 1 / -1;">
                    <label for="orientacion"><strong>Orientación:</strong></label>
                    <div style="display: flex; gap: 15px; margin-top: 5px;">
                        <label><input type="radio" name="orientacion" value="portrait" checked onchange="actualizarConfig()"> Vertical (Portrait)</label>
                        <label><input type="radio" name="orientacion" value="landscape" onchange="actualizarConfig()"> Horizontal (Landscape)</label>
                    </div>
                </div>
            </form>
        </div>
        
        <h4>📋 Vista Previa de Configuración:</h4>
        <div id="vistaPrevia" class="code" style="background: #e8f5e8; border-left: 4px solid #28a745;">
            <!-- Se actualizará con JavaScript -->
        </div>
        
        <h4>💻 Código CSS Personalizado:</h4>
        <div style="background: #f1f1f1; padding: 10px; border-radius: 3px; margin: 10px 0;">
            <textarea id="cssPersonalizado" readonly style="width: 100%; height: 120px; font-family: monospace; font-size: 12px; border: none; background: transparent;">
                <!-- Se generará automáticamente -->
            </textarea>
        </div>
        
        <div style="text-align: center; margin-top: 15px;">
            <button onclick="aplicarConfiguracion()" class="btn btn-success">
                ✅ Aplicar Configuración
            </button>
            <button onclick="descargarCSS()" class="btn btn-primary">
                📥 Descargar CSS
            </button>
            <button onclick="resetearConfig()" class="btn btn-secondary">
                🔄 Resetear
            </button>
        </div>
        
        <h4>📐 Tamaños Predefinidos Comunes:</h4>
        <table style="margin: 10px 0;">
            <tr><th>Tipo</th><th>Ancho</th><th>Alto</th><th>Uso</th><th>Acción</th></tr>
            <tr>
                <td>Recibo Térmico</td><td>80mm</td><td>Auto</td><td>Punto de venta, restaurantes</td>
                <td><button onclick="aplicarPreset('80', 'auto', '10', '0')" class="btn btn-primary" style="padding: 5px 10px; font-size: 11px;">Aplicar</button></td>
            </tr>
            <tr>
                <td>Ticket Compacto</td><td>58mm</td><td>Auto</td><td>Máquinas expendedoras</td>
                <td><button onclick="aplicarPreset('58', 'auto', '9', '0')" class="btn btn-primary" style="padding: 5px 10px; font-size: 11px;">Aplicar</button></td>
            </tr>
            <tr>
                <td>Recibo Estándar</td><td>112mm</td><td>200mm</td><td>Bancos, oficinas</td>
                <td><button onclick="aplicarPreset('112', '200', '11', '2')" class="btn btn-primary" style="padding: 5px 10px; font-size: 11px;">Aplicar</button></td>
            </tr>
            <tr>
                <td>Hoja A4</td><td>210mm</td><td>297mm</td><td>Impresoras láser/inkjet</td>
                <td><button onclick="aplicarPreset('210', '297', '12', '5')" class="btn btn-primary" style="padding: 5px 10px; font-size: 11px;">Aplicar</button></td>
            </tr>
        </table>

    </div>

    <div class="config-section success">
        <h3>✅ Test de Impresión</h3>
        <p>Una vez configurada la impresora, realice las siguientes pruebas:</p>
        <ol>
            <li>Imprima una página de prueba desde Windows</li>
            <li>Use el botón de test abajo para verificar el formato</li>
            <li>Ajuste configuraciones si es necesario</li>
        </ol>
        
        <a href="test_impresion_termica_80mm.php" class="btn btn-primary" target="_blank">
            🧪 Test de Impresión 80mm
        </a>
        <a href="imprimir_recibo_mejorado.php" class="btn btn-success" target="_blank">
            📄 Ver Recibo de Ejemplo
        </a>
    </div>

    <div class="config-section">
        <h3>🔍 Troubleshooting</h3>
        <table>
            <tr><th>Problema</th><th>Solución</th></tr>
            <tr>
                <td>El texto se corta por los lados</td>
                <td>Verificar márgenes (deben ser 0) y ancho de papel (80mm)</td>
            </tr>
            <tr>
                <td>La fuente es muy grande</td>
                <td>Usar Courier New 9-10pt o ajustar escala de impresión</td>
            </tr>
            <tr>
                <td>No imprime automáticamente</td>
                <td>Verificar que la impresora esté seleccionada como predeterminada</td>
            </tr>
            <tr>
                <td>Papel se corta mal</td>
                <td>Configurar "Auto Cut" en las propiedades de la impresora</td>
            </tr>
            <tr>
                <td>Calidad de impresión mala</td>
                <td>Limpiar cabezal térmico y verificar papel térmico</td>
            </tr>
        </table>
    </div>

    <div class="config-section">
        <h3>📞 Soporte Adicional</h3>
        <p>Si continúa teniendo problemas:</p>
        <ul>
            <li>Consulte el manual de su impresora térmica</li>
            <li>Verifique que el papel térmico sea de 80mm de ancho</li>
            <li>Actualice los drivers de la impresora</li>
            <li>Pruebe con diferentes navegadores (Chrome, Firefox, Edge)</li>
        </ul>
    </div>

    <div style="text-align: center; margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 5px;">
        <h4>🔗 Navegación Segura</h4>
        <p style="margin-bottom: 15px; color: #666;">Use estos enlaces para mantener su sesión activa:</p>
        <a href="facturacion.php" class="btn btn-primary" style="margin: 5px;">
            ⬅️ Volver a Facturación
        </a>
        <a href="verificar_pagos.php" class="btn btn-success" style="margin: 5px;">
            🔍 Verificar Estado del Sistema
        </a>
        <a href="test_impresion_termica_80mm.php" class="btn btn-primary" style="margin: 5px;">
            🧪 Test de Impresión
        </a>
        <a href="index.php" class="btn btn-secondary" style="margin: 5px;">
            🏠 Panel Principal
        </a>
        <br>
        <small style="color: #666; margin-top: 10px; display: block;">
            💡 Sesión activa: Usuario ID <?= $_SESSION['id'] ?> | Session: <?= substr(session_id(), 0, 8) ?>...
        </small>
    </div>

<script>
// ========== CONFIGURADOR INTERACTIVO DE RECIBOS ==========

let configuracionActual = {
    ancho: 80,
    alto: 'auto',
    fuente: 10,
    margenes: 0,
    orientacion: 'portrait'
};

// Inicializar al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    actualizarConfig();
});

function actualizarConfig() {
    // Obtener valores del formulario
    configuracionActual.ancho = document.getElementById('anchoRecibo').value;
    configuracionActual.alto = document.getElementById('altoRecibo').value;
    configuracionActual.fuente = document.getElementById('tamanoFuente').value;
    configuracionActual.margenes = document.getElementById('margenes').value;
    configuracionActual.orientacion = document.querySelector('input[name="orientacion"]:checked').value;
    
    // Actualizar vista previa
    actualizarVistaPrevia();
    
    // Generar CSS personalizado
    generarCSSPersonalizado();
}

function actualizarVistaPrevia() {
    const config = configuracionActual;
    const vistaPrevia = document.getElementById('vistaPrevia');
    
    let caracteresLinea = calcularCaracteresPorLinea(config.ancho, config.fuente);
    let altoTexto = config.alto === 'auto' ? 'Automático (Continuo)' : config.alto + 'mm';
    
    vistaPrevia.innerHTML = `
<strong>📐 Configuración Actual:</strong><br>
• Ancho del papel: ${config.ancho}mm<br>
• Alto del papel: ${altoTexto}<br>
• Tamaño de fuente: ${config.fuente}px<br>
• Márgenes: ${config.margenes}mm<br>
• Orientación: ${config.orientacion === 'portrait' ? 'Vertical' : 'Horizontal'}<br>
• Caracteres por línea: ~${caracteresLinea}<br>
• Área útil: ${config.ancho - (config.margenes * 2)}mm de ancho
    `;
}

function calcularCaracteresPorLinea(ancho, fuente) {
    // Cálculo aproximado basado en Courier New
    const caracterPorMM = {
        8: 4.5,
        9: 4.0,
        10: 3.6,
        11: 3.3,
        12: 3.0
    };
    
    const factorCaracter = caracterPorMM[fuente] || 3.6;
    return Math.floor((ancho - (configuracionActual.margenes * 2)) * factorCaracter);
}

function generarCSSPersonalizado() {
    const config = configuracionActual;
    const css = `/* CSS Personalizado para Recibo ${config.ancho}mm */
@media print {
    @page {
        size: ${config.ancho}mm ${config.alto === 'auto' ? 'auto' : config.alto + 'mm'};
        margin: ${config.margenes}mm;
        orientation: ${config.orientacion};
    }
    
    body {
        font-family: 'Courier New', monospace !important;
        font-size: ${config.fuente}px !important;
        line-height: 1.1 !important;
        color: black !important;
        background: white !important;
        width: ${config.ancho}mm !important;
        max-width: ${config.ancho}mm !important;
        margin: 0 !important;
        padding: ${config.margenes}mm !important;
    }
    
    .recibo {
        width: 100% !important;
        max-width: ${config.ancho - (config.margenes * 2)}mm !important;
        border: none !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    
    .linea {
        font-size: ${config.fuente}px !important;
        margin: 0.5mm 0 !important;
    }
    
    .total {
        font-size: ${Math.floor(config.fuente * 1.2)}px !important;
        text-align: center !important;
    }
}`;
    
    document.getElementById('cssPersonalizado').value = css;
}

function aplicarPreset(ancho, alto, fuente, margenes) {
    document.getElementById('anchoRecibo').value = ancho;
    document.getElementById('altoRecibo').value = alto;
    document.getElementById('tamanoFuente').value = fuente;
    document.getElementById('margenes').value = margenes;
    
    actualizarConfig();
    
    // Mostrar confirmación
    alert(`✅ Configuración aplicada:\n• Ancho: ${ancho}mm\n• Alto: ${alto}\n• Fuente: ${fuente}px\n• Márgenes: ${margenes}mm`);
}

function aplicarConfiguracion() {
    const config = configuracionActual;
    
    // Crear un archivo temporal con la nueva configuración
    const cssContent = document.getElementById('cssPersonalizado').value;
    
    // Guardar en localStorage para que otros archivos puedan usarlo
    localStorage.setItem('reciboConfig', JSON.stringify(config));
    localStorage.setItem('reciboCSS', cssContent);
    
    const mensaje = `✅ Configuración aplicada y guardada!\n\n` +
                   `Configuración actual:\n` +
                   `• Ancho: ${config.ancho}mm\n` +
                   `• Alto: ${config.alto}\n` +
                   `• Fuente: ${config.fuente}px\n` +
                   `• Márgenes: ${config.margenes}mm\n\n` +
                   `La configuración se aplicará automáticamente en los recibos.\n` +
                   `¿Desea hacer un test de impresión ahora?`;
    
    if (confirm(mensaje)) {
        window.open('test_impresion_termica_80mm.php', '_blank');
    }
}

function descargarCSS() {
    const cssContent = document.getElementById('cssPersonalizado').value;
    const config = configuracionActual;
    
    // Crear archivo CSS para descarga
    const blob = new Blob([cssContent], { type: 'text/css' });
    const url = window.URL.createObjectURL(blob);
    
    // Crear enlace de descarga
    const a = document.createElement('a');
    a.href = url;
    a.download = `recibo-${config.ancho}mm-config.css`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
    
    alert('📥 Archivo CSS descargado!\nPuede usar este CSS en otros proyectos.');
}

function resetearConfig() {
    if (confirm('¿Está seguro de que quiere resetear la configuración a los valores por defecto?')) {
        document.getElementById('anchoRecibo').value = '80';
        document.getElementById('altoRecibo').value = 'auto';
        document.getElementById('tamanoFuente').value = '10';
        document.getElementById('margenes').value = '0';
        document.querySelector('input[name="orientacion"][value="portrait"]').checked = true;
        
        // Limpiar localStorage
        localStorage.removeItem('reciboConfig');
        localStorage.removeItem('reciboCSS');
        
        actualizarConfig();
        alert('🔄 Configuración reseteada a valores por defecto.');
    }
}

// Cargar configuración guardada si existe
function cargarConfiguracionGuardada() {
    const configGuardada = localStorage.getItem('reciboConfig');
    if (configGuardada) {
        try {
            const config = JSON.parse(configGuardada);
            document.getElementById('anchoRecibo').value = config.ancho;
            document.getElementById('altoRecibo').value = config.alto;
            document.getElementById('tamanoFuente').value = config.fuente;
            document.getElementById('margenes').value = config.margenes;
            document.querySelector(`input[name="orientacion"][value="${config.orientacion}"]`).checked = true;
            actualizarConfig();
        } catch (e) {
            console.error('Error al cargar configuración guardada:', e);
        }
    }
}

// Cargar configuración al inicializar
document.addEventListener('DOMContentLoaded', function() {
    cargarConfiguracionGuardada();
});
</script>

</body>
</html>
