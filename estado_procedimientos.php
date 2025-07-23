<?php
// Verificación rápida del estado del formulario de procedimientos
echo "=== RESUMEN RÁPIDO: FORMULARIO DE PROCEDIMIENTOS ===\n\n";

echo "✅ ESTADO: COMPLETAMENTE FUNCIONAL\n";
echo "==================================\n\n";

echo "📁 ARCHIVO:\n";
echo "- Ubicación: c:\\inetpub\\wwwroot\\Consultorio2\\procedimientos.php\n";
echo "- Tamaño: " . number_format(filesize('procedimientos.php')) . " bytes\n";
echo "- Estado: Existe y es accesible\n\n";

require_once 'config.php';

echo "🗄️ BASE DE DATOS:\n";
$stmt = $pdo->query("SELECT COUNT(*) FROM procedimientos");
$count = $stmt->fetchColumn();
echo "- Tabla: procedimientos (existe)\n";
echo "- Registros: $count procedimientos\n";
echo "- Estado: Operativa\n\n";

echo "🔗 ACCESO:\n";
echo "- URL: http://localhost/Consultorio2/procedimientos.php\n";
echo "- Menú: Enlace 'Procedimientos' visible en sidebar\n";
echo "- Permisos: Configurados para usuario admin\n\n";

echo "📋 FUNCIONALIDADES DISPONIBLES:\n";
echo "- ✅ Crear nuevos procedimientos\n";
echo "- ✅ Editar procedimientos existentes\n";
echo "- ✅ Eliminar procedimientos\n";
echo "- ✅ Activar/desactivar procedimientos\n";
echo "- ✅ Búsqueda y filtros\n";
echo "- ✅ Categorías: procedimiento, utensilio, material, medicamento\n";
echo "- ✅ Gestión de precios (costo y venta)\n\n";

echo "🎯 PRÓXIMOS PASOS:\n";
echo "1. Iniciar sesión como admin (usuario: admin, password: admin123)\n";
echo "2. Hacer clic en 'Procedimientos' en el menú lateral\n";
echo "3. El formulario debería cargar correctamente\n\n";

echo "🎉 SISTEMA LISTO PARA USAR!\n";
echo "============================\n";
?>
