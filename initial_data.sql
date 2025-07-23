-- Script para crear usuario administrador por defecto
-- Ejecutar DESPUÉS de importar database_structure.sql

-- Insertar usuario administrador por defecto
INSERT INTO `usuarios` (`id`, `username`, `password`, `email`, `nombre`, `apellido`, `rol`, `activo`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@consultorio.com', 'Administrador', 'Sistema', 'admin', 1);

-- Asignar TODOS los permisos al usuario admin
INSERT INTO `usuario_permisos` (`usuario_id`, `permiso_id`) 
SELECT 1, id FROM permisos;

-- Agregar permiso específico para gestionar catálogos si no existe
INSERT IGNORE INTO `permisos` (`nombre`, `descripcion`) VALUES 
('gestionar_catalogos', 'Gestionar catálogos y procedimientos');

-- Asegurar que admin tenga el nuevo permiso
INSERT IGNORE INTO `usuario_permisos` (`usuario_id`, `permiso_id`) 
SELECT 1, id FROM permisos WHERE nombre = 'gestionar_catalogos';

-- Insertar algunas enfermedades comunes de ejemplo
INSERT INTO `enfermedades` (`nombre`, `descripcion`, `codigo_cie10`) VALUES
('Hipertensión Arterial', 'Presión arterial alta', 'I10'),
('Diabetes Mellitus Tipo 2', 'Diabetes tipo 2', 'E11'),
('Asma', 'Enfermedad respiratoria crónica', 'J45'),
('Obesidad', 'Exceso de peso corporal', 'E66'),
('Depresión', 'Trastorno depresivo mayor', 'F32'),
('Ansiedad', 'Trastorno de ansiedad generalizada', 'F41.1'),
('Artritis', 'Inflamación de las articulaciones', 'M13'),
('Migraña', 'Dolor de cabeza recurrente', 'G43'),
('Gastritis', 'Inflamación del estómago', 'K29'),
('Hipotiroidismo', 'Función tiroidea reducida', 'E03');

-- Datos de ejemplo para procedimientos odontológicos
INSERT INTO `procedimientos` (`codigo`, `nombre`, `descripcion`, `precio_costo`, `precio_venta`, `activo`, `categoria`) VALUES
('PROC001', 'Limpieza Dental', 'Profilaxis dental completa', 10.00, 50.00, 1, 'procedimiento'),
('PROC002', 'Obturación Simple', 'Relleno de caries superficial', 15.00, 75.00, 1, 'procedimiento'),
('PROC003', 'Extracción Simple', 'Extracción de pieza dental', 20.00, 100.00, 1, 'procedimiento'),
('PROC004', 'Tratamiento de Conducto', 'Endodoncia completa', 50.00, 250.00, 1, 'procedimiento'),
('PROC005', 'Corona Dental', 'Prótesis dental fija', 100.00, 500.00, 1, 'procedimiento'),
('PROC006', 'Implante Dental', 'Implante de titanio', 200.00, 800.00, 1, 'procedimiento'),
('UTEN001', 'Brackets Metálicos', 'Aparato de ortodoncia', 150.00, 600.00, 1, 'utensilio'),
('MAT001', 'Resina Composite', 'Material de obturación', 5.00, 20.00, 1, 'material'),
('MED001', 'Anestesia Local', 'Lidocaína con epinefrina', 2.00, 10.00, 1, 'medicamento'),
('UTEN002', 'Gasas Estériles', 'Material de curación', 1.00, 5.00, 1, 'utensilio');

-- Insertar campos específicos para Odontología
INSERT INTO `especialidad_campos` (`especialidad_id`, `nombre_campo`, `etiqueta`, `tipo_campo`, `requerido`, `orden`) VALUES
(4, 'dientes_tratados', 'Dientes Tratados', 'texto', 0, 1),
(4, 'tratamiento_realizado', 'Tratamiento Realizado', 'seleccion', 1, 2),
(4, 'anestesia_utilizada', 'Anestesia Utilizada', 'seleccion', 0, 3),
(4, 'materiales_utilizados', 'Materiales Utilizados', 'textarea', 0, 4),
(4, 'proxima_cita', 'Próxima Cita', 'fecha', 0, 5);

-- Actualizar opciones para campos de selección en Odontología
UPDATE `especialidad_campos` SET `opciones` = 'Limpieza dental,Obturación,Extracción,Endodoncia,Corona,Implante,Ortodoncia,Blanqueamiento' 
WHERE `nombre_campo` = 'tratamiento_realizado' AND `especialidad_id` = 4;

UPDATE `especialidad_campos` SET `opciones` = 'Local,Tópica,Ninguna' 
WHERE `nombre_campo` = 'anestesia_utilizada' AND `especialidad_id` = 4;

-- Insertar campos para Medicina General
INSERT INTO `especialidad_campos` (`especialidad_id`, `nombre_campo`, `etiqueta`, `tipo_campo`, `requerido`, `orden`) VALUES
(1, 'peso', 'Peso (kg)', 'numero', 0, 1),
(1, 'altura', 'Altura (cm)', 'numero', 0, 2),
(1, 'presion_arterial', 'Presión Arterial', 'texto', 0, 3),
(1, 'temperatura', 'Temperatura (°C)', 'numero', 0, 4),
(1, 'frecuencia_cardiaca', 'Frecuencia Cardíaca', 'numero', 0, 5),
(1, 'alergias', 'Alergias', 'textarea', 0, 6),
(1, 'medicamentos_actuales', 'Medicamentos Actuales', 'textarea', 0, 7);

-- Insertar campos para Pediatría
INSERT INTO `especialidad_campos` (`especialidad_id`, `nombre_campo`, `etiqueta`, `tipo_campo`, `requerido`, `orden`) VALUES
(2, 'peso', 'Peso (kg)', 'numero', 1, 1),
(2, 'altura', 'Altura (cm)', 'numero', 1, 2),
(2, 'percentil_peso', 'Percentil de Peso', 'texto', 0, 3),
(2, 'percentil_altura', 'Percentil de Altura', 'texto', 0, 4),
(2, 'vacunas_al_dia', 'Vacunas al Día', 'checkbox', 0, 5),
(2, 'desarrollo_psicomotor', 'Desarrollo Psicomotor', 'seleccion', 0, 6),
(2, 'alimentacion', 'Alimentación', 'textarea', 0, 7);

-- Actualizar opciones para campos de selección en Pediatría
UPDATE `especialidad_campos` SET `opciones` = 'Normal,Retraso leve,Retraso moderado,Avanzado' 
WHERE `nombre_campo` = 'desarrollo_psicomotor' AND `especialidad_id` = 2;

COMMIT;
