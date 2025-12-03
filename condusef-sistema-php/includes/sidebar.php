<?php
/**
 * CONDUSEF - Sidebar Navigation
 */

// Determinar página activa
$currentPage = basename($_SERVER['PHP_SELF']);
$currentDir = basename(dirname($_SERVER['PHP_SELF']));

function isActive($page, $dir = '') {
    global $currentPage, $currentDir;
    if ($dir && $currentDir === $dir) {
        return 'active';
    }
    return $currentPage === $page ? 'active' : '';
}
?>

<nav id="sidebar">
    <div class="sidebar-header">
        <i class="bi bi-shield-check fs-1"></i>
        <h3>CONDUSEF</h3>
        <p>Sistema de Gestión</p>
    </div>

    <ul class="list-unstyled components">
        <!-- Dashboard -->
        <li>
            <a href="<?php echo BASE_URL; ?>/index.php" class="<?php echo isActive('index.php'); ?>">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <!-- Casos -->
        <li>
            <a href="<?php echo BASE_URL; ?>/pages/casos/lista.php" class="<?php echo isActive('', 'casos'); ?>">
                <i class="bi bi-folder"></i>
                <span>Casos</span>
            </a>
        </li>

        <!-- Clientes -->
        <li>
            <a href="<?php echo BASE_URL; ?>/pages/clientes/lista.php" class="<?php echo isActive('', 'clientes'); ?>">
                <i class="bi bi-people"></i>
                <span>Clientes</span>
            </a>
        </li>

        <!-- Aseguradoras -->
        <li>
            <a href="<?php echo BASE_URL; ?>/pages/aseguradoras/lista.php" class="<?php echo isActive('', 'aseguradoras'); ?>">
                <i class="bi bi-building"></i>
                <span>Aseguradoras</span>
            </a>
        </li>

        <!-- Documentos -->
        <li>
            <a href="<?php echo BASE_URL; ?>/pages/documentos/lista.php" class="<?php echo isActive('', 'documentos'); ?>">
                <i class="bi bi-file-earmark-text"></i>
                <span>Documentos</span>
            </a>
        </li>

        <!-- Reportes (solo admin y abogado) -->
        <?php if (hasRole(['admin', 'abogado'])): ?>
        <li>
            <a href="<?php echo BASE_URL; ?>/pages/reportes.php" class="<?php echo isActive('reportes.php'); ?>">
                <i class="bi bi-graph-up"></i>
                <span>Reportes</span>
            </a>
        </li>
        <?php endif; ?>

        <!-- Usuarios (solo admin) -->
        <?php if (hasRole('admin')): ?>
        <li>
            <a href="<?php echo BASE_URL; ?>/pages/usuarios/lista.php" class="<?php echo isActive('', 'usuarios'); ?>">
                <i class="bi bi-person-gear"></i>
                <span>Usuarios</span>
            </a>
        </li>
        <?php endif; ?>

        <!-- Separador -->
        <li style="margin-top: 2rem; padding-top: 1rem; border-top: 1px solid rgba(255,255,255,0.1);">
            <a href="<?php echo BASE_URL; ?>/pages/ayuda.php" class="<?php echo isActive('ayuda.php'); ?>">
                <i class="bi bi-question-circle"></i>
                <span>Ayuda</span>
            </a>
        </li>

        <li>
            <a href="<?php echo BASE_URL; ?>/logout.php">
                <i class="bi bi-box-arrow-right"></i>
                <span>Cerrar Sesión</span>
            </a>
        </li>
    </ul>

    <!-- Footer del sidebar -->
    <div style="position: absolute; bottom: 0; width: 100%; padding: 1rem; text-align: center; border-top: 1px solid rgba(255,255,255,0.1); font-size: 0.75rem; opacity: 0.7;">
        <div>v<?php echo APP_VERSION; ?></div>
        <div>&copy; <?php echo date('Y'); ?> Maldonado y Asociados</div>
    </div>
</nav>
