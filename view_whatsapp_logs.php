<?php
// Script para visualizar los registros de WhatsApp
header('Content-Type: text/html; charset=UTF-8');

$logFile = __DIR__ . '/whatsapp_debug.log';
$maxLines = isset($_GET['lines']) ? (int)$_GET['lines'] : 50;
$download = isset($_GET['download']) && $_GET['download'] == '1';

// Si se solicita descargar
if ($download) {
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="whatsapp_debug.log"');
    readfile($logFile);
    exit;
}

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Registros de WhatsApp</title>
    <link rel='stylesheet' href='assets/css/bootstrap.min.css'>
    <style>
        .log-entry {
            border-bottom: 1px solid #eee;
            padding: 5px 0;
        }
        .timestamp {
            color: #666;
            font-style: italic;
        }
        .error {
            color: #dc3545;
        }
        .success {
            color: #28a745;
        }
    </style>
</head>
<body>
    <div class='container mt-3'>
        <h1>Visualizador de Registros de WhatsApp</h1>
        <div class='mb-3'>
            <a href='view_whatsapp_logs.php?lines=20' class='btn btn-sm btn-outline-secondary'>Últimas 20 líneas</a>
            <a href='view_whatsapp_logs.php?lines=50' class='btn btn-sm btn-outline-secondary'>Últimas 50 líneas</a>
            <a href='view_whatsapp_logs.php?lines=100' class='btn btn-sm btn-outline-secondary'>Últimas 100 líneas</a>
            <a href='view_whatsapp_logs.php?lines=1000' class='btn btn-sm btn-outline-secondary'>Últimas 1000 líneas</a>
            <a href='view_whatsapp_logs.php?download=1' class='btn btn-sm btn-primary'>Descargar completo</a>
            <a href='test_whatsapp.html' class='btn btn-sm btn-success'>Ir al probador de WhatsApp</a>
        </div>";

if (!file_exists($logFile)) {
    echo "<div class='alert alert-warning'>El archivo de registro no existe todavía.</div>";
} else {
    // Obtener las últimas líneas del archivo de registro
    $lines = file($logFile);
    $totalLines = count($lines);
    
    if ($totalLines > 0) {
        $startLine = max(0, $totalLines - $maxLines);
        $lines = array_slice($lines, $startLine);
        
        echo "<div class='card mb-3'>
                <div class='card-header'>
                    Mostrando últimas $maxLines líneas de un total de $totalLines
                </div>
                <div class='card-body'>
                    <div class='log-container'>";
        
        foreach ($lines as $line) {
            $class = '';
            if (strpos($line, 'error') !== false || strpos($line, 'Error') !== false || strpos($line, 'Exception') !== false) {
                $class = 'error';
            } else if (strpos($line, 'success') !== false || strpos($line, 'Success') !== false) {
                $class = 'success';
            }
            
            // Extraer timestamp si está presente
            $timestamp = '';
            if (preg_match('/\[(.*?)\]/', $line, $matches)) {
                $timestamp = $matches[1];
                $line = str_replace($matches[0], '', $line);
            }
            
            echo "<div class='log-entry $class'>";
            if ($timestamp) {
                echo "<span class='timestamp'>[$timestamp]</span> ";
            }
            echo htmlspecialchars($line) . "</div>";
        }
        
        echo "    </div>
                </div>
            </div>";
    } else {
        echo "<div class='alert alert-info'>El archivo de registro está vacío.</div>";
    }
}

echo "<div class='mt-3 mb-3'>
        <a href='test_whatsapp_cli.php' class='btn btn-info'>Ejecutar prueba de servidor</a>
        <a href='Citas.php' class='btn btn-secondary'>Volver a Citas</a>
      </div>
    </div>
</body>
</html>";
?>

