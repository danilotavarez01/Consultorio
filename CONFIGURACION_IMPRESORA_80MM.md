# 🖨️ GUÍA DE CONFIGURACIÓN - IMPRESORA TÉRMICA 80MM

## 📋 PASOS PARA CONFIGURAR LA IMPRESORA

### 1. **Conexión Física**
- Conectar la impresora térmica al puerto USB
- Encender la impresora
- Verificar que Windows reconozca el dispositivo

### 2. **Instalación de Drivers**
- Descargar drivers específicos del fabricante
- Instalar como "Impresora de Texto Genérica" si no hay drivers específicos
- Configurar como impresora predeterminada (opcional)

### 3. **Configuración en Windows**
```
Panel de Control → Dispositivos e Impresoras → Agregar Impresora
- Tipo: Local
- Puerto: USB001 (o el asignado)
- Driver: Generic/Text Only
- Nombre: Impresora Térmica 80mm
```

### 4. **Configuración de Papel**
- **Tamaño**: Personalizado 80mm x Continuo
- **Orientación**: Vertical
- **Márgenes**: 2mm (todos los lados)
- **Calidad**: Borrador/Rápida

### 5. **Configuración del Navegador**
```
Chrome/Edge → Configuración → Impresión:
- Tamaño: Personalizado (80mm x auto)
- Márgenes: Mínimos
- Opciones: Sin encabezados/pies de página
- Escala: 100%
```

## 🔧 CONFIGURACIÓN EN EL SISTEMA

### En `config.php` (opcional):
```php
// Configuración de impresora térmica
$impresora_config = [
    'nombre' => 'Impresora Térmica 80mm',
    'ancho_papel' => 80, // mm
    'caracteres_linea' => 32,
    'auto_corte' => true
];
```

### En la base de datos `configuracion`:
```sql
UPDATE configuracion SET
nombre_consultorio = 'TU CONSULTORIO AQUÍ',
direccion = 'Dirección completa',
telefono = 'Tu teléfono',
ruc = 'Tu RUC',
email = 'email@consultorio.com';
```

## 🧪 TESTING DE LA IMPRESORA

### 1. **Prueba Básica de Windows**
- Panel de Control → Impresoras
- Click derecho en la impresora térmica
- "Imprimir página de prueba"

### 2. **Prueba en el Sistema**
1. Ir a `facturacion.php`
2. Click en "Mostrar Modal de Prueba"
3. Click en "Sí, Imprimir Recibo"
4. En la ventana que se abre, click "🖨️ Imprimir Ahora"
5. Verificar que sale el recibo

### 3. **Prueba con Pago Real**
1. Crear una factura de prueba
2. Agregar un pago
3. El modal debería aparecer automáticamente
4. Imprimir y verificar formato

## ⚠️ SOLUCIÓN DE PROBLEMAS

### La impresora no imprime:
- ✅ Verificar conexión USB
- ✅ Verificar que esté encendida
- ✅ Verificar papel (no debe estar acabado)
- ✅ Reiniciar spooler de impresión
- ✅ Verificar que sea la impresora predeterminada

### El formato se ve mal:
- ✅ Verificar configuración de tamaño de papel
- ✅ Ajustar márgenes a mínimo
- ✅ Verificar que la fuente sea monoespaciada
- ✅ Verificar escalado al 100%

### Texto cortado:
- ✅ Reducir tamaño de fuente en el CSS
- ✅ Verificar ancho de papel en configuración
- ✅ Ajustar variable ANCHO_PAPEL en el código

### No aparece el modal:
- ✅ Verificar consola del navegador (F12)
- ✅ Verificar que JavaScript esté habilitado
- ✅ Probar con "Simular Pago Real"
- ✅ Verificar variables de sesión en diagnóstico

## 📞 MARCAS DE IMPRESORAS COMPATIBLES

### Probadas y funcionando:
- ✅ **Epson TM-T20II/TM-T20III**
- ✅ **Bixolon SRP-350**
- ✅ **Star TSP143**
- ✅ **Citizen CT-S310**

### Configuración recomendada:
- **Velocidad**: 150mm/s
- **Densidad**: Media
- **Auto-corte**: Habilitado
- **Buffer**: 4KB o más

## 🔗 ARCHIVOS DEL SISTEMA

### Archivos principales:
- `imprimir_recibo_termico.php` - Ventana de impresión
- `facturacion.php` - Modal y funciones
- `config_impresion_termica.php` - Configuraciones
- `crear_pago_prueba.php` - API de pruebas

### CSS personalizable:
```css
/* En imprimir_recibo_termico.php línea ~45 */
font-size: 9pt;  /* Cambiar si es muy grande/pequeño */
max-width: 300px; /* Ajustar según impresora */
```

### JavaScript personalizable:
```javascript
/* Tiempo de auto-cierre después de imprimir */
setTimeout(function() {
    if (confirm('¿Desea cerrar esta ventana?')) {
        window.close();
    }
}, 1000); // Cambiar 1000 por los ms deseados
```

## 📧 SOPORTE

Si tienes problemas:
1. Verificar la configuración paso a paso
2. Probar con otra impresora si está disponible
3. Revisar logs del navegador (F12 → Console)
4. Verificar que todos los archivos estén en su lugar

---
**Fecha**: <?= date('Y-m-d H:i:s') ?>
**Sistema**: Consultorio Odontológico - Impresión Térmica
**Versión**: 1.0
