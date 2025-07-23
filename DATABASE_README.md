# Estructura de Base de Datos - Sistema de Consultorio Médico

## Archivos de Base de Datos

### 1. `database_structure.sql`
**Archivo principal con la estructura completa de la base de datos**
- Compatible con phpMyAdmin
- Incluye todas las tablas necesarias
- Contiene claves foráneas y índices
- Incluye datos básicos (especialidades y permisos)

### 2. `initial_data.sql`
**Datos iniciales y de ejemplo**
- Usuario administrador por defecto
- Enfermedades comunes
- Campos personalizados para especialidades
- Ejecutar DESPUÉS de `database_structure.sql`

## Instrucciones de Instalación

### Opción 1: Con phpMyAdmin
1. Acceder a phpMyAdmin
2. Crear una nueva base de datos llamada `consultorio` (o usar la existente)
3. Importar `database_structure.sql`
4. Importar `initial_data.sql`

### Opción 2: Por línea de comandos
```bash
mysql -u root -p < database_structure.sql
mysql -u root -p consultorio < initial_data.sql
```

### Opción 3: Desde PHP
```php
// Ejecutar los archivos SQL desde PHP
$sql_structure = file_get_contents('database_structure.sql');
$sql_data = file_get_contents('initial_data.sql');

// Ejecutar estructura
$conn->exec($sql_structure);

// Ejecutar datos iniciales
$conn->exec($sql_data);
```

## Usuario Administrador por Defecto

**Después de importar los archivos:**
- **Usuario:** `admin`
- **Contraseña:** `password` (cambiar inmediatamente)
- **Email:** `admin@consultorio.com`

## Estructura de Tablas

### Tablas Principales
- **usuarios** - Usuarios del sistema (médicos, recepcionistas, etc.)
- **pacientes** - Información de pacientes
- **historial_medico** - Consultas y historiales médicos
- **especialidades** - Especialidades médicas
- **citas** - Sistema de citas médicas

### Tablas de Configuración
- **configuracion** - Configuración general del consultorio
- **especialidad_campos** - Campos personalizados por especialidad
- **consulta_campos_valores** - Valores de campos personalizados
- **permisos** y **usuario_permisos** - Sistema de permisos

### Tablas de Catálogos
- **enfermedades** - Catálogo de enfermedades
- **paciente_enfermedades** - Relación paciente-enfermedad
- **whatsapp_config** - Configuración de WhatsApp

## Características Importantes

### 1. Campos Dinámicos por Especialidad
- Cada especialidad puede tener campos personalizados
- Los valores se almacenan en formato JSON
- Soporte para diferentes tipos de campos (texto, número, fecha, etc.)

### 2. Sistema de Permisos
- Permisos granulares por funcionalidad
- Asignación flexible de permisos por usuario
- Roles predefinidos (admin, médico, enfermero, recepcionista)

### 3. Odontograma
- Soporte específico para odontología
- Almacenamiento de dientes seleccionados
- Campos especializados para tratamientos dentales

### 4. Seguridad
- Contraseñas hasheadas con bcrypt
- Claves foráneas para integridad referencial
- Campos de auditoría (created_at, updated_at)

## Configuración del Proyecto

### Archivo `config.php`
Asegúrate de configurar correctamente:
```php
define('DB_SERVER', 'localhost');    // Servidor de base de datos
define('DB_PORT', 3306);             // Puerto MySQL
define('DB_NAME', 'consultorio');    // Nombre de la base de datos
define('DB_USER', 'root');           // Usuario MySQL
define('DB_PASS', 'tu_password');    // Contraseña MySQL
```

## Notas Importantes

1. **Charset:** Todas las tablas usan `utf8mb4` para soporte completo de caracteres Unicode
2. **Engine:** Se usa InnoDB para soporte de transacciones y claves foráneas
3. **JSON:** Los campos dinámicos se almacenan en formato JSON (MySQL 5.7+)
4. **Fotos:** Las fotos de pacientes se almacenan como archivos en `uploads/pacientes/`
5. **Backup:** Recomendable hacer backup regular de la base de datos

## Solución de Problemas

### Error de conexión
- Verificar que MySQL esté ejecutándose
- Comprobar usuario y contraseña en `config.php`
- Verificar que la base de datos existe

### Error de permisos
- Verificar que el usuario MySQL tenga permisos suficientes
- Verificar permisos de archivos en el servidor web

### Error de charset
- Verificar que MySQL soporte utf8mb4
- Verificar configuración de charset en `config.php`

## Mantenimiento

### Backup periódico
```bash
mysqldump -u root -p consultorio > backup_consultorio_$(date +%Y%m%d).sql
```

### Optimización de tablas
```sql
OPTIMIZE TABLE historial_medico, pacientes, usuarios;
```
