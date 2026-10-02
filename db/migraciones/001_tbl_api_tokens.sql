-- 001: tokens de la API móvil.
-- Se guarda solo el hash SHA-256 del token; el token en claro solo lo conoce el cliente.
-- Se aplica después de db/schema o db/seed. Es idempotente.
CREATE TABLE IF NOT EXISTS `tbl_api_tokens` (
  `id_token` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expira_en` datetime NOT NULL,
  `creado_en` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ultimo_uso` datetime DEFAULT NULL,
  PRIMARY KEY (`id_token`),
  UNIQUE KEY `uk_token_hash` (`token_hash`),
  KEY `idx_expira_en` (`expira_en`),
  CONSTRAINT `fk_api_tokens_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `tbl_usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
