// Debug console para consulta_dinamica_simple.js
// Este código se añade al final del consulta_dinamica_simple.js para ayudar con depuración

// Agregar un ID a la consola para saber qué versión está ejecutando
console.log('Versión de consulta_dinamica_simple.js: 2023-06-20 RESILIENTE');

// Prueba básica para verificar que jQuery está cargado correctamente
$(document).ready(function() {
    console.log('jQuery está funcionando correctamente. Versión: ' + $.fn.jquery);
});

// Función para mostrar errores en la interfaz si está en modo depuración
function mostrarErrorEnInterfaz(mensaje, detalles) {
    // Solo mostrar en desarrollo - quitar esta línea para producción
    if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
        const errorBox = $('<div class="alert alert-danger mt-3 mb-3"></div>')
            .html('<strong>Error de depuración:</strong> ' + mensaje + '<br><pre>' + detalles + '</pre>');
        
        // Crear botón para ocultar
        const hideBtn = $('<button type="button" class="close">&times;</button>')
            .on('click', function() {
                errorBox.remove();
            });
            
        errorBox.prepend(hideBtn);
        
        // Agregar al final de la página
        $('#campos_dinamicos').after(errorBox);
    }
}

// Una utilidad para verificar endpoints
function verificarEndpoint(url) {
    console.log('Verificando disponibilidad del endpoint: ' + url);
    
    // Intentar con HEAD primero (es más rápido)
    $.ajax({
        url: url,
        type: 'HEAD',
        timeout: 2000,
        complete: function(xhr) {
            const existe = (xhr.status < 400);
            console.log('Endpoint ' + url + ' existe: ' + existe + ' (HEAD)');
            console.log('Estado HTTP: ' + xhr.status);
            
            // Si el HEAD falló, intentar con GET por si acaso
            if (!existe) {
                console.log('Intentando acceder con GET ya que HEAD falló');
                $.ajax({
                    url: url,
                    type: 'GET',
                    timeout: 3000,
                    success: function() {
                        console.log('Endpoint ' + url + ' existe: true (GET)');
                    },
                    error: function(xhr) {
                        console.log('Endpoint ' + url + ' falló también con GET. Estado: ' + xhr.status);
                        
                        // Si también falla GET, añadir un diagnóstico a la página si estamos en una página de consulta
                        if ($('#campos_dinamicos').length) {
                            if (!$('#diagnostico-endpoints').length) {
                                const infoDebug = $('<div id="diagnostico-endpoints" class="alert alert-info mt-3" style="display:none;"></div>')
                                    .html('<strong>Diagnóstico:</strong> Problemas detectados con el endpoint ' + url);
                                
                                $('#campos_dinamicos').after(infoDebug);
                                
                                // Añadir botón para ejecutar diagnóstico completo
                                const btnDiag = $('<button class="btn btn-sm btn-info mt-2">Ejecutar diagnóstico completo</button>')
                                    .on('click', function() {
                                        window.open('diagnostico_campos_dinamicos.php', '_blank');
                                    });
                                
                                infoDebug.append('<br>').append(btnDiag);
                            }
                        }
                    }
                });
            }
        }
    });
}

// Crear panel flotante de diagnóstico
function crearPanelDiagnostico() {
    // Solo crear si estamos en una página de consulta y no existe ya
    if ($('#campos_dinamicos').length && !$('#debug-panel').length) {
        const panel = $('<div id="debug-panel" class="card" style="position:fixed; bottom:10px; right:10px; z-index:9999; width:300px; display:none;"></div>');
        const header = $('<div class="card-header bg-info text-white py-1">Diagnóstico de campos</div>');
        const body = $('<div class="card-body p-2" style="max-height:200px; overflow-y:auto;"></div>');
        const footer = $('<div class="card-footer p-1 text-center"></div>');
        
        // Añadir botones
        const btnTest = $('<button class="btn btn-sm btn-outline-primary mr-2">Probar endpoints</button>').on('click', verificarTodosEndpoints);
        const btnFix = $('<button class="btn btn-sm btn-success">Reparación automática</button>').on('click', function() {
            window.location.href = 'diagnostico_campos_dinamicos.php?action=autorepair';
        });
        
        footer.append(btnTest).append(btnFix);
        panel.append(header).append(body).append(footer);
        $('body').append(panel);
        
        // Añadir botón para mostrar el panel en algún lugar visible
        const btnShow = $('<button id="show-debug" class="btn btn-sm btn-info" style="position:fixed; bottom:10px; right:10px; z-index:9998;">Herramientas</button>')
            .on('click', function() {
                $('#debug-panel').toggle();
                $(this).hide();
            });
            
        $('body').append(btnShow);
        
        // Añadir botón de cerrar al panel
        const btnClose = $('<button class="close" style="font-size:1.2rem;">&times;</button>').on('click', function() {
            $('#debug-panel').hide();
            $('#show-debug').show();
        });
        
        header.append(btnClose);
    }
}

// Verificar todos los endpoints y mostrar resultados en el panel
function verificarTodosEndpoints() {
    const endpoints = [
        'get_campos_especialidad_nuevo.php',
        'get_campos_simple.php',
        'get_campos_emergencia.php',
        'diagnostico_campos_dinamicos.php'
    ];
    
    $('#debug-panel .card-body').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Verificando...</div>');
    
    let resultados = {};
    let pendientes = endpoints.length;
    
    endpoints.forEach(function(url) {
        $.ajax({
            url: url,
            type: 'HEAD',
            timeout: 2000,
            complete: function(xhr) {
                resultados[url] = {
                    status: xhr.status,
                    ok: (xhr.status >= 200 && xhr.status < 400)
                };
                
                pendientes--;
                if (pendientes === 0) {
                    mostrarResultadosEndpoints(resultados);
                }
            }
        });
    });
}

// Mostrar resultados de la verificación
function mostrarResultadosEndpoints(resultados) {
    const body = $('#debug-panel .card-body');
    body.empty();
    
    const table = $('<table class="table table-sm table-bordered mb-0"></table>');
    const thead = $('<thead class="thead-light"></thead>').append('<tr><th>Endpoint</th><th>Estado</th></tr>');
    const tbody = $('<tbody></tbody>');
    
    let todosOk = true;
    
    Object.entries(resultados).forEach(function([url, result]) {
        const row = $('<tr></tr>');
        row.append('<td>' + url + '</td>');
        
        if (result.ok) {
            row.append('<td class="text-success">OK (' + result.status + ')</td>');
        } else {
            todosOk = false;
            row.append('<td class="text-danger">Error (' + result.status + ')</td>');
        }
        
        tbody.append(row);
    });
    
    table.append(thead).append(tbody);
    body.append(table);
    
    if (!todosOk) {
        body.append('<div class="alert alert-warning mt-2 mb-0 py-1">Se detectaron problemas con algunos endpoints.</div>');
    } else {
        body.append('<div class="alert alert-success mt-2 mb-0 py-1">¡Todos los endpoints funcionan correctamente!</div>');
    }
}

// Ejecutar al inicio para verificar que los endpoints existen
$(document).ready(function() {
    // Verificar disponibilidad de endpoints
    verificarEndpoint('get_campos_especialidad_nuevo.php');
    verificarEndpoint('get_campos_simple.php');
    verificarEndpoint('get_campos_emergencia.php');
    
    // Crear panel de diagnóstico después de un breve retraso
    setTimeout(crearPanelDiagnostico, 1000);
});
