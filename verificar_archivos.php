<?php
// verificar_archivos.php
echo "<h2>Archivos en el sistema:</h2>";
$directorio = '.';
$archivos = scandir($directorio);

echo "<ul>";
foreach ($archivos as $archivo) {
    if ($archivo != '.' && $archivo != '..') {
        $tamaño = filesize($archivo);
        $modificado = date('d/m/Y H:i:s', filemtime($archivo));
        echo "<li><strong>$archivo</strong> - $tamaño bytes - Modificado: $modificado</li>";
    }
}
echo "</ul>";
?>