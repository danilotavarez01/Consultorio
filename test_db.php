<?php
try {
    $pdo = new PDO(
        "mysql:host=localhost;port=3306;dbname=consultorio;charset=utf8",
        "root",
        "820416Dts",
        array(
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_PERSISTENT => true,  // Better for MySQL 5.1
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
            PDO::MYSQL_ATTR_DIRECT_QUERY => false
        )
    );
    echo "✅ Conexión exitosa";
} catch (PDOException $e) {
    // Added more detailed error information
    echo "❌ Error en la conexión: " . $e->getMessage();
    echo "<br>Verify:";
    echo "<br>1. MySQL service is running (check Services.msc)";
    echo "<br>2. Port 3306 is open in Windows Firewall";
    echo "<br>3. User N1l0 has remote connection privileges";
}
?>
