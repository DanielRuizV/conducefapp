<?php
/**
 * CONDUSEF - Configuración de Base de Datos
 * Conexión PDO segura con MySQL
 */

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'condusef_db');
define('DB_USER', 'admin1');
define('DB_PASS', 'uIli[q+0H6@Y');
define('DB_CHARSET', 'utf8mb4');

/**
 * Clase Database - Singleton pattern para conexión PDO
 */
class Database {
    private static $instance = null;
    private $connection;

    /**
     * Constructor privado para Singleton
     */
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
            ];

            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);

        } catch (PDOException $e) {
            error_log("Error de conexión a la base de datos: " . $e->getMessage());
            die("Error al conectar con la base de datos. Por favor contacte al administrador.");
        }
    }

    /**
     * Obtiene la instancia única de Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Obtiene la conexión PDO
     */
    public function getConnection() {
        return $this->connection;
    }

    /**
     * Previene la clonación del objeto
     */
    private function __clone() {}

    /**
     * Previene la deserialización del objeto
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

/**
 * Función helper para obtener la conexión PDO
 * @return PDO
 */
function getDB() {
    return Database::getInstance()->getConnection();
}

/**
 * Ejecuta una consulta preparada y retorna los resultados
 * @param string $sql Query SQL con placeholders
 * @param array $params Parámetros para la consulta
 * @return array Resultados de la consulta
 */
function query($sql, $params = []) {
    try {
        $db = getDB();
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error en query: " . $e->getMessage());
        return [];
    }
}

/**
 * Ejecuta una consulta preparada y retorna un solo resultado
 * @param string $sql Query SQL con placeholders
 * @param array $params Parámetros para la consulta
 * @return array|false Resultado de la consulta
 */
function queryOne($sql, $params = []) {
    try {
        $db = getDB();
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error en queryOne: " . $e->getMessage());
        return false;
    }
}

/**
 * Ejecuta una consulta INSERT, UPDATE o DELETE
 * @param string $sql Query SQL con placeholders
 * @param array $params Parámetros para la consulta
 * @return bool|int True si fue exitoso, o el ID del registro insertado
 */
function execute($sql, $params = []) {
    try {
        $db = getDB();
        $stmt = $db->prepare($sql);
        $success = $stmt->execute($params);

        // Si es un INSERT, retorna el ID insertado
        if (stripos(trim($sql), 'INSERT') === 0) {
            return $db->lastInsertId();
        }

        return $success;
    } catch (PDOException $e) {
        error_log("Error en execute: " . $e->getMessage());
        return false;
    }
}

/**
 * Obtiene el conteo de registros
 * @param string $sql Query SQL con placeholders
 * @param array $params Parámetros para la consulta
 * @return int Número de registros
 */
function queryCount($sql, $params = []) {
    try {
        $db = getDB();
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    } catch (PDOException $e) {
        error_log("Error en queryCount: " . $e->getMessage());
        return 0;
    }
}

/**
 * Inicia una transacción
 */
function beginTransaction() {
    return getDB()->beginTransaction();
}

/**
 * Confirma una transacción
 */
function commit() {
    return getDB()->commit();
}

/**
 * Revierte una transacción
 */
function rollback() {
    return getDB()->rollBack();
}
