<?php
// Archivo de prueba para verificar la corrección de selección múltiple de dientes
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Corrección Final - Odontograma</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <style>
        body { padding: 20px; }
        .debug-panel {
            background-color: #f8f9fa;
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
        .test-case {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            background-color: #f9f9f9;
        }
        .test-case h5 {
            color: #007bff;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="my-4">Verificación Final de Odontograma</h1>
        
        <div class="alert alert-primary">
            <p><strong>Prueba de verificación para la selección múltiple de dientes en el odontograma.</strong></p>
            <p>Esta página incluye la versión corregida del odontograma con las siguientes mejoras:</p>
            <ul>
                <li>Corrección del manejo de selección múltiple de dientes</li>
                <li>Garantía de que todos los dientes seleccionados aparezcan en la lista</li>
                <li>Mejor compatibilidad entre el array de seleccionados y los elementos DOM</li>
            </ul>
        </div>
        
        <div class="debug-panel">
            <h4>Panel de Depuración</h4>
            <div class="debug-log" id="debug-log"></div>
            <div class="btn-toolbar">
                <button id="btn-check-state" class="btn btn-info btn-sm mr-2">Verificar Estado</button>
                <button id="btn-clear-log" class="btn btn-secondary btn-sm mr-2">Limpiar Log</button>
                <button id="btn-test-all" class="btn btn-primary btn-sm">Ejecutar Tests</button>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-8">
                <!-- Odontograma -->
                <div class="card mb-4">
                    <div class="card-body">
                        <?php include 'odontograma_svg_mejorado.php'; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <!-- Casos de prueba -->
                <h4>Casos de prueba</h4>
                
                <div class="test-case">
                    <h5>Test 1: Selección individual</h5>
                    <p>Seleccionar dientes uno por uno:</p>
                    <button class="btn btn-sm btn-outline-primary test-btn" data-test="seleccion-individual">Ejecutar</button>
                </div>
                
                <div class="test-case">
                    <h5>Test 2: Selección múltiple</h5>
                    <p>Seleccionar varios dientes con Ctrl:</p>
                    <button class="btn btn-sm btn-outline-primary test-btn" data-test="seleccion-multiple">Ejecutar</button>
                </div>
                
                <div class="test-case">
                    <h5>Test 3: Selección por cuadrante</h5>
                    <p>Seleccionar dientes usando botones de cuadrante:</p>
                    <button class="btn btn-sm btn-outline-primary test-btn" data-test="seleccion-cuadrante">Ejecutar</button>
                </div>
                
                <div class="test-case">
                    <h5>Test 4: Seleccionar/Deseleccionar todos</h5>
                    <p>Probar seleccionar/deseleccionar todos los dientes:</p>
                    <button class="btn btn-sm btn-outline-primary test-btn" data-test="seleccion-todos">Ejecutar</button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    // Función para agregar mensajes al log
    function logMessage(message) {
        const log = document.getElementById('debug-log');
        const timestamp = new Date().toLocaleTimeString();
        log.innerHTML += `<div>[${timestamp}] ${message}</div>`;
        log.scrollTop = log.scrollHeight;
    }
    
    // Función para simular un clic en un diente
    function clickDiente(numDiente, conCtrl = false) {
        logMessage(`Simulando clic ${conCtrl ? 'con Ctrl' : 'sin Ctrl'} en diente ${numDiente}`);
        
        const diente = document.querySelector(`.tooth-shape[data-num="${numDiente}"]`);
        if (!diente) {
            logMessage(`ERROR: No se encontró el diente ${numDiente}`);
            return false;
        }
        
        // Crear evento de clic con o sin Ctrl
        const event = new MouseEvent('click', {
            bubbles: true,
            cancelable: true,
            view: window,
            ctrlKey: conCtrl
        });
        
        // Disparar evento
        diente.dispatchEvent(event);
        logMessage(`Evento click enviado al diente ${numDiente}`);
        return true;
    }
    
    // Función para verificar el estado actual
    function verificarEstado() {
        logMessage('==== VERIFICANDO ESTADO ACTUAL ====');
        
        // Verificar dientes seleccionados en DOM
        const seleccionadosDom = [];
        document.querySelectorAll('.tooth-shape.tooth-selected').forEach(tooth => {
            seleccionadosDom.push(tooth.getAttribute('data-num'));
        });
        logMessage(`Dientes seleccionados en DOM: ${seleccionadosDom.length > 0 ? seleccionadosDom.join(', ') : 'ninguno'}`);
        
        // Verificar array de seleccionados
        const seleccionadosArray = window.seleccionados ? 
            (Array.isArray(window.seleccionados) ? 
                window.seleccionados.map(n => n.toString()) : 
                Array.from(window.seleccionados).map(n => n.toString())
            ) : [];
        logMessage(`Array window.seleccionados: ${seleccionadosArray.length > 0 ? seleccionadosArray.join(', ') : 'vacío'}`);
        
        // Verificar campo oculto
        const campoOculto = document.getElementById('dientes_seleccionados');
        logMessage(`Campo oculto: ${campoOculto && campoOculto.value ? campoOculto.value : 'vacío'}`);
        
        // Verificar lista HTML
        const listaHTML = document.getElementById('dientes-seleccionados-lista');
        const listaTexto = listaHTML ? listaHTML.textContent.replace(/\s+/g, ' ').trim() : '';
        logMessage(`Lista HTML: ${listaTexto.substring(0, 100)}${listaTexto.length > 100 ? '...' : ''}`);
        
        // Verificar consistencia
        const domOrdenados = [...seleccionadosDom].sort();
        const arrayOrdenados = [...seleccionadosArray].sort();
        const consistente = JSON.stringify(domOrdenados) === JSON.stringify(arrayOrdenados);
        
        logMessage(`Consistencia DOM vs Array: ${consistente ? 'OK ✓' : 'NO COINCIDEN ✗'}`);
        if (!consistente) {
            logMessage('DIFERENCIA DETECTADA entre DOM y Array!');
        }
        
        return consistente;
    }
    
    // Casos de prueba
    const tests = {
        'seleccion-individual': function() {
            logMessage('TEST: Selección Individual - Iniciando...');
            
            // Primero deseleccionamos todo
            document.getElementById('btn-deseleccionar-todos-svg').click();
            
            // Luego seleccionamos dientes individuales
            setTimeout(() => {
                clickDiente(11);
                setTimeout(() => {
                    clickDiente(21);
                    setTimeout(() => {
                        clickDiente(31);
                        setTimeout(() => {
                            clickDiente(41);
                            setTimeout(verificarEstado, 100);
                        }, 300);
                    }, 300);
                }, 300);
            }, 300);
        },
        
        'seleccion-multiple': function() {
            logMessage('TEST: Selección Múltiple - Iniciando...');
            
            // Primero deseleccionamos todo
            document.getElementById('btn-deseleccionar-todos-svg').click();
            
            // Luego seleccionamos múltiples dientes con Ctrl
            setTimeout(() => {
                clickDiente(18);
                setTimeout(() => {
                    clickDiente(16, true); // Con Ctrl
                    setTimeout(() => {
                        clickDiente(26, true); // Con Ctrl
                        setTimeout(() => {
                            clickDiente(36, true); // Con Ctrl
                            setTimeout(() => {
                                clickDiente(46, true); // Con Ctrl
                                setTimeout(verificarEstado, 100);
                            }, 300);
                        }, 300);
                    }, 300);
                }, 300);
            }, 300);
        },
        
        'seleccion-cuadrante': function() {
            logMessage('TEST: Selección por Cuadrante - Iniciando...');
            
            // Primero deseleccionamos todo
            document.getElementById('btn-deseleccionar-todos-svg').click();
            
            // Luego seleccionamos un cuadrante
            setTimeout(() => {
                document.getElementById('btn-q1').click();
                setTimeout(() => {
                    verificarEstado();
                    setTimeout(() => {
                        document.getElementById('btn-q3').click();
                        setTimeout(verificarEstado, 100);
                    }, 300);
                }, 300);
            }, 300);
        },
        
        'seleccion-todos': function() {
            logMessage('TEST: Seleccionar/Deseleccionar Todos - Iniciando...');
            
            // Primero seleccionamos todos
            document.getElementById('btn-seleccionar-todos-svg').click();
            setTimeout(() => {
                verificarEstado();
                // Luego deseleccionamos todos
                setTimeout(() => {
                    document.getElementById('btn-deseleccionar-todos-svg').click();
                    setTimeout(verificarEstado, 100);
                }, 300);
            }, 300);
        }
    };
    
    // Configurar botones al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        logMessage('Página cargada - Esperando acciones del usuario');
        
        // Botón verificar estado
        document.getElementById('btn-check-state').addEventListener('click', verificarEstado);
        
        // Botón limpiar log
        document.getElementById('btn-clear-log').addEventListener('click', function() {
            document.getElementById('debug-log').innerHTML = '';
            logMessage('Log limpiado');
        });
        
        // Botón ejecutar todos los tests
        document.getElementById('btn-test-all').addEventListener('click', function() {
            logMessage('Ejecutando todos los tests secuencialmente...');
            
            const testButtons = document.querySelectorAll('.test-btn');
            let index = 0;
            
            function ejecutarSiguiente() {
                if (index < testButtons.length) {
                    const testBtn = testButtons[index];
                    const testName = testBtn.getAttribute('data-test');
                    
                    logMessage(`Ejecutando test: ${testName}`);
                    tests[testName]();
                    
                    index++;
                    setTimeout(ejecutarSiguiente, 2500); // 2.5 segundos entre tests
                } else {
                    logMessage('Todos los tests completados');
                }
            }
            
            ejecutarSiguiente();
        });
        
        // Configurar botones de test individual
        document.querySelectorAll('.test-btn').forEach(button => {
            button.addEventListener('click', function() {
                const testName = this.getAttribute('data-test');
                if (tests[testName]) {
                    tests[testName]();
                } else {
                    logMessage(`Error: Test '${testName}' no encontrado`);
                }
            });
        });
        
        // Verificar estado inicial
        setTimeout(verificarEstado, 500);
    });
    </script>
    
    <!-- Incluir verificador de selección -->
    <script src="verificador_seleccion_dientes.js"></script>
</body>
</html>

