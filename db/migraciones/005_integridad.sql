-- 005: integridad del modelo (bloque seguro: los datos ya cumplen todo).
--  * NOT NULL en las columnas que el negocio exige (FK, fechas, estados, montos).
--  * UNIQUE: una recolección por socio y fecha; una identificación por socio.
--  * CHECK: litros, precios, deducibles y anticipos no negativos; porcentaje entre 0 y 100.
--  * Índice tbl_recoleccion(fecha, estado), el que usa spProcesarLiquidacionQuincenal.
-- No toca los triggers existentes: siguen dando el mensaje legible; la restricción es la red de seguridad.
-- Si una base ya tiene NULL, duplicados o valores fuera de rango, la migración falla sin cambiar nada
-- de lo que ya aplicó; corrija los datos y vuelva a ejecutarla. Es idempotente. Se aplica después de 004.

DROP PROCEDURE IF EXISTS `sp_mig005_agregar`;
DELIMITER //
CREATE PROCEDURE `sp_mig005_agregar`(IN p_tabla VARCHAR(64), IN p_nombre VARCHAR(64), IN p_ddl TEXT)
BEGIN
    -- Ejecuta p_ddl solo si la tabla aún no tiene un índice ni una restricción con ese nombre.
    IF NOT EXISTS (SELECT 1 FROM information_schema.statistics
                    WHERE table_schema = DATABASE() AND table_name = p_tabla AND index_name = p_nombre)
       AND NOT EXISTS (SELECT 1 FROM information_schema.table_constraints
                        WHERE table_schema = DATABASE() AND table_name = p_tabla AND constraint_name = p_nombre) THEN
        SET @ddl = p_ddl;
        PREPARE s FROM @ddl;
        EXECUTE s;
        DEALLOCATE PREPARE s;
    END IF;
END//
DELIMITER ;

-- NOT NULL -------------------------------------------------------------------------------------------
ALTER TABLE `tbl_socios`
  MODIFY `identificacion` varchar(20) NOT NULL,
  MODIFY `vinculacion` enum('asociado','proveedor') NOT NULL,
  MODIFY `estado` enum('activo','inactivo') NOT NULL;

ALTER TABLE `tbl_recoleccion`
  MODIFY `id_socio` int NOT NULL,
  MODIFY `fecha` date NOT NULL,
  MODIFY `litros_leche` decimal(10,2) NOT NULL,
  MODIFY `estado` enum('confirmado','sin confirmar') NOT NULL;

ALTER TABLE `tbl_produccion`
  MODIFY `id_socio` int NOT NULL,
  MODIFY `fecha` date NOT NULL,
  MODIFY `quincena` enum('1ra','2da') NOT NULL,
  MODIFY `total_litros` decimal(10,2) NOT NULL;

ALTER TABLE `tbl_liquidacion`
  MODIFY `id_produccion` int NOT NULL,
  MODIFY `id_deducible` int NOT NULL,
  MODIFY `id_precio` int NOT NULL,
  MODIFY `id_socio` int NOT NULL,
  MODIFY `quincena` enum('1ra','2da') NOT NULL,
  MODIFY `estado` enum('pre-liquidacion','liquidacion') NOT NULL,
  MODIFY `fecha_liquidacion` date NOT NULL;

ALTER TABLE `tbl_precios`
  MODIFY `vinculacion` enum('asociado','proveedor') NOT NULL,
  MODIFY `precio` decimal(10,2) NOT NULL,
  MODIFY `estado` enum('activo','inactivo') NOT NULL;

ALTER TABLE `tbl_deducibles`
  MODIFY `vinculacion` enum('asociado','proveedor') NOT NULL,
  MODIFY `fedegan` decimal(5,2) NOT NULL,
  MODIFY `administracion` decimal(10,2) NOT NULL,
  MODIFY `ahorro` decimal(10,2) NOT NULL,
  MODIFY `estado` enum('activo','inactivo') NOT NULL;

-- UNIQUE e índice ------------------------------------------------------------------------------------
CALL sp_mig005_agregar('tbl_socios', 'uk_socios_identificacion',
    'ALTER TABLE `tbl_socios` ADD UNIQUE KEY `uk_socios_identificacion` (`identificacion`)');
CALL sp_mig005_agregar('tbl_recoleccion', 'uk_recoleccion_socio_fecha',
    'ALTER TABLE `tbl_recoleccion` ADD UNIQUE KEY `uk_recoleccion_socio_fecha` (`id_socio`, `fecha`)');
CALL sp_mig005_agregar('tbl_recoleccion', 'idx_recoleccion_fecha_estado',
    'ALTER TABLE `tbl_recoleccion` ADD KEY `idx_recoleccion_fecha_estado` (`fecha`, `estado`)');

-- CHECK ----------------------------------------------------------------------------------------------
CALL sp_mig005_agregar('tbl_recoleccion', 'ck_recoleccion_litros',
    'ALTER TABLE `tbl_recoleccion` ADD CONSTRAINT `ck_recoleccion_litros` CHECK (`litros_leche` >= 0)');
CALL sp_mig005_agregar('tbl_produccion', 'ck_produccion_litros',
    'ALTER TABLE `tbl_produccion` ADD CONSTRAINT `ck_produccion_litros` CHECK (`total_litros` >= 0)');
CALL sp_mig005_agregar('tbl_precios', 'ck_precios_precio',
    'ALTER TABLE `tbl_precios` ADD CONSTRAINT `ck_precios_precio` CHECK (`precio` > 0)');
CALL sp_mig005_agregar('tbl_deducibles', 'ck_deducibles_valores',
    'ALTER TABLE `tbl_deducibles` ADD CONSTRAINT `ck_deducibles_valores` CHECK (`fedegan` BETWEEN 0 AND 100 AND `administracion` >= 0 AND `ahorro` >= 0)');
CALL sp_mig005_agregar('tbl_anticipos', 'ck_anticipos_monto',
    'ALTER TABLE `tbl_anticipos` ADD CONSTRAINT `ck_anticipos_monto` CHECK (`monto` > 0)');
CALL sp_mig005_agregar('tbl_liquidacion', 'ck_liquidacion_montos',
    'ALTER TABLE `tbl_liquidacion` ADD CONSTRAINT `ck_liquidacion_montos` CHECK (`total_litros` >= 0 AND `total_ingresos` >= 0 AND `total_deducibles` >= 0 AND `total_anticipos` >= 0)');

DROP PROCEDURE `sp_mig005_agregar`;
