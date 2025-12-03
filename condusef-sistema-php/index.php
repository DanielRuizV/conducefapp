<?php
/**
 * CONDUSEF - Dashboard Principal
 */

$pageTitle = 'Dashboard';
include __DIR__ . '/includes/header.php';

// Obtener estadísticas
try {
    // Total de casos
    $totalCasos = queryOne("SELECT COUNT(*) as total FROM casos")['total'] ?? 0;

    // Casos por estado
    $casosNuevos = queryOne("SELECT COUNT(*) as total FROM casos WHERE estado = 'nuevo'")['total'] ?? 0;
    $casosEnProceso = queryOne("SELECT COUNT(*) as total FROM casos WHERE estado = 'en_proceso'")['total'] ?? 0;
    $casosResueltos = queryOne("SELECT COUNT(*) as total FROM casos WHERE estado = 'resuelto'")['total'] ?? 0;
    $casosCerrados = queryOne("SELECT COUNT(*) as total FROM casos WHERE estado = 'cerrado'")['total'] ?? 0;

    // Total de clientes
    $totalClientes = queryOne("SELECT COUNT(*) as total FROM clientes WHERE activo = 1")['total'] ?? 0;

    // Total de documentos
    $totalDocumentos = queryOne("SELECT COUNT(*) as total FROM documentos")['total'] ?? 0;

    // Monto total reclamado
    $montoReclamado = queryOne("SELECT SUM(monto_reclamado) as total FROM casos")['total'] ?? 0;

    // Monto total recuperado
    $montoRecuperado = queryOne("SELECT SUM(monto_recuperado) as total FROM casos WHERE monto_recuperado > 0")['total'] ?? 0;

    // Casos recientes (últimos 5)
    $sql = "SELECT c.*, cl.nombre_completo as cliente_nombre, u.nombre as usuario_nombre
            FROM casos c
            LEFT JOIN clientes cl ON c.cliente_id = cl.id
            LEFT JOIN usuarios u ON c.usuario_id = u.id
            ORDER BY c.fecha_creacion DESC
            LIMIT 5";
    $casosRecientes = query($sql);

    // Casos próximos a vencer (fecha_limite en los próximos 7 días)
    $sql = "SELECT c.*, cl.nombre_completo as cliente_nombre
            FROM casos c
            LEFT JOIN clientes cl ON c.cliente_id = cl.id
            WHERE c.fecha_limite BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
            AND c.estado NOT IN ('resuelto', 'cerrado')
            ORDER BY c.fecha_limite ASC
            LIMIT 5";
    $casosProximosVencer = query($sql);

    // Actividad reciente
    $sql = "SELECT s.*, c.folio as caso_folio, u.nombre as usuario_nombre
            FROM seguimientos s
            LEFT JOIN casos c ON s.caso_id = c.id
            LEFT JOIN usuarios u ON s.realizado_por = u.id
            ORDER BY s.fecha_creacion DESC
            LIMIT 10";
    $actividadReciente = query($sql);

} catch (Exception $e) {
    error_log("Error en dashboard: " . $e->getMessage());
}
?>

<!-- Estadísticas en Cards -->
<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Casos</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($totalCasos); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-folder fs-2 text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Clientes</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($totalClientes); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-people fs-2 text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Monto Reclamado</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo formatMoney($montoReclamado); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-currency-dollar fs-2 text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Monto Recuperado</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo formatMoney($montoRecuperado); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-wallet2 fs-2 text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Casos por Estado -->
<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-bar-chart"></i> Casos por Estado</span>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3 mb-3">
                        <div class="p-3 bg-light rounded">
                            <h3 class="text-info"><?php echo $casosNuevos; ?></h3>
                            <small>Nuevos</small>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="p-3 bg-light rounded">
                            <h3 class="text-primary"><?php echo $casosEnProceso; ?></h3>
                            <small>En Proceso</small>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="p-3 bg-light rounded">
                            <h3 class="text-success"><?php echo $casosResueltos; ?></h3>
                            <small>Resueltos</small>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="p-3 bg-light rounded">
                            <h3 class="text-secondary"><?php echo $casosCerrados; ?></h3>
                            <small>Cerrados</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-clock-history"></i> Accesos Rápidos
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?php echo BASE_URL; ?>/pages/casos/crear.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Nuevo Caso
                    </a>
                    <a href="<?php echo BASE_URL; ?>/pages/clientes/crear.php" class="btn btn-success">
                        <i class="bi bi-person-plus"></i> Nuevo Cliente
                    </a>
                    <a href="<?php echo BASE_URL; ?>/pages/casos/lista.php" class="btn btn-outline-primary">
                        <i class="bi bi-list-ul"></i> Ver Todos los Casos
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Casos Recientes y Próximos a Vencer -->
<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-clock"></i> Casos Recientes
            </div>
            <div class="card-body">
                <?php if (!empty($casosRecientes)): ?>
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Folio</th>
                                <th>Cliente</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($casosRecientes as $caso): ?>
                            <tr>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>/pages/casos/ver.php?id=<?php echo $caso['id']; ?>">
                                        <?php echo clean($caso['folio']); ?>
                                    </a>
                                </td>
                                <td><?php echo clean($caso['cliente_nombre']); ?></td>
                                <td><span class="<?php echo getEstadoClass($caso['estado']); ?>"><?php echo getNombreEstado($caso['estado']); ?></span></td>
                                <td><?php echo formatDate($caso['fecha_creacion']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-muted text-center">No hay casos registrados</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-exclamation-triangle"></i> Próximos a Vencer
            </div>
            <div class="card-body">
                <?php if (!empty($casosProximosVencer)): ?>
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Folio</th>
                                <th>Cliente</th>
                                <th>Fecha Límite</th>
                                <th>Días</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($casosProximosVencer as $caso): ?>
                            <?php
                                $diasRestantes = daysDiff($caso['fecha_limite']);
                                $colorClass = $diasRestantes <= 2 ? 'text-danger' : ($diasRestantes <= 5 ? 'text-warning' : 'text-info');
                            ?>
                            <tr>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>/pages/casos/ver.php?id=<?php echo $caso['id']; ?>">
                                        <?php echo clean($caso['folio']); ?>
                                    </a>
                                </td>
                                <td><?php echo clean($caso['cliente_nombre']); ?></td>
                                <td><?php echo formatDate($caso['fecha_limite']); ?></td>
                                <td><span class="<?php echo $colorClass; ?> fw-bold"><?php echo $diasRestantes; ?> días</span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-muted text-center">No hay casos próximos a vencer</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Actividad Reciente -->
<div class="row">
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-activity"></i> Actividad Reciente
            </div>
            <div class="card-body">
                <?php if (!empty($actividadReciente)): ?>
                <div class="list-group">
                    <?php foreach ($actividadReciente as $actividad): ?>
                    <div class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">
                                <i class="bi bi-dot"></i>
                                <?php echo clean($actividad['titulo']); ?>
                            </h6>
                            <small><?php echo formatDate($actividad['fecha_actividad'], true); ?></small>
                        </div>
                        <p class="mb-1 small"><?php echo clean(truncate($actividad['descripcion'], 150)); ?></p>
                        <small class="text-muted">
                            Caso: <a href="<?php echo BASE_URL; ?>/pages/casos/ver.php?id=<?php echo $actividad['caso_id']; ?>"><?php echo clean($actividad['caso_folio']); ?></a>
                            <?php if ($actividad['usuario_nombre']): ?>
                            | Por: <?php echo clean($actividad['usuario_nombre']); ?>
                            <?php endif; ?>
                        </small>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p class="text-muted text-center">No hay actividad reciente</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
    .border-left-primary {
        border-left: 4px solid #0284c7;
    }
    .border-left-success {
        border-left: 4px solid #10b981;
    }
    .border-left-info {
        border-left: 4px solid #06b6d4;
    }
    .border-left-warning {
        border-left: 4px solid #f59e0b;
    }
    .shadow {
        box-shadow: 0 .15rem 1.75rem 0 rgba(58,59,69,.15);
    }
</style>

<?php include __DIR__ . '/includes/footer.php'; ?>
