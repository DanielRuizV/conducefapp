<?php
/**
 * CONDUSEF - Configuración General del Sistema
 */

// Zona horaria
date_default_timezone_set('America/Mexico_City');

// Configuración de errores (cambiar en producción)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');

// Configuración de sesiones seguras
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0); // Cambiar a 1 si se usa HTTPS
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.gc_maxlifetime', 3600); // 1 hora

// Configuración del sistema
define('APP_NAME', 'CONDUSEF - Sistema de Gestión');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'https://6nq.a9e.mytemp.website');

// Rutas del sistema
define('BASE_PATH', dirname(__DIR__));
define('UPLOAD_PATH', BASE_PATH . '/uploads/documentos/');
define('TEMP_PATH', BASE_PATH . '/uploads/temp/');
define('PDF_PATH', BASE_PATH . '/pdf/');

// URLs del sistema
define('BASE_URL', APP_URL);
define('ASSETS_URL', BASE_URL . '/assets');
define('UPLOAD_URL', BASE_URL . '/uploads/documentos');

// Configuración de archivos
define('MAX_FILE_SIZE', 10485760); // 10 MB en bytes
define('ALLOWED_EXTENSIONS', ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png']);
define('ALLOWED_MIME_TYPES', [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'image/jpeg',
    'image/png'
]);

// Configuración de seguridad
define('CSRF_TOKEN_NAME', 'csrf_token');
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutos en segundos
define('SESSION_TIMEOUT', 3600); // 1 hora en segundos

// Configuración de paginación
define('ITEMS_PER_PAGE', 20);

// Estados de casos
define('ESTADOS_CASOS', [
    'nuevo' => 'Nuevo',
    'en_proceso' => 'En Proceso',
    'presentado_une' => 'Presentado UNE',
    'presentado_condusef' => 'Presentado CONDUSEF',
    'conciliacion' => 'En Conciliación',
    'resuelto' => 'Resuelto',
    'cerrado' => 'Cerrado'
]);

// Prioridades
define('PRIORIDADES', [
    'baja' => 'Baja',
    'media' => 'Media',
    'alta' => 'Alta',
    'urgente' => 'Urgente'
]);

// Roles de usuario
define('ROLES', [
    'admin' => 'Administrador',
    'abogado' => 'Abogado',
    'asistente' => 'Asistente',
    'cliente' => 'Cliente'
]);

// Tipos de seguimiento
define('TIPOS_SEGUIMIENTO', [
    'nota' => 'Nota',
    'llamada' => 'Llamada',
    'email' => 'Email',
    'presentacion' => 'Presentación',
    'audiencia' => 'Audiencia',
    'resolucion' => 'Resolución'
]);

// Categorías de documentos
define('CATEGORIAS_DOCUMENTOS', [
    'identificacion' => 'Identificación',
    'poliza' => 'Póliza',
    'siniestro' => 'Documentos del Siniestro',
    'comunicaciones' => 'Comunicaciones',
    'resoluciones' => 'Resoluciones',
    'otros' => 'Otros'
]);

// Tipos de comunicación
define('TIPOS_COMUNICACION', [
    'llamada' => 'Llamada',
    'email' => 'Email',
    'whatsapp' => 'WhatsApp',
    'presencial' => 'Presencial',
    'oficio' => 'Oficio',
    'otro' => 'Otro'
]);

/**
 * Función para crear directorios necesarios
 */
function createRequiredDirectories() {
    $dirs = [
        BASE_PATH . '/uploads/documentos',
        BASE_PATH . '/uploads/temp',
        BASE_PATH . '/pdf',
        BASE_PATH . '/logs'
    ];

    foreach ($dirs as $dir) {
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
    }
}

// Crear directorios al cargar la configuración
createRequiredDirectories();
