<?php
require_once "config.php";

try {
    // Verificar que la conexión a base de datos esté disponible
    if (!isset($conn) || $conn === null) {
        header('HTTP/1.0 404 Not Found');
        exit;
    }
    
    // Activar el modo de errores para PDO
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Consulta para obtener el logo
    $stmt = $conn->query("SELECT logo FROM configuracion WHERE id = 1");
    if ($stmt) {
        $config = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($config && isset($config['logo']) && $config['logo'] !== null && !empty($config['logo'])) {
            $logoData = $config['logo'];
            
            // Generar ETag basado en el contenido para cache
            $etag = md5($logoData);
            
            // Verificar si el cliente ya tiene la versión actual
            if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] === $etag) {
                header('HTTP/1.1 304 Not Modified');
                header('ETag: ' . $etag);
                exit;
            }
            
            // Determinar el tipo de contenido
            $contentType = 'image/png'; // Por defecto
            
            // Verificar si es base64 o binario
            if (base64_decode($logoData, true) !== false && base64_encode(base64_decode($logoData)) === $logoData) {
                // Es base64, decodificar
                $imageContent = base64_decode($logoData);
            } else {
                // Es binario
                $imageContent = $logoData;
            }
            
            // Intentar detectar el tipo MIME real de la imagen
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $detectedType = finfo_buffer($finfo, $imageContent);
                if ($detectedType && strpos($detectedType, 'image/') === 0) {
                    $contentType = $detectedType;
                }
                finfo_close($finfo);
            }
            
            // Configurar headers para cache
            header('Content-Type: ' . $contentType);
            header('ETag: ' . $etag);
            header('Cache-Control: public, max-age=3600'); // Cache por 1 hora
            header('Content-Length: ' . strlen($imageContent));
            
            // Enviar la imagen
            echo $imageContent;
            exit;
        }
    }
} catch(PDOException $e) {
    error_log("Error al obtener logo: " . $e->getMessage());
}

// Si llegamos aquí, no hay logo o hubo un error
header('HTTP/1.0 404 Not Found');
exit;
?>
