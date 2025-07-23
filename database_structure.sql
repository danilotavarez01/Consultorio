-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 17-07-2025 a las 10:30:00
-- Versión del servidor: 8.0.30
-- Versión de PHP: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de datos: `consultorio`
--
CREATE DATABASE IF NOT EXISTS `consultorio` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `consultorio`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `apellido` varchar(100) DEFAULT NULL,
  `rol` enum('admin','medico','enfermero','recepcionista') DEFAULT 'medico',
  `especialidad_id` int DEFAULT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `fk_usuarios_especialidad` (`especialidad_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `especialidades`
--

DROP TABLE IF EXISTS `especialidades`;
CREATE TABLE `especialidades` (
  `id` int NOT NULL AUTO_INCREMENT,
  `codigo` varchar(10) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text,
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `especialidad_campos`
--

DROP TABLE IF EXISTS `especialidad_campos`;
CREATE TABLE `especialidad_campos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `especialidad_id` int NOT NULL,
  `nombre_campo` varchar(50) NOT NULL,
  `etiqueta` varchar(100) NOT NULL,
  `tipo_campo` enum('texto','numero','fecha','seleccion','checkbox','textarea') NOT NULL,
  `opciones` text,
  `requerido` tinyint(1) DEFAULT '0',
  `orden` int DEFAULT '0',
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_campo_especialidad` (`especialidad_id`,`nombre_campo`),
  CONSTRAINT `fk_especialidad_campos_especialidad` FOREIGN KEY (`especialidad_id`) REFERENCES `especialidades` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pacientes`
--

DROP TABLE IF EXISTS `pacientes`;
CREATE TABLE `pacientes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `dni` varchar(20) DEFAULT NULL,
  `sexo` enum('M','F','O') DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `direccion` text,
  `seguro_medico` varchar(100) DEFAULT NULL,
  `numero_poliza` varchar(50) DEFAULT NULL,
  `contacto_emergencia` varchar(100) DEFAULT NULL,
  `telefono_emergencia` varchar(20) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dni` (`dni`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_medico`
--

DROP TABLE IF EXISTS `historial_medico`;
CREATE TABLE `historial_medico` (
  `id` int NOT NULL AUTO_INCREMENT,
  `paciente_id` int NOT NULL,
  `doctor_id` int DEFAULT NULL,
  `fecha` date NOT NULL,
  `motivo_consulta` text,
  `diagnostico` text,
  `tratamiento` text,
  `observaciones` text,
  `campos_adicionales` json DEFAULT NULL,
  `especialidad_id` int DEFAULT NULL,
  `dientes_seleccionados` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_historial_paciente` (`paciente_id`),
  KEY `fk_historial_doctor` (`doctor_id`),
  KEY `fk_historial_especialidad` (`especialidad_id`),
  CONSTRAINT `fk_historial_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_historial_especialidad` FOREIGN KEY (`especialidad_id`) REFERENCES `especialidades` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_historial_paciente` FOREIGN KEY (`paciente_id`) REFERENCES `pacientes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `consulta_campos_valores`
--

DROP TABLE IF EXISTS `consulta_campos_valores`;
CREATE TABLE `consulta_campos_valores` (
  `id` int NOT NULL AUTO_INCREMENT,
  `consulta_id` int NOT NULL,
  `campo_id` int NOT NULL,
  `valor` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_consulta_campos_consulta` (`consulta_id`),
  KEY `fk_consulta_campos_campo` (`campo_id`),
  CONSTRAINT `fk_consulta_campos_campo` FOREIGN KEY (`campo_id`) REFERENCES `especialidad_campos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_consulta_campos_consulta` FOREIGN KEY (`consulta_id`) REFERENCES `historial_medico` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `enfermedades`
--

DROP TABLE IF EXISTS `enfermedades`;
CREATE TABLE `enfermedades` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text,
  `codigo_cie10` varchar(10) DEFAULT NULL,
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo_cie10` (`codigo_cie10`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `paciente_enfermedades`
--

DROP TABLE IF EXISTS `paciente_enfermedades`;
CREATE TABLE `paciente_enfermedades` (
  `id` int NOT NULL AUTO_INCREMENT,
  `paciente_id` int NOT NULL,
  `enfermedad_id` int NOT NULL,
  `estado` enum('activa','resuelta','cronica') DEFAULT 'activa',
  `fecha_diagnostico` date DEFAULT NULL,
  `notas` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_paciente_enfermedad` (`paciente_id`,`enfermedad_id`),
  KEY `fk_paciente_enfermedad_enfermedad` (`enfermedad_id`),
  CONSTRAINT `fk_paciente_enfermedad_enfermedad` FOREIGN KEY (`enfermedad_id`) REFERENCES `enfermedades` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_paciente_enfermedad_paciente` FOREIGN KEY (`paciente_id`) REFERENCES `pacientes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `citas`
--

DROP TABLE IF EXISTS `citas`;
CREATE TABLE `citas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `paciente_id` int NOT NULL,
  `medico_id` int DEFAULT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `motivo` text,
  `estado` enum('programada','confirmada','en_curso','completada','cancelada','no_asistio') DEFAULT 'programada',
  `observaciones` text,
  `orden_llegada` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_citas_paciente` (`paciente_id`),
  KEY `fk_citas_medico` (`medico_id`),
  KEY `idx_fecha_hora` (`fecha`,`hora`),
  CONSTRAINT `fk_citas_medico` FOREIGN KEY (`medico_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_citas_paciente` FOREIGN KEY (`paciente_id`) REFERENCES `pacientes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion`
--

DROP TABLE IF EXISTS `configuracion`;
CREATE TABLE `configuracion` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre_consultorio` varchar(255) DEFAULT 'Consultorio Médico',
  `direccion` text,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `especialidad_id` int DEFAULT NULL,
  `horario_atencion` json DEFAULT NULL,
  `configuraciones_adicionales` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_configuracion_especialidad` (`especialidad_id`),
  CONSTRAINT `fk_configuracion_especialidad` FOREIGN KEY (`especialidad_id`) REFERENCES `especialidades` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permisos`
--

DROP TABLE IF EXISTS `permisos`;
CREATE TABLE `permisos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `categoria` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario_permisos`
--

DROP TABLE IF EXISTS `usuario_permisos`;
CREATE TABLE `usuario_permisos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `permiso_id` int NOT NULL,
  `granted_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_usuario_permiso` (`usuario_id`,`permiso_id`),
  KEY `fk_usuario_permisos_permiso` (`permiso_id`),
  CONSTRAINT `fk_usuario_permisos_permiso` FOREIGN KEY (`permiso_id`) REFERENCES `permisos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_usuario_permisos_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `whatsapp_config`
--

DROP TABLE IF EXISTS `whatsapp_config`;
CREATE TABLE `whatsapp_config` (
  `id` int NOT NULL AUTO_INCREMENT,
  `server_url` varchar(255) DEFAULT NULL,
  `api_key` varchar(255) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT '0',
  `configuracion` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `procedimientos`
--

DROP TABLE IF EXISTS `procedimientos`;
CREATE TABLE `procedimientos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `codigo` varchar(20) DEFAULT NULL,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text,
  `precio_costo` decimal(10,2) DEFAULT '0.00',
  `precio_venta` decimal(10,2) NOT NULL DEFAULT '0.00',
  `categoria` enum('procedimiento','utensilio','material','medicamento') DEFAULT 'procedimiento',
  `activo` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo` (`codigo`),
  KEY `idx_categoria` (`categoria`),
  KEY `idx_activo` (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Volcado de datos para la tabla `especialidades`
--

INSERT INTO `especialidades` (`id`, `codigo`, `nombre`, `descripcion`, `estado`) VALUES
(1, 'MG', 'Medicina General', 'Especialidad médica básica y general', 'activo'),
(2, 'PED', 'Pediatría', 'Especialidad médica que estudia al niño y sus enfermedades', 'activo'),
(3, 'GIN', 'Ginecología', 'Especialidad médica de la salud femenina', 'activo'),
(4, 'ODON', 'Odontología', 'Especialidad médica que se encarga del diagnóstico, tratamiento y prevención de las enfermedades del aparato estomatognático', 'activo'),
(5, 'CARD', 'Cardiología', 'Especialidad médica que se encarga del estudio, diagnóstico y tratamiento de las enfermedades del corazón y del aparato circulatorio', 'activo'),
(6, 'DERM', 'Dermatología', 'Especialidad médica que se encarga del estudio de la estructura y función de la piel', 'activo'),
(7, 'OFTAL', 'Oftalmología', 'Especialidad médica que estudia las enfermedades de los ojos y su tratamiento', 'activo'),
(8, 'PSIQ', 'Psiquiatría', 'Especialidad médica que se dedica al estudio de los trastornos mentales', 'activo'),
(9, 'ORTOP', 'Ortopedia', 'Especialidad médica que se dedica a corregir o de evitar las deformidades o traumas del sistema musculoesquelético', 'activo'),
(10, 'NEURO', 'Neurología', 'Especialidad médica que trata los trastornos del sistema nervioso', 'activo');

-- --------------------------------------------------------

--
-- Volcado de datos para la tabla `permisos`
--

INSERT INTO `permisos` (`id`, `nombre`, `descripcion`, `categoria`) VALUES
(1, 'manage_patients', 'Gestionar pacientes (crear, editar, eliminar)', 'Pacientes'),
(2, 'view_patients', 'Ver información de pacientes', 'Pacientes'),
(3, 'manage_appointments', 'Gestionar citas médicas', 'Citas'),
(4, 'view_appointments', 'Ver citas médicas', 'Citas'),
(5, 'manage_medical_records', 'Gestionar historiales médicos', 'Historiales'),
(6, 'view_medical_records', 'Ver historiales médicos', 'Historiales'),
(7, 'manage_users', 'Gestionar usuarios del sistema', 'Administración'),
(8, 'manage_settings', 'Gestionar configuración del sistema', 'Administración'),
(9, 'manage_diseases', 'Gestionar catálogo de enfermedades', 'Catálogos'),
(10, 'manage_specialties', 'Gestionar especialidades médicas', 'Catálogos'),
(11, 'generate_reports', 'Generar reportes', 'Reportes'),
(12, 'manage_whatsapp', 'Gestionar configuración de WhatsApp', 'Comunicación');

-- --------------------------------------------------------

--
-- Volcado de datos para la tabla `configuracion`
--

INSERT INTO `configuracion` (`id`, `nombre_consultorio`, `especialidad_id`) VALUES
(1, 'Consultorio Médico', 1);

-- --------------------------------------------------------

--
-- Agregar las claves foráneas
--

ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usuarios_especialidad` FOREIGN KEY (`especialidad_id`) REFERENCES `especialidades` (`id`) ON DELETE SET NULL;

--
-- AUTO_INCREMENT de las tablas volcadas
--

ALTER TABLE `usuarios`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

ALTER TABLE `especialidades`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

ALTER TABLE `especialidad_campos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

ALTER TABLE `pacientes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

ALTER TABLE `historial_medico`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

ALTER TABLE `consulta_campos_valores`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

ALTER TABLE `enfermedades`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

ALTER TABLE `paciente_enfermedades`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

ALTER TABLE `citas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

ALTER TABLE `configuracion`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `permisos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

ALTER TABLE `usuario_permisos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

ALTER TABLE `whatsapp_config`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

ALTER TABLE `procedimientos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
