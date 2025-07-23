# Corrección: Agregar Doctor al Turno desde Citas

## Problema Identificado
Cuando se usaba el botón "Agregar a Turnos" desde la página de Citas, el turno creado no incluía la información del doctor asociado a la cita original.

## Solución Implementada

### Cambios en `turnos.php`
Se modificó el procesamiento de `agregar_desde_cita` para incluir la información del médico:

#### Antes:
```php
// Insertar el nuevo turno
$sql = "INSERT INTO turnos (paciente_id, fecha_turno, hora_turno, notas, tipo_turno, estado) 
        VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->execute([
    $cita['paciente_id'],
    $cita['fecha'],
    $cita['hora'],
    $notas,
    'Consulta',
    'pendiente'
]);
```

#### Después:
```php
// Insertar el nuevo turno con información del médico
if ($multi_medico) {
    // Si multi_medico está habilitado, usar medico_id y medico_nombre
    $sql = "INSERT INTO turnos (paciente_id, fecha_turno, hora_turno, notas, tipo_turno, estado, medico_id, medico_nombre) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        $cita['paciente_id'],
        $cita['fecha'],
        $cita['hora'],
        $notas,
        'Consulta',
        'pendiente',
        $cita['doctor_id'],
        $doctor_nombre
    ]);
} else {
    // Si multi_medico no está habilitado, usar solo medico_nombre del config
    $sql = "INSERT INTO turnos (paciente_id, fecha_turno, hora_turno, notas, tipo_turno, estado, medico_nombre) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        $cita['paciente_id'],
        $cita['fecha'],
        $cita['hora'],
        $notas,
        'Consulta',
        'pendiente',
        $config['medico_nombre'] ?? 'Médico Tratante'
    ]);
}
```

### Funcionalidad Mejorada

1. **Con multi_medico habilitado:**
   - Se guarda el `medico_id` de la cita original
   - Se guarda el `medico_nombre` del doctor

2. **Con multi_medico deshabilitado:**
   - Se usa el `medico_nombre` de la configuración por defecto
   - Mantiene compatibilidad con configuraciones de un solo médico

3. **Notas limpias:**
   - Se eliminó la redundante información del doctor en las notas
   - Las notas solo incluyen el ID de la cita y observaciones originales

## Beneficios
- ✅ Los turnos creados desde citas mantienen la información del médico
- ✅ Compatibilidad con configuraciones multi-médico y médico único
- ✅ Información consistente entre citas y turnos
- ✅ Mejor trazabilidad médico-paciente

## Archivos Afectados
- `turnos.php` (líneas 104-130 aprox.)

## Archivos de Prueba
- `test_agregar_turno_desde_cita.php` - Script de verificación

## Cómo Probar
1. Ir a `Citas.php`
2. Seleccionar una cita existente
3. Hacer clic en "Agregar a Turnos"
4. Verificar en `turnos.php` que el turno creado tiene la información del médico
5. Ejecutar `test_agregar_turno_desde_cita.php` para análisis detallado

## Fecha de Implementación
23 de julio de 2025
