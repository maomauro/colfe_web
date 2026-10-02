<?php
/**
 * Arranque de las pruebas. Usa la configuración real de la aplicación (variables DB_* del entorno
 * o de .env): las pruebas de liquidación son de integración y necesitan MySQL con el seed demo,
 * las migraciones y db/seed/003_demo_liquidar_pendientes.sql aplicados.
 * ¡Escriben en la base! Úsense solo contra una base desechable (CI, Docker de desarrollo).
 */
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/modelos/conexion.php';
require_once __DIR__ . '/../src/modelos/calendario.modelo.php';

if (!defined('ENVIRONMENT') || ENVIRONMENT === 'production') {
    fwrite(STDERR, "Las pruebas modifican datos: defina ENVIRONMENT=development o staging (nunca production).\n");
    exit(1);
}
require_once __DIR__ . '/liquidacion/BaseDeDatosTestCase.php';
