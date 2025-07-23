// Archivo de JavaScript para manejar el envío de mensajes de WhatsApp
// Este archivo será incluido en Citas.php

// Manejar el evento de clic en el botón WhatsApp
$(document).ready(function() {
    $('#btnEnviarWhatsapp').on('click', function(e) {
        e.preventDefault();
        console.log("WhatsApp button clicked");
        var btnWhatsapp = $(this);
        
        // Check if jQuery is properly loaded
        if (typeof $ !== 'function' || typeof $.ajax !== 'function') {
            console.error("jQuery is not properly loaded!");
            alert("Error: jQuery is not properly loaded. Please check the browser console for details.");
            return;
        }
        
        // Mostrar indicador de carga
        btnWhatsapp.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Verificando...');
        
        // Verificar primero si el servidor está activo mediante un proxy PHP
        $.ajax({
            url: 'whatsapp_fixed.php?check=1',
            type: 'GET',
            timeout: 5000,
            success: function(response) {
                if (response && response.status === 'success') {
                    console.log('Servidor WhatsApp disponible');
                    btnWhatsapp.html('<i class="fas fa-spinner fa-spin"></i> Enviando...');
                    enviarMensajesWhatsapp(btnWhatsapp);
                } else {
                    console.error('Error en respuesta del servidor');
                    alert('El servidor de WhatsApp no está respondiendo correctamente');
                    btnWhatsapp.prop('disabled', false).html('<i class="fab fa-whatsapp"></i> WhatsApp');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('Error: El servidor de WhatsApp no está disponible');
                console.error('Status:', textStatus);
                console.error('Error:', errorThrown);
                
                // Mostrar modal de error
                if ($('#whatsappErrorModal').length === 0) {
                    $('body').append(`
                        <div class="modal fade" id="whatsappErrorModal" tabindex="-1" role="dialog" aria-labelledby="whatsappErrorModalLabel" aria-hidden="true">
                          <div class="modal-dialog" role="document">
                            <div class="modal-content">
                              <div class="modal-header">
                                <h5 class="modal-title" id="whatsappErrorModalLabel">Error de conexión</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                  <span aria-hidden="true">&times;</span>
                                </button>
                              </div>
                              <div class="modal-body">
                                <div class="alert alert-danger">
                                  <p><i class="fas fa-exclamation-triangle"></i> El servidor de WhatsApp no está disponible.</p>
                                  <p>Por favor, verifique que el servidor esté en ejecución en <code>localhost:5000</code> e intente nuevamente.</p>
                                </div>
                              </div>
                              <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                              </div>
                            </div>
                          </div>
                        </div>
                    `);
                }
                
                $('#whatsappErrorModal').modal('show');
                btnWhatsapp.prop('disabled', false).html('<i class="fab fa-whatsapp"></i> WhatsApp');
            }
        });
    });
});

// Función para procesar el envío de mensajes
function enviarMensajesWhatsapp(btnWhatsapp) {
    // Obtener la información de citas con números de teléfono
    var citasPacientes = citasData; // Esta variable se define en el PHP

    console.log("Citas encontradas:", citasPacientes);
    // Si no hay citas con teléfonos disponibles
    if (citasPacientes.length === 0) {
        alert('No hay números de teléfono disponibles en las citas actuales.');
        btnWhatsapp.prop('disabled', false).html('<i class="fab fa-whatsapp"></i> WhatsApp');
        return;
    }
    
    // Contador para seguir el progreso
    var procesados = 0;
    var exitosos = 0;
    var fallidos = 0;
    
    // Función para enviar mensaje a un número
    function enviarMensaje(citaPaciente) {
        var telefono = citaPaciente.telefono;
        console.log('Intentando enviar mensaje a: ' + telefono);
        
        // Mostrar información del paciente para debugging
        console.log('Datos del paciente:', {
            nombre: citaPaciente.paciente,
            fecha: citaPaciente.fecha,
            hora: citaPaciente.hora,
            doctor: citaPaciente.doctor
        });
        
        // Asegurarse de que el teléfono tiene el formato correcto
        if (telefono.length < 8) {
            console.error('Número de teléfono inválido: ' + telefono);
            fallidos++;
            verificarCompletado();
            return;
        }
        
        // Crear un mensaje personalizado para el paciente
        var mensaje = "🏥 *RECORDATORIO DE CITA MEDICA* 🏥\n\n" +
                      "Hola *" + citaPaciente.paciente + "*,\n\n" + 
                      "Le recordamos que tiene una cita médica programada con:\n" +
                      "👨‍⚕️ *Dr. " + citaPaciente.doctor + "*\n" +
                      "📆 *Fecha:* " + citaPaciente.fecha + "\n" +
                    //   "🕒 *Hora:* " + citaPaciente.hora + "\n\n" +
                      "Por favor, confirme su asistencia respondiendo a este mensaje.\n\n" +
                       "Si necesita reprogramar, contáctenos lo antes posible.\n\n" +
                      "¡Gracias por su preferencia! Estamos para servirle.";
        
        var datos = {
            phone: telefono,
            message: mensaje
        };
        
        console.log('Enviando datos: ', datos);
        
        // Intentar enviar el mensaje varias veces si falla
        var intentos = 0;
        var maxIntentos = 2;
        
        function intentarEnvio() {
            $.ajax({
                url: 'whatsapp_fixed.php',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(datos),
                dataType: 'json',
                timeout: 15000, // 15 segundos de timeout
                success: function(response) {
                    console.log('Mensaje enviado a ' + telefono, response);
                    exitosos++;
                    verificarCompletado();
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('Error enviando mensaje a ' + telefono);
                    console.error('Status: ' + textStatus);
                    console.error('Error: ' + errorThrown);
                    
                    if (jqXHR.responseText) {
                        try {
                            var errorData = JSON.parse(jqXHR.responseText);
                            console.error('Detalle del error:', errorData);
                        } catch (e) {
                            console.error('Respuesta no JSON:', jqXHR.responseText);
                        }
                    }
                    
                    // Reintentar si aún hay intentos disponibles
                    intentos++;
                    if (intentos < maxIntentos) {
                        console.log('Reintentando envío a ' + telefono + ' (intento ' + (intentos+1) + ' de ' + maxIntentos + ')');
                        setTimeout(intentarEnvio, 2000); // Esperar 2 segundos antes de reintentar
                    } else {
                        fallidos++;
                        verificarCompletado();
                    }
                },
                complete: function() {
                    console.log('Request completed for: ' + telefono);
                }
            });
        }
        
        // Iniciar el primer intento
        intentarEnvio();
    }
    
    // Función para verificar si se completaron todos los envíos
    function verificarCompletado() {
        procesados++;
        console.log('Progreso: ' + procesados + '/' + citasPacientes.length + ' procesados, ' + exitosos + ' exitosos, ' + fallidos + ' fallidos');
        
        if (procesados >= citasPacientes.length) {
            // Todos los mensajes han sido procesados
            var mensaje = 'Proceso completado: ' + exitosos + ' mensajes enviados';
            if (fallidos > 0) {
                mensaje += ', ' + fallidos + ' fallidos';
                console.warn('Algunos mensajes no se pudieron enviar. Revise la consola para más detalles.');
            }
            
            // Mostrar un modal en lugar de un alert para mejor UX
            if ($('#whatsappResultModal').length === 0) {
                $('body').append(`
                    <div class="modal fade" id="whatsappResultModal" tabindex="-1" role="dialog" aria-labelledby="whatsappResultModalLabel" aria-hidden="true">
                      <div class="modal-dialog" role="document">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title" id="whatsappResultModalLabel">Resultado del envío de mensajes</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                            </button>
                          </div>
                          <div class="modal-body" id="whatsappResultMessage">
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                          </div>
                        </div>
                      </div>
                    </div>
                `);
            }
            
            // Mostrar resultados en el modal
            $('#whatsappResultMessage').html(`
                <p>${mensaje}</p>
                <p>Mensajes exitosos: <span class="badge badge-success">${exitosos}</span></p>
                <p>Mensajes fallidos: <span class="badge badge-danger">${fallidos}</span></p>
                ${fallidos > 0 ? '<p class="text-danger">Revise la consola (F12) para ver detalles de los errores.</p>' : ''}
            `);
            $('#whatsappResultModal').modal('show');
            
            // Restaurar el botón a su estado original
            btnWhatsapp.prop('disabled', false).html('<i class="fab fa-whatsapp"></i> WhatsApp');
        }
    }
    
    // Enviar mensajes a todos los pacientes
    citasPacientes.forEach(function(citaPaciente, index) {
        // Delay entre cada envío para no sobrecargar el servidor
        // Aumentamos el delay para asegurar que el servidor tenga tiempo
        setTimeout(function() {
            enviarMensaje(citaPaciente);
        }, index * 1500); // 1.5 segundos entre mensajes (aumentado de 800ms)
    });
}
