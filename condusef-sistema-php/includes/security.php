<?php
/**
 * CONDUSEF - Funciones de Seguridad
 */

/**
 * Genera un token CSRF y lo guarda en sesión
 * @return string Token CSRF
 */
function generateCSRFToken() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $token = bin2hex(random_bytes(32));
    $_SESSION[CSRF_TOKEN_NAME] = $token;
    $_SESSION[CSRF_TOKEN_NAME . '_time'] = time();

    return $token;
}

/**
 * Valida el token CSRF
 * @param string $token Token a validar
 * @return bool True si es válido
 */
function validateCSRFToken($token) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Verificar que existe el token en sesión
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        return false;
    }

    // Verificar que el token no haya expirado (30 minutos)
    if (isset($_SESSION[CSRF_TOKEN_NAME . '_time'])) {
        if (time() - $_SESSION[CSRF_TOKEN_NAME . '_time'] > 1800) {
            return false;
        }
    }

    // Comparar tokens de forma segura
    return hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

/**
 * Limpia y escapa datos para prevenir XSS
 * @param string $data Dato a limpiar
 * @return string Dato limpio
 */
function clean($data) {
    if (is_array($data)) {
        return array_map('clean', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitiza entrada de texto
 * @param string $data Dato a sanitizar
 * @return string Dato sanitizado
 */
function sanitizeText($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Sanitiza email
 * @param string $email Email a sanitizar
 * @return string Email sanitizado
 */
function sanitizeEmail($email) {
    return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
}

/**
 * Valida email
 * @param string $email Email a validar
 * @return bool True si es válido
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Hashea una contraseña
 * @param string $password Contraseña en texto plano
 * @return string Hash de la contraseña
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Verifica una contraseña contra su hash
 * @param string $password Contraseña en texto plano
 * @param string $hash Hash almacenado
 * @return bool True si coincide
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Registra intento de login fallido
 * @param string $email Email del usuario
 */
function registerFailedLogin($email) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Inicializar contador si no existe
    if (!isset($_SESSION['failed_login_attempts'])) {
        $_SESSION['failed_login_attempts'] = [];
    }

    // Registrar intento
    if (!isset($_SESSION['failed_login_attempts'][$email])) {
        $_SESSION['failed_login_attempts'][$email] = [
            'count' => 0,
            'last_attempt' => time()
        ];
    }

    $_SESSION['failed_login_attempts'][$email]['count']++;
    $_SESSION['failed_login_attempts'][$email]['last_attempt'] = time();

    // Actualizar en base de datos
    $sql = "UPDATE usuarios SET intentos_login = intentos_login + 1 WHERE email = ?";
    execute($sql, [$email]);

    // Bloquear si excede intentos máximos
    if ($_SESSION['failed_login_attempts'][$email]['count'] >= MAX_LOGIN_ATTEMPTS) {
        $lockoutUntil = date('Y-m-d H:i:s', time() + LOGIN_LOCKOUT_TIME);
        $sql = "UPDATE usuarios SET bloqueado_hasta = ? WHERE email = ?";
        execute($sql, [$lockoutUntil, $email]);
    }
}

/**
 * Reinicia contador de intentos fallidos
 * @param string $email Email del usuario
 */
function resetFailedLogins($email) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (isset($_SESSION['failed_login_attempts'][$email])) {
        unset($_SESSION['failed_login_attempts'][$email]);
    }

    $sql = "UPDATE usuarios SET intentos_login = 0, bloqueado_hasta = NULL WHERE email = ?";
    execute($sql, [$email]);
}

/**
 * Verifica si un usuario está bloqueado
 * @param string $email Email del usuario
 * @return bool True si está bloqueado
 */
function isUserLocked($email) {
    $sql = "SELECT bloqueado_hasta FROM usuarios WHERE email = ?";
    $user = queryOne($sql, [$email]);

    if (!$user || !$user['bloqueado_hasta']) {
        return false;
    }

    $lockoutTime = strtotime($user['bloqueado_hasta']);
    if (time() < $lockoutTime) {
        return true;
    }

    // Desbloquear si ya pasó el tiempo
    resetFailedLogins($email);
    return false;
}

/**
 * Valida sesión activa
 * @return bool True si la sesión es válida
 */
function validateSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Verificar que existe el ID de usuario
    if (!isset($_SESSION['user_id'])) {
        return false;
    }

    // Verificar timeout de sesión
    if (isset($_SESSION['last_activity'])) {
        if (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
            session_unset();
            session_destroy();
            return false;
        }
    }

    // Actualizar tiempo de última actividad
    $_SESSION['last_activity'] = time();

    // Regenerar ID de sesión periódicamente (cada 30 minutos)
    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } else if (time() - $_SESSION['created'] > 1800) {
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }

    return true;
}

/**
 * Verifica si el usuario tiene un rol específico
 * @param string|array $roles Rol o array de roles permitidos
 * @return bool True si tiene el rol
 */
function hasRole($roles) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['user_rol'])) {
        return false;
    }

    if (is_array($roles)) {
        return in_array($_SESSION['user_rol'], $roles);
    }

    return $_SESSION['user_rol'] === $roles;
}

/**
 * Requiere autenticación o redirige al login
 */
function requireLogin() {
    if (!validateSession()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit();
    }
}

/**
 * Requiere rol específico o redirige
 * @param string|array $roles Rol o roles requeridos
 */
function requireRole($roles) {
    requireLogin();

    if (!hasRole($roles)) {
        header('HTTP/1.1 403 Forbidden');
        die('No tienes permisos para acceder a esta página.');
    }
}

/**
 * Registra evento en auditoría
 * @param string $accion Acción realizada
 * @param string $tabla Tabla afectada
 * @param int $registroId ID del registro afectado
 * @param array $datosAnteriores Datos antes del cambio
 * @param array $datosNuevos Datos después del cambio
 */
function registrarAuditoria($accion, $tabla = null, $registroId = null, $datosAnteriores = null, $datosNuevos = null) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $usuarioId = $_SESSION['user_id'] ?? null;
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

    $sql = "INSERT INTO auditoria (usuario_id, accion, tabla_afectada, registro_id, ip_address, user_agent, datos_anteriores, datos_nuevos)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    execute($sql, [
        $usuarioId,
        $accion,
        $tabla,
        $registroId,
        $ipAddress,
        $userAgent,
        $datosAnteriores ? json_encode($datosAnteriores) : null,
        $datosNuevos ? json_encode($datosNuevos) : null
    ]);
}

/**
 * Valida extensión de archivo
 * @param string $filename Nombre del archivo
 * @return bool True si es válida
 */
function validateFileExtension($filename) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($ext, ALLOWED_EXTENSIONS);
}

/**
 * Valida tipo MIME de archivo
 * @param string $mimeType Tipo MIME
 * @return bool True si es válido
 */
function validateMimeType($mimeType) {
    return in_array($mimeType, ALLOWED_MIME_TYPES);
}

/**
 * Valida tamaño de archivo
 * @param int $size Tamaño en bytes
 * @return bool True si es válido
 */
function validateFileSize($size) {
    return $size <= MAX_FILE_SIZE;
}

/**
 * Genera nombre seguro para archivo
 * @param string $filename Nombre original
 * @return string Nombre seguro
 */
function generateSecureFilename($filename) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return uniqid() . '_' . time() . '.' . $ext;
}

/**
 * Headers de seguridad HTTP
 */
function setSecurityHeaders() {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    // Solo en producción con HTTPS:
    // header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}
