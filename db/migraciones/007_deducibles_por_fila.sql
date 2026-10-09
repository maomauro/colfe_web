-- 007: deducibles uno por fila y retiro del ahorro (decisión de COLFE, 9 oct 2026).
--  * tbl_deducibles guarda un deducible por fila (nombre, tipo_valor porcentaje|fijo, valor) para una
--    vinculación. Se puede crear cualquier deducible nuevo sin cambiar el esquema. Los de hoy se convierten:
--    «Fedegán» (porcentaje) y «Administración» (fijo). El ahorro se retira: ni columna ni descuento.
--  * tbl_liquidacion_deducible (nueva) guarda, por liquidación, cada deducible aplicado con su nombre, tipo,
--    valor y monto. tbl_liquidacion pierde id_deducible, fedegan, administracion y ahorro; conserva
--    total_deducibles (la suma de los montos).
--  * Las liquidaciones existentes se convierten y se recalculan sin el ahorro (en el demo, las de los
--    socios asociados): neto = ingresos - deducibles.
--  * spProcesarLiquidacionQuincenal y los triggers de auditoría y de validación se recrean.
-- DESTRUCTIVA: borra los datos de ahorro. Haga un respaldo antes (deploy/backup.sh).
-- Es idempotente. Se aplica después de 006.

SET NAMES utf8mb4;

-- 1. Triggers que dependen de las columnas que cambian
DROP TRIGGER IF EXISTS `before_insert_deducibles`;
DROP TRIGGER IF EXISTS `before_update_deducibles`;
DROP TRIGGER IF EXISTS `tr_aud_deducibles_i`;
DROP TRIGGER IF EXISTS `tr_aud_deducibles_u`;
DROP TRIGGER IF EXISTS `tr_aud_deducibles_d`;
DROP TRIGGER IF EXISTS `tr_aud_liquidacion_i`;
DROP TRIGGER IF EXISTS `tr_aud_liquidacion_u`;
DROP TRIGGER IF EXISTS `tr_aud_liquidacion_d`;

-- 2. Detalle de los deducibles aplicados en cada liquidación
CREATE TABLE IF NOT EXISTS `tbl_liquidacion_deducible` (
  `id_liquidacion_deducible` int NOT NULL AUTO_INCREMENT,
  `id_liquidacion` int NOT NULL,
  `id_deducible` int NOT NULL,
  `nombre` varchar(60) NOT NULL,
  `tipo_valor` enum('porcentaje','fijo') NOT NULL,
  `valor` decimal(12,2) NOT NULL,
  `monto` decimal(15,2) NOT NULL,
  PRIMARY KEY (`id_liquidacion_deducible`),
  UNIQUE KEY `uk_liqded_liquidacion_deducible` (`id_liquidacion`, `id_deducible`),
  KEY `idx_liqded_deducible` (`id_deducible`),
  CONSTRAINT `fk_liqded_liquidacion` FOREIGN KEY (`id_liquidacion`) REFERENCES `tbl_liquidacion` (`id_liquidacion`) ON DELETE CASCADE,
  CONSTRAINT `fk_liqded_deducible` FOREIGN KEY (`id_deducible`) REFERENCES `tbl_deducibles` (`id_deducible`),
  CONSTRAINT `ck_liqded_monto` CHECK (`monto` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Conversión de los datos y del esquema (solo si aún existe la columna ahorro)
DROP PROCEDURE IF EXISTS `sp_mig007`;
DELIMITER //
CREATE PROCEDURE `sp_mig007`()
BEGIN
    IF EXISTS (SELECT 1 FROM information_schema.columns
                WHERE table_schema = DATABASE() AND table_name = 'tbl_deducibles' AND column_name = 'ahorro') THEN

        -- 3.1 Cada fila actual pasa a ser el deducible «Fedegán»; la administración se separa en otra fila
        ALTER TABLE `tbl_deducibles`
            ADD COLUMN `nombre` varchar(60) NULL,
            ADD COLUMN `tipo_valor` enum('porcentaje','fijo') NULL,
            ADD COLUMN `valor` decimal(12,2) NULL,
            ADD COLUMN `origen_id` int NULL;
        UPDATE `tbl_deducibles`
           SET `nombre` = 'Fedegán', `tipo_valor` = 'porcentaje', `valor` = `fedegan`;
        INSERT INTO `tbl_deducibles` (`vinculacion`, `fedegan`, `administracion`, `ahorro`, `fecha`, `estado`,
                                      `nombre`, `tipo_valor`, `valor`, `origen_id`)
        SELECT `vinculacion`, 0, 0, 0, `fecha`, `estado`, 'Administración', 'fijo', `administracion`, `id_deducible`
          FROM `tbl_deducibles`
         WHERE `administracion` > 0 AND `nombre` = 'Fedegán';

        -- 3.2 Detalle de las liquidaciones que ya existen
        INSERT INTO `tbl_liquidacion_deducible` (`id_liquidacion`, `id_deducible`, `nombre`, `tipo_valor`, `valor`, `monto`)
        SELECT l.`id_liquidacion`, l.`id_deducible`, 'Fedegán', 'porcentaje', d.`valor`, l.`fedegan`
          FROM `tbl_liquidacion` l
          JOIN `tbl_deducibles` d ON d.`id_deducible` = l.`id_deducible`;
        INSERT INTO `tbl_liquidacion_deducible` (`id_liquidacion`, `id_deducible`, `nombre`, `tipo_valor`, `valor`, `monto`)
        SELECT l.`id_liquidacion`, da.`id_deducible`, 'Administración', 'fijo', da.`valor`, l.`administracion`
          FROM `tbl_liquidacion` l
          JOIN `tbl_deducibles` da ON da.`origen_id` = l.`id_deducible`
         WHERE l.`administracion` > 0;

        -- 3.3 Sin el ahorro: el total de deducibles y el neto se recalculan
        UPDATE `tbl_liquidacion`
           SET `total_deducibles` = ROUND(`fedegan` + `administracion`, 2),
               `neto_a_pagar`     = ROUND(`total_ingresos` - ROUND(`fedegan` + `administracion`, 2), 2);

        -- 3.4 Se quitan las columnas que ya no existen
        ALTER TABLE `tbl_liquidacion` DROP FOREIGN KEY `fk_liquidacion_deducible`;
        ALTER TABLE `tbl_liquidacion`
            DROP COLUMN `id_deducible`, DROP COLUMN `fedegan`, DROP COLUMN `administracion`, DROP COLUMN `ahorro`;
        IF EXISTS (SELECT 1 FROM information_schema.table_constraints
                    WHERE table_schema = DATABASE() AND table_name = 'tbl_deducibles' AND constraint_name = 'ck_deducibles_valores') THEN
            ALTER TABLE `tbl_deducibles` DROP CHECK `ck_deducibles_valores`;
        END IF;
        ALTER TABLE `tbl_deducibles`
            DROP COLUMN `fedegan`, DROP COLUMN `administracion`, DROP COLUMN `ahorro`, DROP COLUMN `origen_id`,
            MODIFY `nombre` varchar(60) NOT NULL,
            MODIFY `tipo_valor` enum('porcentaje','fijo') NOT NULL,
            MODIFY `valor` decimal(12,2) NOT NULL;
    END IF;

    IF NOT EXISTS (SELECT 1 FROM information_schema.table_constraints
                    WHERE table_schema = DATABASE() AND table_name = 'tbl_deducibles' AND constraint_name = 'ck_deducibles_valor') THEN
        ALTER TABLE `tbl_deducibles` ADD CONSTRAINT `ck_deducibles_valor`
            CHECK (`valor` >= 0 AND (`tipo_valor` = 'fijo' OR `valor` <= 100));
    END IF;
END//
DELIMITER ;
CALL `sp_mig007`();
DROP PROCEDURE `sp_mig007`;

-- 4. Triggers de validación: un solo deducible activo con el mismo nombre por vinculación
DELIMITER //
CREATE TRIGGER `before_insert_deducibles` BEFORE INSERT ON `tbl_deducibles` FOR EACH ROW
BEGIN
    IF NEW.`estado` = 'activo' AND EXISTS (
        SELECT 1 FROM `tbl_deducibles`
         WHERE `vinculacion` = NEW.`vinculacion` AND `nombre` = NEW.`nombre` AND `estado` = 'activo') THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Ya existe un deducible activo con ese nombre para esta vinculación';
    END IF;
END//

CREATE TRIGGER `before_update_deducibles` BEFORE UPDATE ON `tbl_deducibles` FOR EACH ROW
BEGIN
    IF NEW.`estado` = 'activo' AND EXISTS (
        SELECT 1 FROM `tbl_deducibles`
         WHERE `vinculacion` = NEW.`vinculacion` AND `nombre` = NEW.`nombre` AND `estado` = 'activo'
           AND `id_deducible` <> NEW.`id_deducible`) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Ya existe un deducible activo con ese nombre para esta vinculación';
    END IF;
END//
DELIMITER ;

-- 5. Procedimiento de liquidación con deducibles por fila
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
			    JOIN tbl_precios pr ON s.vinculacion = pr.vinculacion AND pr.estado = 'activo'
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
DROP TRIGGER IF EXISTS `tr_aud_liquidacion_i`;
DELIMITER //
CREATE TRIGGER `tr_aud_liquidacion_i` AFTER INSERT ON `tbl_liquidacion` FOR EACH ROW
BEGIN
    INSERT INTO `tbl_auditoria` (`tabla`, `accion`, `id_registro`, `id_usuario`, `origen`, `datos_antes`, `datos_despues`)
        VALUES ('tbl_liquidacion', 'INSERT', NEW.`id_liquidacion`, @colfe_usuario, COALESCE(@colfe_origen, 'sistema'), NULL, JSON_OBJECT('id_liquidacion', NEW.`id_liquidacion`, 'id_produccion', NEW.`id_produccion`, 'id_precio', NEW.`id_precio`, 'id_socio', NEW.`id_socio`, 'vinculacion', NEW.`vinculacion`, 'quincena', NEW.`quincena`, 'identificacion', NEW.`identificacion`, 'total_litros', NEW.`total_litros`, 'precio_litro', NEW.`precio_litro`, 'total_ingresos', NEW.`total_ingresos`, 'total_deducibles', NEW.`total_deducibles`, 'neto_a_pagar', NEW.`neto_a_pagar`, 'estado', NEW.`estado`, 'fecha_liquidacion', NEW.`fecha_liquidacion`));
END//
DELIMITER ;

DROP TRIGGER IF EXISTS `tr_aud_liquidacion_u`;
DELIMITER //
CREATE TRIGGER `tr_aud_liquidacion_u` AFTER UPDATE ON `tbl_liquidacion` FOR EACH ROW
BEGIN
    IF NOT (OLD.`id_liquidacion` <=> NEW.`id_liquidacion` AND OLD.`id_produccion` <=> NEW.`id_produccion` AND OLD.`id_precio` <=> NEW.`id_precio` AND OLD.`id_socio` <=> NEW.`id_socio` AND OLD.`vinculacion` <=> NEW.`vinculacion` AND OLD.`quincena` <=> NEW.`quincena` AND OLD.`identificacion` <=> NEW.`identificacion` AND OLD.`total_litros` <=> NEW.`total_litros` AND OLD.`precio_litro` <=> NEW.`precio_litro` AND OLD.`total_ingresos` <=> NEW.`total_ingresos` AND OLD.`total_deducibles` <=> NEW.`total_deducibles` AND OLD.`neto_a_pagar` <=> NEW.`neto_a_pagar` AND OLD.`estado` <=> NEW.`estado` AND OLD.`fecha_liquidacion` <=> NEW.`fecha_liquidacion`) THEN
        INSERT INTO `tbl_auditoria` (`tabla`, `accion`, `id_registro`, `id_usuario`, `origen`, `datos_antes`, `datos_despues`)
        VALUES ('tbl_liquidacion', 'UPDATE', NEW.`id_liquidacion`, @colfe_usuario, COALESCE(@colfe_origen, 'sistema'), JSON_OBJECT('id_liquidacion', OLD.`id_liquidacion`, 'id_produccion', OLD.`id_produccion`, 'id_precio', OLD.`id_precio`, 'id_socio', OLD.`id_socio`, 'vinculacion', OLD.`vinculacion`, 'quincena', OLD.`quincena`, 'identificacion', OLD.`identificacion`, 'total_litros', OLD.`total_litros`, 'precio_litro', OLD.`precio_litro`, 'total_ingresos', OLD.`total_ingresos`, 'total_deducibles', OLD.`total_deducibles`, 'neto_a_pagar', OLD.`neto_a_pagar`, 'estado', OLD.`estado`, 'fecha_liquidacion', OLD.`fecha_liquidacion`), JSON_OBJECT('id_liquidacion', NEW.`id_liquidacion`, 'id_produccion', NEW.`id_produccion`, 'id_precio', NEW.`id_precio`, 'id_socio', NEW.`id_socio`, 'vinculacion', NEW.`vinculacion`, 'quincena', NEW.`quincena`, 'identificacion', NEW.`identificacion`, 'total_litros', NEW.`total_litros`, 'precio_litro', NEW.`precio_litro`, 'total_ingresos', NEW.`total_ingresos`, 'total_deducibles', NEW.`total_deducibles`, 'neto_a_pagar', NEW.`neto_a_pagar`, 'estado', NEW.`estado`, 'fecha_liquidacion', NEW.`fecha_liquidacion`));
    END IF;
END//
DELIMITER ;

DROP TRIGGER IF EXISTS `tr_aud_liquidacion_d`;
DELIMITER //
CREATE TRIGGER `tr_aud_liquidacion_d` AFTER DELETE ON `tbl_liquidacion` FOR EACH ROW
BEGIN
    INSERT INTO `tbl_auditoria` (`tabla`, `accion`, `id_registro`, `id_usuario`, `origen`, `datos_antes`, `datos_despues`)
        VALUES ('tbl_liquidacion', 'DELETE', OLD.`id_liquidacion`, @colfe_usuario, COALESCE(@colfe_origen, 'sistema'), JSON_OBJECT('id_liquidacion', OLD.`id_liquidacion`, 'id_produccion', OLD.`id_produccion`, 'id_precio', OLD.`id_precio`, 'id_socio', OLD.`id_socio`, 'vinculacion', OLD.`vinculacion`, 'quincena', OLD.`quincena`, 'identificacion', OLD.`identificacion`, 'total_litros', OLD.`total_litros`, 'precio_litro', OLD.`precio_litro`, 'total_ingresos', OLD.`total_ingresos`, 'total_deducibles', OLD.`total_deducibles`, 'neto_a_pagar', OLD.`neto_a_pagar`, 'estado', OLD.`estado`, 'fecha_liquidacion', OLD.`fecha_liquidacion`), NULL);
END//
DELIMITER ;

DROP TRIGGER IF EXISTS `tr_aud_deducibles_i`;
DELIMITER //
CREATE TRIGGER `tr_aud_deducibles_i` AFTER INSERT ON `tbl_deducibles` FOR EACH ROW
BEGIN
    INSERT INTO `tbl_auditoria` (`tabla`, `accion`, `id_registro`, `id_usuario`, `origen`, `datos_antes`, `datos_despues`)
        VALUES ('tbl_deducibles', 'INSERT', NEW.`id_deducible`, @colfe_usuario, COALESCE(@colfe_origen, 'sistema'), NULL, JSON_OBJECT('id_deducible', NEW.`id_deducible`, 'vinculacion', NEW.`vinculacion`, 'nombre', NEW.`nombre`, 'tipo_valor', NEW.`tipo_valor`, 'valor', NEW.`valor`, 'fecha', NEW.`fecha`, 'estado', NEW.`estado`));
END//
DELIMITER ;

DROP TRIGGER IF EXISTS `tr_aud_deducibles_u`;
DELIMITER //
CREATE TRIGGER `tr_aud_deducibles_u` AFTER UPDATE ON `tbl_deducibles` FOR EACH ROW
BEGIN
    IF NOT (OLD.`id_deducible` <=> NEW.`id_deducible` AND OLD.`vinculacion` <=> NEW.`vinculacion` AND OLD.`nombre` <=> NEW.`nombre` AND OLD.`tipo_valor` <=> NEW.`tipo_valor` AND OLD.`valor` <=> NEW.`valor` AND OLD.`fecha` <=> NEW.`fecha` AND OLD.`estado` <=> NEW.`estado`) THEN
        INSERT INTO `tbl_auditoria` (`tabla`, `accion`, `id_registro`, `id_usuario`, `origen`, `datos_antes`, `datos_despues`)
        VALUES ('tbl_deducibles', 'UPDATE', NEW.`id_deducible`, @colfe_usuario, COALESCE(@colfe_origen, 'sistema'), JSON_OBJECT('id_deducible', OLD.`id_deducible`, 'vinculacion', OLD.`vinculacion`, 'nombre', OLD.`nombre`, 'tipo_valor', OLD.`tipo_valor`, 'valor', OLD.`valor`, 'fecha', OLD.`fecha`, 'estado', OLD.`estado`), JSON_OBJECT('id_deducible', NEW.`id_deducible`, 'vinculacion', NEW.`vinculacion`, 'nombre', NEW.`nombre`, 'tipo_valor', NEW.`tipo_valor`, 'valor', NEW.`valor`, 'fecha', NEW.`fecha`, 'estado', NEW.`estado`));
    END IF;
END//
DELIMITER ;

DROP TRIGGER IF EXISTS `tr_aud_deducibles_d`;
DELIMITER //
CREATE TRIGGER `tr_aud_deducibles_d` AFTER DELETE ON `tbl_deducibles` FOR EACH ROW
BEGIN
    INSERT INTO `tbl_auditoria` (`tabla`, `accion`, `id_registro`, `id_usuario`, `origen`, `datos_antes`, `datos_despues`)
        VALUES ('tbl_deducibles', 'DELETE', OLD.`id_deducible`, @colfe_usuario, COALESCE(@colfe_origen, 'sistema'), JSON_OBJECT('id_deducible', OLD.`id_deducible`, 'vinculacion', OLD.`vinculacion`, 'nombre', OLD.`nombre`, 'tipo_valor', OLD.`tipo_valor`, 'valor', OLD.`valor`, 'fecha', OLD.`fecha`, 'estado', OLD.`estado`), NULL);
END//
DELIMITER ;
