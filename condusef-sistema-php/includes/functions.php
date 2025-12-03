<?php
/**
 * CONDUSEF - Funciones Útiles Generales
 */

/**
 * Genera respuesta JSON para APIs
 * @param bool $success Estado de la operación
 * @param mixed $data Datos a retornar
 * @param string $message Mensaje descriptivo
 */
function jsonResponse($success, $data = null, $message = '') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message
    ]);
    exit();
}

/**
 * Redirige a una URL
 * @param string $url URL de destino
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * Formatea fecha en español
 * @param string $date Fecha en formato MySQL
 * @param bool $includeTime Incluir hora
 * @return string Fecha formateada
 */
function formatDate($date, $includeTime = false) {
    if (!$date || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
        return 'N/A';
    }

    $timestamp = strtotime($date);
    if ($includeTime) {
        return date('d/m/Y H:i', $timestamp);
    }
    return date('d/m/Y', $timestamp);
}

/**
 * Formatea fecha en español largo
 * @param string $date Fecha en formato MySQL
 * @return string Fecha formateada
 */
function formatDateLong($date) {
    if (!$date || $date === '0000-00-00') {
        return 'N/A';
    }

    $meses = [
        1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
        'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'
    ];

    $timestamp = strtotime($date);
    $dia = date('j', $timestamp);
    $mes = $meses[(int)date('n', $timestamp)];
    $anio = date('Y', $timestamp);

    return "$dia de $mes de $anio";
}

/**
 * Formatea cantidad monetaria
 * @param float $amount Cantidad
 * @return string Cantidad formateada
 */
function formatMoney($amount) {
    if ($amount === null || $amount === '') {
        return '$0.00';
    }
    return '$' . number_format($amount, 2, '.', ',');
}

/**
 * Genera folio único para caso
 * @return string Folio generado
 */
function generateFolio() {
    $year = date('Y');
    $month = date('m');

    // Contar casos del mes actual
    $sql = "SELECT COUNT(*) as total FROM casos WHERE YEAR(fecha_creacion) = ? AND MONTH(fecha_creacion) = ?";
    $result = queryOne($sql, [$year, $month]);
    $consecutivo = ($result['total'] ?? 0) + 1;

    return sprintf('COND-%s%s-%04d', $year, $month, $consecutivo);
}

/**
 * Obtiene nombre del estado del caso
 * @param string $estado Estado del caso
 * @return string Nombre legible
 */
function getNombreEstado($estado) {
    $estados = ESTADOS_CASOS;
    return $estados[$estado] ?? $estado;
}

/**
 * Obtiene clase CSS para estado
 * @param string $estado Estado del caso
 * @return string Clase CSS
 */
function getEstadoClass($estado) {
    $classes = [
        'nuevo' => 'badge bg-info',
        'en_proceso' => 'badge bg-primary',
        'presentado_une' => 'badge bg-warning text-dark',
        'presentado_condusef' => 'badge bg-warning text-dark',
        'conciliacion' => 'badge bg-secondary',
        'resuelto' => 'badge bg-success',
        'cerrado' => 'badge bg-dark'
    ];
    return $classes[$estado] ?? 'badge bg-secondary';
}

/**
 * Obtiene clase CSS para prioridad
 * @param string $prioridad Prioridad del caso
 * @return string Clase CSS
 */
function getPrioridadClass($prioridad) {
    $classes = [
        'baja' => 'badge bg-secondary',
        'media' => 'badge bg-info',
        'alta' => 'badge bg-warning text-dark',
        'urgente' => 'badge bg-danger'
    ];
    return $classes[$prioridad] ?? 'badge bg-secondary';
}

/**
 * Obtiene nombre del rol
 * @param string $rol Rol del usuario
 * @return string Nombre legible
 */
function getNombreRol($rol) {
    $roles = ROLES;
    return $roles[$rol] ?? $rol;
}

/**
 * Convierte bytes a formato legible
 * @param int $bytes Bytes
 * @return string Tamaño formateado
 */
function formatBytes($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

/**
 * Obtiene icono para tipo de archivo
 * @param string $extension Extensión del archivo
 * @return string Clase de icono
 */
function getFileIcon($extension) {
    $icons = [
        'pdf' => 'bi-file-pdf text-danger',
        'doc' => 'bi-file-word text-primary',
        'docx' => 'bi-file-word text-primary',
        'xls' => 'bi-file-excel text-success',
        'xlsx' => 'bi-file-excel text-success',
        'jpg' => 'bi-file-image text-info',
        'jpeg' => 'bi-file-image text-info',
        'png' => 'bi-file-image text-info'
    ];
    return $icons[strtolower($extension)] ?? 'bi-file-earmark';
}

/**
 * Genera paginación
 * @param int $total Total de registros
 * @param int $page Página actual
 * @param int $perPage Registros por página
 * @return array Datos de paginación
 */
function paginate($total, $page = 1, $perPage = ITEMS_PER_PAGE) {
    $totalPages = ceil($total / $perPage);
    $page = max(1, min($page, $totalPages));
    $offset = ($page - 1) * $perPage;

    return [
        'total' => $total,
        'per_page' => $perPage,
        'current_page' => $page,
        'total_pages' => $totalPages,
        'offset' => $offset,
        'has_prev' => $page > 1,
        'has_next' => $page < $totalPages
    ];
}

/**
 * Renderiza paginación HTML
 * @param array $pagination Datos de paginación
 * @param string $baseUrl URL base para links
 * @return string HTML de paginación
 */
function renderPagination($pagination, $baseUrl) {
    if ($pagination['total_pages'] <= 1) {
        return '';
    }

    $html = '<nav aria-label="Paginación"><ul class="pagination justify-content-center">';

    // Botón anterior
    if ($pagination['has_prev']) {
        $prevPage = $pagination['current_page'] - 1;
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=' . $prevPage . '">Anterior</a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link">Anterior</span></li>';
    }

    // Páginas numeradas
    $start = max(1, $pagination['current_page'] - 2);
    $end = min($pagination['total_pages'], $pagination['current_page'] + 2);

    if ($start > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=1">1</a></li>';
        if ($start > 2) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }

    for ($i = $start; $i <= $end; $i++) {
        if ($i == $pagination['current_page']) {
            $html .= '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
        } else {
            $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=' . $i . '">' . $i . '</a></li>';
        }
    }

    if ($end < $pagination['total_pages']) {
        if ($end < $pagination['total_pages'] - 1) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=' . $pagination['total_pages'] . '">' . $pagination['total_pages'] . '</a></li>';
    }

    // Botón siguiente
    if ($pagination['has_next']) {
        $nextPage = $pagination['current_page'] + 1;
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=' . $nextPage . '">Siguiente</a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link">Siguiente</span></li>';
    }

    $html .= '</ul></nav>';
    return $html;
}

/**
 * Trunca texto
 * @param string $text Texto
 * @param int $length Longitud máxima
 * @return string Texto truncado
 */
function truncate($text, $length = 100) {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . '...';
}

/**
 * Calcula diferencia de días entre fechas
 * @param string $date1 Primera fecha
 * @param string $date2 Segunda fecha (opcional, por defecto hoy)
 * @return int Días de diferencia
 */
function daysDiff($date1, $date2 = null) {
    $d1 = new DateTime($date1);
    $d2 = $date2 ? new DateTime($date2) : new DateTime();
    return $d1->diff($d2)->days;
}

/**
 * Verifica si una fecha está vencida
 * @param string $date Fecha a verificar
 * @return bool True si está vencida
 */
function isOverdue($date) {
    if (!$date || $date === '0000-00-00') {
        return false;
    }
    return strtotime($date) < time();
}

/**
 * Obtiene usuario actual
 * @return array|null Datos del usuario
 */
function getCurrentUser() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    $sql = "SELECT id, nombre, email, rol, telefono, foto_perfil FROM usuarios WHERE id = ? AND activo = 1";
    return queryOne($sql, [$_SESSION['user_id']]);
}

/**
 * Obtiene iniciales de un nombre
 * @param string $nombre Nombre completo
 * @return string Iniciales
 */
function getInitials($nombre) {
    $parts = explode(' ', $nombre);
    $initials = '';

    foreach ($parts as $part) {
        if (!empty($part)) {
            $initials .= strtoupper(substr($part, 0, 1));
            if (strlen($initials) >= 2) break;
        }
    }

    return $initials ?: 'NA';
}

/**
 * Genera color aleatorio para avatar
 * @param string $string String para generar color
 * @return string Color hexadecimal
 */
function stringToColor($string) {
    $hash = md5($string);
    return '#' . substr($hash, 0, 6);
}
