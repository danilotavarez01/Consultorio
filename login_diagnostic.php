<?php
// This file helps diagnose login issues
require_once "config.php";

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Login System Diagnostic</h1>";

// Function to show status
function showStatus($test, $success, $message = "") {
    echo "<div style='margin: 10px 0; padding: 10px; background: " . ($success ? "#d4edda" : "#f8d7da") . 
         "; border: 1px solid " . ($success ? "#c3e6cb" : "#f5c6cb") . "; border-radius: 5px;'>";
    echo "<strong>" . ($success ? "✓ PASS" : "✗ FAIL") . ":</strong> $test";
    if ($message) {
        echo "<br><span style='font-size: 0.9em;'>" . $message . "</span>";
    }
    echo "</div>";
}

// 1. Check PHP version
$phpVersion = phpversion();
showStatus(
    "PHP Version", 
    version_compare($phpVersion, '7.0.0', '>='), 
    "Current version: $phpVersion (7.0+ recommended)"
);

// 2. Check PDO and MySQL extension
showStatus(
    "PDO Extension", 
    extension_loaded('PDO'), 
    extension_loaded('PDO') ? "PDO is available" : "PDO extension not loaded"
);

showStatus(
    "PDO MySQL Driver", 
    extension_loaded('pdo_mysql'), 
    extension_loaded('pdo_mysql') ? "MySQL driver is available" : "PDO MySQL driver not loaded"
);

// 3. Check database connection
try {
    if ($conn) {
        $conn->query("SELECT 1");
        showStatus(
            "Database Connection", 
            true, 
            "Successfully connected to MySQL server"
        );
    } else {
        showStatus(
            "Database Connection", 
            false, 
            "Connection variable is null"
        );
    }
} catch (PDOException $e) {
    showStatus(
        "Database Connection", 
        false, 
        "Error: " . $e->getMessage()
    );
}

// 4. Check if database exists
try {
    $databases = $conn->query("SHOW DATABASES LIKE 'consultorio'")->fetchAll();
    showStatus(
        "Database 'consultorio'", 
        count($databases) > 0, 
        count($databases) > 0 ? "Database exists" : "Database does not exist"
    );
} catch (Exception $e) {
    showStatus(
        "Database Check", 
        false, 
        "Error: " . $e->getMessage()
    );
}

// 5. Check if usuarios table exists
try {
    $tables = $conn->query("SHOW TABLES LIKE 'usuarios'")->fetchAll();
    showStatus(
        "Table 'usuarios'", 
        count($tables) > 0, 
        count($tables) > 0 ? "Table exists" : "Table does not exist"
    );
} catch (Exception $e) {
    showStatus(
        "Table Check", 
        false, 
        "Error: " . $e->getMessage()
    );
}

// 6. Check if there are users
try {
    if (count($tables) > 0) {
        $users = $conn->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
        showStatus(
            "User Records", 
            $users > 0, 
            "Found $users user(s)"
        );
    }
} catch (Exception $e) {
    showStatus(
        "User Records Check", 
        false, 
        "Error: " . $e->getMessage()
    );
}

// 7. Check password hashing
echo "<h2>Password Hashing Test</h2>";
$testPassword = "password123";
$hashedPassword = password_hash($testPassword, PASSWORD_DEFAULT);
$verifyResult = password_verify($testPassword, $hashedPassword);

showStatus(
    "Password Hashing Functions", 
    $verifyResult, 
    $verifyResult ? "Password hashing and verification are working correctly" : "Password verification failed"
);

// 8. Test user login
echo "<h2>Manual Login Test</h2>";
echo "<p>Use this form to test login credentials:</p>";
echo "<form method='post' action='' style='margin: 20px 0; padding: 15px; background: #f8f9fa; border: 1px solid #ddd; border-radius: 5px;'>";
echo "<div style='margin-bottom: 15px;'>";
echo "<label style='display: block; margin-bottom: 5px;'>Username:</label>";
echo "<input type='text' name='test_username' style='padding: 8px; width: 100%;' required>";
echo "</div>";
echo "<div style='margin-bottom: 15px;'>";
echo "<label style='display: block; margin-bottom: 5px;'>Password:</label>";
echo "<input type='password' name='test_password' style='padding: 8px; width: 100%;' required>";
echo "</div>";
echo "<button type='submit' name='test_login' style='padding: 8px 15px; background: #0066cc; color: white; border: none; cursor: pointer;'>Test Login</button>";
echo "</form>";

// Process test login
if (isset($_POST['test_login'])) {
    $testUsername = $_POST['test_username'];
    $testPassword = $_POST['test_password'];
    
    try {
        $stmt = $conn->prepare("SELECT id, username, password, nombre, rol FROM usuarios WHERE username = ?");
        $stmt->execute([$testUsername]);
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $passwordVerified = password_verify($testPassword, $user['password']);
            
            if ($passwordVerified) {
                echo "<div style='padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px;'>";
                echo "<strong>Login Successful!</strong><br>";
                echo "Username: " . htmlspecialchars($user['username']) . "<br>";
                echo "Name: " . htmlspecialchars($user['nombre']) . "<br>";
                echo "Role: " . htmlspecialchars($user['rol']) . "<br>";
                echo "</div>";
            } else {
                echo "<div style='padding: 10px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px;'>";
                echo "<strong>Login Failed:</strong> Incorrect password<br>";
                echo "Username found, but password doesn't match";
                echo "</div>";
            }
        } else {
            echo "<div style='padding: 10px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px;'>";
            echo "<strong>Login Failed:</strong> User not found<br>";
            echo "No user with username '" . htmlspecialchars($testUsername) . "' exists";
            echo "</div>";
        }
    } catch (Exception $e) {
        echo "<div style='padding: 10px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px;'>";
        echo "<strong>Error:</strong> " . $e->getMessage();
        echo "</div>";
    }
}

// Links to other resources
echo "<h2>Additional Resources</h2>";
echo "<ul>";
echo "<li><a href='login.php'>Regular Login Page</a></li>";
echo "<li><a href='login_simple.php'>Simple Login Page</a></li>";
echo "<li><a href='debug_logo.php'>Logo Debugging Page</a></li>";
echo "</ul>";
?>
