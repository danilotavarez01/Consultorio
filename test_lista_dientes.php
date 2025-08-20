<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba de Odontograma - Listado de Dientes</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <style>
        body {
            padding: 20px;
            font-family: Arial, sans-serif;
        }
        
        .debug-panel {
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
        }
        
        .log {
            background: #f5f5f5;
            border: 1px solid #e0e0e0;
            padding: 10px;
            height: 150px;
            overflow-y: auto;
            font-family: monospace;
            margin-bottom: 10px;
        }
        
        #dientes-reales {
            background: #fff;
            border: 2px dashed #28a745;
            padding: 15px;
            margin-top: 15px;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">Prueba de Odontograma - Listado de Dientes</h1>
        
        <div class="alert alert-info">
            <p>Esta página es para verificar específicamente el problema de la lista de dientes seleccionados.</p>
            <p>Instrucciones:</p>
            <ol>
                <li>Seleccione varios dientes en el odontograma (puede usar Ctrl+clic para selección múltiple)</li>
                <li>Verifique que los dientes aparezcan en la lista de dientes seleccionados debajo del odontograma</li>
                <li>Use los botones de depuración para mostrar información sobre la selección actual</li>
            </ol>
        </div>
        
        <div class="debug-panel">
            <h4>Panel de Depuración</h4>
            <div class="log" id="log"></div>
            <div class="btn-toolbar">
                <button id="btn-debug" class="btn btn-sm btn-info mr-2">Mostrar Estado Actual</button>
                <button id="btn-clear" class="btn btn-sm btn-secondary mr-2">Limpiar Log</button>
                <button id="btn-fix-list" class="btn btn-sm btn-warning">Forzar Actualización de Lista</button>
            </div>
        </div>
        
        <!-- Contenedor del odontograma -->
        <div class="card mb-4">
            <div class="card-body">
                <?php include 'odontograma_svg_mejorado.php'; ?>
            </div>
        </div>
        
        <!-- Panel para mostrar los dientes seleccionados (independiente del que tiene el odontograma) -->
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Verificación de dientes seleccionados</h5>
            </div>
            <div class="card-body">
                <h6>Value del campo hidden:</h6>
                <pre id="hidden-value" class="p-2 bg-light">(vacío)</pre>
                
                <h6>Contenido del array window.seleccionados:</h6>
                <pre id="array-content" class="p-2 bg-light">(vacío)</pre>
                
                <h6>Dientes con clase "tooth-selected":</h6>
                <div id="selected-elements" class="p-2 bg-light">(ninguno)</div>
                
                <h6>Lo que debería verse en la lista:</h6>
                <div id="dientes-reales" class="mt-3">
                    <p>Aquí se mostrarán los dientes que deberían aparecer en la lista</p>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Función para añadir mensaje al log
        function logMessage(message) {
            const log = document.getElementById('log');
            const timestamp = new Date().toLocaleTimeString();
            log.innerHTML += `<div>[${timestamp}] ${message}</div>`;
            log.scrollTop = log.scrollHeight;
        }
        
        // Actualizar los paneles con datos reales
        function updateDebugPanels() {
            // Campo oculto
            const hiddenField = document.getElementById('dientes_seleccionados');
            const hiddenValue = document.getElementById('hidden-value');
            if (hiddenField) {
                hiddenValue.textContent = hiddenField.value || '(vacío)';
            } else {
                hiddenValue.textContent = 'Campo no encontrado';
            }
            
            // Array de seleccionados
            const arrayContent = document.getElementById('array-content');
            if (window.seleccionados) {
                arrayContent.textContent = JSON.stringify(window.seleccionados) || '[]';
            } else {
                arrayContent.textContent = 'Array no definido';
            }
            
            // Elementos con clase tooth-selected
            const selectedElements = document.querySelectorAll('.tooth-shape.tooth-selected');
            const selectedElementsDiv = document.getElementById('selected-elements');
            if (selectedElements.length > 0) {
                const nums = [];
                selectedElements.forEach(el => {
                    const num = el.getAttribute('data-num');
                    if (num) nums.push(num);
                });
                selectedElementsDiv.textContent = nums.join(', ');
            } else {
                selectedElementsDiv.textContent = '(ninguno)';
            }
            
            // Lo que debería verse
            renderRealDientes();
        }
        
        // Renderizar los dientes que deberían estar seleccionados
        function renderRealDientes() {
            const dientesReales = document.getElementById('dientes-reales');
            
            if (!window.seleccionados || window.seleccionados.length === 0) {
                dientesReales.innerHTML = '<p>No hay dientes seleccionados</p>';
                return;
            }
            
            // Copiar la misma lógica de agrupación por cuadrantes
            const seleccionadosArr = [...window.seleccionados].sort((a, b) => a - b);
            
            const cuadrante1 = seleccionadosArr.filter(n => n >= 11 && n <= 18).sort((a, b) => a - b);
            const cuadrante2 = seleccionadosArr.filter(n => n >= 21 && n <= 28).sort((a, b) => a - b);
            const cuadrante3 = seleccionadosArr.filter(n => n >= 31 && n <= 38).sort((a, b) => a - b);
            const cuadrante4 = seleccionadosArr.filter(n => n >= 41 && n <= 48).sort((a, b) => a - b);
            
            // Función para generar el HTML de un grupo
            function renderGrupo(nums, nombre) {
                if (nums.length === 0) return '';
                
                return `
                    <div class="mb-2">
                        <span class="font-weight-bold">${nombre}:</span>
                        ${nums.map(n => `<span class="badge badge-success mr-1">${n}</span>`).join('')}
                    </div>
                `;
            }
            
            let html = '';
            html += renderGrupo(cuadrante1, 'Cuadrante 1 (Superior Derecho)');
            html += renderGrupo(cuadrante2, 'Cuadrante 2 (Superior Izquierdo)');
            html += renderGrupo(cuadrante3, 'Cuadrante 3 (Inferior Izquierdo)');
            html += renderGrupo(cuadrante4, 'Cuadrante 4 (Inferior Derecho)');
            
            // Si hay dientes que no pertenecen a ningún cuadrante
            const otros = seleccionadosArr.filter(n => 
                !(n >= 11 && n <= 18) && 
                !(n >= 21 && n <= 28) && 
                !(n >= 31 && n <= 38) && 
                !(n >= 41 && n <= 48)
            );
            
            if (otros.length > 0) {
                html += renderGrupo(otros, 'Otros');
            }
            
            if (html) {
                dientesReales.innerHTML = html;
            } else {
                dientesReales.innerHTML = '<p>No hay dientes seleccionados</p>';
            }
        }
        
        // Forzar la actualización de la lista de dientes
        function forceListUpdate() {
            logMessage('Forzando actualización de lista de dientes');
            
            const listaHTML = document.getElementById('dientes-seleccionados-lista');
            if (!listaHTML) {
                logMessage('ERROR: No se encontró el elemento dientes-seleccionados-lista');
                return;
            }
            
            // Limpiar la lista actual
            listaHTML.innerHTML = '';
            
            if (!window.seleccionados || window.seleccionados.length === 0) {
                listaHTML.innerHTML = '<span style="color: #777;" id="seleccionados-texto">Ninguno seleccionado</span>';
                logMessage('Lista vacía, mostrando "Ninguno seleccionado"');
                return;
            }
            
            // Reconstruir la lista de dientes similar a la función updateSeleccionados()
            const seleccionadosArr = [...window.seleccionados].sort((a, b) => a - b);
            
            const cuadrante1 = seleccionadosArr.filter(n => n >= 11 && n <= 18).sort((a, b) => a - b);
            const cuadrante2 = seleccionadosArr.filter(n => n >= 21 && n <= 28).sort((a, b) => a - b);
            const cuadrante3 = seleccionadosArr.filter(n => n >= 31 && n <= 38).sort((a, b) => a - b);
            const cuadrante4 = seleccionadosArr.filter(n => n >= 41 && n <= 48).sort((a, b) => a - b);
            
            // Función para añadir un grupo
            function agregarGrupo(nums, nombre) {
                if (nums.length > 0) {
                    return `
                        <div style="margin-bottom: 8px;">
                            <span style="color: #666; font-size: 14px; margin-right: 6px;">${nombre}:</span>
                            ${nums.map(n => `<span class="badge badge-primary mr-1" style="background: #007bff; color: white; padding: 3px 6px; border-radius: 4px; display: inline-block; margin-right: 4px;">${n}</span>`).join('')}
                        </div>`;
                }
                return '';
            }
            
            let html = '';
            html += agregarGrupo(cuadrante1, 'Cuadrante 1 (Superior Derecho)');
            html += agregarGrupo(cuadrante2, 'Cuadrante 2 (Superior Izquierdo)');
            html += agregarGrupo(cuadrante3, 'Cuadrante 3 (Inferior Izquierdo)');
            html += agregarGrupo(cuadrante4, 'Cuadrante 4 (Inferior Derecho)');
            
            // Otros dientes que no pertenecen a cuadrantes regulares
            const otros = seleccionadosArr.filter(n => 
                !(n >= 11 && n <= 18) && 
                !(n >= 21 && n <= 28) && 
                !(n >= 31 && n <= 38) && 
                !(n >= 41 && n <= 48)
            );
            
            if (otros.length > 0) {
                html += agregarGrupo(otros, 'Otros');
            }
            
            // Establecer el HTML en la lista
            listaHTML.innerHTML = html;
            logMessage(`Lista actualizada con ${seleccionadosArr.length} dientes`);
        }
        
        // Cuando la página esté cargada, configurar los botones
        document.addEventListener('DOMContentLoaded', function() {
            logMessage('Página cargada - Esperando acciones del usuario');
            
            // Actualizar los paneles de depuración
            setTimeout(updateDebugPanels, 500);
            
            // Configurar botones
            document.getElementById('btn-debug').addEventListener('click', function() {
                logMessage('Verificando estado actual...');
                updateDebugPanels();
            });
            
            document.getElementById('btn-clear').addEventListener('click', function() {
                document.getElementById('log').innerHTML = '<div>Log limpiado</div>';
            });
            
            document.getElementById('btn-fix-list').addEventListener('click', function() {
                forceListUpdate();
                updateDebugPanels();
            });
            
            // Observar cambios en la selección de dientes
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                        const target = mutation.target;
                        if (target.classList.contains('tooth-selected') || 
                            (target.classList.contains('tooth-shape') && !target.classList.contains('tooth-selected'))) {
                            logMessage('Cambio detectado en selección de dientes');
                            setTimeout(updateDebugPanels, 100);
                        }
                    }
                });
            });
            
            // Observar todos los dientes
            document.querySelectorAll('.tooth-shape').forEach(function(tooth) {
                observer.observe(tooth, { attributes: true });
            });
        });
    </script>
</body>
</html>

