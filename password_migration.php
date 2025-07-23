<?php
require_once "config.php";

$stmt = $conn->query("SELECT id, password FROM usuarios");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    if (!password_needs_rehash($row['password'], PASSWORD_DEFAULT)) {
        continue;
    }
    
    $newHash = password_hash($row['password'], PASSWORD_DEFAULT);
    $conn->prepare("UPDATE usuarios SET password = ? WHERE id = ?")
         ->execute([$newHash, $row['id']]);
}
echo "Password migration completed!";
?>