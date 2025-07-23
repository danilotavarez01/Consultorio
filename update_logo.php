<?php
// Logo update script
require_once "config.php";

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Logo Update Process</h1>";

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            // Get the logo file
            $logo_tmp = $_FILES['logo']['tmp_name'];
            $logo_data = file_get_contents($logo_tmp);
            
            echo "<h2>1. File Upload</h2>";
            echo "<p>File received: " . htmlspecialchars($_FILES['logo']['name']) . "</p>";
            echo "<p>File size: " . $_FILES['logo']['size'] . " bytes</p>";
            echo "<p>File type: " . $_FILES['logo']['type'] . "</p>";
            
            // Update the logo in the database
            echo "<h2>2. Database Update</h2>";
            $stmt = $conn->prepare("UPDATE configuracion SET logo = :logo WHERE id = 1");
            $stmt->bindParam(':logo', $logo_data, PDO::PARAM_LOB);
            
            if ($stmt->execute()) {
                echo "<p style='color:green'>Logo updated successfully!</p>";
                echo "<p>Now checking if the logo was saved correctly...</p>";
                
                // Verify the logo was saved
                $stmt = $conn->query("SELECT logo FROM configuracion WHERE id = 1");
                $config = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($config && isset($config['logo']) && !empty($config['logo'])) {
                    echo "<p style='color:green'>Logo verified in database: " . strlen($config['logo']) . " bytes</p>";
                    
                    // Display the updated logo
                    $logo_path = 'data:image/png;base64,' . base64_encode($config['logo']);
                    echo "<h2>3. Updated Logo</h2>";
                    echo "<img src='$logo_path' style='max-width:300px; border:1px solid #ccc;' alt='Updated Logo'>";
                } else {
                    echo "<p style='color:red'>Failed to verify logo in database</p>";
                }
            } else {
                echo "<p style='color:red'>Failed to update logo in database</p>";
            }
        } else {
            echo "<p style='color:red'>No file uploaded or there was an error with the upload</p>";
            if (isset($_FILES['logo'])) {
                echo "<p>Error code: " . $_FILES['logo']['error'] . "</p>";
            }
        }
    } else {
        echo "<p style='color:red'>This script only accepts POST requests with a file upload</p>";
    }
} catch (Exception $e) {
    echo "<h3 style='color:red'>Error:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}

echo "<p><a href='debug_logo.php'>Return to logo debug page</a></p>";
echo "<p><a href='login.php'>Go to login page</a></p>";
?>
