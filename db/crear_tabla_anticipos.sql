-- =====================================================
-- SCRIPT PARA CREAR TABLA DE ANTICIPOS
-- Base de datos: colfe_db
-- Motor: MariaDB
-- =====================================================

-- Crear tabla de anticipos
CREATE TABLE IF NOT EXISTS `tbl_anticipos` (
  `id_anticipo` int(11) NOT NULL AUTO_INCREMENT,
  `id_socio` int(11) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha_anticipo` date NOT NULL,
  `estado` enum('pendiente','aprobado','rechazado') NOT NULL DEFAULT 'pendiente',
  `observaciones` text DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `usuario_registro` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_anticipo`),
  KEY `fk_anticipos_socios` (`id_socio`),
  KEY `idx_fecha_anticipo` (`fecha_anticipo`),
  KEY `idx_estado` (`estado`),
  CONSTRAINT `fk_anticipos_socios` FOREIGN KEY (`id_socio`) REFERENCES `tbl_socios` (`id_socio`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- COMENTARIOS DE LA TABLA
-- =====================================================

-- Comentarios de las columnas
ALTER TABLE `tbl_anticipos` 
MODIFY COLUMN `id_anticipo` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador único del anticipo',
MODIFY COLUMN `id_socio` int(11) NOT NULL COMMENT 'ID del socio que solicita el anticipo',
MODIFY COLUMN `monto` decimal(10,2) NOT NULL COMMENT 'Monto del anticipo solicitado',
MODIFY COLUMN `fecha_anticipo` date NOT NULL COMMENT 'Fecha en que se solicita el anticipo',
MODIFY COLUMN `estado` enum('pendiente','aprobado','rechazado') NOT NULL DEFAULT 'pendiente' COMMENT 'Estado del anticipo',
MODIFY COLUMN `observaciones` text DEFAULT NULL COMMENT 'Observaciones adicionales del anticipo',
MODIFY COLUMN `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Fecha y hora de registro',
MODIFY COLUMN `usuario_registro` varchar(50) DEFAULT NULL COMMENT 'Usuario que registró el anticipo';

-- =====================================================
-- DATOS DE PRUEBA (OPCIONAL)
-- =====================================================

-- Insertar algunos anticipos de prueba
INSERT INTO `tbl_anticipos` (`id_socio`, `monto`, `fecha_anticipo`, `estado`, `observaciones`, `usuario_registro`) VALUES
(1, 500000.00, '2025-01-15', 'aprobado', 'Anticipo para gastos médicos', 'admin'),
(2, 300000.00, '2025-01-16', 'pendiente', 'Anticipo para reparaciones', 'admin'),
(3, 750000.00, '2025-01-17', 'aprobado', 'Anticipo para compra de insumos', 'admin'),
(1, 200000.00, '2025-01-18', 'rechazado', 'Anticipo adicional', 'admin'),
(4, 400000.00, '2025-01-19', 'pendiente', 'Anticipo para emergencia familiar', 'admin');

-- =====================================================
-- CONSULTAS DE VERIFICACIÓN
-- =====================================================

-- Verificar que la tabla se creó correctamente
SELECT 
    TABLE_NAME,
    TABLE_ROWS,
    DATA_LENGTH,
    INDEX_LENGTH
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'colfe_db' 
AND TABLE_NAME = 'tbl_anticipos';

-- Verificar la estructura de la tabla
DESCRIBE tbl_anticipos;

-- Verificar los datos insertados
SELECT 
    a.id_anticipo,
    CONCAT(s.nombre, ' ', s.apellido) as socio,
    s.identificacion,
    a.monto,
    a.fecha_anticipo,
    a.estado,
    a.observaciones,
    a.fecha_registro
FROM tbl_anticipos a
INNER JOIN tbl_socios s ON a.id_socio = s.id_socio
ORDER BY a.fecha_registro DESC;

-- =====================================================
-- ÍNDICES ADICIONALES PARA OPTIMIZACIÓN
-- =====================================================

-- Índice compuesto para consultas por socio y fecha
CREATE INDEX `idx_socio_fecha` ON `tbl_anticipos` (`id_socio`, `fecha_anticipo`);

-- Índice para consultas por estado y fecha
CREATE INDEX `idx_estado_fecha` ON `tbl_anticipos` (`estado`, `fecha_anticipo`);

-- =====================================================
-- TRIGGER PARA ACTUALIZAR FECHA DE REGISTRO
-- =====================================================

DELIMITER $$

CREATE TRIGGER `tr_anticipos_before_insert` 
BEFORE INSERT ON `tbl_anticipos`
FOR EACH ROW
BEGIN
    SET NEW.fecha_registro = NOW();
    IF NEW.usuario_registro IS NULL THEN
        SET NEW.usuario_registro = USER();
    END IF;
END$$

DELIMITER ;

-- =====================================================
-- VISTA PARA CONSULTAS FRECUENTES
-- =====================================================

-- Vista para mostrar anticipos con información del socio
CREATE OR REPLACE VIEW `v_anticipos_completos` AS
SELECT 
    a.id_anticipo,
    a.id_socio,
    CONCAT(s.nombre, ' ', s.apellido) as nombre_socio,
    s.identificacion,
    s.telefono,
    s.vinculacion,
    a.monto,
    a.fecha_anticipo,
    a.estado,
    a.observaciones,
    a.fecha_registro,
    a.usuario_registro
FROM tbl_anticipos a
INNER JOIN tbl_socios s ON a.id_socio = s.id_socio
WHERE s.estado = 'activo'
ORDER BY a.fecha_registro DESC;

-- =====================================================
-- PROCEDIMIENTO PARA CALCULAR TOTAL DE ANTICIPOS POR SOCIO
-- =====================================================

DELIMITER $$

CREATE PROCEDURE `sp_total_anticipos_socio`(IN p_id_socio INT, IN p_fecha_inicio DATE, IN p_fecha_fin DATE)
BEGIN
    SELECT 
        s.id_socio,
        CONCAT(s.nombre, ' ', s.apellido) as socio,
        s.identificacion,
        COUNT(a.id_anticipo) as total_anticipos,
        SUM(CASE WHEN a.estado = 'aprobado' THEN a.monto ELSE 0 END) as total_aprobado,
        SUM(CASE WHEN a.estado = 'pendiente' THEN a.monto ELSE 0 END) as total_pendiente,
        SUM(CASE WHEN a.estado = 'rechazado' THEN a.monto ELSE 0 END) as total_rechazado
    FROM tbl_socios s
    LEFT JOIN tbl_anticipos a ON s.id_socio = a.id_socio 
        AND a.fecha_anticipo BETWEEN p_fecha_inicio AND p_fecha_fin
    WHERE s.id_socio = p_id_socio
    GROUP BY s.id_socio, s.nombre, s.apellido, s.identificacion;
END$$

DELIMITER ;

-- =====================================================
-- MENSAJE DE CONFIRMACIÓN
-- =====================================================

SELECT 'Tabla tbl_anticipos creada exitosamente' as mensaje;
