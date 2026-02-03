<?php
// database.php - Versión COMPLETA y FUNCIONAL
require_once 'config.php';

class Database {
    private $connection;
    
    public function __construct() {
        try {
            // Verificar si la carpeta database existe
            if (!file_exists(DB_DIR)) {
                mkdir(DB_DIR, 0777, true);
            }
            
            // Conexión directa a SQLite
            $this->connection = new PDO('sqlite:' . DB_PATH);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->connection->exec('PRAGMA encoding = "UTF-8"');
            
            // Crear tablas si no existen
            $this->crearTablas();
            
            echo "<!-- Database conectado correctamente -->";
            
        } catch(PDOException $e) {
            $mensaje_error = "
            <div style='background: #f8d7da; color: #721c24; padding: 20px; border-radius: 10px; margin: 20px; border: 2px solid #f5c6cb;'>
                <h3 style='margin-top: 0;'>⚠️ Error de Base de Datos</h3>
                <p><strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
                <p><strong>Archivo:</strong> " . DB_PATH . "</p>
                <hr>
                <h4>Soluciones:</h4>
                <ol>
                    <li>Crea manualmente la carpeta: <code>database</code></li>
                    <li>Asegúrate que PHP tiene permisos de escritura</li>
                    <li>Verifica que la extensión PDO SQLite esté activada</li>
                </ol>
                <p>Ejecuta como administrador: <code>icacls \"database\" /grant Everyone:F</code></p>
            </div>
            ";
            die($mensaje_error);
        }
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    private function crearTablas() {
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
            activo BOOLEAN DEFAULT 1,
            ultima_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP
        )";
        $this->connection->exec($sql);
        
        // Tabla correlativos
        $sql = "CREATE TABLE IF NOT EXISTS correlativos_estado (
            estado VARCHAR(50) PRIMARY KEY,
            correlativo INTEGER DEFAULT 1,
            ultima_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP
        )";
        $this->connection->exec($sql);
        
        // Insertar estados iniciales
        $this->insertarEstadosIniciales();
        
        // Crear índices
        $this->connection->exec("CREATE INDEX IF NOT EXISTS idx_personas_codigo ON personas(codigo_credencial)");
        $this->connection->exec("CREATE INDEX IF NOT EXISTS idx_personas_cedula ON personas(cedula_identidad)");
        $this->connection->exec("CREATE INDEX IF NOT EXISTS idx_personas_estado ON personas(estado)");
    }
    
    private function insertarEstadosIniciales() {
        $estados = [
            'Amazonas', 'Anzoátegui', 'Apure', 'Aragua', 'Barinas',
            'Bolívar', 'Carabobo', 'Cojedes', 'Delta Amacuro',
            'Distrito Capital', 'Falcón', 'Guárico', 'Lara',
            'Mérida', 'Miranda', 'Monagas', 'Nueva Esparta',
            'Portuguesa', 'Sucre', 'Táchira', 'Trujillo',
            'Vargas', 'Yaracuy', 'Zulia'
        ];
        
        foreach ($estados as $estado) {
            $stmt = $this->connection->prepare("INSERT OR IGNORE INTO correlativos_estado (estado) VALUES (?)");
            $stmt->execute([$estado]);
        }
    }
}

// Función auxiliar para debug
function debugDatabase() {
    echo "<!-- Debug Database: Clase Database existe -->";
    return true;
}
?>