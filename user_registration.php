// When creating/updating users:
$hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);
// Use $hashed_password in your INSERT/UPDATE statements