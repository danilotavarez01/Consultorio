<?php
function getConnection() {
    global $conn;
    
    try {
        // Check if connection is still alive
        if ($conn && $conn->query('SELECT 1')) {
            return $conn;
        }
    } catch (PDOException $e) {
        // Connection is dead, we'll create a new one
    }

    try {
        // Create new connection
        $dsn = "mysql:host=" . DB_SERVER . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8";
        $options = array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
            // Add these settings to prevent timeout issues
            PDO::ATTR_TIMEOUT => 3, // 3 seconds timeout
            PDO::ATTR_PERSISTENT => true // Use persistent connections
        );
        
        $conn = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $conn;
    } catch (PDOException $e) {
        error_log("Connection failed: " . $e->getMessage());
        throw new PDOException("Error de conexión a la base de datos. Por favor, inténtelo de nuevo.");
    }
}

function executeQuery($query, $params = []) {
    $retries = 2;
    $lastError = null;
    
    while ($retries > 0) {
        try {
            $conn = getConnection();
            $stmt = $conn->prepare($query);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            $lastError = $e;
            if (strpos($e->getMessage(), 'server has gone away') !== false) {
                $retries--;
                if ($retries > 0) {
                    continue;
                }
            }
            throw $e;
        }
    }
    
    throw $lastError;
}

function obtenerCamposEspecialidad($especialidad_id) {
    global $conn;
    $query = "SELECT * FROM especialidad_campos WHERE especialidad_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $especialidad_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}
?>
