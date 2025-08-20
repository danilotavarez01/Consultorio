<?php
// Archivo de prueba para verificar la corrección de selección múltiple de dientes
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Corrección de Selección Múltiple</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <style>
        body { padding: 20px; }
        .debug-panel {
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-top: 20px;
            margin-bottom: 20px;
        }
        .debug-log {
            height: 200px;
            overflow-y: auto;
            border: 1px solid #ccc;
            padding: 10px;
            font-family: monospace;
            background-color: #fff;
            margin-bottom: 10px;
        }
        .test-panel {
            margin-top: 20px;
            padding: 15px;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            background-color: #fafafa;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="my-4">Test de Corrección de Selección Múltiple</h1>
        
        <div class="alert alert-info">
            <p>Este archivo de prueba verifica específicamente la corrección del problema de selección múltiple de dientes en el odontograma.</p>
            <p>Problemas que debe resolver:</p>
            <ul>
                <li>Verificar que la selección múltiple funcione correctamente (con tecla Ctrl/Cmd)</li>
                <li>Asegurar que todos los dientes seleccionados aparezcan en la lista de dientes seleccionados</li>
                <li>Confirmar que el valor del campo oculto contenga todos los dientes seleccionados</li>
            </ul>
        </div>
        
        <div class="debug-panel">
            <h4>Panel de Depuración</h4>
            <div class="debug-log" id="debug-log"></div>
            <div class="form-row">
                <div class="col">
                    <button id="btn-check-state" class="btn btn-info btn-sm">Verificar Estado</button>
                    <button id="btn-clear-log" class="btn btn-secondary btn-sm ml-2">Limpiar Log</button>
                </div>
            </div>
        </div>
        
        <!-- Odontograma con selección múltiple -->
        <div id="odontograma-container" class="my-4">
            <?php 
            // Incluir el odontograma mediante un include para asegurar que se carga completamente
            include 'odontograma_svg_mejorado.php'; 
            ?>
        </div>
        
        <div class="test-panel">
            <h4>Pruebas de Selección</h4>
            <p>Seleccione los siguientes dientes para verificar:</p>
            
            <button class="btn btn-outline-primary btn-sm m-1" onclick="seleccionarConCtrl(18)">18</button>
            <button class="btn btn-outline-primary btn-sm m-1" onclick="seleccionarConCtrl(16)">16</button>
            <button class="btn btn-outline-primary btn-sm m-1" onclick="seleccionarConCtrl(21)">21</button>
            <button class="btn btn-outline-primary btn-sm m-1" onclick="seleccionarConCtrl(26)">26</button>
            <button class="btn btn-outline-primary btn-sm m-1" onclick="seleccionarConCtrl(31)">31</button>
            <button class="btn btn-outline-primary btn-sm m-1" onclick="seleccionarConCtrl(36)">36</button>
            <button class="btn btn-outline-primary btn-sm m-1" onclick="seleccionarConCtrl(41)">41</button>
            <button class="btn btn-outline-primary btn-sm m-1" onclick="seleccionarConCtrl(46)">46</button>
            
            <hr>
            <p>Ver resultados:</p>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Valor del campo oculto:</label>
                        <input type="text" id="campo-valor" class="form-control" readonly>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Valor del Set window.seleccionados:</label>
                        <input type="text" id="set-valor" class="form-control" readonly>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Función para escribir en el log de depuración
    function log(message) {
        const logElement = document.getElementById('debug-log');
        const timestamp = new Date().toLocaleTimeString();
        logElement.innerHTML += `<div>[${timestamp}] ${message}</div>`;
        logElement.scrollTop = logElement.scrollHeight;
    }
    
    // Función para seleccionar un diente con la tecla Ctrl simulada
    function seleccionarConCtrl(numDiente) {
        log(`Seleccionando diente ${numDiente} con Ctrl`);
        
        // Encontrar el diente
        const diente = document.querySelector(`.tooth-shape[data-num="${numDiente}"]`);
        
        if (diente) {
            // Crear un evento de clic con Ctrl presionado
            const event = new MouseEvent('click', {
                bubbles: true,
                cancelable: true,
                view: window,
                ctrlKey: true
            });
            
            // Disparar el evento
            diente.dispatchEvent(event);
            log(`Evento enviado al diente ${numDiente}`);
            
            // Actualizar los campos de valor
            setTimeout(actualizarCamposValor, 100);
        } else {
            log(`ERROR: No se encontró el diente ${numDiente}`);
        }
    }
    
    // Función para actualizar los campos de valor
    function actualizarCamposValor() {
        const campoValor = document.getElementById('campo-valor');
        const setValor = document.getElementById('set-valor');
        
        if (document.getElementById('dientes_seleccionados')) {
            campoValor.value = document.getElementById('dientes_seleccionados').value;
        }
        
        if (window.seleccionados) {
            setValor.value = Array.from(window.seleccionados).join(', ');
        }
    }
    
    // Configurar botones de depuración
    document.addEventListener('DOMContentLoaded', function() {
        log('Página de prueba cargada');
        
        document.getElementById('btn-check-state').addEventListener('click', function() {
            log('Verificando estado actual:');
            
            // Verificar el Set de seleccionados
            if (window.seleccionados) {
                const selArr = Array.from(window.seleccionados);
                log(`- Set window.seleccionados: ${selArr.length} dientes - [${selArr.join(', ')}]`);
            } else {
                log('- ERROR: window.seleccionados no está definido');
            }
            
            // Verificar el campo oculto
            const inputField = document.getElementById('dientes_seleccionados');
            if (inputField) {
                log(`- Campo oculto valor: "${inputField.value}"`);
            } else {
                log('- ERROR: Campo dientes_seleccionados no encontrado');
            }
            
            // Verificar elementos DOM con clase tooth-selected
            const selectedElements = document.querySelectorAll('.tooth-selected');
            const selectedNums = [];
            selectedElements.forEach(el => {
                selectedNums.push(el.getAttribute('data-num'));
            });
            
            log(`- Elementos DOM seleccionados: ${selectedNums.length} - [${selectedNums.join(', ')}]`);
            
            // Verificar la lista visual
            const listaHTML = document.getElementById('dientes-seleccionados-lista');
            if (listaHTML) {
                const listaText = listaHTML.textContent.replace(/\s+/g, ' ').trim();
                log(`- Lista visual muestra: "${listaText.substring(0, 100)}${listaText.length > 100 ? '...' : ''}"`);
            } else {
                log('- ERROR: Lista visual no encontrada');
            }
            
            actualizarCamposValor();
        });
        
        document.getElementById('btn-clear-log').addEventListener('click', function() {
            document.getElementById('debug-log').innerHTML = '<div>Log limpiado</div>';
        });
        
        // Verificar estado inicial
        setTimeout(function() {
            log('Verificando estado inicial:');
            actualizarCamposValor();
        }, 500);
    });
    </script>
</body>
</html>

