<?php
session_start();
require_once "permissions.php";

// Verificar si el usuario está logueado y tiene permisos
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !hasPermission('manage_diseases')) {
    echo json_encode(['success' => false, 'message' => 'No tiene permisos para realizar esta acción']);
    exit;
}

require_once "config.php";

// Verificar que se recibieron los datos necesarios
if(empty($_POST['nombre'])) {
    echo json_encode(['success' => false, 'message' => 'El nombre de la enfermedad es obligatorio']);
    exit;
}

try {
    // Insertar la nueva enfermedad
    $sql = "INSERT INTO enfermedades (nombre, descripcion) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$_POST['nombre'], $_POST['descripcion'] ?? '']);
    
    // Obtener el ID de la enfermedad recién creada
    $id = $conn->lastInsertId();
    
    // Devolver respuesta exitosa
    echo json_encode([
        'success' => true, 
        'id' => $id, 
        'nombre' => $_POST['nombre']
    ]);
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>