# Módulo de Procedimientos Odontológicos

## Descripción
El módulo de procedimientos permite gestionar un catálogo completo de procedimientos, materiales y utensilios para el consultorio odontológico.

## Características Principales

### 1. Gestión de Procedimientos
- **Crear** nuevos procedimientos, materiales y utensilios
- **Editar** información existente
- **Activar/Desactivar** elementos del catálogo
- **Eliminar** procedimientos no utilizados

### 2. Categorización
- **Procedimientos**: Tratamientos y servicios médicos
- **Utensilios**: Herramientas y equipos reutilizables
- **Materiales**: Insumos consumibles

### 3. Control de Precios
- Precio de costo (opcional)
- Precio de venta (obligatorio)
- Cálculo automático de márgenes

### 4. Filtros y Búsqueda
- Filtrar por categoría (procedimiento, utensilio, material)
- Filtrar por estado (activo/inactivo)
- Búsqueda por nombre o descripción

## Estructura de la Base de Datos

### Tabla `procedimientos`
```sql
CREATE TABLE `procedimientos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text,
  `precio_costo` decimal(10,2) DEFAULT '0.00',
  `precio_venta` decimal(10,2) NOT NULL DEFAULT '0.00',
  `activo` tinyint(1) DEFAULT '1',
  `categoria` enum('procedimiento','utensilio','material') DEFAULT 'procedimiento',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_activo` (`activo`),
  KEY `idx_categoria` (`categoria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

## Permisos Requeridos

### `gestionar_catalogos`
- Permiso principal para acceder al módulo
- Permite crear, editar, activar/desactivar y eliminar procedimientos
- Por defecto asignado al usuario administrador

## Archivos del Módulo

### `procedimientos.php`
- Archivo principal del módulo
- Contiene toda la lógica de CRUD
- Interfaz de usuario completa con Bootstrap 5

### `setup_procedimientos.php`
- Script de configuración inicial
- Crea el permiso `gestionar_catalogos`
- Asigna el permiso al usuario administrador

### `verificar_procedimientos.php`
- Script de verificación y diagnóstico
- Crea la tabla si no existe
- Inserta datos de ejemplo
- Muestra estructura y contenido actual

## Instalación y Configuración

### 1. Ejecutar Scripts de Configuración
```bash
# 1. Verificar y crear tabla
http://localhost/Consultorio2/verificar_procedimientos.php

# 2. Configurar permisos
http://localhost/Consultorio2/setup_procedimientos.php
```

### 2. Importar Estructura (Alternativo)
Si prefieres usar phpMyAdmin:
```sql
-- Importar database_structure.sql
-- Importar initial_data.sql
```

### 3. Verificar Acceso
- Iniciar sesión como administrador
- El menú "Procedimientos" debe aparecer en el sidebar
- Acceder a la gestión de procedimientos

## Datos de Ejemplo
El sistema incluye 10 procedimientos de ejemplo:

| Nombre | Categoría | Precio |
|--------|-----------|--------|
| Limpieza Dental | Procedimiento | $50.00 |
| Obturación Simple | Procedimiento | $75.00 |
| Extracción Simple | Procedimiento | $100.00 |
| Tratamiento de Conducto | Procedimiento | $250.00 |
| Corona Dental | Procedimiento | $500.00 |
| Implante Dental | Procedimiento | $800.00 |
| Brackets Metálicos | Utensilio | $600.00 |
| Resina Composite | Material | $20.00 |
| Anestesia Local | Material | $10.00 |
| Gasas Estériles | Utensilio | $5.00 |

## Integración Futura

### Facturación
- Vincular procedimientos con consultas
- Generar presupuestos automáticos
- Control de inventario para materiales

### Reportes
- Procedimientos más utilizados
- Análisis de rentabilidad
- Control de costos

### Historial Médico
- Registro de procedimientos realizados
- Seguimiento de tratamientos
- Alertas de mantenimiento

## Solución de Problemas

### Error: "sin_permisos"
- Verificar que el usuario tenga el permiso `gestionar_catalogos`
- Ejecutar `setup_procedimientos.php`

### Tabla no existe
- Ejecutar `verificar_procedimientos.php`
- Importar `database_structure.sql`

### No aparece en el menú
- Verificar permisos del usuario
- Revisar la función `hasPermission()` en `sidebar.php`

## Seguridad

### Validaciones
- Nombre obligatorio
- Precio de venta mayor a 0
- Sanitización de entradas HTML
- Protección contra inyección SQL

### Permisos
- Control de acceso por roles
- Verificación en cada operación
- Sesiones seguras

## Personalización

### Agregar Nuevas Categorías
Modificar el enum en la tabla:
```sql
ALTER TABLE procedimientos 
MODIFY categoria ENUM('procedimiento','utensilio','material','servicio','consulta');
```

### Campos Adicionales
```sql
ALTER TABLE procedimientos 
ADD COLUMN codigo VARCHAR(50),
ADD COLUMN proveedor VARCHAR(255),
ADD COLUMN stock_minimo INT DEFAULT 0;
```
