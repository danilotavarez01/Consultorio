# Sistema de Consultorio Médico

Sistema web completo para la gestión de un consultorio médico desarrollado en PHP.

## Características

- **Gestión de Pacientes**: Registro, edición y consulta de información de pacientes
- **Historial Médico**: Mantenimiento completo del historial médico de cada paciente
- **Citas y Turnos**: Sistema de agendamiento y gestión de citas médicas
- **Facturación**: Sistema completo de facturación y control de pagos
- **Especialidades Médicas**: Soporte para múltiples especialidades médicas
- **Usuarios y Permisos**: Sistema de roles y permisos para diferentes tipos de usuarios
- **Procedimientos**: Catálogo de procedimientos médicos con precios
- **Recetas Médicas**: Generación y gestión de recetas médicas

## Estructura de la Base de Datos

El sistema cuenta con 20 tablas principales:
- `pacientes`: Información de los pacientes
- `historial_medico`: Historial médico detallado
- `citas`: Gestión de citas médicas
- `facturas` y `factura_detalles`: Sistema de facturación
- `usuarios` y `permisos`: Control de acceso
- `especialidades` y `especialidad_campos`: Especialidades médicas
- `procedimientos`: Catálogo de procedimientos
- `recetas`: Prescripciones médicas

## Requisitos del Sistema

- PHP 7.4 o superior
- MySQL 5.7 o superior
- Servidor web (Apache/IIS)
- Extensiones PHP: mysqli, gd, mbstring

## Instalación

1. Clona este repositorio
2. Configura la base de datos usando el archivo `Database Schema.sql`
3. Configura los parámetros de conexión en `config.php`
4. Asegúrate de que el servidor web tenga permisos de escritura en las carpetas necesarias

## Configuración

Revisa y ajusta los archivos de configuración:
- `config.php`: Configuración principal
- `configuracion.php`: Configuraciones del consultorio

## Contribuciones

Las contribuciones son bienvenidas. Por favor, crea un pull request con tus cambios.

## Licencia

Este proyecto está bajo licencia [especificar licencia].
