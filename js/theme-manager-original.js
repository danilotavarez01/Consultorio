/* Sistema de Modo Oscuro - JavaScript */

class ThemeManager {
    constructor() {
        this.init();
    }

    init() {
        // Cargar tema guardado o usar el del sistema
        const savedTheme = localStorage.getItem('consultorio-theme');
        const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        const currentTheme = savedTheme || systemTheme;
        
        this.setTheme(currentTheme);
        this.updateToggleButton(currentTheme === 'dark');
        
        // Escuchar cambios en la preferencia del sistema
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (!localStorage.getItem('consultorio-theme')) {
                this.setTheme(e.matches ? 'dark' : 'light');
                this.updateToggleButton(e.matches);
            }
        });
        
        console.log('Theme Manager inicializado. Tema actual:', currentTheme);
    }

    setTheme(theme) {
        if (theme === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
            document.body.classList.add('dark-theme');
        } else {
            document.documentElement.setAttribute('data-theme', 'light');
            document.body.classList.remove('dark-theme');
        }
        
        // Guardar preferencia
        localStorage.setItem('consultorio-theme', theme);
        
        // Disparar evento personalizado para otros componentes
        window.dispatchEvent(new CustomEvent('themeChanged', { 
            detail: { theme: theme } 
        }));
        
        console.log('Tema cambiado a:', theme);
    }

    toggleTheme() {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        this.setTheme(newTheme);
        this.updateToggleButton(newTheme === 'dark');
        
        // Mostrar notificación
        this.showThemeNotification(newTheme);
        
        return newTheme;
    }

    updateToggleButton(isDark) {
        const toggleButton = document.getElementById('theme-toggle');
        const toggleCheckbox = document.getElementById('theme-checkbox');
        
        if (toggleButton) {
            toggleButton.setAttribute('aria-pressed', isDark);
            toggleButton.title = isDark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro';
        }
        
        if (toggleCheckbox) {
            toggleCheckbox.checked = isDark;
        }
        
        // Actualizar iconos si existen
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

    showThemeNotification(theme) {
        // Crear notificación temporal
        const notification = document.createElement('div');
        notification.className = 'theme-notification';
        notification.innerHTML = `
            <i class="fas fa-${theme === 'dark' ? 'moon' : 'sun'}"></i>
            Modo ${theme === 'dark' ? 'oscuro' : 'claro'} activado
        `;
        
        // Estilos inline para la notificación
        Object.assign(notification.style, {
            position: 'fixed',
            top: '20px',
            right: '20px',
            background: theme === 'dark' ? '#2d2d30' : '#ffffff',
            color: theme === 'dark' ? '#ffffff' : '#000000',
            padding: '12px 20px',
            borderRadius: '8px',
            boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
            zIndex: '9999',
            border: '1px solid ' + (theme === 'dark' ? '#404040' : '#dee2e6'),
            fontSize: '14px',
            fontFamily: 'Arial, sans-serif',
            transition: 'all 0.3s ease',
            opacity: '0',
            transform: 'translateY(-20px)'
        });
        
        document.body.appendChild(notification);
        
        // Animar entrada
        setTimeout(() => {
            notification.style.opacity = '1';
            notification.style.transform = 'translateY(0)';
        }, 10);
        
        // Remover después de 3 segundos
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateY(-20px)';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 3000);
    }

    getCurrentTheme() {
        return document.documentElement.getAttribute('data-theme') || 'light';
    }

    // Método para aplicar tema a elementos específicos del sistema
    applyThemeToElements() {
        const theme = this.getCurrentTheme();
        
        // Aplicar a modales que se crean dinámicamente
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            if (theme === 'dark') {
                modal.classList.add('dark-theme');
            } else {
                modal.classList.remove('dark-theme');
            }
        });
        
        // Aplicar a elementos de DataTables si existen
        if (window.jQuery && window.jQuery.fn.DataTable) {
            setTimeout(() => {
                const tables = window.jQuery('.dataTable');
                tables.each(function() {
                    const wrapper = window.jQuery(this).closest('.dataTables_wrapper');
                    if (theme === 'dark') {
                        wrapper.addClass('dark-theme');
                    } else {
                        wrapper.removeClass('dark-theme');
                    }
                });
            }, 100);
        }
    }
}

// Función global para cambiar tema (compatible con onclick)
function toggleTheme() {
    if (window.themeManager) {
        return window.themeManager.toggleTheme();
    }
}

// Función para aplicar tema a elementos nuevos
function applyThemeToElement(element) {
    if (window.themeManager) {
        const theme = window.themeManager.getCurrentTheme();
        if (theme === 'dark') {
            element.classList.add('dark-theme');
        } else {
            element.classList.remove('dark-theme');
        }
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    window.themeManager = new ThemeManager();
    
    // Aplicar tema a elementos existentes
    setTimeout(() => {
        window.themeManager.applyThemeToElements();
    }, 100);
});

// Escuchar cambios de tema para aplicar a elementos dinámicos
window.addEventListener('themeChanged', function(e) {
    setTimeout(() => {
        window.themeManager.applyThemeToElements();
    }, 50);
});

// Exportar para uso en módulos si es necesario
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { ThemeManager, toggleTheme, applyThemeToElement };
}
