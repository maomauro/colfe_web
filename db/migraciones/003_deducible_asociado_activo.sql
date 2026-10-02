-- 003: el deducible de la vinculación 'asociado' quedó con estado NULL en el seed.
-- spProcesarLiquidacionQuincenal solo toma deducibles con estado = 'activo', así que los socios
-- asociados nunca se liquidaban y el procedimiento los omitía sin avisar.
-- Activa el deducible solo si no hay ya uno activo para esa vinculación. Es idempotente.
UPDATE `tbl_deducibles`
   SET `estado` = 'activo'
 WHERE `id_deducible` = (SELECT `id` FROM (
           SELECT MAX(`id_deducible`) AS `id` FROM `tbl_deducibles`
            WHERE `vinculacion` = 'asociado' AND `estado` IS NULL) AS `candidato`)
   AND (SELECT COUNT(*) FROM (
           SELECT 1 FROM `tbl_deducibles`
            WHERE `vinculacion` = 'asociado' AND `estado` = 'activo') AS `activos`) = 0;
