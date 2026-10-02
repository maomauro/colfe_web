-- SOLO PARA EL DEMO. Se aplica después de las migraciones, nunca en una base con datos reales.
--
-- Regulariza las producciones que el seed dejó sin liquidar (los 27 socios "asociado": su deducible
-- estaba inactivo, ver migración 003). Aplica exactamente las fórmulas de
-- spProcesarLiquidacionQuincenal, con el precio y el deducible activos, y solo para producciones
-- que no tienen liquidación. Es idempotente.
INSERT INTO tbl_liquidacion (
    id_produccion, id_deducible, id_precio, id_socio, vinculacion, quincena, identificacion,
    total_litros, precio_litro, total_ingresos,
    fedegan, administracion, ahorro, total_deducibles, total_anticipos,
    neto_a_pagar, estado, fecha_liquidacion
)
SELECT
    p.id_produccion, d.id_deducible, pr.id_precio, p.id_socio, s.vinculacion, p.quincena, s.identificacion,
    p.total_litros, pr.precio,
    ROUND(p.total_litros * pr.precio, 2),
    ROUND((p.total_litros * pr.precio) * (d.fedegan / 100), 2),
    d.administracion, d.ahorro,
    ROUND(((p.total_litros * pr.precio) * (d.fedegan / 100)) + d.administracion + d.ahorro, 2),
    COALESCE(ROUND(SUM(CASE WHEN a.estado = 'aprobado' THEN a.monto ELSE 0 END), 2), 0.00),
    ROUND(
        (p.total_litros * pr.precio)
        - (((p.total_litros * pr.precio) * (d.fedegan / 100)) + d.administracion + d.ahorro)
        - COALESCE(SUM(CASE WHEN a.estado = 'aprobado' THEN a.monto ELSE 0 END), 0.00)
    , 2),
    'pre-liquidacion', p.fecha
FROM tbl_produccion p
JOIN tbl_socios s      ON s.id_socio = p.id_socio AND s.estado = 'activo'
JOIN tbl_deducibles d  ON d.vinculacion = s.vinculacion AND d.estado = 'activo'
JOIN tbl_precios pr    ON pr.vinculacion = s.vinculacion AND pr.estado = 'activo'
LEFT JOIN tbl_anticipos a ON a.id_socio = p.id_socio AND a.estado = 'aprobado'
    AND a.fecha_anticipo BETWEEN
        IF(p.quincena = '1ra',
           DATE_SUB(p.fecha, INTERVAL 14 DAY),
           DATE_ADD(DATE_SUB(p.fecha, INTERVAL DAY(p.fecha) - 1 DAY), INTERVAL 15 DAY))
        AND p.fecha
WHERE NOT EXISTS (SELECT 1 FROM tbl_liquidacion l WHERE l.id_produccion = p.id_produccion)
GROUP BY p.id_produccion, d.id_deducible, pr.id_precio, p.id_socio, s.vinculacion, p.quincena,
         s.identificacion, p.total_litros, pr.precio, d.fedegan, d.administracion, d.ahorro, p.fecha;
