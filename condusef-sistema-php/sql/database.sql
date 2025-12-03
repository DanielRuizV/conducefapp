-- =====================================================
-- CONDUSEF - Sistema de Gestión de Casos
-- Base de Datos MySQL
-- =====================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS `condusef_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `condusef_db`;

-- =====================================================
-- TABLA: usuarios
-- =====================================================
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('admin','abogado','asistente','cliente') NOT NULL DEFAULT 'asistente',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `telefono` varchar(20) DEFAULT NULL,
  `foto_perfil` varchar(255) DEFAULT NULL,
  `ultimo_acceso` datetime DEFAULT NULL,
  `intentos_login` int(11) NOT NULL DEFAULT 0,
  `bloqueado_hasta` datetime DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_rol` (`rol`),
  KEY `idx_activo` (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: clientes
-- =====================================================
CREATE TABLE `clientes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_completo` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `telefono_alternativo` varchar(20) DEFAULT NULL,
  `curp` varchar(18) DEFAULT NULL,
  `rfc` varchar(13) DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `domicilio_calle` varchar(255) DEFAULT NULL,
  `domicilio_numero` varchar(20) DEFAULT NULL,
  `domicilio_colonia` varchar(100) DEFAULT NULL,
  `domicilio_ciudad` varchar(100) DEFAULT NULL,
  `domicilio_estado` varchar(100) DEFAULT NULL,
  `domicilio_cp` varchar(10) DEFAULT NULL,
  `notas` text DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_por` int(11) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_nombre` (`nombre_completo`),
  KEY `idx_email` (`email`),
  KEY `idx_curp` (`curp`),
  KEY `fk_clientes_creado_por` (`creado_por`),
  CONSTRAINT `fk_clientes_creado_por` FOREIGN KEY (`creado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: aseguradoras
-- =====================================================
CREATE TABLE `aseguradoras` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `razon_social` varchar(255) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `sitio_web` varchar(255) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `activa` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_nombre` (`nombre`),
  KEY `idx_activa` (`activa`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: casos
-- =====================================================
CREATE TABLE `casos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `folio` varchar(50) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL COMMENT 'Usuario asignado al caso',
  `aseguradora_id` int(11) DEFAULT NULL,
  `numero_poliza` varchar(100) DEFAULT NULL,
  `tipo_seguro` varchar(100) DEFAULT NULL,
  `fecha_siniestro` date DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `monto_reclamado` decimal(15,2) DEFAULT NULL,
  `monto_ofrecido` decimal(15,2) DEFAULT NULL,
  `monto_recuperado` decimal(15,2) DEFAULT NULL,
  `estado` enum('nuevo','en_proceso','presentado_une','presentado_condusef','conciliacion','resuelto','cerrado') NOT NULL DEFAULT 'nuevo',
  `prioridad` enum('baja','media','alta','urgente') NOT NULL DEFAULT 'media',
  `fecha_limite` date DEFAULT NULL,
  `notas_internas` text DEFAULT NULL,
  `creado_por` int(11) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `fecha_cierre` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `folio` (`folio`),
  KEY `idx_estado` (`estado`),
  KEY `idx_fecha_siniestro` (`fecha_siniestro`),
  KEY `idx_prioridad` (`prioridad`),
  KEY `fk_casos_cliente` (`cliente_id`),
  KEY `fk_casos_usuario` (`usuario_id`),
  KEY `fk_casos_aseguradora` (`aseguradora_id`),
  KEY `fk_casos_creado_por` (`creado_por`),
  CONSTRAINT `fk_casos_aseguradora` FOREIGN KEY (`aseguradora_id`) REFERENCES `aseguradoras` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_casos_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_casos_creado_por` FOREIGN KEY (`creado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_casos_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: cuestionarios
-- =====================================================
CREATE TABLE `cuestionarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `caso_id` int(11) NOT NULL,
  `datos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`datos`)),
  `progreso` int(11) NOT NULL DEFAULT 0 COMMENT 'Porcentaje de completado (0-100)',
  `completado` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_completado` datetime DEFAULT NULL,
  `ultima_seccion` varchar(100) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `caso_id` (`caso_id`),
  CONSTRAINT `fk_cuestionarios_caso` FOREIGN KEY (`caso_id`) REFERENCES `casos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: documentos
-- =====================================================
CREATE TABLE `documentos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `caso_id` int(11) NOT NULL,
  `categoria` varchar(100) NOT NULL COMMENT 'Ej: identificacion, poliza, siniestro, comunicaciones, otros',
  `nombre_archivo` varchar(255) NOT NULL,
  `nombre_original` varchar(255) NOT NULL,
  `ruta_archivo` varchar(500) NOT NULL,
  `tipo_mime` varchar(100) DEFAULT NULL,
  `tamano_kb` int(11) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `subido_por` int(11) DEFAULT NULL,
  `fecha_subida` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_caso` (`caso_id`),
  KEY `idx_categoria` (`categoria`),
  KEY `fk_documentos_subido_por` (`subido_por`),
  CONSTRAINT `fk_documentos_caso` FOREIGN KEY (`caso_id`) REFERENCES `casos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_documentos_subido_por` FOREIGN KEY (`subido_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: seguimientos
-- =====================================================
CREATE TABLE `seguimientos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `caso_id` int(11) NOT NULL,
  `tipo_actividad` varchar(100) NOT NULL COMMENT 'Ej: nota, llamada, email, presentacion, audiencia, resolucion',
  `titulo` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_actividad` datetime NOT NULL,
  `realizado_por` int(11) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_caso` (`caso_id`),
  KEY `idx_fecha_actividad` (`fecha_actividad`),
  KEY `idx_tipo` (`tipo_actividad`),
  KEY `fk_seguimientos_usuario` (`realizado_por`),
  CONSTRAINT `fk_seguimientos_caso` FOREIGN KEY (`caso_id`) REFERENCES `casos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_seguimientos_usuario` FOREIGN KEY (`realizado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: historial_comunicaciones
-- =====================================================
CREATE TABLE `historial_comunicaciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `caso_id` int(11) NOT NULL,
  `fecha` datetime NOT NULL,
  `tipo` enum('llamada','email','whatsapp','presencial','oficio','otro') NOT NULL,
  `direccion` enum('entrante','saliente') NOT NULL DEFAULT 'saliente',
  `con_quien` varchar(255) DEFAULT NULL COMMENT 'Nombre de la persona contactada',
  `cargo` varchar(255) DEFAULT NULL,
  `institucion` varchar(255) DEFAULT NULL COMMENT 'Aseguradora, CONDUSEF, etc.',
  `asunto` varchar(255) DEFAULT NULL,
  `resumen` text DEFAULT NULL,
  `adjuntos` text DEFAULT NULL COMMENT 'JSON con rutas de archivos adjuntos',
  `registrado_por` int(11) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_caso` (`caso_id`),
  KEY `idx_fecha` (`fecha`),
  KEY `idx_tipo` (`tipo`),
  KEY `fk_comunicaciones_usuario` (`registrado_por`),
  CONSTRAINT `fk_comunicaciones_caso` FOREIGN KEY (`caso_id`) REFERENCES `casos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_comunicaciones_usuario` FOREIGN KEY (`registrado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: auditoria
-- =====================================================
CREATE TABLE `auditoria` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `accion` varchar(100) NOT NULL COMMENT 'Ej: login, logout, crear_caso, editar_cliente, etc.',
  `tabla_afectada` varchar(100) DEFAULT NULL,
  `registro_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `datos_anteriores` text DEFAULT NULL COMMENT 'JSON con datos antes del cambio',
  `datos_nuevos` text DEFAULT NULL COMMENT 'JSON con datos después del cambio',
  `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_usuario` (`usuario_id`),
  KEY `idx_accion` (`accion`),
  KEY `idx_fecha` (`fecha_creacion`),
  CONSTRAINT `fk_auditoria_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- DATOS INICIALES: Usuario Admin
-- =====================================================
-- Password: admin123 (hasheado con bcrypt)
INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password`, `rol`, `activo`, `telefono`) VALUES
(1, 'Administrador', 'admin@condusef.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1, NULL);

-- =====================================================
-- DATOS INICIALES: 18 Aseguradoras Mexicanas
-- =====================================================
INSERT INTO `aseguradoras` (`id`, `nombre`, `razon_social`, `telefono`, `email`, `sitio_web`, `activa`) VALUES
(1, 'AXA Seguros', 'AXA Seguros S.A. de C.V.', '55-5169-1000', 'atencion@axa.com.mx', 'https://www.axa.mx', 1),
(2, 'GNP Seguros', 'Grupo Nacional Provincial S.A.B.', '55-5227-9000', 'atencion@gnp.com.mx', 'https://www.gnp.com.mx', 1),
(3, 'Qualitas', 'Qualitas Compañía de Seguros S.A. de C.V.', '55-5000-5500', 'atencion@qualitas.com.mx', 'https://www.qualitas.com.mx', 1),
(4, 'Mapfre', 'Mapfre Tepeyac S.A.', '55-5328-9000', 'atencion@mapfre.com.mx', 'https://www.mapfre.com.mx', 1),
(5, 'Seguros Banorte', 'Seguros Banorte S.A. de C.V.', '55-1670-2999', 'atencion@banorte.com', 'https://www.segurosbanorte.com.mx', 1),
(6, 'HDI Seguros', 'HDI Seguros S.A. de C.V.', '55-4170-9300', 'atencion@hdi.com.mx', 'https://www.hdi.com.mx', 1),
(7, 'Inbursa', 'Seguros Inbursa S.A.', '55-5625-4500', 'atencion@inbursa.com', 'https://www.inbursa.com', 1),
(8, 'Zurich', 'Zurich Compañía de Seguros S.A.', '55-5284-6000', 'atencion@zurich.com.mx', 'https://www.zurich.com.mx', 1),
(9, 'Chubb Seguros', 'Chubb Seguros México S.A.', '55-5250-7500', 'atencion@chubb.com', 'https://www.chubb.com/mx', 1),
(10, 'Atlas', 'Seguros Atlas S.A.', '33-3669-1010', 'atencion@segurosatlas.com.mx', 'https://www.segurosatlas.com.mx', 1),
(11, 'ANA Seguros', 'ANA Compañía de Seguros S.A. de C.V.', '55-5169-7500', 'atencion@ana.com.mx', 'https://www.ana.com.mx', 1),
(12, 'Afirme Seguros', 'Afirme Grupo Financiero', '81-8318-7000', 'atencion@afirme.com', 'https://www.afirme.com', 1),
(13, 'Primero Seguros', 'Primero Seguros S.A. de C.V.', '55-5627-1000', 'atencion@primeroseguros.com', 'https://www.primeroseguros.com', 1),
(14, 'Plan Seguro', 'Plan Seguro Compañía de Seguros S.A. de C.V.', '55-5081-9000', 'atencion@planseguro.com.mx', 'https://www.planseguro.com.mx', 1),
(15, 'Monterrey New York Life', 'MNL Seguros S.A. de C.V.', '81-8155-5000', 'atencion@mnl.com.mx', 'https://www.mnl.com.mx', 1),
(16, 'Seguros Sura', 'Seguros Sura S.A. de C.V.', '55-5269-1010', 'atencion@sura.com.mx', 'https://www.segurossura.com.mx', 1),
(17, 'El Aguila', 'El Aguila Compañía de Seguros S.A. de C.V.', '33-3669-0200', 'atencion@elaguila.com.mx', 'https://www.elaguila.com.mx', 1),
(18, 'Metlife', 'MetLife México S.A.', '55-5328-7000', 'atencion@metlife.com.mx', 'https://www.metlife.com.mx', 1);

-- =====================================================
-- FIN DEL SCRIPT
-- =====================================================
