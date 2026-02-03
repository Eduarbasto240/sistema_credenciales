<?php
// credencial.php - Página individual de credencial
require_once 'config.php';
require_once 'database.php';
require_once 'funciones.php';

$manager = new CredencialManager();
$persona = null;
$codigo = $_GET['codigo'] ?? '';

// Buscar persona por código
if ($codigo) {
    $persona = $manager->buscarPersona($codigo);
}

// Si no se encuentra, mostrar error
if (!$persona) {
    http_response_code(404);
    die("
    <!DOCTYPE html>
<html>
<head>
    <title>Credencial No Encontrada - <?php echo SITE_NAME; ?></title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css'>
    <style>
        body { 
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .error-card {
            max-width: 600px;
            margin: 100px auto;
            border: none;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .error-icon {
            font-size: 5rem;
            color: #e74c3c;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body>
    <div class='container'>
        <div class='error-card'>
            <div class='card-header bg-danger text-white text-center py-4'>
                <i class='fas fa-exclamation-triangle fa-3x mb-3'></i>
                <h2 class='mb-0'>Credencial No Encontrada</h2>
            </div>
            <div class='card-body text-center p-5'>
                <div class='error-icon mb-4'>
                    <i class='fas fa-id-card'></i>
                </div>
                <h3 class='text-danger mb-3'>¡Ups! Algo salió mal</h3>
                <p class='lead mb-4'>
                    La credencial con el código <strong>" . htmlspecialchars($codigo) . "</strong> 
                    no existe en nuestro sistema o ha sido eliminada.
                </p>
                <div class='alert alert-info'>
                    <h5><i class='fas fa-lightbulb me-2'></i>¿Qué puedes hacer?</h5>
                    <ul class='mb-0 text-start'>
                        <li>Verifica que el código esté correctamente escrito</li>
                        <li>Consulta el listado completo de credenciales</li>
                        <li>Registra una nueva persona si es necesario</li>
                    </ul>
                </div>
                <div class='mt-5'>
                    <div class='btn-group' role='group'>
                        <a href='index.php' class='btn btn-primary btn-lg'>
                            <i class='fas fa-user-plus me-2'></i>Nuevo Registro
                        </a>
                        <a href='verificar.php' class='btn btn-success btn-lg'>
                            <i class='fas fa-search me-2'></i>Verificar Otra
                        </a>
                        <a href='lista.php' class='btn btn-info btn-lg'>
                            <i class='fas fa-list me-2'></i>Ver Listado
                        </a>
                    </div>
                </div>
            </div>
            <div class='card-footer text-center text-muted py-3'>
                <small><i class='fas fa-info-circle me-1'></i>Sistema de Credenciales - " . date('Y') . "</small>
            </div>
        </div>
    </div>
</body>
</html>
    ");
}

// Modo impresión
$modoImpresion = isset($_GET['print']) && $_GET['print'] == 'true';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Credencial: <?php echo $persona['codigo_credencial']; ?> - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        <?php if ($modoImpresion): ?>
        /* Estilos para impresión */
        @media print {
            @page {
                size: A4;
                margin: 0.5cm;
            }
            body { 
                margin: 0; 
                padding: 0; 
                background: white !important; 
                font-size: 12pt;
            }
            .no-print { 
                display: none !important; 
            }
            .credential-card { 
                border: 3px solid #000 !important; 
                box-shadow: none !important; 
                margin: 0 !important;
                page-break-inside: avoid;
            }
            .page-break { 
                page-break-after: always; 
            }
            .watermark { 
                opacity: 0.08 !important; 
            }
            .data-value {
                font-size: 11pt !important;
            }
        }
        <?php endif; ?>
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .credential-card {
            max-width: 850px;
            margin: 0 auto;
            border: 4px solid #2c3e50;
            border-radius: 25px;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            position: relative;
            overflow: hidden;
            box-shadow: 0 25px 50px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }
        
        .credential-header {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            color: white;
            padding: 30px;
            border-radius: 21px 21px 0 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .header-pattern {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><rect width="100" height="100" fill="none"/><path d="M0,50 L100,50 M50,0 L50,100" stroke="rgba(255,255,255,0.1)" stroke-width="2"/></svg>');
            opacity: 0.3;
        }
        
        .credential-logo {
            width: 100px;
            height: 100px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: #3498db;
            font-size: 2.5rem;
            border: 3px solid rgba(255,255,255,0.3);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
            position: relative;
            z-index: 1;
        }
        
        .credential-code {
            position: absolute;
            top: 25px;
            right: 25px;
            background: rgba(255,255,255,0.95);
            color: #2c3e50;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: bold;
            font-size: 1.2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            z-index: 1;
        }
        
        .status-badge {
            position: absolute;
            top: 25px;
            left: 25px;
            padding: 10px 25px;
            border-radius: 25px;
            font-weight: 700;
            font-size: 1rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            z-index: 1;
        }
        
        .person-photo {
            width: 220px;
            height: 280px;
            object-fit: cover;
            border: 6px solid white;
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
            border-radius: 10px;
        }
        
        .qr-display {
            width: 200px;
            height: 200px;
            border: 2px solid #dee2e6;
            padding: 15px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .data-section {
            border-left: 4px solid #3498db;
            padding-left: 20px;
            margin-bottom: 25px;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 0 10px 10px 0;
        }
        
        .data-label {
            font-weight: 700;
            color: #7f8c8d;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }
        
        .data-value {
            font-size: 1.15rem;
            color: #2c3e50;
            margin-bottom: 15px;
            font-weight: 500;
        }
        
        .watermark {
            position: absolute;
            font-size: 140px;
            opacity: 0.03;
            transform: rotate(-45deg);
            z-index: 0;
            white-space: nowrap;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            color: #2c3e50;
            font-weight: 900;
            font-family: 'Arial Black', sans-serif;
        }
        
        .valid-text {
            font-size: 0.9rem;
            color: #27ae60;
            font-weight: 600;
            background: rgba(39, 174, 96, 0.1);
            padding: 5px 15px;
            border-radius: 20px;
            display: inline-block;
        }
        
        .signature-area {
            border-top: 3px dashed #ddd;
            padding-top: 25px;
            margin-top: 30px;
            position: relative;
        }
        
        .signature-line {
            border-bottom: 1px solid #000;
            width: 250px;
            margin: 0 auto;
            padding-top: 40px;
        }
        
        .organization-seal {
            width: 120px;
            height: 120px;
            border: 2px solid #e74c3c;
            border-radius: 50%;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .photo-frame {
            position: relative;
            display: inline-block;
        }
        
        .photo-frame::after {
            content: '';
            position: absolute;
            top: -10px;
            left: -10px;
            right: -10px;
            bottom: -10px;
            border: 2px solid #3498db;
            border-radius: 15px;
            z-index: -1;
            opacity: 0.3;
        }
        
        .qr-container {
            position: relative;
        }
        
        .qr-container::before {
            content: 'ESCANEAR AQUÍ';
            position: absolute;
            top: -25px;
            left: 50%;
            transform: translateX(-50%);
            background: #3498db;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            white-space: nowrap;
        }
        
        @media (max-width: 768px) {
            .credential-card {
                border-radius: 15px;
                margin: 10px;
            }
            
            .person-photo {
                width: 180px;
                height: 230px;
            }
            
            .qr-display {
                width: 150px;
                height: 150px;
            }
            
            .watermark {
                font-size: 80px;
            }
        }
    </style>
</head>
<body class="<?php echo $modoImpresion ? 'bg-white' : 'bg-light'; ?>">
    <?php if (!$modoImpresion): ?>
    <!-- Navegación (no se imprime) -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark py-3 no-print shadow">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-id-card me-2"></i><?php echo SITE_NAME; ?>
            </a>
            <div class="navbar-nav ms-auto">
                <a href="verificar.php?codigo=<?php echo $persona['codigo_credencial']; ?>" 
                   class="btn btn-outline-light btn-sm me-2">
                    <i class="fas fa-search me-1"></i>Verificar
                </a>
                <button onclick="imprimirCredencial()" class="btn btn-success btn-sm me-2">
                    <i class="fas fa-print me-1"></i>Imprimir
                </button>
                <a href="credencial.php?codigo=<?php echo $persona['codigo_credencial']; ?>&print=true" 
                   target="_blank" class="btn btn-primary btn-sm">
                    <i class="fas fa-external-link-alt me-1"></i>Vista Impresión
                </a>
            </div>
        </div>
    </nav>
    <?php endif; ?>
    
    <!-- Credencial -->
    <div class="container py-<?php echo $modoImpresion ? '0' : '4'; ?>">
        <div class="credential-card p-<?php echo $modoImpresion ? '4' : '0'; ?>">
            <!-- Patrón de fondo del header -->
            <div class="header-pattern"></div>
            
            <!-- Marca de agua -->
            <div class="watermark">
                <?php echo substr($persona['estado'], 0, 3) . ' • ' . SITE_NAME . ' • ' . date('Y'); ?>
            </div>
            
            <!-- Encabezado -->
            <div class="credential-header">
                <div class="credential-logo">
                    <i class="fas fa-id-card"></i>
                </div>
                
                <div class="credential-code">
                    <?php echo $persona['codigo_credencial']; ?>
                </div>
                
                <div class="status-badge bg-<?php echo $persona['estado_clase']; ?>">
                    <i class="fas fa-circle me-1"></i><?php echo $persona['estado_texto']; ?>
                </div>
                
                <h1 class="mb-3">CREDENCIAL DE IDENTIFICACIÓN</h1>
                <h4 class="mb-3">Miembro del Movimiento Social</h4>
                <div class="valid-text">
                    <i class="fas fa-shield-alt me-1"></i>Documento Verificado Electrónicamente
                </div>
            </div>
            
            <!-- Cuerpo de la credencial -->
            <div class="p-5 position-relative" style="z-index: 1;">
                <div class="row">
                    <!-- Columna Izquierda: Foto y QR -->
                    <div class="col-lg-4 text-center">
                        <!-- Foto -->
                        <div class="mb-5 photo-frame">
                            <?php if (!empty($persona['foto_url'])): ?>
                            <img src="<?php echo $persona['foto_url']; ?>" 
                                 alt="Foto" class="person-photo">
                            <?php else: ?>
                            <div class="person-photo d-flex align-items-center justify-content-center bg-light">
                                <i class="fas fa-user fa-6x text-secondary"></i>
                            </div>
                            <?php endif; ?>
                            <div class="mt-3">
                                <small class="text-muted">
                                    <i class="fas fa-camera me-1"></i>Foto para identificación oficial
                                </small>
                            </div>
                        </div>
                        
                        <!-- QR -->
                        <div class="mt-5 qr-container">
                            <?php if (!empty($persona['qr_imagen_url'])): ?>
                            <img src="<?php echo $persona['qr_imagen_url']; ?>" 
                                 alt="QR" class="img-fluid qr-display">
                            <?php endif; ?>
                            <div class="mt-3">
                                <small class="text-muted">
                                    <i class="fas fa-mobile-alt me-1"></i>Escanear con cámara del teléfono
                                </small>
                            </div>
                        </div>
                        
                        <!-- Fechas -->
                        <div class="mt-5">
                            <div class="data-section">
                                <div class="data-label">Expedición</div>
                                <div class="data-value">
                                    <i class="fas fa-calendar-check me-1 text-success"></i>
                                    <?php echo date('d/m/Y', strtotime($persona['fecha_registro'])); ?>
                                </div>
                                
                                <div class="data-label">Actualización</div>
                                <div class="data-value">
                                    <i class="fas fa-sync-alt me-1 text-info"></i>
                                    <?php echo date('d/m/Y', strtotime($persona['ultima_actualizacion'])); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Columna Derecha: Datos -->
                    <div class="col-lg-8">
                        <!-- Nombre completo -->
                        <div class="mb-5">
                            <h2 class="text-primary mb-3">
                                <?php echo $persona['primer_nombre'] . ' ' . 
                                       ($persona['segundo_nombre'] ? $persona['segundo_nombre'] . ' ' : '') .
                                       $persona['primer_apellido'] . ' ' . 
                                       ($persona['segundo_apellido'] ? $persona['segundo_apellido'] : ''); ?>
                            </h2>
                            <div class="data-label">Nombre completo del titular</div>
                        </div>
                        
                        <!-- Datos principales en dos columnas -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="data-section mb-4">
                                    <div class="data-label">Cédula de Identidad</div>
                                    <div class="data-value">
                                        <i class="fas fa-id-card me-1 text-primary"></i>
                                        <?php echo $persona['cedula_identidad']; ?>
                                    </div>
                                    
                                    <div class="data-label">Teléfono</div>
                                    <div class="data-value">
                                        <i class="fas fa-phone me-1 text-success"></i>
                                        <?php echo $persona['telefono']; ?>
                                    </div>
                                    
                                    <div class="data-label">Estado</div>
                                    <div class="data-value">
                                        <i class="fas fa-map-marker-alt me-1 text-danger"></i>
                                        <?php echo $persona['estado']; ?>
                                    </div>
                                    
                                    <div class="data-label">Municipio</div>
                                    <div class="data-value">
                                        <i class="fas fa-map-marked-alt me-1 text-warning"></i>
                                        <?php echo $persona['municipio']; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="data-section mb-4">
                                    <?php if ($persona['parroquia']): ?>
                                    <div class="data-label">Parroquia</div>
                                    <div class="data-value">
                                        <i class="fas fa-map-pin me-1 text-info"></i>
                                        <?php echo $persona['parroquia']; ?>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="data-label">Cargo</div>
                                    <div class="data-value">
                                        <i class="fas fa-briefcase me-1 text-secondary"></i>
                                        <?php echo $persona['cargo']; ?>
                                    </div>
                                    
                                    <div class="data-label">Responsable de Registro</div>
                                    <div class="data-value">
                                        <i class="fas fa-user-check me-1 text-success"></i>
                                        <?php echo $persona['persona_responsable']; ?>
                                    </div>
                                    
                                    <div class="data-label">ID del Registro</div>
                                    <div class="data-value">
                                        <i class="fas fa-hashtag me-1 text-muted"></i>
                                        <?php echo $persona['id']; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Información de seguridad -->
                        <div class="mt-4">
                            <div class="alert alert-warning">
                                <h5><i class="fas fa-exclamation-triangle me-2"></i>Características de Seguridad:</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <ul class="mb-2">
                                            <li>Código único e irrepetible</li>
                                            <li>QR verificable en tiempo real</li>
                                            <li>Foto del titular</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <ul class="mb-2">
                                            <li>Datos encriptados</li>
                                            <li>Marcas de agua de seguridad</li>
                                            <li>Firma digital del sistema</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Firma y sello -->
                        <div class="signature-area">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="text-center">
                                        <div class="signature-line"></div>
                                        <div class="data-label mt-2">Firma del Titular</div>
                                        <small class="text-muted">Autoriza el uso de sus datos</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-center">
                                        <div class="organization-seal">
                                            <div class="text-center">
                                                <i class="fas fa-stamp fa-3x text-danger"></i>
                                                <div class="mt-2">
                                                    <small class="text-muted d-block">Sello oficial</small>
                                                    <small class="text-muted"><?php echo SITE_NAME; ?></small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="data-label mt-3">Sello de la Organización</div>
                                        <small class="text-muted">Valida autenticidad del documento</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Pie de página -->
            <div class="border-top p-4 text-center bg-light">
                <div class="row">
                    <div class="col-md-8 text-start">
                        <small class="text-muted">
                            <i class="fas fa-globe-americas me-1"></i>
                            <strong>URL de verificación:</strong> 
                            <a href="<?php echo $persona['qr_url']; ?>" target="_blank" class="text-decoration-none">
                                <?php echo $persona['qr_url']; ?>
                            </a>
                        </small>
                    </div>
                    <div class="col-md-4 text-end">
                        <small class="text-muted">
                            <i class="fas fa-phone me-1"></i>Soporte: Sistema de Credenciales
                            <br>
                            <i class="fas fa-envelope me-1"></i>Documento generado: <?php echo date('d/m/Y H:i'); ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if (!$modoImpresion): ?>
        <!-- Acciones adicionales (no se imprimen) -->
        <div class="text-center mt-4 no-print">
            <div class="btn-group" role="group">
                <a href="verificar.php" class="btn btn-outline-primary">
                    <i class="fas fa-search me-1"></i>Verificar Otra Credencial
                </a>
                <a href="lista.php" class="btn btn-outline-secondary">
                    <i class="fas fa-list me-1"></i>Ver Todas las Credenciales
                </a>
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-user-plus me-1"></i>Registrar Nueva Persona
                </a>
                <button onclick="descargarCredencial()" class="btn btn-success">
                    <i class="fas fa-download me-1"></i>Descargar PDF
                </button>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Scripts -->
    <?php if (!$modoImpresion): ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php endif; ?>
    
    <script>
        <?php if ($modoImpresion): ?>
        // Auto-imprimir en modo impresión
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
        
        // Volver después de imprimir
        window.onafterprint = function() {
            setTimeout(function() {
                window.location.href = 'credencial.php?codigo=<?php echo $persona['codigo_credencial']; ?>';
            }, 1000);
        };
        <?php endif; ?>
        
        // Imprimir credencial
        function imprimirCredencial() {
            window.open('credencial.php?codigo=<?php echo $persona['codigo_credencial']; ?>&print=true', '_blank');
        }
        
        // Descargar como PDF (simulado)
        function descargarCredencial() {
            alert('Para guardar como PDF:\n1. Haga clic en "Imprimir"\n2. En destino seleccione "Guardar como PDF"\n3. Guarde el archivo');
            imprimirCredencial();
        }
        
        // Compartir credencial
        function compartirCredencial() {
            const texto = `Credencial de <?php echo $persona['primer_nombre'] . ' ' . $persona['primer_apellido']; ?> - Código: <?php echo $persona['codigo_credencial']; ?>`;
            const url = '<?php echo $persona['qr_url']; ?>';
            
            if (navigator.share) {
                navigator.share({
                    title: 'Credencial de Identificación',
                    text: texto,
                    url: url
                });
            } else {
                // Fallback: copiar al portapapeles
                navigator.clipboard.writeText(`${texto}\n${url}`).then(() => {
                    alert('Enlace copiado al portapapeles');
                });
            }
        }
        
        // Configurar para impresión
        <?php if (!$modoImpresion): ?>
        document.addEventListener('DOMContentLoaded', function() {
            // Añadir estilos de impresión
            const printStyles = `
                @media print {
                    body * {
                        visibility: hidden;
                    }
                    .credential-card, .credential-card * {
                        visibility: visible;
                    }
                    .credential-card {
                        position: absolute;
                        left: 0;
                        top: 0;
                        width: 100%;
                        border: 3px solid #000 !important;
                        box-shadow: none !important;
                        margin: 0 !important;
                    }
                    .no-print {
                        display: none !important;
                    }
                    .watermark {
                        opacity: 0.08 !important;
                    }
                }
            `;
            
            const styleSheet = document.createElement("style");
            styleSheet.type = "text/css";
            styleSheet.innerText = printStyles;
            document.head.appendChild(styleSheet);
        });
        <?php endif; ?>
    </script>
</body>
</html>