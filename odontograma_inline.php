<?php
// This file contains the odontogram directly embedded as HTML/SVG instead of loading from an external file
?>
<div id="odontograma-inline" class="mb-4">
    <h5 class="mt-4 mb-2 text-primary">Odontograma</h5>
    <div id="odontograma-contenido" style="max-width: 900px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9;">
        <h2 style="color: #0056b3; text-align: center; margin-bottom: 20px;">Odontograma - Selección de Dientes</h2>
        
        <svg class="odontograma-svg" width="800" height="400" xmlns="http://www.w3.org/2000/svg">
            <!-- Definiciones para gradientes -->
            <defs>
                <linearGradient id="coronaGrad" x1="0%" y1="0%" x2="0%" y2="100%">
                    <stop offset="0%" style="stop-color:#ffffff" />
                    <stop offset="100%" style="stop-color:#e6e6e6" />
                </linearGradient>
                <linearGradient id="coronaGradHover" x1="0%" y1="0%" x2="0%" y2="100%">
                    <stop offset="0%" style="stop-color:#f0f9ff" />
                    <stop offset="100%" style="stop-color:#a5d7ff" />
                </linearGradient>
                <linearGradient id="coronaGradSel" x1="0%" y1="0%" x2="0%" y2="100%">
                    <stop offset="0%" style="stop-color:#ffebeb" />
                    <stop offset="100%" style="stop-color:#ffb8b8" />
                </linearGradient>
                <linearGradient id="rootGrad" x1="0%" y1="0%" x2="0%" y2="100%">
                    <stop offset="0%" style="stop-color:#e6e6e6" />
                    <stop offset="100%" style="stop-color:#cccccc" />
                </linearGradient>
            </defs>

            <!-- Grupo de dientes superiores -->
            <g id="teethUpper" transform="translate(50, 50)">
                <!-- Diente 18 -->
                <g id="tooth-18" class="tooth" data-tooth-id="18">
                    <path class="tooth-root" d="M30,60 L20,120 L40,120 Z" fill="url(#rootGrad)" />
                    <path class="tooth-shape" d="M10,30 Q10,10 30,10 Q50,10 50,30 Q50,50 30,60 Q10,50 10,30 Z" />
                    <text x="30" y="35" text-anchor="middle" fill="#333" font-size="12">18</text>
                </g>
                
                <!-- Diente 17 -->
                <g id="tooth-17" class="tooth" data-tooth-id="17" transform="translate(45, 0)">
                    <path class="tooth-root" d="M30,60 L20,120 L40,120 Z" fill="url(#rootGrad)" />
                    <path class="tooth-shape" d="M10,30 Q10,10 30,10 Q50,10 50,30 Q50,50 30,60 Q10,50 10,30 Z" />
                    <text x="30" y="35" text-anchor="middle" fill="#333" font-size="12">17</text>
                </g>
                
                <!-- Ejemplo de más dientes (agregue los restantes) -->
                <!-- Diente 16 -->
                <g id="tooth-16" class="tooth" data-tooth-id="16" transform="translate(90, 0)">
                    <path class="tooth-root" d="M30,60 L20,120 L40,120 Z" fill="url(#rootGrad)" />
                    <path class="tooth-shape" d="M10,30 Q10,10 30,10 Q50,10 50,30 Q50,50 30,60 Q10,50 10,30 Z" />
                    <text x="30" y="35" text-anchor="middle" fill="#333" font-size="12">16</text>
                </g>
                
                <!-- Diente 15 -->
                <g id="tooth-15" class="tooth" data-tooth-id="15" transform="translate(135, 0)">
                    <path class="tooth-root" d="M30,60 L20,120 L40,120 Z" fill="url(#rootGrad)" />
                    <path class="tooth-shape" d="M10,30 Q10,10 30,10 Q50,10 50,30 Q50,50 30,60 Q10,50 10,30 Z" />
                    <text x="30" y="35" text-anchor="middle" fill="#333" font-size="12">15</text>
                </g>

                <!-- Más dientes superiores... -->
                
            </g>

            <!-- Grupo de dientes inferiores -->
            <g id="teethLower" transform="translate(50, 230)">
                <!-- Diente 48 -->
                <g id="tooth-48" class="tooth" data-tooth-id="48">
                    <path class="tooth-root" d="M30,10 L20,-50 L40,-50 Z" fill="url(#rootGrad)" />
                    <path class="tooth-shape" d="M10,40 Q10,60 30,60 Q50,60 50,40 Q50,20 30,10 Q10,20 10,40 Z" />
                    <text x="30" y="40" text-anchor="middle" fill="#333" font-size="12">48</text>
                </g>
                
                <!-- Diente 47 -->
                <g id="tooth-47" class="tooth" data-tooth-id="47" transform="translate(45, 0)">
                    <path class="tooth-root" d="M30,10 L20,-50 L40,-50 Z" fill="url(#rootGrad)" />
                    <path class="tooth-shape" d="M10,40 Q10,60 30,60 Q50,60 50,40 Q50,20 30,10 Q10,20 10,40 Z" />
                    <text x="30" y="40" text-anchor="middle" fill="#333" font-size="12">47</text>
                </g>
                
                <!-- Más dientes inferiores... -->
            </g>
        </svg>

        <!-- Lista para mostrar los dientes seleccionados -->
        <div class="mt-3" style="padding: 15px; background-color: white; border-radius: 8px; box-shadow: 0 1px 3px #0001;">
            <h4 style="color: #444; margin-bottom: 10px; font-size: 16px;">Dientes seleccionados:</h4>
            <ul id="selected-teeth-list" style="padding-left: 20px;"></ul>
            <input type="hidden" id="dientes-seleccionados" name="dientes_seleccionados" value="">
        </div>
    </div>
</div>

<script>
// Script para el funcionamiento del odontograma
document.addEventListener('DOMContentLoaded', function() {
    let selectedTeeth = [];
    
    // Obtener todos los dientes del SVG
    const teeth = document.querySelectorAll('.tooth');
    
    // Añadir evento de clic a cada diente
    teeth.forEach(tooth => {
        tooth.addEventListener('click', function() {
            const toothId = this.getAttribute('data-tooth-id');
            const toothShape = this.querySelector('.tooth-shape');
            
            // Toggle selección
            if (toothShape.classList.contains('tooth-selected')) {
                toothShape.classList.remove('tooth-selected');
                selectedTeeth = selectedTeeth.filter(id => id !== toothId);
            } else {
                toothShape.classList.add('tooth-selected');
                selectedTeeth.push(toothId);
            }
            
            // Actualizar lista y campo oculto
            updateSelectedTeethList();
        });
    });
    
    // Función para actualizar la lista de dientes seleccionados
    function updateSelectedTeethList() {
        const selectedTeethList = document.getElementById('selected-teeth-list');
        const hiddenInput = document.getElementById('dientes-seleccionados');
        
        // Ordenar los dientes seleccionados numéricamente
        selectedTeeth.sort((a, b) => parseInt(a) - parseInt(b));
        
        // Actualizar el campo oculto
        hiddenInput.value = selectedTeeth.join(',');
        
        // Actualizar la lista visual
        selectedTeethList.innerHTML = '';
        if (selectedTeeth.length === 0) {
            selectedTeethList.innerHTML = '<li style="color: #777;">Ninguno seleccionado</li>';
        } else {
            selectedTeeth.forEach(toothId => {
                const li = document.createElement('li');
                li.textContent = `Diente ${toothId}`;
                selectedTeethList.appendChild(li);
            });
        }
    }
    
    // Inicializar la lista
    updateSelectedTeethList();
});
</script>
