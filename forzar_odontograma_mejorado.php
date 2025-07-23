<?php
// Script para forzar la carga del odontograma mejorado en consultas de odontología
// Este archivo se incluye en nueva_consulta.php para mostrar automáticamente el odontograma
?>
<script>
// Script mejorado para forzar la carga del odontograma con selección de dientes funcionando
$(document).ready(function() {
    console.log('[FORZAR ODONTOGRAMA MEJORADO] Iniciando...');
    
    // Variable global para controlar si se debe mostrar el odontograma
    window.MOSTRAR_ODONTOGRAMA = false;
    
    // Función para detectar si la especialidad actual es odontología
    function esEspecialidadOdontologia() {
        // Verificar si hay algún indicio de que es odontología
        return new Promise((resolve, reject) => {
            console.log('[FORZAR ODONTOGRAMA] Verificando especialidad...');
            
            // Hacer una consulta para verificar la especialidad configurada
            $.get('get_especialidad_actual.php')
                .done(function(data) {
                    try {
                        let resultado = typeof data === 'string' ? JSON.parse(data) : data;
                        let esOdontologia = resultado.es_odontologia || false;
                        
                        console.log('[FORZAR ODONTOGRAMA] Especialidad detectada:', {
                            nombre: resultado.nombre || 'No definida',
                            es_odontologia: esOdontologia
                        });
                        
                        window.MOSTRAR_ODONTOGRAMA = esOdontologia;
                        resolve(esOdontologia);
                    } catch (e) {
                        console.error('[FORZAR ODONTOGRAMA] Error parseando respuesta:', e);
                        resolve(false);
                    }
                })
                .fail(function(xhr, status, error) {
                    console.warn('[FORZAR ODONTOGRAMA] No se pudo verificar especialidad:', error);
                    resolve(false);
                });
        });
    }
    
    // Función para cargar el odontograma mejorado
    function cargarOdontogramaMejorado() {
        console.log('[FORZAR ODONTOGRAMA] Cargando odontograma mejorado...');
        
        // Verificar si ya existe un odontograma
        if ($('#odontograma-dinamico').length > 0) {
            console.log('[FORZAR ODONTOGRAMA] El odontograma ya existe, no se recarga');
            return;
        }
        
        // Crear contenedor para el odontograma si no existe
        if ($('#campos_dinamicos #odontograma-container').length === 0) {
            const contenedorOdontograma = `
                <div id="odontograma-container" class="mt-4">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="fas fa-tooth"></i> Odontograma</h5>
                        </div>
                        <div class="card-body" id="odontograma-dinamico">
                            <div class="text-center">
                                <i class="fas fa-spinner fa-spin"></i> Cargando odontograma...
                            </div>
                        </div>
                    </div>
                </div>
            `;
            $('#campos_dinamicos').append(contenedorOdontograma);
        }
        
        // Cargar el odontograma mejorado via AJAX
        $.get('odontograma_svg_mejorado.php')
            .done(function(data) {
                $('#odontograma-dinamico').html(data);
                console.log('[FORZAR ODONTOGRAMA] Odontograma mejorado cargado exitosamente');
                
                // Verificar que la función updateSeleccionados esté disponible
                setTimeout(() => {
                    if (typeof window.updateSeleccionados === 'function') {
                        console.log('[FORZAR ODONTOGRAMA] Función updateSeleccionados disponible');
                        
                        // Verificar que el array de seleccionados esté inicializado
                        if (typeof window.seleccionados === 'undefined') {
                            window.seleccionados = [];
                            console.log('[FORZAR ODONTOGRAMA] Inicializado array de seleccionados');
                        }
                        
                        // Llamar a updateSeleccionados para asegurar que la lista se muestre
                        window.updateSeleccionados();
                        
                        // Agregar listener para verificar selecciones
                        $(document).on('click', '.tooth-shape', function() {
                            setTimeout(() => {
                                console.log('[FORZAR ODONTOGRAMA] Dientes seleccionados:', window.seleccionados);
                                if (typeof window.updateSeleccionados === 'function') {
                                    window.updateSeleccionados();
                                }
                            }, 100);
                        });
                        
                    } else {
                        console.error('[FORZAR ODONTOGRAMA] Función updateSeleccionados no disponible');
                    }
                }, 500);
            })
            .fail(function(xhr, status, error) {
                console.error('[FORZAR ODONTOGRAMA] Error cargando odontograma:', error);
                $('#odontograma-dinamico').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> 
                        Error al cargar el odontograma: ${error}
                        <br>
                        <small>Verifica que el archivo odontograma_svg_mejorado.php exista.</small>
                    </div>
                `);
            });
    }
    
    // Función principal
    function inicializar() {
        esEspecialidadOdontologia().then(esOdontologia => {
            if (esOdontologia) {
                console.log('[FORZAR ODONTOGRAMA] Es especialidad odontología - cargando odontograma');
                cargarOdontogramaMejorado();
            } else {
                console.log('[FORZAR ODONTOGRAMA] No es especialidad odontología - no se carga odontograma');
                // Remover odontograma si existe
                $('#odontograma-container').remove();
            }
        });
    }
    
    // Inicializar después de un pequeño delay para asegurar que el DOM esté listo
    setTimeout(inicializar, 100);
    
    // También ejecutar cuando cambien los campos dinámicos
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList' && 
                mutation.target.id === 'campos_dinamicos' && 
                window.MOSTRAR_ODONTOGRAMA === true &&
                $('#odontograma-container').length === 0) {
                console.log('[FORZAR ODONTOGRAMA] Cambio detectado en campos dinámicos - verificando odontograma');
                cargarOdontogramaMejorado();
            }
        });
    });
    
    // Observar cambios en el contenedor de campos dinámicos
    const camposDinamicos = document.getElementById('campos_dinamicos');
    if (camposDinamicos) {
        observer.observe(camposDinamicos, { 
            childList: true, 
            subtree: false 
        });
    }
});
</script>
