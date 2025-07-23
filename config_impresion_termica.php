<?php
/**
 * Configuración para Impresión Térmica
 * Este archivo contiene configuraciones específicas para impresoras térmicas de 80mm
 */

// Configuración de impresora térmica
$config_impresora = [
    // Dimensiones de impresora térmica estándar 80mm
    'ancho_papel' => '80mm',
    'ancho_caracteres' => 42, // Caracteres por línea en fuente normal
    'margen' => '2mm',
    
    // Configuraciones de formato
    'fuente' => 'Courier New, monospace',
    'tamaño_fuente_normal' => '10px',
    'tamaño_fuente_titulo' => '12px',
    'espaciado_linea' => '1.2',
    
    // Configuraciones específicas de recibo
    'mostrar_logo' => false, // Deshabilitado para simplicidad
    'incluir_qr' => false,   // Podría implementarse más adelante
    'auto_corte' => true,    // Si la impresora soporta auto-corte
    
    // Comandos ESC/POS básicos (para impresoras que los soporten)
    'cmd_inicializar' => "\x1B\x40",        // ESC @
    'cmd_corte' => "\x1D\x56\x41\x03",      // GS V A 3
    'cmd_texto_normal' => "\x1B\x21\x00",   // ESC ! 0
    'cmd_texto_negrita' => "\x1B\x21\x08",  // ESC ! 8
    'cmd_centrar' => "\x1B\x61\x01",        // ESC a 1
    'cmd_izquierda' => "\x1B\x61\x00",      // ESC a 0
];

/**
 * Función para generar línea de separación
 */
function lineaSeparacion($caracter = '-', $longitud = 42) {
    return str_repeat($caracter, $longitud);
}

/**
 * Función para centrar texto
 */
function centrarTexto($texto, $ancho = 42) {
    $longitud = strlen($texto);
    if ($longitud >= $ancho) {
        return $texto;
    }
    
    $espacios = floor(($ancho - $longitud) / 2);
    return str_repeat(' ', $espacios) . $texto;
}

/**
 * Función para justificar texto (izquierda y derecha)
 */
function justificarTexto($izquierda, $derecha, $ancho = 42) {
    $longitud_total = strlen($izquierda) + strlen($derecha);
    if ($longitud_total >= $ancho) {
        return $izquierda . ' ' . $derecha;
    }
    
    $espacios = $ancho - $longitud_total;
    return $izquierda . str_repeat(' ', $espacios) . $derecha;
}

/**
 * CSS optimizado para impresión térmica
 */
function getCSSImpresionTermica() {
    return "
    /* Reset completo para impresión térmica */
    @media print {
        @page {
            size: 80mm auto;
            margin: 0;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Courier New', monospace;
            font-size: 10px;
            line-height: 1.2;
            color: black;
            background: white;
            width: 80mm;
            padding: 2mm;
        }
        
        .recibo {
            width: 100%;
            border: none;
            padding: 0;
            margin: 0;
        }
        
        .header {
            text-align: center;
            margin-bottom: 3mm;
            padding-bottom: 2mm;
            border-bottom: 1px dashed black;
        }
        
        .linea {
            margin: 1mm 0;
            word-wrap: break-word;
        }
        
        .total {
            font-weight: bold;
            text-align: center;
            margin-top: 3mm;
            padding-top: 2mm;
            border-top: 1px dashed black;
            font-size: 11px;
        }
        
        .separador {
            text-align: center;
            margin: 2mm 0;
        }
        
        .no-print {
            display: none !important;
        }
    }
    ";
}

/**
 * JavaScript para manejo avanzado de impresión
 */
function getJavaScriptImpresion() {
    return "
    // Configuración de impresión térmica
    const configImpresion = {
        reintentos: 3,
        delayInicial: 500,
        delayReintentos: 1000
    };
    
    let intentosRealizados = 0;
    
    function ejecutarImpresion() {
        console.log('Ejecutando impresión - Intento:', intentosRealizados + 1);
        
        try {
            // Para navegadores que soportan la API de impresión
            if (window.print) {
                window.print();
                return true;
            }
        } catch (error) {
            console.error('Error en window.print():', error);
            return false;
        }
        
        return false;
    }
    
    function imprimirConReintentos() {
        if (intentosRealizados >= configImpresion.reintentos) {
            console.log('Máximo de reintentos alcanzado');
            mostrarMensajeError();
            return;
        }
        
        const exito = ejecutarImpresion();
        intentosRealizados++;
        
        if (!exito && intentosRealizados < configImpresion.reintentos) {
            console.log('Reintentando impresión en', configImpresion.delayReintentos, 'ms');
            setTimeout(imprimirConReintentos, configImpresion.delayReintentos);
        }
    }
    
    function mostrarMensajeError() {
        const mensaje = document.getElementById('mensajeImpresion');
        if (mensaje) {
            mensaje.innerHTML = '⚠️ No se pudo imprimir automáticamente. Use el botón manual.';
            mensaje.style.background = '#fff3cd';
        }
    }
    ";
}
?>
