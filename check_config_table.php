<?php
require_once "config.php";

try {
    // First check if we can connect to the database
    echo "Checking database connection...\n";
    if ($conn) {
        echo "Database connection successful.\n\n";
    }

    // Check if the configuracion table exists
    $tables = $conn->query("SHOW TABLES LIKE 'configuracion'")->fetchAll();
    if (empty($tables)) {
        echo "Error: Table 'configuracion' does not exist.\n";
        exit(1);
    }

    echo "Checking configuration table structure:\n";
    echo "=====================================\n";
    
    $stmt = $conn->query("SHOW FULL COLUMNS FROM configuracion");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        printf("%-20s %-20s %-10s %s\n", 
            $row['Field'],
            $row['Type'],
            $row['Null'],
            $row['Default'] ?? 'NULL'
        );
    }

} catch(PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
    exit(1);
} catch(Exception $e) {
    echo "General Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
