-- 008: precios con vigencia (decisión de COLFE, 9 oct 2026).
--  * tbl_precios pasa a ser un historial: fecha_inicio (obligatoria) y fecha_fin (vacía = abierto).
--    Manda la vigencia por fechas; el campo estado y la columna fecha desaparecen.
--  * Un trigger impide que dos precios de la misma vinculación se solapen y que la fecha de fin
--    sea anterior a la de inicio (el CHECK ck_precios_vigencia es la red de seguridad).
--  * spCrearPrecio crea un precio y, si había uno abierto que empezó antes, lo cierra un día antes
--    del inicio del nuevo, todo en una transacción.
--  * spProcesarLiquidacionQuincenal toma el precio vigente en la fecha de cierre de la quincena
--    (liquidación fija: toda la quincena con ese precio).
--  * Los precios que existen hoy quedan abiertos desde su fecha de registro.
-- Falla, sin cambiar nada, si hay precios con estado inactivo: cada uno debe cerrarse antes con una
-- fecha_fin. Es idempotente. Se aplica después de 007.

SET NAMES utf8mb4;

-- 1. Triggers que dependen de las columnas que cambian
DROP TRIGGER IF EXISTS `before_insert_precios`;
DROP TRIGGER IF EXISTS `before_update_precios`;
DROP TRIGGER IF EXISTS `tr_aud_precios_i`;
DROP TRIGGER IF EXISTS `tr_aud_precios_u`;
DROP TRIGGER IF EXISTS `tr_aud_precios_d`;

-- 2. Conversión de la tabla
DROP PROCEDURE IF EXISTS `sp_mig008`;
DELIMITER //
CREATE PROCEDURE `sp_mig008`()
BEGIN
    IF EXISTS (SELECT 1 FROM information_schema.columns
                WHERE table_schema = DATABASE() AND table_name = 'tbl_precios' AND column_name = 'estado') THEN
        IF EXISTS (SELECT 1 FROM `tbl_precios` WHERE `estado` = 'inactivo') THEN
            SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Hay precios inactivos: cierre cada uno con una fecha de fin antes de aplicar esta migración';
        END IF;
        ALTER TABLE `tbl_precios`
            ADD COLUMN `fecha_inicio` date NULL,
            ADD COLUMN `fecha_fin` date NULL;
        UPDATE `tbl_precios` SET `fecha_inicio` = COALESCE(`fecha`, '2000-01-01');
        ALTER TABLE `tbl_precios`
            DROP COLUMN `fecha`,
            DROP COLUMN `estado`,
            MODIFY `fecha_inicio` date NOT NULL;
    END IF;
    IF NOT EXISTS (SELECT 1 FROM information_schema.table_constraints
                    WHERE table_schema = DATABASE() AND table_name = 'tbl_precios' AND constraint_name = 'ck_precios_vigencia') THEN
        ALTER TABLE `tbl_precios` ADD CONSTRAINT `ck_precios_vigencia`
            CHECK (`fecha_fin` IS NULL OR `fecha_fin` >= `fecha_inicio`);
    END IF;
END//
DELIMITER ;
CALL `sp_mig008`();
DROP PROCEDURE `sp_mig008`;

-- 3. Triggers de validación: los rangos de una misma vinculación no se solapan
DELIMITER //
CREATE TRIGGER `before_insert_precios` BEFORE INSERT ON `tbl_precios` FOR EACH ROW
BEGIN
    IF NEW.`fecha_fin` IS NOT NULL AND NEW.`fecha_fin` < NEW.`fecha_inicio` THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'La fecha de fin no puede ser anterior a la de inicio';
    END IF;
    IF EXISTS (SELECT 1 FROM `tbl_precios` p
                WHERE p.`vinculacion` = NEW.`vinculacion`
                  AND p.`fecha_inicio` <= COALESCE(NEW.`fecha_fin`, '9999-12-31')
                  AND COALESCE(p.`fecha_fin`, '9999-12-31') >= NEW.`fecha_inicio`) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El rango de fechas se solapa con otro precio de esta vinculación';
    END IF;
END//

CREATE TRIGGER `before_update_precios` BEFORE UPDATE ON `tbl_precios` FOR EACH ROW
BEGIN
    IF NEW.`fecha_fin` IS NOT NULL AND NEW.`fecha_fin` < NEW.`fecha_inicio` THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'La fecha de fin no puede ser anterior a la de inicio';
    END IF;
    IF EXISTS (SELECT 1 FROM `tbl_precios` p
                WHERE p.`vinculacion` = NEW.`vinculacion`
                  AND p.`id_precio` <> NEW.`id_precio`
                  AND p.`fecha_inicio` <= COALESCE(NEW.`fecha_fin`, '9999-12-31')
                  AND COALESCE(p.`fecha_fin`, '9999-12-31') >= NEW.`fecha_inicio`) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El rango de fechas se solapa con otro precio de esta vinculación';
    END IF;
END//
DELIMITER ;

-- 4. Crear un precio cerrando el abierto anterior
DROP PROCEDURE IF EXISTS `spCrearPrecio`;
DELIMITER //
CREATE PROCEDURE `spCrearPrecio`(
    IN `p_vinculacion` VARCHAR(20),
    IN `p_precio` DECIMAL(10,2),
    IN `p_inicio` DATE,
    IN `p_fin` DATE
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    START TRANSACTION;
    -- El precio abierto que empezó antes termina el día anterior al inicio del nuevo
    UPDATE `tbl_precios`
       SET `fecha_fin` = DATE_SUB(p_inicio, INTERVAL 1 DAY)
     WHERE `vinculacion` = p_vinculacion AND `fecha_fin` IS NULL AND `fecha_inicio` < p_inicio;
    INSERT INTO `tbl_precios` (`vinculacion`, `precio`, `fecha_inicio`, `fecha_fin`)
        VALUES (p_vinculacion, p_precio, p_inicio, p_fin);
    COMMIT;
    SELECT LAST_INSERT_ID() AS `id_precio`;
END//
DELIMITER ;

-- 5. Procedimiento de liquidación con el precio vigente en la fecha de cierre
DROP PROCEDURE IF EXISTS `spProcesarLiquidacionQuincenal`;
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

        -- Paso 2: liquidación por socio. Los deducibles activos de su vinculación se suman uno a uno:
        -- un porcentaje se aplica sobre los ingresos y un valor fijo se descuenta tal cual.
			INSERT INTO tbl_liquidacion (
			    id_produccion, id_precio, id_socio, vinculacion, quincena, identificacion,
			    total_litros, precio_litro, total_ingresos, total_deducibles,
			    neto_a_pagar, estado, fecha_liquidacion
			)
			SELECT
			    c.id_produccion, c.id_precio, c.id_socio, c.vinculacion, c.quincena, c.identificacion,
			    c.total_litros, c.precio, c.ingresos, c.deducibles,
			    ROUND(c.ingresos - c.deducibles, 2),
			    'pre-liquidacion', p_fecha_liquidacion
			FROM (
			    SELECT
			        p.id_produccion, pr.id_precio, p.id_socio, s.vinculacion, p.quincena, s.identificacion,
			        p.total_litros, pr.precio,
			        ROUND(p.total_litros * pr.precio, 2) AS ingresos,
			        (SELECT COALESCE(SUM(CASE d.tipo_valor
			                                 WHEN 'porcentaje' THEN ROUND(ROUND(p.total_litros * pr.precio, 2) * d.valor / 100, 2)
			                                 ELSE d.valor END), 0)
			           FROM tbl_deducibles d
			          WHERE d.vinculacion = s.vinculacion AND d.estado = 'activo') AS deducibles
			    FROM tbl_produccion p
			    JOIN tbl_socios s ON p.id_socio = s.id_socio
			    JOIN tbl_precios pr ON s.vinculacion = pr.vinculacion
			                       AND pr.fecha_inicio <= p_fecha_liquidacion
			                       AND (pr.fecha_fin IS NULL OR pr.fecha_fin >= p_fecha_liquidacion)
			    WHERE p.fecha = p_fecha_liquidacion
			      AND p.quincena = v_quincena
			) c;

        -- Paso 3: detalle de cada deducible aplicado (copia de nombre, tipo y valor al liquidar)
			INSERT INTO tbl_liquidacion_deducible (id_liquidacion, id_deducible, nombre, tipo_valor, valor, monto)
			SELECT
			    l.id_liquidacion, d.id_deducible, d.nombre, d.tipo_valor, d.valor,
			    CASE d.tipo_valor WHEN 'porcentaje' THEN ROUND(l.total_ingresos * d.valor / 100, 2) ELSE d.valor END
			FROM tbl_liquidacion l
			JOIN tbl_deducibles d ON d.vinculacion = l.vinculacion AND d.estado = 'activo'
			WHERE l.fecha_liquidacion = p_fecha_liquidacion AND l.quincena = v_quincena;

        -- Confirmar transacción
        COMMIT;

        SELECT TRUE AS resultado; -- Registros creados
    END;
END//
DELIMITER ;

-- 6. Triggers de auditoría con las columnas nuevas
DROP TRIGGER IF EXISTS `tr_aud_precios_i`;
DELIMITER //
CREATE TRIGGER `tr_aud_precios_i` AFTER INSERT ON `tbl_precios` FOR EACH ROW
BEGIN
    INSERT INTO `tbl_auditoria` (`tabla`, `accion`, `id_registro`, `id_usuario`, `origen`, `datos_antes`, `datos_despues`)
        VALUES ('tbl_precios', 'INSERT', NEW.`id_precio`, @colfe_usuario, COALESCE(@colfe_origen, 'sistema'), NULL, JSON_OBJECT('id_precio', NEW.`id_precio`, 'vinculacion', NEW.`vinculacion`, 'precio', NEW.`precio`, 'fecha_inicio', NEW.`fecha_inicio`, 'fecha_fin', NEW.`fecha_fin`));
END//
DELIMITER ;

DROP TRIGGER IF EXISTS `tr_aud_precios_u`;
DELIMITER //
CREATE TRIGGER `tr_aud_precios_u` AFTER UPDATE ON `tbl_precios` FOR EACH ROW
BEGIN
    IF NOT (OLD.`id_precio` <=> NEW.`id_precio` AND OLD.`vinculacion` <=> NEW.`vinculacion` AND OLD.`precio` <=> NEW.`precio` AND OLD.`fecha_inicio` <=> NEW.`fecha_inicio` AND OLD.`fecha_fin` <=> NEW.`fecha_fin`) THEN
        INSERT INTO `tbl_auditoria` (`tabla`, `accion`, `id_registro`, `id_usuario`, `origen`, `datos_antes`, `datos_despues`)
        VALUES ('tbl_precios', 'UPDATE', NEW.`id_precio`, @colfe_usuario, COALESCE(@colfe_origen, 'sistema'), JSON_OBJECT('id_precio', OLD.`id_precio`, 'vinculacion', OLD.`vinculacion`, 'precio', OLD.`precio`, 'fecha_inicio', OLD.`fecha_inicio`, 'fecha_fin', OLD.`fecha_fin`), JSON_OBJECT('id_precio', NEW.`id_precio`, 'vinculacion', NEW.`vinculacion`, 'precio', NEW.`precio`, 'fecha_inicio', NEW.`fecha_inicio`, 'fecha_fin', NEW.`fecha_fin`));
    END IF;
END//
DELIMITER ;

DROP TRIGGER IF EXISTS `tr_aud_precios_d`;
DELIMITER //
CREATE TRIGGER `tr_aud_precios_d` AFTER DELETE ON `tbl_precios` FOR EACH ROW
BEGIN
    INSERT INTO `tbl_auditoria` (`tabla`, `accion`, `id_registro`, `id_usuario`, `origen`, `datos_antes`, `datos_despues`)
        VALUES ('tbl_precios', 'DELETE', OLD.`id_precio`, @colfe_usuario, COALESCE(@colfe_origen, 'sistema'), JSON_OBJECT('id_precio', OLD.`id_precio`, 'vinculacion', OLD.`vinculacion`, 'precio', OLD.`precio`, 'fecha_inicio', OLD.`fecha_inicio`, 'fecha_fin', OLD.`fecha_fin`), NULL);
END//
DELIMITER ;
