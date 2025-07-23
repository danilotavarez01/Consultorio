<?php
// Este archivo contiene la implementación del odontograma SVG avanzado
// Debe ser incluido en forzar_odontograma_simple.php para reemplazar el odontograma básico

// No iniciar sesión ni hacer verificaciones aquí, este archivo es solo para incluir
// El control de si se debe mostrar o no está en forzar_odontograma_simple.php
?>

<div id="odontograma-dinamico" class="mb-4">
    <!-- <h5 class="mt-4 mb-2 text-primary">Odontograma</h5> -->
    <div id="odontograma-container" style="max-width: 900px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9;">
        <h2 style="color: #0056b3; text-align: center; margin-bottom: 20px;">Odontograma - Selección de Dientes</h2>
        
        <!-- Checkbox para opciones del odontograma -->
        <!-- <div class="opciones" style="display: flex; justify-content: flex-end; margin-bottom: 10px; gap: 15px;">
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="mostrarAlertas">
                <label class="form-check-label" for="mostrarAlertas">Mostrar posición en alertas</label>
            </div>
        </div> -->
        
        <div class="leyenda" style="display: flex; align-items: center; gap: 18px; margin: 18px 0 10px 0; justify-content: center;">
            <span class="leyenda-item" style="display: flex; align-items: center; gap: 6px; font-size: 15px;"><svg class="leyenda-svg" style="width: 28px; height: 32px;"><ellipse cx="14" cy="16" rx="12" ry="15" fill="url(#coronaGrad)" stroke="#333" stroke-width="1.5"/></svg> Normal</span>
            <span class="leyenda-item" style="display: flex; align-items: center; gap: 6px; font-size: 15px;"><svg class="leyenda-svg" style="width: 28px; height: 32px;"><ellipse cx="14" cy="16" rx="12" ry="15" fill="url(#coronaGradSel)" stroke="#ff6347" stroke-width="3"/></svg> Seleccionado</span>
            <span class="leyenda-item" style="display: flex; align-items: center; gap: 6px; font-size: 15px;"><svg class="leyenda-svg" style="width: 28px; height: 32px;"><ellipse cx="14" cy="16" rx="12" ry="15" fill="url(#coronaGradHover)" stroke="#1976d2" stroke-width="2.5"/></svg> Hover</span>
        </div>
        
        <svg id="odontograma" class="odontograma-svg" width="900" height="520" viewBox="0 0 900 520" style="display: block; margin: 0 auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 8px #0001;">
            <defs>
                <linearGradient id="coronaGrad" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#fff"/>
                    <stop offset="100%" stop-color="#e0e0e0"/>
                </linearGradient>
                <linearGradient id="coronaGradHover" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#b3e5fc"/>
                    <stop offset="100%" stop-color="#e0f7fa"/>
                </linearGradient>
                <linearGradient id="coronaGradSel" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#ffb39b"/>
                    <stop offset="100%" stop-color="#ff6347"/>
                </linearGradient>
                <linearGradient id="raizGrad" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#ffe082"/>
                    <stop offset="100%" stop-color="#fffde7"/>
                </linearGradient>
            </defs>
            <!-- Etiqueta Maxilar Superior -->
            <text x="450" y="50" text-anchor="middle" font-size="28" fill="#1976d2" font-weight="bold">Maxilar Superior</text>
            <ellipse cx="450" cy="170" rx="350" ry="90" fill="#e3f2fd" opacity="0.5" />
            <!-- Líneas divisorias de cuadrantes superiores -->
            <line x1="450" y1="80" x2="450" y2="260" stroke="#bbb" stroke-width="2" stroke-dasharray="8,6" />
            <line x1="250" y1="170" x2="650" y2="170" stroke="#bbb" stroke-width="2" stroke-dasharray="8,6" />
            <!-- Dientes superiores (arco) -->
            <g id="arc-superior"></g>
            <!-- Cuadrantes -->
            <text x="270" y="100" font-size="16" fill="#1976d2">1er</text>
            <text x="630" y="100" font-size="16" fill="#1976d2">2do</text>
            
            <!-- Etiqueta Maxilar Inferior -->
            <text x="450" y="520" text-anchor="middle" font-size="28" fill="#388e3c" font-weight="bold">Maxilar Inferior</text>
            <ellipse cx="450" cy="370" rx="350" ry="90" fill="#e8f5e9" opacity="0.5" />
            <!-- Líneas divisorias de cuadrantes inferiores -->
            <line x1="450" y1="280" x2="450" y2="460" stroke="#bbb" stroke-width="2" stroke-dasharray="8,6" />
            <line x1="250" y1="370" x2="650" y2="370" stroke="#bbb" stroke-width="2" stroke-dasharray="8,6" />
            <!-- Dientes inferiores (arco) -->
            <g id="arc-inferior"></g>
            <!-- Cuadrantes -->
            <text x="270" y="490" font-size="16" fill="#388e3c">3er</text>
            <text x="630" y="490" font-size="16" fill="#388e3c">4to</text>
        </svg>
        <div class="mt-4" style="padding: 15px; background-color: white; border-radius: 8px; box-shadow: 0 1px 3px #0001;">
            <h4 style="color: #444; margin-bottom: 10px; font-size: 16px;">Dientes seleccionados:</h4>
            <div id="dientes-seleccionados-lista" style="min-height: 30px;"><span style="color: #777;" id="seleccionados-texto">Ninguno seleccionado</span></div>
            <!-- Campo oculto dientes_seleccionados removido -->
        </div>
    </div>
</div>

<style>
.tooth-shape {
    cursor: pointer;
    fill: url(#coronaGrad);
    stroke: #333;
    stroke-width: 1.5;
    filter: drop-shadow(0 1px 2px #0002);
    transition: fill 0.2s, stroke-width 0.2s, filter 0.2s;
}
.tooth-shape:hover {
    fill: url(#coronaGradHover);
    stroke-width: 2.5;
    filter: drop-shadow(0 2px 6px #1976d255);
}
.tooth-selected {
    fill: url(#coronaGradSel) !important;
    stroke: #ff6347;
    stroke-width: 3 !important;
    filter: drop-shadow(0 2px 8px #ff634755);
    transform: none;
}
.tooth-root {
    fill: url(#raizGrad);
    stroke: #bfa76f;
    stroke-width: 1.2;
}
.tooth-separator {
    stroke: #bbb;
    stroke-width: 1;
}
.tooth-label {
    font-size: 13px;
    text-anchor: middle;
    fill: #333;
    pointer-events: none;
    font-weight: bold;
}

.tooth-tooltip {
    position: absolute;
    background: #fffbe7;
    border: 1px solid #bfa76f;
    border-radius: 4px;
    padding: 6px 12px;
    font-size: 15px;
    color: #333;
    pointer-events: none;
    box-shadow: 0 2px 8px #0002;
    z-index: 999;
    display: none;
}
</style>

<script>
// --- Datos de dientes y nombres ---
const dientes = [
    // Superior derecha (cuadrante 1)
    { num: 18, nombre: 'Tercer molar sup. der.' },
    { num: 17, nombre: 'Segundo molar sup. der.' },
    { num: 16, nombre: 'Primer molar sup. der.' },
    { num: 15, nombre: 'Segundo premolar sup. der.' },
    { num: 14, nombre: 'Primer premolar sup. der.' },
    { num: 13, nombre: 'Canino sup. der.' },
    { num: 12, nombre: 'Incisivo lateral sup. der.' },
    { num: 11, nombre: 'Incisivo central sup. der.' },
    // Superior izquierda (cuadrante 2)
    { num: 21, nombre: 'Incisivo central sup. izq.' },
    { num: 22, nombre: 'Incisivo lateral sup. izq.' },
    { num: 23, nombre: 'Canino sup. izq.' },
    { num: 24, nombre: 'Primer premolar sup. izq.' },
    { num: 25, nombre: 'Segundo premolar sup. izq.' },
    { num: 26, nombre: 'Primer molar sup. izq.' },
    { num: 27, nombre: 'Segundo molar sup. izq.' },
    { num: 28, nombre: 'Tercer molar sup. izq.' },
    // Inferior izquierda (cuadrante 3)
    { num: 38, nombre: 'Tercer molar inf. izq.' },
    { num: 37, nombre: 'Segundo molar inf. izq.' },
    { num: 36, nombre: 'Primer molar inf. izq.' },
    { num: 35, nombre: 'Segundo premolar inf. izq.' },
    { num: 34, nombre: 'Primer premolar inf. izq.' },
    { num: 33, nombre: 'Canino inf. izq.' },
    { num: 32, nombre: 'Incisivo lateral inf. izq.' },
    { num: 31, nombre: 'Incisivo central inf. izq.' },
    // Inferior derecha (cuadrante 4)
    { num: 41, nombre: 'Incisivo central inf. der.' },
    { num: 42, nombre: 'Incisivo lateral inf. der.' },
    { num: 43, nombre: 'Canino inf. der.' },
    { num: 44, nombre: 'Primer premolar inf. der.' },
    { num: 45, nombre: 'Segundo premolar inf. der.' },
    { num: 46, nombre: 'Primer molar inf. der.' },
    { num: 47, nombre: 'Segundo molar inf. der.' },
    { num: 48, nombre: 'Tercer molar inf. der.' },
];

// Inicializar odontograma
function inicializarOdontogramaSVG() {
    console.log('[ODONTOGRAMA SVG] Inicializando odontograma avanzado...');
    
    const odontograma = document.getElementById('odontograma');
    const seleccionados = new Set();
    
    // Crear tooltip
    let tooltip = document.getElementById('tooth-tooltip');
    if (!tooltip) {
        tooltip = document.createElement('div');
        tooltip.id = 'tooth-tooltip';
        tooltip.className = 'tooth-tooltip';
        document.body.appendChild(tooltip);
    }

    // Función para dibujar una fila de dientes
    function drawToothRow(g, startIdx, endIdx, y, isSuperior) {
        const total = endIdx - startIdx + 1;
        const toothWidth = 44;
        const gap = 8;
        const rowWidth = toothWidth * total + gap * (total - 1);
        const startX = 450 - rowWidth / 2 + toothWidth / 2;
        
        for (let i = 0; i < total; i++) {
            const idx = isSuperior ? startIdx + i : endIdx - i;
            const x = startX + i * (toothWidth + gap);
            // Dibuja el diente (sin rotación ni arco)
            drawTooth(g, x, y, 0, dientes[idx].num, dientes[idx].nombre, isSuperior ? 'sup' : 'inf', idx);
        }
    }

    // Función para dibujar un diente
    function drawTooth(g, x, y, angle, num, nombre, arc, idx) {
        // Dibuja cada diente con una forma SVG aproximada a su tipo
        let coronaPath, raizPath;
        
        // Superior
        if (arc === 'sup') {
            if (idx <= 2 || idx >= 13) { // Molares
                coronaPath = `M ${x-18} ${y} Q ${x-10} ${y-18} ${x} ${y-12} Q ${x+10} ${y-18} ${x+18} ${y} Q ${x+12} ${y+8} ${x} ${y+8} Q ${x-12} ${y+8} ${x-18} ${y} Z`;
                raizPath = `M ${x-10} ${y+8} Q ${x-12} ${y+22} ${x-6} ${y+30} Q ${x} ${y+22} ${x+6} ${y+30} Q ${x+12} ${y+22} ${x+10} ${y+8} Z`;
            } else if (idx === 3 || idx === 12) { // 2dos premolares
                coronaPath = `M ${x-14} ${y} Q ${x} ${y-16} ${x+14} ${y} Q ${x+10} ${y+8} ${x} ${y+8} Q ${x-10} ${y+8} ${x-14} ${y} Z`;
                raizPath = `M ${x-6} ${y+8} Q ${x-8} ${y+22} ${x-2} ${y+28} Q ${x} ${y+22} ${x+2} ${y+28} Q ${x+8} ${y+22} ${x+6} ${y+8} Z`;
            } else if (idx === 4 || idx === 11) { // 1ros premolares
                coronaPath = `M ${x-12} ${y} Q ${x} ${y-18} ${x+12} ${y} Q ${x+8} ${y+8} ${x} ${y+8} Q ${x-8} ${y+8} ${x-12} ${y} Z`;
                raizPath = `M ${x-5} ${y+8} Q ${x-6} ${y+20} ${x} ${y+26} Q ${x+6} ${y+20} ${x+5} ${y+8} Z`;
            } else if (idx === 5 || idx === 10) { // Caninos
                coronaPath = `M ${x-8} ${y} Q ${x} ${y-26} ${x+8} ${y} Q ${x+4} ${y+8} ${x} ${y+8} Q ${x-4} ${y+8} ${x-8} ${y} Z`;
                raizPath = `M ${x-2} ${y+8} Q ${x} ${y+30} ${x+2} ${y+8} Z`;
            } else { // Incisivos
                coronaPath = `M ${x-7} ${y} Q ${x} ${y-18} ${x+7} ${y} Q ${x+5} ${y+8} ${x} ${y+8} Q ${x-5} ${y+8} ${x-7} ${y} Z`;
                raizPath = `M ${x-2} ${y+8} Q ${x} ${y+24} ${x+2} ${y+8} Z`;
            }
        } else { // Inferior
            if (idx <= 18 || idx >= 29) { // Molares
                coronaPath = `M ${x-18} ${y} Q ${x-10} ${y+18} ${x} ${y+12} Q ${x+10} ${y+18} ${x+18} ${y} Q ${x+12} ${y-8} ${x} ${y-8} Q ${x-12} ${y-8} ${x-18} ${y} Z`;
                raizPath = `M ${x-10} ${y-8} Q ${x-12} ${y-22} ${x-6} ${y-30} Q ${x} ${y-22} ${x+6} ${y-30} Q ${x+12} ${y-22} ${x+10} ${y-8} Z`;
            } else if (idx === 19 || idx === 28) { // 2dos premolares
                coronaPath = `M ${x-14} ${y} Q ${x} ${y+16} ${x+14} ${y} Q ${x+10} ${y-8} ${x} ${y-8} Q ${x-10} ${y-8} ${x-14} ${y} Z`;
                raizPath = `M ${x-6} ${y-8} Q ${x-8} ${y-22} ${x-2} ${y-28} Q ${x} ${y-22} ${x+2} ${y-28} Q ${x+8} ${y-22} ${x+6} ${y-8} Z`;
            } else if (idx === 20 || idx === 27) { // 1ros premolares
                coronaPath = `M ${x-12} ${y} Q ${x} ${y+18} ${x+12} ${y} Q ${x+8} ${y-8} ${x} ${y-8} Q ${x-8} ${y-8} ${x-12} ${y} Z`;
                raizPath = `M ${x-5} ${y-8} Q ${x-6} ${y-20} ${x} ${y-26} Q ${x+6} ${y-20} ${x+5} ${y-8} Z`;
            } else if (idx === 21 || idx === 26) { // Caninos
                coronaPath = `M ${x-8} ${y} Q ${x} ${y+26} ${x+8} ${y} Q ${x+4} ${y-8} ${x} ${y-8} Q ${x-4} ${y-8} ${x-8} ${y} Z`;
                raizPath = `M ${x-2} ${y-8} Q ${x} ${y-30} ${x+2} ${y-8} Z`;
            } else { // Incisivos
                coronaPath = `M ${x-7} ${y} Q ${x} ${y+18} ${x+7} ${y} Q ${x+5} ${y-8} ${x} ${y-8} Q ${x-5} ${y-8} ${x-7} ${y} Z`;
                raizPath = `M ${x-2} ${y-8} Q ${x} ${y-24} ${x+2} ${y-8} Z`;
            }
        }
        
        const toothG = document.createElementNS('http://www.w3.org/2000/svg', 'g');
        toothG.setAttribute('tabindex', '0');
        toothG.setAttribute('aria-label', `${num} - ${nombre}`);
        
        // Raíz
        const raiz = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        raiz.setAttribute('d', raizPath);
        raiz.setAttribute('class', 'tooth-root');
        toothG.appendChild(raiz);
        
        // Corona
        const corona = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        corona.setAttribute('d', coronaPath);
        corona.setAttribute('class', 'tooth-shape');
        corona.setAttribute('data-num', num);
        corona.setAttribute('data-nombre', nombre);
        toothG.appendChild(corona);
        
        // Guardar las coordenadas del diente en atributos de datos
        corona.setAttribute('data-posx', x);
        corona.setAttribute('data-posy', y);
        
        // Añadir un pequeño marcador visual en la posición exacta

        
        // Eventos de selección y tooltip
        corona.addEventListener('click', function(e) {
            // Obtener las coordenadas reales del diente (SVG)
            const posX = parseFloat(this.getAttribute('data-posx'));
            const posY = parseFloat(this.getAttribute('data-posy'));
            
            if (seleccionados.has(num)) {
                seleccionados.delete(num);
                corona.classList.remove('tooth-selected');
            } else {
                seleccionados.add(num);
                corona.classList.add('tooth-selected');
            }
            updateSeleccionados();
            
            // Registro detallado de la interacción
            console.log('==== DIENTE CLICKEADO ====');
            console.log('Número:', num);
            console.log('Nombre:', nombre);
            console.log('Posición X (SVG):', posX);
            console.log('Posición Y (SVG):', posY);
            console.log('Coordenadas del evento click - ClientX:', e.clientX, 'ClientY:', e.clientY);
            console.log('Coordenadas del evento click - PageX:', e.pageX, 'PageY:', e.pageY);
            console.log('Coordenadas del evento click - OffsetX:', e.offsetX, 'OffsetY:', e.offsetY);
            console.log('Seleccionados actualizados:', Array.from(seleccionados));
            
            // Mostrar alert con el número del diente seleccionado
            // alert(`Diente seleccionado: #${num}`);
            
            // Mostrar alert detallado con la información del diente y su posición si está activada la opción
            const mostrarAlertas = document.getElementById('mostrarAlertas').checked;
            if (mostrarAlertas) {
                alert(`Diente #${num}: ${nombre}\nPosición X: ${posX.toFixed(1)}\nPosición Y: ${posY.toFixed(1)}\nEstado: ${seleccionados.has(num) ? 'Seleccionado' : 'No seleccionado'}`);
            }
            
            // Guardar la posición del diente en window para acceso global
            window.ultimoDienteCliqueado = {
                numero: num,
                nombre: nombre,
                posicionX: posX,
                posicionY: posY,
                eventoX: e.pageX,
                eventoY: e.pageY,
                timestamp: new Date().getTime()
            };
            
            // Emitir un evento personalizado con los datos del diente
            const eventoDetalleDiente = new CustomEvent('dienteClic', {
                detail: {
                    numero: num,
                    nombre: nombre,
                    posX: posX,
                    posY: posY,
                    posicionTexto: `(${Math.round(posX)},${Math.round(posY)})`,
                    seleccionado: seleccionados.has(num),
                    eventoX: e.pageX,
                    eventoY: e.pageY
                }
            });
            document.dispatchEvent(eventoDetalleDiente);
            
            // Guardar el número del diente seleccionado para mostrar en alertas
            window.ultimoDienteSeleccionado = num;
            console.log('[ODONTOGRAMA] Último diente seleccionado actualizado:', num);
        });
        
        corona.addEventListener('mouseenter', function(e) {
            tooltip.innerText = `${num} - ${nombre}`;
            tooltip.style.display = 'block';
        });
        
        corona.addEventListener('mouseleave', function(e) {
            tooltip.style.display = 'none';
        });
        
        corona.addEventListener('mousemove', function(e) {
            tooltip.style.left = (e.pageX + 18) + 'px';
            tooltip.style.top = (e.pageY - 10) + 'px';
        });
        
        g.appendChild(toothG);
        
        // Etiqueta de número debajo del diente
        const label = document.createElementNS('http://www.w3.org/2000/svg', 'text');
        label.setAttribute('x', x);
        label.setAttribute('y', y + 48);
        label.setAttribute('class', 'tooth-label');
        label.textContent = num;
        g.appendChild(label);
        

    }

    // Función para actualizar la lista de dientes seleccionados
    function updateSeleccionados() {
        // Verificar que seleccionados existe y es iterable
        if (!seleccionados || typeof seleccionados[Symbol.iterator] !== 'function') {
            console.error('[ODONTOGRAMA] Error: seleccionados no es iterable');
            return;
        }
        
        // Ordenar los dientes numéricamente (convertir a números primero)
        const seleccionadosArr = Array.from(seleccionados)
            .map(num => parseInt(num, 10)) // Asegurar que todos son números
            .filter(num => !isNaN(num)) // Filtrar valores no numéricos
            .sort((a, b) => a - b);
        console.log('[ODONTOGRAMA] Dientes seleccionados (ordenados):', seleccionadosArr);
        
        // El campo oculto dentro del odontograma ha sido removido
        console.log('[ODONTOGRAMA] Dientes seleccionados (valor):', seleccionadosArr.join(','));
        
        // El campo oculto del formulario principal ha sido removido
        // Los dientes seleccionados ahora solo se guardan en el array JSON
        
        // Actualizar el campo oculto de JSON array - este es ahora el único lugar donde se almacenan los dientes
        const campoArrayJSON = document.querySelector('form input[name="dientes_array_json"]');
        if (campoArrayJSON) {
            campoArrayJSON.value = JSON.stringify(seleccionadosArr);
            console.log('[ODONTOGRAMA] Campo dientes_array_json actualizado con:', campoArrayJSON.value);
            // Guardar en variable global para acceso por otras funciones
            window.dientesArrayJSON = campoArrayJSON.value;
            // Almacenar también la versión de cadena por compatibilidad
            window.dientesSeleccionadosStr = seleccionadosArr.join(',');
        } else {
            console.warn('[ODONTOGRAMA] No se encontró el campo dientes_array_json en el formulario');
        }
        
        // Guardar el último diente cliqueado para referencia (no se guarda en el JSON)
        if (window.ultimoDienteCliqueado) {
            window.ultimoDienteSeleccionado = window.ultimoDienteCliqueado.numero;
            console.log('[ODONTOGRAMA] Último diente cliqueado actualizado:', window.ultimoDienteSeleccionado);
        }
        
        // Texto para visualización
        const texto = seleccionadosArr.length === 0
            ? 'Ninguno seleccionado'
            : seleccionadosArr.join(', ');
            
        // Intentar actualizar el elemento seleccionados-texto si existe
        const seleccionadosTextoElement = document.getElementById('seleccionados-texto');
        if (seleccionadosTextoElement) {
            seleccionadosTextoElement.textContent = texto;
        }
        
        // Log para depuración
        console.log('[ODONTOGRAMA] Actualizando lista de dientes seleccionados:', seleccionadosArr);
        
        // Actualizar lista visual agrupada por cuadrantes
        var listaHtml = '';
        if (seleccionadosArr.length > 0) {
            // Agrupar dientes por cuadrantes para mejor visualización
            var cuadrante1 = seleccionadosArr.filter(d => d >= 11 && d <= 18);
            var cuadrante2 = seleccionadosArr.filter(d => d >= 21 && d <= 28);
            var cuadrante3 = seleccionadosArr.filter(d => d >= 31 && d <= 38);
            var cuadrante4 = seleccionadosArr.filter(d => d >= 41 && d <= 48);
            
            // Función para generar HTML de un grupo de dientes
            function generarGrupoDientes(cuadrante, nombre) {
                var html = '';
                if (cuadrante.length > 0) {
                    html += '<div class="mb-2"><small class="text-muted">' + nombre + ':</small> ';
                    cuadrante.forEach(function(diente) {
                        html += '<span class="badge badge-primary mr-1" style="background: #007bff; color: white; padding: 3px 6px; border-radius: 4px; display: inline-block; margin-right: 4px;">' + diente + '</span>';
                    });
                    html += '</div>';
                }
                return html;
            }
            
            // Generar HTML para cada cuadrante
            listaHtml += generarGrupoDientes(cuadrante1, 'Cuadrante 1 (Sup. Der.)');
            listaHtml += generarGrupoDientes(cuadrante2, 'Cuadrante 2 (Sup. Izq.)');
            listaHtml += generarGrupoDientes(cuadrante3, 'Cuadrante 3 (Inf. Izq.)');
            listaHtml += generarGrupoDientes(cuadrante4, 'Cuadrante 4 (Inf. Der.)');
            
            const listaElement = document.getElementById('dientes-seleccionados-lista');
            if (listaElement) {
                listaElement.innerHTML = listaHtml;
                console.log('[ODONTOGRAMA] Lista HTML actualizada con', seleccionadosArr.length, 'dientes');
            } else {
                console.warn('[ODONTOGRAMA] Elemento dientes-seleccionados-lista no encontrado');
            }
        } else {
            const listaElement = document.getElementById('dientes-seleccionados-lista');
            if (listaElement) {
                listaElement.innerHTML = '<span style="color: #777;">Ninguno seleccionado</span>';
                console.log('[ODONTOGRAMA] Lista HTML actualizada - ningún diente seleccionado');
            }
        }
    }
    
    // Hacer la función disponible globalmente
    window.updateSeleccionados = updateSeleccionados;

    // Limpiar y dibujar odontograma
    function drawOdontograma() {
        // Limpiar grupos de dientes
        const arcSup = document.getElementById('arc-superior');
        const arcInf = document.getElementById('arc-inferior');
        if (arcSup) arcSup.innerHTML = '';
        if (arcInf) arcInf.innerHTML = '';
        
        // Dientes superiores (18-28)
        drawToothRow(arcSup, 0, 15, 120, true);
        
        // Dientes inferiores (48-38)
        drawToothRow(arcInf, 16, 31, 320, false);
        
        // Línea punteada entre filas
        const svg = document.getElementById('odontograma');
        let sepLine = document.getElementById('linea-separadora');
        if (!sepLine) {
            sepLine = document.createElementNS('http://www.w3.org/2000/svg', 'line');
            sepLine.setAttribute('id', 'linea-separadora');
            svg.appendChild(sepLine);
        }
        sepLine.setAttribute('x1', 80);
        sepLine.setAttribute('x2', 820);
        sepLine.setAttribute('y1', 220);
        sepLine.setAttribute('y2', 220);
        sepLine.setAttribute('stroke', '#bbb');
        sepLine.setAttribute('stroke-width', '1.5');
        sepLine.setAttribute('stroke-dasharray', '6,6');
        
        console.log('[ODONTOGRAMA SVG] Odontograma dibujado correctamente');
    }

    // Dibujar odontograma al cargar
    try {
        drawOdontograma();
        console.log('[ODONTOGRAMA SVG] Inicialización completa');
        

    } catch (e) {
        console.error('[ODONTOGRAMA SVG] Error al inicializar:', e);
    }
}

// Cuando el DOM esté listo, inicializar odontograma
$(document).ready(function() {
    inicializarOdontogramaSVG();
});
</script>
