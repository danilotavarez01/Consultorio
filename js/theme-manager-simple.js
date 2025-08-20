/* Versión simplificada de theme-manager para debugging */
console.log('Theme-manager simple iniciando...');

// Función para cambiar tema
function toggleTheme() {
    console.log('toggleTheme llamado');
    const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    
    console.log('Cambiando de', currentTheme, 'a', newTheme);
    
    // Aplicar nuevo tema
    document.documentElement.setAttribute('data-theme', newTheme);
    document.body.classList.toggle('dark-theme', newTheme === 'dark');
    
    // Guardar en localStorage
    localStorage.setItem('consultorio-theme', newTheme);
    
    // Actualizar checkbox
    const checkbox = document.getElementById('theme-checkbox');
    if (checkbox) {
        checkbox.checked = newTheme === 'dark';
    }
    
    // Actualizar iconos
    updateThemeIcons(newTheme === 'dark');
    
    console.log('Tema aplicado:', newTheme);
    return newTheme;
}

// Función para actualizar iconos
function updateThemeIcons(isDark) {
    const lightIcon = document.querySelector('.theme-icon-light');
    const darkIcon = document.querySelector('.theme-icon-dark');
    
    if (lightIcon && darkIcon) {
        if (isDark) {
            lightIcon.style.display = 'none';
            darkIcon.style.display = 'inline';
        } else {
            lightIcon.style.display = 'inline';
            darkIcon.style.display = 'none';
        }
    }
}

// Función para inicializar el tema
function initTheme() {
    console.log('Inicializando tema...');
    
    // Cargar tema guardado
    const savedTheme = localStorage.getItem('consultorio-theme');
    const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    const currentTheme = savedTheme || systemTheme;
    
    console.log('Tema inicial:', currentTheme);
    
    // Aplicar tema
    document.documentElement.setAttribute('data-theme', currentTheme);
    document.body.classList.toggle('dark-theme', currentTheme === 'dark');
    
    // Actualizar checkbox
    const checkbox = document.getElementById('theme-checkbox');
    if (checkbox) {
        checkbox.checked = currentTheme === 'dark';
    }
    
    // Actualizar iconos
    updateThemeIcons(currentTheme === 'dark');
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM listo - inicializando tema simple');
    setTimeout(initTheme, 100);
});

console.log('Theme-manager simple cargado');
