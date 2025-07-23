/**
 * Sistema de verificación automática de sesión
 * Este script se debe incluir en todas las páginas del sistema
 */

class SessionManager {
    constructor(options = {}) {
        this.checkInterval = options.checkInterval || 300000; // 5 minutos por defecto
        this.warningTime = options.warningTime || 300; // 5 minutos antes de expirar
        this.sessionTimeout = options.sessionTimeout || 7200; // 2 horas por defecto
        this.lastActivity = Date.now();
        this.isActive = true;
        this.warningShown = false;
        
        this.init();
    }

    init() {
        // Inicializar eventos de actividad del usuario
        this.bindActivityEvents();
        
        // Iniciar verificación periódica
        this.startPeriodicCheck();
        
        // Manejar visibilidad de la página
        this.handlePageVisibility();
        
        console.log('SessionManager inicializado');
    }

    bindActivityEvents() {
        const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];
        
        events.forEach(event => {
            document.addEventListener(event, () => {
                this.updateActivity();
            }, true);
        });
    }

    updateActivity() {
        this.lastActivity = Date.now();
        this.warningShown = false;
        
        // Opcional: Enviar ping al servidor para actualizar sesión
        if (this.isActive) {
            this.pingServer();
        }
    }

    startPeriodicCheck() {
        setInterval(() => {
            this.checkSession();
        }, this.checkInterval);
    }

    checkSession() {
        const now = Date.now();
        const timeSinceActivity = now - this.lastActivity;
        const timeUntilExpiry = this.sessionTimeout * 1000 - timeSinceActivity;

        // Si quedan menos de 5 minutos y no se ha mostrado advertencia
        if (timeUntilExpiry <= this.warningTime * 1000 && !this.warningShown) {
            this.showSessionWarning(Math.floor(timeUntilExpiry / 1000));
            this.warningShown = true;
        }

        // Si la sesión ha expirado
        if (timeUntilExpiry <= 0) {
            this.handleSessionExpiry();
        }
    }

    showSessionWarning(secondsLeft) {
        const minutes = Math.floor(secondsLeft / 60);
        const message = `Tu sesión expirará en ${minutes} minuto(s). ¿Deseas continuar?`;
        
        if (confirm(message)) {
            this.extendSession();
        } else {
            this.logout('user_choice');
        }
    }

    extendSession() {
        this.updateActivity();
        
        // Hacer una petición al servidor para mantener la sesión activa
        fetch('verificar_sesion.php', {
            method: 'GET',
            cache: 'no-cache'
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                console.log('Sesión extendida exitosamente');
                this.showNotification('Sesión extendida', 'success');
            } else {
                console.log('Error al extender sesión');
                this.handleSessionExpiry();
            }
        })
        .catch(error => {
            console.error('Error verificando sesión:', error);
        });
    }

    handleSessionExpiry() {
        this.isActive = false;
        this.showNotification('Sesión expirada. Redirigiendo al login...', 'warning');
        
        setTimeout(() => {
            this.logout('expired');
        }, 2000);
    }

    logout(reason = 'manual') {
        // Limpiar datos locales si es necesario
        localStorage.removeItem('user_data');
        sessionStorage.clear();
        
        // Redirigir al logout
        let logoutUrl = 'logout.php';
        if (reason === 'expired') {
            logoutUrl += '?reason=inactive';
        }
        
        window.location.href = logoutUrl;
    }

    pingServer() {
        // Envío periódico y silencioso para mantener sesión activa
        if (Math.random() < 0.1) { // Solo 10% de las veces para no saturar
            fetch('verificar_sesion.php', {
                method: 'GET',
                cache: 'no-cache'
            }).catch(() => {
                // Ignorar errores en pings silenciosos
            });
        }
    }

    handlePageVisibility() {
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') {
                // La página volvió a ser visible, verificar sesión
                this.checkSessionStatus();
            }
        });
    }

    checkSessionStatus() {
        fetch('verificar_sesion.php', {
            method: 'GET',
            cache: 'no-cache'
        })
        .then(response => response.json())
        .then(data => {
            if (data.status !== 'success') {
                this.handleSessionExpiry();
            }
        })
        .catch(error => {
            console.error('Error verificando estado de sesión:', error);
        });
    }

    showNotification(message, type = 'info') {
        // Crear notificación visual
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} session-notification`;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            animation: slideIn 0.3s ease;
        `;
        notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check' : type === 'warning' ? 'exclamation-triangle' : 'info'}-circle"></i>
            ${message}
        `;

        // Agregar al DOM
        document.body.appendChild(notification);

        // Remover después de 5 segundos
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 5000);
    }

    // Método público para forzar logout desde otros scripts
    forceLogout(reason = 'forced') {
        this.logout(reason);
    }
}

// CSS para animaciones
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    .session-notification {
        transition: all 0.3s ease;
    }
`;
document.head.appendChild(style);

// Inicializar automáticamente cuando se carga el DOM
document.addEventListener('DOMContentLoaded', function() {
    // Solo inicializar si no estamos en la página de login
    if (!window.location.pathname.includes('login.php')) {
        window.sessionManager = new SessionManager();
        
        // Exponer función global para logout manual
        window.logoutUser = function(reason = 'manual') {
            if (window.sessionManager) {
                window.sessionManager.forceLogout(reason);
            } else {
                window.location.href = 'logout.php';
            }
        };
    }
});

// Exportar para uso en módulos si es necesario
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SessionManager;
}
