<?php
/**
 * CONDUSEF - Lista de Clientes
 */

$pageTitle = 'Clientes';
include __DIR__ . '/../../includes/header.php';

// Parámetros de búsqueda y paginación
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = ITEMS_PER_PAGE;

// Construir consulta
$whereClauses = [];
$params = [];

if (!empty($search)) {
    $whereClauses[] = "(nombre_completo LIKE ? OR email LIKE ? OR telefono LIKE ? OR curp LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
}

$where = !empty($whereClauses) ? "WHERE " . implode(" AND ", $whereClauses) : "";

// Contar total de registros
$sqlCount = "SELECT COUNT(*) as total FROM clientes $where";
$totalClientes = queryOne($sqlCount, $params)['total'] ?? 0;

// Obtener datos de paginación
$pagination = paginate($totalClientes, $page, $perPage);

// Obtener clientes
$sql = "SELECT c.*, u.nombre as creado_por_nombre,
        (SELECT COUNT(*) FROM casos WHERE cliente_id = c.id) as total_casos
        FROM clientes c
        LEFT JOIN usuarios u ON c.creado_por = u.id
        $where
        ORDER BY c.fecha_creacion DESC
        LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}";

$clientes = query($sql, $params);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-people"></i> Clientes</h2>
    <a href="crear.php" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Nuevo Cliente
    </a>
</div>

<!-- Búsqueda -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-10">
                <input type="text" name="search" class="form-control" placeholder="Buscar por nombre, email, teléfono o CURP..." value="<?php echo clean($search); ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Buscar</button>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de clientes -->
<div class="card">
    <div class="card-body">
        <?php if (!empty($clientes)): ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nombre Completo</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>CURP</th>
                        <th>Casos</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clientes as $cliente): ?>
                    <tr>
                        <td>
                            <a href="ver.php?id=<?php echo $cliente['id']; ?>" class="fw-bold">
                                <?php echo clean($cliente['nombre_completo']); ?>
                            </a>
                        </td>
                        <td><?php echo clean($cliente['email'] ?: 'N/A'); ?></td>
                        <td><?php echo clean($cliente['telefono'] ?: 'N/A'); ?></td>
                        <td><?php echo clean($cliente['curp'] ?: 'N/A'); ?></td>
                        <td>
                            <span class="badge bg-info"><?php echo $cliente['total_casos']; ?> casos</span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="ver.php?id=<?php echo $cliente['id']; ?>" class="btn btn-outline-primary" title="Ver">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="editar.php?id=<?php echo $cliente['id']; ?>" class="btn btn-outline-warning" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php if (hasRole(['admin'])): ?>
                                <button class="btn btn-outline-danger btn-delete" data-id="<?php echo $cliente['id']; ?>" title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <?php echo renderPagination($pagination, 'lista.php' . ($search ? '?search=' . urlencode($search) . '&' : '?')); ?>

        <?php else: ?>
        <div class="alert alert-info text-center">
            <i class="bi bi-info-circle"></i> No se encontraron clientes.
            <a href="crear.php" class="alert-link">Crear el primer cliente</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
$(document).ready(function() {
    // Eliminar cliente
    $('.btn-delete').click(function() {
        if (!confirm('¿Está seguro de eliminar este cliente? Esta acción no se puede deshacer.')) {
            return;
        }

        const clienteId = $(this).data('id');
        const row = $(this).closest('tr');

        $.ajax({
            url: '<?php echo BASE_URL; ?>/api/clientes/eliminar.php',
            type: 'POST',
            data: {
                id: clienteId,
                csrf_token: '<?php echo $_SESSION[CSRF_TOKEN_NAME]; ?>'
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    row.fadeOut(300, function() {
                        $(this).remove();
                    });
                    showAlert(response.message, 'success', '.card-body');
                } else {
                    showAlert(response.message, 'danger', '.card-body');
                }
            },
            error: function() {
                showAlert('Error al eliminar el cliente', 'danger', '.card-body');
            }
        });
    });
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
