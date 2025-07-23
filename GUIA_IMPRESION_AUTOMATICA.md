# 🖨️ Guía de Configuración para Impresión Automática

## Problema Solucionado
El recibo se genera correctamente pero no se envía automáticamente a la impresora.

## Soluciones Implementadas

### 1. 🔧 Auto-impresión Mejorada
- **Auto-impresión automática** cuando se abre el recibo
- **Botón de impresión manual** como respaldo
- **Feedback visual** del estado de impresión
- **Reintentos automáticos** si falla la primera vez

### 2. 📄 Formato Optimizado para Impresora Térmica 80mm
- **CSS específico** para impresión térmica
- **Tamaño de página** configurado para 80mm
- **Fuentes y espaciado** optimizados
- **Sin márgenes** para aprovechamiento máximo del papel

### 3. 🛠️ Herramientas de Diagnóstico
- **Test de impresión automática** (`test_impresion_automatica.php`)
- **Botón de test** integrado en facturación
- **Logs detallados** en consola del navegador

## Configuración Requerida

### En Windows:
1. **Configurar impresora como predeterminada**
   - Panel de Control > Dispositivos e impresoras
   - Clic derecho en la impresora térmica
   - Seleccionar "Establecer como impresora predeterminada"

2. **Verificar drivers de impresora**
   - Asegurarse de que los drivers estén instalados correctamente
   - Para impresoras térmicas, usar drivers genéricos "Generic/Text Only" si no hay drivers específicos

### En el Navegador:
1. **Permitir ventanas emergentes**
   - Agregar el sitio a la lista blanca de ventanas emergentes
   - Chrome: Configuración > Privacidad y seguridad > Configuración del sitio > Ventanas emergentes

2. **Permitir impresión automática**
   - Algunos navegadores requieren permitir específicamente la impresión automática

## Cómo Funciona

### Flujo Normal:
1. Usuario registra un pago en facturación
2. Se muestra el modal "¿Desea imprimir el recibo?"
3. Usuario hace clic en "Sí, Imprimir Recibo"
4. Se abre ventana con el recibo
5. **AUTOMÁTICAMENTE** se ejecuta `window.print()`
6. El diálogo de impresión aparece
7. Usuario confirma la impresión

### Flujo de Respaldo:
Si la impresión automática falla:
1. El recibo muestra el mensaje "Use el botón manual"
2. Usuario hace clic en "Imprimir Manualmente"
3. Se ejecuta `window.print()` nuevamente

## Archivos Modificados

### ✅ `imprimir_recibo.php`
- Auto-impresión al cargar la página
- CSS optimizado para impresora térmica 80mm
- Botones mejorados con feedback
- Manejo de errores robusto

### ✅ `facturacion.php`
- Función `imprimirRecibo()` mejorada
- Botón de test integrado
- Mejor manejo de errores de ventanas

### ✅ Archivos Nuevos
- `test_impresion_automatica.php` - Herramienta de test
- `config_impresion_termica.php` - Configuraciones específicas

## Troubleshooting

### Si NO imprime automáticamente:
1. **Verificar consola del navegador** (F12) para ver errores
2. **Usar el botón "Imprimir Manualmente"**
3. **Verificar que la impresora esté encendida y conectada**
4. **Usar la herramienta de test** (`test_impresion_automatica.php`)

### Si la ventana no se abre:
1. **Permitir ventanas emergentes** en el navegador
2. **Usar el botón "🔧 Test"** en facturación para diagnosticar

### Si el formato no es correcto:
1. **Verificar configuración de impresora** (tamaño de papel, orientación)
2. **Para impresoras térmicas**: usar papel de 80mm
3. **Configurar márgenes mínimos** en las propiedades de la impresora

## Test de Funcionamiento

### Método 1: Test Integrado
1. Ir a Facturación
2. Clic en "🖨️ Test Impresión"
3. Clic en "PROBAR IMPRESIÓN AUTOMÁTICA"
4. Verificar que se abre el recibo y aparece el diálogo de impresión

### Método 2: Test Manual
1. Ir a Facturación
2. Registrar un pago real
3. En el modal, clic en "Sí, Imprimir Recibo"
4. Verificar impresión automática

## Notas Técnicas

- **Auto-impresión se ejecuta** 500ms después de cargar la página
- **Reintentos automáticos** si falla el primer intento
- **Compatible con impresoras térmicas** de 80mm estándar
- **Funciona con impresoras láser/inkjet** normales también
- **Optimizado para navegadores modernos** (Chrome, Firefox, Edge)

## Soporte

Si persisten problemas:
1. Revisar logs en consola del navegador
2. Verificar configuración de Windows
3. Probar con una impresora diferente
4. Contactar soporte técnico con detalles específicos del error
