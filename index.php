<?php
// index.php - VERSIÓN SIMPLIFICADA
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Cargar configuración básica
define('SITE_URL', 'http://localhost/sistema_credenciales/');
define('SITE_NAME', 'Sistema de Credenciales');

// Directorios
define('ROOT_PATH', __DIR__ . '/');
define('DB_PATH', ROOT_PATH . 'database/credenciales.db');
define('FOTOS_URL', SITE_URL . 'uploads/fotos/');
define('QR_URL', SITE_URL . 'uploads/qrcodes/');

// Estados de Venezuela
$ESTADOS_VENEZUELA = [
    'Amazonas', 'Anzoátegui', 'Apure', 'Aragua', 'Barinas',
    'Bolívar', 'Carabobo', 'Cojedes', 'Delta Amacuro',
    'Distrito Capital', 'Falcón', 'Guárico', 'Lara',
    'Mérida', 'Miranda', 'Monagas', 'Nueva Esparta',
    'Portuguesa', 'Sucre', 'Táchira', 'Trujillo',
    'Vargas', 'Yaracuy', 'Zulia'
];

// Función simple para sanitizar
function sanitizar($texto) {
    return htmlspecialchars(trim($texto), ENT_QUOTES, 'UTF-8');
}

// Verificar si se envió el formulario
$mensaje = '';
$registroExitoso = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Conectar a SQLite
        $db = new PDO('sqlite:' . DB_PATH);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Generar código (simplificado)
        $estado = $_POST['estado'] ?? '';
        $prefijo = strtoupper(substr($estado, 0, 3));
        
        // Obtener correlativo
        $stmt = $db->prepare("SELECT correlativo FROM correlativos_estado WHERE estado = ?");
        $stmt->execute([$estado]);
        $result = $stmt->fetch();
        
        if ($result) {
            $correlativo = $result['correlativo'] + 1;
            $stmt = $db->prepare("UPDATE correlativos_estado SET correlativo = ? WHERE estado = ?");
            $stmt->execute([$correlativo, $estado]);
        } else {
            $correlativo = 1;
            $stmt = $db->prepare("INSERT INTO correlativos_estado (estado, correlativo) VALUES (?, ?)");
            $stmt->execute([$estado, $correlativo]);
        }
        
        $codigo = $prefijo . '-' . str_pad($correlativo, 4, '0', STR_PAD_LEFT);
        
        // Procesar foto (simplificado)
        $fotoNombre = null;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
            $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $fotoNombre = 'foto_' . uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['foto']['tmp_name'], 'uploads/fotos/' . $fotoNombre);
        }
        
        // Insertar persona
        $sql = "INSERT INTO personas (
            codigo_credencial, primer_nombre, primer_apellido, 
            cedula_identidad, telefono, estado, municipio, 
            cargo, persona_responsable, foto, qr_url
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            $codigo,
            sanitizar($_POST['primer_nombre']),
            sanitizar($_POST['primer_apellido']),
            sanitizar($_POST['cedula']),
            sanitizar($_POST['telefono']),
            sanitizar($_POST['estado']),
            sanitizar($_POST['municipio']),
            sanitizar($_POST['cargo']),
            sanitizar($_POST['persona_responsable']),
            $fotoNombre,
            SITE_URL . 'verificar.php?codigo=' . $codigo
        ]);
        
        $registroExitoso = [
            'codigo' => $codigo,
            'qr_url' => SITE_URL . 'verificar.php?codigo=' . $codigo,
            'foto_url' => $fotoNombre ? FOTOS_URL . $fotoNombre : null
        ];
        
        $mensaje = "✅ Registro exitoso! Código: $codigo";
        
    } catch(Exception $e) {
        $mensaje = "❌ Error: " . $e->getMessage();
    }
}

// Obtener estadísticas simples
$totalPersonas = 0;
try {
    $db = new PDO('sqlite:' . DB_PATH);
    $stmt = $db->query("SELECT COUNT(*) as total FROM personas");
    $result = $stmt->fetch();
    $totalPersonas = $result['total'] ?? 0;
} catch(Exception $e) {
    // Ignorar error
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; padding-top: 20px; }
        .card { margin-bottom: 20px; }
        .required::after { content: " *"; color: red; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0">Sistema de Credenciales - Registro Simplificado</h3>
            </div>
            <div class="card-body">
                
                <?php if ($mensaje): ?>
                <div class="alert alert-info"><?php echo $mensaje; ?></div>
                <?php endif; ?>
                
                <form method="POST" enctype="multipart/form-data">
                    <!-- Campos básicos -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label required">Primer Nombre</label>
                                <input type="text" name="primer_nombre" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label required">Primer Apellido</label>
                                <input type="text" name="primer_apellido" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label required">Cédula</label>
                                <input type="text" name="cedula" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label required">Teléfono</label>
                                <input type="tel" name="telefono" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label required">Estado</label>
                                <select name="estado" class="form-select" required>
                                    <option value="">Seleccione...</option>
                                    <?php foreach ($ESTADOS_VENEZUELA as $estado): ?>
                                    <option value="<?php echo $estado; ?>"><?php echo $estado; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label required">Municipio</label>
                                <input type="text" name="municipio" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label required">Cargo</label>
                                <input type="text" name="cargo" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label required">Persona Responsable</label>
                                <input type="text" name="persona_responsable" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Foto -->
                    <div class="mb-3">
                        <label class="form-label">Foto (Opcional)</label>
                        <input type="file" name="foto" class="form-control" accept="image/*">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Registrar Persona</button>
                </form>
                
                <!-- Resultado -->
                <?php if ($registroExitoso): ?>
                <div class="card mt-4 border-success">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">✅ ¡Registro Exitoso!</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Código:</strong> <?php echo $registroExitoso['codigo']; ?></p>
                        <p><strong>URL de verificación:</strong> 
                            <a href="<?php echo $registroExitoso['qr_url']; ?>" target="_blank">
                                <?php echo $registroExitoso['qr_url']; ?>
                            </a>
                        </p>
                        <?php if ($registroExitoso['foto_url']): ?>
                        <p><strong>Foto:</strong> 
                            <a href="<?php echo $registroExitoso['foto_url']; ?>" target="_blank">Ver</a>
                        </p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Estadísticas -->
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">📊 Estadísticas</h5>
            </div>
            <div class="card-body">
                <p>Total de personas registradas: <strong><?php echo $totalPersonas; ?></strong></p>
                <div class="d-flex gap-2">
                    <a href="verificar.php" class="btn btn-outline-primary">Verificar Credencial</a>
                    <a href="lista.php" class="btn btn-outline-secondary">Ver Listado</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>