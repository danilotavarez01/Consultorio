CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    rol ENUM('admin', 'doctor', 'recepcionista') NOT NULL
);

CREATE TABLE IF NOT EXISTS configuracion (
    id INT PRIMARY KEY,
    nombre_consultorio VARCHAR(255) NOT NULL DEFAULT 'Consultorio Médico',
    email_contacto VARCHAR(255),
    logo MEDIUMBLOB,
    especialidad_id INT,
    duracion_cita INT DEFAULT 30,
    hora_inicio TIME DEFAULT '09:00:00',
    hora_fin TIME DEFAULT '18:00:00',
    require_https BOOLEAN DEFAULT FALSE,
    modo_mantenimiento BOOLEAN DEFAULT FALSE,
    telefono VARCHAR(50),
    direccion TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by VARCHAR(50),
    FOREIGN KEY (especialidad_id) REFERENCES especialidades(id)
);

-- Insertar configuración inicial
INSERT INTO configuracion (id, nombre_consultorio, email_contacto, duracion_cita, hora_inicio, hora_fin, especialidad_id) 
SELECT 1, 'Consultorio Médico', NULL, 30, '09:00:00', '18:00:00', 
    (SELECT id FROM especialidades WHERE codigo = 'MG') 
ON DUPLICATE KEY UPDATE id = id;

CREATE TABLE IF NOT EXISTS pacientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    dni VARCHAR(20) NOT NULL UNIQUE,
    fecha_nacimiento DATE,
    telefono VARCHAR(20),
    email VARCHAR(100),
    direccion TEXT,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS turnos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    paciente_id INT,
    fecha_turno DATE NOT NULL,
    hora_turno TIME NOT NULL,
    estado ENUM('pendiente', 'atendido', 'cancelado') DEFAULT 'pendiente',
    notas TEXT,
    FOREIGN KEY (paciente_id) REFERENCES pacientes(id)
);

CREATE TABLE IF NOT EXISTS recetas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    paciente_id INT,
    fecha_receta DATE NOT NULL,
    medicamentos TEXT NOT NULL,
    indicaciones TEXT,
    doctor_id INT,
    tamano_receta VARCHAR(10) DEFAULT 'completa',
    FOREIGN KEY (paciente_id) REFERENCES pacientes(id),
    FOREIGN KEY (doctor_id) REFERENCES usuarios(id)
);

CREATE TABLE IF NOT EXISTS historial_medico (
    id INT AUTO_INCREMENT PRIMARY KEY,
    paciente_id INT,
    fecha DATE NOT NULL,
    motivo_consulta TEXT NOT NULL,
    diagnostico TEXT,
    tratamiento TEXT,
    notas TEXT,
    doctor_id INT,
    especialidad_id INT,
    temperatura DECIMAL(4,1),
    presion_arterial VARCHAR(20),
    frecuencia_cardiaca INT,
    frecuencia_respiratoria INT,
    saturacion_oxigeno INT,
    estado_consulta ENUM('pendiente', 'en_proceso', 'completada', 'cancelada') DEFAULT 'pendiente',
    FOREIGN KEY (paciente_id) REFERENCES pacientes(id),
    FOREIGN KEY (doctor_id) REFERENCES usuarios(id),
    FOREIGN KEY (especialidad_id) REFERENCES especialidades(id),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS enfermedades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS paciente_enfermedades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    paciente_id INT,
    enfermedad_id INT,
    fecha_diagnostico DATE,
    estado ENUM('activa', 'en_tratamiento', 'superada') DEFAULT 'activa',
    notas TEXT,
    FOREIGN KEY (paciente_id) REFERENCES pacientes(id),
    FOREIGN KEY (enfermedad_id) REFERENCES enfermedades(id),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS receptionist_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    receptionist_id INT NOT NULL,
    permission VARCHAR(50) NOT NULL,
    assigned_by INT NOT NULL,
    FOREIGN KEY (receptionist_id) REFERENCES usuarios(id),
    FOREIGN KEY (assigned_by) REFERENCES usuarios(id),
    UNIQUE KEY unique_permission (receptionist_id, permission)
);

CREATE TABLE IF NOT EXISTS especialidades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    estado ENUM('activo', 'inactivo') DEFAULT 'activo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS especialidad_campos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    especialidad_id INT NOT NULL,
    nombre_campo VARCHAR(50) NOT NULL,
    etiqueta VARCHAR(100) NOT NULL,
    tipo_campo ENUM('texto', 'numero', 'fecha', 'seleccion', 'checkbox', 'textarea') NOT NULL,
    opciones TEXT NULL,
    requerido TINYINT(1) DEFAULT 0,
    orden INT DEFAULT 0,
    estado ENUM('activo', 'inactivo') DEFAULT 'activo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (especialidad_id) REFERENCES especialidades(id) ON DELETE CASCADE,
    UNIQUE KEY unique_campo_especialidad (especialidad_id, nombre_campo)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS consulta_campos_valores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    consulta_id INT NOT NULL,
    campo_id INT NOT NULL,
    valor TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (consulta_id) REFERENCES historial_medico(id) ON DELETE CASCADE,
    FOREIGN KEY (campo_id) REFERENCES especialidad_campos(id) ON DELETE CASCADE,
    UNIQUE KEY unique_valor_campo (consulta_id, campo_id)
) ENGINE=InnoDB;

-- Insertar especialidades básicas
INSERT IGNORE INTO especialidades (codigo, nombre, descripcion) VALUES
('MG', 'Medicina General', 'Atención médica general y preventiva'),
('PED', 'Pediatría', 'Especialidad médica que estudia al niño y sus enfermedades'),
('GIN', 'Ginecología', 'Especialidad médica de la salud femenina'),
('CAR', 'Cardiología', 'Especialidad que trata enfermedades del corazón'),
('DER', 'Dermatología', 'Especialidad en enfermedades de la piel'),
('OFT', 'Oftalmología', 'Especialidad en enfermedades de los ojos');

-- Insertar campos para Pediatría
INSERT IGNORE INTO especialidad_campos 
(especialidad_id, nombre_campo, etiqueta, tipo_campo, requerido, orden)
SELECT 
    (SELECT id FROM especialidades WHERE codigo = 'PED'),
    nombre_campo,
    etiqueta,
    tipo_campo,
    requerido,
    orden
FROM (
    SELECT 'peso' AS nombre_campo, 'Peso (kg)' AS etiqueta, 'numero' AS tipo_campo, TRUE AS requerido, 1 AS orden
    UNION SELECT 'talla', 'Talla (cm)', 'numero', TRUE, 2
    UNION SELECT 'perimetro_cefalico', 'Perímetro Cefálico (cm)', 'numero', TRUE, 3
    UNION SELECT 'desarrollo', 'Desarrollo', 'seleccion', FALSE, 4
    UNION SELECT 'vacunas_completas', 'Vacunas al día', 'checkbox', FALSE, 5
) AS campos_pediatria;

-- Insertar campos para Ginecología
INSERT IGNORE INTO especialidad_campos 
(especialidad_id, nombre_campo, etiqueta, tipo_campo, opciones, requerido, orden)
SELECT 
    (SELECT id FROM especialidades WHERE codigo = 'GIN'),
    nombre_campo,
    etiqueta,
    tipo_campo,
    opciones,
    requerido,
    orden
FROM (
    SELECT 'ultima_menstruacion' AS nombre_campo, 'Fecha última menstruación' AS etiqueta, 'fecha' AS tipo_campo, NULL AS opciones, TRUE AS requerido, 1 AS orden
    UNION SELECT 'gestas', 'Número de embarazos', 'numero', NULL, FALSE, 2
    UNION SELECT 'partos', 'Número de partos', 'numero', NULL, FALSE, 3
    UNION SELECT 'cesareas', 'Número de cesáreas', 'numero', NULL, FALSE, 4
    UNION SELECT 'abortos', 'Número de abortos', 'numero', NULL, FALSE, 5
    UNION SELECT 'metodo_anticonceptivo', 'Método anticonceptivo', 'seleccion', 'Ninguno,DIU,Implante,Oral,Inyectable,Otro', FALSE, 6
    UNION SELECT 'papanicolau', 'Fecha último Papanicolau', 'fecha', NULL, FALSE, 7
) AS campos_ginecologia;

-- Crear usuario administrador por defecto
-- Usuario: admin
-- Contraseña: admin123
INSERT INTO usuarios (username, password, nombre, rol) 
VALUES ('admin', '$2y$10$8K1p/0G6nFk1rw1XZxzK8.3uXZHvCKt.QhQNB5fF5J5MvoVOXtB2q', 'Administrador', 'admin')
ON DUPLICATE KEY UPDATE username = username;

-- --------------------------------------------------------
-- Tabla: procedimientos
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS procedimientos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) UNIQUE,
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT,
    precio_costo DECIMAL(10,2) DEFAULT 0.00,
    precio_venta DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    categoria ENUM('procedimiento','utensilio','material','medicamento') DEFAULT 'procedimiento',
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL,
    INDEX idx_categoria (categoria),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Insertar datos de ejemplo para procedimientos
INSERT IGNORE INTO procedimientos (codigo, nombre, descripcion, precio_costo, precio_venta, categoria) VALUES
('PR001', 'Consulta General', 'Consulta médica general', 15.00, 25.00, 'procedimiento'),
('PR002', 'Limpieza Dental', 'Profilaxis dental completa', 20.00, 35.00, 'procedimiento'),
('PR003', 'Extracción Dental', 'Extracción de pieza dental', 30.00, 50.00, 'procedimiento'),
('PR004', 'Obturación Simple', 'Obturación con amalgama o resina', 25.00, 40.00, 'procedimiento'),
('PR005', 'Endodoncia', 'Tratamiento de conducto radicular', 80.00, 120.00, 'procedimiento'),
('UT001', 'Jeringa 5ml', 'Jeringa desechable de 5ml', 0.50, 1.00, 'utensilio'),
('UT002', 'Guantes Latex', 'Guantes desechables de látex', 0.25, 0.50, 'utensilio'),
('UT003', 'Mascarilla Quirúrgica', 'Mascarilla desechable', 0.30, 0.60, 'utensilio'),
('MT001', 'Algodón', 'Algodón estéril', 2.00, 3.50, 'material'),
('MT002', 'Gasa Estéril', 'Gasa estéril 5x5cm', 1.50, 3.00, 'material'),
('MT003', 'Anestesia Local', 'Lidocaína 2%', 5.00, 8.00, 'material'),
('MD001', 'Paracetamol 500mg', 'Analgésico y antipirético', 0.10, 0.25, 'medicamento'),
('MD002', 'Ibuprofeno 400mg', 'Antiinflamatorio', 0.15, 0.35, 'medicamento'),
('MD003', 'Amoxicilina 500mg', 'Antibiótico', 0.20, 0.45, 'medicamento');

-- --------------------------------------------------------
-- Tabla: permisos
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS permisos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(255) DEFAULT NULL,
    categoria VARCHAR(50) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------
-- Tabla: usuario_permisos
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS usuario_permisos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    permiso_id INT NOT NULL,
    granted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_usuario_permiso (usuario_id, permiso_id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (permiso_id) REFERENCES permisos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Insertar permisos básicos
INSERT IGNORE INTO permisos (nombre, descripcion, categoria) VALUES
('manage_patients', 'Gestionar pacientes (crear, editar, eliminar)', 'Pacientes'),
('view_patients', 'Ver información de pacientes', 'Pacientes'),
('manage_appointments', 'Gestionar citas médicas', 'Citas'),
('view_appointments', 'Ver citas médicas', 'Citas'),
('manage_medical_records', 'Gestionar historiales médicos', 'Historiales'),
('view_medical_records', 'Ver historiales médicos', 'Historiales'),
('manage_users', 'Gestionar usuarios del sistema', 'Administración'),
('manage_settings', 'Gestionar configuración del sistema', 'Administración'),
('manage_diseases', 'Gestionar catálogo de enfermedades', 'Catálogos'),
('manage_specialties', 'Gestionar especialidades médicas', 'Catálogos'),
('gestionar_catalogos', 'Gestionar catálogos y procedimientos', 'Catálogos'),
('manage_procedures', 'Gestionar procedimientos odontológicos', 'Catálogos'),
('generate_reports', 'Generar reportes', 'Reportes'),
('manage_whatsapp', 'Gestionar configuración de WhatsApp', 'Comunicación'),
('manage_prescriptions', 'Gestionar recetas médicas', 'Prescripciones'),
('view_prescriptions', 'Ver recetas médicas', 'Prescripciones');

-- Asignar todos los permisos al usuario admin
INSERT IGNORE INTO usuario_permisos (usuario_id, permiso_id)
SELECT 
    (SELECT id FROM usuarios WHERE username = 'admin'),
    p.id
FROM permisos p;