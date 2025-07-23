<?php
// Debug script to check logo retrieval
require_once "config.php";

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Logo Database Debug</h1>";

try {
    // Check database connection
    echo "<h2>1. Database Connection</h2>";
    if ($conn) {
        echo "<p style='color:green'>Database connection successful</p>";
    } else {
        echo "<p style='color:red'>Database connection failed</p>";
    }
    
    // Check if configuracion table exists
    echo "<h2>2. Checking 'configuracion' Table</h2>";
    $tables = $conn->query("SHOW TABLES LIKE 'configuracion'")->fetchAll();
    if (count($tables) > 0) {
        echo "<p style='color:green'>Table 'configuracion' exists</p>";
    } else {
        echo "<p style='color:red'>Table 'configuracion' does NOT exist</p>";
        exit;
    }
    
    // Check table structure
    echo "<h2>3. Table Structure</h2>";
    $columns = $conn->query("SHOW COLUMNS FROM configuracion")->fetchAll(PDO::FETCH_ASSOC);
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    $hasLogoColumn = false;
    foreach ($columns as $column) {
        echo "<tr>";
        foreach ($column as $key => $value) {
            echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
        }
        echo "</tr>";
        if ($column['Field'] === 'logo') {
            $hasLogoColumn = true;
        }
    }
    echo "</table>";
    
    if (!$hasLogoColumn) {
        echo "<p style='color:red'>The 'logo' column is missing!</p>";
        exit;
    } else {
        echo "<p style='color:green'>The 'logo' column exists</p>";
    }
    
    // Check if any data exists in the table
    echo "<h2>4. Data Check</h2>";
    $count = $conn->query("SELECT COUNT(*) FROM configuracion")->fetchColumn();
    echo "<p>Number of rows in configuracion table: $count</p>";
    
    if ($count === 0) {
        echo "<p style='color:red'>No data in configuracion table!</p>";
        exit;
    }
    
    // Try to retrieve logo data
    echo "<h2>5. Logo Data Check</h2>";
    $stmt = $conn->query("SELECT id, nombre_consultorio, logo FROM configuracion WHERE id = 1");
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<p>Configuration record:</p>";
    echo "<ul>";
    echo "<li>ID: " . ($config['id'] ?? 'No ID') . "</li>";
    echo "<li>Nombre consultorio: " . htmlspecialchars($config['nombre_consultorio'] ?? 'No name') . "</li>";
    echo "<li>Logo size: " . (isset($config['logo']) ? strlen($config['logo']) . " bytes" : "No logo data") . "</li>";
    echo "</ul>";
    
    if (isset($config['logo']) && !empty($config['logo'])) {
        echo "<h2>6. Logo Display Test</h2>";
        $logo_path = 'data:image/png;base64,' . base64_encode($config['logo']);
        echo "<p>Logo data converted to base64. Here is the result:</p>";
        echo "<img src='$logo_path' style='max-width:300px; border:1px solid #ccc;' alt='Logo'>";
    } else {
        echo "<p style='color:red'>No logo data found in the database!</p>";
    }
    
    // Script to add a test logo to the database
    echo "<h2>7. Update Logo Tool</h2>";
    echo "<p>Use this form to upload a new logo to the database:</p>";
    echo "<form action='update_logo.php' method='post' enctype='multipart/form-data'>";
    echo "<input type='file' name='logo' accept='image/*'>";
    echo "<button type='submit'>Upload Logo</button>";
    echo "</form>";
    
} catch (Exception $e) {
    echo "<h3 style='color:red'>Error:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>
