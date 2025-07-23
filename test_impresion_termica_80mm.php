<?php
require_once 'session_config.php';
session_start();
require_once 'config.php';

// Verificar autenticación
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    echo "❌ Usuario no autenticado. <a href='index.php'>Login</a>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Impresora Térmica 80mm</title>
    <style>
        /* ========== ESTILOS PARA PANTALLA ========== */
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px; 
            background: #f5f5f5; 
        }
        .test-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .recibo-test { 
            max-width: 320px; 
            margin: 20px auto; 
            border: 2px solid #000; 
            padding: 15px; 
            background: white;
            font-family: 'Courier New', monospace;
            font-size: 12px;
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
        .centrado { text-align: center; }
        .derecha { text-align: right; }
        .btn {
            padding: 10px 20px;
            margin: 5px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
        }
        .btn-success { background: #28a745; color: white; }
        .btn-primary { background: #007bff; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-warning { background: #ffc107; color: black; }
        
        /* ========== ESTILOS PARA IMPRESIÓN TÉRMICA 80MM ========== */
        @media print {
            .no-print { display: none !important; }
            
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
            
            .recibo-test {
                width: 100% !important;
                max-width: 78mm !important;
                border: none !important;
                padding: 0 !important;
                margin: 0 !important;
                background: white !important;
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
            
            .info-adicional {
                font-size: 8px !important;
                margin: 0.5mm 0 !important;
            }
        }
    <!-- Script para cargar configuración personalizada -->
    <script>
        // Cargar configuración personalizada si existe
        document.addEventListener('DOMContentLoaded', function() {
            const cssPersonalizado = localStorage.getItem('reciboCSS');
            const config = localStorage.getItem('reciboConfig');
            
            if (cssPersonalizado && config) {
                try {
                    const configObj = JSON.parse(config);
                    console.log('🔧 Aplicando configuración personalizada al test:', configObj);
                    
                    // Crear estilo personalizado
                    const style = document.createElement('style');
                    style.id = 'estilosPersonalizados';
                    style.textContent = cssPersonalizado;
                    document.head.appendChild(style);
                    
                    // Actualizar título
                    document.title = `Test Impresora Térmica ${configObj.ancho}mm`;
                    
                    // Mostrar indicador de configuración personalizada
                    const titulo = document.querySelector('h1');
                    if (titulo) {
                        titulo.innerHTML = `🧪 Test de Impresora Térmica ${configObj.ancho}mm (Personalizado)`;
                    }
                    
                    // Actualizar información en la página
                    const infoConfig = document.createElement('div');
                    infoConfig.className = 'no-print';
                    infoConfig.style.cssText = `
                        background: #d1ecf1; 
                        border: 1px solid #bee5eb; 
                        padding: 10px; 
                        border-radius: 5px; 
                        margin-bottom: 15px;
                    `;
                    infoConfig.innerHTML = `
                        <strong>⚙️ Configuración Personalizada Activa:</strong><br>
                        • Ancho: ${configObj.ancho}mm<br>
                        • Alto: ${configObj.alto === 'auto' ? 'Automático' : configObj.alto + 'mm'}<br>
                        • Fuente: ${configObj.fuente}px<br>
                        • Márgenes: ${configObj.margenes}mm<br>
                        • Orientación: ${configObj.orientacion === 'portrait' ? 'Vertical' : 'Horizontal'}
                    `;
                    
                    const container = document.querySelector('.test-container');
                    if (container) {
                        container.insertBefore(infoConfig, container.children[1]);
                    }
                    
                } catch (e) {
                    console.error('Error al cargar configuración personalizada en test:', e);
                }
            } else {
                console.log('📐 Test usando configuración por defecto (80mm)');
            }
        });
    </script>
    
</head>
<body>

<div class="test-container no-print">
    <h1>🧪 Test de Impresora Térmica 80mm</h1>
    
    <div style="background: #e3f2fd; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
        <h3>📋 Instrucciones del Test</h3>
        <ol>
            <li><strong>Configure su impresora:</strong> Asegúrese de que esté configurada para papel de 80mm</li>
            <li><strong>Revise el ejemplo:</strong> Vea cómo se ve el recibo en pantalla</li>
            <li><strong>Imprima el test:</strong> Use el botón "Imprimir Test" para verificar el formato</li>
            <li><strong>Compare resultados:</strong> El recibo impreso debe verse igual al ejemplo en pantalla</li>
        </ol>
    </div>

    <div style="text-align: center; margin-bottom: 20px;">
        <button onclick="imprimirTest()" class="btn btn-success">🖨️ Imprimir Test</button>
        <a href="configuracion_impresora_80mm.php" class="btn btn-primary">⚙️ Configuración</a>
        <a href="facturacion.php" class="btn btn-secondary">⬅️ Volver</a>
    </div>

    <h3>👁️ Vista Previa del Recibo (Optimizado para 80mm):</h3>
</div>

<!-- RECIBO DE TEST -->
<div class="recibo-test">
    <!-- Header del consultorio - Formato optimizado para 80mm -->
    <div class="header">
        <div class="logo">CONSULTORIO ODONTOLOGICO</div>
        <div class="centrado">RECIBO DE PAGO - TEST</div>
        <div class="centrado">=============================</div>
    </div>

    <!-- Información del recibo - Formato de dos columnas para 80mm -->
    <div class="linea">
        <span class="label">RECIBO #:</span>
        <span class="valor">000001</span>
    </div>

    <div class="linea">
        <span class="label">FECHA:</span>
        <span class="valor"><?= date('d/m/Y H:i') ?></span>
    </div>

    <div class="linea">
        <span class="label">FACTURA:</span>
        <span class="valor">FAC-0001</span>
    </div>

    <div class="separador">-----------------------------</div>

    <!-- Información del paciente -->
    <div class="linea-simple centrado"><strong>DATOS DEL PACIENTE</strong></div>
    <div class="linea-simple">NOMBRE: JUAN CARLOS PEREZ LOPEZ</div>
    <div class="linea-simple">CEDULA: 12.345.678-9</div>
    <div class="linea-simple">MEDICO: DR. MARIA RODRIGUEZ</div>

    <div class="separador">-----------------------------</div>

    <!-- Método de pago -->
    <div class="linea">
        <span class="label">METODO PAGO:</span>
        <span class="valor">EFECTIVO</span>
    </div>

    <div class="separador">-----------------------------</div>

    <!-- Procedimientos de ejemplo -->
    <div class="linea-simple centrado"><strong>SERVICIOS</strong></div>
    <div class="linea">
        <span class="label">CONSULTA GENERAL:</span>
        <span class="valor">$50.00</span>
    </div>
    <div class="linea">
        <span class="label">LIMPIEZA DENTAL:</span>
        <span class="valor">$80.00</span>
    </div>
    <div class="linea">
        <span class="label">RADIOGRAFIA:</span>
        <span class="valor">$25.00</span>
    </div>

    <div class="separador">=============================</div>

    <!-- Total - Destacado y centrado -->
    <div class="total">
        <div style="font-size: 12px; margin-bottom: 2mm;"><strong>MONTO TOTAL PAGADO</strong></div>
        <div style="font-size: 16px; font-weight: bold;">$155.00</div>
    </div>

    <div class="separador">=============================</div>

    <!-- Footer optimizado para 80mm -->
    <div class="centrado info-adicional">¡GRACIAS POR SU PAGO!</div>
    <div class="centrado info-adicional">Conserve este recibo</div>
    <div class="centrado info-adicional">Test generado: <?= date('d/m/Y H:i') ?></div>
    <div class="centrado info-adicional">Ancho: 80mm - Fuente: Courier 10px</div>

    <!-- Espacio adicional para corte del papel -->
    <div style="height: 10mm;"></div>
</div>

<div class="no-print" style="text-align: center; margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 5px;">
    <h3>✅ Verificación del Test</h3>
    <p>Después de imprimir, verifique que:</p>
    <ul style="text-align: left; display: inline-block;">
        <li>✅ El texto no se corta por los lados</li>
        <li>✅ La fuente es legible (no muy grande ni muy pequeña)</li>
        <li>✅ Las líneas están bien alineadas</li>
        <li>✅ El separador de líneas se ve correctamente</li>
        <li>✅ El total está centrado y destacado</li>
        <li>✅ No hay espacios en blanco excesivos</li>
    </ul>

    <div style="margin-top: 20px;">
        <button onclick="imprimirTest()" class="btn btn-success">🔄 Imprimir Nuevamente</button>
        <a href="imprimir_recibo_mejorado.php" class="btn btn-primary">📄 Ver Recibo Real</a>
        <a href="configuracion_impresora_80mm.php" class="btn btn-warning">⚙️ Ajustar Configuración</a>
    </div>

    <div style="margin-top: 15px; font-size: 12px; color: #666;">
        <em>💡 Si algo no se ve bien, use el enlace "Ajustar Configuración" para revisar la configuración de su impresora.</em>
    </div>
</div>

<script>
function imprimirTest() {
    console.log('🧪 Iniciando test de impresión térmica 80mm...');
    
    // Configuración específica para impresión térmica
    const configImpresion = {
        orientation: 'portrait',
        units: 'mm',
        format: [80, 200], // 80mm de ancho, 200mm de alto máximo
        border: {
            top: 0,
            right: 0,
            bottom: 0,
            left: 0
        }
    };
    
    // Mostrar configuraciones antes de imprimir
    console.log('Configuración de impresión:', configImpresion);
    
    // Mensaje de preparación
    const mensaje = '🖨️ Preparando impresión de test...\n\n' +
                   'Configuración:\n' +
                   '• Ancho: 80mm\n' +
                   '• Orientación: Vertical\n' +
                   '• Márgenes: 0mm\n' +
                   '• Fuente: Courier New 10px\n\n' +
                   '¿Continuar con la impresión?';
    
    if (confirm(mensaje)) {
        console.log('✅ Usuario confirmó impresión');
        window.print();
    } else {
        console.log('❌ Impresión cancelada por el usuario');
    }
}

// Auto-configurar al cargar la página
window.addEventListener('load', function() {
    console.log('🔧 Página de test cargada - Verificando configuración...');
    
    // Detectar si es una impresora térmica por el user agent o configuración
    const esPantallaEstrecha = window.innerWidth <= 400;
    if (esPantallaEstrecha) {
        console.log('📱 Pantalla estrecha detectada - Puede ser modo impresión');
    }
    
    // Log de las dimensiones actuales
    console.log('Dimensiones de pantalla:', {
        width: window.innerWidth,
        height: window.innerHeight,
        devicePixelRatio: window.devicePixelRatio
    });
});
</script>

</body>
</html>
