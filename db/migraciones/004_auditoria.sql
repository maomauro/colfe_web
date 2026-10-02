-- 004: auditoría de cambios (generado por db/tools/generar_migracion_auditoria.py; no editar a mano).
--
-- Registra INSERT/UPDATE/DELETE de liquidaciones, anticipos, precios, deducibles y socios, y las
-- ediciones de recolección, con el usuario (@colfe_usuario), el origen ('web', 'api' o 'sistema')
-- y los valores antes/después en JSON. Una actualización que no cambia nada no se registra.
--
-- En una base con datos demo conviene aplicarla DESPUÉS de los archivos db/seed/00x_demo_*.sql,
-- para que la regularización del demo no llene la auditoría. Es idempotente.
CREATE TABLE IF NOT EXISTS `tbl_auditoria` (
  `id_auditoria` bigint NOT NULL AUTO_INCREMENT,
  `fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `tabla` varchar(40) NOT NULL,
  `accion` enum('INSERT','UPDATE','DELETE') NOT NULL,
  `id_registro` varchar(40) NOT NULL,
  `id_usuario` int DEFAULT NULL,
  `origen` varchar(10) NOT NULL DEFAULT 'sistema',
  `datos_antes` json DEFAULT NULL,
  `datos_despues` json DEFAULT NULL,
  PRIMARY KEY (`id_auditoria`),
  KEY `idx_aud_registro` (`tabla`,`id_registro`),
  KEY `idx_aud_fecha` (`fecha`),
  KEY `idx_aud_usuario` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE OR REPLACE VIEW `v_auditoria` AS
  SELECT a.`id_auditoria`, a.`fecha`, a.`tabla`, a.`accion`, a.`id_registro`,
         a.`id_usuario`, u.`username`, a.`origen`, a.`datos_antes`, a.`datos_despues`
    FROM `tbl_auditoria` a
    LEFT JOIN `tbl_usuarios` u ON u.`id` = a.`id_usuario`;

DROP TRIGGER IF EXISTS `tr_aud_liquidacion_i`;
DELIMITER //
CREATE TRIGGER `tr_aud_liquidacion_i` AFTER INSERT ON `tbl_liquidacion` FOR EACH ROW
BEGIN
    INSERT INTO `tbl_auditoria` (`tabla`, `accion`, `id_registro`, `id_usuario`, `origen`, `datos_antes`, `datos_despues`)
        VALUES ('tbl_liquidacion', 'INSERT', NEW.`id_liquidacion`, @colfe_usuario, COALESCE(@colfe_origen, 'sistema'), NULL, JSON_OBJECT('id_liquidacion', NEW.`id_liquidacion`, 'id_produccion', NEW.`id_produccion`, 'id_deducible', NEW.`id_deducible`, 'id_precio', NEW.`id_precio`, 'id_socio', NEW.`id_socio`, 'vinculacion', NEW.`vinculacion`, 'quincena', NEW.`quincena`, 'identificacion', NEW.`identificacion`, 'total_litros', NEW.`total_litros`, 'precio_litro', NEW.`precio_litro`, 'total_ingresos', NEW.`total_ingresos`, 'fedegan', NEW.`fedegan`, 'administracion', NEW.`administracion`, 'ahorro', NEW.`ahorro`, 'total_deducibles', NEW.`total_deducibles`, 'total_anticipos', NEW.`total_anticipos`, 'neto_a_pagar', NEW.`neto_a_pagar`, 'estado', NEW.`estado`, 'fecha_liquidacion', NEW.`fecha_liquidacion`));
END//
DELIMITER ;

DROP TRIGGER IF EXISTS `tr_aud_liquidacion_u`;
DELIMITER //
CREATE TRIGGER `tr_aud_liquidacion_u` AFTER UPDATE ON `tbl_liquidacion` FOR EACH ROW
BEGIN
    IF NOT (OLD.`id_liquidacion` <=> NEW.`id_liquidacion` AND OLD.`id_produccion` <=> NEW.`id_produccion` AND OLD.`id_deducible` <=> NEW.`id_deducible` AND OLD.`id_precio` <=> NEW.`id_precio` AND OLD.`id_socio` <=> NEW.`id_socio` AND OLD.`vinculacion` <=> NEW.`vinculacion` AND OLD.`quincena` <=> NEW.`quincena` AND OLD.`identificacion` <=> NEW.`identificacion` AND OLD.`total_litros` <=> NEW.`total_litros` AND OLD.`precio_litro` <=> NEW.`precio_litro` AND OLD.`total_ingresos` <=> NEW.`total_ingresos` AND OLD.`fedegan` <=> NEW.`fedegan` AND OLD.`administracion` <=> NEW.`administracion` AND OLD.`ahorro` <=> NEW.`ahorro` AND OLD.`total_deducibles` <=> NEW.`total_deducibles` AND OLD.`total_anticipos` <=> NEW.`total_anticipos` AND OLD.`neto_a_pagar` <=> NEW.`neto_a_pagar` AND OLD.`estado` <=> NEW.`estado` AND OLD.`fecha_liquidacion` <=> NEW.`fecha_liquidacion`) THEN
        INSERT INTO `tbl_auditoria` (`tabla`, `accion`, `id_registro`, `id_usuario`, `origen`, `datos_antes`, `datos_despues`)
        VALUES ('tbl_liquidacion', 'UPDATE', NEW.`id_liquidacion`, @colfe_usuario, COALESCE(@colfe_origen, 'sistema'), JSON_OBJECT('id_liquidacion', OLD.`id_liquidacion`, 'id_produccion', OLD.`id_produccion`, 'id_deducible', OLD.`id_deducible`, 'id_precio', OLD.`id_precio`, 'id_socio', OLD.`id_socio`, 'vinculacion', OLD.`vinculacion`, 'quincena', OLD.`quincena`, 'identificacion', OLD.`identificacion`, 'total_litros', OLD.`total_litros`, 'precio_litro', OLD.`precio_litro`, 'total_ingresos', OLD.`total_ingresos`, 'fedegan', OLD.`fedegan`, 'administracion', OLD.`administracion`, 'ahorro', OLD.`ahorro`, 'total_deducibles', OLD.`total_deducibles`, 'total_anticipos', OLD.`total_anticipos`, 'neto_a_pagar', OLD.`neto_a_pagar`, 'estado', OLD.`estado`, 'fecha_liquidacion', OLD.`fecha_liquidacion`), JSON_OBJECT('id_liquidacion', NEW.`id_liquidacion`, 'id_produccion', NEW.`id_produccion`, 'id_deducible', NEW.`id_deducible`, 'id_precio', NEW.`id_precio`, 'id_socio', NEW.`id_socio`, 'vinculacion', NEW.`vinculacion`, 'quincena', NEW.`quincena`, 'identificacion', NEW.`identificacion`, 'total_litros', NEW.`total_litros`, 'precio_litro', NEW.`precio_litro`, 'total_ingresos', NEW.`total_ingresos`, 'fedegan', NEW.`fedegan`, 'administracion', NEW.`administracion`, 'ahorro', NEW.`ahorro`, 'total_deducibles', NEW.`total_deducibles`, 'total_anticipos', NEW.`total_anticipos`, 'neto_a_pagar', NEW.`neto_a_pagar`, 'estado', NEW.`estado`, 'fecha_liquidacion', NEW.`fecha_liquidacion`));
    END IF;
END//
DELIMITER ;

DROP TRIGGER IF EXISTS `tr_aud_liquidacion_d`;
DELIMITER //
CREATE TRIGGER `tr_aud_liquidacion_d` AFTER DELETE ON `tbl_liquidacion` FOR EACH ROW
BEGIN
    INSERT INTO `tbl_auditoria` (`tabla`, `accion`, `id_registro`, `id_usuario`, `origen`, `datos_antes`, `datos_despues`)
        VALUES ('tbl_liquidacion', 'DELETE', OLD.`id_liquidacion`, @colfe_usuario, COALESCE(@colfe_origen, 'sistema'), JSON_OBJECT('id_liquidacion', OLD.`id_liquidacion`, 'id_produccion', OLD.`id_produccion`, 'id_deducible', OLD.`id_deducible`, 'id_precio', OLD.`id_precio`, 'id_socio', OLD.`id_socio`, 'vinculacion', OLD.`vinculacion`, 'quincena', OLD.`quincena`, 'identificacion', OLD.`identificacion`, 'total_litros', OLD.`total_litros`, 'precio_litro', OLD.`precio_litro`, 'total_ingresos', OLD.`total_ingresos`, 'fedegan', OLD.`fedegan`, 'administracion', OLD.`administracion`, 'ahorro', OLD.`ahorro`, 'total_deducibles', OLD.`total_deducibles`, 'total_anticipos', OLD.`total_anticipos`, 'neto_a_pagar', OLD.`neto_a_pagar`, 'estado', OLD.`estado`, 'fecha_liquidacion', OLD.`fecha_liquidacion`), NULL);
END//
DELIMITER ;

DROP TRIGGER IF EXISTS `tr_aud_anticipos_i`;
DELIMITER //
CREATE TRIGGER `tr_aud_anticipos_i` AFTER INSERT ON `tbl_anticipos` FOR EACH ROW
BEGIN
    INSERT INTO `tbl_auditoria` (`tabla`, `accion`, `id_registro`, `id_usuario`, `origen`, `datos_antes`, `datos_despues`)
        VALUES ('tbl_anticipos', 'INSERT', NEW.`id_anticipo`, @colfe_usuario, COALESCE(@colfe_origen, 'sistema'), NULL, JSON_OBJECT('id_anticipo', NEW.`id_anticipo`, 'id_socio', NEW.`id_socio`, 'monto', NEW.`monto`, 'fecha_anticipo', NEW.`fecha_anticipo`, 'estado', NEW.`estado`, 'observaciones', NEW.`observaciones`, 'fecha_registro', NEW.`fecha_registro`, 'usuario_registro', NEW.`usuario_registro`));
END//
DELIMITER ;

DROP TRIGGER IF EXISTS `tr_aud_anticipos_u`;
DELIMITER //
CREATE TRIGGER `tr_aud_anticipos_u` AFTER UPDATE ON `tbl_anticipos` FOR EACH ROW
BEGIN
    IF NOT (OLD.`id_anticipo` <=> NEW.`id_anticipo` AND OLD.`id_socio` <=> NEW.`id_socio` AND OLD.`monto` <=> NEW.`monto` AND OLD.`fecha_anticipo` <=> NEW.`fecha_anticipo` AND OLD.`estado` <=> NEW.`estado` AND OLD.`observaciones` <=> NEW.`observaciones` AND OLD.`fecha_registro` <=> NEW.`fecha_registro` AND OLD.`usuario_registro` <=> NEW.`usuario_registro`) THEN
        INSERT INTO `tbl_auditoria` (`tabla`, `accion`, `id_registro`, `id_usuario`, `origen`, `datos_antes`, `datos_despues`)
        VALUES ('tbl_anticipos', 'UPDATE', NEW.`id_anticipo`, @colfe_usuario, COALESCE(@colfe_origen, 'sistema'), JSON_OBJECT('id_anticipo', OLD.`id_anticipo`, 'id_socio', OLD.`id_socio`, 'monto', OLD.`monto`, 'fecha_anticipo', OLD.`fecha_anticipo`, 'estado', OLD.`estado`, 'observaciones', OLD.`observaciones`, 'fecha_registro', OLD.`fecha_registro`, 'usuario_registro', OLD.`usuario_registro`), JSON_OBJECT('id_anticipo', NEW.`id_anticipo`, 'id_socio', NEW.`id_socio`, 'monto', NEW.`monto`, 'fecha_anticipo', NEW.`fecha_anticipo`, 'estado', NEW.`estado`, 'observaciones', NEW.`observaciones`, 'fecha_registro', NEW.`fecha_registro`, 'usuario_registro', NEW.`usuario_registro`));
    END IF;
END//
DELIMITER ;

DROP TRIGGER IF EXISTS `tr_aud_anticipos_d`;
DELIMITER //
CREATE TRIGGER `tr_aud_anticipos_d` AFTER DELETE ON `tbl_anticipos` FOR EACH ROW
BEGIN
    INSERT INTO `tbl_auditoria` (`tabla`, `accion`, `id_registro`, `id_usuario`, `origen`, `datos_antes`, `datos_despues`)
        VALUES ('tbl_anticipos', 'DELETE', OLD.`id_anticipo`, @colfe_usuario, COALESCE(@colfe_origen, 'sistema'), JSON_OBJECT('id_anticipo', OLD.`id_anticipo`, 'id_socio', OLD.`id_socio`, 'monto', OLD.`monto`, 'fecha_anticipo', OLD.`fecha_anticipo`, 'estado', OLD.`estado`, 'observaciones', OLD.`observaciones`, 'fecha_registro', OLD.`fecha_registro`, 'usuario_registro', OLD.`usuario_registro`), NULL);
END//
DELIMITER ;

DROP TRIGGER IF EXISTS `tr_aud_precios_i`;
DELIMITER //
CREATE TRIGGER `tr_aud_precios_i` AFTER INSERT ON `tbl_precios` FOR EACH ROW
BEGIN
    INSERT INTO `tbl_auditoria` (`tabla`, `accion`, `id_registro`, `id_usuario`, `origen`, `datos_antes`, `datos_despues`)
        VALUES ('tbl_precios', 'INSERT', NEW.`id_precio`, @colfe_usuario, COALESCE(@colfe_origen, 'sistema'), NULL, JSON_OBJECT('id_precio', NEW.`id_precio`, 'vinculacion', NEW.`vinculacion`, 'precio', NEW.`precio`, 'fecha', NEW.`fecha`, 'estado', NEW.`estado`));
END//
DELIMITER ;

DROP TRIGGER IF EXISTS `tr_aud_precios_u`;
DELIMITER //
CREATE TRIGGER `tr_aud_precios_u` AFTER UPDATE ON `tbl_precios` FOR EACH ROW
BEGIN
    IF NOT (OLD.`id_precio` <=> NEW.`id_precio` AND OLD.`vinculacion` <=> NEW.`vinculacion` AND OLD.`precio` <=> NEW.`precio` AND OLD.`fecha` <=> NEW.`fecha` AND OLD.`estado` <=> NEW.`estado`) THEN
        INSERT INTO `tbl_auditoria` (`tabla`, `accion`, `id_registro`, `id_usuario`, `origen`, `datos_antes`, `datos_despues`)
        VALUES ('tbl_precios', 'UPDATE', NEW.`id_precio`, @colfe_usuario, COALESCE(@colfe_origen, 'sistema'), JSON_OBJECT('id_precio', OLD.`id_precio`, 'vinculacion', OLD.`vinculacion`, 'precio', OLD.`precio`, 'fecha', OLD.`fecha`, 'estado', OLD.`estado`), JSON_OBJECT('id_precio', NEW.`id_precio`, 'vinculacion', NEW.`vinculacion`, 'precio', NEW.`precio`, 'fecha', NEW.`fecha`, 'estado', NEW.`estado`));
    END IF;
END//
DELIMITER ;

DROP TRIGGER IF EXISTS `tr_aud_precios_d`;
DELIMITER //
CREATE TRIGGER `tr_aud_precios_d` AFTER DELETE ON `tbl_precios` FOR EACH ROW
BEGIN
    INSERT INTO `tbl_auditoria` (`tabla`, `accion`, `id_registro`, `id_usuario`, `origen`, `datos_antes`, `datos_despues`)
        VALUES ('tbl_precios', 'DELETE', OLD.`id_precio`, @colfe_usuario, COALESCE(@colfe_origen, 'sistema'), JSON_OBJECT('id_precio', OLD.`id_precio`, 'vinculacion', OLD.`vinculacion`, 'precio', OLD.`precio`, 'fecha', OLD.`fecha`, 'estado', OLD.`estado`), NULL);
END//
DELIMITER ;

DROP TRIGGER IF EXISTS `tr_aud_deducibles_i`;
DELIMITER //
CREATE TRIGGER `tr_aud_deducibles_i` AFTER INSERT ON `tbl_deducibles` FOR EACH ROW
BEGIN
    INSERT INTO `tbl_auditoria` (`tabla`, `accion`, `id_registro`, `id_usuario`, `origen`, `datos_antes`, `datos_despues`)
        VALUES ('tbl_deducibles', 'INSERT', NEW.`id_deducible`, @colfe_usuario, COALESCE(@colfe_origen, 'sistema'), NULL, JSON_OBJECT('id_deducible', NEW.`id_deducible`, 'vinculacion', NEW.`vinculacion`, 'fedegan', NEW.`fedegan`, 'administracion', NEW.`administracion`, 'ahorro', NEW.`ahorro`, 'fecha', NEW.`fecha`, 'estado', NEW.`estado`));
END//
DELIMITER ;

DROP TRIGGER IF EXISTS `tr_aud_deducibles_u`;
DELIMITER //
CREATE TRIGGER `tr_aud_deducibles_u` AFTER UPDATE ON `tbl_deducibles` FOR EACH ROW
BEGIN
    IF NOT (OLD.`id_deducible` <=> NEW.`id_deducible` AND OLD.`vinculacion` <=> NEW.`vinculacion` AND OLD.`fedegan` <=> NEW.`fedegan` AND OLD.`administracion` <=> NEW.`administracion` AND OLD.`ahorro` <=> NEW.`ahorro` AND OLD.`fecha` <=> NEW.`fecha` AND OLD.`estado` <=> NEW.`estado`) THEN
        INSERT INTO `tbl_auditoria` (`tabla`, `accion`, `id_registro`, `id_usuario`, `origen`, `datos_antes`, `datos_despues`)
        VALUES ('tbl_deducibles', 'UPDATE', NEW.`id_deducible`, @colfe_usuario, COALESCE(@colfe_origen, 'sistema'), JSON_OBJECT('id_deducible', OLD.`id_deducible`, 'vinculacion', OLD.`vinculacion`, 'fedegan', OLD.`fedegan`, 'administracion', OLD.`administracion`, 'ahorro', OLD.`ahorro`, 'fecha', OLD.`fecha`, 'estado', OLD.`estado`), JSON_OBJECT('id_deducible', NEW.`id_deducible`, 'vinculacion', NEW.`vinculacion`, 'fedegan', NEW.`fedegan`, 'administracion', NEW.`administracion`, 'ahorro', NEW.`ahorro`, 'fecha', NEW.`fecha`, 'estado', NEW.`estado`));
    END IF;
END//
DELIMITER ;

DROP TRIGGER IF EXISTS `tr_aud_deducibles_d`;
DELIMITER //
CREATE TRIGGER `tr_aud_deducibles_d` AFTER DELETE ON `tbl_deducibles` FOR EACH ROW
BEGIN
    INSERT INTO `tbl_auditoria` (`tabla`, `accion`, `id_registro`, `id_usuario`, `origen`, `datos_antes`, `datos_despues`)
        VALUES ('tbl_deducibles', 'DELETE', OLD.`id_deducible`, @colfe_usuario, COALESCE(@colfe_origen, 'sistema'), JSON_OBJECT('id_deducible', OLD.`id_deducible`, 'vinculacion', OLD.`vinculacion`, 'fedegan', OLD.`fedegan`, 'administracion', OLD.`administracion`, 'ahorro', OLD.`ahorro`, 'fecha', OLD.`fecha`, 'estado', OLD.`estado`), NULL);
END//
DELIMITER ;

DROP TRIGGER IF EXISTS `tr_aud_socios_i`;
DELIMITER //
CREATE TRIGGER `tr_aud_socios_i` AFTER INSERT ON `tbl_socios` FOR EACH ROW
BEGIN
    INSERT INTO `tbl_auditoria` (`tabla`, `accion`, `id_registro`, `id_usuario`, `origen`, `datos_antes`, `datos_despues`)
        VALUES ('tbl_socios', 'INSERT', NEW.`id_socio`, @colfe_usuario, COALESCE(@colfe_origen, 'sistema'), NULL, JSON_OBJECT('id_socio', NEW.`id_socio`, 'nombre', NEW.`nombre`, 'apellido', NEW.`apellido`, 'identificacion', NEW.`identificacion`, 'telefono', NEW.`telefono`, 'direccion', NEW.`direccion`, 'vinculacion', NEW.`vinculacion`, 'fecha_ingreso', NEW.`fecha_ingreso`, 'estado', NEW.`estado`));
END//
DELIMITER ;

DROP TRIGGER IF EXISTS `tr_aud_socios_u`;
DELIMITER //
CREATE TRIGGER `tr_aud_socios_u` AFTER UPDATE ON `tbl_socios` FOR EACH ROW
BEGIN
    IF NOT (OLD.`id_socio` <=> NEW.`id_socio` AND OLD.`nombre` <=> NEW.`nombre` AND OLD.`apellido` <=> NEW.`apellido` AND OLD.`identificacion` <=> NEW.`identificacion` AND OLD.`telefono` <=> NEW.`telefono` AND OLD.`direccion` <=> NEW.`direccion` AND OLD.`vinculacion` <=> NEW.`vinculacion` AND OLD.`fecha_ingreso` <=> NEW.`fecha_ingreso` AND OLD.`estado` <=> NEW.`estado`) THEN
        INSERT INTO `tbl_auditoria` (`tabla`, `accion`, `id_registro`, `id_usuario`, `origen`, `datos_antes`, `datos_despues`)
        VALUES ('tbl_socios', 'UPDATE', NEW.`id_socio`, @colfe_usuario, COALESCE(@colfe_origen, 'sistema'), JSON_OBJECT('id_socio', OLD.`id_socio`, 'nombre', OLD.`nombre`, 'apellido', OLD.`apellido`, 'identificacion', OLD.`identificacion`, 'telefono', OLD.`telefono`, 'direccion', OLD.`direccion`, 'vinculacion', OLD.`vinculacion`, 'fecha_ingreso', OLD.`fecha_ingreso`, 'estado', OLD.`estado`), JSON_OBJECT('id_socio', NEW.`id_socio`, 'nombre', NEW.`nombre`, 'apellido', NEW.`apellido`, 'identificacion', NEW.`identificacion`, 'telefono', NEW.`telefono`, 'direccion', NEW.`direccion`, 'vinculacion', NEW.`vinculacion`, 'fecha_ingreso', NEW.`fecha_ingreso`, 'estado', NEW.`estado`));
    END IF;
END//
DELIMITER ;

DROP TRIGGER IF EXISTS `tr_aud_socios_d`;
DELIMITER //
CREATE TRIGGER `tr_aud_socios_d` AFTER DELETE ON `tbl_socios` FOR EACH ROW
BEGIN
    INSERT INTO `tbl_auditoria` (`tabla`, `accion`, `id_registro`, `id_usuario`, `origen`, `datos_antes`, `datos_despues`)
        VALUES ('tbl_socios', 'DELETE', OLD.`id_socio`, @colfe_usuario, COALESCE(@colfe_origen, 'sistema'), JSON_OBJECT('id_socio', OLD.`id_socio`, 'nombre', OLD.`nombre`, 'apellido', OLD.`apellido`, 'identificacion', OLD.`identificacion`, 'telefono', OLD.`telefono`, 'direccion', OLD.`direccion`, 'vinculacion', OLD.`vinculacion`, 'fecha_ingreso', OLD.`fecha_ingreso`, 'estado', OLD.`estado`), NULL);
END//
DELIMITER ;

DROP TRIGGER IF EXISTS `tr_aud_recoleccion_u`;
DELIMITER //
CREATE TRIGGER `tr_aud_recoleccion_u` AFTER UPDATE ON `tbl_recoleccion` FOR EACH ROW
BEGIN
    IF NOT (OLD.`id_recoleccion` <=> NEW.`id_recoleccion` AND OLD.`id_socio` <=> NEW.`id_socio` AND OLD.`fecha` <=> NEW.`fecha` AND OLD.`litros_leche` <=> NEW.`litros_leche` AND OLD.`estado` <=> NEW.`estado`) THEN
        INSERT INTO `tbl_auditoria` (`tabla`, `accion`, `id_registro`, `id_usuario`, `origen`, `datos_antes`, `datos_despues`)
        VALUES ('tbl_recoleccion', 'UPDATE', NEW.`id_recoleccion`, @colfe_usuario, COALESCE(@colfe_origen, 'sistema'), JSON_OBJECT('id_recoleccion', OLD.`id_recoleccion`, 'id_socio', OLD.`id_socio`, 'fecha', OLD.`fecha`, 'litros_leche', OLD.`litros_leche`, 'estado', OLD.`estado`), JSON_OBJECT('id_recoleccion', NEW.`id_recoleccion`, 'id_socio', NEW.`id_socio`, 'fecha', NEW.`fecha`, 'litros_leche', NEW.`litros_leche`, 'estado', NEW.`estado`));
    END IF;
END//
DELIMITER ;
