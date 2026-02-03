<?php
// debug.php - Para diagnosticar problemas
echo "<h2>Debug del Sistema</h2>";

// 1. Verificar archivos
echo "<h3>1. Archivos existentes:</h3>";
$archivos = ['config.php', 'database.php', 'funciones.php', 'index.php'];
foreach ($archivos as $archivo) {
    echo file_exists($archivo) ? "✅ $archivo<br>" : "❌ $archivo (FALTANTE)<br>";
}

// 2. Verificar clases
echo "<h3>2. Clases cargadas:</h3>";
if (class_exists('PDO')) {
    echo "✅ PDO está disponible<br>";
} else {
    echo "❌ PDO NO está disponible<br>";
}

if (class_exists('Database')) {
    echo "✅ Clase Database existe<br>";
} else {
    echo "❌ Clase Database NO existe<br>";
}

// 3. Verificar método
echo "<h3>3. Método obtenerUno:</h3>";
$db = new PDO('sqlite:database/credenciales.db');
if (method_exists($db, 'obtenerUno')) {
    echo "✅ Método obtenerUno existe en PDO<br>";
} else {
    echo "❌ Método obtenerUno NO existe en PDO<br>";
    echo "PDO solo tiene estos métodos:<br>";
    echo "<pre>";
    print_r(get_class_methods($db));
    echo "</pre>";
}

// 4. Verificar estructura de database.php
echo "<h3>4. Contenido de database.php:</h3>";
echo "<pre>";
$content = file_get_contents('database.php');
echo htmlspecialchars(substr($content, 0, 1000)) . "...";
echo "</pre>";
?>