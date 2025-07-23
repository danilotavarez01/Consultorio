# 🏥 Sistema de Consultas Médicas con Campos Dinámicos

## 📋 Descripción General

El nuevo sistema de consultas médicas permite crear formularios personalizados según la especialidad médica seleccionada. Cada especialidad puede tener sus propios campos específicos que se cargan dinámicamente en el formulario.

## ✨ Características Principales

### 🎯 **Selección de Especialidad/Perfil**
- **Interfaz visual de tarjetas** para seleccionar la especialidad
- **Carga dinámica de campos** específicos según la especialidad elegida
- **Especialidad por defecto** configurada desde la administración

### 🔧 **Campos Dinámicos Configurables**
- **Tipos de campo soportados:**
  - Texto simple
  - Números
  - Fechas
  - Áreas de texto (textarea)
  - Listas desplegables (select)
  - Casillas de verificación (checkbox)

### 💾 **Almacenamiento Dual**
- **JSON en historial_medico**: Para compatibilidad y consultas rápidas
- **Tabla especializada**: `consulta_campos_valores` para consultas avanzadas

## 🏗️ Arquitectura del Sistema

### 📊 **Base de Datos**

```sql
-- Tabla de especialidades médicas
especialidades (
    id, codigo, nombre, descripcion, estado
)

-- Campos específicos por especialidad
especialidad_campos (
    id, especialidad_id, nombre_campo, etiqueta, 
    tipo_campo, opciones, requerido, orden
)

-- Valores de campos por consulta
consulta_campos_valores (
    id, consulta_id, campo_id, valor
)
```

### 🔌 **Endpoints**

#### `get_campos_especialidad_por_id.php`
- **Función**: Obtiene campos específicos por ID de especialidad
- **Parámetros**: `especialidad_id` (GET)
- **Respuesta**: JSON con campos formateados para el frontend

#### `nueva_consulta_avanzada.php`
- **Función**: Formulario principal con selector de especialidades
- **Características**: 
  - Selector visual de especialidades
  - Carga dinámica de campos
  - Validación automática
  - Interfaz responsive

## 🎨 **Especialidades Configuradas**

### 🩺 **Medicina General (MG)**
- Temperatura (°C)
- Presión Arterial
- Síntomas Generales
- Tipo de Consulta

### 👶 **Pediatría (PED)**
- Peso (kg)
- Talla (cm)
- Perímetro Cefálico
- Desarrollo
- Vacunas al día
- Tipo de Alimentación

### 👩‍⚕️ **Ginecología (GIN)**
- Fecha Última Regla (FUR)
- Número de Embarazos (G)
- Número de Partos (P)
- Número de Abortos (A)
- Número de Cesáreas (C)
- Método Anticonceptivo
- Fecha último Papanicolau
- Fecha última Mamografía

### ❤️ **Cardiología (CAR)**
- Presión Sistólica/Diastólica
- Frecuencia Cardíaca
- Dolor Torácico
- Dificultad para Respirar
- Edema en Extremidades
- Antecedentes Cardíacos

### 🌟 **Dermatología (DER)**
- Tipo de Lesión
- Localización de la Lesión
- Tiempo de Evolución
- Presencia de Picazón
- Dolor en la Lesión
- Antecedentes Alérgicos
- Exposición Solar

### 👁️ **Oftalmología (OFT)**
- Agudeza Visual OD/OI
- Presión Intraocular OD/OI
- Dolor Ocular
- Visión Borrosa
- Fotofobia
- Uso de Lentes

## 🚀 **Uso del Sistema**

### Para Médicos:
1. **Acceder** a nueva consulta avanzada
2. **Seleccionar** la especialidad/perfil apropiado
3. **Completar** los campos específicos que aparecen
4. **Guardar** la consulta normalmente

### Para Administradores:
1. **Configurar** especialidades en el sistema
2. **Definir** campos personalizados por especialidad
3. **Establecer** especialidad por defecto
4. **Gestionar** tipos de campo y validaciones

## 📁 **Archivos Principales**

### 🔧 **Configuración**
- `configurar_especialidades_completas.php` - Configuración inicial
- `get_campos_especialidad_por_id.php` - Endpoint de campos
- `nueva_consulta_avanzada.php` - Formulario principal

### 🗄️ **Base de Datos**
- Tablas: `especialidades`, `especialidad_campos`, `consulta_campos_valores`
- Relaciones con `historial_medico` y `configuracion`

## 🎯 **Beneficios del Sistema**

### ✅ **Para los Médicos**
- **Formularios personalizados** según su especialidad
- **Campos relevantes** para cada tipo de consulta
- **Interfaz intuitiva** y fácil de usar
- **Datos organizados** por especialidad

### ✅ **Para la Administración**
- **Flexibilidad total** en la configuración
- **Escalabilidad** para nuevas especialidades
- **Mantenimiento sencillo** de campos
- **Reportes especializados** por área médica

### ✅ **Para el Sistema**
- **Arquitectura modular** y extensible
- **Compatibilidad** con sistema existente
- **Performance optimizada** con carga dinámica
- **Base de datos normalizada**

## 🔧 **Configuración y Mantenimiento**

### Agregar Nueva Especialidad:
1. Ejecutar `configurar_especialidades_completas.php`
2. Modificar el array `$especialidades` con la nueva especialidad
3. Definir los campos específicos necesarios
4. Guardar y ejecutar la configuración

### Modificar Campos Existentes:
1. Acceder a la tabla `especialidad_campos`
2. Modificar etiquetas, tipos, opciones según necesidad
3. Los cambios se reflejan inmediatamente en el formulario

### Configurar Especialidad por Defecto:
1. Acceder a configuración del sistema
2. Seleccionar la especialidad deseada
3. Los nuevos usuarios verán esta especialidad preseleccionada

---

## 🏆 **Resultado Final**

El sistema ahora proporciona:
- ✅ **Formularios dinámicos** basados en especialidad
- ✅ **Interfaz moderna** con selector visual
- ✅ **Campos específicos** para cada área médica
- ✅ **Almacenamiento optimizado** de datos
- ✅ **Escalabilidad** para futuras especialidades
- ✅ **Experiencia de usuario mejorada**

Este sistema transforma una consulta médica estática en una experiencia personalizada y específica para cada especialidad médica, mejorando la calidad de los datos capturados y la eficiencia del proceso de consulta.
