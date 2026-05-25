-- Base de datos para pruebas de integración en Git / CI
-- Proyecto: Sistema de gestión de envíos y tracking
-- Compatible con MySQL 8.x / MariaDB 10.x
-- Usuario de prueba para login:
--   admin@sistema.com / 123456
--   operador@sistema.com / 123456
--   cliente@sistema.com / 123456

SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
SET time_zone = '+00:00';
SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS `sistema_envios`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `sistema_envios`;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `historia_estado`;
DROP TABLE IF EXISTS `envio`;
DROP TABLE IF EXISTS `usuario`;
DROP TABLE IF EXISTS `estado`;
DROP TABLE IF EXISTS `rol`;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE `rol` (
  `id_rol` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id_rol`),
  UNIQUE KEY `uk_rol_nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `estado` (
  `id_estado` INT NOT NULL AUTO_INCREMENT,
  `nombre_estado` VARCHAR(100) NOT NULL,
  `descripcion` VARCHAR(255) DEFAULT NULL,
  `orden_estado` INT NOT NULL,
  `es_final` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_estado`),
  UNIQUE KEY `uk_estado_nombre` (`nombre_estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `usuario` (
  `id_usuario` INT NOT NULL AUTO_INCREMENT,
  `id_rol` INT NOT NULL,
  `nombres` VARCHAR(100) NOT NULL,
  `apellidos` VARCHAR(100) NOT NULL,
  `correo` VARCHAR(120) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `telefono` VARCHAR(20) DEFAULT NULL,
  `direccion` VARCHAR(255) DEFAULT NULL,
  `estado` VARCHAR(20) NOT NULL DEFAULT 'activo',
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `uk_usuario_correo` (`correo`),
  KEY `idx_usuario_rol` (`id_rol`),
  CONSTRAINT `fk_usuario_rol`
    FOREIGN KEY (`id_rol`) REFERENCES `rol` (`id_rol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `envio` (
  `id_envio` INT NOT NULL AUTO_INCREMENT,
  `codigo_guia` VARCHAR(40) NOT NULL,
  `id_usuario_remitente` INT NOT NULL,
  `nombre_remitente` VARCHAR(150) NOT NULL,
  `telefono_remitente` VARCHAR(20) DEFAULT NULL,
  `direccion_remitente` VARCHAR(255) DEFAULT NULL,
  `nombre_destinatario` VARCHAR(100) NOT NULL,
  `telefono_destinatario` VARCHAR(20) NOT NULL,
  `direccion_destinatario` VARCHAR(255) NOT NULL,
  `descripcion_paquete` VARCHAR(255) NOT NULL,
  `peso` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `es_fragil` TINYINT(1) NOT NULL DEFAULT 0,
  `tipo_paquete` VARCHAR(80) DEFAULT NULL,
  `fecha_registro` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `estado_actual_id` INT NOT NULL,
  `observaciones` VARCHAR(255) DEFAULT NULL,
  `instrucciones_entrega` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id_envio`),
  UNIQUE KEY `uk_envio_codigo_guia` (`codigo_guia`),
  KEY `idx_envio_usuario_remitente` (`id_usuario_remitente`),
  KEY `idx_envio_estado_actual` (`estado_actual_id`),
  CONSTRAINT `fk_envio_usuario`
    FOREIGN KEY (`id_usuario_remitente`) REFERENCES `usuario` (`id_usuario`),
  CONSTRAINT `fk_envio_estado_actual`
    FOREIGN KEY (`estado_actual_id`) REFERENCES `estado` (`id_estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `historia_estado` (
  `id_historial` INT NOT NULL AUTO_INCREMENT,
  `id_envio` INT NOT NULL,
  `id_estado` INT NOT NULL,
  `id_usuario` INT NOT NULL,
  `fecha_hora` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `comentario` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id_historial`),
  KEY `idx_historial_envio` (`id_envio`),
  KEY `idx_historial_estado` (`id_estado`),
  KEY `idx_historial_usuario` (`id_usuario`),
  CONSTRAINT `fk_historial_envio`
    FOREIGN KEY (`id_envio`) REFERENCES `envio` (`id_envio`) ON DELETE CASCADE,
  CONSTRAINT `fk_historial_estado`
    FOREIGN KEY (`id_estado`) REFERENCES `estado` (`id_estado`),
  CONSTRAINT `fk_historial_usuario`
    FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `rol` (`id_rol`, `nombre`) VALUES
(1, 'Administrador'),
(2, 'Operador'),
(3, 'cliente');

INSERT INTO `estado` (`id_estado`, `nombre_estado`, `descripcion`, `orden_estado`, `es_final`) VALUES
(1, 'Paquete registrado', 'El paquete fue registrado en el sistema', 1, 0),
(2, 'En oficina', 'El paquete se encuentra en oficina', 2, 0),
(3, 'En proceso de ruta', 'El paquete está siendo preparado para salir a ruta', 3, 0),
(4, 'En ruta', 'El paquete va camino al destino', 4, 0),
(5, 'Entregado a usuario', 'El paquete fue entregado al destinatario', 5, 1),
(6, 'En sede para recoger', 'El paquete está disponible para recoger', 5, 0),
(7, 'Entregado en sede', 'El usuario recogió el paquete en sede', 6, 1),
(8, 'Cancelado', 'El envío fue cancelado', 99, 1);

INSERT INTO `usuario` (
  `id_usuario`,
  `id_rol`,
  `nombres`,
  `apellidos`,
  `correo`,
  `password_hash`,
  `telefono`,
  `direccion`,
  `estado`
) VALUES
(1, 1, 'Admin', 'Sistema', 'admin@sistema.com', '$2y$12$6031a7eqOPi1nhaTvh3eSuM1.DxjelxPBHanmVcDpOO8qBZzEKQQS', '00000000', 'Sistema', 'activo'),
(2, 2, 'Operador', 'Sistema', 'operador@sistema.com', '$2y$12$6031a7eqOPi1nhaTvh3eSuM1.DxjelxPBHanmVcDpOO8qBZzEKQQS', '11111111', 'Oficina central', 'activo'),
(3, 3, 'Cliente', 'Prueba', 'cliente@sistema.com', '$2y$12$6031a7eqOPi1nhaTvh3eSuM1.DxjelxPBHanmVcDpOO8qBZzEKQQS', '22222222', 'Zona 1 Guatemala', 'activo');

INSERT INTO `envio` (
  `id_envio`,
  `codigo_guia`,
  `id_usuario_remitente`,
  `nombre_remitente`,
  `telefono_remitente`,
  `direccion_remitente`,
  `nombre_destinatario`,
  `telefono_destinatario`,
  `direccion_destinatario`,
  `descripcion_paquete`,
  `peso`,
  `es_fragil`,
  `tipo_paquete`,
  `fecha_registro`,
  `estado_actual_id`,
  `observaciones`,
  `instrucciones_entrega`
) VALUES
(1, 'TEST-REGISTRADO-001', 3, 'Cliente Prueba', '22222222', 'Zona 1 Guatemala', 'Destinatario Uno', '55550001', 'Zona 10 Guatemala', 'Caja pequeña para prueba', 2.50, 1, 'Caja', '2026-01-01 08:00:00', 1, 'Prueba de integración', 'Entregar en recepción'),
(2, 'TEST-RUTA-002', 3, 'Cliente Prueba', '22222222', 'Zona 1 Guatemala', 'Destinatario Dos', '55550002', 'Zona 11 Guatemala', 'Documentos para prueba', 0.20, 0, 'Sobre', '2026-01-01 09:00:00', 4, 'Envío en ruta para pruebas', 'Llamar antes de entregar'),
(3, 'TEST-ENTREGADO-003', 2, 'Remitente Manual Operador', '33333333', 'Oficina central', 'Destinatario Tres', '55550003', 'Zona 12 Guatemala', 'Paquete entregado para prueba', 1.00, 0, 'Caja', '2026-01-01 10:00:00', 5, 'Envío finalizado', 'Sin instrucciones');

INSERT INTO `historia_estado` (
  `id_historial`,
  `id_envio`,
  `id_estado`,
  `id_usuario`,
  `fecha_hora`,
  `comentario`
) VALUES
(1, 1, 1, 3, '2026-01-01 08:00:00', 'Envío registrado en el sistema'),
(2, 2, 1, 3, '2026-01-01 09:00:00', 'Envío registrado en el sistema'),
(3, 2, 4, 2, '2026-01-01 09:30:00', 'Estado actualizado desde prueba'),
(4, 3, 1, 2, '2026-01-01 10:00:00', 'Envío registrado por operador'),
(5, 3, 5, 2, '2026-01-01 10:30:00', 'Entrega confirmada');

ALTER TABLE `rol` AUTO_INCREMENT = 4;
ALTER TABLE `estado` AUTO_INCREMENT = 9;
ALTER TABLE `usuario` AUTO_INCREMENT = 4;
ALTER TABLE `envio` AUTO_INCREMENT = 4;
ALTER TABLE `historia_estado` AUTO_INCREMENT = 6;

COMMIT;
