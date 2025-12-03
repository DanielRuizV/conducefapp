<?php
/**
 * CONDUSEF - API Eliminar Cliente
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../../includes/functions.php';

session_start();
setSecurityHeaders();

// Verificar autenticación y rol
requireRole(['admin']);

// Solo permitir POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, null, 'Método no permitido');
}

try {
    // Validar CSRF
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        jsonResponse(false, null, 'Token de seguridad inválido');
    }

    $clienteId = (int)($_POST['id'] ?? 0);

    if (!$clienteId) {
        jsonResponse(false, null, 'ID de cliente inválido');
    }

    // Verificar que el cliente existe
    $cliente = queryOne("SELECT * FROM clientes WHERE id = ?", [$clienteId]);
    if (!$cliente) {
        jsonResponse(false, null, 'Cliente no encontrado');
    }

    // Verificar si tiene casos asociados
    $casosCuenta = queryOne("SELECT COUNT(*) as total FROM casos WHERE cliente_id = ?", [$clienteId]);
    if ($casosCuenta['total'] > 0) {
        jsonResponse(false, null, 'No se puede eliminar el cliente porque tiene casos asociados');
    }

    // Eliminar cliente
    $sql = "DELETE FROM clientes WHERE id = ?";
    $success = execute($sql, [$clienteId]);

    if (!$success) {
        jsonResponse(false, null, 'Error al eliminar el cliente');
    }

    // Registrar en auditoría
    registrarAuditoria('eliminar_cliente', 'clientes', $clienteId, [
        'nombre_completo' => $cliente['nombre_completo'],
        'email' => $cliente['email']
    ], null);

    jsonResponse(true, null, 'Cliente eliminado exitosamente');

} catch (Exception $e) {
    error_log("Error al eliminar cliente: " . $e->getMessage());
    jsonResponse(false, null, 'Error al procesar la solicitud');
}
