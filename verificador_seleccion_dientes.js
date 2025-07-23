/**
 * Verificador de selección de dientes para el odontograma
 * Este script puede incluirse en cualquier página que use el odontograma
 * para diagnosticar y corregir problemas de selección
 */

(function() {
    // Esperar a que la página cargue completamente
    document.addEventListener('DOMContentLoaded', function() {
        console.log('[VERIFICADOR] Inicializando verificador de selección de dientes');
        
        // Crear el contenedor del verificador
        const verificador = document.createElement('div');
        verificador.id = 'verificador-seleccion';
        verificador.style.cssText = 'position: fixed; bottom: 10px; right: 10px; padding: 10px; background: #f8f9fa; border: 1px solid #ddd; border-radius: 5px; box-shadow: 0 2px 8px rgba(0,0,0,0.2); z-index: 9999; max-width: 300px; font-family: monospace; font-size: 12px;';
        
        // Agregar título
        const titulo = document.createElement('div');
        titulo.innerHTML = '<b>Verificador de selección</b> <span style="float: right; cursor: pointer; font-weight: bold;" id="cerrar-verificador">X</span>';
        titulo.style.marginBottom = '8px';
        verificador.appendChild(titulo);
        
        // Agregar contador de dientes seleccionados
        const contador = document.createElement('div');
        contador.id = 'verificador-contador';
        contador.style.marginBottom = '5px';
        verificador.appendChild(contador);
        
        // Agregar lista de dientes
        const lista = document.createElement('div');
        lista.id = 'verificador-lista';
        lista.style.cssText = 'max-height: 100px; overflow-y: auto; margin-bottom: 5px; border: 1px solid #eee; padding: 5px;';
        verificador.appendChild(lista);
        
        // Agregar botones
        const botonesDiv = document.createElement('div');
        botonesDiv.style.cssText = 'display: flex; justify-content: space-between;';
        
        const actualizarBtn = document.createElement('button');
        actualizarBtn.textContent = 'Verificar';
        actualizarBtn.style.cssText = 'background: #007bff; color: white; border: none; padding: 3px 8px; border-radius: 3px; cursor: pointer;';
        actualizarBtn.onclick = actualizarVerificador;
        
        const corregirBtn = document.createElement('button');
        corregirBtn.textContent = 'Corregir';
        corregirBtn.style.cssText = 'background: #28a745; color: white; border: none; padding: 3px 8px; border-radius: 3px; cursor: pointer;';
        corregirBtn.onclick = corregirSeleccion;
        
        botonesDiv.appendChild(actualizarBtn);
        botonesDiv.appendChild(corregirBtn);
        verificador.appendChild(botonesDiv);
        
        // Agregar el verificador al body
        document.body.appendChild(verificador);
        
        // Configurar el botón cerrar
        document.getElementById('cerrar-verificador').addEventListener('click', function() {
            verificador.style.display = 'none';
        });
        
        // Primera actualización
        setTimeout(actualizarVerificador, 500);
        
        // Configurar observador para detectar cambios en la selección
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                    const target = mutation.target;
                    if (target.classList.contains('tooth-selected') || 
                        (target.classList.contains('tooth-shape') && !target.classList.contains('tooth-selected'))) {
                        actualizarVerificador();
                    }
                }
            });
        });
        
        // Observar todos los dientes
        document.querySelectorAll('.tooth-shape').forEach(function(tooth) {
            observer.observe(tooth, { attributes: true });
        });
    });
    
    // Función para actualizar el verificador
    function actualizarVerificador() {
        console.log('[VERIFICADOR] Actualizando estado');
        
        // Obtener dientes seleccionados del DOM
        const seleccionadosDOM = [];
        document.querySelectorAll('.tooth-shape.tooth-selected').forEach(function(tooth) {
            const num = parseInt(tooth.getAttribute('data-num'));
            if (!isNaN(num)) seleccionadosDOM.push(num);
        });
        
        // Obtener dientes seleccionados del array global
        const seleccionadosArray = window.seleccionados ? 
            (Array.isArray(window.seleccionados) ? window.seleccionados : Array.from(window.seleccionados)) 
            : [];
        
        // Ordenar ambos arrays para comparación
        seleccionadosDOM.sort((a, b) => a - b);
        const seleccionadosArrayOrdenados = [...seleccionadosArray].sort((a, b) => a - b);
        
        // Comprobar si hay inconsistencia
        const hayConsistencia = JSON.stringify(seleccionadosDOM) === JSON.stringify(seleccionadosArrayOrdenados);
        
        // Obtener el valor del campo oculto
        const campoOculto = document.getElementById('dientes_seleccionados');
        const valorCampoOculto = campoOculto ? campoOculto.value : '';
        
        // Actualizar contador
        const contador = document.getElementById('verificador-contador');
        if (contador) {
            contador.innerHTML = `
                <div>DOM: <b>${seleccionadosDOM.length}</b> dientes</div>
                <div>Array: <b>${seleccionadosArray.length}</b> dientes</div>
                <div>Campo: <b>${valorCampoOculto ? valorCampoOculto.split(',').length : 0}</b> valores</div>
                <div>Estado: <span style="color: ${hayConsistencia ? 'green' : 'red'}">${hayConsistencia ? '✓ Consistente' : '✗ Inconsistente'}</span></div>
            `;
        }
        
        // Actualizar lista
        const lista = document.getElementById('verificador-lista');
        if (lista) {
            lista.innerHTML = `
                <div><small>DOM:</small> <span style="color: #007bff">${seleccionadosDOM.join(', ') || 'ninguno'}</span></div>
                <div><small>Array:</small> <span style="color: #28a745">${seleccionadosArrayOrdenados.join(', ') || 'ninguno'}</span></div>
                <div><small>Campo:</small> <span style="color: #dc3545">${valorCampoOculto || 'vacío'}</span></div>
            `;
        }
    }
    
    // Función para corregir inconsistencias
    function corregirSeleccion() {
        console.log('[VERIFICADOR] Corrigiendo inconsistencias');
        
        // Sincronizar basado en el DOM (que es la fuente de verdad visual)
        const dientesSeleccionados = [];
        document.querySelectorAll('.tooth-shape.tooth-selected').forEach(function(tooth) {
            const num = parseInt(tooth.getAttribute('data-num'));
            if (!isNaN(num) && !dientesSeleccionados.includes(num)) {
                dientesSeleccionados.push(num);
            }
        });
        
        // Actualizar el array global
        window.seleccionados = dientesSeleccionados;
        
        // Forzar la actualización de la lista
        if (typeof window.updateSeleccionados === 'function') {
            window.updateSeleccionados();
            console.log('[VERIFICADOR] Lista actualizada mediante updateSeleccionados()');
        } else {
            // Actualizar el campo oculto manualmente si la función no existe
            const campoOculto = document.getElementById('dientes_seleccionados');
            if (campoOculto) {
                campoOculto.value = dientesSeleccionados.join(',');
                console.log('[VERIFICADOR] Campo oculto actualizado manualmente');
            }
        }
        
        actualizarVerificador();
        console.log('[VERIFICADOR] Corrección completada');
    }
})();
