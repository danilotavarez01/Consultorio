-- Database Schema
-- Generated on 2025-07-19T15:09:23.886Z

-- Total tables: 20

-- Table: citas
-- Columns: 7
CREATE TABLE citas (
    id int NOT NULL,
    paciente_id int NOT NULL,
    fecha date NOT NULL,
    hora time NOT NULL,
    doctor_id int NOT NULL,
    estado enum DEFAULT Pendiente,
    observaciones text
);

-- Table: configuracion
-- Columns: 30
CREATE TABLE configuracion (
    id int NOT NULL,
    nombre_consultorio varchar NOT NULL DEFAULT Consultorio Médico,
    email_contacto varchar,
    medico_nombre varchar DEFAULT Dr. Médico,
    multi_medico tinyint DEFAULT 0,
    logo mediumblob,
    duracion_cita int DEFAULT 30,
    hora_inicio time DEFAULT 09:00:00,
    hora_fin time DEFAULT 18:00:00,
    require_https tinyint DEFAULT 0,
    modo_mantenimiento tinyint DEFAULT 0,
    telefono varchar,
    direccion text,
    updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by varchar,
    dias_laborables varchar DEFAULT 1,2,3,4,5,
    intervalo_citas int DEFAULT 30,
    moneda varchar DEFAULT $,
    zona_horaria varchar DEFAULT America/Santo_Domingo,
    formato_fecha varchar DEFAULT Y-m-d,
    idioma varchar DEFAULT es,
    tema_color varchar DEFAULT light,
    mostrar_alertas_stock tinyint DEFAULT 1,
    notificaciones_email tinyint DEFAULT 0,
    especialidad_id int,
    whatsapp_server varchar DEFAULT https://api.whatsapp.com,
    email varchar,
    ruc varchar,
    mensaje_recibo text,
    logo_path varchar
);

-- Table: consulta_campos_valores
-- Columns: 6
CREATE TABLE consulta_campos_valores (
    id int NOT NULL,
    consulta_id int NOT NULL,
    campo_id int NOT NULL,
    valor text,
    created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime
);

-- Table: enfermedades
-- Columns: 4
CREATE TABLE enfermedades (
    id int NOT NULL,
    nombre varchar NOT NULL,
    descripcion text,
    fecha_registro timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Table: especialidades
-- Columns: 7
CREATE TABLE especialidades (
    id int NOT NULL,
    codigo varchar NOT NULL,
    nombre varchar NOT NULL,
    descripcion text,
    estado enum DEFAULT activo,
    created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime
);

-- Table: especialidad_campos
-- Columns: 11
CREATE TABLE especialidad_campos (
    id int NOT NULL,
    especialidad_id int NOT NULL,
    nombre_campo varchar NOT NULL,
    etiqueta varchar NOT NULL,
    tipo_campo enum NOT NULL,
    opciones text,
    requerido tinyint DEFAULT 0,
    orden int DEFAULT 0,
    estado enum DEFAULT activo,
    created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime
);

-- Table: facturas
-- Columns: 15
CREATE TABLE facturas (
    id int NOT NULL,
    numero_factura varchar NOT NULL,
    paciente_id int NOT NULL,
    medico_id int,
    fecha_factura date NOT NULL,
    fecha_vencimiento date,
    subtotal decimal NOT NULL DEFAULT 0.00,
    descuento decimal DEFAULT 0.00,
    impuestos decimal DEFAULT 0.00,
    total decimal NOT NULL DEFAULT 0.00,
    estado enum DEFAULT pendiente,
    metodo_pago varchar,
    observaciones text,
    created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp
);

-- Table: factura_detalles
-- Columns: 9
CREATE TABLE factura_detalles (
    id int NOT NULL,
    factura_id int NOT NULL,
    procedimiento_id int,
    descripcion varchar NOT NULL,
    cantidad int NOT NULL DEFAULT 1,
    precio_unitario decimal NOT NULL,
    descuento_item decimal DEFAULT 0.00,
    subtotal decimal NOT NULL,
    created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Table: historial_medico
-- Columns: 23
CREATE TABLE historial_medico (
    id int NOT NULL,
    paciente_id int,
    fecha date NOT NULL,
    motivo_consulta text NOT NULL,
    diagnostico text,
    tratamiento text,
    notas text,
    doctor_id int,
    fecha_registro timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    observaciones text,
    campos_adicionales text,
    especialidad_id int,
    medico varchar,
    presion_sanguinea varchar,
    frecuencia_cardiaca int,
    tipo_consulta varchar DEFAULT Consulta,
    peso varchar,
    dientes_seleccionados text,
    temperatura decimal,
    presion_arterial varchar,
    frecuencia_respiratoria int,
    saturacion_oxigeno int,
    estado enum DEFAULT pendiente
);

-- Table: pacientes
-- Columns: 15
CREATE TABLE pacientes (
    id int NOT NULL,
    nombre varchar NOT NULL,
    apellido varchar NOT NULL,
    dni varchar NOT NULL,
    fecha_nacimiento date,
    telefono varchar,
    email varchar,
    direccion text,
    fecha_registro timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    sexo varchar,
    seguro_medico varchar,
    numero_poliza varchar,
    contacto_emergencia varchar,
    telefono_emergencia varchar,
    foto varchar
);

-- Table: paciente_enfermedades
-- Columns: 7
CREATE TABLE paciente_enfermedades (
    id int NOT NULL,
    paciente_id int,
    enfermedad_id int,
    fecha_diagnostico date,
    estado enum DEFAULT activa,
    notas text,
    fecha_registro timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Table: pagos
-- Columns: 8
CREATE TABLE pagos (
    id int NOT NULL,
    factura_id int NOT NULL,
    fecha_pago date NOT NULL,
    monto decimal NOT NULL,
    metodo_pago varchar NOT NULL,
    numero_referencia varchar,
    observaciones text,
    created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Table: permisos
-- Columns: 5
CREATE TABLE permisos (
    id int NOT NULL,
    nombre varchar NOT NULL,
    descripcion varchar,
    categoria varchar,
    created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Table: permissions
-- Columns: 5
CREATE TABLE permissions (
    id int NOT NULL,
    name varchar NOT NULL,
    description varchar,
    category varchar DEFAULT General,
    created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Table: procedimientos
-- Columns: 10
CREATE TABLE procedimientos (
    id int NOT NULL,
    codigo varchar,
    nombre varchar NOT NULL,
    descripcion text,
    precio_costo decimal DEFAULT 0.00,
    precio_venta decimal NOT NULL DEFAULT 0.00,
    categoria enum DEFAULT procedimiento,
    activo tinyint DEFAULT 1,
    created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime
);

-- Table: receptionist_permissions
-- Columns: 4
CREATE TABLE receptionist_permissions (
    id int NOT NULL,
    receptionist_id int NOT NULL,
    permission varchar NOT NULL,
    assigned_by int NOT NULL
);

-- Table: recetas
-- Columns: 7
CREATE TABLE recetas (
    id int NOT NULL,
    paciente_id int,
    fecha_receta date NOT NULL,
    medicamentos text NOT NULL,
    indicaciones text,
    doctor_id int,
    tamano_receta varchar DEFAULT completa
);

-- Table: turnos
-- Columns: 7
CREATE TABLE turnos (
    id int NOT NULL,
    paciente_id int,
    fecha_turno date NOT NULL,
    hora_turno time NOT NULL,
    estado enum DEFAULT pendiente,
    notas text,
    tipo_turno varchar DEFAULT Consulta
);

-- Table: usuarios
-- Columns: 7
CREATE TABLE usuarios (
    id int NOT NULL,
    username varchar NOT NULL,
    password varchar NOT NULL,
    nombre varchar NOT NULL,
    rol enum NOT NULL,
    active tinyint NOT NULL DEFAULT 1,
    especialidad_id int
);

-- Table: usuario_permisos
-- Columns: 4
CREATE TABLE usuario_permisos (
    id int NOT NULL,
    usuario_id int NOT NULL,
    permiso_id int NOT NULL,
    granted_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
);

