<?php
// Simple login test script
session_start();
require_once "config.php";

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Login Test Page</h1>";

// Check connection status
echo "<h2>Database Connection</h2>";
try {
    if ($conn) {
        echo "<p style='color:green'>Database connection is working</p>";
        
        // Check for users table
        $userCount = $conn->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
        echo "<p>Found $userCount users in the database</p>";
        
        // Display a sample user (without password)
        $user = $conn->query("SELECT id, username, nombre, rol FROM usuarios LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            echo "<div style='background:#f5f5f5; padding:10px; margin:10px 0; border:1px solid #ccc;'>";
            echo "<p>Sample user:</p>";
            echo "<ul>";
            echo "<li>ID: " . $user['id'] . "</li>";
            echo "<li>Username: " . htmlspecialchars($user['username']) . "</li>";
            echo "<li>Nombre: " . htmlspecialchars($user['nombre']) . "</li>";
            echo "<li>Rol: " . htmlspecialchars($user['rol']) . "</li>";
            echo "</ul>";
            echo "</div>";
        }
    } else {
        echo "<p style='color:red'>Database connection failed!</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}

// Check if the user is already logged in
echo "<h2>Session Status</h2>";
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    echo "<p style='color:green'>Currently logged in as: " . htmlspecialchars($_SESSION["username"]) . " (Role: " . htmlspecialchars($_SESSION["rol"]) . ")</p>";
    echo "<p><a href='logout.php'>Logout</a></p>";
} else {
    echo "<p>Not currently logged in</p>";
    echo "<p>Try logging in with the form below:</p>";
    
    // Simple login form
    echo "<form method='post' action='login.php' style='background:#f5f5f5; padding:20px; max-width:400px; margin:20px 0;'>";
    echo "<div style='margin-bottom:15px;'>";
    echo "<label style='display:block; margin-bottom:5px;'>Username:</label>";
    echo "<input type='text' name='username' style='width:100%; padding:8px;'>";
    echo "</div>";
    echo "<div style='margin-bottom:15px;'>";
    echo "<label style='display:block; margin-bottom:5px;'>Password:</label>";
    echo "<input type='password' name='password' style='width:100%; padding:8px;'>";
    echo "</div>";
    echo "<div>";
    echo "<button type='submit' style='padding:8px 15px; background:#0066cc; color:white; border:none; cursor:pointer;'>Login</button>";
    echo "</div>";
    echo "</form>";
}

// Additional navigation
echo "<h2>Navigation</h2>";
echo "<p><a href='login.php'>Go to regular login page</a></p>";
echo "<p><a href='debug_logo.php'>Check logo status</a></p>";
echo "<p><a href='index.php'>Go to main page</a></p>";
?>
