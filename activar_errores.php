<?php
// activar_errores.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "Errores activados. Ahora prueba el sistema:<br>";
echo "<a href='index.php'>Ir a index.php</a>";
?>