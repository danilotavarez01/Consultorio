<?php
session_start();
require_once "permissions.php";

echo "===== HTML generado por sidebar.php =====\n";
ob_start();
include 'sidebar.php';
$sidebar_html = ob_get_clean();
echo htmlentities($sidebar_html);
?>
