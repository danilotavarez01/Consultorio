# Módulo de Facturación - Sistema de Consultorio Odontológico

## ✅ Funcionalidades Implementadas

### 📋 **Gestión de Facturas**
- **Crear facturas**: Formulario completo con selección de paciente, procedimientos y cálculos automáticos
- **Listar facturas**: Vista con filtros por estado, paciente y fechas
- **Estados de factura**: Pendiente, Pagada, Vencida, Cancelada
- **Gestión de items**: Múltiples procedimientos por factura con cantidades y descuentos
- **Numeración automática**: Sistema de numeración FAC-0001, FAC-0002, etc.

### 💰 **Gestión de Pagos**
- **Registrar pagos**: Múltiples métodos de pago (efectivo, transferencia, tarjetas, cheque)
- **Pagos parciales**: Soporte para pagos en cuotas
- **Estado automático**: Cambio automático a "Pagada" cuando se completa el pago
- **Referencia de transacciones**: Número de referencia para cada pago

### 📊 **Reportes y Análisis**
- **Dashboard de facturación**: Resumen general con estadísticas clave
- **Análisis por período**: Filtros de fecha personalizables
- **Top procedimientos**: Los más facturados y rentables
- **Top pacientes**: Clientes con mayor facturación
- **Métodos de pago**: Análisis de preferencias de pago
- **Facturación diaria**: Evolución día a día

## 🗄️ **Estructura de Base de Datos**

### Tabla `facturas`
```sql
- id (PK)
- numero_factura (UNIQUE)
- paciente_id (FK)
- medico_id (FK)
- fecha_factura
- fecha_vencimiento
- subtotal, descuento, impuestos, total
- estado (pendiente/pagada/vencida/cancelada)
- metodo_pago
- observaciones
- created_at, updated_at
```

### Tabla `factura_detalles`
```sql
- id (PK)
- factura_id (FK)
- procedimiento_id (FK opcional)
- descripcion
- cantidad, precio_unitario, descuento_item, subtotal
- created_at
```

### Tabla `pagos`
```sql
- id (PK)
- factura_id (FK)
- fecha_pago
- monto
- metodo_pago
- numero_referencia
- observaciones
- created_at
```

## 🔐 **Sistema de Permisos**

### Permisos de Facturación
- `manage_billing`: Gestión completa de facturación
- `create_invoices`: Crear nuevas facturas
- `view_invoices`: Ver facturas existentes
- `edit_invoices`: Editar facturas
- `delete_invoices`: Eliminar facturas
- `manage_payments`: Gestionar pagos
- `view_reports`: Ver reportes

### Asignación Automática
- Todos los permisos se asignan automáticamente al usuario admin
- Los permisos se pueden gestionar desde la interfaz de permisos

## 🌐 **Archivos del Sistema**

### Principales
- `facturacion.php`: Módulo principal de facturación
- `reportes_facturacion.php`: Dashboard de reportes y análisis
- `sidebar.php`: Menú lateral actualizado con enlaces

### Scripts de Configuración
- Tablas creadas automáticamente con charset UTF8 compatible
- Permisos insertados y asignados automáticamente
- Datos de ejemplo para pruebas

## 🎯 **Características Destacadas**

### 💡 **Interfaz Intuitiva**
- Diseño moderno con Bootstrap 4
- Modales para acciones rápidas
- Cálculos automáticos en tiempo real
- Filtros dinámicos
- Indicadores visuales de estado

### 🔧 **Funcionalidad Avanzada**
- Integración con módulo de procedimientos
- Códigos automáticos de procedimientos
- Cálculos automáticos de totales
- Validación de permisos por acción
- Compatible con versiones antiguas de MySQL

### 📱 **Responsivo**
- Diseño adaptable a diferentes dispositivos
- Tablas responsivas
- Modales optimizados para móviles

## 🚀 **Uso del Sistema**

### Para Crear una Factura:
1. Ir a **Facturación** → **Nueva Factura**
2. Seleccionar paciente
3. Agregar procedimientos (automático o manual)
4. El sistema calcula totales automáticamente
5. Guardar factura

### Para Registrar un Pago:
1. En la lista de facturas, click en 💰
2. Ingresar monto y método de pago
3. Agregar referencia si es necesario
4. El sistema actualiza el estado automáticamente

### Para Ver Reportes:
1. Ir a **Reportes**
2. Seleccionar período de análisis
3. Ver estadísticas y gráficos automáticos

## 📈 **Beneficios del Sistema**

- ✅ **Gestión completa** de facturación odontológica
- ✅ **Reportes automáticos** para toma de decisiones
- ✅ **Control de pagos** y estado de cuentas
- ✅ **Integración completa** con el sistema existente
- ✅ **Permisos granulares** para diferentes usuarios
- ✅ **Diseño profesional** y fácil de usar

## 🔗 **Enlaces de Acceso**

- **Facturación**: http://localhost/Consultorio2/facturacion.php
- **Reportes**: http://localhost/Consultorio2/reportes_facturacion.php
- **Procedimientos**: http://localhost/Consultorio2/procedimientos.php

---

**El módulo de facturación está completamente funcional y listo para uso en producción.**
