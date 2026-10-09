-- Esquema sin datos, generado con db/tools/extraer_esquema.py desde colfe_demo_2026.sql
-- ATENCION: es el esquema BASE, anterior a las migraciones. El modelo vigente es este esquema mas
-- db/migraciones/001 a 007 en orden (006 retira anticipos; 007 pasa los deducibles a una fila cada uno y retira el ahorro).
-- Para ver el modelo actual: docs/DICCIONARIO_DATOS.md y docs/diagramas/er-colfe.html.
-- NOTA: fechas desplazadas +1 anio(s) con db/tools/desplazar_fechas.py.
-- Origen: colfe_db_20260929.sql. No editar a mano: regenerar con el script.
-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         8.0.30 - MySQL Community Server - GPL
-- SO del servidor:              Win64
-- HeidiSQL Versión:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Volcando estructura de base de datos para colfe_db
CREATE DATABASE IF NOT EXISTS `colfe_db` /*!40100 DEFAULT CHARACTER SET utf8mb3 */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `colfe_db`;

-- Volcando estructura para función colfe_db.generar_litros_leche
DELIMITER //
CREATE FUNCTION `generar_litros_leche`(fecha DATE, id_socio INT) RETURNS decimal(10,2)
    NO SQL
BEGIN
    DECLARE base_litros DECIMAL(10,2);
    DECLARE variacion DECIMAL(10,2);
    DECLARE litros_final DECIMAL(10,2);
    
    -- Base según el ID (para diferenciar productores)
    SET base_litros = 50 + (id_socio % 20); -- Entre 50 y 70 litros base
    
    -- Variación estacional (más producción en épocas de lluvia)
    IF MONTH(fecha) BETWEEN 4 AND 6 OR MONTH(fecha) BETWEEN 10 AND 11 THEN
        SET variacion = RAND() * 15 + 5; -- Aumento en temporadas lluviosas
    ELSE
        SET variacion = RAND() * 10 - 5; -- Variación normal o leve disminución
    END IF;
    
    -- Variación aleatoria diaria
    SET variacion = variacion + (RAND() * 8 - 4);
    
    -- Asegurar mínimo de producción
    SET litros_final = base_litros + variacion;
    IF litros_final < 30 THEN SET litros_final = 30; END IF;
    
    RETURN ROUND(litros_final, 2);
END//
DELIMITER ;

-- Volcando estructura para procedimiento colfe_db.spCrearEventoRecoleccion
DELIMITER //
CREATE PROCEDURE `spCrearEventoRecoleccion`(
	IN `p_nombre_evento` VARCHAR(50),
	IN `p_fecha` DATE
)
BEGIN
    -- Solo ejecutar si el nombre del evento es 'recoleccion'
    IF p_nombre_evento = 'recoleccion' THEN

        -- Verificar si ya existen registros para esa fecha
        IF NOT EXISTS (
            SELECT 1 FROM tbl_recoleccion WHERE fecha = p_fecha
        ) THEN

            -- Insertar todos los socios activos en la tabla recoleccion para esa fecha
            INSERT INTO tbl_recoleccion (id_socio, fecha, litros_leche, estado)
            SELECT id_socio, p_fecha, 0, 'sin confirmar'
            FROM tbl_socios
            WHERE estado = 'activo';

            SELECT TRUE AS resultado; -- Registros creados
        ELSE
            SELECT TRUE AS resultado; -- Ya existen, no hacer nada
        END IF;

    ELSE
        SELECT FALSE AS resultado; -- No es el evento correcto
    END IF;
END//
DELIMITER ;

-- Volcando estructura para procedimiento colfe_db.spInsertIntoRecoleccion
DELIMITER //
CREATE PROCEDURE `spInsertIntoRecoleccion`()
BEGIN
    -- DECLARE fecha_actual DATE DEFAULT '2026-08-26';
    DECLARE fecha_actual DATE DEFAULT '2025-01-01';  -- ← CAMBIAR AQUÍ

    -- WHILE fecha_actual <= CURDATE() DO
    WHILE fecha_actual <= '2026-08-26' DO  -- ← Y AQUÍ TAMBIÉN
	     -- Insertar recolección diaria (siempre se hace)
        INSERT INTO tbl_recoleccion (id_socio, fecha, litros_leche, estado)
        SELECT 
            s.id_socio, 
            fecha_actual AS fecha,
            generar_litros_leche(fecha_actual, s.id_socio) AS litros_leche,
            'confirmado' AS estado
        FROM tbl_socios AS s
        ORDER BY s.id_socio;

        -- Procesar liquidación solo los días 15 y último del mes
        IF fecha_actual > '2025-01-01' THEN
            IF DAY(fecha_actual) = 15 OR fecha_actual = LAST_DAY(fecha_actual) THEN
                CALL spProcesarLiquidacionQuincenal('liquidacion', fecha_actual);
            END IF;
        END IF;

        SET fecha_actual = DATE_ADD(fecha_actual, INTERVAL 1 DAY);
    END WHILE;
END//
DELIMITER ;

-- Volcando estructura para procedimiento colfe_db.spProcesarLiquidacionQuincenal
DELIMITER //
CREATE PROCEDURE `spProcesarLiquidacionQuincenal`(
	IN `p_evento` VARCHAR(50),
	IN `p_fecha_liquidacion` DATE
)
proc: BEGIN
    DECLARE v_quincena VARCHAR(10);
    DECLARE v_fecha_inicio DATE;
    DECLARE v_fecha_fin DATE;
    DECLARE v_dia_fecha INT;
    DECLARE v_count INT;
    DECLARE v_dias_quincena INT;
    DECLARE v_dias_con_registros INT;
    DECLARE v_mensaje VARCHAR(255);

    -- Validar que el evento sea 'liquidacion'
    IF p_evento != 'liquidacion' THEN
        SELECT FALSE AS resultado;
        LEAVE proc;
    END IF;

    -- Obtener día de la fecha de liquidación
    SET v_dia_fecha = DAY(p_fecha_liquidacion);

    -- Verificar si ya existen registros para esa fecha
    IF EXISTS (
        SELECT 1 FROM tbl_liquidacion WHERE fecha_liquidacion = p_fecha_liquidacion
    ) THEN
        SELECT TRUE AS resultado; -- Ya existen, no hacer nada
        LEAVE proc;
    END IF;

	  -- Validar que la fecha sea día 15 o último del mes
	 IF v_dia_fecha != 15 AND p_fecha_liquidacion != LAST_DAY(p_fecha_liquidacion) THEN
		   SIGNAL SQLSTATE '45000'
		   SET MESSAGE_TEXT = 'La fecha de liquidación debe ser día 15 o el último día del mes';
	 END IF;

    -- Determinar la quincena y el rango de fechas
	 IF v_dia_fecha = 15 THEN
	    -- Primera quincena: días 1 al 15
	    SET v_quincena = '1ra';
	    SET v_fecha_inicio = DATE_SUB(p_fecha_liquidacion, INTERVAL 14 DAY); -- día 1
	    SET v_fecha_fin = p_fecha_liquidacion;
	 ELSE
	    -- Segunda quincena: días 16 al último día del mes
		 SET v_quincena = '2da';
		 SET v_fecha_inicio = DATE_SUB(p_fecha_liquidacion, INTERVAL (DAY(p_fecha_liquidacion) - 1) DAY); -- día 1 del mes
		 SET v_fecha_inicio = DATE_ADD(v_fecha_inicio, INTERVAL 15 DAY); -- día 16
		 SET v_fecha_fin = p_fecha_liquidacion;
	 END IF;

    -- Cálculo preciso de días en la quincena (considerando meses con diferente cantidad de días)
	  SET v_dias_quincena = DATEDIFF(v_fecha_fin, v_fecha_inicio) + 1;

    -- 1. Validación de días completos con registros confirmados
    SELECT COUNT(DISTINCT fecha) INTO v_dias_con_registros
    FROM tbl_recoleccion
    WHERE fecha BETWEEN v_fecha_inicio AND v_fecha_fin
    AND estado = 'confirmado';

    IF v_dias_con_registros < v_dias_quincena THEN
        SET v_mensaje = CONCAT('Faltan registros confirmados para ',
                             (v_dias_quincena - v_dias_con_registros),
                             ' día(s) de la quincena (',
                             v_fecha_inicio, ' al ', v_fecha_fin, ')');
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = v_mensaje;
    END IF;

    -- 2. Validar que no existan registros sin confirmar
    SELECT COUNT(*) INTO v_count
    FROM tbl_recoleccion
    WHERE fecha BETWEEN v_fecha_inicio AND v_fecha_fin
    AND estado != 'confirmado';

    IF v_count > 0 THEN
        SET v_mensaje = CONCAT('Existen ', v_count, ' registros no confirmados en el período ',
                             v_fecha_inicio, ' al ', v_fecha_fin, '. Todos deben estar confirmados.');
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = v_mensaje;
    END IF;

    -- Iniciar transacción
    START TRANSACTION;

    BEGIN
        DECLARE EXIT HANDLER FOR SQLEXCEPTION
        BEGIN
            ROLLBACK;
            RESIGNAL;
        END;

        -- Paso 1: Poblar tabla produccion con los totales de litros por socio
        INSERT INTO tbl_produccion (id_socio, fecha, quincena, total_litros)
        SELECT
            r.id_socio,
            p_fecha_liquidacion,
            v_quincena,
            SUM(r.litros_leche) AS total_litros
        FROM
            tbl_recoleccion r
        JOIN
            tbl_socios s ON r.id_socio = s.id_socio
        WHERE
            r.fecha BETWEEN v_fecha_inicio AND v_fecha_fin
            AND r.estado = 'confirmado'
            AND s.estado = 'activo'
        GROUP BY
            r.id_socio;

        -- Paso 2: Poblar tabla liquidacion con los cálculos completos (INCLUYENDO ANTICIPOS)
			INSERT INTO tbl_liquidacion (
			    id_produccion, id_deducible, id_precio, id_socio, vinculacion, quincena, identificacion,
			    total_litros, precio_litro, total_ingresos,
			    fedegan, administracion, ahorro, total_deducibles, total_anticipos,
			    neto_a_pagar, estado, fecha_liquidacion
			)
			SELECT
			    p.id_produccion,
			    d.id_deducible,
			    pr.id_precio,
			    p.id_socio,
			    s.vinculacion,
			    p.quincena,
			    s.identificacion,
			    p.total_litros,
			    pr.precio AS precio_litro,
			    ROUND(p.total_litros * pr.precio, 2) AS total_ingresos,
			    ROUND((p.total_litros * pr.precio) * (d.fedegan / 100), 2) AS fedegan,
			    d.administracion,
			    d.ahorro,
			    ROUND(((p.total_litros * pr.precio) * (d.fedegan / 100)) + d.administracion + d.ahorro, 2) AS total_deducibles,
			    -- Cálculo del total de anticipos aprobados del socio en la quincena
			    COALESCE(ROUND(SUM(CASE 
			        WHEN a.estado = 'aprobado' THEN a.monto 
			        ELSE 0 
			    END), 2), 0.00) AS total_anticipos,
			    -- Cálculo del neto a pagar considerando anticipos
			    ROUND(
			        (p.total_litros * pr.precio) - 
			        (((p.total_litros * pr.precio) * (d.fedegan / 100)) + d.administracion + d.ahorro) -
			        COALESCE(SUM(CASE 
			            WHEN a.estado = 'aprobado' THEN a.monto 
			            ELSE 0 
			        END), 0.00)
			    , 2) AS neto_a_pagar,
			    'pre-liquidacion' AS estado,
			    p_fecha_liquidacion AS fecha_liquidacion
			FROM
			    tbl_produccion p
			JOIN
			    tbl_socios s ON p.id_socio = s.id_socio
			JOIN
			    tbl_deducibles d ON s.vinculacion = d.vinculacion AND d.estado = 'activo'
			JOIN
			    tbl_precios pr ON s.vinculacion = pr.vinculacion AND pr.estado = 'activo'
			-- LEFT JOIN para anticipos (puede que no haya anticipos)
			LEFT JOIN
			    tbl_anticipos a ON p.id_socio = a.id_socio 
			    AND a.estado = 'aprobado'
			    AND a.fecha_anticipo BETWEEN v_fecha_inicio AND v_fecha_fin
			WHERE
			    p.fecha = p_fecha_liquidacion
			    AND p.quincena = v_quincena
			GROUP BY
			    p.id_produccion, d.id_deducible, pr.id_precio, p.id_socio, s.vinculacion, 
			    p.quincena, s.identificacion, p.total_litros, pr.precio, d.fedegan, 
			    d.administracion, d.ahorro;

        -- Confirmar transacción
        COMMIT;

        SELECT TRUE AS resultado; -- Registros creados
    END;
END//
DELIMITER ;

-- Volcando estructura para procedimiento colfe_db.sp_total_anticipos_socio
DELIMITER //
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
END//
DELIMITER ;

-- Volcando estructura para tabla colfe_db.tbl_anticipos
CREATE TABLE IF NOT EXISTS `tbl_anticipos` (
  `id_anticipo` int NOT NULL AUTO_INCREMENT COMMENT 'Identificador único del anticipo',
  `id_socio` int NOT NULL COMMENT 'ID del socio que solicita el anticipo',
  `monto` decimal(10,2) NOT NULL COMMENT 'Monto del anticipo solicitado',
  `fecha_anticipo` date NOT NULL COMMENT 'Fecha en que se solicita el anticipo',
  `estado` enum('pendiente','aprobado','rechazado') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'pendiente' COMMENT 'Estado del anticipo',
  `observaciones` text COLLATE utf8mb4_general_ci COMMENT 'Observaciones adicionales del anticipo',
  `fecha_registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha y hora de registro',
  `usuario_registro` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Usuario que registró el anticipo',
  PRIMARY KEY (`id_anticipo`),
  KEY `fk_anticipos_socios` (`id_socio`),
  KEY `idx_fecha_anticipo` (`fecha_anticipo`),
  KEY `idx_estado` (`estado`),
  KEY `idx_socio_fecha` (`id_socio`,`fecha_anticipo`),
  KEY `idx_estado_fecha` (`estado`,`fecha_anticipo`),
  CONSTRAINT `fk_anticipos_socios` FOREIGN KEY (`id_socio`) REFERENCES `tbl_socios` (`id_socio`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla colfe_db.tbl_anticipos: ~23 rows (aproximadamente)

-- Volcando estructura para tabla colfe_db.tbl_deducibles
CREATE TABLE IF NOT EXISTS `tbl_deducibles` (
  `id_deducible` int NOT NULL AUTO_INCREMENT,
  `vinculacion` enum('asociado','proveedor') DEFAULT NULL,
  `fedegan` decimal(5,2) DEFAULT NULL,
  `administracion` decimal(10,2) DEFAULT NULL,
  `ahorro` decimal(10,2) DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `estado` enum('activo','inactivo') DEFAULT NULL,
  PRIMARY KEY (`id_deducible`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3;

-- Volcando datos para la tabla colfe_db.tbl_deducibles: ~2 rows (aproximadamente)

-- Volcando estructura para tabla colfe_db.tbl_liquidacion
CREATE TABLE IF NOT EXISTS `tbl_liquidacion` (
  `id_liquidacion` int NOT NULL AUTO_INCREMENT,
  `id_produccion` int DEFAULT NULL,
  `id_deducible` int DEFAULT NULL,
  `id_precio` int DEFAULT NULL,
  `id_socio` int DEFAULT NULL,
  `vinculacion` enum('asociado','proveedor') DEFAULT NULL,
  `quincena` enum('1ra','2da') DEFAULT NULL,
  `identificacion` varchar(20) DEFAULT NULL,
  `total_litros` decimal(10,2) DEFAULT NULL,
  `precio_litro` decimal(10,2) DEFAULT NULL,
  `total_ingresos` decimal(15,2) DEFAULT NULL,
  `fedegan` decimal(10,2) DEFAULT NULL,
  `administracion` decimal(10,2) DEFAULT NULL,
  `ahorro` decimal(10,2) DEFAULT NULL,
  `total_deducibles` decimal(15,2) DEFAULT NULL,
  `total_anticipos` decimal(15,2) DEFAULT NULL,
  `neto_a_pagar` decimal(15,2) DEFAULT NULL,
  `estado` enum('pre-liquidacion','liquidacion') DEFAULT NULL,
  `fecha_liquidacion` date DEFAULT NULL,
  PRIMARY KEY (`id_liquidacion`),
  UNIQUE KEY `idx_liquidacion_unica` (`id_socio`,`fecha_liquidacion`,`quincena`),
  KEY `id_produccion` (`id_produccion`),
  KEY `id_deducible` (`id_deducible`),
  KEY `id_precio` (`id_precio`),
  CONSTRAINT `fk_liquidacion_deducible` FOREIGN KEY (`id_deducible`) REFERENCES `tbl_deducibles` (`id_deducible`),
  CONSTRAINT `fk_liquidacion_precio` FOREIGN KEY (`id_precio`) REFERENCES `tbl_precios` (`id_precio`),
  CONSTRAINT `fk_liquidacion_produccion` FOREIGN KEY (`id_produccion`) REFERENCES `tbl_produccion` (`id_produccion`),
  CONSTRAINT `fk_liquidacion_socios` FOREIGN KEY (`id_socio`) REFERENCES `tbl_socios` (`id_socio`)
) ENGINE=InnoDB AUTO_INCREMENT=4907 DEFAULT CHARSET=utf8mb3;

-- Volcando datos para la tabla colfe_db.tbl_liquidacion: ~2.993 rows (aproximadamente)

-- Volcando estructura para tabla colfe_db.tbl_precios
CREATE TABLE IF NOT EXISTS `tbl_precios` (
  `id_precio` int NOT NULL AUTO_INCREMENT,
  `vinculacion` enum('asociado','proveedor') DEFAULT NULL,
  `precio` decimal(10,2) DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `estado` enum('activo','inactivo') DEFAULT NULL,
  PRIMARY KEY (`id_precio`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3;

-- Volcando datos para la tabla colfe_db.tbl_precios: ~2 rows (aproximadamente)

-- Volcando estructura para tabla colfe_db.tbl_produccion
CREATE TABLE IF NOT EXISTS `tbl_produccion` (
  `id_produccion` int NOT NULL AUTO_INCREMENT,
  `id_socio` int DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `quincena` enum('1ra','2da') DEFAULT NULL,
  `total_litros` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id_produccion`),
  UNIQUE KEY `idx_produccion_unica` (`id_socio`,`fecha`,`quincena`),
  CONSTRAINT `fk_produccion_socios` FOREIGN KEY (`id_socio`) REFERENCES `tbl_socios` (`id_socio`)
) ENGINE=InnoDB AUTO_INCREMENT=4934 DEFAULT CHARSET=utf8mb3;

-- Volcando datos para la tabla colfe_db.tbl_produccion: ~4.173 rows (aproximadamente)

-- Volcando estructura para tabla colfe_db.tbl_recoleccion
CREATE TABLE IF NOT EXISTS `tbl_recoleccion` (
  `id_recoleccion` int NOT NULL AUTO_INCREMENT,
  `id_socio` int DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `litros_leche` decimal(10,2) DEFAULT NULL,
  `estado` enum('confirmado','sin confirmar') DEFAULT NULL,
  PRIMARY KEY (`id_recoleccion`),
  KEY `id_socio` (`id_socio`),
  CONSTRAINT `tbl_recoleccion_ibfk_1` FOREIGN KEY (`id_socio`) REFERENCES `tbl_socios` (`id_socio`)
) ENGINE=InnoDB AUTO_INCREMENT=76689 DEFAULT CHARSET=utf8mb3;

-- Volcando datos para la tabla colfe_db.tbl_recoleccion: ~61.645 rows (aproximadamente)

-- Volcando estructura para tabla colfe_db.tbl_socios
CREATE TABLE IF NOT EXISTS `tbl_socios` (
  `id_socio` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) DEFAULT NULL,
  `apellido` varchar(50) DEFAULT NULL,
  `identificacion` varchar(20) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `direccion` varchar(100) DEFAULT NULL,
  `vinculacion` enum('asociado','proveedor') DEFAULT NULL,
  `fecha_ingreso` date DEFAULT NULL,
  `estado` enum('activo','inactivo') DEFAULT NULL,
  PRIMARY KEY (`id_socio`)
) ENGINE=InnoDB AUTO_INCREMENT=159 DEFAULT CHARSET=utf8mb3;

-- Volcando datos para la tabla colfe_db.tbl_socios: ~107 rows (aproximadamente)

-- Volcando estructura para tabla colfe_db.tbl_usuarios
CREATE TABLE IF NOT EXISTS `tbl_usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3;

-- Volcando datos para la tabla colfe_db.tbl_usuarios: ~0 rows (aproximadamente)

-- Volcando estructura para vista colfe_db.v_anticipos_completos
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `v_anticipos_completos` (
	`id_anticipo` INT(10) NOT NULL COMMENT 'Identificador único del anticipo',
	`id_socio` INT(10) NOT NULL COMMENT 'ID del socio que solicita el anticipo',
	`nombre_socio` VARCHAR(101) NULL COLLATE 'utf8mb3_general_ci',
	`identificacion` VARCHAR(20) NULL COLLATE 'utf8mb3_general_ci',
	`telefono` VARCHAR(20) NULL COLLATE 'utf8mb3_general_ci',
	`vinculacion` ENUM('asociado','proveedor') NULL COLLATE 'utf8mb3_general_ci',
	`monto` DECIMAL(10,2) NOT NULL COMMENT 'Monto del anticipo solicitado',
	`fecha_anticipo` DATE NOT NULL COMMENT 'Fecha en que se solicita el anticipo',
	`estado` ENUM('pendiente','aprobado','rechazado') NOT NULL COMMENT 'Estado del anticipo' COLLATE 'utf8mb4_general_ci',
	`observaciones` TEXT NULL COMMENT 'Observaciones adicionales del anticipo' COLLATE 'utf8mb4_general_ci',
	`fecha_registro` TIMESTAMP NOT NULL COMMENT 'Fecha y hora de registro',
	`usuario_registro` VARCHAR(50) NULL COMMENT 'Usuario que registró el anticipo' COLLATE 'utf8mb4_general_ci'
) ENGINE=MyISAM;

-- Volcando estructura para disparador colfe_db.before_insert_deducibles
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `before_insert_deducibles` BEFORE INSERT ON `tbl_deducibles` FOR EACH ROW BEGIN
    IF (NEW.estado = 'activo') THEN
        IF EXISTS (
            SELECT 1 FROM tbl_deducibles 
            WHERE vinculacion = NEW.vinculacion AND estado = 'activo' LIMIT 1
        ) THEN
            SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Ya existe un registro activo para esta vinculación';
        END IF;
    END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para disparador colfe_db.before_insert_precios
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `before_insert_precios` BEFORE INSERT ON `tbl_precios` FOR EACH ROW BEGIN
    IF (NEW.estado = 'activo') THEN
        IF (
            SELECT COUNT(*) 
            FROM tbl_precios 
            WHERE vinculacion = NEW.vinculacion 
              AND estado = 'activo'
        ) > 0 THEN
            SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Ya existe un registro activo para esta vinculación';
        END IF;
    END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para disparador colfe_db.before_insert_recoleccion
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `before_insert_recoleccion` BEFORE INSERT ON `tbl_recoleccion` FOR EACH ROW BEGIN
    IF EXISTS (
        SELECT 1 FROM tbl_recoleccion 
        WHERE id_socio = NEW.id_socio 
        AND fecha = NEW.fecha
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Ya existe un registro para este socio en esta fecha';
    END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para disparador colfe_db.before_insert_usuario
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `before_insert_usuario` BEFORE INSERT ON `tbl_usuarios` FOR EACH ROW BEGIN
    IF EXISTS (SELECT 1 FROM tbl_usuarios WHERE username = NEW.username) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El nombre de usuario ya está registrado';
    END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para disparador colfe_db.before_update_deducibles
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `before_update_deducibles` BEFORE UPDATE ON `tbl_deducibles` FOR EACH ROW BEGIN
    IF (NEW.estado = 'activo') THEN
        IF (
            SELECT COUNT(*) 
            FROM tbl_deducibles 
            WHERE vinculacion = NEW.vinculacion 
              AND estado = 'activo'
              AND id_deducible <> NEW.id_deducible
        ) > 0 THEN
            SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Ya existe un registro activo para esta vinculación';
        END IF;
    END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para disparador colfe_db.before_update_precios
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `before_update_precios` BEFORE UPDATE ON `tbl_precios` FOR EACH ROW BEGIN
    IF (NEW.estado = 'activo') THEN
        IF (
            SELECT COUNT(*) 
            FROM tbl_precios 
            WHERE vinculacion = NEW.vinculacion 
              AND estado = 'activo'
              AND id_precio <> NEW.id_precio
        ) > 0 THEN
            SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Ya existe un registro activo para esta vinculación';
        END IF;
    END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para disparador colfe_db.before_update_recoleccion
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `before_update_recoleccion` BEFORE UPDATE ON `tbl_recoleccion` FOR EACH ROW BEGIN
    IF EXISTS (
        SELECT 1 FROM tbl_recoleccion 
        WHERE id_socio = NEW.id_socio 
        AND fecha = NEW.fecha
        AND id_recoleccion != NEW.id_recoleccion
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Ya existe un registro para este socio en esta fecha';
    END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para disparador colfe_db.tr_anticipos_before_insert
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `tr_anticipos_before_insert` BEFORE INSERT ON `tbl_anticipos` FOR EACH ROW BEGIN
    SET NEW.fecha_registro = NOW();
    IF NEW.usuario_registro IS NULL THEN
        SET NEW.usuario_registro = USER();
    END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para vista colfe_db.v_anticipos_completos
-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `v_anticipos_completos`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_anticipos_completos` AS select `a`.`id_anticipo` AS `id_anticipo`,`a`.`id_socio` AS `id_socio`,concat(`s`.`nombre`,' ',`s`.`apellido`) AS `nombre_socio`,`s`.`identificacion` AS `identificacion`,`s`.`telefono` AS `telefono`,`s`.`vinculacion` AS `vinculacion`,`a`.`monto` AS `monto`,`a`.`fecha_anticipo` AS `fecha_anticipo`,`a`.`estado` AS `estado`,`a`.`observaciones` AS `observaciones`,`a`.`fecha_registro` AS `fecha_registro`,`a`.`usuario_registro` AS `usuario_registro` from (`tbl_anticipos` `a` join `tbl_socios` `s` on((`a`.`id_socio` = `s`.`id_socio`))) where (`s`.`estado` = 'activo') order by `a`.`fecha_registro` desc;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
