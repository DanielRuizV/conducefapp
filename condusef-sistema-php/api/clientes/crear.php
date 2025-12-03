<?php
/**
 * CONDUSEF - API Crear Cliente
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../../includes/functions.php';

session_start();
setSecurityHeaders();

// Verificar autenticación
requireLogin();

// Solo permitir POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, null, 'Método no permitido');
}

try {
    // Validar CSRF
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        jsonResponse(false, null, 'Token de seguridad inválido');
    }

    // Obtener y sanitizar datos
    $nombreCompleto = sanitizeText($_POST['nombre_completo'] ?? '');
    $email = sanitizeEmail($_POST['email'] ?? '');
    $telefono = sanitizeText($_POST['telefono'] ?? '');
    $telefonoAlternativo = sanitizeText($_POST['telefono_alternativo'] ?? '');
    $curp = sanitizeText($_POST['curp'] ?? '');
    $rfc = sanitizeText($_POST['rfc'] ?? '');
    $fechaNacimiento = $_POST['fecha_nacimiento'] ?? null;
    $domicilioCalle = sanitizeText($_POST['domicilio_calle'] ?? '');
    $domicilioNumero = sanitizeText($_POST['domicilio_numero'] ?? '');
    $domicilioColonia = sanitizeText($_POST['domicilio_colonia'] ?? '');
    $domicilioCiudad = sanitizeText($_POST['domicilio_ciudad'] ?? '');
    $domicilioEstado = sanitizeText($_POST['domicilio_estado'] ?? '');
    $domicilioCp = sanitizeText($_POST['domicilio_cp'] ?? '');
    $notas = sanitizeText($_POST['notas'] ?? '');

    // Validaciones
    if (empty($nombreCompleto)) {
        jsonResponse(false, null, 'El nombre completo es requerido');
    }

    if (!empty($email) && !validateEmail($email)) {
        jsonResponse(false, null, 'El email no es válido');
    }

    // Verificar si el email ya existe
    if (!empty($email)) {
        $existeEmail = queryOne("SELECT id FROM clientes WHERE email = ?", [$email]);
        if ($existeEmail) {
            jsonResponse(false, null, 'El email ya está registrado');
        }
    }

    // Insertar cliente
    $sql = "INSERT INTO clientes (
                nombre_completo, email, telefono, telefono_alternativo, curp, rfc,
                fecha_nacimiento, domicilio_calle, domicilio_numero, domicilio_colonia,
                domicilio_ciudad, domicilio_estado, domicilio_cp, notas, creado_por
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $clienteId = execute($sql, [
        $nombreCompleto,
        $email ?: null,
        $telefono ?: null,
        $telefonoAlternativo ?: null,
        $curp ?: null,
        $rfc ?: null,
        $fechaNacimiento ?: null,
        $domicilioCalle ?: null,
        $domicilioNumero ?: null,
        $domicilioColonia ?: null,
        $domicilioCiudad ?: null,
        $domicilioEstado ?: null,
        $domicilioCp ?: null,
        $notas ?: null,
        $_SESSION['user_id']
    ]);

    if (!$clienteId) {
        jsonResponse(false, null, 'Error al crear el cliente');
    }

    // Registrar en auditoría
    registrarAuditoria('crear_cliente', 'clientes', $clienteId, null, [
        'nombre_completo' => $nombreCompleto,
        'email' => $email
    ]);

    jsonResponse(true, ['id' => $clienteId], 'Cliente creado exitosamente');

} catch (Exception $e) {
    error_log("Error en crear cliente: " . $e->getMessage());
    jsonResponse(false, null, 'Error al procesar la solicitud');
}
