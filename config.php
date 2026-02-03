<?php
// config.php - Configuración del sistema con SQLite
session_start();

// URLs y rutas
define('SITE_URL', 'http://localhost/sistema_credenciales/');
define('SITE_NAME', 'Sistema de Credenciales');
define('ROOT_PATH', __DIR__ . DIRECTORY_SEPARATOR);

// Base de datos SQLite
define('DB_PATH', ROOT_PATH . 'database' . DIRECTORY_SEPARATOR . 'credenciales.db');
define('DB_DIR', ROOT_PATH . 'database');

// Directorios de archivos
define('UPLOAD_DIR', ROOT_PATH . 'uploads');
define('FOTOS_DIR', UPLOAD_DIR . DIRECTORY_SEPARATOR . 'fotos');
define('QR_DIR', UPLOAD_DIR . DIRECTORY_SEPARATOR . 'qrcodes');

// URLs para acceder a archivos
define('FOTOS_URL', SITE_URL . 'uploads/fotos/');
define('QR_URL', SITE_URL . 'uploads/qrcodes/');

// Configuración de subida
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_TYPES', ['jpg', 'jpeg', 'png', 'gif']);
define('MAX_FOTO_WIDTH', 800);
define('MAX_FOTO_HEIGHT', 800);

// Estados de Venezuela
$ESTADOS_VENEZUELA = [
    'Amazonas', 'Anzoátegui', 'Apure', 'Aragua', 'Barinas',
    'Bolívar', 'Carabobo', 'Cojedes', 'Delta Amacuro',
    'Distrito Capital', 'Falcón', 'Guárico', 'Lara',
    'Mérida', 'Miranda', 'Monagas', 'Nueva Esparta',
    'Portuguesa', 'Sucre', 'Táchira', 'Trujillo',
    'Vargas', 'Yaracuy', 'Zulia'
];

// Crear directorios si no existen
$directorios = [DB_DIR, UPLOAD_DIR, FOTOS_DIR, QR_DIR];
foreach ($directorios as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }
}

// Habilitar errores para desarrollo
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Función para sanitizar datos
function sanitizar($dato) {
    return htmlspecialchars(trim($dato), ENT_QUOTES, 'UTF-8');
}

// Función para generar hash único
function generarHash($longitud = 10) {
    return substr(md5(uniqid(mt_rand(), true)), 0, $longitud);
}

// Función para redireccionar
function redireccionar($url, $mensaje = null) {
    if ($mensaje) {
        $_SESSION['mensaje'] = $mensaje;
    }
    header("Location: $url");
    exit();
}

// Función para mostrar mensajes
function mostrarMensaje() {
    if (isset($_SESSION['mensaje'])) {
        $mensaje = $_SESSION['mensaje'];
        unset($_SESSION['mensaje']);
        return $mensaje;
    }
    return null;
}
?>