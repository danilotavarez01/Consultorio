# Generación Automática de Códigos de Procedimientos

## Descripción
El sistema de procedimientos incluye una funcionalidad de generación automática de códigos únicos basados en la categoría seleccionada.

## Categorías y Prefijos
- **Procedimiento**: PROC (PROC001, PROC002, ...)
- **Utensilio**: UTEN (UTEN001, UTEN002, ...)
- **Material**: MAT (MAT001, MAT002, ...)
- **Medicamento**: MED (MED001, MED002, ...)

## Funcionalidad

### Backend (PHP)
- **Endpoint AJAX**: `?action=get_next_code&categoria={categoria}`
- **Algoritmo**: 
  1. Busca el último código usado para la categoría
  2. Extrae el número del código
  3. Incrementa en 1
  4. Verifica que no exista (prevención de duplicados)
  5. Genera código con formato: {PREFIJO}{NNN}

### Frontend (JavaScript)
- **Generación automática** al cargar la página si el campo está vacío
- **Regeneración automática** al cambiar la categoría (solo si contiene un código auto-generado)
- **Botón de regenerar** para forzar la creación de un nuevo código
- **Indicador visual** durante la generación (spinner)

## Características
- ✅ Códigos únicos garantizados
- ✅ Generación automática según categoría
- ✅ Botón de regeneración manual
- ✅ Fallback en caso de error de conexión
- ✅ Validación de unicidad en el backend
- ✅ Interfaz intuitiva con indicadores visuales

## Uso
1. Seleccionar una categoría en el formulario
2. El código se genera automáticamente
3. Si necesitas un código nuevo, hacer clic en el botón de regenerar (🔄)
4. El código se puede editar manualmente si es necesario

## Archivo modificado
- `procedimientos.php`: Contiene toda la lógica de generación automática

## Pruebas realizadas
- ✅ Generación de códigos para todas las categorías
- ✅ Manejo de errores de conexión
- ✅ Validación de sintaxis PHP
- ✅ Funcionalidad AJAX
- ✅ Interfaz de usuario

---
**Nota**: Los códigos se generan secuencialmente y son únicos dentro de cada categoría.
