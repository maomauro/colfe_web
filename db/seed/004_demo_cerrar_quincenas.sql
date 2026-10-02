-- SOLO PARA EL DEMO. Se aplica después de 003_demo_liquidar_pendientes.sql.
--
-- El seed deja todas las liquidaciones en 'pre-liquidacion'. El dashboard solo grafica las quincenas
-- ya confirmadas ('liquidacion'), así que la pantalla de inicio salía vacía. Se cierran todas las
-- quincenas anteriores al 15-ago-2026 y se deja la última en pre-liquidación para poder mostrar la
-- confirmación desde la aplicación. Es idempotente.
UPDATE tbl_liquidacion
   SET estado = 'liquidacion'
 WHERE estado = 'pre-liquidacion'
   AND fecha_liquidacion < '2026-08-15';
