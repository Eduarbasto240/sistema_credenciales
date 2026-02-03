<?php
// lista.php - Listado de todas las credenciales
require_once 'config.php';
require_once 'database.php';
require_once 'funciones.php';

$manager = new CredencialManager();

// Filtros
$filtros = [];
if (!empty($_GET['estado'])) {
    $filtros['estado'] = $_GET['estado'];
}
if (!empty($_GET['busqueda'])) {
    $filtros['busqueda'] = $_GET['busqueda'];
}

// Obtener personas
$personas = $manager->obtenerPersonas($filtros);
$estadisticas = $manager->obtenerEstadisticas();

// Exportar a CSV
if (isset($_GET['exportar']) && $_GET['exportar'] == 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=credenciales_' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    // Encabezados en español
    fputcsv($output, [
        'Código', 'Cédula', 'Nombre', 'Apellido', 'Teléfono', 
        'Estado', 'Municipio', 'Parroquia', 'Cargo', 'Estado', 'Fecha Registro', 'Responsable'
    ], ';');
    
    foreach ($personas as $p) {
        fputcsv($output, [
            $p['codigo_credencial'],
            $p['cedula_identidad'],
            $p['primer_nombre'] . ' ' . ($p['segundo_nombre'] ?: ''),
            $p['primer_apellido'] . ' ' . ($p['segundo_apellido'] ?: ''),
            $p['telefono'],
            $p['estado'],
            $p['municipio'],
            $p['parroquia'] ?: '',
            $p['cargo'],
            $p['activo'] ? 'Activa' : 'Inactiva',
            $p['fecha_registro'],
            $p['persona_responsable']
        ], ';');
    }
    
    fclose($output);
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Credenciales - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap5.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --success-color: #27ae60;
            --danger-color: #e74c3c;
            --warning-color: #f39c12;
        }
        
        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--secondary-color) 0%, var(--primary-color) 100%);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }
        
        .table th {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-bottom: 3px solid var(--primary-color);
            font-weight: 700;
            color: var(--secondary-color);
            padding: 15px;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
        }
        
        .table td {
            padding: 12px 15px;
            vertical-align: middle;
            border-bottom: 1px solid #eee;
        }
        
        .table tbody tr {
            transition: all 0.2s ease;
        }
        
        .table tbody tr:hover {
            background: linear-gradient(135deg, rgba(52, 152, 219, 0.05) 0%, rgba(41, 128, 185, 0.05) 100%);
            transform: translateX(5px);
        }
        
        .badge-estado {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            min-width: 80px;
            text-align: center;
        }
        
        .foto-miniatura {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .foto-miniatura:hover {
            transform: scale(1.2);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }
        
        .filter-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            border-left: 5px solid var(--primary-color);
        }
        
        .export-btn {
            background: linear-gradient(135deg, var(--success-color) 0%, #219653 100%);
            border: none;
            color: white;
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .export-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 14px rgba(39, 174, 96, 0.3);
        }
        
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            border-top: 4px solid var(--primary-color);
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }
        
        .stats-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin: 10px 0;
        }
        
        .stats-label {
            color: #7f8c8d;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .action-buttons .btn {
            padding: 5px 10px;
            border-radius: 8px;
            font-size: 0.85rem;
        }
        
        .dataTables_wrapper {
            padding: 0;
        }
        
        .dataTables_length select,
        .dataTables_filter input {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 8px 15px;
        }
        
        .dataTables_info {
            color: #7f8c8d;
            font-weight: 500;
        }
        
        .pagination .page-item.active .page-link {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .pagination .page-link {
            color: var(--secondary-color);
            border: 2px solid #e9ecef;
            margin: 0 3px;
            border-radius: 8px;
            font-weight: 500;
        }
        
        .pagination .page-link:hover {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        @media (max-width: 768px) {
            .table-responsive {
                border: none;
            }
            
            .stats-card {
                margin-bottom: 15px;
            }
            
            .foto-miniatura {
                width: 40px;
                height: 40px;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-id-card me-2"></i><?php echo SITE_NAME; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-user-plus me-1"></i>Registro
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="verificar.php">
                            <i class="fas fa-search me-1"></i>Verificar
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="lista.php">
                            <i class="fas fa-list me-1"></i>Listado
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <!-- Título y estadísticas -->
        <div class="row mb-4">
            <div class="col-md-8">
                <h1 class="text-primary fw-bold">
                    <i class="fas fa-list-alt me-2"></i>Listado de Credenciales
                </h1>
                <p class="text-muted fs-5">
                    Sistema de gestión y consulta de credenciales registradas
                </p>
            </div>
            <div class="col-md-4 text-end">
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="index.php" class="btn btn-primary">
                        <i class="fas fa-user-plus me-1"></i>Nuevo Registro
                    </a>
                    <a href="lista.php?exportar=csv" class="btn btn-success">
                        <i class="fas fa-file-export me-1"></i>Exportar CSV
                    </a>
                </div>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="row mb-4">
            <div class="col-md-3 col-6 mb-3">
                <div class="stats-card">
                    <div class="stats-number"><?php echo $estadisticas['total']; ?></div>
                    <div class="stats-label">Total Registrados</div>
                    <small class="text-muted">Personas en el sistema</small>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="stats-card">
                    <div class="stats-number"><?php echo $estadisticas['activas']; ?></div>
                    <div class="stats-label">Activas</div>
                    <small class="text-muted">Credenciales vigentes</small>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="stats-card">
                    <div class="stats-number"><?php echo count($estadisticas['por_estado']); ?></div>
                    <div class="stats-label">Estados</div>
                    <small class="text-muted">Representación territorial</small>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="stats-card">
                    <div class="stats-number">
                        <?php echo $estadisticas['ultimo_registro'] 
                            ? date('d/m', strtotime($estadisticas['ultimo_registro'])) 
                            : '--'; ?>
                    </div>
                    <div class="stats-label">Último</div>
                    <small class="text-muted">Registro más reciente</small>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="filter-card">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">
                        <i class="fas fa-filter me-1"></i>Filtrar por Estado
                    </label>
                    <select name="estado" class="form-select" onchange="this.form.submit()">
                        <option value="">Todos los estados</option>
                        <?php 
                        global $ESTADOS_VENEZUELA;
                        foreach ($ESTADOS_VENEZUELA as $estado): 
                        ?>
                        <option value="<?php echo $estado; ?>" 
                            <?php echo (!empty($_GET['estado']) && $_GET['estado'] == $estado) ? 'selected' : ''; ?>>
                            <?php echo $estado; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label fw-bold">
                        <i class="fas fa-search me-1"></i>Búsqueda Avanzada
                    </label>
                    <div class="input-group">
                        <input type="text" name="busqueda" class="form-control" 
                               placeholder="Buscar por código, cédula, nombre, teléfono, cargo..." 
                               value="<?php echo htmlspecialchars($_GET['busqueda'] ?? ''); ?>">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">
                        <i class="fas fa-cog me-1"></i>Acciones
                    </label>
                    <div class="d-grid gap-2">
                        <a href="lista.php" class="btn btn-outline-secondary">
                            <i class="fas fa-undo me-1"></i>Limpiar Filtros
                        </a>
                    </div>
                </div>
            </form>
            
            <!-- Resultados del filtro -->
            <?php if (!empty($_GET['estado']) || !empty($_GET['busqueda'])): ?>
            <div class="mt-3">
                <div class="alert alert-info p-2">
                    <small>
                        <i class="fas fa-info-circle me-1"></i>
                        <strong>Filtros aplicados:</strong>
                        <?php if (!empty($_GET['estado'])): ?>
                        <span class="badge bg-primary me-2">Estado: <?php echo $_GET['estado']; ?></span>
                        <?php endif; ?>
                        <?php if (!empty($_GET['busqueda'])): ?>
                        <span class="badge bg-secondary">Búsqueda: "<?php echo htmlspecialchars($_GET['busqueda']); ?>"</span>
                        <?php endif; ?>
                        <span class="badge bg-success ms-2">Resultados: <?php echo count($personas); ?></span>
                    </small>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Tabla de credenciales -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-table me-2"></i>Credenciales Registradas
                    </h5>
                    <span class="badge bg-light text-primary fs-6">
                        <?php echo count($personas); ?> registros
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tablaCredenciales" class="table table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th width="60">Foto</th>
                                <th width="120">Código</th>
                                <th>Nombre</th>
                                <th width="130">Cédula</th>
                                <th width="120">Teléfono</th>
                                <th width="150">Ubicación</th>
                                <th width="150">Cargo</th>
                                <th width="110">Fecha</th>
                                <th width="100">Estado</th>
                                <th width="150" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($personas as $p): ?>
                            <tr>
                                <!-- Foto -->
                                <td>
                                    <?php if (!empty($p['foto_url'])): ?>
                                    <img src="<?php echo $p['foto_url']; ?>" 
                                         alt="Foto" class="foto-miniatura"
                                         title="<?php echo $p['primer_nombre']; ?>"
                                         data-bs-toggle="tooltip"
                                         data-bs-placement="top">
                                    <?php else: ?>
                                    <div class="foto-miniatura d-flex align-items-center justify-content-center bg-light"
                                         data-bs-toggle="tooltip"
                                         data-bs-placement="top"
                                         title="Sin foto">
                                        <i class="fas fa-user text-secondary"></i>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                
                                <!-- Código -->
                                <td>
                                    <strong class="text-primary"><?php echo $p['codigo_credencial']; ?></strong>
                                </td>
                                
                                <!-- Nombre -->
                                <td>
                                    <div class="fw-bold"><?php echo $p['primer_nombre'] . ' ' . $p['primer_apellido']; ?></div>
                                    <?php if ($p['segundo_nombre'] || $p['segundo_apellido']): ?>
                                    <small class="text-muted">
                                        <?php echo ($p['segundo_nombre'] ? $p['segundo_nombre'] . ' ' : '') . 
                                               ($p['segundo_apellido'] ? $p['segundo_apellido'] : ''); ?>
                                    </small>
                                    <?php endif; ?>
                                </td>
                                
                                <!-- Cédula -->
                                <td>
                                    <code class="bg-light p-1 rounded"><?php echo $p['cedula_identidad']; ?></code>
                                </td>
                                
                                <!-- Teléfono -->
                                <td>
                                    <span class="d-block">
                                        <i class="fas fa-phone text-success me-1"></i>
                                        <?php echo $p['telefono']; ?>
                                    </span>
                                </td>
                                
                                <!-- Ubicación -->
                                <td>
                                    <div class="fw-bold"><?php echo $p['estado']; ?></div>
                                    <small class="text-muted"><?php echo $p['municipio']; ?></small>
                                    <?php if ($p['parroquia']): ?>
                                    <br><small class="text-muted"><?php echo $p['parroquia']; ?></small>
                                    <?php endif; ?>
                                </td>
                                
                                <!-- Cargo -->
                                <td>
                                    <span class="badge bg-info text-dark">
                                        <?php echo $p['cargo']; ?>
                                    </span>
                                </td>
                                
                                <!-- Fecha -->
                                <td>
                                    <div class="text-nowrap">
                                        <?php echo date('d/m/Y', strtotime($p['fecha_registro'])); ?>
                                        <br>
                                        <small class="text-muted"><?php echo date('H:i', strtotime($p['fecha_registro'])); ?></small>
                                    </div>
                                </td>
                                
                                <!-- Estado -->
                                <td>
                                    <span class="badge bg-<?php echo $p['estado_clase']; ?> badge-estado">
                                        <i class="fas fa-circle me-1"></i>
                                        <?php echo $p['estado_texto']; ?>
                                    </span>
                                </td>
                                
                                <!-- Acciones -->
                                <td>
                                    <div class="action-buttons d-flex justify-content-center gap-1">
                                        <a href="credencial.php?codigo=<?php echo $p['codigo_credencial']; ?>" 
                                           class="btn btn-outline-primary btn-sm"
                                           data-bs-toggle="tooltip"
                                           data-bs-placement="top"
                                           title="Ver credencial completa">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="verificar.php?codigo=<?php echo $p['codigo_credencial']; ?>" 
                                           class="btn btn-outline-success btn-sm"
                                           data-bs-toggle="tooltip"
                                           data-bs-placement="top"
                                           title="Verificar autenticidad">
                                            <i class="fas fa-search"></i>
                                        </a>
                                        <a href="credencial.php?codigo=<?php echo $p['codigo_credencial']; ?>&print=true" 
                                           target="_blank"
                                           class="btn btn-outline-info btn-sm"
                                           data-bs-toggle="tooltip"
                                           data-bs-placement="top"
                                           title="Imprimir credencial">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Distribución por estado -->
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Distribución por Estado</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($estadisticas['por_estado'] as $estad): 
                        $porcentaje = $estadisticas['total'] > 0 ? round(($estad['cantidad'] / $estadisticas['total']) * 100, 1) : 0;
                    ?>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <h6 class="card-title text-truncate" title="<?php echo $estad['estado']; ?>">
                                    <?php echo $estad['estado']; ?>
                                </h6>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="fs-4 fw-bold text-primary"><?php echo $estad['cantidad']; ?></div>
                                    <div class="text-muted"><?php echo $porcentaje; ?>%</div>
                                </div>
                                <div class="progress mt-2" style="height: 8px;">
                                    <div class="progress-bar bg-primary" 
                                         role="progressbar" 
                                         style="width: <?php echo $porcentaje; ?>%"
                                         aria-valuenow="<?php echo $porcentaje; ?>" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100"></div>
                                </div>
                                <small class="text-muted d-block mt-1">Personas registradas</small>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h6><?php echo SITE_NAME; ?></h6>
                    <p class="mb-0 small">
                        <i class="fas fa-database me-1"></i>
                        Sistema de registro y verificación de credenciales
                    </p>
                    <p class="mb-0 small">
                        <i class="fas fa-info-circle me-1"></i>
                        Base de datos: SQLite - <?php echo date('Y'); ?>
                    </p>
                </div>
                <div class="col-md-6 text-end">
                    <p class="mb-0 small">
                        <i class="fas fa-chart-line me-1"></i>
                        <strong>Estadísticas:</strong>
                        <?php echo $estadisticas['total']; ?> registros totales
                        <br>
                        <span class="text-muted">
                            <i class="fas fa-clock me-1"></i>
                            Actualizado: <?php echo date('d/m/Y H:i'); ?>
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script>
        // Inicializar DataTable
        $(document).ready(function() {
            $('#tablaCredenciales').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
                },
                dom: '<"row"<"col-md-6"l><"col-md-6"f>>rt<"row"<"col-md-6"i><"col-md-6"p>>',
                pageLength: 25,
                order: [[7, 'desc']], // Ordenar por fecha descendente
                buttons: [
                    {
                        extend: 'excel',
                        text: '<i class="fas fa-file-excel me-1"></i>Excel',
                        className: 'btn btn-success btn-sm'
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="fas fa-file-pdf me-1"></i>PDF',
                        className: 'btn btn-danger btn-sm'
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print me-1"></i>Imprimir',
                        className: 'btn btn-info btn-sm'
                    }
                ],
                initComplete: function() {
                    // Añadir botones al DOM
                    this.api().buttons().container().appendTo('#tablaCredenciales_wrapper .col-md-6:eq(0)');
                }
            });
            
            // Inicializar tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Auto-enfocar campo de búsqueda si está vacío
            const searchInput = document.querySelector('input[name="busqueda"]');
            if (searchInput && !searchInput.value) {
                searchInput.focus();
            }
        });
        
        // Función para copiar código al portapapeles
        function copiarCodigo(codigo) {
            navigator.clipboard.writeText(codigo).then(function() {
                alert('Código copiado: ' + codigo);
            }).catch(function(err) {
                console.error('Error al copiar: ', err);
            });
        }
        
        // Función para filtrar por estado desde JavaScript
        function filtrarPorEstado(estado) {
            if (estado) {
                window.location.href = 'lista.php?estado=' + encodeURIComponent(estado);
            } else {
                window.location.href = 'lista.php';
            }
        }
        
        // Función para buscar rápidamente
        function buscarRapido() {
            const busqueda = prompt('Ingrese texto para buscar (código, cédula, nombre):');
            if (busqueda) {
                window.location.href = 'lista.php?busqueda=' + encodeURIComponent(busqueda);
            }
        }
    </script>
</body>
</html>