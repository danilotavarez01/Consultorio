# 🖼️ PROBLEMA FOTOS PACIENTES - DIAGNÓSTICO Y SOLUCIÓN

## 🚨 Problema Identificado
- **Síntoma:** Las fotos de los pacientes no se muestran en la página de detalles
- **Ubicación:** `ver_paciente.php` y posiblemente `pacientes.php`

## 🔍 Diagnóstico Realizado

### Archivos Afectados:
1. **ver_paciente.php** - Página de detalles del paciente
2. **pacientes.php** - Lista de pacientes  
3. **uploads/pacientes/** - Directorio de fotos

### Verificaciones:
- ✅ Directorio `uploads/pacientes/` existe
- ✅ Contiene archivos de fotos (varios .jpg y .png)
- ❓ Rutas web para acceso desde navegador

## 🔧 Soluciones Implementadas

### 1. Mejora en `ver_paciente.php`:
```php
// ANTES: Ruta problemática
$rutaFotoWeb = '/uploads/pacientes/' . htmlspecialchars($paciente['foto']);

// AHORA: Verificación robusta
$rutaFotoLocal = 'uploads/pacientes/' . $paciente['foto'];
$rutaFotoCompleta = __DIR__ . '/' . $rutaFotoLocal;

if (file_exists($rutaFotoCompleta)) {
    // Mostrar foto con manejo de errores
} else {
    // Mostrar mensaje de archivo no encontrado
}
```

### 2. Mejora en `pacientes.php`:
```php
// ANTES: Sin verificación
echo "<img src='uploads/pacientes/" . htmlspecialchars($row['foto']) . "'>";

// AHORA: Con verificación y fallback
$rutaFoto = 'uploads/pacientes/' . htmlspecialchars($row['foto']);
if (file_exists($rutaFoto)) {
    echo "<img src='$rutaFoto' class='foto-paciente-lista' alt='Foto'>";
} else {
    echo "<img src='placeholder' alt='Archivo no encontrado'>";
}
```

## 🧪 Scripts de Debug Creados:
1. **debug_fotos.php** - Análisis completo de rutas y archivos
2. **test_fotos.php** - Prueba visual de diferentes rutas

## 📋 Posibles Causas del Problema:

### A. Rutas Incorrectas:
- Usar `/uploads/` vs `uploads/` (absoluta vs relativa)
- Problemas con `$_SERVER['DOCUMENT_ROOT']` en IIS
- Diferencias entre rutas físicas y rutas web

### B. Permisos de Archivos:
- IIS podría no tener permisos para servir archivos desde `uploads/`
- Configuración de directorio virtual faltante

### C. Configuración del Servidor:
- Tipos MIME no configurados para imágenes
- Restricciones de seguridad en IIS

## ✅ Verificaciones Realizadas:
- [x] Directorio existe físicamente
- [x] Archivos de fotos existen
- [x] Código PHP mejorado
- [ ] Verificar acceso web directo a fotos
- [ ] Probar rutas en navegador
- [ ] Verificar configuración IIS

## 🔄 Próximos Pasos:
1. Ejecutar scripts de debug
2. Probar acceso directo a fotos vía URL
3. Verificar configuración de IIS si es necesario
4. Ajustar rutas según resultados de las pruebas

---
**Estado:** EN PROGRESO 🔄
**Fecha:** 23 de julio de 2025
