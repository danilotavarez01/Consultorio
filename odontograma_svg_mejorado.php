<?php
// Este archivo contiene la implementación del odontograma SVG avanzado
// Debe ser incluido en forzar_odontograma_simple.php para reemplazar el odontograma básico

// No iniciar sesión ni hacer verificaciones aquí, este archivo es solo para incluir
// El control de si se debe mostrar o no está en forzar_odontograma_simple.php
?>

<div id="odontograma-dinamico" class="mb-4">
    <h5 class="mt-4 mb-2 text-primary">Odontograma</h5>
    <div id="odontograma-container" style="max-width: 900px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9;">
        <h2 style="color: #0056b3; text-align: center; margin-bottom: 20px;">Odontograma - Selección de Dientes</h2>        <div class="leyenda" style="display: flex; align-items: center; gap: 18px; margin: 18px 0 10px 0; justify-content: center;">
            <span class="leyenda-item" style="display: flex; align-items: center; gap: 6px; font-size: 15px;"><svg class="leyenda-svg" style="width: 28px; height: 32px;"><ellipse cx="14" cy="16" rx="12" ry="15" fill="url(#coronaGrad)" stroke="#333" stroke-width="1.5"/></svg> Normal</span>
            <span class="leyenda-item" style="display: flex; align-items: center; gap: 6px; font-size: 15px;"><svg class="leyenda-svg" style="width: 28px; height: 32px;"><ellipse cx="14" cy="16" rx="12" ry="15" fill="url(#coronaGradSel)" stroke="#ff6347" stroke-width="3"/></svg> Seleccionado</span>
            <span class="leyenda-item" style="display: flex; align-items: center; gap: 6px; font-size: 15px;"><svg class="leyenda-svg" style="width: 28px; height: 32px;"><ellipse cx="14" cy="16" rx="12" ry="15" fill="url(#coronaGradHover)" stroke="#1976d2" stroke-width="2.5"/></svg> Hover</span>
        </div>
        <div style="text-align: center; margin: 10px 0; padding: 8px; background-color: #f8f9fa; border-radius: 5px; color: #666;">
            <p style="margin: 0; font-size: 14px;">
                <span style="font-weight: bold;">Selección múltiple:</span> 
                Mantén presionada la tecla <kbd style="background: #eee; border: 1px solid #ccc; border-radius: 3px; padding: 1px 4px; font-size: 12px;">Ctrl</kbd> (o <kbd style="background: #eee; border: 1px solid #ccc; border-radius: 3px; padding: 1px 4px; font-size: 12px;">⌘ Cmd</kbd> en Mac) mientras haces clic para seleccionar múltiples dientes.
            </p>
        </div>
          <div class="mb-3">
            <div class="text-center mb-2">
                <button type="button" id="btn-seleccionar-todos-svg" class="btn btn-sm btn-outline-primary mx-1">Seleccionar todos</button>
                <button type="button" id="btn-deseleccionar-todos-svg" class="btn btn-sm btn-outline-secondary mx-1">Deseleccionar todos</button>
            </div>
            <div class="text-center mb-1">
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" id="btn-q1" class="btn btn-sm btn-outline-info mx-1" title="Superior derecho (18-11)">Cuadrante 1</button>
                    <button type="button" id="btn-q2" class="btn btn-sm btn-outline-info mx-1" title="Superior izquierdo (21-28)">Cuadrante 2</button>
                    <button type="button" id="btn-q3" class="btn btn-sm btn-outline-info mx-1" title="Inferior izquierdo (31-38)">Cuadrante 3</button>
                    <button type="button" id="btn-q4" class="btn btn-sm btn-outline-info mx-1" title="Inferior derecho (48-41)">Cuadrante 4</button>
                </div>
            </div>
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
            <input type="hidden" id="dientes_seleccionados" name="dientes_seleccionados" value="">
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
    { num: 48, nombre: 'Tercer molar inf. der.' }
];

// Función para dibujar el odontograma
function drawOdontograma() {
    console.log('[ODONTOGRAMA SVG] Dibujando odontograma...');
      // Global para dientes seleccionados - Reinicializado como array para mejor compatibilidad
    window.seleccionados = [];
    console.log('[ODONTOGRAMA] Inicializando array de dientes seleccionados');
    
    // Intentar cargar valores previos si existen
    try {
        const inputField = document.getElementById('dientes_seleccionados');
        const valorPrevio = inputField ? inputField.value : '';
        
        console.log('[ODONTOGRAMA] Valor previo encontrado:', valorPrevio);
        
        if (valorPrevio && valorPrevio.length > 0) {
            const valoresPrevios = valorPrevio.split(',');
            console.log('[ODONTOGRAMA] Valores previos detectados:', valoresPrevios);
            
            valoresPrevios.forEach(val => {
                const num = parseInt(val.trim());
                if (!isNaN(num) && !window.seleccionados.includes(num)) {
                    window.seleccionados.push(num);
                    console.log(`[ODONTOGRAMA] Añadido diente previo: ${num}`);
                }
            });
              console.log('[ODONTOGRAMA] Dientes cargados de valores previos:', window.seleccionados);
            
            // Función para actualizar la visualización de selecciones al cargar datos previos
            setTimeout(() => {
                applyVisualSelections();
            }, 100);
        } else {
            console.log('[ODONTOGRAMA] No se encontraron valores previos');
        }
        
        // Función para aplicar visualmente las selecciones
        function applyVisualSelections() {
            if (!Array.isArray(window.seleccionados) || window.seleccionados.length === 0) {
                console.log('[ODONTOGRAMA] No hay dientes para seleccionar visualmente');
                return;
            }
            
            console.log('[ODONTOGRAMA] Aplicando selecciones visuales para', window.seleccionados.length, 'dientes');
            
            // Recorrer todos los dientes y marcar los seleccionados
            document.querySelectorAll('.tooth-shape').forEach(tooth => {
                const num = parseInt(tooth.getAttribute('data-num'));
                
                if (window.seleccionados.includes(num)) {
                    tooth.classList.add('tooth-selected');
                    console.log(`[ODONTOGRAMA] Marcado visualmente el diente: ${num}`);
                }
            });
        }
    } catch (e) {
        console.error('[ODONTOGRAMA] Error al cargar valores previos:', e);
    }
    
    // Crear tooltip
    const tooltip = document.createElement('div');
    tooltip.className = 'tooth-tooltip';
    document.body.appendChild(tooltip);
    
    // Función para ubicar los dientes en un arco semi-circular
    function posicionarEnArco(index, total, radiusX, radiusY, centerX, centerY, arc='sup') {
        const angle = Math.PI / total * index + Math.PI / 2;
        const x = centerX + radiusX * Math.cos(angle);
        const y = arc === 'sup' ? 
            centerY - radiusY * Math.sin(angle) : 
            centerY + radiusY * Math.sin(angle);
        return { x, y };
    }    // Limpiar grupos de dientes primero
    const arcSuperior = document.getElementById('arc-superior');
    const arcInferior = document.getElementById('arc-inferior');
    if (arcSuperior) arcSuperior.innerHTML = '';
    if (arcInferior) arcInferior.innerHTML = '';
    
    // Configuración común
    const toothWidth = 44;
    const gap = 8;
    const totalTeethPerRow = 16; // 16 dientes por fila
    const rowWidth = toothWidth * totalTeethPerRow + gap * (totalTeethPerRow - 1);
    const startX = 450 - rowWidth / 2 + toothWidth / 2;
      // Función para dibujar una fila de dientes
    function drawToothRow(container, startIdx, endIdx, y, isTop) {
        const total = endIdx - startIdx + 1;
        
        for (let i = 0; i < total; i++) {
            // Mantener exactamente el mismo orden que en el odontograma original
            // Para cuadrantes superiores (1 y 2) e inferiores (4 y 3)
            const idx = startIdx + i;
            const diente = dientes[idx];
            const { num, nombre } = diente;
            const x = startX + i * (toothWidth + gap);
            
            drawTooth(container, idx, num, nombre, x, y, isTop ? 'sup' : 'inf', tooltip);
        }
    }
    
    // Dibujar dientes superiores (18-28) - cuadrantes 1 y 2
    drawToothRow(arcSuperior, 0, 15, 120, true);
    
    // Dibujar dientes inferiores (48-38) - cuadrantes 4 y 3
    drawToothRow(arcInferior, 16, 31, 320, false);
      // Dibujar un diente
    function drawTooth(g, idx, num, nombre, x, y, arc, tooltip) {
        // Path para dibujar la forma del diente según su tipo
        let coronaPath = '';
        let raizPath = '';
        
        // Determinar tipo de diente basado en su número dental (no en el índice)
        // Esto asegura que los molares, premolares, etc. tengan la forma correcta independientemente del orden
        const tipoDiente = getTipoDiente(num);
        
        // Superior
        if (arc === 'sup') {
            if (tipoDiente === 'molar') { // Molares
                coronaPath = `M ${x-18} ${y} Q ${x-10} ${y-18} ${x} ${y-12} Q ${x+10} ${y-18} ${x+18} ${y} Q ${x+12} ${y+8} ${x} ${y+8} Q ${x-12} ${y+8} ${x-18} ${y} Z`;
                raizPath = `M ${x-10} ${y+8} Q ${x-12} ${y+22} ${x-6} ${y+30} Q ${x} ${y+22} ${x+6} ${y+30} Q ${x+12} ${y+22} ${x+10} ${y+8} Z`;
            } else if (tipoDiente === 'premolar2') { // 2dos premolares
                coronaPath = `M ${x-14} ${y} Q ${x} ${y-16} ${x+14} ${y} Q ${x+10} ${y+8} ${x} ${y+8} Q ${x-10} ${y+8} ${x-14} ${y} Z`;
                raizPath = `M ${x-6} ${y+8} Q ${x-8} ${y+22} ${x-2} ${y+28} Q ${x} ${y+22} ${x+2} ${y+28} Q ${x+8} ${y+22} ${x+6} ${y+8} Z`;
            } else if (tipoDiente === 'premolar1') { // 1ros premolares
                coronaPath = `M ${x-12} ${y} Q ${x} ${y-18} ${x+12} ${y} Q ${x+8} ${y+8} ${x} ${y+8} Q ${x-8} ${y+8} ${x-12} ${y} Z`;
                raizPath = `M ${x-5} ${y+8} Q ${x-6} ${y+20} ${x} ${y+26} Q ${x+6} ${y+20} ${x+5} ${y+8} Z`;
            } else if (tipoDiente === 'canino') { // Caninos
                coronaPath = `M ${x-8} ${y} Q ${x} ${y-26} ${x+8} ${y} Q ${x+4} ${y+8} ${x} ${y+8} Q ${x-4} ${y+8} ${x-8} ${y} Z`;
                raizPath = `M ${x-2} ${y+8} Q ${x} ${y+30} ${x+2} ${y+8} Z`;
            } else { // Incisivos
                coronaPath = `M ${x-7} ${y} Q ${x} ${y-18} ${x+7} ${y} Q ${x+5} ${y+8} ${x} ${y+8} Q ${x-5} ${y+8} ${x-7} ${y} Z`;
                raizPath = `M ${x-2} ${y+8} Q ${x} ${y+24} ${x+2} ${y+8} Z`;
            }
        } else { // Inferior
            if (tipoDiente === 'molar') { // Molares
                coronaPath = `M ${x-18} ${y} Q ${x-10} ${y+18} ${x} ${y+12} Q ${x+10} ${y+18} ${x+18} ${y} Q ${x+12} ${y-8} ${x} ${y-8} Q ${x-12} ${y-8} ${x-18} ${y} Z`;
                raizPath = `M ${x-10} ${y-8} Q ${x-12} ${y-22} ${x-6} ${y-30} Q ${x} ${y-22} ${x+6} ${y-30} Q ${x+12} ${y-22} ${x+10} ${y-8} Z`;
            } else if (tipoDiente === 'premolar2') { // 2dos premolares
                coronaPath = `M ${x-14} ${y} Q ${x} ${y+16} ${x+14} ${y} Q ${x+10} ${y-8} ${x} ${y-8} Q ${x-10} ${y-8} ${x-14} ${y} Z`;
                raizPath = `M ${x-6} ${y-8} Q ${x-8} ${y-22} ${x-2} ${y-28} Q ${x} ${y-22} ${x+2} ${y-28} Q ${x+8} ${y-22} ${x+6} ${y-8} Z`;
            } else if (tipoDiente === 'premolar1') { // 1ros premolares
                coronaPath = `M ${x-12} ${y} Q ${x} ${y+18} ${x+12} ${y} Q ${x+8} ${y-8} ${x} ${y-8} Q ${x-8} ${y-8} ${x-12} ${y} Z`;
                raizPath = `M ${x-5} ${y-8} Q ${x-6} ${y-20} ${x} ${y-26} Q ${x+6} ${y-20} ${x+5} ${y-8} Z`;
            } else if (tipoDiente === 'canino') { // Caninos
                coronaPath = `M ${x-8} ${y} Q ${x} ${y+26} ${x+8} ${y} Q ${x+4} ${y-8} ${x} ${y-8} Q ${x-4} ${y-8} ${x-8} ${y} Z`;
                raizPath = `M ${x-2} ${y-8} Q ${x} ${y-30} ${x+2} ${y-8} Z`;
            } else { // Incisivos
                coronaPath = `M ${x-7} ${y} Q ${x} ${y+18} ${x+7} ${y} Q ${x+5} ${y-8} ${x} ${y-8} Q ${x-5} ${y-8} ${x-7} ${y} Z`;
                raizPath = `M ${x-2} ${y-8} Q ${x} ${y-24} ${x+2} ${y-8} Z`;
            }
        }
        
        // Función auxiliar para determinar el tipo de diente basado en su número
        function getTipoDiente(numero) {
            // Normalizar el número dental para obtener su posición (1-8)
            let posicion;
            if (numero >= 11 && numero <= 18) posicion = 9 - (numero - 10); // 11->8, 12->7, ..., 18->1
            else if (numero >= 21 && numero <= 28) posicion = numero - 20; // 21->1, 22->2, ..., 28->8
            else if (numero >= 31 && numero <= 38) posicion = numero - 30; // 31->1, 32->2, ..., 38->8
            else if (numero >= 41 && numero <= 48) posicion = 9 - (numero - 40); // 41->8, 42->7, ..., 48->1
            
            // Determinar el tipo de diente basado en su posición
            if (posicion >= 6) return 'molar';
            else if (posicion === 5) return 'premolar2';
            else if (posicion === 4) return 'premolar1';
            else if (posicion === 3) return 'canino';
            else return 'incisivo'; // 1 y 2 son incisivos
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
          // Si estaba previamente seleccionado, marcar
        if (window.seleccionados.includes(num)) {
            corona.classList.add('tooth-selected');
        }
        
        toothG.appendChild(corona);        // Eventos de selección y tooltip
        corona.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            console.log('Click en diente:', num, 'Seleccionados antes:', window.seleccionados);
            
            // Comprobar si la tecla Ctrl está presionada para permitir selección múltiple
            // Si no está presionada, limpiamos selecciones previas solo si no es un toggle del mismo diente
            const indexInSeleccionados = window.seleccionados.indexOf(num);
            const estaSeleccionado = indexInSeleccionados !== -1;
            
            if (!e.ctrlKey && !e.metaKey && !estaSeleccionado) {
                // Limpiar selecciones previas solo si no estamos manteniendo Ctrl presionado
                const dientesSeleccionados = document.querySelectorAll('.tooth-selected');
                console.log('Limpiando selecciones previas, dientes seleccionados:', dientesSeleccionados.length);
                
                // Limpiar los elementos visuales
                dientesSeleccionados.forEach(tooth => {
                    tooth.classList.remove('tooth-selected');
                });
                
                // Limpiar el array de seleccionados
                window.seleccionados = [];
            }
            
            // Toggle de la selección del diente actual
            if (estaSeleccionado) {
                // Deseleccionar: quitar del array y quitar clase visual
                console.log('Deseleccionando diente actual:', num);
                window.seleccionados.splice(indexInSeleccionados, 1);
                corona.classList.remove('tooth-selected');
            } else {
                // Seleccionar: añadir al array y añadir clase visual
                console.log('Seleccionando diente actual:', num);
                window.seleccionados.push(num);
                corona.classList.add('tooth-selected');
            }
            
            console.log('Seleccionados después:', window.seleccionados);
            updateSeleccionados();
            return false;
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
    }    // Función para actualizar la lista de dientes seleccionados    window.updateSeleccionados = function() {
        // Asegurarse que window.seleccionados sea un array
        if (!Array.isArray(window.seleccionados)) {
            console.warn('window.seleccionados no es un array, convirtiendo...');
            window.seleccionados = Array.isArray(window.seleccionados) ? window.seleccionados : 
                                   (window.seleccionados ? Array.from(window.seleccionados) : []);
        }
        
        // Asegurar primero que todos son números y limpiar posibles valores no numéricos
        window.seleccionados = window.seleccionados
            .map(n => typeof n === 'string' ? parseInt(n) : n)
            .filter(n => !isNaN(n) && n > 0);
            
        // Ordenar el Array por número y eliminar posibles duplicados
        const seleccionadosArr = [...new Set(window.seleccionados)].sort((a, b) => a - b);
        console.log('updateSeleccionados() - Actualizando lista con dientes:', seleccionadosArr);
        
        // Actualizar el campo oculto con todos los valores
        const inputField = document.getElementById('dientes_seleccionados');
        if (inputField) {
            inputField.value = seleccionadosArr.join(',');
            console.log('Campo dientes_seleccionados actualizado con valor:', inputField.value);
        } else {
            console.warn('Campo dientes_seleccionados no encontrado');
        }
          // Actualizar la lista visual HTML
        const listaHTML = document.getElementById('dientes-seleccionados-lista');
        if (!listaHTML) {
            console.warn('Lista HTML de dientes seleccionados no encontrada');
            return;
        }
        
        // Los elementos ya fueron convertidos a números y filtrados
        const seleccionadosNumeros = seleccionadosArr;
        
        if (seleccionadosNumeros.length === 0) {
            listaHTML.innerHTML = '<span style="color: #777;" id="seleccionados-texto">Ninguno seleccionado</span>';
            console.log('No hay dientes seleccionados, mostrando mensaje vacío');
        } else {
            console.log('Hay', seleccionadosNumeros.length, 'dientes seleccionados, actualizando HTML');
            
            // Generar una representación visual más clara de los dientes seleccionados
            let html = '';
            
            // Agrupar por cuadrantes para mejor visualización
            // Asegurar que los números sean tratados como enteros
            const cuadrante1 = seleccionadosNumeros.filter(n => n >= 11 && n <= 18).sort((a, b) => a - b);
            const cuadrante2 = seleccionadosNumeros.filter(n => n >= 21 && n <= 28).sort((a, b) => a - b);
            const cuadrante3 = seleccionadosNumeros.filter(n => n >= 31 && n <= 38).sort((a, b) => a - b);
            const cuadrante4 = seleccionadosNumeros.filter(n => n >= 41 && n <= 48).sort((a, b) => a - b);
            
            console.log('Dientes por cuadrante:', 
                'C1:', cuadrante1.length, 
                'C2:', cuadrante2.length, 
                'C3:', cuadrante3.length, 
                'C4:', cuadrante4.length);
            
            // Añadir un grupo si tiene elementos
            function agregarGrupo(nums, nombre) {
                if (nums.length > 0) {
                    const grupo = `
                        <div style="margin-bottom: 8px;">
                            <span style="color: #666; font-size: 14px; margin-right: 6px;">${nombre}:</span>
                            ${nums.map(n => `<span class="badge badge-primary mr-1" style="background: #007bff; color: white; padding: 3px 6px; border-radius: 4px; display: inline-block; margin-right: 4px;">${n}</span>`).join('')}
                        </div>`;
                    return grupo;
                }
                return '';
            }
            
            // Construir HTML por cuadrante en el mismo orden que el odontograma
            html += agregarGrupo(cuadrante1, 'Cuadrante 1 (Superior Derecho)');
            html += agregarGrupo(cuadrante2, 'Cuadrante 2 (Superior Izquierdo)');
            html += agregarGrupo(cuadrante3, 'Cuadrante 3 (Inferior Izquierdo)');
            html += agregarGrupo(cuadrante4, 'Cuadrante 4 (Inferior Derecho)');
            
            // Si hay algún diente que no pertenece a ningún cuadrante, añadirlo como "Otros"
            const otros = seleccionadosArr.filter(n => 
                !(n >= 11 && n <= 18) && 
                !(n >= 21 && n <= 28) && 
                !(n >= 31 && n <= 38) && 
                !(n >= 41 && n <= 48)
            );
            
            if (otros.length > 0) {
                html += agregarGrupo(otros, 'Otros');
                console.log('Hay dientes fuera de cuadrantes estándar:', otros);
            }
            
            listaHTML.innerHTML = html;
        }
        
        console.log('Dientes seleccionados actualizados:', seleccionadosArr.join(', '));
    }        // Activar interacciones y precargar selecciones
    setupTeethInteractions();
      // Sincronizar seleccionados con los elementos DOM que tengan la clase tooth-selected
    const syncSeleccionadosConDOM = function() {
        const selectedTeeth = document.querySelectorAll('.tooth-shape.tooth-selected');
        const numsSeleccionados = [];
        
        selectedTeeth.forEach(tooth => {
            const num = parseInt(tooth.getAttribute('data-num'));
            if (!isNaN(num) && !numsSeleccionados.includes(num)) {
                numsSeleccionados.push(num);
            }
        });
        
        // Asegurarse que window.seleccionados contiene solo números para la comparación
        const seleccionadosNumeros = Array.isArray(window.seleccionados) ? 
            window.seleccionados.map(n => typeof n === 'string' ? parseInt(n) : n).filter(n => !isNaN(n)) : 
            [];
            
        // Ordenar ambos arrays para comparación
        const numsOrdenados = [...numsSeleccionados].sort((a, b) => a - b);
        const selOrdenados = [...seleccionadosNumeros].sort((a, b) => a - b);
        
        // Si hay diferencias entre los dientes visualmente seleccionados y el array
        if (JSON.stringify(numsOrdenados) !== JSON.stringify(selOrdenados)) {
            console.warn('Diferencia detectada entre DOM y array, sincronizando...');
            console.log('DOM tiene:', numsOrdenados);
            console.log('Array tiene:', selOrdenados);
            window.seleccionados = numsSeleccionados;
        }
    };
    
    // Sincronizar y actualizar
    syncSeleccionadosConDOM();
    updateSeleccionados();
    
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

// Configuración de interacciones adicionales
function setupTeethInteractions() {
    // Asegurar que window.seleccionados sea un array
    if (!Array.isArray(window.seleccionados)) {
        console.warn('setupTeethInteractions: window.seleccionados no es un array, convirtiendo...');
        window.seleccionados = Array.isArray(window.seleccionados) ? window.seleccionados : 
                              (window.seleccionados ? Array.from(window.seleccionados) : []);
    }

    // Configurar el comportamiento de selección múltiple
    const toothElements = document.querySelectorAll('.tooth-shape');
    
    // Asegurarnos de que el evento click esté correctamente manejado en cada diente
    toothElements.forEach(tooth => {
        // Asegurarse que el elemento tenga los atributos necesarios
        const num = parseInt(tooth.getAttribute('data-num'));
        
        // Si ya está seleccionado por valor previo, aplicar clase
        if (window.seleccionados && window.seleccionados.includes(num)) {
            tooth.classList.add('tooth-selected');
        }
    });
}

// Configurar los botones de seleccionar/deseleccionar todos
function setupSelectionButtons() {    document.getElementById('btn-seleccionar-todos-svg').addEventListener('click', function() {
        console.log('[ODONTOGRAMA] Seleccionando todos los dientes');
        // Limpiar array primero para evitar posibles problemas
        window.seleccionados = [];
        
        const toothElements = document.querySelectorAll('.tooth-shape');
        toothElements.forEach(tooth => {
            const num = parseInt(tooth.getAttribute('data-num'));
            if (!isNaN(num)) {
                window.seleccionados.push(num);
                tooth.classList.add('tooth-selected');
                console.log(`[ODONTOGRAMA] Añadido diente: ${num}`);
            }
        });
        
        updateSeleccionados();
        console.log('[ODONTOGRAMA] Todos los dientes seleccionados:', window.seleccionados.length);
    });
    
    document.getElementById('btn-deseleccionar-todos-svg').addEventListener('click', function() {
        console.log('[ODONTOGRAMA] Deseleccionando todos los dientes');
        
        const toothElements = document.querySelectorAll('.tooth-shape');
        toothElements.forEach(tooth => {
            tooth.classList.remove('tooth-selected');
        });
        
        // Limpiar completamente el array para asegurar que está vacío
        window.seleccionados = [];
        updateSeleccionados();
        console.log('[ODONTOGRAMA] Todos los dientes deseleccionados');
    });    // Función auxiliar para seleccionar dientes por rango    function seleccionarPorRango(min, max) {
        console.log(`Seleccionando dientes por rango: ${min}-${max}`);
        
        // Asegurarse que min y max son números
        min = parseInt(min);
        max = parseInt(max);
        
        if (isNaN(min) || isNaN(max)) {
            console.error('Error: Los límites del rango deben ser números válidos');
            return;
        }
        
        // Primero deseleccionamos todos
        document.querySelectorAll('.tooth-shape').forEach(tooth => {
            tooth.classList.remove('tooth-selected');
        });
        
        // Vaciar el array de seleccionados
        window.seleccionados = [];
        
        // Luego seleccionamos el rango específico
        document.querySelectorAll('.tooth-shape').forEach(tooth => {
            const num = parseInt(tooth.getAttribute('data-num'));
            if (isNaN(num)) return;
            
            // Para rangos normales (ej: 21-28)
            // o para rangos invertidos (ej: 18-11)
            let enRango = false;
            
            if (min <= max) {
                // Rango normal (ascendente)
                enRango = num >= min && num <= max;
            } else {
                // Rango invertido (descendente)
                enRango = num >= max && num <= min;
            }
            
            if (enRango) {
                console.log(`Seleccionando diente ${num} en el rango ${min}-${max}`);
                window.seleccionados.push(parseInt(num)); // Asegurarse que se guarda como número
                tooth.classList.add('tooth-selected');
            }
        });
        
        console.log('Después de selección por rango, dientes:', window.seleccionados);
        updateSeleccionados();
    }
    
    // Botones para seleccionar cuadrantes
    document.getElementById('btn-q1').addEventListener('click', function() {
        seleccionarPorRango(18, 11); // Cuadrante 1 (Superior Derecho)
    });
    
    document.getElementById('btn-q2').addEventListener('click', function() {
        seleccionarPorRango(21, 28); // Cuadrante 2 (Superior Izquierdo)
    });
    
    document.getElementById('btn-q3').addEventListener('click', function() {
        seleccionarPorRango(31, 38); // Cuadrante 3 (Inferior Izquierdo)
    });
    
    document.getElementById('btn-q4').addEventListener('click', function() {
        seleccionarPorRango(48, 41); // Cuadrante 4 (Inferior Derecho)
    });
}

// Dibujar odontograma al cargar
try {
    drawOdontograma();
    // Configurar los botones de selección múltiple
    setupSelectionButtons();
    console.log('[ODONTOGRAMA SVG] Inicialización completa');
} catch (e) {
    console.error('[ODONTOGRAMA SVG] Error al inicializar:', e);
}
</script>
