# Implementación: Filtro por Doctor en Turnos

## Funcionalidad Agregada
Se implementó un filtro por doctor en la página de gestión de turnos (`turnos.php`) para permitir filtrar los turnos por médico específico.

## Cambios Implementados

### 1. Interfaz de Usuario
Se modificó la sección de filtros para incluir:

#### Antes:
```html
<div class="row mb-3">
    <div class="col-md-4">
        <input type="date" id="filtroFecha" class="form-control">
    </div>
    <div class="col-md-4">
        <select id="filtroEstado" class="form-control">
            <!-- opciones de estado -->
        </select>
    </div>
</div>
```

#### Después:
```html
<div class="row mb-3">
    <div class="col-md-3">
        <input type="date" id="filtroFecha" class="form-control">
    </div>
    <div class="col-md-3">
        <select id="filtroEstado" class="form-control">
            <!-- opciones de estado -->
        </select>
    </div>
    <?php if ($multi_medico): ?>
    <div class="col-md-3">
        <select id="filtroMedico" class="form-control">
            <option value="">Todos los médicos</option>
            <?php foreach ($doctores as $doctor): ?>
            <option value="<?php echo $doctor['id']; ?>">
                <?php echo htmlspecialchars($doctor['nombre']); ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php endif; ?>
    <div class="col-md-3">
        <button type="button" id="aplicarFiltros" class="btn btn-primary">
            <i class="fas fa-search"></i> Filtrar
        </button>
        <button type="button" id="limpiarFiltros" class="btn btn-secondary">
            <i class="fas fa-eraser"></i> Limpiar
        </button>
    </div>
</div>
```

### 2. Lógica de Backend
Se mejoró la consulta SQL para soportar filtros dinámicos:

#### Antes:
```php
$sql = "SELECT t.*, p.nombre, p.apellido, p.dni 
        FROM turnos t 
        JOIN pacientes p ON t.paciente_id = p.id 
        WHERE fecha_turno = ? 
        ORDER BY t.orden_llegada IS NULL, t.orden_llegada, t.hora_turno";
$stmt->execute([$fecha_mostrar]);
```

#### Después:
```php
// Construir la consulta con filtros dinámicos
$where_conditions = ["fecha_turno = ?"];
$params = [$fecha_mostrar];

// Agregar filtro por estado si se especifica
if (isset($_GET['estado']) && !empty($_GET['estado'])) {
    $where_conditions[] = "t.estado = ?";
    $params[] = $_GET['estado'];
}

// Agregar filtro por médico si se especifica y multi_medico está habilitado
if ($multi_medico && isset($_GET['medico']) && !empty($_GET['medico'])) {
    $where_conditions[] = "t.medico_id = ?";
    $params[] = $_GET['medico'];
}

$where_clause = implode(" AND ", $where_conditions);
$sql = "SELECT t.*, p.nombre, p.apellido, p.dni 
        FROM turnos t 
        JOIN pacientes p ON t.paciente_id = p.id 
        WHERE $where_clause 
        ORDER BY t.orden_llegada IS NULL, t.orden_llegada, t.hora_turno";
$stmt->execute($params);
```

### 3. JavaScript Mejorado
Se implementó un sistema de filtros más robusto:

```javascript
// Función para aplicar filtros
function aplicarFiltros() {
    var fecha = $('#filtroFecha').val();
    var estado = $('#filtroEstado').val();
    var medico = $('#filtroMedico').length ? $('#filtroMedico').val() : '';
    
    var url = 'turnos.php?fecha=' + fecha;
    if (estado) {
        url += '&estado=' + estado;
    }
    if (medico) {
        url += '&medico=' + medico;
    }
    
    window.location.href = url;
}

// Establecer los valores de los filtros desde la URL
var urlParams = new URLSearchParams(window.location.search);
if (urlParams.has('fecha')) {
    $('#filtroFecha').val(urlParams.get('fecha'));
}
if (urlParams.has('estado')) {
    $('#filtroEstado').val(urlParams.get('estado'));
}
if (urlParams.has('medico') && $('#filtroMedico').length) {
    $('#filtroMedico').val(urlParams.get('medico'));
}
```

## Características

### 1. Condicional según Configuración
- **Con multi_medico habilitado:** Muestra dropdown con todos los médicos disponibles
- **Con multi_medico deshabilitado:** No muestra el filtro (solo un médico en el sistema)

### 2. Filtros Combinables
- ✅ Fecha + Estado
- ✅ Fecha + Médico
- ✅ Fecha + Estado + Médico
- ✅ Filtros persistentes en URL

### 3. Interfaz Intuitiva
- Botones "Filtrar" y "Limpiar" para mejor UX
- Filtros se mantienen al cambiar estados de turnos
- Layout responsive adaptado

### 4. Parámetros URL Soportados
- `fecha`: Fecha de los turnos (formato: YYYY-MM-DD)
- `estado`: Estado del turno (pendiente, en_consulta, atendido, cancelado)
- `medico`: ID del médico (solo si multi_medico está habilitado)

## Ejemplos de URLs

```
# Turnos de hoy
turnos.php?fecha=2025-07-23

# Turnos pendientes de hoy
turnos.php?fecha=2025-07-23&estado=pendiente

# Turnos del Dr. García de hoy
turnos.php?fecha=2025-07-23&medico=2

# Turnos atendidos del Dr. García de hoy
turnos.php?fecha=2025-07-23&estado=atendido&medico=2
```

## Archivos Modificados
- `turnos.php` - Implementación completa del filtro por doctor

## Archivos de Prueba
- `test_filtro_doctor_turnos.php` - Script de verificación y estadísticas

## Beneficios
- ✅ Mejor organización de turnos por médico
- ✅ Facilita el seguimiento de carga de trabajo por doctor
- ✅ Interfaz más limpia y funcional
- ✅ Compatibilidad total con configuraciones existentes
- ✅ Filtros persistentes y combinables

## Cómo Usar
1. Ir a la página de turnos
2. Seleccionar fecha (obligatorio)
3. Opcionalmente seleccionar estado y/o médico
4. Hacer clic en "Filtrar" o cambiar la fecha para aplicar
5. Usar "Limpiar" para resetear filtros manteniendo la fecha

## Fecha de Implementación
23 de julio de 2025
