-- 002: autenticación segura.
--  * tbl_login_intentos: registro de intentos fallidos para el bloqueo por intentos.
--  * Se eliminan los usuarios demo con contraseña en texto plano (admin/admin, user/12345).
--    Después de aplicar esta migración hay que crear el usuario administrador con:
--        php db/tools/crear_usuario.php <usuario>
-- Es idempotente. Se aplica después de 001.
CREATE TABLE IF NOT EXISTS `tbl_login_intentos` (
  `id_intento` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `ip` varchar(45) NOT NULL,
  `creado_en` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_intento`),
  KEY `idx_intentos_usuario` (`username`,`creado_en`),
  KEY `idx_intentos_ip` (`ip`,`creado_en`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Solo borra los usuarios demo si todavía tienen la clave de ejemplo en texto plano.
-- Los tokens de API asociados se eliminan en cascada.
DELETE FROM `tbl_usuarios` WHERE `username` = 'admin' AND `password` = 'admin';
DELETE FROM `tbl_usuarios` WHERE `username` = 'user'  AND `password` = '12345';
