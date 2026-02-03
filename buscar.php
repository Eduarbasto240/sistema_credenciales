<?php
// buscar.php - Panel de búsqueda y verificación
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';
require_once 'database.php';
require_once 'funciones.php';

$manager = new CredencialManager();
$resultados = [];
$busquedaRealizada = false;

// Procesar búsqueda
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buscar'])) {
    $termino = trim($_POST['termino']);
    $criterio = $_POST['criterio'];
    
    $resultados = $manager->buscarPersonas($termino, $criterio);
    $busquedaRealizada = true;
}

// Procesar eliminación
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $manager->eliminarPersona($id);
    header('Location: buscar.php?mensaje=Persona+eliminada+exitosamente');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - BÚSQUEDA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: #f8f9fa; }
        .card { border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .table-hover tbody tr:hover { background-color: rgba(0,0,0,0.075); }
        .badge-id { font-size: 0.9rem; }
        .qr-small { max-width: 80px; }
    </style>
</head>
<body>
    <div class="container py-4">
        <!-- Mensajes -->
        <?php if (isset($_GET['mensaje'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo htmlspecialchars($_GET['mensaje']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0"><i class="fas fa-search me-2"></i>BUSCAR Y VERIFICAR CREDENCIALES</h3>
            </div>
            <div class="card-body">
                <!-- Formulario de búsqueda -->
                <form method="POST" class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <input type="text" name="termino" class="form-control" 
                                   placeholder="Ingrese cédula, nombre, apellido o código..." 
                                   value="<?php echo $_POST['termino'] ?? ''; ?>">
                        </div>
                        <div class="col-md-4">
                            <select name="criterio" class="form-select">
                                <option value="todos" <?php echo ($_POST['criterio'] ?? '') == 'todos' ? 'selected' : ''; ?>>Todos los campos</option>
                                <option value="cedula" <?php echo ($_POST['criterio'] ?? '') == 'cedula' ? 'selected' : ''; ?>>Cédula</option>
                                <option value="nombre" <?php echo ($_POST['criterio'] ?? '') == 'nombre' ? 'selected' : ''; ?>>Nombre</option>
                                <option value="apellido" <?php echo ($_POST['criterio'] ?? '') == 'apellido' ? 'selected' : ''; ?>>Apellido</option>
                                <option value="codigo" <?php echo ($_POST['criterio'] ?? '') == 'codigo' ? 'selected' : ''; ?>>Código</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" name="buscar" class="btn btn-primary w-100">
                                <i class="fas fa-search me-1"></i> Buscar
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Resultados de búsqueda -->
                <?php if ($busquedaRealizada): ?>
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>CÓDIGO</th>
                                <th>NOMBRE COMPLETO</th>
                                <th>CÉDULA</th>
                                <th>CARGO</th>
                                <th>RESPONSABLE</th>
                                <th>FECHA REGISTRO</th>
                                <th>ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($resultados) > 0): ?>
                                <?php foreach ($resultados as $index => $persona): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td>
                                        <span class="badge bg-primary badge-id"><?php echo $persona['codigo_credencial']; ?></span>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($persona['primer_nombre'] . ' ' . 
                                            ($persona['segundo_nombre'] ? $persona['segundo_nombre'] . ' ' : '') . 
                                            $persona['primer_apellido'] . ' ' . 
                                            ($persona['segundo_apellido'] ? $persona['segundo_apellido'] : '')); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($persona['cedula']); ?></td>
                                    <td><?php echo htmlspecialchars($persona['cargo']); ?></td>
                                    <td><?php echo htmlspecialchars($persona['persona_responsable']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($persona['fecha_registro'])); ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="ver.php?id=<?php echo $persona['id']; ?>" 
                                               class="btn btn-info" title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="editar.php?id=<?php echo $persona['id']; ?>" 
                                               class="btn btn-warning" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?eliminar=<?php echo $persona['id']; ?>" 
                                               class="btn btn-danger" 
                                               onclick="return confirm('¿Está seguro de eliminar este registro?')"
                                               title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        <i class="fas fa-search fa-2x mb-3"></i><br>
                                        No se encontraron resultados para la búsqueda.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Estadísticas de búsqueda -->
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Se encontraron <strong><?php echo count($resultados); ?></strong> resultado(s)
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Enlaces de navegación -->
        <div class="text-center mt-4">
            <a href="index.php" class="btn btn-outline-primary me-2">
                <i class="fas fa-plus-circle me-2"></i>NUEVO REGISTRO
            </a>
            <a href="reporte.php" class="btn btn-outline-success">
                <i class="fas fa-file-excel me-2"></i>GENERAR REPORTE
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Confirmar antes de eliminar
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('a[href*="eliminar"]');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    if (!confirm('¿Está seguro de que desea eliminar este registro? Esta acción no se puede deshacer.')) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
</body>
</html>