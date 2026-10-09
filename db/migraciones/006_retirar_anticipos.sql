-- 006: retirar el módulo de anticipos (decisión de COLFE, 9 oct 2026).
--  * Se eliminan la tabla tbl_anticipos, su vista v_anticipos_completos, el procedimiento
--    sp_total_anticipos_socio y los triggers tr_anticipos_before_insert y tr_aud_anticipos_*.
--  * tbl_liquidacion pierde la columna total_anticipos. El neto pasa a ser ingresos - deducibles:
--    las liquidaciones que descontaron anticipos (en el demo, 16) se recalculan.
--  * spProcesarLiquidacionQuincenal y los triggers de auditoría de liquidación se recrean sin anticipos.
--  * tbl_auditoria conserva el historial de los anticipos que ya se habían registrado.
-- DESTRUCTIVA: borra los datos de anticipos. Haga un respaldo antes (deploy/backup.sh).
-- Es idempotente. Se aplica después de 005.

-- El procedimiento lleva mensajes con acentos: el cliente debe leer el archivo como UTF-8
SET NAMES utf8mb4;

-- 1. Triggers, vista y procedimiento que dependen de los anticipos o de total_anticipos
DROP TRIGGER IF EXISTS `tr_anticipos_before_insert`;
DROP TRIGGER IF EXISTS `tr_aud_anticipos_i`;
DROP TRIGGER IF EXISTS `tr_aud_anticipos_u`;
DROP TRIGGER IF EXISTS `tr_aud_anticipos_d`;
DROP VIEW IF EXISTS `v_anticipos_completos`;
DROP PROCEDURE IF EXISTS `sp_total_anticipos_socio`;
-- Se quitan los de liquidación para recalcular sin dejar rastro de auditoría; se recrean abajo
DROP TRIGGER IF EXISTS `tr_aud_liquidacion_i`;
DROP TRIGGER IF EXISTS `tr_aud_liquidacion_u`;
DROP TRIGGER IF EXISTS `tr_aud_liquidacion_d`;

-- 2. tbl_liquidacion: recalcular el neto y quitar total_anticipos
DROP PROCEDURE IF EXISTS `sp_mig006`;
DELIMITER //
CREATE PROCEDURE `sp_mig006`()
BEGIN
    IF EXISTS (SELECT 1 FROM information_schema.columns
                WHERE table_schema = DATABASE() AND table_name = 'tbl_liquidacion' AND column_name = 'total_anticipos') THEN
        UPDATE `tbl_liquidacion`
           SET `neto_a_pagar` = ROUND(`total_ingresos` - `total_deducibles`, 2)
         WHERE `total_anticipos` <> 0;
        IF EXISTS (SELECT 1 FROM information_schema.table_constraints
                    WHERE table_schema = DATABASE() AND table_name = 'tbl_liquidacion' AND constraint_name = 'ck_liquidacion_montos') THEN
            ALTER TABLE `tbl_liquidacion` DROP CHECK `ck_liquidacion_montos`;
        END IF;
        ALTER TABLE `tbl_liquidacion` DROP COLUMN `total_anticipos`;
    END IF;
    IF NOT EXISTS (SELECT 1 FROM information_schema.table_constraints
                    WHERE table_schema = DATABASE() AND table_name = 'tbl_liquidacion' AND constraint_name = 'ck_liquidacion_montos') THEN
        ALTER TABLE `tbl_liquidacion` ADD CONSTRAINT `ck_liquidacion_montos`
            CHECK (`total_litros` >= 0 AND `total_ingresos` >= 0 AND `total_deducibles` >= 0);
    END IF;
END//
DELIMITER ;
CALL `sp_mig006`();
DROP PROCEDURE `sp_mig006`;

-- 3. La tabla de anticipos
DROP TABLE IF EXISTS `tbl_anticipos`;

-- 4. Procedimiento de liquidación sin anticipos
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

        -- Paso 2: Poblar tabla liquidacion con los cálculos completos 
			INSERT INTO tbl_liquidacion (
			    id_produccion, id_deducible, id_precio, id_socio, vinculacion, quincena, identificacion,
			    total_litros, precio_litro, total_ingresos,
			    fedegan, administracion, ahorro, total_deducibles,
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
			    -- Neto a pagar = ingresos - deducibles
			    ROUND(
			        (p.total_litros * pr.precio) -
			        (((p.total_litros * pr.precio) * (d.fedegan / 100)) + d.administracion + d.ahorro)
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

-- 5. Triggers de auditoría de liquidación sin total_anticipos
DELIMITER //
CREATE TRIGGER `tr_aud_liquidacion_i` AFTER INSERT ON `tbl_liquidacion` FOR EACH ROW
BEGIN
    INSERT INTO `tbl_auditoria` (`tabla`, `accion`, `id_registro`, `id_usuario`, `origen`, `datos_antes`, `datos_despues`)
        VALUES ('tbl_liquidacion', 'INSERT', NEW.`id_liquidacion`, @colfe_usuario, COALESCE(@colfe_origen, 'sistema'), NULL, JSON_OBJECT('id_liquidacion', NEW.`id_liquidacion`, 'id_produccion', NEW.`id_produccion`, 'id_deducible', NEW.`id_deducible`, 'id_precio', NEW.`id_precio`, 'id_socio', NEW.`id_socio`, 'vinculacion', NEW.`vinculacion`, 'quincena', NEW.`quincena`, 'identificacion', NEW.`identificacion`, 'total_litros', NEW.`total_litros`, 'precio_litro', NEW.`precio_litro`, 'total_ingresos', NEW.`total_ingresos`, 'fedegan', NEW.`fedegan`, 'administracion', NEW.`administracion`, 'ahorro', NEW.`ahorro`, 'total_deducibles', NEW.`total_deducibles`, 'neto_a_pagar', NEW.`neto_a_pagar`, 'estado', NEW.`estado`, 'fecha_liquidacion', NEW.`fecha_liquidacion`));
END//

CREATE TRIGGER `tr_aud_liquidacion_u` AFTER UPDATE ON `tbl_liquidacion` FOR EACH ROW
BEGIN
    IF NOT (OLD.`id_liquidacion` <=> NEW.`id_liquidacion` AND OLD.`id_produccion` <=> NEW.`id_produccion` AND OLD.`id_deducible` <=> NEW.`id_deducible` AND OLD.`id_precio` <=> NEW.`id_precio` AND OLD.`id_socio` <=> NEW.`id_socio` AND OLD.`vinculacion` <=> NEW.`vinculacion` AND OLD.`quincena` <=> NEW.`quincena` AND OLD.`identificacion` <=> NEW.`identificacion` AND OLD.`total_litros` <=> NEW.`total_litros` AND OLD.`precio_litro` <=> NEW.`precio_litro` AND OLD.`total_ingresos` <=> NEW.`total_ingresos` AND OLD.`fedegan` <=> NEW.`fedegan` AND OLD.`administracion` <=> NEW.`administracion` AND OLD.`ahorro` <=> NEW.`ahorro` AND OLD.`total_deducibles` <=> NEW.`total_deducibles` AND OLD.`neto_a_pagar` <=> NEW.`neto_a_pagar` AND OLD.`estado` <=> NEW.`estado` AND OLD.`fecha_liquidacion` <=> NEW.`fecha_liquidacion`) THEN
        INSERT INTO `tbl_auditoria` (`tabla`, `accion`, `id_registro`, `id_usuario`, `origen`, `datos_antes`, `datos_despues`)
        VALUES ('tbl_liquidacion', 'UPDATE', NEW.`id_liquidacion`, @colfe_usuario, COALESCE(@colfe_origen, 'sistema'), JSON_OBJECT('id_liquidacion', OLD.`id_liquidacion`, 'id_produccion', OLD.`id_produccion`, 'id_deducible', OLD.`id_deducible`, 'id_precio', OLD.`id_precio`, 'id_socio', OLD.`id_socio`, 'vinculacion', OLD.`vinculacion`, 'quincena', OLD.`quincena`, 'identificacion', OLD.`identificacion`, 'total_litros', OLD.`total_litros`, 'precio_litro', OLD.`precio_litro`, 'total_ingresos', OLD.`total_ingresos`, 'fedegan', OLD.`fedegan`, 'administracion', OLD.`administracion`, 'ahorro', OLD.`ahorro`, 'total_deducibles', OLD.`total_deducibles`, 'neto_a_pagar', OLD.`neto_a_pagar`, 'estado', OLD.`estado`, 'fecha_liquidacion', OLD.`fecha_liquidacion`), JSON_OBJECT('id_liquidacion', NEW.`id_liquidacion`, 'id_produccion', NEW.`id_produccion`, 'id_deducible', NEW.`id_deducible`, 'id_precio', NEW.`id_precio`, 'id_socio', NEW.`id_socio`, 'vinculacion', NEW.`vinculacion`, 'quincena', NEW.`quincena`, 'identificacion', NEW.`identificacion`, 'total_litros', NEW.`total_litros`, 'precio_litro', NEW.`precio_litro`, 'total_ingresos', NEW.`total_ingresos`, 'fedegan', NEW.`fedegan`, 'administracion', NEW.`administracion`, 'ahorro', NEW.`ahorro`, 'total_deducibles', NEW.`total_deducibles`, 'neto_a_pagar', NEW.`neto_a_pagar`, 'estado', NEW.`estado`, 'fecha_liquidacion', NEW.`fecha_liquidacion`));
    END IF;
END//

CREATE TRIGGER `tr_aud_liquidacion_d` AFTER DELETE ON `tbl_liquidacion` FOR EACH ROW
BEGIN
    INSERT INTO `tbl_auditoria` (`tabla`, `accion`, `id_registro`, `id_usuario`, `origen`, `datos_antes`, `datos_despues`)
        VALUES ('tbl_liquidacion', 'DELETE', OLD.`id_liquidacion`, @colfe_usuario, COALESCE(@colfe_origen, 'sistema'), JSON_OBJECT('id_liquidacion', OLD.`id_liquidacion`, 'id_produccion', OLD.`id_produccion`, 'id_deducible', OLD.`id_deducible`, 'id_precio', OLD.`id_precio`, 'id_socio', OLD.`id_socio`, 'vinculacion', OLD.`vinculacion`, 'quincena', OLD.`quincena`, 'identificacion', OLD.`identificacion`, 'total_litros', OLD.`total_litros`, 'precio_litro', OLD.`precio_litro`, 'total_ingresos', OLD.`total_ingresos`, 'fedegan', OLD.`fedegan`, 'administracion', OLD.`administracion`, 'ahorro', OLD.`ahorro`, 'total_deducibles', OLD.`total_deducibles`, 'neto_a_pagar', OLD.`neto_a_pagar`, 'estado', OLD.`estado`, 'fecha_liquidacion', OLD.`fecha_liquidacion`), NULL);
END//
DELIMITER ;
