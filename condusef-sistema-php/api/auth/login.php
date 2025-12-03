<?php
/**
 * CONDUSEF - API de Login
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../../includes/functions.php';

session_start();
setSecurityHeaders();

// Solo permitir POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, null, 'Método no permitido');
}

try {
    // Validar token CSRF
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!validateCSRFToken($csrfToken)) {
        jsonResponse(false, null, 'Token de seguridad inválido');
    }

    // Obtener y sanitizar datos
    $email = sanitizeEmail($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validaciones básicas
    if (empty($email) || empty($password)) {
        jsonResponse(false, null, 'Por favor completa todos los campos');
    }

    if (!validateEmail($email)) {
        jsonResponse(false, null, 'El correo electrónico no es válido');
    }

    // Verificar si el usuario está bloqueado
    if (isUserLocked($email)) {
        jsonResponse(false, null, 'Tu cuenta ha sido bloqueada temporalmente por múltiples intentos fallidos. Inténtalo más tarde.');
    }

    // Buscar usuario en la base de datos
    $sql = "SELECT id, nombre, email, password, rol, activo, bloqueado_hasta
            FROM usuarios
            WHERE email = ?";

    $user = queryOne($sql, [$email]);

    // Verificar si el usuario existe
    if (!$user) {
        registerFailedLogin($email);
        jsonResponse(false, null, 'Credenciales incorrectas');
    }

    // Verificar si el usuario está activo
    if ($user['activo'] != 1) {
        jsonResponse(false, null, 'Tu cuenta está desactivada. Contacta al administrador.');
    }

    // Verificar contraseña
    if (!verifyPassword($password, $user['password'])) {
        registerFailedLogin($email);

        // Calcular intentos restantes
        $sql = "SELECT intentos_login FROM usuarios WHERE email = ?";
        $userData = queryOne($sql, [$email]);
        $intentos = $userData['intentos_login'] ?? 0;
        $restantes = MAX_LOGIN_ATTEMPTS - $intentos;

        if ($restantes > 0) {
            jsonResponse(false, null, "Credenciales incorrectas. Te quedan $restantes intentos.");
        } else {
            jsonResponse(false, null, 'Cuenta bloqueada por múltiples intentos fallidos.');
        }
    }

    // Login exitoso - Reiniciar intentos fallidos
    resetFailedLogins($email);

    // Actualizar último acceso
    $sql = "UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?";
    execute($sql, [$user['id']]);

    // Crear sesión
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_nombre'] = $user['nombre'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_rol'] = $user['rol'];
    $_SESSION['last_activity'] = time();
    $_SESSION['created'] = time();

    // Registrar en auditoría
    registrarAuditoria('login', 'usuarios', $user['id']);

    jsonResponse(true, [
        'user' => [
            'id' => $user['id'],
            'nombre' => $user['nombre'],
            'email' => $user['email'],
            'rol' => $user['rol']
        ]
    ], 'Inicio de sesión exitoso');

} catch (Exception $e) {
    error_log("Error en login: " . $e->getMessage());
    jsonResponse(false, null, 'Error al procesar la solicitud');
}
