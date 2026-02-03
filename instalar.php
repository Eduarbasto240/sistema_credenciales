<?php
// instalar.php - Instalador automático del sistema
echo "<h2>Instalador del Sistema de Credenciales</h2>";

// Crear carpetas necesarias
$carpetas = ['database', 'uploads', 'uploads/fotos', 'uploads/qrcodes'];

foreach ($carpetas as $carpeta) {
    if (!file_exists($carpeta)) {
        mkdir($carpeta, 0777, true);
        echo "<p>✅ Carpeta creada: <strong>$carpeta</strong></p>";
    } else {
        echo "<p>📁 Carpeta ya existe: <strong>$carpeta</strong></p>";
    }
}

// Verificar permisos
echo "<h3>Verificando permisos:</h3>";
foreach ($carpetas as $carpeta) {
    if (is_writable($carpeta)) {
        echo "<p style='color: green'>✅ $carpeta tiene permisos de escritura</p>";
    } else {
        echo "<p style='color: red'>❌ $carpeta NO tiene permisos de escritura</p>";
    }
}

// Crear base de datos SQLite
echo "<h3>Creando base de datos:</h3>";
try {
    $db = new PDO('sqlite:database/credenciales.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Tabla personas
    $sql = "CREATE TABLE IF NOT EXISTS personas (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        codigo_credencial VARCHAR(20) UNIQUE NOT NULL,
        primer_nombre VARCHAR(50) NOT NULL,
        segundo_nombre VARCHAR(50),
        primer_apellido VARCHAR(50) NOT NULL,
        segundo_apellido VARCHAR(50),
        cedula_identidad VARCHAR(20) UNIQUE NOT NULL,
        telefono VARCHAR(20) NOT NULL,
        estado VARCHAR(50) NOT NULL,
        municipio VARCHAR(50) NOT NULL,
        parroquia VARCHAR(50),
        cargo VARCHAR(100) NOT NULL,
        persona_responsable VARCHAR(100) NOT NULL,
        foto VARCHAR(255),
        qr_url VARCHAR(255),
        qr_imagen VARCHAR(255),
        fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
        activo BOOLEAN DEFAULT 1
    )";
    $db->exec($sql);
    echo "<p>✅ Tabla 'personas' creada</p>";
    
    // Tabla correlativos
    $sql = "CREATE TABLE IF NOT EXISTS correlativos_estado (
        estado VARCHAR(50) PRIMARY KEY,
        correlativo INTEGER DEFAULT 1
    )";
    $db->exec($sql);
    echo "<p>✅ Tabla 'correlativos_estado' creada</p>";
    
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 10px; margin: 20px 0;'>
            <h3 style='color: #155724;'>🎉 ¡Instalación completada!</h3>
            <p>La base de datos SQLite se creó correctamente.</p>
            <p><strong>Archivo:</strong> database/credenciales.db</p>
            <p><strong>Tamaño:</strong> " . filesize('database/credenciales.db') . " bytes</p>
          </div>";
    
} catch(PDOException $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 10px;'>
            <h3 style='color: #721c24;'>❌ Error al crear base de datos</h3>
            <p><strong>Error:</strong> " . $e->getMessage() . "</p>
            <p>Soluciones:</p>
            <ol>
                <li>Da permisos de escritura a la carpeta 'database'</li>
                <li>Ejecuta como administrador: <code>icacls \"database\" /grant Everyone:F</code></li>
                <li>Verifica que PHP tenga la extensión PDO SQLite</li>
            </ol>
          </div>";
}

// Verificar PHP
echo "<h3>Configuración PHP:</h3>";
echo "<ul>";
echo "<li>PHP Version: " . phpversion() . "</li>";
echo "<li>PDO SQLite: " . (extension_loaded('pdo_sqlite') ? '✅ Disponible' : '❌ No disponible') . "</li>";
echo "<li>GD Library: " . (extension_loaded('gd') ? '✅ Disponible (para QR)' : '❌ No disponible') . "</li>";
echo "</ul>";

// Enlace al sistema
echo "<hr>";
echo "<h3>Acceso al sistema:</h3>";
echo "<p><a href='index.php' style='font-size: 18px; background: #007bff; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none;'>🚀 Ir al Sistema de Credenciales</a></p>";

echo "<hr><small>Ejecuta este instalador solo una vez. Luego borra este archivo.</small>";
?>