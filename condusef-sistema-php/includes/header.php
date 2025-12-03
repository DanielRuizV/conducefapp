<?php
/**
 * CONDUSEF - Header
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/functions.php';

// Verificar autenticación
requireLogin();

// Obtener usuario actual
$currentUser = getCurrentUser();
if (!$currentUser) {
    redirect(BASE_URL . '/logout.php');
}

// Generar token CSRF si no existe
if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
    generateCSRFToken();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?><?php echo APP_NAME; ?></title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #0284c7;
            --primary-dark: #0369a1;
            --sidebar-width: 260px;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f8f9fa;
        }

        .wrapper {
            display: flex;
            width: 100%;
            align-items: stretch;
        }

        /* Sidebar */
        #sidebar {
            min-width: var(--sidebar-width);
            max-width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: #fff;
            min-height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            transition: all 0.3s;
        }

        #sidebar.active {
            margin-left: calc(var(--sidebar-width) * -1);
        }

        #sidebar .sidebar-header {
            padding: 1.5rem;
            background: rgba(0, 0, 0, 0.2);
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        #sidebar .sidebar-header h3 {
            font-size: 1.2rem;
            font-weight: 700;
            margin: 0;
        }

        #sidebar .sidebar-header p {
            font-size: 0.75rem;
            margin: 0;
            opacity: 0.8;
        }

        #sidebar ul.components {
            padding: 1rem 0;
        }

        #sidebar ul li {
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        #sidebar ul li a {
            padding: 0.85rem 1.5rem;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            transition: all 0.3s;
        }

        #sidebar ul li a:hover,
        #sidebar ul li a.active {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        #sidebar ul li a i {
            margin-right: 0.75rem;
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }

        /* Content */
        #content {
            width: 100%;
            min-height: 100vh;
            transition: all 0.3s;
            margin-left: var(--sidebar-width);
        }

        #content.active {
            margin-left: 0;
        }

        /* Navbar */
        .navbar {
            padding: 0.75rem 1.5rem;
            background: #fff;
            border-bottom: 1px solid #dee2e6;
            box-shadow: 0 2px 4px rgba(0,0,0,0.04);
        }

        .navbar-brand {
            font-weight: 600;
            color: var(--primary-color) !important;
        }

        /* Main content */
        .main-content {
            padding: 2rem;
        }

        /* Cards */
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 1.5rem;
        }

        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #f0f0f0;
            padding: 1rem 1.5rem;
            font-weight: 600;
        }

        /* Buttons */
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }

        /* User dropdown */
        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            #sidebar {
                margin-left: calc(var(--sidebar-width) * -1);
            }

            #sidebar.active {
                margin-left: 0;
            }

            #content {
                margin-left: 0;
            }

            #content.active {
                margin-left: var(--sidebar-width);
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <?php include __DIR__ . '/sidebar.php'; ?>

        <!-- Page Content -->
        <div id="content">
            <!-- Navbar -->
            <nav class="navbar navbar-expand-lg navbar-light">
                <div class="container-fluid">
                    <button type="button" id="sidebarCollapse" class="btn btn-link">
                        <i class="bi bi-list fs-4"></i>
                    </button>

                    <span class="navbar-brand mb-0"><?php echo isset($pageTitle) ? $pageTitle : 'Dashboard'; ?></span>

                    <div class="ms-auto d-flex align-items-center">
                        <!-- Notificaciones -->
                        <div class="dropdown me-3">
                            <button class="btn btn-link position-relative" type="button" id="notificationsDropdown" data-bs-toggle="dropdown">
                                <i class="bi bi-bell fs-5"></i>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                                    0
                                </span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" style="min-width: 300px;">
                                <li><h6 class="dropdown-header">Notificaciones</h6></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-center text-muted" href="#">No hay notificaciones</a></li>
                            </ul>
                        </div>

                        <!-- Usuario -->
                        <div class="dropdown">
                            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown">
                                <div class="user-avatar me-2">
                                    <?php echo getInitials($currentUser['nombre']); ?>
                                </div>
                                <div class="d-none d-md-block">
                                    <div class="fw-semibold" style="font-size: 0.9rem;"><?php echo clean($currentUser['nombre']); ?></div>
                                    <div class="text-muted" style="font-size: 0.75rem;"><?php echo getNombreRol($currentUser['rol']); ?></div>
                                </div>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/pages/perfil.php"><i class="bi bi-person me-2"></i> Mi Perfil</a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/pages/configuracion.php"><i class="bi bi-gear me-2"></i> Configuración</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>/logout.php"><i class="bi bi-box-arrow-right me-2"></i> Cerrar Sesión</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Main Content -->
            <div class="main-content">
