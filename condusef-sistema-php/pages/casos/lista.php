<?php
/**
 * CONDUSEF - Lista de Casos
 */

$pageTitle = 'Casos';
include __DIR__ . '/../../includes/header.php';

// Parámetros de búsqueda y filtros
$search = $_GET['search'] ?? '';
$estado = $_GET['estado'] ?? '';
$prioridad = $_GET['prioridad'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Construir consulta
$whereClauses = [];
$params = [];

if (!empty($search)) {
    $whereClauses[] = "(c.folio LIKE ? OR cl.nombre_completo LIKE ? OR c.numero_poliza LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
}

if (!empty($estado)) {
    $whereClauses[] = "c.estado = ?";
    $params[] = $estado;
}

if (!empty($prioridad)) {
    $whereClauses[] = "c.prioridad = ?";
    $params[] = $prioridad;
}

$where = !empty($whereClauses) ? "WHERE " . implode(" AND ", $whereClauses) : "";

// Contar total
$sqlCount = "SELECT COUNT(*) as total FROM casos c LEFT JOIN clientes cl ON c.cliente_id = cl.id $where";
$totalCasos = queryOne($sqlCount, $params)['total'] ?? 0;

// Paginación
$pagination = paginate($totalCasos, $page, ITEMS_PER_PAGE);

// Obtener casos
$sql = "SELECT c.*, cl.nombre_completo as cliente_nombre, u.nombre as usuario_nombre, a.nombre as aseguradora_nombre
        FROM casos c
        LEFT JOIN clientes cl ON c.cliente_id = cl.id
        LEFT JOIN usuarios u ON c.usuario_id = u.id
        LEFT JOIN aseguradoras a ON c.aseguradora_id = a.id
        $where
        ORDER BY c.fecha_creacion DESC
        LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}";

$casos = query($sql, $params);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-folder"></i> Casos</h2>
    <a href="crear.php" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Nuevo Caso
    </a>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Buscar por folio, cliente o póliza..." value="<?php echo clean($search); ?>">
            </div>
            <div class="col-md-3">
                <select name="estado" class="form-select">
                    <option value="">Todos los estados</option>
                    <?php foreach (ESTADOS_CASOS as $key => $value): ?>
                    <option value="<?php echo $key; ?>" <?php echo $estado === $key ? 'selected' : ''; ?>>
                        <?php echo $value; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select name="prioridad" class="form-select">
                    <option value="">Todas las prioridades</option>
                    <?php foreach (PRIORIDADES as $key => $value): ?>
                    <option value="<?php echo $key; ?>" <?php echo $prioridad === $key ? 'selected' : ''; ?>>
                        <?php echo $value; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-filter"></i> Filtrar</button>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de casos -->
<div class="card">
    <div class="card-body">
        <?php if (!empty($casos)): ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Folio</th>
                        <th>Cliente</th>
                        <th>Aseguradora</th>
                        <th>Póliza</th>
                        <th>Estado</th>
                        <th>Prioridad</th>
                        <th>Monto</th>
                        <th>Asignado a</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($casos as $caso): ?>
                    <tr>
                        <td>
                            <a href="ver.php?id=<?php echo $caso['id']; ?>" class="fw-bold">
                                <?php echo clean($caso['folio']); ?>
                            </a>
                        </td>
                        <td><?php echo clean($caso['cliente_nombre']); ?></td>
                        <td><?php echo clean($caso['aseguradora_nombre'] ?: 'N/A'); ?></td>
                        <td><?php echo clean($caso['numero_poliza'] ?: 'N/A'); ?></td>
                        <td><span class="<?php echo getEstadoClass($caso['estado']); ?>"><?php echo getNombreEstado($caso['estado']); ?></span></td>
                        <td><span class="<?php echo getPrioridadClass($caso['prioridad']); ?>"><?php echo PRIORIDADES[$caso['prioridad']]; ?></span></td>
                        <td><?php echo formatMoney($caso['monto_reclamado']); ?></td>
                        <td><?php echo clean($caso['usuario_nombre'] ?: 'Sin asignar'); ?></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="ver.php?id=<?php echo $caso['id']; ?>" class="btn btn-outline-primary" title="Ver">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="editar.php?id=<?php echo $caso['id']; ?>" class="btn btn-outline-warning" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php echo renderPagination($pagination, 'lista.php?'); ?>

        <?php else: ?>
        <div class="alert alert-info text-center">
            <i class="bi bi-info-circle"></i> No se encontraron casos.
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
