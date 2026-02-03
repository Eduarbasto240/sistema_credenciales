<?php
// verificar.php - Página para verificar credenciales
require_once 'config.php';
require_once 'database.php';
require_once 'funciones.php';

$manager = new CredencialManager();
$persona = null;
$codigo = $_GET['codigo'] ?? '';

// Buscar persona si se proporciona código
if ($codigo) {
    $persona = $manager->buscarPersona($codigo);
}

// Si no se encuentra por código, verificar si es una URL directa
if (!$persona && isset($_SERVER['REQUEST_URI'])) {
    $uri = $_SERVER['REQUEST_URI'];
    $parts = explode('/', $uri);
    $lastPart = end($parts);
    
    if ($lastPart && $lastPart !== 'verificar.php') {
        $persona = $manager->buscarPersona($lastPart);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar Credencial - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .verification-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .search-box {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 15px;
            padding: 30px;
            border: 2px dashed #dee2e6;
        }
        
        .input-group-lg .input-group-text {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            border: none;
            color: white;
            font-weight: 600;
        }
        
        .result-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
        }
        
        .result-card:hover {
            transform: translateY(-5px);
        }
        
        .status-badge {
            font-size: 0.9rem;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 600;
        }
        
        .person-photo-large {
            width: 220px;
            height: 280px;
            object-fit: cover;
            border: 5px solid white;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            border-radius: 10px;
        }
        
        .qr-verification {
            width: 200px;
            height: 200px;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 15px;
            background: white;
        }
        
        .data-item {
            padding: 12px 0;
            border-bottom: 1px solid #f1f1f1;
        }
        
        .data-label {
            font-weight: 600;
            color: #7f8c8d;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .data-value {
            color: #2c3e50;
            font-size: 1.1rem;
        }
        
        .mobile-instructions {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            border-left: 4px solid #2196f3;
        }
        
        .not-found {
            background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%);
            border-left: 4px solid #f44336;
        }
        
        .example-box {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="verification-card p-4 p-md-5">
                    <!-- Header -->
                    <div class="text-center mb-5">
                        <div class="mb-3">
                            <i class="fas fa-search fa-3x text-primary"></i>
                        </div>
                        <h1 class="text-primary fw-bold">Verificar Credencial</h1>
                        <p class="text-muted fs-5">Sistema de verificación de autenticidad de credenciales</p>
                    </div>
                    
                    <!-- Buscador -->
                    <div class="search-box mb-5">
                        <form method="GET" class="row g-3">
                            <div class="col-lg-9">
                                <div class="input-group input-group-lg shadow-sm">
                                    <span class="input-group-text">
                                        <i class="fas fa-qrcode"></i>
                                    </span>
                                    <input type="text" name="codigo" class="form-control" 
                                           placeholder="Ingrese código (MIR-0001), cédula (V-12345678) o URL" 
                                           value="<?php echo htmlspecialchars($codigo); ?>"
                                           required
                                           autofocus>
                                </div>
                                
                                <!-- Ejemplos -->
                                <div class="example-box mt-3">
                                    <p class="mb-1"><strong>Ejemplos:</strong></p>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <small><i class="fas fa-barcode me-1"></i>Código: <strong>MIR-0001</strong></small>
                                        </div>
                                        <div class="col-md-4">
                                            <small><i class="fas fa-id-card me-1"></i>Cédula: <strong>V-12345678</strong></small>
                                        </div>
                                        <div class="col-md-4">
                                            <small><i class="fas fa-link me-1"></i>URL: <strong><?php echo SITE_URL; ?>abc123</strong></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-lg-3">
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg shadow">
                                        <i class="fas fa-search me-2"></i>Verificar Ahora
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Resultado de Verificación -->
                    <?php if ($persona): ?>
                    <div class="result-card border-success mb-5">
                        <div class="card-header bg-success text-white py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="mb-0">
                                        <i class="fas fa-check-circle me-2"></i>Credencial Verificada
                                    </h4>
                                    <small>Verificación exitosa - Documento auténtico</small>
                                </div>
                                <span class="status-badge bg-white text-success">
                                    <?php echo $persona['estado_texto']; ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="card-body p-4">
                            <div class="row">
                                <!-- Columna Izquierda: Foto y QR -->
                                <div class="col-lg-4 text-center">
                                    <!-- Foto -->
                                    <div class="mb-4">
                                        <?php if (!empty($persona['foto_url'])): ?>
                                        <img src="<?php echo $persona['foto_url']; ?>" 
                                             alt="Foto" class="person-photo-large mb-3">
                                        <p class="text-muted small">
                                            <i class="fas fa-camera me-1"></i>Foto de identificación
                                        </p>
                                        <?php else: ?>
                                        <div class="person-photo-large d-flex align-items-center justify-content-center bg-light mb-3">
                                            <i class="fas fa-user fa-5x text-secondary"></i>
                                        </div>
                                        <p class="text-muted small">
                                            <i class="fas fa-user me-1"></i>Sin foto registrada
                                        </p>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- QR -->
                                    <?php if (!empty($persona['qr_imagen_url'])): ?>
                                    <div class="mt-4">
                                        <img src="<?php echo $persona['qr_imagen_url']; ?>" 
                                             alt="QR" class="img-fluid qr-verification mb-3">
                                        <p class="text-muted small">
                                            <i class="fas fa-qrcode me-1"></i>Escaneado el: <?php echo date('d/m/Y H:i'); ?>
                                        </p>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Columna Derecha: Datos -->
                                <div class="col-lg-8">
                                    <!-- Nombre y código -->
                                    <div class="mb-4">
                                        <h2 class="text-primary">
                                            <?php echo $persona['primer_nombre'] . ' ' . 
                                                   ($persona['segundo_nombre'] ? $persona['segundo_nombre'] . ' ' : '') .
                                                   $persona['primer_apellido'] . ' ' . 
                                                   ($persona['segundo_apellido'] ? $persona['segundo_apellido'] : ''); ?>
                                        </h2>
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-primary fs-6 me-3">
                                                <?php echo $persona['codigo_credencial']; ?>
                                            </span>
                                            <small class="text-muted">
                                                <i class="fas fa-calendar me-1"></i>
                                                Registrado: <?php echo date('d/m/Y', strtotime($persona['fecha_registro'])); ?>
                                            </small>
                                        </div>
                                    </div>
                                    
                                    <!-- Datos en grid -->
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="data-item">
                                                <div class="data-label">Cédula de Identidad</div>
                                                <div class="data-value"><?php echo $persona['cedula_identidad']; ?></div>
                                            </div>
                                            
                                            <div class="data-item">
                                                <div class="data-label">Teléfono</div>
                                                <div class="data-value">
                                                    <i class="fas fa-phone me-1 text-success"></i>
                                                    <?php echo $persona['telefono']; ?>
                                                </div>
                                            </div>
                                            
                                            <div class="data-item">
                                                <div class="data-label">Estado</div>
                                                <div class="data-value">
                                                    <i class="fas fa-map-marker-alt me-1 text-danger"></i>
                                                    <?php echo $persona['estado']; ?>
                                                </div>
                                            </div>
                                            
                                            <div class="data-item">
                                                <div class="data-label">Municipio</div>
                                                <div class="data-value"><?php echo $persona['municipio']; ?></div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <?php if ($persona['parroquia']): ?>
                                            <div class="data-item">
                                                <div class="data-label">Parroquia</div>
                                                <div class="data-value"><?php echo $persona['parroquia']; ?></div>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <div class="data-item">
                                                <div class="data-label">Cargo</div>
                                                <div class="data-value">
                                                    <i class="fas fa-briefcase me-1 text-warning"></i>
                                                    <?php echo $persona['cargo']; ?>
                                                </div>
                                            </div>
                                            
                                            <div class="data-item">
                                                <div class="data-label">Responsable de Registro</div>
                                                <div class="data-value">
                                                    <i class="fas fa-user-check me-1 text-info"></i>
                                                    <?php echo $persona['persona_responsable']; ?>
                                                </div>
                                            </div>
                                            
                                            <div class="data-item">
                                                <div class="data-label">Última Actualización</div>
                                                <div class="data-value">
                                                    <i class="fas fa-sync-alt me-1 text-secondary"></i>
                                                    <?php echo date('d/m/Y H:i', strtotime($persona['ultima_actualizacion'])); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Información de verificación -->
                                    <div class="alert alert-success mt-4">
                                        <div class="d-flex">
                                            <div class="me-3">
                                                <i class="fas fa-shield-alt fa-2x"></i>
                                            </div>
                                            <div>
                                                <h5 class="alert-heading">✅ Verificación Exitosa</h5>
                                                <p class="mb-0">Esta credencial ha sido verificada como auténtica y está registrada en nuestro sistema.</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Acciones -->
                                    <div class="mt-4 pt-4 border-top">
                                        <div class="d-flex flex-wrap gap-3">
                                            <a href="credencial.php?codigo=<?php echo $persona['codigo_credencial']; ?>" 
                                               class="btn btn-primary">
                                                <i class="fas fa-id-card me-1"></i> Ver Credencial Completa
                                            </a>
                                            <a href="credencial.php?codigo=<?php echo $persona['codigo_credencial']; ?>&print=true" 
                                               target="_blank" class="btn btn-success">
                                                <i class="fas fa-print me-1"></i> Imprimir Credencial
                                            </a>
                                            <a href="index.php" class="btn btn-outline-primary">
                                                <i class="fas fa-plus me-1"></i> Nuevo Registro
                                            </a>
                                            <a href="lista.php" class="btn btn-outline-secondary">
                                                <i class="fas fa-list me-1"></i> Ver Todos
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php elseif ($codigo): ?>
                    <!-- No encontrado -->
                    <div class="result-card border-danger">
                        <div class="card-header bg-danger text-white py-3">
                            <h4 class="mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>Credencial No Encontrada
                            </h4>
                        </div>
                        <div class="card-body p-4 not-found">
                            <div class="text-center py-4">
                                <i class="fas fa-times-circle fa-4x text-danger mb-3"></i>
                                <h3 class="text-danger">¡Ups! No encontramos esa credencial</h3>
                                <p class="lead">
                                    El código "<strong><?php echo htmlspecialchars($codigo); ?></strong>" 
                                    no existe en nuestro sistema.
                                </p>
                                
                                <div class="row mt-4">
                                    <div class="col-md-4">
                                        <div class="card h-100 border-light">
                                            <div class="card-body text-center">
                                                <i class="fas fa-search fa-2x text-primary mb-3"></i>
                                                <h6>Verifique el código</h6>
                                                <p class="small text-muted">Asegúrese de escribir correctamente el código o cédula</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card h-100 border-light">
                                            <div class="card-body text-center">
                                                <i class="fas fa-user-plus fa-2x text-success mb-3"></i>
                                                <h6>Registre nueva persona</h6>
                                                <p class="small text-muted">Si es nueva, regístrela en el sistema</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card h-100 border-light">
                                            <div class="card-body text-center">
                                                <i class="fas fa-list fa-2x text-info mb-3"></i>
                                                <h6>Consulte el listado</h6>
                                                <p class="small text-muted">Revise todas las credenciales registradas</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-4">
                                    <a href="index.php" class="btn btn-primary btn-lg">
                                        <i class="fas fa-user-plus me-1"></i> Registrar Nueva Persona
                                    </a>
                                    <a href="lista.php" class="btn btn-outline-primary btn-lg ms-2">
                                        <i class="fas fa-list me-1"></i> Ver Listado Completo
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Instrucciones para móviles -->
                    <div class="mt-5 mobile-instructions p-4 rounded">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h5>
                                    <i class="fas fa-mobile-alt me-2 text-primary"></i>
                                    ¿Cómo verificar desde un teléfono móvil?
                                </h5>
                                <ol class="mb-0">
                                    <li><strong>Abrir la cámara:</strong> Use la aplicación de cámara de su teléfono</li>
                                    <li><strong>Enfocar el QR:</strong> Apunte al código QR de la credencial</li>
                                    <li><strong>Tocar enlace:</strong> Presione el enlace que aparece en pantalla</li>
                                    <li><strong>Verificar:</strong> Será redirigido automáticamente a esta página</li>
                                </ol>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="position-relative">
                                    <i class="fas fa-qrcode fa-5x text-primary"></i>
                                    <div class="position-absolute top-50 start-50 translate-middle">
                                        <i class="fas fa-mobile-alt fa-2x text-white"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Volver al inicio -->
                    <div class="text-center mt-4">
                        <a href="index.php" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left me-1"></i> Volver al Registro
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-enfocar el campo de búsqueda
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.querySelector('input[name="codigo"]');
            if (searchInput && !searchInput.value) {
                searchInput.focus();
            }
        });
        
        // Detectar si es móvil y mostrar instrucciones específicas
        if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
            document.addEventListener('DOMContentLoaded', function() {
                // Resaltar instrucciones móviles
                const mobileInstructions = document.querySelector('.mobile-instructions');
                if (mobileInstructions) {
                    mobileInstructions.classList.add('border', 'border-primary');
                    mobileInstructions.scrollIntoView({ behavior: 'smooth' });
                }
            });
        }
        
        // Copiar código al portapapeles
        function copiarCodigo(codigo) {
            navigator.clipboard.writeText(codigo).then(function() {
                alert('Código copiado al portapapeles: ' + codigo);
            }).catch(function(err) {
                console.error('Error al copiar: ', err);
            });
        }
        
        // Compartir por WhatsApp
        function compartirWhatsApp(codigo, nombre) {
            const texto = `Verifica esta credencial:\n\nNombre: ${nombre}\nCódigo: ${codigo}\n\n${window.location.href}?codigo=${codigo}`;
            const url = `https://wa.me/?text=${encodeURIComponent(texto)}`;
            window.open(url, '_blank');
        }
    </script>
</body>
</html>