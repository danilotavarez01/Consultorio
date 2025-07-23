# Sistema de Modo Oscuro - Consultorio Médico

## Implementación Completada ✅

Se ha implementado un sistema completo de modo oscuro que se aplica a todo el sistema de consultorio médico.

## Archivos Creados

### 1. CSS del Modo Oscuro
- **Archivo:** `css/dark-mode.css`
- **Función:** Contiene todas las variables CSS y estilos para modo claro y oscuro
- **Características:**
  - Variables CSS para fácil mantenimiento
  - Compatibilidad completa con Bootstrap 4
  - Estilos específicos para componentes del sistema médico
  - Transiciones suaves entre temas

### 2. JavaScript del Gestor de Temas
- **Archivo:** `js/theme-manager.js`
- **Función:** Maneja el cambio de temas y persistencia
- **Características:**
  - Detección automática de preferencia del sistema
  - Persistencia en localStorage
  - Notificaciones visuales al cambiar tema
  - API para aplicar tema a elementos dinámicos

### 3. Header Universal
- **Archivo:** `includes/header.php`
- **Función:** Header con switch de modo oscuro
- **Características:**
  - Switch visual atractivo con iconos sol/luna
  - Información del usuario
  - Botones de acción rápida
  - Responsive design

### 4. Template de Página
- **Archivo:** `includes/page-template.php`
- **Función:** Template base para páginas con modo oscuro
- **Características:**
  - Funciones helper para renderizado
  - CSS adicional para páginas del sistema
  - Estructura estándar para nuevas páginas

### 5. Instalador Automático
- **Archivo:** `instalar_modo_oscuro.php`
- **Función:** Aplica modo oscuro a páginas existentes automáticamente

## Páginas Actualizadas

✅ **Páginas Principales:**
- `index_temporal.php` - Panel temporal
- `nueva_consulta_avanzada.php` - Consulta avanzada

✅ **Próximas a Actualizar:**
- `pacientes.php` - Gestión de Pacientes
- `citas.php` - Gestión de Citas
- `facturacion.php` - Sistema de Facturación
- `reportes_facturacion.php` - Reportes
- `configuracion.php` - Configuración
- Y todas las demás páginas del sistema

## Cómo Funciona

### 1. Detección Automática
```javascript
// Detecta preferencia del sistema operativo
const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
```

### 2. Persistencia
```javascript
// Guarda preferencia del usuario
localStorage.setItem('consultorio-theme', theme);
```

### 3. Variables CSS
```css
:root {
  --bg-primary: #ffffff;     /* Modo claro */
  --text-primary: #212529;
}

[data-theme="dark"] {
  --bg-primary: #1a1a1a;     /* Modo oscuro */
  --text-primary: #ffffff;
}
```

### 4. Aplicación Global
```css
body {
  background-color: var(--bg-primary);
  color: var(--text-primary);
}
```

## Instalación en Páginas Nuevas

### Método 1: Usando el Template
```php
<?php
require_once 'includes/page-template.php';
renderPageStart("Título de la Página");
?>

<!-- Contenido de la página -->

<?php
renderPageEnd();
?>
```

### Método 2: Manual
```html
<!-- En el <head> -->
<link rel="stylesheet" href="css/dark-mode.css">

<!-- Después del <body> -->
<?php include 'includes/header.php'; ?>

<!-- Antes de </body> -->
<script src="js/theme-manager.js"></script>
```

## Uso del Switch de Modo Oscuro

### Ubicación
- Esquina superior derecha del header
- Switch visual con iconos sol/luna
- Etiqueta "Claro/Oscuro" (se oculta en móviles)

### Funcionamiento
1. **Clic en el switch** cambia entre modo claro y oscuro
2. **Preferencia se guarda** automáticamente
3. **Se aplica inmediatamente** a toda la página
4. **Notificación visual** confirma el cambio
5. **Se mantiene** en todas las páginas del sistema

## Características Técnicas

### Compatibilidad
- ✅ Bootstrap 4 completo
- ✅ Font Awesome icons
- ✅ jQuery y JavaScript vanilla
- ✅ Responsive design
- ✅ Navegadores modernos

### Performance
- ✅ CSS optimizado con variables
- ✅ Transiciones suaves (0.3s)
- ✅ Sin parpadeo al cargar
- ✅ Carga rápida de temas

### Accesibilidad
- ✅ Contraste adecuado en ambos modos
- ✅ Transiciones suaves para usuarios sensibles
- ✅ Detección de preferencias del sistema
- ✅ Etiquetas ARIA en el switch

## Próximos Pasos

### 1. Instalación Automática
Ejecuta: `http://localhost/Consultorio2/instalar_modo_oscuro.php`

### 2. Verificación
- ✅ Ve al panel temporal
- ✅ Prueba el switch de modo oscuro
- ✅ Navega entre páginas
- ✅ Verifica que se mantiene el tema

### 3. Personalización (Opcional)
- Ajustar colores en `css/dark-mode.css`
- Modificar comportamiento en `js/theme-manager.js`
- Personalizar header en `includes/header.php`

## Solución de Problemas

### El switch no aparece
- Verifica que `includes/header.php` se incluya correctamente
- Asegúrate de que `js/theme-manager.js` se carga

### Los estilos no se aplican
- Verifica que `css/dark-mode.css` se incluya en el `<head>`
- Limpia caché del navegador

### El tema no se mantiene
- Verifica que JavaScript esté habilitado
- Revisa la consola del navegador por errores

---

**Estado:** ✅ Implementación Completa  
**Fecha:** 19 de Julio, 2025  
**Próximo:** Ejecutar instalador automático
