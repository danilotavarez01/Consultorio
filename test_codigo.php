<?php
require_once 'config.php';

$_GET['action'] = 'get_next_code';
$_GET['categoria'] = 'procedimiento';

if (isset($_GET['action']) && $_GET['action'] === 'get_next_code') {
    $categoria = $_GET['categoria'] ?? 'procedimiento';
    
    $prefijos = [
        'procedimiento' => 'PROC',
        'utensilio' => 'UTEN',
        'material' => 'MAT',
        'medicamento' => 'MED'
    ];
    
    $prefijo = $prefijos[$categoria] ?? 'PROC';
    
    try {
        // Buscar el último código usado para esta categoría
        $stmt = $conn->prepare("SELECT codigo FROM procedimientos WHERE codigo LIKE ? ORDER BY codigo DESC LIMIT 1");
        $stmt->execute([$prefijo . '%']);
        $ultimo_codigo = $stmt->fetchColumn();
        
        if ($ultimo_codigo) {
            // Extraer el número del código y incrementar
            $numero = intval(substr($ultimo_codigo, strlen($prefijo)));
            $nuevo_numero = $numero + 1;
        } else {
            $nuevo_numero = 1;
        }
        
        // Generar código con padding de 3 dígitos
        $nuevo_codigo = $prefijo . str_pad($nuevo_numero, 3, '0', STR_PAD_LEFT);
        
        echo "Último código encontrado: " . ($ultimo_codigo ?: 'ninguno') . "\n";
        echo "Nuevo código generado: " . $nuevo_codigo . "\n";
        
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage() . "\n";
        echo "Código por defecto: " . $prefijo . '001' . "\n";
    }
}

// Probar diferentes categorías
echo "\n--- Probando diferentes categorías ---\n";

$categorias = ['procedimiento', 'utensilio', 'material', 'medicamento'];

foreach ($categorias as $cat) {
    $_GET['categoria'] = $cat;
    $prefijos = [
        'procedimiento' => 'PROC',
        'utensilio' => 'UTEN',
        'material' => 'MAT',
        'medicamento' => 'MED'
    ];
    
    $prefijo = $prefijos[$cat] ?? 'PROC';
    
    try {
        $stmt = $conn->prepare("SELECT codigo FROM procedimientos WHERE codigo LIKE ? ORDER BY codigo DESC LIMIT 1");
        $stmt->execute([$prefijo . '%']);
        $ultimo_codigo = $stmt->fetchColumn();
        
        if ($ultimo_codigo) {
            $numero = intval(substr($ultimo_codigo, strlen($prefijo)));
            $nuevo_numero = $numero + 1;
        } else {
            $nuevo_numero = 1;
        }
        
        $nuevo_codigo = $prefijo . str_pad($nuevo_numero, 3, '0', STR_PAD_LEFT);
        
        echo "Categoría: $cat -> Código: $nuevo_codigo\n";
        
    } catch (PDOException $e) {
        echo "Categoría: $cat -> Error: " . $e->getMessage() . "\n";
    }
}
?>
