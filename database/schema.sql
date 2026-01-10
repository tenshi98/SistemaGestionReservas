-- ============================================================================
-- Schema de Base de Datos para Sistema de Gestión de Reservas
-- Motor: MySQL 5.7+
-- Descripción: Estructura completa para almacenar datos
-- ============================================================================

-- Crear base de datos si no existe
CREATE DATABASE IF NOT EXISTS SistemaGestionReservas
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE SistemaGestionReservas;

-- ============================================
-- Tabla: tokens_listado
-- Descripción: Almacena los tokens autorizados para acceder a la API
-- ============================================
CREATE TABLE IF NOT EXISTS tokens_listado (
    idToken INT UNSIGNED NOT NULL AUTO_INCREMENT,
    Token VARCHAR(255) NOT NULL,
    Nombre VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (idToken),
    UNIQUE KEY unique_token (Token),
    INDEX idx_token (Token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Tokens autorizados para acceso a la API';

-- ============================================
-- Tabla: sucursal_listado
-- Descripción: Almacena las sucursales disponibles
-- ============================================
CREATE TABLE IF NOT EXISTS sucursal_listado (
    idSucursal INT UNSIGNED NOT NULL AUTO_INCREMENT,
    Nombre VARCHAR(255) NOT NULL,
    PRIMARY KEY (idSucursal),
    UNIQUE KEY unique_nombre (Nombre),
    INDEX idx_nombre (Nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Listado de sucursales';

-- ============================================
-- Tabla: reservas_listado
-- Descripción: Almacena las reservas realizadas
-- ============================================
CREATE TABLE IF NOT EXISTS reservas_listado (
    idReservas INT UNSIGNED NOT NULL AUTO_INCREMENT,
    idToken INT UNSIGNED NOT NULL,
    Nombre VARCHAR(255) NOT NULL DEFAULT '',
    ApellidoPat VARCHAR(255) NOT NULL DEFAULT '',
    ApellidoMat VARCHAR(255) DEFAULT NULL,
    NombreCompleto VARCHAR(255) NOT NULL,
    Email VARCHAR(255) NOT NULL,
    idSucursal INT UNSIGNED NOT NULL,
    Etapa VARCHAR(255) NOT NULL,
    Fecha DATE NOT NULL DEFAULT '0000-00-00',
    FechaAcceso TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    idSendMail INT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (idReservas),
    INDEX idx_email_fecha (Email, Fecha),
    INDEX idx_token (idToken),
    INDEX idx_sucursal (idSucursal),
    INDEX idx_fecha (Fecha),
    INDEX idx_etapa (Etapa),
    INDEX idx_sendmail (idSendMail),
    FOREIGN KEY (idToken) REFERENCES tokens_listado(idToken) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (idSucursal) REFERENCES sucursal_listado(idSucursal) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Listado de reservas';

-- ============================================
-- Datos de Ejemplo (Opcional)
-- ============================================

-- Insertar token de ejemplo
INSERT INTO tokens_listado (Token, Nombre) VALUES
('ejemplo_token_123456789', 'Token de Prueba'),
('api_key_production_xyz', 'Token de Producción')
ON DUPLICATE KEY UPDATE Nombre = VALUES(Nombre);

-- Insertar sucursales de ejemplo
INSERT INTO sucursal_listado (Nombre) VALUES
('Sucursal Centro'),
('Sucursal Norte'),
('Sucursal Sur')
ON DUPLICATE KEY UPDATE Nombre = VALUES(Nombre);

-- ============================================
-- Verificación
-- ============================================

-- Mostrar tablas creadas
SHOW TABLES;

-- Mostrar estructura de cada tabla
DESCRIBE tokens_listado;
DESCRIBE sucursal_listado;
DESCRIBE reservas_listado;

-- Mostrar datos de ejemplo
SELECT * FROM tokens_listado;
SELECT * FROM sucursal_listado;
