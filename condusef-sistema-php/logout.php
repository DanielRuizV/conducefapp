<?php
/**
 * CONDUSEF - Cerrar Sesión
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/security.php';

session_start();

// Registrar en auditoría si hay sesión activa
if (isset($_SESSION['user_id'])) {
    registrarAuditoria('logout', 'usuarios', $_SESSION['user_id']);
}

// Destruir todas las variables de sesión
$_SESSION = [];

// Destruir la cookie de sesión
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destruir la sesión
session_destroy();

// Redirigir al login
header('Location: ' . BASE_URL . '/login.php');
exit();
