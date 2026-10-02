-- =====================================================================================
-- REINICIO DE PRODUCCIÓN: elimina TODOS los datos demo y deja la base lista para datos reales.
--
--   NO EJECUTAR A MANO. Usar deploy/reset_produccion.sh, que exige respaldo previo y confirmación.
--
-- Se ELIMINAN:   socios, recolecciones, producción, liquidaciones, anticipos, tokens de API e
--                intentos de login. Los contadores (AUTO_INCREMENT) vuelven a 1.
--                También el generador de datos falsos (spInsertIntoRecoleccion y generar_litros_leche).
-- Se CONSERVAN:  usuarios (el administrador), precios y deducibles (revisar sus valores con la
--                cooperativa antes de liquidar), y todo el esquema, vistas, triggers y procedimientos
--                de la aplicación.
-- =====================================================================================
SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE tbl_liquidacion;
TRUNCATE TABLE tbl_produccion;
TRUNCATE TABLE tbl_recoleccion;
TRUNCATE TABLE tbl_anticipos;
TRUNCATE TABLE tbl_socios;
TRUNCATE TABLE tbl_api_tokens;
TRUNCATE TABLE tbl_login_intentos;

SET FOREIGN_KEY_CHECKS = 1;

-- El generador del demo inserta litros aleatorios: no debe poder ejecutarse en producción
DROP PROCEDURE IF EXISTS spInsertIntoRecoleccion;
DROP FUNCTION  IF EXISTS generar_litros_leche;
