<?php
// Archivo para forzar la carga del odontograma en la nueva consulta si la especialidad es odontología
// Se debe incluir este archivo en nueva_consulta.php

// Obtener la especialidad configurada en el sistema
require_once "config.php";
$mostrarOdontograma = false;
try {
    $stmt = $conn->prepare("SELECT e.nombre FROM configuracion c 
                           JOIN especialidades e ON c.especialidad_id = e.id 
                           WHERE c.id = 1");
    $stmt->execute();
    $especialidad = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Determinar si se debe mostrar el odontograma (si es odontología)
    if ($especialidad) {
        $nombreEspecialidad = strtolower(trim($especialidad['nombre']));
        $especialidadesOdontologicas = ['odontologia', 'odontología', 'dental', 
                                      'odontologica', 'odontológica', 'dentista', 
                                      'odontopediatria', 'odontopediatría'];
        
        // Verificar si el nombre de la especialidad está en la lista o contiene "odonto" o "dental"
        if (in_array($nombreEspecialidad, $especialidadesOdontologicas) || 
            strpos($nombreEspecialidad, 'odonto') !== false ||
            strpos($nombreEspecialidad, 'dental') !== false) {
            $mostrarOdontograma = true;
        }
    }
} catch (Exception $e) {
    // Si hay error, no mostrar el odontograma
    $mostrarOdontograma = false;
}
?>
<script>
// Establecer una variable global para indicar si mostrar o no el odontograma
window.MOSTRAR_ODONTOGRAMA = <?php echo $mostrarOdontograma ? 'true' : 'false'; ?>;
<?php if ($mostrarOdontograma): ?>
window.ESPECIALIDAD_NOMBRE = '<?php echo htmlspecialchars($especialidad['nombre']); ?>';
<?php endif; ?>

// Función para insertar directamente el odontograma
function insertarOdontograma() {
    console.log('Insertando odontograma con diseño profesional SVG');
    
    // Solo insertar si no existe ya
    if ($('#odontograma-dinamico').length === 0) {
        var odontogramaHtml = `
            <div id="odontograma-dinamico" class="mb-4">
                <h5 class="mt-4 mb-2 text-primary">Odontograma</h5>
                <div id="odontograma-container" style="max-width: 900px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9;">
                    <h2 style="color: #0056b3; text-align: center; margin-bottom: 20px;">Odontograma - Selección de Dientes</h2>
                    
                    <div class="leyenda" style="display: flex; align-items: center; gap: 18px; margin: 18px 0 10px 0; justify-content: center;">
                        <span class="leyenda-item" style="display: flex; align-items: center; gap: 6px; font-size: 15px;">
                            <svg class="leyenda-svg" width="28" height="32"><ellipse cx="14" cy="16" rx="12" ry="15" fill="url(#coronaGrad)" stroke="#333" stroke-width="1.5"/></svg> Normal
                        </span>
                        <span class="leyenda-item" style="display: flex; align-items: center; gap: 6px; font-size: 15px;">
                            <svg class="leyenda-svg" width="28" height="32"><ellipse cx="14" cy="16" rx="12" ry="15" fill="url(#coronaGradSel)" stroke="#ff6347" stroke-width="3"/></svg> Seleccionado
                        </span>
                        <span class="leyenda-item" style="display: flex; align-items: center; gap: 6px; font-size: 15px;">
                            <svg class="leyenda-svg" width="28" height="32"><ellipse cx="14" cy="16" rx="12" ry="15" fill="url(#coronaGradHover)" stroke="#1976d2" stroke-width="2.5"/></svg> Hover
                        </span>
                    </div>
                      <div class="odontograma-svg-container" style="position: relative; width: 100%; overflow: visible;">
                        <svg id="odontograma-svg" class="odontograma-svg" width="100%" height="560" viewBox="0 0 900 560" style="display: block; margin: 0 auto; background: #fff; border-radius: 12px; box-shadow: 0 3px 10px #0002;">
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
                            </defs>                            <!-- Etiqueta Maxilar Superior -->
                            <text x="450" y="50" text-anchor="middle" font-size="28" fill="#1976d2" font-weight="bold">Maxilar Superior</text>
                            <ellipse cx="450" cy="170" rx="390" ry="120" fill="#e3f2fd" opacity="0.5" />
                            <!-- Líneas divisorias de cuadrantes superiores -->
                            <line x1="450" y1="80" x2="450" y2="260" stroke="#bbb" stroke-width="2" stroke-dasharray="8,6" />
                            <line x1="200" y1="170" x2="700" y2="170" stroke="#bbb" stroke-width="2" stroke-dasharray="8,6" />
                            <!-- Dientes superiores (arco) -->
                            <g id="arc-superior"></g>                            <!-- Cuadrantes -->
                            <text x="270" y="100" font-size="16" fill="#1976d2" font-weight="bold">Cuadrante 1</text>
                            <text x="630" y="100" font-size="16" fill="#1976d2" font-weight="bold">Cuadrante 2</text>
                              <!-- Etiqueta Maxilar Inferior -->
                            <text x="450" y="520" text-anchor="middle" font-size="28" fill="#388e3c" font-weight="bold">Maxilar Inferior</text>
                            <ellipse cx="450" cy="370" rx="390" ry="120" fill="#e8f5e9" opacity="0.5" />
                            <!-- Líneas divisorias de cuadrantes inferiores -->
                            <line x1="450" y1="280" x2="450" y2="460" stroke="#bbb" stroke-width="2" stroke-dasharray="8,6" />
                            <line x1="200" y1="370" x2="700" y2="370" stroke="#bbb" stroke-width="2" stroke-dasharray="8,6" />
                            <!-- Dientes inferiores (arco) -->
                            <g id="arc-inferior"></g>
                            <!-- Cuadrantes -->
                            <text x="270" y="440" font-size="16" fill="#388e3c" font-weight="bold">Cuadrante 4</text>
                            <text x="630" y="440" font-size="16" fill="#388e3c" font-weight="bold">Cuadrante 3</text>
                        </svg>
                        
                <div id="tooltip-odontograma" style="position: absolute; background: #fffbe7; border: 1px solid #bfa76f; 
                             border-radius: 6px; padding: 8px 14px; font-size: 15px; font-weight: bold; color: #333; pointer-events: none; 
                             box-shadow: 0 3px 12px rgba(0,0,0,0.15); z-index: 10; display: none; transition: opacity 0.2s;"></div>
                    </div>
                    
                    <div class="mt-4" style="padding: 15px; background-color: white; border-radius: 8px; box-shadow: 0 1px 3px #0001;">
                        <h4 style="color: #444; margin-bottom: 10px; font-size: 16px;">Dientes seleccionados:</h4>
                        <div id="dientes-seleccionados-lista" style="min-height: 30px;"><span style="color: #777;">Ninguno seleccionado</span></div>
                        <input type="hidden" id="dientes_seleccionados" name="dientes_seleccionados" value="">
                    </div>
                </div>
            </div>`;
        
        // Insertar al inicio de campos_dinamicos
        $('#campos_dinamicos').prepend(odontogramaHtml);
        
        // Una vez insertado el HTML, inicializar el odontograma SVG
        inicializarOdontogramaSVG();
        
        console.log('Odontograma profesional SVG insertado correctamente');
    } else {
        console.log('El odontograma ya existe, no se inserta de nuevo');
    }
}

// Función para inicializar el odontograma SVG con todos los dientes en forma de arco dental profesional
function inicializarOdontogramaSVG() {
    console.log("[ODONTOGRAMA] Inicializando odontograma SVG con arco dental anatómico profesional");
    // Datos de los dientes con sus nombres
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
    
    const seleccionados = new Set();
    const tooltip = document.getElementById('tooltip-odontograma');      // Función para obtener la posición de un diente en el arco dental anatómico
    function getToothPosition(idx, arc) {
        const total = 16;
        const cx = 450;
        const cy = arc === 'sup' ? 170 : 370;
        
        // Parámetros optimizados para forma dental anatómicamente realista
        // Arco dental elíptico con ajustes por tipo de diente
        const a_base = 320;  // Eje mayor base (ancho) de la elipse - aumentado para mejor distribución
        const b = 100;   // Eje menor (alto) de la elipse - aumentado para arco más natural
        
        // Factor de ajuste para el eje mayor según la posición del diente
        let t = idx / (total - 1);
        let a_factor = 1.0;
        
        // Hacer el arco más ancho en los molares, más estrecho en los incisivos
        // Ajustes más pronunciados para mejor representación anatómica
        if (t <= 0.2 || t >= 0.8) {
            a_factor = 1.15; // Molares más separados horizontalmente
        } else if (t > 0.2 && t < 0.4 || t > 0.6 && t < 0.8) {
            a_factor = 1.05; // Premolares y caninos ligeramente más estrechos
        } else {
            a_factor = 0.90; // Incisivos más juntos, formando la parte frontal del arco
        }
        
        const a = a_base * a_factor;
        
        // Ángulos optimizados para distribuir dientes en arco bucal (en radianes)
        // Ajuste para conseguir una parábola más natural y anatómica
        const angleStart = arc === 'sup' ? (Math.PI * 1.08) : (Math.PI * 0.92);
        const angleEnd = arc === 'sup' ? (Math.PI * 1.92) : (Math.PI * 2.08);
        
        let angle = angleStart + (angleEnd - angleStart) * t;
        
        // Ajuste más natural para los incisivos centrales y laterales
        if (t > 0.4 && t < 0.6) {
            // Incisivos centrales más adelantados y juntos - mejor curvatura frontal
            const centerAdjust = (arc === 'sup' ? -1 : 1) * 0.18 * Math.pow(Math.cos((t-0.5)*Math.PI*5), 2);
            angle += centerAdjust;
        } else if ((t >= 0.3 && t <= 0.4) || (t >= 0.6 && t <= 0.7)) {
            // Incisivos laterales ligeramente adelantados - transición más suave
            const lateralAdjust = (arc === 'sup' ? -1 : 1) * 0.1 * Math.pow(Math.cos((t-0.5)*Math.PI*2.5), 2);
            angle += lateralAdjust;
        } else if ((t >= 0.2 && t < 0.3) || (t > 0.7 && t <= 0.8)) {
            // Ajuste para caninos - forma más natural en esquinas del arco
            const canineAdjust = (arc === 'sup' ? -1 : 1) * 0.05 * Math.pow(Math.cos((t-0.5)*Math.PI*1.8), 2);
            angle += canineAdjust;
        }
        
        // Mayor separación entre molares posteriores
        if (t < 0.15) angle -= 0.1 * (0.15-t)/0.15;
        if (t > 0.85) angle += 0.1 * (t-0.85)/0.15;
          // Posición elíptica optimizada para anatomía dental
        const x = cx + a * Math.cos(angle);
        
        // Ajuste vertical mejorado: curvas naturales del maxilar y mandíbula
        // Superior: curva de Spee - incisivos ligeramente más bajos que molares
        // Inferior: curva opuesta - incisivos ligeramente más altos que molares
        const curvaSpee = Math.pow(Math.abs(t - 0.5) * 2, 2) * 15; // Parábola suave para curva natural
        const yOffset = arc === 'sup' 
            ? -20 + curvaSpee  // Superior: incisivos más bajos
            : 20 - curvaSpee;  // Inferior: incisivos más altos
        
        // Ajuste adicional para los molares traseros (ligeramente más altos/bajos según el arco)
        const molarAdjust = (t <= 0.15 || t >= 0.85) ? (arc === 'sup' ? -8 : 8) : 0;
        
        const y = cy + b * Math.sin(angle) + yOffset + molarAdjust;
        
        // Rotación mejorada del diente para seguir el arco (en grados)
        // Ajustar rotación para que los dientes sigan la curva natural del arco
        const baseRot = (angle - Math.PI/2) * 57.3;
        
        // Los incisivos deben estar más rectos, los molares más angulados para seguir el contorno bucal
        let rotFactor;
        if (t > 0.4 && t < 0.6) {
            rotFactor = 0.4; // Incisivos centrales casi rectos
        } else if ((t >= 0.3 && t <= 0.4) || (t >= 0.6 && t <= 0.7)) {
            rotFactor = 0.7; // Incisivos laterales ligeramente angulados
        } else if ((t >= 0.2 && t < 0.3) || (t > 0.7 && t <= 0.8)) {
            rotFactor = 0.9; // Caninos y premolares con angulación moderada
        } else {
            rotFactor = 1.2; // Molares más angulados
        }
        
        const rot = baseRot * rotFactor;
        
        return { x, y, angle, rot };
    }
    
    // Función para dibujar un diente    function drawTooth(g, x, y, angle, num, nombre, arc, idx, rot=0) {
        let coronaPath, raizPath;
        
        // Factor de escala según tipo de diente (tamaño anatómico proporcional)
        let scaleFactor = 1.0;
        
        // Escalar dientes según su tipo anatómico
        if (idx <= 2 || idx >= 13 || idx <= 18 || idx >= 29) { // Molares (más grandes)
            scaleFactor = 1.1;
        } else if ((idx === 3 || idx === 12) || (idx === 19 || idx === 28)) { // Premolares 
            scaleFactor = 0.95;
        } else if ((idx === 4 || idx === 11) || (idx === 20 || idx === 27)) { // Premolares
            scaleFactor = 0.92;
        } else if ((idx === 5 || idx === 10) || (idx === 21 || idx === 26)) { // Caninos
            scaleFactor = 0.9;
        } else { // Incisivos (más pequeños)
            scaleFactor = 0.85;
        }
        
        // Definir la forma del diente según su posición y tipo anatómico
        if (arc === 'sup') { // Dientes superiores
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
        } else { // Dientes inferiores
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
        
        // Crear el grupo para el diente
        const toothG = document.createElementNS('http://www.w3.org/2000/svg', 'g');
        toothG.setAttribute('transform', `rotate(${rot} ${x} ${y})`);
        
        // Crear la raíz del diente
        const raiz = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        raiz.setAttribute('d', raizPath);
        raiz.setAttribute('fill', 'url(#raizGrad)');
        raiz.setAttribute('stroke', '#bfa76f');
        raiz.setAttribute('stroke-width', '1.2');
        toothG.appendChild(raiz);
        
        // Crear la corona del diente
        const corona = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        corona.setAttribute('d', coronaPath);
        corona.setAttribute('fill', 'url(#coronaGrad)');
        corona.setAttribute('stroke', '#333');
        corona.setAttribute('stroke-width', '1.5');
        corona.setAttribute('data-num', num);
        corona.setAttribute('data-nombre', nombre);
        corona.setAttribute('class', 'tooth-shape');
        corona.setAttribute('style', 'cursor: pointer; filter: drop-shadow(0 1px 2px #0002); transition: fill 0.2s, stroke-width 0.2s, filter 0.2s;');
        toothG.appendChild(corona);
        
        // Agregar eventos a la corona del diente
        corona.addEventListener('click', function(e) {
            if (seleccionados.has(num)) {
                seleccionados.delete(num);
                corona.setAttribute('fill', 'url(#coronaGrad)');
                corona.setAttribute('stroke', '#333');
                corona.setAttribute('stroke-width', '1.5');
                corona.setAttribute('filter', 'drop-shadow(0 1px 2px #0002)');
                corona.classList.remove('tooth-selected');
            } else {
                seleccionados.add(num);
                corona.setAttribute('fill', 'url(#coronaGradSel)');
                corona.setAttribute('stroke', '#ff6347');
                corona.setAttribute('stroke-width', '3');
                corona.setAttribute('filter', 'drop-shadow(0 2px 8px #ff634755)');
                corona.classList.add('tooth-selected');
            }
            actualizarListaDientes();
        });
        
        corona.addEventListener('mouseenter', function(e) {
            if (!corona.classList.contains('tooth-selected')) {
                corona.setAttribute('fill', 'url(#coronaGradHover)');
                corona.setAttribute('stroke-width', '2.5');
                corona.setAttribute('filter', 'drop-shadow(0 2px 6px #1976d255)');
            }
            tooltip.textContent = `${num} - ${nombre}`;
            tooltip.style.display = 'block';
        });
        
        corona.addEventListener('mouseleave', function(e) {
            if (!corona.classList.contains('tooth-selected')) {
                corona.setAttribute('fill', 'url(#coronaGrad)');
                corona.setAttribute('stroke', '#333');
                corona.setAttribute('stroke-width', '1.5');
                corona.setAttribute('filter', 'drop-shadow(0 1px 2px #0002)');
            }
            tooltip.style.display = 'none';
        });
        
        corona.addEventListener('mousemove', function(e) {
            const rect = e.target.ownerSVGElement.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            tooltip.style.left = (x + 18) + 'px';
            tooltip.style.top = (y - 10) + 'px';
        });
        
        g.appendChild(toothG);
          // Etiqueta de número debajo/encima del diente, optimizada para la nueva disposición
        const label = document.createElementNS('http://www.w3.org/2000/svg', 'text');
        label.setAttribute('x', x);
        
        // Ajustar posición Y según el arco y la posición en el arco
        let labelYOffset;
        
        if (arc === 'sup') {
            // Para el arco superior, las etiquetas van debajo
            // Ajustar la distancia según la posición del diente (más cerca en incisivos, más lejos en molares)
            if (idx <= 2 || idx >= 13) { // Molares
                labelYOffset = 42;
            } else if ((idx === 3 || idx === 12) || (idx === 4 || idx === 11)) { // Premolares
                labelYOffset = 40;
            } else {
                labelYOffset = 38; // Incisivos y caninos
            }
        } else {
            // Para el arco inferior, las etiquetas van encima
            if (idx <= 18 || idx >= 29) { // Molares
                labelYOffset = -24;
            } else if ((idx === 19 || idx === 28) || (idx === 20 || idx === 27)) { // Premolares
                labelYOffset = -22;
            } else {
                labelYOffset = -20; // Incisivos y caninos
            }
        }
        
        label.setAttribute('y', y + labelYOffset);
        label.setAttribute('text-anchor', 'middle');
        label.setAttribute('font-size', '13px');
        label.setAttribute('font-weight', 'bold');
        label.setAttribute('fill', arc === 'sup' ? '#1976d2' : '#388e3c');
        label.setAttribute('stroke', '#fff');
        label.setAttribute('stroke-width', '0.5');
        label.setAttribute('pointer-events', 'none');
        label.textContent = num;
        g.appendChild(label);
    }    // Dibujar todos los dientes en el odontograma con disposición anatómica optimizada
    function drawOdontograma() {
        // Obtener los grupos para los arcos superior e inferior
        const arcSup = document.getElementById('arc-superior');
        const arcInf = document.getElementById('arc-inferior');
        
        // Limpiar los grupos antes de dibujar para evitar duplicados
        arcSup.innerHTML = '';
        arcInf.innerHTML = '';
        
        // Dibujar dientes superiores del cuadrante 1 y 2 (18-28)
        // Ordenamos por número para asegurar la correcta secuencia anatómica
        for (let i = 0; i < 16; i++) {
            const pos = getToothPosition(i, 'sup');
            drawTooth(arcSup, pos.x, pos.y, pos.angle, dientes[i].num, dientes[i].nombre, 'sup', i, pos.rot);
        }
        
        // Dibujar dientes inferiores del cuadrante 3 y 4 (48-31)
        for (let i = 0; i < 16; i++) {
            const pos = getToothPosition(i, 'inf');
            drawTooth(arcInf, pos.x, pos.y, pos.angle, dientes[i+16].num, dientes[i+16].nombre, 'inf', i+16, pos.rot);
        }
        
        // Añadir líneas de referencia sutil para mostrar forma del arco
        const svg = document.getElementById('odontograma-svg');
        
        // Línea de referencia para arco superior
        const pathSup = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        pathSup.setAttribute('d', 'M 120,170 Q 450,50 780,170');
        pathSup.setAttribute('fill', 'none');
        pathSup.setAttribute('stroke', '#1976d230');
        pathSup.setAttribute('stroke-width', '1');
        pathSup.setAttribute('stroke-dasharray', '3,3');
        svg.appendChild(pathSup);
        
        // Línea de referencia para arco inferior
        const pathInf = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        pathInf.setAttribute('d', 'M 120,370 Q 450,490 780,370');
        pathInf.setAttribute('fill', 'none');
        pathInf.setAttribute('stroke', '#388e3c30');
        pathInf.setAttribute('stroke-width', '1');
        pathInf.setAttribute('stroke-dasharray', '3,3');
        svg.appendChild(pathInf);
        
        // Añadir línea divisoria central
        const svg = document.getElementById('odontograma-svg');
        const lineaMid = document.createElementNS('http://www.w3.org/2000/svg', 'line');
        lineaMid.setAttribute('x1', '450');
        lineaMid.setAttribute('x2', '450');
        lineaMid.setAttribute('y1', '80');
        lineaMid.setAttribute('y2', '440');
        lineaMid.setAttribute('stroke', '#aaa');
        lineaMid.setAttribute('stroke-width', '1');
        lineaMid.setAttribute('stroke-dasharray', '5,5');
        svg.appendChild(lineaMid);
    }
    
    // Inicializar el dibujo del odontograma
    drawOdontograma();
}

// Función para actualizar la lista de dientes seleccionados
function actualizarListaDientes() {
    var dientesSeleccionados = [];
    
    // Recolectar los dientes seleccionados
    document.querySelectorAll('.tooth-shape.tooth-selected').forEach(function(diente) {
        dientesSeleccionados.push(parseInt(diente.getAttribute('data-num')));
    });
    
    // Ordenar numéricamente
    dientesSeleccionados.sort(function(a, b) { return a - b; });
    
    // Actualizar campo oculto con los valores
    $('#dientes_seleccionados').val(dientesSeleccionados.join(','));
    
    // Actualizar lista visual con agrupación por cuadrantes
    var listaHtml = '';
    if (dientesSeleccionados.length === 0) {
        listaHtml = '<span style="color: #777;">Ninguno seleccionado</span>';
    } else {
        // Agrupar dientes por cuadrantes para mejor visualización
        var cuadrante1 = dientesSeleccionados.filter(d => d >= 11 && d <= 18);
        var cuadrante2 = dientesSeleccionados.filter(d => d >= 21 && d <= 28);
        var cuadrante3 = dientesSeleccionados.filter(d => d >= 31 && d <= 38);
        var cuadrante4 = dientesSeleccionados.filter(d => d >= 41 && d <= 48);
        
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
    }
    
    $('#dientes-seleccionados-lista').html(listaHtml);
}

// Ejecutar al cargar
$(document).ready(function() {    
    // Verificar si debemos mostrar el odontograma según la especialidad configurada
    console.log('[ODONTOGRAMA] Verificando si mostrar odontograma - MOSTRAR_ODONTOGRAMA:', window.MOSTRAR_ODONTOGRAMA);
    
    // En nueva_consulta.php siempre queremos mostrar el odontograma independientemente de la configuración
    // por lo que vamos a forzar esta variable a true
    window.MOSTRAR_ODONTOGRAMA = true;

    // Mostrar mensaje de depuración
    console.log('[ODONTOGRAMA] Inicializando carga del odontograma profesional para especialidad: ' + 
                (typeof window.ESPECIALIDAD_NOMBRE !== 'undefined' ? window.ESPECIALIDAD_NOMBRE : 'Desconocida'));
    
    // Función para detectar y cargar el odontograma en campos_dinamicos
    function detectarYCargar() {
        if ($('#campos_dinamicos').length > 0) {
            if ($('#odontograma-dinamico').length === 0) {
                console.log('Contenedor encontrado, insertando odontograma profesional...');
                insertarOdontograma();
                return true;
            } else {
                console.log('Odontograma ya insertado');
                return true;
            }
        }
        return false;
    }
    
    // Primera intento inmediatamente
    if (detectarYCargar()) {
        console.log('Odontograma cargado en el primer intento');
    } else {
        // Segundo intento después de que la página esté completamente cargada
        setTimeout(function() {
            console.log('Segundo intento de inserción del odontograma...');
            if (detectarYCargar()) {
                console.log('Odontograma cargado en el segundo intento');
            } else {
                // Tercer intento después de un tiempo más largo (por si acaso)
                setTimeout(function() {
                    console.log('Tercer intento de inserción del odontograma...');
                    detectarYCargar();
                }, 2000);
            }
        }, 1000);
    }
      // Agregar listener para cambios en especialidad, en caso de que la interfaz permita cambiarla
    $(document).on('change', 'select[name="especialidad"], select#especialidad', function() {
        let especialidadSeleccionada = $(this).val() || '';
        let nombreEspecialidad = $(this).find('option:selected').text().toLowerCase();
        
        // Verificar si la especialidad seleccionada es odontología
        let esOdontologia = nombreEspecialidad.includes('odonto') || 
                           nombreEspecialidad.includes('dental') ||
                           nombreEspecialidad.includes('dentista');
        
        console.log('Cambio de especialidad detectado: ' + nombreEspecialidad + ' (Es odontología: ' + esOdontologia + ')');
        
        // Si es odontología y no existe el odontograma, insertar
        if (esOdontologia && $('#odontograma-dinamico').length === 0) {
            console.log('Especialidad odontológica seleccionada, insertando odontograma...');
            insertarOdontograma();
        } 
        // Si no es odontología y existe el odontograma, ocultar
        else if (!esOdontologia && $('#odontograma-dinamico').length > 0) {
            console.log('Especialidad no odontológica seleccionada, ocultando odontograma...');
            $('#odontograma-dinamico').hide();
        }
        // Si es odontología y existe el odontograma (pero podría estar oculto), mostrar
        else if (esOdontologia && $('#odontograma-dinamico').length > 0) {
            $('#odontograma-dinamico').show();
        }
    });
    
    // Ejecutar inmediatamente una vez
    console.log('[ODONTOGRAMA] Intentando insertar el odontograma inmediatamente...');
    insertarOdontograma();
});

// Exportar la función globalmente para que pueda ser llamada directamente desde nueva_consulta.php
window.insertarOdontograma = insertarOdontograma;
</script>
