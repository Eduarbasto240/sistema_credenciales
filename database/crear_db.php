<?php
// crear_db.php - Crea la base de datos SQLite inicial
// Ejecutar una sola vez: http://localhost/sistema_credenciales/crear_db.php

echo "<h2>Creando base de datos SQLite...</h2>";

// Directorios necesarios
$directorios = [
    'database',
    'uploads',
    'uploads/fotos',
    'uploads/qrcodes'
];

foreach ($directorios as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
        echo "<p>✅ Carpeta creada: $dir</p>";
    } else {
        echo "<p>📁 Carpeta ya existe: $dir</p>";
    }
}

// Ruta de la base de datos
$db_file = 'database/credenciales.db';

// Crear archivo de base de datos si no existe
if (!file_exists($db_file)) {
    try {
        // Crear conexión a SQLite
        $db = new PDO('sqlite:' . $db_file);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Crear tabla: personas
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
            activo BOOLEAN DEFAULT 1,
            ultima_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP
        )";
        $db->exec($sql);
        echo "<p>✅ Tabla 'personas' creada</p>";
        
        // Crear tabla: correlativos_estado
        $sql = "CREATE TABLE IF NOT EXISTS correlativos_estado (
            estado VARCHAR(50) PRIMARY KEY,
            correlativo INTEGER DEFAULT 1,
            ultima_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP
        )";
        $db->exec($sql);
        echo "<p>✅ Tabla 'correlativos_estado' creada</p>";
        
        // Insertar estados venezolanos
        $estados = [
            'Amazonas', 'Anzoátegui', 'Apure', 'Aragua', 'Barinas',
            'Bolívar', 'Carabobo', 'Cojedes', 'Delta Amacuro',
            'Distrito Capital', 'Falcón', 'Guárico', 'Lara',
            'Mérida', 'Miranda', 'Monagas', 'Nueva Esparta',
            'Portuguesa', 'Sucre', 'Táchira', 'Trujillo',
            'Vargas', 'Yaracuy', 'Zulia'
        ];
        
        foreach ($estados as $estado) {
            $stmt = $db->prepare("INSERT OR IGNORE INTO correlativos_estado (estado) VALUES (?)");
            $stmt->execute([$estado]);
        }
        echo "<p>✅ Estados de Venezuela insertados: " . count($estados) . "</p>";
        
        // Crear índices
        $db->exec("CREATE INDEX IF NOT EXISTS idx_personas_codigo ON personas(codigo_credencial)");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_personas_cedula ON personas(cedula_identidad)");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_personas_estado ON personas(estado)");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_personas_activo ON personas(activo)");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_personas_fecha ON personas(fecha_registro)");
        echo "<p>✅ Índices creados para mejor rendimiento</p>";
        
        echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0;'>
                <h3 style='color: #155724;'>✅ Base de datos creada exitosamente</h3>
                <p><strong>Archivo:</strong> $db_file</p>
                <p><strong>Tamaño:</strong> " . filesize($db_file) . " bytes</p>
                <p><strong>Fecha creación:</strong> " . date('d/m/Y H:i:s') . "</p>
              </div>";
        
        // Verificar permisos
        echo "<h3>Verificando permisos:</h3>";
        if (is_writable('database')) {
            echo "<p style='color: green'>✅ Carpeta 'database' tiene permisos de escritura</p>";
        } else {
            echo "<p style='color: red'>❌ Carpeta 'database' NO tiene permisos de escritura</p>";
            echo "<p>Ejecuta este comando como Administrador:</p>";
            echo "<pre>icacls \"C:\\xampp\\htdocs\\sistema_credenciales\\database\" /grant Everyone:F</pre>";
        }
        
        if (is_writable('uploads')) {
            echo "<p style='color: green'>✅ Carpeta 'uploads' tiene permisos de escritura</p>";
        } else {
            echo "<p style='color: red'>❌ Carpeta 'uploads' NO tiene permisos de escritura</p>";
        }
        
        echo "<hr>";
        echo "<h3>📊 Información de la base de datos:</h3>";
        
        // Contar tablas
        $stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name");
        $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<p><strong>Tablas creadas:</strong> " . implode(', ', $tablas) . "</p>";
        
        foreach ($tablas as $tabla) {
            $stmt = $db->query("SELECT COUNT(*) as count FROM $tabla");
            $count = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "<p>• $tabla: " . $count['count'] . " registros</p>";
        }
        
        echo "<hr>";
        echo "<div style='background: #cce5ff; padding: 15px; border-radius: 10px;'>
                <h4>🎉 ¡Base de datos lista!</h4>
                <p>Ahora puedes acceder al sistema:</p>
                <p><a href='index.php' style='font-size: 18px; color: white; background: #007bff; padding: 10px 20px; border-radius: 5px; text-decoration: none;'>Ir al Sistema de Credenciales</a></p>
              </div>";
        
    } catch(PDOException $e) {
        echo "<div style='background: #f8d7da; padding: 20px; border-radius: 10px;'>
                <h3 style='color: #721c24;'>❌ Error al crear base de datos</h3>
                <p><strong>Error:</strong> " . $e->getMessage() . "</p>
                <p>Posibles soluciones:</p>
                <ol>
                    <li>Crear manualmente la carpeta 'database'</li>
                    <li>Dar permisos de escritura a la carpeta</li>
                    <li>Verificar que PHP tenga PDO_SQLite habilitado</li>
                </ol>
              </div>";
    }
} else {
    echo "<div style='background: #fff3cd; padding: 20px; border-radius: 10px;'>
            <h3 style='color: #856404;'>📁 Base de datos ya existe</h3>
            <p><strong>Archivo:</strong> $db_file</p>
            <p><strong>Tamaño:</strong> " . filesize($db_file) . " bytes</p>
            <p><strong>Modificación:</strong> " . date('d/m/Y H:i:s', filemtime($db_file)) . "</p>
            <p><a href='index.php'>Ir al sistema</a></p>
          </div>";
}

// Verificar configuración PHP
echo "<hr><h3>🔧 Configuración PHP:</h3>";
echo "<ul>";
echo "<li>PHP Version: " . phpversion() . "</li>";
echo "<li>PDO SQLite: " . (extension_loaded('pdo_sqlite') ? '✅ Disponible' : '❌ No disponible') . "</li>";
echo "<li>GD Library: " . (extension_loaded('gd') ? '✅ Disponible' : '❌ No disponible') . "</li>";
echo "<li>Upload Max Filesize: " . ini_get('upload_max_filesize') . "</li>";
echo "<li>Post Max Size: " . ini_get('post_max_size') . "</li>";
echo "</ul>";

echo "<hr>";
echo "<p><a href='index.php' style='font-size: 16px;'>← Volver al sistema</a></p>";
?>